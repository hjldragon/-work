<?php
/*
 * [Rocky 2018-07-23]
 * 支付相关
 *
 */
require_once("current_dir_env.php");
// require_once("page_util_v2.inc");
require_once("wx_util.inc");
require_once("WxCfg.php");
require_once("cache.php");

// Permission::PageCheck();
// $_=$_REQUEST;
// LogDebug($_);


function GetWxpayCode(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    // if(!PageUtil::IsWeixin())    <<<<<<<<<<<<<<<<<<<<
    // {
    //     LogErr("CLIENT_TYPE_ERR");
    //     return errcode::CLIENT_TYPE_ERR;
    // }
    $order_id = $_['order_id'];
    $token    = $_['token'];
    if(!$order_id || !$token)
    {
        LogErr("OrderId err");
        return errcode::PARAM_ERR;
    }
    $openid = \Cache\Login::Get($token)->openid;
    if(!$openid)
    {
        LogErr("openid err");
        return errcode::PARAM_ERR;
    }
    // 订单信息
    $order_info = \Cache\VendorOrder::Get($order_id);
    if(!$order_info)
    {
        LogErr("Order err");
        return errcode::PARAM_ERR;
    }
    if(2 == $order_info->order_status)
    {
        LogErr("Order status err");
        return errcode::ORDER_ST_PAID;
    }
    if(PlShopId::ID ==$order_info->shop_id)
    {
        $sub_mch_id = \Pub\Wx\Cfg::SUB_MCH_ID;
    }
    else
    {
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
    }

    if(!$sub_mch_id)
    {
        LogErr("order err, order_id:[$order_id]");
        return errcode::WX_NO_SUPPORT;
    }
    //应付价格转单位分
    $total_fee = $order_info->order_fee*100;
    if($total_fee <= 0)
    {
        LogErr("order price err, order_id:[$order_id]");
        return errcode::PLAY_NOT_ZERO;
    }
    // 创建微信支付所需要的数据
    $pay_param = \Wx\Util::GenPayData([
        'openid'     => $openid, // "客人的微信openid",  // 注意，openid应在客人扫码后，存入redis中，这里就不通过跳转(location:url)来获取了
        'sub_mch_id' => $sub_mch_id, // 店铺的子商户号
        'total_fee'  => $total_fee, // （单位：分）
        'attach'     => [ // 附带的数据(跟据业务需要，可以填多个字段)
            'shop_id' => $order_info->shop_id,
            'order_id' => $order_id,
            // ...
            // ...
        ],
    ]);
    if(!$pay_param)
    {
        LogErr("GenPayData err");
        return errcode::GET_PAY_INFO_ERR;
    }
    $domain = Cfg::GetPrimaryDomain();
    $pay_success_url = "http://vendor.$domain:8084/index.html?vendor_id=$order_info->vendor_id#/payFail?order_id=$order_id";//<<<<<<<<<<<<<<<<<<<正式去掉端口号
    $pay_cancel_url  = "http://vendor.$domain:8084/index.html?vendor_id=$order_info->vendor_id#/order?order_id=$order_id";


    // 支付页面模板
    $html = \Pub\PageUtil::TemplateOut("../template/wx_pay_page.tpl",
        [ // 模板文件中的$param参数
            'pay_param'       => $pay_param,
            'order_id'        => $order_id,
            'payable_fee'     => $order_info->order_fee,
            'pay_success_url' => $pay_success_url,
            'pay_cancel_url'  => $pay_cancel_url
            // 'order_status' => $orderinfo->order_status,
            // 'pay_status' => $orderinfo->pay_status,
            // 'order_time' => $orderinfo->order_time,
        ],
        ['out'=>0]
    );
    // LogDebug($html);

    $html = htmlspecialchars(($html));

    $resp = (object)array(
        'pay_code' => $html,
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if("get_wxpay_code" == $_["opr"])
{
    $ret = GetWxpayCode($resp);
}
else
{
    LogErr("param err");
    $ret = errcode::PARAM_ERR;
}

\Pub\PageUtil::HtmlOut($ret, $resp);
