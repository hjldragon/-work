<?php
ini_set('date.timezone','Asia/Shanghai');
require_once("current_dir_env.php");
require_once "WxUnifiedorder.php";
require_once("cache.php");
require_once("const.php");
require_once("redis_pay.php");
require_once("page_util.php");

// 被动扫描支付
function Micropay(&$resp)
{
    $_ = $_REQUEST;
    $order_id  = $_['order_id'];
    $auth_code = $_['auth_code'];
    $price     = (float)$_['price'];
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
        LogErr("order err, order_id:[$order_id]");
        return errcode::ORDER_NOT_EXIST;
    }
    if(NewOrderStatus::PAY == $order_info->pay_status)
    {
        LogErr("Order had play");
        return errcode::ORDER_ST_PAID;
    }
    if($order_info->order_payable < $price)
    {
        LogErr("price err");
        return errcode::FEE_MONEY_ERR;
    }
    // 店铺信息
    $shop_info = \Cache\Shop::Get($order_info->shop_id);
    if(!$shop_info)
    {
        LogErr("order err, order_id:[$order_id]");
        return errcode::SHOP_NOT_WEIXIN;
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
        return errcode::WXPLAY_NO_SUPPORT;
    }

    $unifiedorder = new \Pub\Wx\Unifiedorder();
    $attach = (object)array("order_id"=>$order_info->order_id);
    if(!$param->attach)
    {
        $param->attach = json_encode($attach);
    }

    //应付价格转单位分
    $total_fee = $price*100;
    $out_trade_no = time() . "_" . $order_id;
    $unifiedorder->SetParam('auth_code', $auth_code);                   // 授权码
    $unifiedorder->SetParam('body', $shop_info->shop_name);             // 商品描述
    $unifiedorder->SetParam('attach', $param->attach);                  // 附加数据
    $unifiedorder->SetParam('out_trade_no', $out_trade_no);             // 商户订单号
    $unifiedorder->SetParam('sub_mch_id', (string)$sub_mch_id);         // 子商户号
    $unifiedorder->SetParam('total_fee', (int)$total_fee);              // 总金额
    $xml = $unifiedorder->SubmitMicropay();
    $play_ret = \Pub\Wx\Util::FromXml($xml);
    if($play_ret['return_code'] != 'SUCCESS')
    {
        LogErr("order play err, order_id:[$order_id], msg:" . print_r($play_ret, true));
        return errcode::PAY_ERR;
    }
    LogDebug("query empty, play_ret:" . print_r($play_ret, true));
    $redis = new \DaoRedis\Pay();
    $info = new \DaoRedis\PayEntry();
    $info->order_id       = $order_id;
    $info->transaction_id = $play_ret['transaction_id'];
    $info->out_trade_no   = $out_trade_no;
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
        return 0;
    }
    else
    {
        LogDebug("query empty, play_ret:" . print_r($play_ret, true));
    }

    if($play_ret['result_code'] != 'SUCCESS')
    {
        if($play_ret['err_code'] == 'USERPAYING')
        {
            LogInfo("need passwd, order_id:[$order_id]");
            return errcode::PAY_NEED_PASSWD;
        }
        LogErr("order play err, order_id:[$order_id], msg:" . print_r($play_ret, true));
        return errcode::PAY_ERR;
    }
    // 支付成功
    return 0;
}

$resp = (object)[];
$ret = Micropay($resp);
$html = json_encode((object)array(
    'ret'   => $ret,
    'msg'  => errcode::toString($ret),
    'data'  => $resp
));
// 允许跨域访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Content-Type: text/html; charset=utf-8');
echo $html;
?>


