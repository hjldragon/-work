<?php
ini_set('date.timezone','Asia/Shanghai');
require_once "WxUnifiedorder.php";
require_once("cache.php");
require_once("mgo_seat.php");
require_once("cfg.php");


// test test test test test test test
// exit(0);





$_ = $_REQUEST;
$order_id = $_['order_id'];
LogDebug($_);
if(!$order_id)
{
    LogErr("OrderId err");
    $msg = "订单出错...";
    require("tips_box.php");
    exit();
}
// 订单信息
$order_info = \Cache\Order::Get($order_id);

if(!$order_info)
{
    LogErr("Order err");
    $msg = "订单出错...";
    require("tips_box.php");
    exit();
}
if(2 == $order_info->pay_status)
{
    $msg = "此订单已支付...";
    require("tips_box.php");
    exit();
}
$mgo = new \DaoMongodb\Seat();
$seat_info = $mgo->GetSeatById($order_info->seat_id);

$req   = \Wx\Util::GetOpenid();
$openid = $req->openid;
// 顾客信息
$customer_info = \Cache\Customer::Get($order_info->customer_id);
// 用户微信信息
//$weixin_info = \Cache\Weixin::GetUser($customer_info->userid);
// 店铺信息
$shop_info = \Cache\Shop::Get($order_info->shop_id);

$attach = (object)array("order_id"=>$order_info->order_id);
if(!$param->attach)
{
    $param->attach = json_encode($attach);
}
if(!$param->referer)
{
    $main_domain = Cfg::instance()->GetMainDomain();
    $param->referer = "http://customer.$main_domain/index.html?shop_id=$order_info->shop_id&seat_id=$order_info->seat_id&customer_id=$order_info->customer_id&userid=$customer_info->userid/#/payFail?order_id=$order_id";
    $param->ref = "http://customer.$main_domain/index.html?shop_id=$order_info->shop_id&seat_id=$order_info->seat_id&customer_id=$order_info->customer_id&userid=$customer_info->userid/#/order?order_id=$order_id";
}

// 兼容处理
$sub_mch_id = "";
if($shop_info->weixin_pay_set && $shop_info->weixin_pay_set->sub_mch_id)
{
    $sub_mch_id = $shop_info->weixin_pay_set->sub_mch_id;
}
if("" == $sub_mch_id && $shop_info->weixin && $shop_info->weixin->sub_mch_id)
{
    $sub_mch_id = $shop_info->weixin->sub_mch_id;
}
if("" == $sub_mch_id)
{
    LogErr("order err, order_id:[$order_id]");
    $msg = "商家暂未开通微信支付功能,请选择其他选择方式。";
    require("tips_box.php");
    exit(0); //return errcode::WXPLAY_NO_SUPPORT;
}

$unifiedorder = new \Wx\Unifiedorder();

//应付价格转单位分
$total_fee = $order_info->order_payable*100;
$unifiedorder->SetParam('body', (string)$shop_info->shop_name);                 // 商品描述
$unifiedorder->SetParam('attach', $param->attach);                              // 附加数据
$unifiedorder->SetParam('out_trade_no', time() . "_" . $order_info->order_id);  // 商户订单号
$unifiedorder->SetParam('sub_mch_id', (string)$sub_mch_id);                     // 子商户号
$unifiedorder->SetParam('notify_url', \Wx\Cfg::WX_URL_NOTIFY_URL);              // 通知地址
$unifiedorder->SetParam('total_fee', (int)$total_fee);                          // 总金额
$unifiedorder->SetParam('openid', $openid);


$xml = $unifiedorder->Submit();
$unifiedorder_ret = \Wx\Util::XmlToJson($xml);
$unifiedorder_ret = json_decode($unifiedorder_ret);
if("SUCCESS" != $unifiedorder_ret->return_code)
{
    LogErr("mch_id[$sub_mch_id] err,[$unifiedorder_ret->return_msg]");
    $msg = "商家暂未开通微信支付功能,请选择其他选择方式。";
    require("tips_box.php");
    exit(0);
}
$tmp = [
    "appId"     => (string)$unifiedorder_ret->appid,
    "signType"  => "MD5",
    "package"   => "prepay_id={$unifiedorder_ret->prepay_id}",
    "timeStamp" => (string)time(),
    "nonceStr"  => md5(rand()),
];
$tmp["paySign"] = \Wx\Util::GetSign($tmp);
$pay_param = json_encode($tmp);

?>


<script type="text/javascript">
//调用微信JS api 支付
function jsApiCall()
{
    WeixinJSBridge.invoke(
        'getBrandWCPayRequest',
        <?php echo $pay_param; ?>,
        function(res){
            //WeixinJSBridge.log(res.err_msg);
            var msg = res.err_msg.split(':');
            var str = '';
            if(msg[1] == 'ok'){
                str = '支付成功';
                location.href = "<?=$param->referer?>";
            }else{
                str = '支付出错或取消';
                location.href = "<?=$param->ref?>";
            }
            // WeixinJSBridge.call('closeWindow');  // 关闭当前页面回到微信对话窗口

            var msg = document.getElementById("id_msg");
            msg.innerHTML = "正在返回订单页...";
            location.href = "<?=$param->referer?>";
        }
    );
}

function callpay()
{
    if (typeof WeixinJSBridge == "undefined"){
        if( document.addEventListener ){
            document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
        }else if (document.attachEvent){
            document.attachEvent('WeixinJSBridgeReady', jsApiCall);
            document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
        }
    }else{
        jsApiCall();
    }
}

callpay();
</script>
<?php
require("wx_pay_page.php");
?>
