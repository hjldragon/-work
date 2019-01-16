<?php
ini_set('date.timezone','Asia/Shanghai');
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/weixin/WxUnifiedorder.php");
require_once("cache.php");
require_once("/www/public.sailing.com/php/page_util.php");
require_once("redis_pay.php");
require_once("cfg.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
use \Pub\Mongodb as Mgo;


// test test test test test test test
// exit(0);


$_ = $_REQUEST;

$goods_order_id = $_["goods_order_id"];
$total_price    = $_['total_price'];
$token          = $_['token'];
$history        = $_['history'];
LogDebug($_);
$body = '赛领科技';
if(!$goods_order_id)
{
    LogErr("GooodsOrderId err");
    $msg = "订单出错...";
    require("tips_box.php");
    exit();
}
$main_domain = Cfg::instance()->GetMainDomain();
$param->ref = "http://mart.$main_domain/weixin/#/orderpay?orderid=$goods_order_id&history=$history";

$param->referer = "http://mart.$main_domain/weixin/#/paystauts?orderid=$goods_order_id&price=$total_price";
$mgo  = new Mgo\GoodsOrder;
$info = $mgo->GetGoodsOrderById($goods_order_id);

// 检查商品库存够不够
$goods = \Pub\PageUtil::CheckGoodsStockNum($info->goods_list);
if(count($goods)>0){
        foreach ($goods as $v) {
        $goods_id   = $v->goods_id;
        $goods_name = $v->goods_name;
        $spec_id    = $v->spec_id;
    }
    LogErr("not enough, goods_id:[{$goods_id}]");
    $msg = $goods_name."库存不足...";
    require("tips_box.php");
    exit();
}

// 检查商品下架状态
$list = \Pub\PageUtil::CheckGoodsSaleOff($goods_list);
if(count($list)>0){
    foreach ($list as $v) {
        $goods_id   = $v->goods_id;
        $goods_name = $v->goods_name;
    }
    LogErr("sale off, goods_id:[{$goods_id}]");
    $msg = $goods_name."已下架...";
    require("tips_box.php");
    exit();
}
if(1 != $info->order_status)
{
    $msg = "此订单不能支付...";
    require("tips_box.php");
    exit();
}
$goodsinfo = [];
$data = \Pub\PageUtil::GetOrderPrice($info->goods_list, null, $goodsinfo);
if($data == null)
{
    foreach ($goodsinfo as $v) {
        $goods_id   = $v->goods_id;
        $goods_name = $v->goods_name;
    }
    LogErr("goods sepc change, goods_id:[{$goods_id}]");
    $msg = $goods_name."信息已改变...";
    require("tips_box.php");
    exit();
}
$total_fee = $data['goods_price_all'] + $info->freight_price;
LogDebug($total_fee);
if($total_price != $total_fee)
{
    LogErr("price_all error");
    $msg = "价格已变更...";
    require("tips_box.php");
    exit();
}
$entry = new Mgo\GoodsOrderEntry;
$entry->goods_order_id    = $goods_order_id;
$entry->goods_list        = $data['order_goods_list'];
$entry->goods_num_all     = $data['goods_num_all'];
$entry->goods_price_all   = $data['goods_price_all'];
$entry->rebates_price_all = $data['rebates_price_all'];
$entry->rebates           = $data['order_rebates'];
$entry->order_fee         = $total_fee;
Mgo\GoodsOrder::My()->Save($entry);


$req   = \Pub\Wx\Util::GetOpenid();
$openid = $req->openid;


$attach = (object)array("goods_order_id"=>$info->goods_order_id);
if(!$param->attach)
{
    $param->attach = json_encode($attach);
}



$unifiedorder = new \Pub\Wx\Unifiedorder();

//应付价格转单位分
$total_fee = $total_fee*100;
$out_trade_no = time() . "_" . $info->goods_order_id;
$unifiedorder->SetParam('body', $body);                                        // 商品描述
$unifiedorder->SetParam('attach', $param->attach);                              // 附加数据
$unifiedorder->SetParam('out_trade_no', $out_trade_no);                         // 商户订单号
$unifiedorder->SetParam('sub_mch_id', \Pub\Wx\Cfg::SUB_MCH_ID);                     // 子商户号
$unifiedorder->SetParam('notify_url', \Pub\Wx\Cfg::WX_URL_MART_NOTIFY);              // 通知地址
$unifiedorder->SetParam('total_fee', (int)$total_fee);                          // 总金额
$unifiedorder->SetParam('openid', $openid);

$xml = $unifiedorder->Submit();
$unifiedorder_ret = \Pub\Wx\Util::XmlToJson($xml);
$unifiedorder_ret = json_decode($unifiedorder_ret);
LogDebug($unifiedorder_ret);
if("SUCCESS" != $unifiedorder_ret->return_code)
{
    LogErr("mch_id[$sub_mch_id] err,[$unifiedorder_ret->return_msg]");
    $msg = "商家暂未开通微信支付功能,请选择其他选择方式。";
    require("tips_box.php");
    exit(0);
}
// $redis = new \DaoRedis\Pay();
// $info = new \DaoRedis\PayEntry();
// $info->order_id       = $order_id;
// $info->out_trade_no   = $out_trade_no;
// $ret = $redis->Save($info);
// if(0 < $ret)
// {
//     LogErr("redis save err");
//     return $ret;
// }
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
require("wx_pay_page.php");

?>
