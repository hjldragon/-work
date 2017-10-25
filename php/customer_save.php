<?php
/*
 * 客户信息保存类
 */
require_once("current_dir_env.php");
require_once("cache.php");
require_once("db_pool.php");
require_once("redis_login.php");
require_once("mgo_customer.php");

Permission::PageCheck();
//$_=$_REQUEST;
//保存客户信息
function SaveCustomer(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $openid                = $_['openid'];
    $customer_id           = $_['customer_id'];
    $phone                 = $_['phone'];
    $is_vip                = $_['is_vip'];
    $customer_name         = $_['customer_name'];
    $weixin_account        = $_['weixin_account'];
    $vip_level             = $_['vip_level'];
    $userid                = $_['userid'];
    //获取店铺id<<<<<<<<现在在创建数据
    $shop_id               = 4; //\Cache\Login::GetShopId();
    //链接mongodb数据库
    $mongodb               = new \DaoMongodb\Customer;
    $entry                 = new \DaoMongodb\CustomerEntry;
    $entry->customer_id    = $customer_id;
    $entry->phone          = $phone;
    $entry->is_vip         = $is_vip;
    $entry->shop_id        = $shop_id;
    $entry->customer_name  = $customer_name;
    $entry->openid         = $openid;
    $entry->ctime          = time();
    $entry->delete         = 0;
    $entry->weixin_account = $weixin_account;
    $entry->vip_level      = $vip_level;
    $entry->userid         = $userid;
    $ret                   = $mongodb->Save($entry);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array();
    LogInfo("save ok");
    LogDebug($resp);
    return 0;
}
//前台编辑用户信息,及修改用户信息
function SaveUserInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $mgo   = new \DaoMongodb\User;
    $entry = new \DaoMongodb\UserEntry;
    //获取客户id
    $cutomer_id    = $_['customer_id'];
    $usernick      = $_['usernick'];
    //$user_avater = $_['user_avater'];
    $phone         = $_['phone'];
    $sex           = $_['sex'];
    if($cutomer_id){
        $cutomer_info = Cache\Customer::Get($cutomer_id);
        $userid       = $cutomer_info->userid;
    }
    //昵称
    if($usernick){
        $entry->usernick = $usernick;
    }
    //性别
    if($sex){
        $entry->sex = $sex;
    }
    //手机验证码
    if($phone && $userid){
        $code       = strtolower($_['code']);
        $radis      = \Cache\Login::Get($token);
        $phone_code = $radis->phone_code;
        $code_time  = $radis->code_time;
        if($radis_coke != $coke || $code_time < time()){
            LogErr("coke err");
            return errcode::COKE_ERR;
        }
        $entry->phone = $phone;
    }
    if (!$userid)
    {
        $userid = \DaoRedis\Id::GenUserId();
        $entry->ctime = time();
    }

    $entry->userid        = $userid;
    //$entry->user_avater = $user_avater;
    $entry->lastmodtime   = time();
    $entry->delete        = 0;
   
    $ret = $mgo->Save($entry);

    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(//'userid' => $entry->userid
    );
    LogInfo("save ok");
    return 0;
}
//删除用户信息
function DeleteCustomer(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
   
    $customer_id_list = json_decode($_['customer_id_list']);
    $mongodb = new \DaoMongodb\Customer;
    $ret = $mongodb->BatchDeleteById($customer_id_list);
    
    if(0 != $ret)
    {
        LogErr("Delete err");
        return errcode::SYS_ERR;
    }
    
    $resp = (object)array(
    );
    LogInfo("delete ok");
    return 0;
}
//发送手机验证码
function GetCoke(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token = $_['token'];
    $phone = $_['phone'];
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

    if(!preg_match('/^1([0-9]{9})/',$phone)){
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }
    $mgo  = new \DaoMongodb\User;
    $info = $mgo->QueryByPhone($phone);
    if($info->phone)
    {
        LogErr("phone is exist");
        return errcode::PHONE_IS_EXIST;
    }
    //$code  = rand(100000,999999);
      $code    = 654321;//<<<<<<<<<<<<<<<<<<<<<<<<<<<<调试写死数据
//    $right = Cfg::SendCheckCode($code,$phone);
//    if($right != 0)
//    {
//        return errcode::PHONE_SEND_FAIL;
//    }

    $redis = new \DaoRedis\Login();
    $data  = new \DaoRedis\LoginEntry();
    $data->token      = $token;
    $data->phone_code = $code;
    $data->code_time  = time()+5*60*1000;
    LogDebug($data);
    $redis->Save($data);
    $resp = (object)array(
        'phone_code' => $code
    );
    return 0;
}
//发送手机解绑验证码
function GetUnBindCode(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token      = $_['token'];
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
    $shop_id = \Cache\Login::GetShopId();
    $userid  = \Cache\Login::GetUserid();

    if (!$userid || !$shop_id)
    {
        return errcode::USER_NOLOGIN;
    }
    //获取用户已绑定的手机号码
    $mgo      = new \DaoMongodb\User;
    $userinfo = $mgo->QueryById($userid);
    $phone   = $userinfo->phone;
    $code    = 654321;//<<<<<<<<<<<<<<<<<<<<<<<<<<<<调试写死数据
//    $code  = rand(100000, 999999);
//    $right = Cfg::SendCheckCode($code, $phone);
//    if ($right != 0)
//    {
//        return errcode::PHONE_SEND_FAIL;
//    }

    $redis            = new \DaoRedis\Login();
    $data             = new \DaoRedis\LoginEntry();
    $data->token      = $token;
    $data->phone_code = $code;
    $data->code_time  = time() + 5 * 60 * 1000;
    LogDebug($data);

    $redis->Save($data);
    $resp = (object)[
        'phone_code' => $code,
    ];
    return 0;
}

$ret = -1;
$resp = (object)array();
if (isset($_['customer_save']))
{
    $ret = SaveUserInfo($resp);
}elseif (isset($_['get_coke'])) {

    $ret = GetCoke($resp);
}elseif (isset($_['get_unbind_code'])) {

    $ret = GetUnBindCode($resp);
}elseif (isset($_['del_customer']) || isset($_['del']))
{   
    $ret = DeleteCustomer($resp);
}
else
{
    LogErr("param err");
    $ret = errcode::PARAM_ERR;
}
$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>
