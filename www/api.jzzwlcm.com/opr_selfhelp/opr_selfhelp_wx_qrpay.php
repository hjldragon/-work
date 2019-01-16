<?php
/*
 *
 */
ob_start();
require_once("current_dir_env.php");
ob_end_clean();


function Input()
{
    $_       = &$GLOBALS["_"];
    $domain  = Cfg::instance()->GetMainDomain();
    $url     = "http://wx.$domain/wx_qrpay.php?order_id=" . $_['order_id'] . '&token=' . $_['token'].'&price='.$_['price'];
    header("HTTP/1.1 302 See Other");
    header("Location: $url");
    //LogDebug($_);
}
//http://api.jzzwlcm.com/selfhelp.php?opr=qrpay&qr_name=wx_qrpay&order_id=SL13486&token=T1diOaS67anU13fs&srctype=4&price=58
function Output(&$obj)
{
}

Input();
?>