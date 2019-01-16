<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 微信用户信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_weixin.php");
require_once("redis_id.php");
require_once("const.php");

function WeixinSave($info, &$wx_id=null)
{
    if(!$info)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $id          = $info->id;
    $userid      = $info->userid;
    $headimgurl  = $info->headimgurl;
    $nickname    = $info->nickname;
    $sex         = $info->sex;
    $openid      = $info->openid;
    $city        = $info->city;
    $country     = $info->country;
    $province    = $info->province;
    $src         = $info->src;
    $srctype     = $info->srctype;


    if(!$id)
    {
        $id = \DaoRedis\Id::GenWeixinId();
    }
    $entry = new \DaoMongodb\WeixinEntry;
    $mongodb = new \DaoMongodb\Weixin;

    $entry->id          = $id;
    $entry->userid      = $userid;
    $entry->headimgurl  = $headimgurl;
    $entry->nickname    = $nickname;
    $entry->sex         = $sex;
    $entry->openid      = $openid;
    $entry->city        = $city;
    $entry->province    = $province;
    $entry->country     = $country;
    $entry->src         = $src;
    $entry->delete      = 0;
    $entry->srctype     = $srctype;
    $entry->lastmodtime = time();
    $ret = $mongodb->Save($entry);
    LogDebug($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $wx_id = $id;
    LogInfo("save ok");
    return 0;
}

?>