<?php
/*
 * [Rocky 2017-05-12 20:16:27]
 * 订单信息保存类
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_order.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_stat_vendor_order_byday.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_stat_vendor_byday.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_stat_vendor_goods_byday.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("mgo_shop.php");
use Pub\Vendor\Mongodb as VendorMgo;

class OrderUpdate{
//微信支付里面的支付流程
static function SavePayOrder($order_id)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }


    $vendor_order_id    = $order_id;

    if(!$vendor_order_id)
    {
        LogDebug('no vendor id');
        return errcode::PARAM_ERR;
    }

    $vendor_mgo  = new VendorMgo\Vendor;
    $vendor_entry= new VendorMgo\VendorEntry;
    $aisle_entry = new VendorMgo\AisleEntry;
    $aisle_mgo   = new VendorMgo\Aisle;
    $order_entry = new VendorMgo\VendorOrderEntry;
    $order_mgo   = new VendorMgo\VendorOrder;
    $goods_mgo   = new VendorMgo\VendorGoods;
    $stat_order  = new VendorMgo\StatVendorOrder;
    $stat_vendor = new VendorMgo\StatVendor;
    $stat_goods  = new VendorMgo\StatVendorGoods;


    $order_info  = $order_mgo->QueryById($vendor_order_id);
    //货道库存的减少,并且如果无货改变设备和货道状态
    foreach ($order_info->goods_list as &$v)
    {
        //更新每个货道库存信息
         $aisle_info             = $aisle_mgo->QueryById($v->aisle_id);
         $new_goods_num          = $aisle_info->goods_num - $v->goods_num;
         $aisle_entry->aisle_id  = $v->aisle_id;
         $aisle_entry->goods_num = $new_goods_num;
        if($new_goods_num == 0)
        {
            $aisle_entry->stockout_time = time();
         }
         $a_ret = $aisle_mgo->Save($aisle_entry);
        if (0 != $a_ret)
        {
            LogErr("aisle num update err");
            return errcode::SYS_ERR;
        }
        //发送给前端通知信息已放下商品<<<<<<<<<<<占无,需要和外包对接通知消息等
        //如果货道库存变成了0就改变该设备的状态变为缺货设备
         if($new_goods_num == 0)
         {
             $vendor_entry->vendor_id     = $aisle_info->vendor_id;
             $goods_info = $goods_mgo->GetVendorGoodsById($v->vendor_goods_id);
             if($goods_info->goods_stock == 0)//如果该商品库存是0就变成断货
             {
                 $vendor_entry->vendor_status = VendorStatus::OUT;
             }else{
                 $vendor_entry->vendor_status = VendorStatus::STOCKOUT;//变成缺货
             }
             $v_ret = $vendor_mgo->Save($vendor_entry);
             if (0 != $v_ret)
             {
                 LogErr("vendor save err");
                 return errcode::SYS_ERR;
             }
         }
    }

    //支付成功后的订单信息
    $order_entry->vendor_order_id = $vendor_order_id;
    $order_entry->paid_price      = $order_info->order_fee;//实际支付金额
    $order_entry->pay_time        = time();
    $order_entry->order_status    = VendorOrderStatus::PAY;

    $ret  = $order_mgo->Save($order_entry);
    if (0 != $ret)
    {
        LogErr("vendor order pay err");
        return errcode::SYS_ERR;
    }
    //统计支付的数据统计
    //1.先统计订单表<<<<<<平台统计
    $all_num     = 0;
    foreach ($order_info->goods_list as &$g)
    {
        $all_num += $g->goods_num;
    }
    $day = date('Ymd',time());
    $stat_order->SellNumAdd(
        $order_info->shop_id,
        $day,
        [
            'all_money' => $order_info->order_fee,
            'order_num' => 1,
            'goods_num' => $all_num
        ]);
    //2.统计设备数据<<<<<<<用于公众号统计
    $stat_vendor->SellNumAdd(
        $order_info->shop_id,
        $order_info->vendor_id,
        [
            'all_money' => $order_info->order_fee,
            'day'       => $day
        ]);
    //3.商品数据统计<<<<<用于公众号统计
    foreach ($order_info->goods_list as &$g1)
    {
        $stat_goods->SellNumAdd(
            $order_info->shop_id,
            $g1->vendor_goods_id,
            [
                'all_money' => $g1->goods_num*$g1->goods_price,
                'all_num'   => $g1->goods_num,
                'day'       => $day
            ]);
    }

    return 0;
}

}
?>

