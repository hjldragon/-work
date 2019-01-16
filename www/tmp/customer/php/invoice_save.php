<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 发票信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_invoice.php");
require_once("redis_id.php");
require_once("const.php");

//$_=$_REQUEST;
function SaveInvoiceinfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $userid            = (int)$_['userid'];
    $type              = (int)$_['type'];
    $phone             = $_['phone'];
    $mail_address      = $_['mail_address'];
    $invoice_title     = $_['invoice_title'];
    $duty_paragraph    = $_['duty_paragraph'];
    $unit_address      = $_['unit_address'];
    $bank_name         = $_['bank_name'];
    $bank_account      = $_['bank_account'];
    $email             = $_['email'];

   if(!$userid)
   {
       return errcode::USER_NOLOGIN;
   }
   //纸质个人发票
    if($type == 1){
        $paperindinvoice->invoice_title   = $invoice_title;
        $paperindinvoice->phone           = $phone;
        $paperindinvoice->mail_address    = $mail_address;
    }
    //纸质单位发票
   if($type == 2){
     $paperunitinvoice->invoice_title  = $invoice_title;
     $paperunitinvoice->duty_paragraph = $duty_paragraph;
     $paperunitinvoice->phone          = $phone;
     $paperunitinvoice->unit_address   = $unit_address;
     $paperunitinvoice->bank_name      = $bank_name;
     $paperunitinvoice->bank_account   = $bank_account;
   }
    //电子个人发票
    if($type == 3)
    {
        $eleindinvoice->invoice_title     = $invoice_title;
        $eleindinvoice->phone             = $phone;
        $eleindinvoice->email             = $email;
    }
    //电子单位发票
   if($type == 4){
       $eleunitinvoice->invoice_title    = $invoice_title;
       $eleunitinvoice->duty_paragraph   = $duty_paragraph;
       $eleunitinvoice->phone            = $phone;
       $eleunitinvoice->email            = $email;
   }


    $entry                            = new \DaoMongodb\InvoiceEntry;
    $mongodb                          = new \DaoMongodb\Invoice;
    $entry->userid                    = $userid;
    $entry->paperindinvoice           = $paperindinvoice;
    $entry->paperunitinvoice          = $paperunitinvoice;
    $entry->eleunitinvoice            = $eleunitinvoice;
    $entry->eleindinvoice             = $eleindinvoice;
    $entry->delete                    = 0;
    $ret                              = $mongodb->Save($entry);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($entry);
    $resp = (object)array(
        "user_id" => $userid
    );
    LogInfo("save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['save']))
{
    $ret = SaveInvoiceinfo($resp);
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


