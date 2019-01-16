
<?php
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once("current_dir_env.php");
require_once("AlipayUtil.php");
require_once("AopClient.php");
require_once("cache.php");
require_once("SignData.php");
require_once("AlipayTradePrecreateRequest.php");
require_once("redis_pay.php");

$_ = $_REQUEST;
LogDebug($_);
function OrderQrAlipay(&$resp)
{
     $_ = $GLOBALS["_"];
    $order_id = $_['order_id'];
    $token    = $_['token'];
    $price    = (float)$_['price'];
    LogDebug($_);
    if(!$order_id)
    {
        LogErr("OrderId err");
        return errcode::PARAM_ERR;
    }
    if($price <= 0)
    {
        LogErr("price err:[$price]");
        return errcode::PLAY_NOT_ZERO;
    }
    // 订单信息
    $order_info = \Cache\Order::Get($order_id);

    if(!$order_info)
    {
        LogErr("Order err");
        return errcode::PARAM_ERR;
    }
    if(NewOrderStatus::PAY == $order_info->pay_status)
    {
        LogErr("Order Status err");
        return errcode::ORDER_ST_PAID;
    }
    if($order_info->order_payable < $price)
    {
        LogErr("price err");
        return errcode::FEE_MONEY_ERR;
    }
    // 店铺信息
    $shop_info = \Cache\Shop::Get($order_info->shop_id);

    $appid       = $shop_info->alipay_set->alipay_app_id;
    $public_key  = $shop_info->alipay_set->public_key;
    $private_key = $shop_info->alipay_set->private_key;

    if(!$appid || !$public_key || !$private_key)
    {
        LogErr("order err, order_id:[$order_id]");
        return errcode::ALIPAYPLAY_NO_SUPPORT;
    }

    $content = (object)array();
    $goods_detail = array();
    foreach ($order_info->food_list as $item)
    {
        $good = (object)array();
        $good->goods_id   = $item->food_id;                   //商品的编号
        $good->goods_name = $item->food_name;                 //商品名称
        $good->quantity   = $item->food_num;                  //商品数量
        $good->price      = $item->food_price;                //商品单价，单位为元
        array_push($goods_detail, $good);
    }
    $attach = (object)array("order_id"=>$order_info->order_id,"token"=>$token);
    $out_trade_no = time() . "_" . $order_info->order_id;
    $content->out_trade_no = $out_trade_no;                       //商户订单号
    $content->total_amount = $price;                              //订单总金额
    $content->subject      = $shop_info->shop_name;               //订单标题
    $content->goods_detail = $goods_detail;                       //订单包含的商品列表信息.Json格式.
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
    $request = new AlipayTradePrecreateRequest();
    $request->setBizContent($content);
    $request->setNotifyUrl(Cfg::instance()->alipay->alipay_notify_url);
    $result = $aop->execute($request);
    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
    $resultCode = $result->$responseNode->code;
    LogDebug($result);
    if(empty($resultCode)||$resultCode != 10000){
        LogErr("QRCODE err");
        return errcode::PARAM_ERR;
    }
    $redis = new \DaoRedis\Pay();
    $info = new \DaoRedis\PayEntry();
    $info->order_id       = $order_info->order_id;
    $info->trade_no       = $out_trade_no;
    $ret = $redis->Save($info);
    if(0 < $ret)
    {
        LogErr("redis save err");
        return $ret;
    }
    $resp = (object)array(
        'url' => $result->$responseNode->qr_code
    );
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();

$ret = OrderQrAlipay($resp);


$info = (object)array(
    'ret' => $ret,
    'msg'  => errcode::toString($ret),
    'data' => $resp
);

//echo json_encode($info);
// 允许跨域访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Content-Type: text/html; charset=utf-8');
echo json_encode($info);
?>

