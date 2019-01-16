<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_order.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_stat_vendor_order_byday.inc");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

function GetOrderList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_id        = $_['vendor_id'];
    $pay_way          = $_['pay_way'];
    $begin_time       = $_['begin_time'];
    $end_time         = $_['end_time'];
    $vendor_order_id  = $_['vendor_order_id'];
    $shop_id          = $_['shop_id'];
    //$order_status     = $_['order_status'];
    $page_no          = $_['page_no'];
    $page_size        = $_['page_size'];
    if(!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if(!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    $order_status_list = [VendorOrderStatus::PAY,VendorOrderStatus::REFUND];

    $order_mgo  = new VendorMgo\VendorOrder;
    $stat_order = new VendorMgo\StatVendorOrder;
    $aisle_mgo  = new VendorMgo\Aisle;
    $total      = 0;
    $order_list = $order_mgo->GetAllList(
        [
            'shop_id'           => $shop_id,
            'pay_way'           => $pay_way,
            'vendor_id'         => $vendor_id,
            'vendor_order_id'   => $vendor_order_id,
            'begin_time'        => $begin_time,
            'end_time'          => $end_time,
            'order_status_list' => $order_status_list
        ],
        $page_size,
        $page_no,
        [],
        $total
    );
   foreach ($order_list as &$v)
   {
       foreach ($v->goods_list as &$g)
       {
           $info          = $aisle_mgo->QueryById($g->aisle_id);
           $g->aisle_name = $info->aisle_name;
       }
   }
    $stat_order->QueryByDayAll(
        [
            'shop_id'   => $shop_id,
            'day'       => date('Ymd',time()),
        ],
        $today_all
    );
    $stat_order->QueryByDayAll(
        [
            'shop_id'   => $shop_id,
            'begin_day' => date('Ymd',time()-30*24*60*60),
            'end_day'   => date('Ymd',time()),
        ],
        $tomoth_all
    );
    $data = [];
    $data['today_num']     = $today_all['order_num'];
    $data['today_money']   = $today_all['all_money'];
    $data['tomoth_num']    = $tomoth_all['order_num'];
    $data['tomoth_money']  = $tomoth_all['all_money'];
    $resp = (object)[
        'data'        => $data,
        'order_list'  => $order_list,
        'total'       => $total,
        'page_size'   => $page_size,
        'page_no'     => $page_no
    ];

    LogInfo("--ok--");
    return 0;
}

function GetOrderInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_order_id        = $_['vendor_order_id'];
     if(!$vendor_order_id)
     {
         LogDebug('no vendor order id');
         return errcode::PARAM_ERR;
     }


     $order_mgo  = new VendorMgo\VendorOrder;
     $vendor_mgo = new VendorMgo\Vendor;
     $aisle_mgo  = new VendorMgo\Aisle;

    $info          = $order_mgo->QueryById($vendor_order_id);
    $vendor_info   = $vendor_mgo->QueryById($info->vendor_id);
    $all_goods_num = 0;
    foreach ($info->goods_list as &$v)
    {
        $aisle_info    = $aisle_mgo->QueryById($v->aisle_id);
        $v->aisle_name = $aisle_info->aisle_name;
        $v->all_money  = $v->goods_price * $v->goods_num;
        $all_goods_num += $v->goods_num;
    }
    $info->vendor_num    = $vendor_info->vendor_num;
    $info->goods_all_num = $all_goods_num;

    $resp = (object)[
        'info'  => $info,
    ];

    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();

if(isset($_["get_order_list"]))
{
    $ret = GetOrderList($resp);
}elseif(isset($_["get_order_info"]))
{
    $ret = GetOrderInfo($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

