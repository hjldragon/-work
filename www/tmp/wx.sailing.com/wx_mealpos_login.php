<?php
declare(encoding='UTF-8');
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
require_once("mgo_shop.php");
require_once("mgo_weixin.php");
require_once("weixin_save.php");
require_once("mgo_login.php");
require_once("mgo_user.php");
require_once("redis_employee.php");
use \Pub\Mongodb as Mgo;

$_ = $_REQUEST;
$token       = $_['token'];
$shop_id     = $_['shop_id'];
LogDebug($token);
if(!$shop_id || !$token)
{
    LogErr("papam err");
    echo "系统忙...";exit(0);
}
function MpLogin($token, $shop_id)
{
    $req    = \Pub\Wx\Util::GetOpenid();
    $openid = $req->openid;
    $access_token = $req->access_token;
    LogDebug($req);
    $mgo = new \DaoMongodb\Weixin;
    $weixin = $mgo->QueryByOpenId($openid, Src::SHOP, WxSrctype::PC);
    LogDebug($weixin);
    $user   = \Pub\Wx\Util::GetUserInfo($access_token,$openid);
    $wx_id = null;
    $user->id        = $weixin->id;
    $user->src       = Src::SHOP;
    $user->WxSrctype = WxSrctype::PC;
    $ret = WeixinSave($user, $wx_id);
    //LogDebug($ret);
    if(0 != $ret){
        LogErr("WeixinSave err");
        echo "系统忙...";exit(0);
    }
    $main_domain = Cfg::instance()->GetMainDomain();

    $info = $mgo->QueryById($wx_id);
    $userid = $info->userid;
    if(!$userid)
    {
        $resp = (object)array(
            'wx_id'      => $wx_id,
            'headimgurl' => $info->headimgurl,
            'nickname'   => $info->nickname,
            'shop_id'    => $shop_id
        );
        $url = "http://mealpos.$main_domain/index.html#/login?".http_build_query($resp);
        return $url;
    }


    $employee      = new DaoMongodb\Employee;
    // $resources     = new Mgo\Resources;
    // $term          = new Mgo\TermBinding;

    $employee_one = $employee->QueryByUserId($shop_id,$userid);
    $resp = (object)array();
    if(!$employee_one->employee_id)
    {
        $resp = (object)array(
            'wx_id'      => $wx_id,
            'headimgurl' => $info->headimgurl,
            'nickname'   => $info->nickname,
            'shop_id'    => $shop_id
        );
        $url = "http://mealpos.$main_domain/index.html#/login?".http_build_query($resp);
        return $url;
    }
    $user = new \DaoMongodb\User;
    $userinfo = $user->QueryById($employee_one->userid);
    $shop     = new \DaoMongodb\Shop;
    $shopinfo = $shop->GetShopById($shop_id);
    //店铺是否被冻结
    if($shopinfo->is_freeze == \EmployeeFreeze::FREEZE)
    {
        LogErr("shop freeze");
        //return errcode::SHOP_IS_FREEZE;
        $resp = (object)array(
                'wx_id'      => $wx_id,
                'headimgurl' => $info->headimgurl,
                'nickname'   => $info->nickname,
                'shop_id'    => $shop_id,
                'phone'      => $userinfo->phone,
                'ret'        => errcode::SHOP_IS_FREEZE
            );
            $url = "http://mealpos.$main_domain/index.html#/login?".http_build_query($resp);
            return $url;
    }

    if($employee_one->is_admin != 1)
    {
        if($employee_one->is_freeze == IsFreeze::YES)
        {
            LogErr("employee is freeze");
            $resp = (object)array(
                'wx_id'      => $wx_id,
                'headimgurl' => $info->headimgurl,
                'nickname'   => $info->nickname,
                'shop_id'    => $shop_id,
                'phone'      => $userinfo->phone,
                'ret'        => errcode::EMPLOYEE_IS_FREEZE
            );
            $url = "http://mealpos.$main_domain/index.html#/login?".http_build_query($resp);
            return $url;
        }
    }
    $redis_employee = new \DaoRedis\Employee();
    $employee_info = $redis_employee->Get($employee_one->employee_id);
    LogDebug($employee_info);
    if($employee_info->token && $employee_info->token != $token)
    {
        LogErr("Login err");
        $resp = (object)array(
            'wx_id'      => $wx_id,
            'headimgurl' => $info->headimgurl,
            'nickname'   => $info->nickname,
            'shop_id'    => $shop_id,
            'phone'      => $userinfo->phone,
            'ret'        => errcode::USER_LOGINED_EXIST
        );
        $url = "http://mealpos.$main_domain/index.html#/login?".http_build_query($resp);
        return $url;
    }
    $employee  = new \DaoRedis\EmployeeEntry();

    $employee->token       = $token;
    $employee->employee_id = $employee_one->employee_id;
    LogDebug($employee);
    $ret = $redis_employee->Save($employee);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        echo "系统忙...";exit(0);
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
        echo "系统忙...";exit(0);
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
        echo "系统忙...";exit(0);
    }
    $resp = (object)array(
            'userid'      => $userid,
            'shop_id'     => $shop_id,
            'employee_id' => $employee_one->employee_id,
            'phone'       => $userinfo->phone
        );
    $url = "http://mealpos.$main_domain/index.html#/good?".http_build_query($resp);
    return $url;
}

$url = MpLogin($token, $shop_id);

header("HTTP/1.1 302 See Other");
header("Location: $url");
?>

