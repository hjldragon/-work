<?php
/*
 * [Rocky 2017-05-12 20:16:27]
 * 订单信息保存类
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_record.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_order.inc");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

//自动售货机下单<<<<<<<公众号
function SaveVendorOrder(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }


    $vendor_id         = $_['shop_id'];
    $customer_id       = $_['customer_id'];
    $order_fee         = $_['food_price_all'];
    $pay_way           = $_['pay_way'];
    $food_list         = json_decode($_['food_list']);

    if(!$vendor_id)
    {
        LogDebug('no vendor id');
        return errcode::PARAM_ERR;
    }

    $vendor_order_id = \DaoRedis\Id::GenVendorOrderId();
    $vendor_mgo  = new VendorMgo\Vendor;
    $order_entry = new VendorMgo\VendorOrderEntry;
    $order_mgo   = new VendorMgo\VendorOrder;

    $vendor_info = $vendor_mgo->QueryById($vendor_id);
    $goods_all   = [];
    foreach ($food_list as &$v)
    {
        $goods                 = (object)array();
       $goods->vendor_goods_id = $v->food_id;
       $goods->goods_name      = $v->food_name;
       $goods->goods_price     = $v->food_price;
       $goods->goods_num       = $v->food_num;
       array_push($goods_all,$goods);
    }

    $order_entry->vendor_order_id = $vendor_order_id;
    $order_entry->order_fee       = $order_fee;
    $order_entry->pay_way         = $pay_way;
    $order_entry->customer_id     = $customer_id;
    $order_entry->vendor_id       = $vendor_id;
    $order_entry->shop_id         = $vendor_info->shop_id;
    $order_entry->order_time      = time();
    $order_entry->goods_list      = $goods_all;
    $order_entry->order_status    = VendorOrderStatus::NOPAY;

    $ret  = $order_mgo->Save($order_entry);
    if (0 != $ret)
    {
            LogErr("vendor order Save err");
            return errcode::SYS_ERR;
    }

    $resp = (object)[
        'order_id' => $vendor_order_id
    ];

    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_['save']))
{
    $ret = SaveVendorOrder($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

