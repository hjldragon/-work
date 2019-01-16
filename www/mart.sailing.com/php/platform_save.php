<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 平台信息保存类
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/php/mgo_platform.php");
use \Pub\Mongodb as Mgo;

//保存平台广告信息
function SaveAdvertise(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $type = $_["type"]; //(0:pc,1手机)
    $list = (object)[];
    $list->banner_picture   = json_decode($_["banner_picture"]);
    $list->hotgoods_picture = json_decode($_["hotgoods_picture"]);
    $list->hardware_adpic   = json_decode($_["hardware_adpic"]);
    $list->hardware_picture = json_decode($_["hardware_picture"]);
    $list->software_adpic   = json_decode($_["software_adpic"]);
    $list->software_picture = json_decode($_["software_picture"]);
    $list->consum_adpic     = json_decode($_["consum_adpic"]);
    $list->consum_picture   = json_decode($_["consum_picture"]);
    $list->access_adpic     = json_decode($_["access_adpic"]);
    $list->access_picture   = json_decode($_["access_picture"]);

    $platform_id = PlatformID::ID;


    $entry = new Mgo\PlatformEntry;
    $entry->platform_id      = $platform_id;
    if($type)
    {
        $entry->phone_advertise = $list;
    }
    else
    {
        $entry->pc_advertise = $list;
    }
    // $entry->banner_picture   = $banner_picture;
    // $entry->hotgoods_picture = $hotgoods_picture;
    // $entry->hardware_adpic   = $hardware_adpic;
    // $entry->hardware_picture = $hardware_picture;
    // $entry->software_adpic   = $software_adpic;
    // $entry->software_picture = $software_picture;
    // $entry->consum_adpic     = $consum_adpic;
    // $entry->consum_picture   = $consum_picture;
    // $entry->access_adpic     = $access_adpic;
    // $entry->access_picture   = $access_picture;

    $ret = Mgo\Platform::My()->Save($entry);

    if (0 != $ret) {
        LogErr("platform_id:[$platform_id] Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

//保存平台运费信息
function SaveFreight(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }


    $first_fee        = $_["first_fee"];
    $add_fee          = $_["add_fee"];
    $first_weight     = $_["first_weight"];
    $add_weight       = $_["add_weight"];

    $platform_id = PlatformID::ID;


    $entry = new Mgo\PlatformEntry;
    $entry->platform_id      = $platform_id;
    $entry->first_fee        = $first_fee;
    $entry->add_fee          = $add_fee;
    $entry->first_weight     = $first_weight;
    $entry->add_weight       = $add_weight;

    $ret = Mgo\Platform::My()->Save($entry);

    if (0 != $ret) {
        LogErr("platform_id:[$platform_id] Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['advertise_save']))
{
    $ret = SaveAdvertise($resp);
}
elseif(isset($_['freight_save']))
{
    $ret = SaveFreight($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
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
?><?php /******************************以下为html代码******************************/?>

