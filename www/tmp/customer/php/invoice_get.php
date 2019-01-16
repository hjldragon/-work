<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 获取发票信息
 */
require_once("current_dir_env.php");
require_once("mgo_invoice.php");
require_once("redis_id.php");
require_once("const.php");
//$_=$_REQUEST;
function GetInvoiceInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid = (int)$_['userid'];
    $mgo  = new \DaoMongodb\Invoice;
    $info = $mgo->GetInvoiceByUserid($userid);

    $resp = (object)[
        'invoice_info' => $info,
    ];
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)[];

if (isset($_["get_invoice_info"]))
{
    $ret = GetInvoiceInfo($resp);
}

$html = json_encode((object)[
    'ret'  => $ret,
    'data' => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
]);

?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>
