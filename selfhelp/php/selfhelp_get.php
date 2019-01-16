<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取餐桌位置信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("/www/public.sailing.com/php/mgo_selfhelp.php");
use \Pub\Mongodb as Mgo;

function GetSelfhelpInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $selfhelp_id = (string)$_['selfhelp_id'];
    if(!$selfhelp_id)
    {
        LogErr("no selfhelp_id");
        return errcode::PARAM_ERR;
    }

    $self  = new Mgo\Selfhelp;
    $info = $self->GetExampleById($selfhelp_id);

    $resp = (object)array(
        'info' => $info
    );
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_["get_selfhelp_info"]))
{
    $ret = GetSelfhelpInfo($resp);
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

