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
    $url     = "http://alipay.$domain/alipay_qrpay.php?order_id=" . $_['order_id'] . '&token=' . $_['token'].'&price='.$_['price'];
    header("HTTP/1.1 302 See Other");
    header("Location: $url");
    //LogDebug($_);
}
//http://api.jzzwlcm.com/selfhelp.php?opr=qrpay&qr_name=alipay_qrpay&order_id=SL20166&token=T1diOaS67anU13fs&srctype=4&price=0.02
function Output(&$obj)
{
}

Input();
?>