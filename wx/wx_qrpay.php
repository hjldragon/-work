<?php
ini_set('date.timezone','Asia/Shanghai');
require_once("current_dir_env.php");
require_once "WxUnifiedorder.php";
require_once("cache.php");
require_once("redis_pay.php");
$_ = $_REQUEST;
function OrderQRpay(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id = $_['order_id'];
    $token    = $_['token'];
    $price    = (float)$_['price'];
    LogDebug($token);
    if(!$order_id || $price <= 0)
    {
        LogErr("OrderId err");
        return errcode::PARAM_ERR;
    }
    // 订单信息
    $order_info = \Cache\Order::Get($order_id);

    if(!$order_info)
    {
        LogErr("Order err");
        return errcode::PARAM_ERR;
    }
    if(2 == $order_info->pay_status)
    {
        LogErr("Order Status err");
        return errcode::PARAM_ERR;
    }
    // 店铺信息
    $shop_info = \Cache\Shop::Get($order_info->shop_id);
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
        return errcode::WXPLAY_NO_SUPPORT;
    }

    $unifiedorder = new \Wx\Unifiedorder();
    $attach = (object)array("order_id"=>$order_info->order_id,"token"=>$token);
    if(!$param->attach)
    {
        $param->attach = json_encode($attach);
    }
    $out_trade_no = time() . "_" . $order_info->order_id;
    //应付价格转单位分
    $total_fee = $price*100;
    $unifiedorder->SetParam('body', (string)$shop_info->shop_name);                 // 商品描述
    $unifiedorder->SetParam('attach', $param->attach);                              // 附加数据
    $unifiedorder->SetParam('out_trade_no', $out_trade_no);                         // 商户订单号
    $unifiedorder->SetParam('sub_mch_id', (string)$sub_mch_id);                     // 子商户号
    $unifiedorder->SetParam('notify_url', \Wx\Cfg::WX_URL_NOTIFY_URL);              // 通知地址
    $unifiedorder->SetParam('total_fee', (int)$total_fee);                          // 总金额


    $xml = $unifiedorder->SubmitQRpay();
    $unifiedorder_ret = \Wx\Util::XmlToJson($xml);
    $unifiedorder_ret = json_decode($unifiedorder_ret);
    if($unifiedorder_ret->result_code != 'SUCCESS')
    {
        LogErr("QRCODE err");
        return errcode::PARAM_ERR;
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
    $resp = (object)array(
        'url' => $unifiedorder_ret->code_url
    );
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();

$ret = OrderQRpay($resp);

$result = (object)array(
    'ret' => $ret,
    'data' => $resp
);

echo json_encode($result);
?>

