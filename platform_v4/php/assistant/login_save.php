<?php
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/page_util.php");
require_once("mgo_user.php");
require_once("mgo_employee.php");
require_once("mgo_platformer.php");
require_once("mgo_shop.php");
require_once("mgo_weixin.php");
require_once("redis_login.php");
require_once("mgo_login.php");

use Pub\Vendor\Mongodb as VendorMgo;
function LoginSave(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $token        = $_["token"];
    $phone        = $_["phone"];
    $password_md5 = $_["password_md5"];
    //$password_md5 = md5($_["password_md5"]);//<<<<<<<<<<接口测试用的
    $weixin_id    = $_["weixin_id"];
    $type         = $_["type"];
    if(empty($phone))
    {
        LogErr("param err, phone empty");
        return errcode::USER_NAME_EMPTY;
    }
    $user_mgo     = new \DaoMongodb\User;

    $shop_userinfo = $user_mgo->QueryUser($phone, $phone, $password_md5, UserSrc::SHOP);

    if($shop_userinfo->userid)
    {
        $userid = $shop_userinfo->userid;
    }
    else
    {
        $pl_userinfo = $user_mgo->QueryUser($phone, $phone, $password_md5, UserSrc::PLATFORM);
        $userid      = $pl_userinfo->userid;
        $platform_id = PlatformID::ID;
        $pl_mgo      = new DaoMongodb\Platformer;
        $platformer  = $pl_mgo->QueryByUserId($userid, $platform_id);

        if(!$platformer->platformer_id)
        {
            LogErr("user is password err");
            return errcode::USER_PASSWD_ERR;
        }
    }

    if(!$userid)
    {
        LogErr("user is password err");
        return errcode::USER_PASSWD_ERR;
    }

    $time = time();
    if(!empty($weixin_id))
    {
        $weixin   = new \DaoMongodb\Weixin;
        if($platform_id)
        {
            $wx_user = $weixin->QueryByUserIdSrc($userid, Src::PLATFORM);
            $src = Src::PLATFORM;
        }
        else
        {
            $wx_user = $weixin->QueryByUserIdSrc($userid, Src::SHOP);
            $src     = Src::SHOP;
            $srctype = WXSrcType::PC;
        }

        if($wx_user->id && $wx_user->id != $weixin_id)
        {
            LogErr("WeixinUser err");
            return errcode::WEIXIN_NO_REBINDING;
        }
        $weixin_info = $weixin->QueryById($weixin_id);
        if($weixin_info->userid && $weixin_info->userid != $userid)
        {
            LogErr("WeixinUser err");
            return errcode::WEIXIN_NO_BINDING;
        }
        $wx_entry = new \DaoMongodb\WeixinEntry;
        $wx_entry->id          = $weixin_id;
        $wx_entry->userid      = $userid;
        $wx_entry->src         = $src;
        $wx_entry->srctype     = $srctype;
        $wx_entry->lastmodtime = $time;

        $ret = $weixin->Save($wx_entry);
        if(0 != $ret)
        {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
        $user_entry = new \DaoMongodb\UserEntry;
        $user_entry->userid    = $userid;
        $user_entry->is_weixin = 1;
        $save = $user_mgo->Save($user_entry);
        if(0 != $save)
        {
            LogErr("UserSave err");
            return errcode::DB_ERR;
        }
    }

    $mongodb = new \DaoMongodb\Login();
    $entry   = new \DaoMongodb\LoginEntry();

    $time = time();
    $entry->id          = \DaoRedis\Id::GenLoginId();
    $entry->userid      = $userid;
    $entry->ip          = $_SERVER['REMOTE_ADDR'];
    $entry->login_time  = $time;
    $entry->logout_time = 0;
    $entry->ctime       = $time;
    $entry->mtime       = $time;
    $ret = $mongodb->Save($entry);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
        'userid'      => $userid,
        'platform_id' => $platform_id,
        'type'        => $type
    );

    $redis = new \DaoRedis\Login();
    $login_info  = new \DaoRedis\LoginEntry();

    // 注：$login_info->key字段在终端初次提交时设置
    $login_info->token    = $token;
    $login_info->userid   = $userid;
    $login_info->login    = 1;
    //LogDebug($login_info);

    $ret = $redis->Save($login_info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }
    return 0;
}

function GetEmployee(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $userid        = $_["userid"];
    $platform_id   = $_["platform_id"];

    if(empty($userid))
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_list = [];
    if($platform_id)
    {
        $info['shop_id']   = PlShopId::ID;
        $info['shop_name'] = '赛领科技';
        $info['shop_logo'] = '23dbad1796ef1e365a393cdab4372966.png';
        $info['province']  = '广东省';
        $info['city']      = '深圳市';
        $info['area']      = '宝安区';
        $info['address']   = '创业二路创景1号';
        array_push($shop_list,$info);
    }
    else
    {
        $shop_mgo     = new DaoMongodb\Shop;
        $employee_mgo = new DaoMongodb\Employee;
        $shop_employee = $employee_mgo->GetEmployeeById($userid);
        foreach ($shop_employee as &$s)
        {
            $shop_info         = $shop_mgo->GetShopById($s->shop_id);
            $info['shop_id']   = $shop_info->shop_id;
            $info['shop_name'] = $shop_info->shop_name;
            $info['shop_logo'] = $shop_info->shop_logo;
            $info['province']  = $shop_info->province;
            $info['city']      = $shop_info->city;
            $info['area']      = $shop_info->area;
            $info['address']   = $shop_info->address;

            array_push($shop_list,$info);
        }
    }

    $resp = (object)array(
        'shop_list' => $shop_list
    );
    return 0;
}


function GetShop(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $userid  = $_["userid"];
    $shop_id = $_["shop_id"];
    $token   = $_["token"];

    if(!$userid || !$shop_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_info = (object)array();
    if(PlShopId::ID == $shop_id)
    {
        $platform_id = PlatformID::ID;
        $pl_mgo = new DaoMongodb\Platformer;
        $platformer = $pl_mgo->QueryByUserId($userid, $platform_id);
        if($platformer->is_freeze == \EmployeeFreeze::FREEZE && $platformer->is_admin != 1)
        {
            LogErr("employee freeze");
            return errcode::EMPLOYEE_IS_FREEZE;
        }

        $shop_info->shop_id   = PlShopId::ID;
        $shop_info->shop_name = '赛领科技';
        $shop_info->shop_logo = '23dbad1796ef1e365a393cdab4372966.png';
    }
    else
    {
        $shop = new DaoMongodb\Shop;
        $shopinfo = $shop->GetShopById($shop_id);
        //店铺是否被冻结
        if($shopinfo->is_freeze == \EmployeeFreeze::FREEZE)
        {
            LogErr("shop freeze");
            return errcode::SHOP_IS_FREEZE;
        }
        $employee      = new DaoMongodb\Employee;
        $employee_one = $employee->QueryByUserId($shop_id,$userid);
        if($employee_one->is_freeze == IsFreeze::YES && $employee_one->is_admin != 1)
        {
            LogErr("employee is freeze");
            return errcode::EMPLOYEE_IS_FREEZE;
        }
        $shop_info->shop_id   = $shopinfo->shop_id;
        $shop_info->shop_name = $shopinfo->shop_name;
        $shop_info->shop_logo = $shopinfo->shop_logo;
    }

    $redis = new \DaoRedis\Login();
    $login_info  = new \DaoRedis\LoginEntry();

    // 注：$login_info->key字段在终端初次提交时设置
    $login_info->token    = $token;
    $login_info->userid   = $userid;
    $login_info->shop_id  = $shop_id;
    $login_info->login    = 1;
    //LogDebug($login_info);

    $ret = $redis->Save($login_info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
        'shop_info'  => $shop_info,
        'platformer' => $platformer,
        'employee'   => $employee_one
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
    $token         = $_["token"];
    $userid        = $_["userid"];
    $platform_id   = $_["platform_id"];
    if(!$token || !$userid)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $weixin = new \DaoMongodb\Weixin;
    if($platform_id)
    {
        $wx_user = $weixin->QueryByUserIdSrc($userid, Src::PLATFORM);
    }
    else
    {
        $wx_user = $weixin->QueryByUserIdSrc($userid, Src::SHOP);
    }
    if(!$wx_user->id)
    {
        LogErr("wx user err");
        return errcode::PARAM_ERR;
    }
    $wx_entry = new \DaoMongodb\WeixinEntry;
    $wx_entry->id          = $wx_user->id;
    $wx_entry->userid      = 0;
    $wx_entry->lastmodtime = time();
    $ret = $weixin->Save($wx_entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $user_mgo = new \DaoMongodb\User;
    $user_entry = new \DaoMongodb\UserEntry;
    $user_entry->userid    = $userid;
    $user_entry->is_weixin = 0;
    $save = $user_mgo->Save($user_entry);
    if(0 != $save)
    {
        LogErr("UserSave err");
        return errcode::DB_ERR;
    }
    $redis = new \DaoRedis\Login();
    $info  = new \DaoRedis\LoginEntry();

    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->login    = 0;
    $info->userid   = 0;
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

$ret  = -1;
$resp = (object)array();

if(isset($_['login_save']))
{
    $ret = LoginSave($resp);
}
elseif(isset($_['get_employee']))
{
    $ret = GetEmployee($resp);
}
elseif(isset($_['get_shop']))
{
    $ret = GetShop($resp);
}
else if(isset($_['logout']))
{
    $ret = Logout($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);
