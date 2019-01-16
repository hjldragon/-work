<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 支付宝用户信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_weixin.php");
require_once("redis_id.php");
require_once("const.php");
 

function AlipaySave($info)
{
    if(!$info)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $id          = $info->id;
    $userid      = $info->userid;
    $avatar      = $info->avatar;
    $nickname    = $info->nick_name;
    $sex         = $info->sex;
    $alipay_id   = $info->user_id;
    $city        = $info->city;
    $province    = $info->province;
    $src         = $info->src;
    

    if(!$id)
    {
        $id = \DaoRedis\Id::GenAlipayId();
    }
    LogDebug($id);
    $entry = new \DaoMongodb\AlipayEntry;

    $mongodb = new \DaoMongodb\Alipay;
    
    $entry->id          = $id;
    $entry->userid      = $userid;
    $entry->avatar      = $avatar;
    $entry->nickname    = $nickname;
    $entry->sex         = $sex;
    $entry->alipay_id   = $alipay_id;
    $entry->city        = $city;
    $entry->province    = $province;
    $entry->src         = $src;
    $entry->delete      = 0;
    $entry->lastmodtime = time();
    $ret = $mongodb->Save($entry);
    //LogDebug($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogInfo("save ok");
    return 0;
}



?>


