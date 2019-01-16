<?php
ini_set('date.timezone','Asia/Shanghai');
require_once "WxUnifiedorder.php";
require_once("redis_pay.php");
require_once("mgo_order.php");
require_once("page_util.php");


$ret = -1;
$resp = (object)array();
$_ = $_REQUEST;
function GetOrder(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $order_id = $_['order_id'];
    if(!$order_id)
    {
        LogErr("OrderId err");
        return errcode::PARAM_ERR;
    }
    $ret = PageUtil::OrderQuery($order_id, $data);
    $resp = $data;
    return $ret;
}
$resp = (object)array();
$ret = GetOrder($resp);

$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
));
echo $html;


/*
<xml>
    <return_code><![CDATA[SUCCESS]]></return_code>
    <return_msg><![CDATA[OK]]></return_msg>
    <appid><![CDATA[wxaaceede0e7695fcf]]></appid>
    <mch_id><![CDATA[1464120802]]></mch_id>
    <sub_mch_id><![CDATA[1467121102]]></sub_mch_id>
    <device_info><![CDATA[WEB]]></device_info>
    <nonce_str><![CDATA[MMl2INSQf3SYhSfE]]></nonce_str>
    <sign><![CDATA[FFF6505F0BBE079968114AAF456B6B8D]]></sign>
    <result_code><![CDATA[SUCCESS]]></result_code>
    <openid><![CDATA[oVQGs1CM07N3qgJNTyQMAIkmxEMw]]></openid>
    <is_subscribe><![CDATA[N]]></is_subscribe>
    <trade_type><![CDATA[MICROPAY]]></trade_type>
    <bank_type><![CDATA[ICBC_DEBIT]]></bank_type>
    <total_fee>1</total_fee>
    <fee_type><![CDATA[CNY]]></fee_type>
    <transaction_id><![CDATA[4200000021201712186680986033]]></transaction_id>
    <out_trade_no><![CDATA[1513590655_111]]></out_trade_no>
    <attach><![CDATA[无]]></attach>
    <time_end><![CDATA[20171218175056]]></time_end>
    <trade_state><![CDATA[SUCCESS]]></trade_state>
    <cash_fee>1</cash_fee>
</xml>
*/
?>