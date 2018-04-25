<?php
/*
 *
 */
ob_start();
require_once("current_dir_env.php");
ob_end_clean();


function Input()
{
    $_ = &$_REQUEST; //&$GLOBALS["_"];
    $domain        = Cfg::instance()->GetMainDomain();
    $url = "http://wx.$domain/wx_login.php?token=" . $_['token'];
    header("HTTP/1.1 302 See Other");
    header("Location: $url");
    LogDebug($_);
}

function Output(&$obj)
{
}

Input();
?>