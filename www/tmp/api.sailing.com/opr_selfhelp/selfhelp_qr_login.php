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
    $domain    = Cfg::instance()->GetMainDomain();
    $url = "http://wx.$domain/wx_login.php?token=" . $_['token']."&selfhelp_id=".$_['selfhelp_id'];
    header("HTTP/1.1 302 See Other");
    header("Location: $url");
    LogDebug($_);
}
//http:api.jzzwlcm.com/selfhelp.php?opr=qr_login&token=T3G8xVvqBECG2Z7S&selhelp_id=MTBEMDdBNzQ1MjdG
function Output(&$obj)
{
}

Input();
?>