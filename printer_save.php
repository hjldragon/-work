<?php
/*
 * [Rocky 2017-05-04 00:25:54]
 * 打印机信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_printer.php");


function SavePrinter(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<待合并
    if(!PageUtil::LoginCheck())
    {
        LogDebug("not login, token:{$_['token']}");
        return errcode::USER_NOLOGIN;
    }

    $printer_id   = $_['printer_id'];
    $printer_name = $_['printer_name'];

    $shop_id = \Cache\Login::GetShopId();

    $mongodb = new \DaoMongodb\Printer;
    $entry = new \DaoMongodb\PrinterEntry;

    $entry->printer_id   = $printer_id;
    $entry->printer_name = $printer_name;
    $entry->shop_id      = $shop_id;

    $ret = $mongodb->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['save']))
{
    $ret = SavePrinter($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
