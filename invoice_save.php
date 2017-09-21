<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 发票信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_invoice.php");
require_once("redis_id.php");
require_once("const.php");




 

function SaveInvoiceinfo(&$resp)
{
    
    $_ = $GLOBALS["_"];
    LogDebug($_);
    //var_dump(json_decode($_['list']));die;
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    // if(!PageUtil::LoginCheck())
    // {
    //     LogDebug("not login, token:{$_['token']}");
    //     return errcode::USER_NOLOGIN;
    // }

    $invoice_id          = (string)$_['invoice_id'];
    $userid              = (int)$_['userid'];
    $type                = (int)$_['type'];
    $title_type          = (int)$_['title_type'];
    $invoice_title       = (string)$_['invoice_title'];
    $duty_paragraph      = (string)$_['duty_paragraph'];
    $phone               = (string)$_['phone'];
    $address             = (string)$_['address'];
    $bank_name           = (string)$_['bank_name'];
    $bank_account        = (string)$_['bank_account'];
    $email               = (string)$_['email'];
   

    if(!$invoice_id)
    {
        $invoice_id = \DaoRedis\Id::GenInvoiceId();
    }

    $entry = new \DaoMongodb\InvoiceEntry;

    $mongodb = new \DaoMongodb\Invoice;

    $entry->invoice_id     = $invoice_id;
    $entry->userid         = $userid;
    $entry->type           = $type;
    $entry->title_type     = $title_type;
    $entry->invoice_title  = $invoice_title;
    $entry->duty_paragraph = $duty_paragraph;
    $entry->phone          = $phone;
    $entry->address        = $address;
    $entry->bank_name      = $bank_name;
    $entry->email          = $email;
    $entry->delete         = 0;
    $ret = $mongodb->Save($entry);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($entry);

    $resp = (object)array(
        "invoice_id" => $invoice_id
    );
    
    LogInfo("save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['save']))
{
    $ret = SaveInvoiceinfo($resp);
}


$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>


