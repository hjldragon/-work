<?php
ini_set('date.timezone','Asia/Shanghai');
require_once("current_dir_env.php");
require_once("const.php");
require_once("page_util.php");
require_once("AlipayUtil.php");
require_once("AopClient.php");
require_once("cache.php");
require_once("SignData.php");
require_once("AlipayTradeQueryRequest.php");
require_once("redis_pay.php");
require_once("mgo_order.php");

// 订单查询
function PayQuery(&$resp)
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
    // $redis = new \DaoRedis\Pay();
    // $order = $redis->Get($order_id);
    // if(!$order || !$order->out_trade_no)
    // {
    //     LogErr("OrderInfo or status err");
    //     return errcode::ORDER_NOT_EXIST;
    // }
    // if(1 == $order->is_pay)
    // {
    //     $resp = (object)array(
    //         "order" => $order
    //     );
    //     return 0;
    // }
    // // 订单信息
    // $order_info = \Cache\Order::Get($order_id);
    // if(!$order_info)
    // {
    //     LogErr("order err, order_id:[$order_id]");
    //     return errcode::ORDER_NOT_EXIST;
    // }

    // // 店铺信息
    // $shop_info = \Cache\Shop::Get($order_info->shop_id);

    // $appid       = $shop_info->alipay_set->alipay_app_id;
    // $public_key  = $shop_info->alipay_set->public_key;
    // $private_key = $shop_info->alipay_set->private_key;

    // if(!$appid || !$public_key || !$private_key)
    // {
    //     LogErr("order err, order_id:[$order_id]");
    //     return errcode::ALIPAYPLAY_NO_SUPPORT;
    // }

    // $content = (object)array();
    // $content->out_trade_no = $order->out_trade_no;              //商户订单号
    // $content = json_encode($content);

    // $aop = new AopClient ();
    // $aop->appId              = $appid;
    // $aop->rsaPrivateKey      = $private_key;
    // $aop->alipayrsaPublicKey = $public_key;
    // $aop->apiVersion         = '1.0';
    // $aop->signType           = 'RSA2';
    // $aop->postCharset        = 'UTF-8';
    // $aop->format             = 'json';

    // $request = new AlipayTradeQueryRequest();
    // $request->setBizContent($content);
    // $request->setNotifyUrl(\Alipay\Cfg::ALIPAY_NOTIFY_URL);
    // $result = $aop->execute($request);
    // $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
    // $resultCode = $result->$responseNode->code;
    // $trade_status = $result->$responseNode->trade_status; //交易状态：WAIT_BUYER_PAY（交易创建，等待买家付款）、TRADE_CLOSED（未付款交易超时关闭，或支付完成后全额退款）、TRADE_SUCCESS（交易支付成功）、TRADE_FINISHED（交易结束，不可退款）
    // if(empty($resultCode) || $resultCode != 10000 || $trade_status != "TRADE_SUCCESS"){
    //     LogErr("$order_id:[$result->$responseNode->sub_msg]");
    //     return errcode::PARAM_ERR;
    // }

    // $pay = new \DaoRedis\PayEntry();
    // $pay->order_id       = $order_id;
    // $pay->out_trade_no   = $result->$responseNode->out_trade_no;
    // $pay->is_pay         = 1;
    // $pay->pay_price      = $result->$responseNode->total_amount;
    // $save = $redis->Save($pay);
    // if(0 != $save)
    // {
    //     LogErr("Save err");
    //     return errcode::SYS_ERR;
    // }
    // $mgo = new \DaoMongodb\Order;
    // $entry = new \DaoMongodb\OrderEntry;
    // $paid_price = $result->$responseNode->total_amount;
    // $entry->order_id         = $order_id;
    // $entry->order_status     = OrderStatus::PAID;
    // $entry->pay_way          = PayWay::APAY;
    // $entry->pay_status       = PayStatus::PAY;
    // $entry->paid_price       = $paid_price;
    // $entry->order_waiver_fee = $order_info->order_payable - $paid_price;
    // $entry->pay_time         = time();
    // $order_ret = $mgo->Save($entry);
    // if(0 != $order_ret)
    // {
    //     LogErr("Save err");
    //     return errcode::SYS_ERR;
    // }
    // // 更新缓存
    // \Cache\Order::Clear($order_id);
    // // 更新餐品日销售量
    // // 增加餐品售出数
    // PageUtil::UpdateFoodDauSoldNum($order_id);
}


$ret = -1;
$resp = (object)array();

$ret = PayQuery($resp);

$info = (object)array(
    'ret' => $ret,
    'data' => $resp
);

// 允许跨域访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Content-Type: text/html; charset=utf-8');
echo json_encode($info);
?>


