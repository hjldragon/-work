<?php
require_once("current_dir_env.php");
require_once("mgo_user.php");
require_once("redis_login.php");
require_once("redis_id.php");
require_once("mgo_employee.php");
require_once("mgo_login.php");
require_once("mgo_user.php");
require_once("mgo_resources.php");
require_once("mgo_term_binding.php");
require_once("/www/wx.jzzwlcm.com/WxUtil.php");
use \Pub\Mongodb as Mgo;

function Login(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $token         = $_["token"];
    $srctype       = $_['srctype'];    //<<<<<<目前未用到
    $phone         = $_["phone"];
    $password_md5  = $_["password_md5"];
    //$password_md5 = md5($_["password_md5"]);//<<<<<<<<<<接口测试用的
    LogDebug($_);
    $true_phone = PageUtil::GetPhone($phone);
    if(!$true_phone)
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }

    $user      = new \DaoMongodb\User;
    $employee  = new \DaoMongodb\Employee;
    $shop      = new \DaoMongodb\Shop;
    $resources = new Mgo\Resources;
    $term      =  new Mgo\TermBinding;
    $userinfo  = $user->QueryUser($phone, $phone, $password_md5, UserSrc::SHOP);

    if(!$userinfo->userid)
    {
        LogErr("The user is empty");
        return errcode::USER_LOGIN_ERR;
    }

    $employee_user = $employee->GetEmployeeById($userinfo->userid);
    $is_overdue = 0;
    if(count($employee_user) == 1)
    {
            $employee_one = $employee->QueryByUserId($employee_user[0]->shop_id,$userinfo->userid);
            $shopinfo     = $shop->GetShopById($employee_one->shop_id);
            //店铺是否被冻结
            if($shopinfo->is_freeze == \EmployeeFreeze::FREEZE)
            {
                LogErr("shop freeze");
                return errcode::SHOP_IS_FREEZE;
            }

            if($employee_one->is_admin != 1)
            {
                //员工是否被冻结
                if($employee_one->is_freeze == \EmployeeFreeze::FREEZE)
                {
                    LogErr("employee freeze");
                    return errcode::EMPLOYEE_IS_FREEZE;
                }

                $resources_info = $resources->GetList(
                    [
                        'shop_id'        => $employee_one->shop_id,
                        'resources_type' => $srctype,
                        'login'          => 1 // 登录
                    ]
                );
                if(empty($resources_info[0]->resources_id))
                {
                    LogErr("resources not enough");
                    return errcode::RESOURCES_NOT_ENOUGH;
                }

                $term_info = $term->QueryByEmployeeId($employee_one->employee_id);
                if($term_info->term_binding_id && $term_info->term_id != $token)
                {
                    LogErr($employee_one->employee_id."not binging term");
                    return errcode::NOT_BIND_TERM;
                }
            }
            $overdue_resources = $resources->GetList(//过期时间小于1周的资源
                [
                    'shop_id'   => $employee_one->shop_id,
                    'end_time'  => time() + 7*24*60*60
                ]);
            if(count($overdue_resources)>0)
            {
                $is_overdue = 1;
            }

    }


    $ret = LoginSave($userinfo, $token, $resp);
    if(0 != $ret)
    {
        LogErr("Login err");
        return $ret;
    }
    $resp->is_overdue = $is_overdue;
    LogInfo('ok');
    return 0;
}

function LoginWx(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token         = $_["token"];
    $userid        = $_['user_id'];
    //LogDebug($_);
    if(!$userid)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userinfo = \Cache\UsernInfo::Get($userid);

    if(null == $userinfo)
    {
        LogErr("user is password err");
        return errcode::USER_PASSWD_ERR;
    }
    $ret = LoginSave($userinfo, $token, $resp);

    if(0 != $ret)
    {
        LogErr("Login err");
        return $ret;
    }
    return 0;
}

function LoginSave($userinfo, $token, &$resp)
{

    $mongodb  = new \DaoMongodb\Login();
    $entry    = new \DaoMongodb\LoginEntry();


    $now = time();
    $entry->id          = \DaoRedis\Id::GenLoginId();
    $entry->userid      = $userinfo->userid;
    $entry->ip          = $_SERVER['REMOTE_ADDR'];
    $entry->login_time  = $now;
    $entry->logout_time = 0;
    $entry->ctime       = $now;
    $entry->mtime       = $now;
    $entry->delete      = 0;
    $ret = $mongodb->Save($entry);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }

    $redis = new \DaoRedis\Login();
    $info  = new \DaoRedis\LoginEntry();

    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->userid   = $userinfo->userid;
    $info->username = $userinfo->username;
    $info->shop_id  = '';
    $info->login    = 1;
    $ret = $redis->Save($info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }
    $shop_list    = array();
    $mgo          = new \DaoMongodb\Shop;
    $employee_mgo = new \DaoMongodb\Employee;
    $employee = $employee_mgo->GetEmployeeById($userinfo->userid);
    $domain= Cfg::instance()->GetMainDomain();
    foreach ($employee as $key => $value) {

            if($value->is_freeze == EmployeeFreeze::NOFREEZE || $value->is_admin == 1)
            {
                if($value->shop_id){
                    $shop = $mgo->GetShopById($value->shop_id);
                    if($shop->shop_id){
                        $shop_info['shop_id']        = $shop->shop_id;
                        $shop_info['shop_name']      = $shop->shop_name;
                        $shop_info['shop_logo']      = "http://kitchen.$domain/php/img_get.php?img=1&imgname=".$shop->shop_logo;
                        array_push($shop_list, $shop_info);
                    }
                }
            }
    }
     if(count($shop_list) == 0)
     {
         LogErr('the user have any one shop');
         return errcode::SHOP_NOT_WEIXIN;
     }

    $resp = (object)array(
        'account'       => $userinfo->phone,
        'phone'         => "400-0020-158",
        'shop_list'     => $shop_list,
    );
    return 0;
}

function LogQuery(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid = \Cache\Login::GetUserid();
    if(!$userid)
    {
        LogDebug('user not login');
        return errcode::USER_NOLOGIN;
    }
    $resp = (object)array(
        'userid'       => $userid,
    );
    return 0;
}

function Logout(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token = $_["token"];
    $redis = new \DaoRedis\Login();
    $info  = new \DaoRedis\LoginEntry();

    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->login    = 0;
    $info->userid   = 0;
    LogDebug($info);
    $ret = $redis->Save($info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("logout ok, token:{$token}");
    return 0;
}
LogDebug($_);

$ret = -1;
$resp = (object)array();
if(isset($_['login']))
{
    $ret = Login($resp);
}
else if(isset($_['login_wx']))
{
    $ret = LoginWx($resp);
}else if(isset($_['login_query']))
{
    $ret = LogQuery($resp);
}
else if(isset($_['logout']))
{
    $ret = Logout($resp);
}
else
{
    $ret = -1;
    LogErr("param err");
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
    'data' => $resp
);
if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}

?><?php /******************************以下为html代码******************************/?>
