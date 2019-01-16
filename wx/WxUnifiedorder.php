<?php

require_once("/www/public.sailing.com/php/weixin/WxUnifiedorder.php");
/*
 * [Rocky 2017-05-26 22:05:03]
 * 统一下单接口
 */
// declare(encoding='UTF-8');
// namespace Wx;
// require_once "WxUtil.php";

// class Unifiedorder
// {
//     public $value = [];

//     public function SetParam($key, $value)
//     {
//         $this->value[$key] = $value;
//     }

//     public function Submit()
//     {

//         //$this->value['body']             = xxx;  // 商品描述
//         //$this->value['attach']           = xxx;  // 附加数据
//         //$this->value['out_trade_no']     = xxx;  // 商户订单号
//         //$this->value['sub_mch_id']       = xxx;  // 子商户号
//         //$this->value['notify_url']       = xxx;  // 通知地址
//         //$this->value['total_fee']        = xxx;  // 总金额
//         //$this->value['sub_openid']       = xxx;  // 用户子标识
//         if(!$this->value['openid'])
//         {
//             $this->value['openid'] = \Wx\Util::GetOpenid();  // 用户标识
//         }

//         $this->value['appid']            = \Wx\Cfg::APPID;  // 公众账号ID
//         $this->value['mch_id']           = \Wx\Cfg::MCH_ID;  // 商户号
//         $this->value['device_info']      = "WEB";  // 设备号
//         $this->value['nonce_str']        = md5(rand());  // 随机字符串
//         $this->value['fee_type']         = "CNY";  // 货币类型
//         $this->value['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];  // 终端IP
//         $this->value['trade_type']       = "JSAPI";  // 交易类型(另注，用"NATIVE"可生成扫码支付的链接)
//         $this->value['sign_type']        = "MD5";  // 签名类型
//         $this->value['sign']             = \Wx\Util::GetSign($this->value);  // 签名
//         LogDebug($this->value);
//         $xml = \Wx\Util::ToXml($this->value);
//         // echo htmlspecialchars($xml) . "<hr>";
//         $ret = \Wx\Util::HttpPost(\Wx\Cfg::WX_URL_UNIFIEDORDER, $xml);
//         return $ret;
//     }

//     public function SubmitMicropay()
//     {

//         $this->value['appid']            = \Wx\Cfg::APPID;  // 公众账号ID
//         $this->value['mch_id']           = \Wx\Cfg::MCH_ID;  // 商户号
//         $this->value['device_info']      = "WEB";  // 设备号
//         $this->value['nonce_str']        = md5(rand());  // 随机字符串
//         $this->value['fee_type']         = "CNY";  // 货币类型
//         $this->value['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];  // 终端IP
//         $this->value['sign']             = \Wx\Util::GetSign($this->value);  // 签名

//         $xml = \Wx\Util::ToXml($this->value);
//         $ret = \Wx\Util::HttpPost(\Wx\Cfg::WX_URL_MICROPAY, $xml);
//         //echo htmlspecialchars($ret) . "<hr>";die;
//         return $ret;
//     }

//     public function SubmitOrder()
//     {

//         $this->value['appid']            = \Wx\Cfg::APPID;  // 公众账号ID
//         $this->value['mch_id']           = \Wx\Cfg::MCH_ID;  // 商户号
//         $this->value['nonce_str']        = md5(rand());  // 随机字符串
//         $this->value['sign']             = \Wx\Util::GetSign($this->value);  // 签名

//         $xml = \Wx\Util::ToXml($this->value);
//         $ret = \Wx\Util::HttpPost(\Wx\Cfg::WX_URL_ORDERQUERY, $xml);
//         //echo htmlspecialchars($ret) . "<hr>";die;
//         return $ret;
//     }

//     public function SubmitSend($openid, $content, $access_token)
//     {
//         $data->touser           = $openid;  // 用户标识
//         $data->msgtype          = 'text';  // 发送类型
//         $data->text             = $content;  // 发送内容
//         $data = json_encode($data);
//         $url = \Wx\Cfg::WX_URL_SEND. "?access_token=$access_token";
//         $ret = \Wx\Util::HttpPost($url, $data);
//         return $ret;
//     }

//     public function SubmitQRpay()
//     {
//         $this->value['appid']            = \Wx\Cfg::APPID;  // 公众账号ID
//         $this->value['mch_id']           = \Wx\Cfg::MCH_ID;  // 商户号
//         $this->value['device_info']      = "WEB";  // 设备号
//         $this->value['nonce_str']        = md5(rand());  // 随机字符串
//         $this->value['fee_type']         = "CNY";  // 货币类型
//         $this->value['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];  // 终端IP
//         $this->value['trade_type']       = "NATIVE";  // 交易类型(另注，用"NATIVE"可生成扫码支付的链接)
//         $this->value['sign_type']        = "MD5";  // 签名类型
//         $this->value['sign']             = \Wx\Util::GetSign($this->value);  // 签名

//         $xml = \Wx\Util::ToXml($this->value);
//         // echo htmlspecialchars($xml) . "<hr>";
//         $ret = \Wx\Util::HttpPost(\Wx\Cfg::WX_URL_UNIFIEDORDER, $xml);
//         return $ret;
//     }

//     public function SubmitRefund()
//     {
//         $this->value['appid']            = \Wx\Cfg::APPID;  // 公众账号ID
//         $this->value['mch_id']           = \Wx\Cfg::MCH_ID;  // 商户号
//         $this->value['nonce_str']        = md5(rand());  // 随机字符串
//         $this->value['refund_fee_type']  = "CNY";  // 货币类型
//         $this->value['sign_type']        = "MD5";  // 签名类型
//         $this->value['sign']             = \Wx\Util::GetSign($this->value);  // 签名

//         $xml = \Wx\Util::ToXml($this->value);
//         LogDebug($xml);
//         LogDebug(\Wx\Cfg::WX_URL_REFUND);
//         // echo htmlspecialchars($xml) . "<hr>";
//         $ret = \Wx\Util::HttpPost(\Wx\Cfg::WX_URL_REFUND, $xml);
//         return $ret;
//     }
// }

// <xml>
//     <out_refund_no><![CDATA[1524882907_refund_SL10791]]></out_refund_no>
//     <out_trade_no><![CDATA[1524820473_SL10791]]></out_trade_no>
//     <total_fee><![CDATA[1]]></total_fee>
//     <refund_fee><![CDATA[1]]></refund_fee>
//     <notify_url><![CDATA[http://wx.jzzwlcm.com/refund_notify.php]]></notify_url>
//     <appid><![CDATA[wxaaceede0e7695fcf]]></appid>
//     <mch_id><![CDATA[1464120802]]></mch_id>
//     <nonce_str><![CDATA[54332316e2e09cd8a2f10dc0725b7466]]></nonce_str>
//     <refund_fee_type><![CDATA[CNY]]></refund_fee_type>
//     <sign_type><![CDATA[MD5]]></sign_type>
//     <sign><![CDATA[BDAA59E5E5598618CA03858C51A93183]]></sign>
// </xml>


/*
<xml>
    <auth_code><![CDATA[135247227887499677]]></auth_code>
    <body><![CDATA[商品]]></body>
    <attach><![CDATA[无]]></attach>
    <out_trade_no><![CDATA[1513587266_111]]></out_trade_no>
    <sub_mch_id><![CDATA[1467121102]]></sub_mch_id>
    <total_fee><![CDATA[1]]></total_fee>
    <appid><![CDATA[wxaaceede0e7695fcf]]></appid>
    <mch_id><![CDATA[1464120802]]></mch_id>
    <device_info><![CDATA[WEB]]></device_info>
    <nonce_str><![CDATA[c0f493258957f982a8ce6d59f9789e42]]></nonce_str>
    <fee_type><![CDATA[CNY]]></fee_type>
    <spbill_create_ip><![CDATA[121.35.185.115]]></spbill_create_ip>
    <sign_type><![CDATA[MD5]]></sign_type>
    <sign><![CDATA[722180C93F14D48FFC584DA76555F538]]></sign>
</xml>

<xml>
   <appid>wx2421b1c4370ec43b</appid>
   <attach>订单额外描述</attach>
   <auth_code>120269300684844649</auth_code>
   <body>刷卡支付测试</body>
   <device_info>1000</device_info>
   <goods_tag></goods_tag>
   <mch_id>10000100</mch_id>
<sub_mch_id>10000101</sub_mch_id>
   <nonce_str>8aaee146b1dee7cec9100add9b96cbe2</nonce_str>
   <out_trade_no>1415757673</out_trade_no>
   <spbill_create_ip>14.17.22.52</spbill_create_ip>
   <time_expire></time_expire>
   <total_fee>1</total_fee>
   <sign>C29DB7DB1FD4136B84AE35604756362C</sign>
</xml>
 -->

<!--  <xml>
    <return_code><![CDATA[SUCCESS]]></return_code>
    <return_msg><![CDATA[OK]]></return_msg>
    <appid><![CDATA[wxaaceede0e7695fcf]]></appid>
    <mch_id><![CDATA[1464120802]]></mch_id>
    <sub_mch_id><![CDATA[1467121102]]></sub_mch_id>
    <device_info><![CDATA[WEB]]></device_info>
    <nonce_str><![CDATA[f98f034c1daa2fc0dbbf450d1619af84]]></nonce_str>
    <sign><![CDATA[33BA17CE713EB04257EA1151156AABB1]]></sign>
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
    <cash_fee>1</cash_fee>
    <cash_fee_type><![CDATA[CNY]]></cash_fee_type>
</xml>
*/
?>