<?php
/*
 * 客户信息保存类
 */
require_once("current_dir_env.php");
require_once("cache.php");
require_once("db_pool.php");
require_once("redis_login.php");
require_once("mgo_customer.php");
require_once("mgo_user.php");
require_once("/www/public.sailing.com/php/send_sms.php");
//Permission::PageCheck();

//保存顾客信息
function CustomerSave(&$info)
{
    if (!$info)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $customer_id     = $info->customer_id;
    $phone           = $info->phone;
    $usernick        = $info->usernick;
    $userid          = $info->userid;
    $shop_id         = $info->shop_id;
    $sex             = $info->sex;
    if(!$customer_id){
        $customer_id = \DaoRedis\Id::GenCustomerId();
    }
    //链接mongodb数据库
    $mongodb               = new \DaoMongodb\Customer;
    $entry                 = new \DaoMongodb\CustomerEntry;
    $entry->customer_id    = $customer_id;
    $entry->phone          = $phone;
    $entry->is_vip         = 0;
    $entry->shop_id        = $shop_id;
    $entry->sex            = $sex;
    $entry->usernick       = $usernick;
    $entry->ctime          = time();
    $entry->delete         = 0;
    $entry->userid         = $userid;
    $ret                   = $mongodb->Save($entry);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $info->customer_id = $customer_id;
    LogInfo("save ok");

    return 0;
}

//保存用户信息
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
    $entry->is_pad_customer= 0;    //不是pad端用户
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
    $customer_id = $_['customer_id'];
    $usernick    = $_['usernick'];
    $user_avater = $_['user_avater'];
    $birthday    = $_['birthday'];
    $phone       = $_['phone'];
    $sex         = $_['sex'];

   
    $customer_info = Cache\Customer::Get($customer_id);
    $userid        = $customer_info->userid;
   
    //手机验证码
    if($phone){
        $token = $_['token'];
        $phone = $_['phone'];
        if(!preg_match('/^\d{11}$/', $phone)){
            LogErr("phone err");
            return errcode::PHONE_ERR;
        }
//        $user     = new \DaoMongodb\User;
//        $userinfo = $user->QueryByPhone($phone);
//        if($userinfo->phone){
//            return errcode::PHONE_IS_EXIST;
//        }
        $phone_code = $_['code'];//手机验证码
        $result     = PageUtil::VerifyPhoneCode($token, $phone, $phone_code);
        //验证手机结果
        if($result != 0){
            LogDebug($result);
            return $result;
        }
    }
    //如果没有用户id就新建用户id
    // if (!$userid)
    // {
    //     $userid       = \DaoRedis\Id::GenUserId();
    //     $entry->ctime = time();
    // }

    $entry->userid      = $userid;
    $entry->sex         = $sex;
    $entry->phone       = $phone;
    $entry->usernick    = $usernick;
    $entry->user_avater = $user_avater;
    $entry->birthday    = $birthday;
    $ret = $mgo->Save($entry);

    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $cus_mgo = new \DaoMongodb\Customer;
    $info = new \DaoMongodb\CustomerEntry;

    $info->userid   = $userid;
    $info->usernick = $usernick;
    $info->phone    = $phone;
    $info->sex      = $sex;
    $ret = $cus_mgo->UserSave($info);

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

function GetCoke(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token = $_['token'];
    $phone = $_['phone'];
    if(!preg_match('/^1([0-9]{9})/',$phone)){
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }
    $mgo  = new \DaoMongodb\User;
    $info = $mgo->QueryByPhone($phone);
    if($info->phone){
        LogErr("phone is exist");
        return errcode::PHONE_IS_EXIST;
    }
    $code  = rand(100000,999999);
    $redis = new \DaoRedis\Login();
    $data  = new \DaoRedis\LoginEntry();

    $data->token      = $token;
    $data->phone_code = $code;
    $data->code_time  = time()+5*60*1000;
    LogDebug($data);

    $redis->Save($data);
    $resp = (object)array(
        'code' => $code
    );
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
    if (!preg_match('/^\d{11}$/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }

    $code    = mt_rand(100000, 999999);
    //执行成功后发送短信
    $msg     = '尊敬的用户，您本次的验证码为'.$code.'，有效期2分钟，请确认是本人操作，勿将短信内容告诉他人！';
    $msg_ret = Util::SmsSend($phone, $msg);
    if(0 != $msg_ret)
    {
        LogErr("phone send err".$msg_ret);
    }
    $redis            = new \DaoRedis\Login();
    $data             = new \DaoRedis\LoginEntry();
    $data->phone      = $phone;
    $data->token      = $token;
    $data->phone_code = $code;
    $data->code_time  = time() + 2*60;

    $ret = $redis->Save($data);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    return 0;
}
$ret = -1;
$resp = (object)array();
if (isset($_['customer_save']))
{
    $ret = SaveUserInfo($resp);
}elseif (isset($_['get_coke'])) {

    $ret = GetPhoneCode($resp);
}else if(isset($_['del_customer']) || isset($_['del']))
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
