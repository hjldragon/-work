<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 获取发票信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/mart/mgo_invoice.php");
use \Pub\Mongodb as Mgo;

function GetInvoiceList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid = (int)$_['userid'];
    $mgo  = new Mgo\Invoice;
    $info = $mgo->GetInvoiceByUserid($userid);

    $resp = (object)[
        'invoice_list' => $info,
    ];
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetInvoiceinfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $invoice_id = $_['invoice_id'];
    $mgo  = new Mgo\Invoice;
    $info = $mgo->GetInvoiceById($invoice_id);

    $resp = (object)[
        'invoice_info' => $info,
    ];
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)[];

if (isset($_["get_invoice"]))
{
    $ret = GetInvoiceList($resp);
}
if (isset($_["get_invoice_info"]))
{
    $ret = GetInvoiceinfo($resp);
}

$html = json_encode((object)[
    'ret'  => $ret,
    'data' => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
]);

?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>
