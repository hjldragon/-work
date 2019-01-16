<?php
/*
 * 处理微信回调
 */
require_once("current_dir_env.php");
require_once("vendor_order_pay.php");
require_once("wx_util.inc");
use \Typedef as T;

function WxNotify()
{
    $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
    // test
    if(!$xml)
    {
        $xml =<<<eof
            <xml><appid><![CDATA[wxaaceede0e7695fcf]]></appid>
            <attach><![CDATA[{"order_id":"O800", "shop_id":"A10000"}]]></attach>
            <bank_type><![CDATA[CFT]]></bank_type>
            <cash_fee><![CDATA[2]]></cash_fee>
            <device_info><![CDATA[WEB]]></device_info>
            <fee_type><![CDATA[CNY]]></fee_type>
            <is_subscribe><![CDATA[Y]]></is_subscribe>
            <mch_id><![CDATA[1464120802]]></mch_id>
            <nonce_str><![CDATA[076593026779f0ba95168420219226b1]]></nonce_str>
            <openid><![CDATA[oVQGs1Imf8L2EBcn2N0DyJRKQ8pc]]></openid>
            <out_trade_no><![CDATA[1532424853_O472]]></out_trade_no>
            <result_code><![CDATA[SUCCESS]]></result_code>
            <return_code><![CDATA[SUCCESS]]></return_code>
            <sign><![CDATA[B2A98CE41E8FAC24BC5FFC6263B5FAE7]]></sign>
            <sub_mch_id><![CDATA[1467121102]]></sub_mch_id>
            <time_end><![CDATA[20180724173454]]></time_end>
            <total_fee>2</total_fee>
            <trade_type><![CDATA[JSAPI]]></trade_type>
            <transaction_id><![CDATA[4200000110201807248053171704]]></transaction_id>
            </xml>
eof;
    }
    $ary = \Wx\Util::FromXml($xml);
    LogDebug($ary);
    $sign = \Wx\Util::GetSign($ary);
    if($sign !== $ary->sign || "SUCCESS" != $ary->return_code)
    {
        LogErr("sign err, sign:[$sign], return_code:{$ary->return_code}");
        return errcode::SYS_ERR;
    }

    $attach = json_decode($ary->attach);

    if(!$attach->order_id)
    {
        LogErr("order_id err");
        return errcode::SYS_ERR;
    }

    // 这里应调用公共的订单处理函数（微信、支付宝一样）
    // 更新订单信息
    $order_ret =  OrderUpdate::SavePayOrder($attach->order_id);
    if($order_ret != 0)
    {
        LogDebug('order change err');
        return errcode::PARAM_ERR;
    }
    // 发通知

    return 0;
}

function main()
{
    $ret = WxNotify();
    if(0 == $ret)
    {
        $ret_xml =<<<eof
<xml>
   <return_code><![CDATA[SUCCESS]]></return_code>
   <return_msg><![CDATA[OK]]></return_msg>
</xml>
eof;
        echo $ret_xml;
    }
    else
    {

        $ret_xml =<<<eof
<xml>
   <return_code><![CDATA[FAIL]]></return_code>
   <return_msg><![CDATA[出错]]></return_msg>
</xml>
eof;
        echo $ret_xml;
    }
}

main();

// test
// curl 'http://vendor.jzzwlcm.com:8084/php/wx_notify_pay.php'
//
