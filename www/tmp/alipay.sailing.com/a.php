<?php
ini_set('date.timezone','Asia/Shanghai');
require_once("AlipayUtil.php");
require_once("AopClient.php");
require_once("cache.php");
require_once("SignData.php");
require_once("AlipayTradeWapPayRequest.php");
require_once("redis_pay.php");


$order_id = "A10000";
$fee = 0.01;

if(!$param->referer)
{
    $param->referer = "http://customer.jzzwlcm.com/index.html?shop_id=$order_info->shop_id&seat_id=$order_info->seat_id&customer_id=$order_info->customer_id&userid=$customer_info->userid/#/payFail?order_id=$order_id";
}

$content = (object)[];
$attach = (object)["order_id"=>$order_id];
$out_trade_no = time() . "_" . $order_info->order_id;
$content->out_trade_no = $out_trade_no;                       //商户订单号
$content->total_amount = $fee;          //订单总金额
$content->subject      = "测试品";               //订单标题
$content->product_code = 'QUICK_WAP_WAY';                     //销售产品码，商家和支付宝签约的产品码。该产品请填写固定值：QUICK_WAP_WAY
$content->body         = json_encode($attach);                //对交易或商品的描述
$content = json_encode($content);
$aop = new AopClient ();
$aop->appId              = \Alipay\Cfg::APPID;
$aop->rsaPrivateKey      = Cfg::instance()->rsa->privatekey;
$aop->alipayrsaPublicKey = Cfg::instance()->rsa->publickey;
$aop->apiVersion         = '1.0';
$aop->signType           = 'RSA2';
$aop->postCharset        = 'UTF-8';
$aop->format             = 'json';
$request = new AlipayTradeWapPayRequest();
$request->setBizContent($content);
$request->setNotifyUrl(\Alipay\Cfg::ALIPAY_NOTIFY_URL);
$result = $aop->pageExecute($request, "GET");
// $result = $aop->pageExecute($request);

?>

<head>
<meta charset="UTF-8">
</head>
<body  style="width: 100%; font-size: 32px;">
<h2 style="text-align: center;" >
正在支付...
</h2>
<p style="text-align: center;">单号: <?=$order_id?></p>
<p style="text-align: center;">金额: <?=$fee?></p>

<br>
<!--
<a href="<?=$result?>" target="f1">再次支付</a>
<iframe name="f1" id="f1" style="display: none;"></iframe>
<script type="text/javascript">
document.getElementById('f1').src = "<?=$result?>";
</script>
-->

<script type="text/javascript">
// 注意：支付宝中，点击支付窗口中左上的叉，等于关闭当前窗口，
document.addEventListener('AlipayJSBridgeReady', function () {
    if(location.href.indexOf("new_win") != -1)
    {
        location.href = "<?=$result?>";
    }
    else
    {
        AlipayJSBridge.call('pushWindow', {
          url: location.href + "?new_win=1",
        });
    }
}, false);

</script>

</body>