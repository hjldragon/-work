<?php
/*
 * [Rocky 2017-05-04 11:48:01]
 * 取打印机信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_printer.php");

// $_=$_REQUEST;

function GetPrinterInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $printer_id = (string)$_['printer_id'];

    $mgo = new \DaoMongodb\Printer;
    $info = $mgo->GetPrinterById($printer_id);

    $resp = (object)array(
        'info' => $info
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetPrinterList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Printer;
    $list = $mgo->GetList($shop_id);
    $resp = (object)array(
        'list' => $list
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_["info"]))
{
    $ret = GetPrinterInfo($resp);
}
elseif(isset($_["list"]))
{
    $ret = GetPrinterList($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
