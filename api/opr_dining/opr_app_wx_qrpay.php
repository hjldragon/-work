<?php
/*
 *
 */
ob_start();
//require_once("current_dir_env.php");
set_include_path(dirname(__FILE__) . "/" . ":./opr:/www/dining.jzzwlcm.com/php/" );
date_default_timezone_set('Asia/Shanghai');   // 2015-01-28 20:49:45
require_once("global.php");
ob_end_clean();


function Input()
{
    $_       = &$GLOBALS["_"]; // &$_REQUEST; //
    $domain  = Cfg::instance()->GetMainDomain();
    $url     = "http://wx.$domain/wx_qrpay.php?order_id=" . $_['order_id'] . '&token=' . $_['token'];
    header("HTTP/1.1 302 See Other");
    header("Location: $url");
    LogDebug($_);
}

function Output(&$obj)
{
}

Input();
?>