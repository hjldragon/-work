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
require_once("mgo_seat.php");
require_once("mgo_customer.php");
require_once ("mgo_employee.php");

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
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $order_id         = $_['order_id'];
    $is_invoicing     = $_['is_invoicing'];
    $dine_way         = $_['dine_way'];
    $pay_way          = $_['pay_way'];
    $order_status     = $_['order_status'];
    $seat_name        = $_['seat_name'];
    $order_begin_time = $_["order_begin_time"];
    $order_end_time   = $_["order_end_time"];//订单时间
    $begin_time       = $_["begin_time"];  //各种状态开始时间
    $end_time         = $_["end_time"];    //各种状态结束时间
    $page_size        = $_['page_size'];
    $page_no          = $_['page_no'];
    //排序字段
    $sort_order_id      = $_['sort_order_id'];
    $sort_order_time    = $_['sort_order_time'];
    $sort_order_fee     = $_['sort_order_fee'];
    $sort_pay_time      = $_['sort_pay_time'];
    $sort_order_payable = $_['sort_order_payable'];
    $sort_paid_price    = $_['sort_paid_price'];
    $sort_sc_time       = $_['sort_sc_time'];
    $sort_fd_time       = $_['sort_fd_time'];
    $sort_close_time    = $_['sort_close_time'];
    $sort_out_time      = $_['sort_out_time'];
    if (!$page_size)
    {
        $page_size = 7;//如果没有传默认10条
    }
    if (!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    if (!$order_begin_time && $order_end_time)
    {
        $order_begin_time = -28800; //默认时间戳开始时间
    }
    if (!$order_end_time && $order_begin_time)
    {
        $order_end_time = time(); //默认当前时间
    }
    if (!$begin_time && $end_time)
    {
        $begin_time = -28800; //默认时间戳开始时间
    }
    if (!$end_time && $begin_time)
    {
        $end_time = time(); //默认当前时间
    }
    if ($sort_order_id)
    {
        $sort["order_id"] = (int)$sort_order_id;
    }
    if ($sort_order_time)
    {
        $sort["order_time"] = (int)$sort_order_time;
    }
    if ($sort_order_fee)
    {
        $sort["order_fee"] = (int)$sort_order_fee;
    }
    if ($sort_order_payable)
    {
        $sort["order_payable"] = (int)$sort_order_payable;
    }
    if ($sort_paid_price)
    {
        $sort["paid_price"] = (int)$sort_paid_price;
    }
    if ($sort_sc_time)
    {
        $sort["refunds_time"] = (int)$sort_sc_time;
    }
    if ($sort_fd_time)
    {
        $sort["refunds_fail_time"] = (int)$sort_fd_time;
    }
    if ($sort_close_time)
    {
        $sort["close_time"] = (int)$sort_close_time;
    }
    if ($sort_out_time)
    {
        $sort["checkout_time"] = (int)$sort_out_time;
    }
    if ($sort_pay_time)
    {
        $sort["pay_time"] = (int)$sort_pay_time;
    }
    LogDebug("begin_time:$order_begin_time, end_time:$order_end_time");
    LogDebug("begin_time:$begin_time, end_time:$end_time");
    $shop_id    = \Cache\Login::GetShopId();
    LogDebug("shop_id:[$shop_id]");
    $total      = 0; //总单合计
    if($seat_name){
        $seat       = new \DaoMongodb\Seat;
        $seat_info  = $seat->GetSeatByName($shop_id,$seat_name);
        $seat_id    = $seat_info->seat_id;
        if(!$seat_id)
        {
            $seat_id = 0;
        }
    }
    $pirce_list = [];
    $mgo        = new \DaoMongodb\Order;
    LogDebug($_);
    $order_list = $mgo->GetOrderAllList(
        [
            'shop_id'          => $shop_id,
            'order_id'         => $order_id,
            'seat_id'          => $seat_id,
            'is_invoicing'     => $is_invoicing,
            'dine_way'         => $dine_way,
            'pay_way'          => $pay_way,
            'order_status'     => $order_status,
            'order_begin_time' => $order_begin_time,
            'order_end_time'   => $order_end_time,
            'begin_time'       => $begin_time,
            'end_time'         => $end_time,
        ],
      $sort,
        $page_size,
        $page_no,
        $total,
        $pirce_list
    );
    LogDebug($pirce_list);
    if ($pirce_list == null) {
        //return errcode::SYS_BUSY;
        $pirce_list = [];
    }

    foreach ($order_list as &$v)
    {
        $customer         = new \DaoMongodb\Customer;
        $customer_info    = $customer->QueryById($v->customer_id);
        $v->customer_name = $customer_info->usernick;
        $employee         = new \DaoMongodb\Employee;
        $employee_info    = $employee->GetEmployeeInfo($shop_id,$v->employee_id);
        $v->employee_name = $employee_info->real_name;
        $employee_info2    = $employee->GetEmployeeInfo($shop_id,$v->status_info->employee_id);
        $v->status_info->employee_name = $employee_info2->real_name;
    }
    LogDebug($order_list['customer_name']);
    //订单金额总价
    $all_order_fee       = $pirce_list['all_order_fee'];
    //订单总客人数
    $all_customer_num    = $pirce_list['all_customer_num'];
    //实收总价
    $order_payable       = $pirce_list['all_order_payable'];
    //订单平均价
    $order_average_price = $all_order_fee / $total;
    //客单价格
    $order_people_price  = $all_order_fee / $all_customer_num;
    foreach ($order_list as $key => &$value)
    {
        $value->seat = \Cache\Seat::Get($value->seat_id);
        if (!$value->seat)
        {
            $value->seat = [];
        } else
            {
            $value->seat->seat_price = Util::FenToYuan($value->seat->seat_price);
        }
    }

    $resp = (object)[
        'order_list'          => $order_list,
        'total'               => $total,                                  //总单数
        'all_order_fee'       => round($all_order_fee,2),        //订单金额总价
        'get_real_order'      => round($order_payable,2),        //实收总价
        'order_average_price' => round($order_average_price,2),  //订单平均价
        'order_people_price'  => round($order_people_price,2)    //客单价
    ];
    LogDebug($resp);
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
