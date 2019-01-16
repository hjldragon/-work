<?php
ini_set('date.timezone','Asia/Shanghai');
require_once("current_dir_env.php");
require_once("AlipayUtil.php");
require_once("AopClient.php");
require_once("cache.php");
require_once("SignData.php");
require_once("AlipayTradeWapPayRequest.php");
require_once("redis_pay.php");


// test test test test test test test
// exit(0);



$_ = $_REQUEST;
$order_id = $_['order_id'];
if(!$order_id)
{
    LogErr("OrderId err");
        echo <<<'eof'
    <script>
    alert("订单id出错...");
    </script>
eof;
    exit();
}
// 订单信息
$order_info = \Cache\Order::Get($order_id);

if(!$order_info)
{
    LogErr("Order err");
    echo <<<'eof'
    <script>
    alert("订单出错...");
    </script>
eof;
    exit();
}
if(2 == $order_info->pay_status)
{
     echo <<<'eof'
    <script>
    alert("此订单已支付...");
    </script>
eof;
    exit();
}
// 顾客信息
$customer_info = \Cache\Customer::Get($order_info->customer_id);

// 店铺信息
$shop_info = \Cache\Shop::Get($order_info->shop_id);
$main_domain = Cfg::instance()->GetMainDomain();
$referer = "http://customer.$main_domain/index.html?shop_id=$order_info->shop_id&seat_id=$order_info->seat_id&customer_id=$order_info->customer_id&userid=$customer_info->userid/#/payFail?order_id=$order_id";
$ref = "http://customer.$main_domain/index.html?shop_id=$order_info->shop_id&seat_id=$order_info->seat_id&customer_id=$order_info->customer_id&userid=$customer_info->userid/#/order?order_id=$order_id";


$appid       = $shop_info->alipay_set->alipay_app_id;
$public_key  = $shop_info->alipay_set->public_key;
$private_key = $shop_info->alipay_set->private_key;

if(!$appid || !$public_key || !$private_key)
{
    LogErr("order err, order_id:[$order_id]");
    $msg = "商家暂未开通支付宝支付功能,请选择其他选择方式。";
    require("tips_box.php");
    exit(0); //return errcode::WXPLAY_NO_SUPPORT;
}


$content = (object)array();
$attach = (object)array("order_id"=>$order_id);
$out_trade_no = time() . "_" . $order_info->order_id;
$content->out_trade_no = $out_trade_no;                       //商户订单号
$content->total_amount = $order_info->order_payable;          //订单总金额
$content->subject      = $shop_info->shop_name;               //订单标题
$content->product_code = 'QUICK_WAP_WAY';                     //销售产品码，商家和支付宝签约的产品码。该产品请填写固定值：QUICK_WAP_WAY
$content->body         = json_encode($attach);                //对交易或商品的描述
$content = json_encode($content);
$aop = new AopClient ();
$aop->appId              = $appid;
$aop->rsaPrivateKey      = $private_key;
$aop->alipayrsaPublicKey = $public_key;
$aop->apiVersion         = '1.0';
$aop->signType           = 'RSA2';
$aop->postCharset        = 'UTF-8';
$aop->format             = 'json';
$request = new AlipayTradeWapPayRequest();
$request->setBizContent($content);
$request->setNotifyUrl(Cfg::instance()->alipay->alipay_notify_url);
$request->setReturnUrl($referer);
$result = $aop->pageExecute($request);
LogDebug($result);
?>


<body  style="width: 100%;">
<?php
require("aly_pay_page.php");
?>
<?php echo $result; ?>
</body>


<!-- <form id='alipaysubmit' name='alipaysubmit' action='https://openapi.alipay.com/gateway.do?charset=UTF-8' method='POST'>
<input type='hidden' name='biz_content' value='{"out_trade_no":"1526538819_SL12261","total_amount":1.01,"subject":"testshop","product_code":"QUICK_WAP_WAY","body":"{\"order_id\":\"SL12261\"}"}'/>
<input type='hidden' name='app_id' value='2018031302365379'/>
<input type='hidden' name='version' value='1.0'/>
<input type='hidden' name='format' value='json'/>
<input type='hidden' name='sign_type' value='RSA2'/>
<input type='hidden' name='method' value='alipay.trade.wap.pay'/>
<input type='hidden' name='timestamp' value='2018-05-17 14:33:39'/>
<input type='hidden' name='alipay_sdk' value='alipay-sdk-php-20161101'/>
<input type='hidden' name='notify_url' value='http://alipay.jzzwlcm.com/notify.php'/>
<input type='hidden' name='charset' value='UTF-8'/>
<input type='hidden' name='sign' value='cwF0sD592f4aYFje/wVHkNQrIxnsYveEss21Z3ttobsymx0hov9dCYSdy6Njy911oOhvtXemfnFMD0TBGrno5UGaRy5XfJuoJ50MEZ1yE1nHhuQV5nZXy/OGbgsstjnNt8id0l5vNd1P7PcgD8yE8FQvjJFzEWLxZs8QVQeJwzOjhx7xmViP9vcuvyYSD7XhOLnPhPT78V+QVymtv9XSfumBuNmMxED06wibbi4DcjuF032BT3GTgHNZ4t27/p5Bt8Bfj0IslcZsYX3dkvQFnEwcBopdrNUM8TAD0vVF2E+FF9divTL5W9T/j5hvdMVFqt0s38MyiHtWVTacrE3vBQ=='/>
<input type='submit' value='ok' style='display:none;''>
</form>
<script>document.forms['alipaysubmit'].submit();</script> -->
<script type="text/javascript">
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