<?php
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once("current_dir_env.php");
require_once "WxUtil.php";
require_once("cfg.php");
require_once("cache.php");
require_once("redis_login.php");
require_once("page_util.php");
require_once("const.php");
require_once("mgo_employee.php");
require_once("mgo_resources.php");
require_once("mgo_shop.php");
require_once("mgo_term_binding.php");
require_once("/www/public.sailing.com/php/mgo_selfhelp.php");
use \Pub\Mongodb as Mgo;
$_ = $_REQUEST;

function login($token, &$userid, &$selfhelp_id, &$srctype)
{
    if(!$token)
    {
        LogErr("token err");
        return errcode::PARAM_ERR;
    }
    $req   = \Pub\Wx\Util::GetOpenid();
    $openid = $req->openid;
    LogDebug($openid);
    $weixin = \Cache\Weixin::Get($openid, Src::SHOP);
    $userid = $weixin->userid;
    if(!$userid)
    {
        LogErr("WeixinUser err");
        return errcode::WEIXIN_NO_LOGIN;
    }
    if($selfhelp_id)
    {
        $srctype = NewSrcType::SELFHELP;
    }
    $selfhelp      = new Mgo\Selfhelp;
    $employee      = new DaoMongodb\Employee;
    $resources     = new Mgo\Resources;
    $term          =  new Mgo\TermBinding;
    $selfhelp_user = $selfhelp->GetByUserId($userid);

    $employee_user = $employee->GetEmployeeById($userid);


    if(count($employee_user) == 1)
    {
        $shop     = new \DaoMongodb\Shop;
        $shopinfo = $shop->GetShopById($employee_user[0]->shop_id);
        //店铺是否被冻结
        if($shopinfo->is_freeze == \EmployeeFreeze::FREEZE)
        {
            LogErr("shop freeze");
            return errcode::SHOP_IS_FREEZE;
        }
        $employee_one = $employee->QueryByUserId($employee_user[0]->shop_id,$userid);
        if($employee_one->is_admin != 1)
        {
            if($employee_one->is_freeze == IsFreeze::YES)
            {
                LogErr("employee is freeze");
                return errcode::EMPLOYEE_NOT_LOGIN;
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

    }
    elseif(count($employee_user) == 0)
    {
        LogErr('the user no any one shop');
        return errcode::SHOP_ID_NOT;
    }
    if($selfhelp_user->userid)
    {
        $shop_id       = $selfhelp_user->shop_id;

        $employee_info = $employee->QueryByUserId($shop_id, $userid);

        if($employee_info->is_freeze == EmployeeFreeze::FREEZE)
        {
            LogErr("employee is freeze");
            return errcode::EMPLOYEE_NOT_LOGIN;
        }
    }

    if($selfhelp_id)
    {
        $selfhelp_info = $selfhelp->GetExampleById($selfhelp_id);

        if($selfhelp_info->userid)//机子绑定的时候才进行下面步骤
        {
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
                if($selfhelp_info->userid != $userid)
                {
                    LogErr("The user is binding");
                    return errcode::SELFHELP_IS_BINDING;
                }
            }

            if(($employee_info->authorize & 16) == 0 && $employee_info->is_admin != 1)
            {
                LogErr("employee authorize:".$employee_info->authorize);
                return errcode::EMPLOYEE_NOT_LOGIN;
            }
        }

    }
    $redis = new \DaoRedis\Login();
    $info  = new \DaoRedis\LoginEntry();

    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->userid   = $userid;
    $info->username = '';
    $info->shop_id  = '';
    $info->login    = 1;
    LogDebug($info);

    $ret = $redis->Save($info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        // echo "系统忙...";exit(0);
    }
    return 0;
}

$token       = $_['token'];
$userid      = null;
$selfhelp_id = $_['selfhelp_id'];
$srctype     = $_['srctype'];
$ret = login($token, $userid, $selfhelp_id, $srctype);
if(0!= $ret)
{
    $data = 10;
    if($ret == errcode::WEIXIN_NO_LOGIN)
    {
        $data = 2;
    }
    if($ret == errcode::EMPLOYEE_NOT_LOGIN)
    {
        $data = 8;
    }
    if($ret == errcode::SHOP_ID_NOT)
    {
        $data = 9;
    }
    if($ret == errcode::SELFHELP_IS_BINDING || $ret == errcode::USER_SELFHELP_BINDING)
    {
        $data = 7;
    }
    require("wx_codetip.php");
    exit(0);
}
$tokendata = \Cache\Login::Get($token); //存缓存

// 发送能知到服务端
$url = Cfg::instance()->orderingsrv->webserver_url;

// 正常情况下，使用下面的（这里为调试兼容，暂时保留）
$ret_json_str = PageUtil::HttpPostJsonEncData(
    $url,
    [
        'name' => "cmd_publish",
        'param' => json_encode([
            'opr'   => "once",
            'param' => [
                'topic' => "login_qrcode@$token",
                'data' => [
                    'info' =>[
                        'ret' => $ret,
                        'userid' => $userid
                    ]
                ]
            ],
        ])
    ]
);
LogDebug("[$ret_json_str]");
// 发退登录通知
$ret_json_str = PageUtil::HttpPostJsonEncData(
    $url,
    [
        'name' => "cmd_publish",
        'param' => json_encode([
            'opr'   => "login_qrcode",
            'param' => [
                'token' => "$token",
                'data' => [
                    'info' =>[
                        'ret'    => $ret,
                        'userid' => $userid
                    ]
                ]
            ],
        ])
    ]
);
LogDebug("[$ret_json_str]");
$ret_json_obj = json_decode($ret_json_str);
if(0 != $ret_json_obj->ret)
{
    $data = 10;
}
else
{
    $data = 1;
}
require("wx_codetip.php");
?>

