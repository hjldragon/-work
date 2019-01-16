<?php
ini_set('date.timezone','Asia/Shanghai');
require_once("current_dir_env.php");
require_once "WxUnifiedorder.php";
require_once("cache.php");
require_once("page_util.php");
require_once("mgo_seat.php");
require_once("redis_pay.php");
require_once("cfg.php");
require_once("mgo_menu.php");


// test test test test test test test
// exit(0);


$_ = $_REQUEST;
$order_id = $_['order_id'];
$vendor   = $_['vendor'];
//LogDebug($_);
if(!$order_id)
{
    LogErr("OrderId err");
    $msg = "订单出错...";
    require("tips_box.php");
    exit();
}
// 订单信息
$order_info = \Cache\Order::Get($order_id);
// 顾客信息
$customer_info = \Cache\Customer::Get($order_info->customer_id);
if($vendor)
{
    $module = "vendor";
}
else
{
    $module = "customer";
}
$main_domain = Cfg::instance()->GetMainDomain();
$param->referer = "http://$module.$main_domain/index.html?shop_id=$order_info->shop_id&seat_id=$order_info->seat_id&customer_id=$order_info->customer_id&userid=$customer_info->userid/#/payFail?order_id=$order_id";
$param->ref = "http://$module.$main_domain/index.html?shop_id=$order_info->shop_id&seat_id=$order_info->seat_id&customer_id=$order_info->customer_id&userid=$customer_info->userid/#/order?order_id=$order_id";

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

 $need_food_list = [];
 //因为一个订单中相同的菜品可能会存在多个(打包,赠送情况)
 foreach ($order_info->food_list as $v)
 {
     $need_food_list[$v->food_id]->food_num += $v->food_num;
     $need_food_list[$v->food_id]->food_id   = $v->food_id;
 }
 // 检查餐品库存够不够
 $food = PageUtil::CheckFoodStockNum($order_info->shop_id, $need_food_list);

 foreach ($food as $v) {
     $food_id   = $v->food_id;
     $food_name = $v->food_name;
 }

 if(count($food) > 0){
     $msg = $food_name."库存不足";
     require("tips_box.php");
     exit();
 }

$mgo = new \DaoMongodb\Seat();
$seat_info = $mgo->GetSeatById($order_info->seat_id);

$req   = \Pub\Wx\Util::GetOpenid();
$openid = $req->openid;

// 用户微信信息
//$weixin_info = \Cache\Weixin::GetUser($customer_info->userid);
// 店铺信息
$shop_info = \Cache\Shop::Get($order_info->shop_id);

$attach = (object)array("order_id"=>$order_info->order_id);
if(!$param->attach)
{
    $param->attach = json_encode($attach);
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
if(!$sub_mch_id)
{
    LogErr("order err, order_id:[$order_id]");
    $msg = "商家暂未开通微信支付功能,请选择其他选择方式。";
    require("tips_box.php");
    exit(0); //return errcode::WXPLAY_NO_SUPPORT;
}

$unifiedorder = new \Pub\Wx\Unifiedorder();

//应付价格转单位分
$total_fee = $order_info->order_payable*100;
if($total_fee <= 0)
{
    LogErr("order price err, order_id:[$order_id]");
    $msg = "订单价格必须大于0..";
    require("tips_box.php");
    exit(0);
}
$out_trade_no = time() . "_" . $order_info->order_id;
$unifiedorder->SetParam('body', (string)$shop_info->shop_name);                 // 商品描述
$unifiedorder->SetParam('attach', $param->attach);                              // 附加数据
$unifiedorder->SetParam('out_trade_no', $out_trade_no);                         // 商户订单号
$unifiedorder->SetParam('sub_mch_id', (string)$sub_mch_id);                     // 子商户号
$unifiedorder->SetParam('notify_url', \Pub\Wx\Cfg::WX_URL_NOTIFY_URL);              // 通知地址
$unifiedorder->SetParam('total_fee', (int)$total_fee);                          // 总金额
$unifiedorder->SetParam('openid', $openid);

$xml = $unifiedorder->Submit();
$unifiedorder_ret = \Pub\Wx\Util::XmlToJson($xml);
$unifiedorder_ret = json_decode($unifiedorder_ret);
//LogDebug($unifiedorder_ret);
if("SUCCESS" != $unifiedorder_ret->return_code)
{
    LogErr("mch_id[$sub_mch_id] err,[$unifiedorder_ret->return_msg]");
    $msg = "商家暂未开通微信支付功能,请选择其他选择方式。";
    require("tips_box.php");
    exit(0);
}
$redis = new \DaoRedis\Pay();
$info = new \DaoRedis\PayEntry();
$info->order_id       = $order_id;
$info->out_trade_no   = $out_trade_no;
$ret = $redis->Save($info);
if(0 < $ret)
{
    LogErr("redis save err");
    return $ret;
}
$tmp = [
    "appId"     => (string)$unifiedorder_ret->appid,
    "signType"  => "MD5",
    "package"   => "prepay_id={$unifiedorder_ret->prepay_id}",
    "timeStamp" => (string)time(),
    "nonceStr"  => md5(rand()),
];
$tmp["paySign"] = \Pub\Wx\Util::GetSign($tmp);
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
if($vendor)
{
    require("vendor_pay_page.php");
}
else
{
    require("wx_pay_page.php");
}


// $param = urlencode(json_encode((object)[
//     "shop_name"    => $shop_info->shop_name,
//     "seat_name"    => $seat_info->seat_name,
//     "customer_num" => $order_info->customer_num,
//     "order_id"     => $order_info->order_id,
//     "order_fee"    => $order_info->order_fee
// ]));
?>
