<?php
/*
 * 客户信息保存类
 */
require_once("current_dir_env.php");
require_once("cache.php");
require_once("db_pool.php");
require_once("redis_login.php");
require_once("mgo_customer.php");
require_once("mgo_employee.php");
require_once("ChuanglanSmsHelper/ChuanglanSmsApi.php");
//Permission::PageCheck();
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
    
    $customer_id           = $_['customer_id'];
    $is_vip                = $_['is_vip'];
    $remark                = $_['remark'];
    $mongodb               = new \DaoMongodb\Customer;
    $entry                 = new \DaoMongodb\CustomerEntry;
    $entry->customer_id    = $customer_id;
    $entry->is_vip         = $is_vip;
    $entry->mtime          = time();
    $entry->remark         = $remark;
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
//发送图形手机验证码
function GetCoke(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token      = $_['token'];
    $phone      = $_['phone'];
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

    if (!preg_match('/^1([0-9]{9})/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }

        $code    = 654321;//<<<<<<<<<<<<<<<<<<<<<<<<<<<<调试写死数据
    //$code = mt_rand(100000, 999999);
//    $clapi  = new ChuanglanSmsApi();
//    $msg    = '【赛领新吃货】尊敬的用户，您本次的验证码为' . $code . '有效期5分钟。打死不要将内容告诉其他人！';
//    $result = $clapi->sendSMS($phone, $msg);
//    LogDebug($result);
//    if (!is_null(json_decode($result)))
//    {
//        $output = json_decode($result, true);
//        if (isset($output['code']) && $output['code'] == '0')
//        {
//            LogDebug('短信发送成功！');
//        } else {
//            return $output['errorMsg'] . errcode::PHONE_SEND_FAIL;
//        }
//    }

    $redis            = new \DaoRedis\Login();
    $data             = new \DaoRedis\LoginEntry();
    $data->phone      = $phone;
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
    if (!preg_match('/^1([0-9]{9})/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }

    $code    = 654321;//<<<<<<<<<<<<<<<<<<<<<<<<<<<<调试写死数据
    //$code = mt_rand(100000, 999999);
//    $clapi  = new ChuanglanSmsApi();
//    $msg    = '【赛领新吃货】尊敬的用户，您本次的验证码为' . $code . '有效期5分钟。打死不要将内容告诉其他人！';
//    $result = $clapi->sendSMS($phone, $msg);
//    LogDebug($result);
//    if (!is_null(json_decode($result)))
//    {
//        $output = json_decode($result, true);
//        if (isset($output['code']) && $output['code'] == '0')
//        {
//            LogDebug('短信发送成功！');
//        } else {
//            return $output['errorMsg'] . errcode::PHONE_SEND_FAIL;
//        }
//    }

    $redis            = new \DaoRedis\Login();
    $data             = new \DaoRedis\LoginEntry();
    $data->phone      = $phone;
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
    $ret = SaveCustomer($resp);
}elseif (isset($_['get_coke'])) {

    $ret = GetCoke($resp);
}elseif (isset($_['del_customer']) || isset($_['del']))
{   
    $ret = DeleteCustomer($resp);
}elseif (isset($_['get_phone_code'])) {

    $ret = GetPhoneCode($resp);
}
else
{
    LogErr("param err");
    $ret = errcode::PARAM_ERR;
}
$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>
