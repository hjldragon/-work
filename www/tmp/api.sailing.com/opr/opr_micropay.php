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
    if($_['type'] == 'wx')
    {
        $url = "http://wx.$domain/wx_micropay.php?order_id=" . $_['order_id'] . '&auth_code=' . $_['auth_code'] . '&price=' . $_['price'];
    }else{
        $url = "http://alipay.$domain/alipay_micropay.php?order_id=" . $_['order_id'] . '&auth_code=' . $_['auth_code'].'&price='.$_['price'];
    }
    header("HTTP/1.1 302 See Other");
    header("Location: $url");
    LogDebug($_);
}

function Output(&$obj)
{
}
//http://api.jzzwlcm.com/index.php?opr=micropay&type=wx&order_id=SL15681&auth_code=135600340992272226&price=0.01
Input();
?>