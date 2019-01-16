<?php
/*
 *
 */
ob_start();
require_once("current_dir_env.php");
ob_end_clean();


function Input()
{
    $_ = &$GLOBALS["_"]; // &$_REQUEST; //
    $domain        = Cfg::instance()->GetMainDomain();
    if($_['type'] == 'wx') {
        $url = "http://wx.$domain/wx_playquery.php?order_id=" . $_['order_id'];
    }else{
        $url = "http://alipay.$domain/alipay_payquery.php?order_id=" . $_['order_id'];
    }
    header("HTTP/1.1 302 See Other");
    header("Location: $url");
    LogDebug($_);
}

function Output(&$obj)
{
}
//http://api.jzzwlcm.com/index.php?opr=payquery&type=wx&order_id=SL15686
Input();
?>