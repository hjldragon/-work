<?php
/*
 *
 */
ob_start();
require_once("current_dir_env.php");
ob_end_clean();


function Input()
{	
	//LogDebug($_);

    $_ = &$GLOBALS["_"]; // &$_REQUEST; //
    $domain        = Cfg::instance()->GetMainDomain();
    if($_['type'] == 'wx')
    {
        $url     = "http://wx.$domain/wx_qrpay.php?order_id=" . $_['order_id'] . '&token=' . $_['token'].'&price='.$_['price'];
    }else{
        $url     = "http://alipay.$domain/alipay_qrpay.php?order_id=" . $_['order_id'] . '&token=' . $_['token'].'&price='.$_['price'];
    }
    header("HTTP/1.1 302 See Other");
    header("Location: $url");
}
//http:wx.jzzwlcm.com/wx_qrpay.php?order_id=SL18876&token=T1diOaS67anU13fs&srctype=4&price=58
function Output(&$obj)
{

}

Input();
?>