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
    //LogDebug($resp);//stdClass Object()//是一个stdClass 对象吗？
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
//LogDebug(PageUtil::LoginCheck());打印出来是1或者2,2是登录，1是没有登录
    if(!PageUtil::LoginCheck())
    {
        LogDebug("not login, token:{$_['token']}");
        return errcode::USER_NOLOGIN;
    }

    $printer_id   = $_['printer_id'];
    $printer_name = $_['printer_name'];
    $printer_category = $_['printer_category'];
    $printer_size = $_['printer_size'];
    $printer_brand = $_['printer_brand'];
    $printer_note = $_['printer_note'];
    $print_position_left = $_['print_position_left'];
    $print_position_top = $_['print_position_top'];
    $print_position_width = $_['print_position_width'];
    $print_position_height = $_['print_position_height'];
    $food_category_list = json_decode($_['food_category_list']);
    LogDebug($food_category_list);

    $shop_id = \Cache\Login::GetShopId();//获取店铺ID，根据登录者来判断店铺ID？

    $mongodb = new \DaoMongodb\Printer;
    $entry = new \DaoMongodb\PrinterEntry;

    $entry->printer_id            = $printer_id;
    $entry->printer_name          = $printer_name;
    $entry->printer_category      = $printer_category;
    $entry->printer_size          = $printer_size;
    $entry->printer_brand         = $printer_brand;
    $entry->printer_note          = $printer_note;
    $entry->shop_id               = $shop_id;
    $entry->delete                = 0;
    $entry->food_category_list    = $food_category_list;
    $entry->print_position_left   = $print_position_left;
    $entry->print_position_top    = $print_position_top;
    $entry->print_position_width  = $print_position_width;
    $entry->print_position_height = $print_position_height;

    $ret = $mongodb->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    // 通知到golang服务器，再转到店服务器（以便打印处理等）
    $url = Cfg::instance()->orderingsrv->webserver_url;
    //LogDebug("post to:[$url]");//http://192.168.56.1:21121/webserver//这是打印的地址吗？
    $ret = PageUtil::HttpPostJsonData($url, [
        'Opr' => "UpdatePrinter",
        'ShopId' => $shop_id,
    ]);
     LogDebug("post, ret:[$ret], url:[$url]");
    //LogDebug($ret);
    $ret = json_decode($ret);

    if(0 != $ret->Ret)
    {
        LogErr("post err: {$ret->Msg}, url:[$url]");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogDebug($resp);
    LogInfo("save ok");
    return 0;
}

function DeletePrinter(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    if(!PageUtil::LoginCheck())
    {
        LogDebug("not login, token:{$_['token']}");
        return errcode::USER_NOLOGIN;
    }

    $printer_id_list = json_decode($_['printer_id_list']);

    $mongodb = new \DaoMongodb\Printer;
    $ret = $mongodb->BatchDelete($printer_id_list);
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
if(isset($_['del']))
{
    $ret = DeletePrinter($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
