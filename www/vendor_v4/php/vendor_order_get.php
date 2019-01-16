<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_order.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
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
    $order_status     = $_['order_status'];
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


    $order_mgo  = new VendorMgo\VendorOrder;
    $aisle_mgo  = new VendorMgo\Aisle;
    $total      = 0;
    $order_list = $order_mgo->GetAllList(
        [
            'shop_id'         => $shop_id,
            'pay_way'         => $pay_way,
            'vendor_id'       => $vendor_id,
            'vendor_order_id' => $vendor_order_id,
            'begin_time'      => $begin_time,
            'end_time'        => $end_time,
            'order_status'    => $order_status
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
    $resp = (object)[
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
    $info       = $order_mgo->QueryById($vendor_order_id);
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
}else{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

