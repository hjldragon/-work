<?php
/*
 *
 */
ob_start();
require_once("current_dir_env.php");
ob_end_clean();


function Input()
{
    $_       = &$GLOBALS["_"]; // &$_REQUEST; //
    $domain  = Cfg::instance()->GetMainDomain();
    $url     = "http://wx.$domain/wx_qrpay.php?order_id=" . $_['order_id'] . '&token=' . $_['token'].'&price='.$_['price'];
    header("HTTP/1.1 302 See Other");
    header("Location: $url");
    LogDebug($_);
}

function Output(&$obj)
{
}

Input();
?>