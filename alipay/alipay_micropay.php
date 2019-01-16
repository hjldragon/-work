<?php
ini_set('date.timezone','Asia/Shanghai');
require_once("current_dir_env.php");
require_once("const.php");
require_once("page_util.php");
require_once("AlipayUtil.php");
require_once("AopClient.php");
require_once("cache.php");
require_once("SignData.php");
require_once("AlipayTradePayRequest.php");
require_once("AlipayTradeQueryRequest.php");
require_once("redis_pay.php");

// 被动扫描支付
function Micropay(&$resp)
{
    $_ = $_REQUEST;
    $order_id  = $_['order_id'];
    $auth_code = $_['auth_code'];
    $price     = (float)$_['price'];
    if(!$order_id || !$auth_code)
    {
        LogErr("OrderId or auth_code err");
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
        LogErr($order_id . "Order err");
        return errcode::ORDER_NOT_EXIST;
    }
    if(NewOrderStatus::PAY == $order_info->pay_status)
    {
        LogErr($order_id . "Order had play");
        return errcode::ORDER_ST_PAID;
    }
    if($order_info->order_payable < $price)
    {
        LogErr($order_id . "price err");
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
    $attach = (object)array("order_id"=>$order_info->order_id);
    $out_trade_no = time() . "_" . $order_info->order_id;
    $content->out_trade_no = $out_trade_no;                       //商户订单号
    $content->scene        = "bar_code";                          //支付场景 条码支付，取值：bar_code 声波支付，取值：wave_code
    $content->auth_code    = $auth_code;                          //支付授权码
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

    $request = new AlipayTradePayRequest();
    $request->setBizContent($content);
    $result = $aop->execute($request);
    LogDebug($result);
    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
    $resultCode = $result->$responseNode->code;
    if(empty($resultCode)||$resultCode != 10000){
        $msg = $result->$responseNode->sub_msg;
        LogErr("order_id:[$msg]");
        // if($trade_status != "WAIT_BUYER_PAY")
        // {
        //     return errcode::PAY_NEED_PASSWD;
        // }
        return errcode::PAY_ERR;
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
    $query_ret_obj = (object)[];
    $order_ret = PageUtil::OrderQuery($order_id, $query_ret_obj);
    if(0 == $order_ret)
    {
        // 可以查询到，支付成功。
        LogDebug("play ok");
        $resp = $query_ret_obj;
    }
    return $order_ret;
}


$ret = -1;
$resp = (object)array();

$ret = Micropay($resp);

$info = (object)array(
    'ret' => $ret,
    'msg'  => errcode::toString($ret),
    'data' => $resp
);

// 允许跨域访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Content-Type: text/html; charset=utf-8');
echo json_encode($info);
?>


