<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取餐品类别信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("/www/public.sailing.com/php/mart/mgo_freight.php");
use \Pub\Mongodb as Mgo;

function GetFreightInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $freight_id = (string)$_['freight_id'];
    if(!$freight_id)
    {
        LogErr("no freight_id");
        return errcode::PARAM_ERR;
    }
    $mgo  = new Mgo\Freight;
    $info = $mgo->GetFreightById($freight_id);

    $resp = (object)array(
        'info' => $info
    );
   // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetFreightList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $platform_id = PlatformID::ID;
    $mgo  = new Mgo\Freight;
    $list = $mgo->GetFreightList($platform_id);
    $resp = (object)array(
        'list' => $list
    );
    LogInfo("--ok--");
    return 0;
}



$ret = -1;
$resp = (object)array();
if(isset($_["get_freight_info"]))
{
    $ret = GetFreightInfo($resp);
}
elseif(isset($_["get_freight_list"]))
{
    $ret = GetFreightList($resp);
}

else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
LogDebug($html);
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
