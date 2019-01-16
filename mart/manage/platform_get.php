<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取餐桌位置信息
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/php/mgo_platform.php");
use \Pub\Mongodb as Mgo;


function GetPlatformInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $platform_id = PlatformID::ID;

    $mgo = new Mgo\Platform;
    $info = $mgo->GetPlatformById($platform_id);
    $resp = (object)array(
        'info' => $info
    );

    LogInfo("--ok--");
    return 0;
}



$ret = -1;
$resp = (object)array();
if(isset($_["get_platform_info"]))
{
    $ret = GetPlatformInfo($resp);
}

$result = (object)array(
    'ret' => $ret,
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

