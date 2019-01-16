<?php
require_once("current_dir_env.php");
require_once("mgo_resources.php");
use \Pub\Mongodb as Mgo;
function SavePingInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token = $_["token"];
    $mgo = new Mgo\Resources;
    $info = $mgo->QueryByToken($token);
    if(!$info || $info->last_use_time + 90 < time())
    {
        LogErr("Login Lapse");
        return errcode::LOGIN_LAPSE;
    }
    $entry = new Mgo\ResourcesEntry;
    $entry->resources_id  = $info->resources_id;
    $entry->last_use_time = time();
    $entry->term_id       = $token;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("resources save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(

    );

    LogInfo("--ok--");
    return 0;
}



$ret = -1;

$resp = (object)array();
if (isset($_["save_ping"]))
{
    $ret = SavePingInfo($resp);
}

$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp,//<<<<<<<未加密返回数据
    //'crypt' => 1, // 是加密数据标记
    //'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>