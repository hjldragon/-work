<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 平台运费保存类
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/php/mart/mgo_freight.php");
use \Pub\Mongodb as Mgo;

//保存平台运费信息
function SaveFreightInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $freight_id   = $_["freight_id"];
    $province     = $_["province"];
    $city         = $_["city"];
    $first_fee    = $_["first_fee"];
    $add_fee      = $_["add_fee"];
    $first_weight = $_["first_weight"];
    $add_weight   = $_["add_weight"];
    $platform_id  = PlatformID::ID;

    if(!$freight_id)
    {
        if(!$city || !$first_fee || !$add_fee || !$first_weight || !$add_weight)
        {
            LogErr("param err");
            return errcode::PARAM_ERR;
        }
        $freight_id = \DaoRedis\Id::GenFreightId();
        $ctime = time();
    }
    $mgo = new Mgo\Freight;
    if($city)
    {
        $info = $mgo->GetFreightByCity($platform_id, $city);
        if($info->freight_id && $freight_id != $info->freight_id)
        {
            LogErr("city:[$city] exist");
            return errcode::NAME_IS_EXIST;
        }
    }

    $entry = new Mgo\FreightEntry;
    $entry->freight_id   = $freight_id;
    $entry->platform_id  = $platform_id;
    $entry->province     = $province;
    $entry->city         = $city;
    $entry->first_fee    = $first_fee;
    $entry->add_fee      = $add_fee;
    $entry->first_weight = $first_weight;
    $entry->add_weight   = $add_weight;
    $entry->ctime        = $ctime;

    $ret = $mgo->Save($entry);

    if (0 != $ret) {
        LogErr("freight_id:[$freight_id] Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

function DeleteFreight(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $freight_id_list = json_decode($_['freight_id_list']);

    $mongodb = new Mgo\Freight;
    $ret = $mongodb->BatchDelete($freight_id_list);
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

$ret = -1;
$resp = (object)array();
if(isset($_['freight_save']))
{
    $ret = SaveFreightInfo($resp);
}
elseif(isset($_['freight_del']))
{
    $ret = DeleteFreight($resp);
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

