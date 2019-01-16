<?php
require_once("current_dir_env.php");
require_once("mgo_login.php");
require_once("mgo_user.php");
require_once("redis_login.php");
require_once("redis_id.php");
require_once("mgo_employee.php");
require_once("mgo_position.php");
require_once("mgo_department.php");
require_once("/www/public.sailing.com/php/mgo_selfhelp.php");
require_once("mgo_user.php");
require_once("ChuanglanSmsHelper/ChuanglanSmsApi.php");
require_once("/www/public.sailing.com/php/send_sms.php");
require_once("/www/wx.jzzwlcm.com/WxUtil.php");
require_once("mgo_env.php");
use \Pub\Mongodb as Mgo;
// require_once("../../public.sailing.com/php/page_util.php");
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
    $selfhelp_id   = $_['selfhelp_id'];
    $phone         = $_["phone"];
    $password_md5  = $_["password_md5"];

    //$password_md5 = md5($_["password_md5"]);//<<<<<<<<<<接口测试用的a

    $true_phone = PageUtil::GetPhone($phone);
    if(!$true_phone)
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }

    $user      = new \DaoMongodb\User;
    $selfhelp  = new Mgo\Selfhelp;
    $employee  = new \DaoMongodb\Employee;
    $userinfo = $user->QueryUser($phone, $phone, $password_md5, UserSrc::SHOP);
    if(!$userinfo->userid)
    {
        LogErr("The user is empty");
        return errcode::USER_NO_EXIST;
    }

    $selfhelp_user = $selfhelp->GetByUserId($userinfo->userid);
    $selfhelp_info = $selfhelp->GetExampleById($selfhelp_id);
    //先判断该登陆用户是否绑定账号
    if($selfhelp_user->selfhelp_id)
    {
        if($selfhelp_user->selfhelp_id != $selfhelp_id)
        {
            LogErr("The user is binding");
            return errcode::USER_SELFHELP_BINDING;
        }
    }
    //再判断该自主点餐机是否绑定账号
    if($selfhelp_info->userid)
    {
        if($selfhelp_info->userid != $userinfo->userid)
        {
            LogErr("The user is binding");
            return errcode::SELFHELP_IS_BINDING;
        }
    }

    if(null == $userinfo)
    {
        LogErr("user is password err");
        return errcode::USER_PASSWD_ERR;
    }
   //判断用户是否有点餐机登录权限
    if($selfhelp_info->shop_id){
        $employee_info  = $employee->QueryByUserId($selfhelp_info->shop_id,$userinfo->userid);
         if($employee_info->is_freeze == EmployeeFreeze::FREEZE)
         {
             LogErr("employee is freeze");
             return errcode::EMPLOYEE_NOT_LOGIN;
         }
        if(($employee_info->authorize & 16) == 0 && $employee_info->is_admin != 1)
        {
            LogErr("employee authorize:".$employee_info->authorize);
            return errcode::EMPLOYEE_NOT_LOGIN;
        }
    }

   //LogDebug($userinfo);
    $ret = LoginSave($userinfo, $token, $resp);
    if(0 != $ret)
    {
        LogErr("Login err");
        return $ret;
    }
    LogInfo('ok');
    return 0;
}

function LoginWx(&$resp)
{
    $_ = $GLOBALS["_"];
    //LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token         = $_["token"];
    $userid        = $_['user_id'];
    $selfhelp_id   = $_['selfhelp_id'];
    //LogDebug($_);
    if(!$userid)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userinfo = \Cache\UsernInfo::Get($userid);

    $selfhelp      = new Mgo\Selfhelp;
    $employee      = new \DaoMongodb\Employee;
    $selfhelp_user = $selfhelp->GetByUserId($userinfo->userid);
    $selfhelp_info = $selfhelp->GetExampleById($selfhelp_id);
    //先判断该登陆用户是否绑定账号
    if($selfhelp_user->selfhelp_id)
    {
        if($selfhelp_user->selfhelp_id != $selfhelp_id)
        {
            LogErr("The user is binding");
            return errcode::USER_SELFHELP_BINDING;
        }
    }
    //再判断该自主点餐机是否绑定账号
    if($selfhelp_info->userid)
    {
        if($selfhelp_info->userid != $userinfo->userid)
        {
            LogErr("The user is binding");
            return errcode::SELFHELP_IS_BINDING;
        }
    }
    //判断用户是否有点餐机登录权限
    if($selfhelp_info->shop_id){
        $employee_info  = $employee->QueryByUserId($selfhelp_info->shop_id,$userinfo->userid);
        if($employee_info->is_freeze == EmployeeFreeze::FREEZE)
        {
            LogErr("employee is freeze");
            return errcode::EMPLOYEE_NOT_LOGIN;
        }
        if(($employee_info->authorize & 16) == 0 && $employee_info->is_admin != 1)
        {
            LogErr("employee authorize:".$employee_info->authorize);
            return errcode::EMPLOYEE_NOT_LOGIN;
        }
    }
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
    foreach ($employee as $key => $value) {

            if((($value->authorize & AUTHORIZE::SELFHELP) != 0 && $value->is_freeze == EmployeeFreeze::NOFREEZE) || $value->is_admin == 1)
            {
                if($value->shop_id){
                    $shop = $mgo->GetShopById($value->shop_id);
                    if($shop->shop_id){
                        $shop_info['shop_id']        = $shop->shop_id;
                        $shop_info['shop_name']      = $shop->shop_name;
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
    $selfhelp      = new Mgo\Selfhelp;
    $selfhelp_info = $selfhelp->GetByUserId($userinfo->userid);
    //LogDebug($userinfo->userid);
    if(!$selfhelp_info->shop_id)
    {
        $shop_id = null;
    }else{
        $shop_id = $selfhelp_info->shop_id;
    }

    $resp = (object)array(
        'account'       => $userinfo->phone,
        'shop_id'       => $shop_id,//自助点餐机已绑定的店铺id
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
//发送图形手机验证码
function GetCoke(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token = $_['token'];
    $phone = $_['phone'];
    $name  = $_['name'];
    $mgo   = new \DaoMongodb\User;
    // 查找手机号码
    $ret = $mgo->QueryByPhone($phone,UserSrc::SHOP);
    //LogDebug($ret);
    switch ($name)
    {
        case 'forgetPwd':
            if(!$ret->phone){
                return errcode::USER_NOT_ZC;
            }
            break;
        case 'registrate':
            if ($ret->phone) {
                return errcode::PHONE_IS_EXIST;
            }
            break;
        default:
            return errcode::PARAM_ERR;
            break;
    }
    $page_code  = strtolower($_['page_code']);
    $db         = new \DaoRedis\Login;
    $redis      = $db->Get($token);
    $radis_code = $redis->page_code;
    //验证验证码
    if ($radis_code != $page_code)
    {
        LogErr("coke err");
        return errcode::COKE_ERR;
    }

    if (!preg_match('/^1[34578]\d{9}$/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }
    $code   = mt_rand(100000, 999999);
    Sms::GetSms($phone,$code);//发送手机验证码
    $redis            = new \DaoRedis\Login();
    $data             = new \DaoRedis\LoginEntry();
    $data->phone      = $phone;
    $data->token      = $token;
    $data->phone_code = $code;
    $data->code_time  = time() + 5 * 60 * 1000;
    LogDebug($data);
    $redis->Save($data);
    $resp = (object)[
        //'phone_code' => $code,
    ];
    return 0;
}
//无图形验证码发送手机验证码
function GetPhoneCode(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token      = $_['token'];
    $phone      = $_['phone'];
    $user       = new \DaoMongodb\User;
    if(!$_['srctype'])
    {
        $userinfo   = $user->QueryByPhone($phone, UserSrc::SHOP);
        if(!$userinfo->phone)
        {
            LogDebug("not user message");
            return errcode::USER_NOT_ZC;
        }
    }
    if (!preg_match('/^1[34578]\d{9}$/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }
    $code   = mt_rand(100000, 999999);
    Sms::GetSms($phone,$code);//发送手机验证码
    $redis            = new \DaoRedis\Login();
    $data             = new \DaoRedis\LoginEntry();
    $data->phone      = $phone;
    $data->token      = $token;
    $data->phone_code = $code;
    $data->code_time  = time() + 5 * 60;
    //LogDebug($data);
    $redis->Save($data);
    $resp = (object)[
        //'phone_code' => $code,
    ];
    return 0;
}


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
}elseif (isset($_['get_coke'])) {

    $ret = GetCoke($resp);
}elseif (isset($_['get_phone_code'])){

    $ret = GetPhoneCode($resp);
}
else
{
    $ret = -1;
    LogErr("param err");
}

/////////////////// 这里改为下面的 ///////////////////
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


///////////////// 换为这里 /////////////////////
// \Pub\PageUtil::HtmlOut($ret, $resp);

?><?php /******************************以下为html代码******************************/?>
