<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_order.php");


//Permission::EmployeePermissionCheck(
//     Permission::CHK_ORDER_R
//);



function GetOrderInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id = $_['order_id'];

    $mgo = new \DaoMongodb\Order;
    $info = $mgo->GetOrderById($order_id);
    $info->seat = \Cache\Seat::Get($info->seat_id);
    if($info->seat)
    {
        $info->seat->seat_price = Util::FenToYuan($info->seat->seat_price);
    }
    $info->customer = \Cache\Customer::Get($info->customer_id);

    $resp = (object)array(
        'info' => $info
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetOrderList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id   = $_['order_id'];
    $seat_id    = $_['seat_id'];
    $begin_time = $_["begin_time"];
    $end_time   = $_["end_time"];

    if(!$begin_time)
    {
        $begin_time = date("Y-m-d");
    }
    if(!$end_time)
    {
        $end_time = $begin_time;
    }
    LogDebug("begin_time:$begin_time, end_time:$end_time");
    $begin_time_sec = strtotime($begin_time);
    $end_time_sec = strtotime($end_time) + 3600*24 - 1; // 一天的最后一秒

    $shop_id = \Cache\Login::GetShopId();
    LogDebug("shop_id:[$shop_id]");

    $mgo = new \DaoMongodb\Order;
    $order_list = $mgo->GetOrderList(
        [
            'shop_id'    => $shop_id,
            'order_id'   => $order_id,
            'seat_id'    => $seat_id,
            'begin_time' => $begin_time_sec,
            'end_time'   => $end_time_sec
        ],
        ["food_list"=>0],
        ["_id"=>-1]
    );

    //取餐桌信息
    foreach($order_list as $key => &$value)
    {
        $value->seat = \Cache\Seat::Get($value->seat_id);
        if(!$value->seat)
        {
            $value->seat = [];
        }
        else
        {
            $value->seat->seat_price = Util::FenToYuan($value->seat->seat_price);
        }
    }

    $resp = (object)array(
        'list' => $order_list
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
function GetOrderAllList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id     = $_['order_id'];
    $seat_id      = $_['seat_id'];
    $is_invoicing = $_['is_invoicing'];
    $dine_way     = $_['dine_way'];
    $pay_way      = $_['pay_way'];
    $order_status = $_['order_status'];
    $begin_time   = $_["begin_time"];
    $end_time     = $_["end_time"];
    $page_size    = $_['page_size'];
    $page_no      = $_['page_no'];
    if(!$page_size)
    {

        $page_size = 7;//如果没有传默认10条
    }
    if(!$page_no)
    {
        $page_no = 1; //第一页开始
    }
/*    if(!$begin_time)
    {
        $begin_time = date("Y-m-d");
    }
    if(!$end_time)
    {
        $end_time = $begin_time;
    }
    LogDebug("begin_time:$begin_time, end_time:$end_time");
    $begin_time_sec = strtotime($begin_time);
    $end_time_sec = strtotime($end_time) + 3600*24 - 1; // 一天的最后一秒*/

    $shop_id = \Cache\Login::GetShopId();
    LogDebug("shop_id:[$shop_id]");

    $mgo = new \DaoMongodb\Order;
    $order_list = $mgo->GetOrderAllList(
        [
            'shop_id'      => $shop_id,
            'order_id'     => $order_id,
            'seat_id'      => $seat_id,
            'is_invoicing' => $is_invoicing,
            'dine_way'     => $dine_way,
            'pay_way'      => $pay_way,
            'order_status' => $order_status,
//            'begin_time'   => $begin_time_sec,
//            'end_time'     => $end_time_sec,
        ],
        ["_id"=>-1],
        $page_size,
        $page_no
    );
    LogDebug($order_list);
    //取餐桌信息
    foreach($order_list as $key => &$value)
    {
        $value->seat = \Cache\Seat::Get($value->seat_id);
        if(!$value->seat)
        {
            $value->seat = [];
        }
        else
        {
            $value->seat->seat_price = Util::FenToYuan($value->seat->seat_price);
        }
    }

    $resp = (object)array(
        'order_list' => $order_list
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
// 订单统计
function GetOrderStat(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $is_byday   = $_["byday"];
    $is_bymon   = $_["bymon"];
    $is_byyear  = $_["byyear"];
    $begin_time = $_["begin_time"];
    $end_time   = $_["end_time"];

    if("" == $begin_time)
    {
        $begin_time = date("Y-m-d");
    }
    if("" == $end_time)
    {
        $end_time = $begin_time;
    }
    LogDebug("begin_time:[$begin_time], end_time:[$end_time]");
    $begin_time_sec = strtotime($begin_time);
    $end_time_sec = strtotime($end_time) + 3600*24 - 1; // 一天的最后一秒

    $shop_id = \Cache\Login::GetShopId();
    LogDebug("shop_id:[$shop_id]");

    $mgo = new \DaoMongodb\Order;
    $order_list = $mgo->GetOrderList(
        [
            'shop_id'    => $shop_id,
            'begin_time' => $begin_time_sec,
            'end_time'   => $end_time_sec
        ],
        [
            "order_time"     => 1,
            "customer_num"   => 1,
            "food_price_all" => 1,
            "food_num_all"   => 1,
        ],
        ["lastmodtime" => 1]
    );
    LogDebug($order_list);

    // 按天统计
    $byday = [];
    if($is_byday)
    {
        foreach($order_list as $key => &$value)
        {
            $order_day = date("Y-m-d", $value->order_time);
            $data = &$byday[$order_day];
            $data['order_num']++;
            $data['food_num'] += $value->food_num_all;
            $data['order_fee'] += $value->food_price_all;
            $data['persion_num'] += $value->customer_num;
        }
        foreach($byday as $k => &$v)
        {
            $v['order_day'] = $k;
        }
        usort($byday);
    }

    // 按月统计
    $bymon = [];
    if($is_bymon)
    {
        foreach($order_list as $key => &$value)
        {
            $order_mon = date("Y-m", $value->order_time);
            $data = &$bymon[$order_mon];
            $data['order_num']++;
            $data['food_num'] += $value->food_num_all;
            $data['order_fee'] += $value->food_price_all;
            $data['persion_num'] += $value->customer_num;
        }
        foreach($bymon as $k => &$v)
        {
            $v['order_mon'] = $k;
        }
        usort($bymon);
    }

    // 按年统计
    $byyear = [];
    if($is_byyear)
    {
        foreach($order_list as $key => &$value)
        {
            $order_year = date("Y", $value->order_time);
            $data = &$byyear[$order_year];
            $data['order_num']++;
            $data['food_num'] += $value->food_num_all;
            $data['order_fee'] += $value->food_price_all;
            $data['persion_num'] += $value->customer_num;
        }
        foreach($byyear as $k => &$v)
        {
            $v['order_year'] = $k;
        }
        usort($byyear);
    }

    $resp = (object)array(
        'byday' => $byday,
        'bymon' => $bymon,
        'byyear' => $byyear,
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_["orderinfo"]))
{
    $ret = GetOrderInfo($resp);
}
elseif(isset($_["orderlist"]))
{
    $ret = GetOrderList($resp);
}
elseif(isset($_["get_order_list"]))
{
    $ret = GetOrderAllList($resp);
}
elseif(isset($_["orderstat"]))
{
    $ret = GetOrderStat($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
