<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 *
 */
require_once("current_dir_env.php");
require_once("mgo_user.php");
require_once("mgo_customer.php");
require_once("cache.php");
require_once("db_pool.php");
require_once("redis_public.php");


function SaveUserInfo(&$resp)
{
    $_ = $GLOBALS["_"];

    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    //获取客户id
    $cutomer_id = $_['customer_id'];
    $cutomer_info = Cache\Customer::Get($cutomer_id);
    $userid = $cutomer_info->userid;
    $usernick = $_['usernick'];
    //$user_avater   = $_['user_avater'];
    $phone = $_['phone'];
    $sex = $_['sex'];

    $mgo = new \DaoMongodb\User;
    $entry = new \DaoMongodb\UserEntry;

    $entry->userid = $userid;
    $entry->usernick = $usernick;
    //$entry->user_avater = $user_avater;
    $entry->phone = $phone;
    $entry->sex = $sex;
    $ret = $mgo->Save($entry);

    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $cus_mgo = new \DaoMongodb\Customer;
    $info = new \DaoMongodb\CustomerEntry;

    $info->userid = $userid;
    $info->usernick = $usernick;
    $info->phone = $phone;
    $info->sex = $sex;
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

function GetCoke(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
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
    $coke = rand(100000,999999);
    $db   = \DbPool::GetRedis(DB_LOGIN);
    $ret  = $db->setex($phone, 300, $coke);
    $resp = (object)array(
        'coke' => $coke
    );
    return 0;
}

$ret = -1;
$resp = (object)array();
if (isset($_['customer_save']))
{
    $ret = SaveUserInfo($resp);
}elseif (isset($_['get_coke'])) {

    $ret = GetCoke($resp);
}
$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>

