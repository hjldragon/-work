<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 发票信息保存类
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/php/mart/mgo_invoice.php");
use \Pub\Mongodb as Mgo;

function SaveInvoiceinfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $invoice_id      = $_['invoice_id'];
    $userid          = $_['userid'];
    $invoice_type    = $_['invoice_type'];
    $title_type      = $_['title_type'];
    $invoice_title   = $_['invoice_title'];
    $duty_paragraph  = $_['duty_paragraph'];
    $phone           = $_['phone'];
    $email           = $_['email'];
    $unit_phone      = $_['unit_phone'];
    $unit_address    = $_['unit_address'];
    $bank_name       = $_['bank_name'];
    $bank_account    = $_['bank_account'];

    if(!$userid)
    {
       LogErr("userid err");
       return errcode::USER_NOLOGIN;
    }
    $entry = new Mgo\InvoiceEntry;
    if(!$invoice_id)
    {
        $invoice_id = \DaoRedis\Id::GenInvoiceId();
        $entry_time = time();
    }

    $entry->invoice_id      = $invoice_id;
    $entry->userid          = $userid;
    $entry->invoice_type    = $invoice_type;
    $entry->title_type      = $title_type;
    $entry->invoice_title   = $invoice_title;
    $entry->duty_paragraph  = $duty_paragraph;
    $entry->phone           = $phone;
    $entry->email           = $email;
    $entry->unit_phone      = $unit_phone;
    $entry->unit_address    = $unit_address;
    $entry->bank_name       = $bank_name;
    $entry->bank_account    = $bank_account;
    $entry->ctime           = $entry_time;
    $ret = Mgo\Invoice::My()->Save($entry);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($entry);
    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

function DeleteInvoice(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $invoice_id_list = json_decode($_['invoice_id_list']);

    $mongodb = new Mgo\Invoice;
    $ret = $mongodb->BatchDelete($invoice_id_list);
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
if(isset($_['invoice_save']))
{
    $ret = SaveInvoiceinfo($resp);
}elseif(isset($_['invoice_del']))
{
    $ret = DeleteInvoice($resp);
}else{
    LogErr("param no");
    return errcode::PARAM_ERR;
}


$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>


