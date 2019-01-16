<?php
/*
 *
 */
ob_start();
require_once("current_dir_env.php");
ob_end_clean();


function Input()
{
    $_       = &$_REQUEST; //&$GLOBALS["_"];
    $domain  = Cfg::instance()->GetMainDomain();
    $url     = "http://wx.$domain/wx_login.php?token=" . $_['token']."&srctype=".$_['srctype'];
    header("HTTP/1.1 302 See Other");
    header("Location: $url");
    LogDebug($_);
}
//http://wx.jzzwlcm.com/wx_login.php?token=T3G8xVvqBECG2Z7S&
function Output(&$obj)
{
}

Input();
?>