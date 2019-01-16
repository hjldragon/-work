<?php
ini_set('date.timezone','Asia/Shanghai');
require_once "WxUnifiedorder.php";
require_once("cache.php");
require_once("const.php");
require_once("redis_pay.php");
require_once("page_util.php");

// 被动扫描支付
function Refund(&$resp)
{
    $_ = $_REQUEST;
    $order_id  = $_['order_id'];
    if(!$order_id)
    {
        LogErr("OrderId err");
        return errcode::PARAM_ERR;
    }
    $redis = new \DaoRedis\Pay();
    $order = $redis->Get($order_id);
    if(!$order || !$order->out_trade_no)
    {
        LogErr("OrderInfo or status err");
        return errcode::ORDER_NOT_EXIST;
    }
    // 订单信息
    $order_info = \Cache\Order::Get($order_id);
    if(!$order_info)
    {
        LogErr("order err, order_id:[$order_id]");
        return errcode::ORDER_NOT_EXIST;
    }
    
    // 店铺信息
    // $shop_info = \Cache\Shop::Get($order_info->shop_id);
    // if(!$shop_info)
    // {
    //     LogErr("order err, order_id:[$order_id]");
    //     return errcode::SHOP_NOT_WEIXIN;
    // }

    // // 兼容处理
    // $sub_mch_id = "";
    // if($shop_info->weixin_pay_set && $shop_info->weixin_pay_set->sub_mch_id)
    // {
    //     $sub_mch_id = $shop_info->weixin_pay_set->sub_mch_id;
    // }
    // if("" == $sub_mch_id && $shop_info->weixin && $shop_info->weixin->sub_mch_id)
    // {
    //     $sub_mch_id = $shop_info->weixin->sub_mch_id;
    // }
    // if("" == $sub_mch_id)
    // {
    //     LogErr("order err, order_id:[$order_id]");
    //     return errcode::WXPLAY_NO_SUPPORT;
    // }

    $unifiedorder = new \Wx\Unifiedorder();
    
    $out_refund_no = $order->out_refund_no;
    if(!$out_refund_no)
    {
        $out_refund_no = time() . "_refund_" . $order_id;
    }

    //应付价格转单位分
    $total_fee = $order_info->paid_price*100;
    $unifiedorder->SetParam('out_refund_no', $out_refund_no);               // 商户系统内部的退款单号
    $unifiedorder->SetParam('out_trade_no', $order->out_trade_no);          // 商户订单号
    //$unifiedorder->SetParam('sub_mch_id', (string)$sub_mch_id);            // 子商户号
    $unifiedorder->SetParam('total_fee', (int)$total_fee);                  // 总金额
    $unifiedorder->SetParam('refund_fee', (int)$total_fee);                 // 退款总金额
    $unifiedorder->SetParam('notify_url', \Wx\Cfg::WX_URL_REFUND_NOTIFY);   // 通知地址
    $xml = $unifiedorder->SubmitRefund();
    $play_ret = \Wx\Util::FromXml($xml);
    var_dump($play_ret);die;
    if($play_ret['return_code'] != 'SUCCESS')
    {
        LogErr("order play err, order_id:[$order_id], msg:" . print_r($play_ret, true));
        return errcode::PAY_ERR;
    }
    LogDebug("query empty, play_ret:" . print_r($play_ret, true));
    $redis = new \DaoRedis\Pay();
    $info = new \DaoRedis\PayEntry();
    $info->order_id       = $order_id;
    $info->out_trade_no   = $out_trade_no;
    $info->out_refund_no  = $out_refund_no;
    $ret = $redis->Save($info);
    if(0 < $ret)
    {
        LogErr("redis save err");
        return $ret;
    }
    // 支付成功
    return 0;
}

$resp = (object)[];
$ret  = Refund($resp);
$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
));
// 允许跨域访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Content-Type: text/html; charset=utf-8');
echo $html;
?>


