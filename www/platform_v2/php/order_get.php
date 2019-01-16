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
require_once("mgo_order_status.php");

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
    $mgo                              = new \DaoMongodb\Order;
    $info                             = $mgo->GetOrderById($order_id);
    $order_status                     = new \DaoMongodb\OrderStatus;
    $status_info                      = $order_status->GetOrderById($order_id);
    $shop_id                          = $_['shop_id'];
    if(!$shop_id)
    {
        $shop_id                       = \Cache\Login::GetShopId();
    }
    $customer                         = new \DaoMongodb\Customer;
    $customer_info                    = $customer->QueryById($info->customer_id);
    $customer_name                    = $customer_info->usernick;
    $seat                             = new \DaoMongodb\Seat;
    $seat_info                        = $seat->GetSeatById($info->seat_id);
    $employee                         = new \DaoMongodb\Employee;
    $employee_info                    = $employee->GetEmployeeInfo($shop_id, $info->employee_id);
    $employee_name                    = $employee_info->real_name;
    if($info->order_status == 8 && $info->is_confirm == 1)
    {
        $info->order_status = 2;
    }
    $status_infos = [];
    foreach ($status_info as $s)
    {
        $employee_info2          = $employee->GetEmployeeInfo($shop_id, $s->employee_id);
        $employee_name2          = $employee_info2->real_name;
        $customer_info2          = $customer->QueryById($s->customer_id);
        $customer_phone          = $customer_info2->phone;
        $customer_name2          = $customer_info2->usernick;
        $infos['order_status']   = $s->order_status;
        $infos['customer_phone'] = $customer_phone;
        $infos['employee_name']  = $employee_name2;
        $infos['customer_name']  = $customer_name2;
        $infos['made_time']      = $s->made_time;
        $infos['made_reson']     = $s->made_reson;
        $infos['made_cz_reson']  = $s->made_cz_reson;
        array_push($status_infos,$infos);
    }
    if($info->seat_id){
        $info->seat->seat_id              = $seat_info->seat_id;
        $info->seat->seat_name            = $seat_info->seat_name;
        $info->seat->seat_type            = $seat_info->seat_type;
        $info->seat->seat_region          = $seat_info->seat_region;
    }else{
        $info->seat->seat_name            = $info->plate;
    }

    $info->employee_name                  = $employee_name;
    if($info->order_from == 1)
    {
        $info->customer_name = $employee_name;
    }else{
        $info->customer_name = $customer_name;
    }
    $info->status_info                = $status_infos;
    $resp = (object)array(
        'order_info' => $info,
    );
    LogDebug($resp);
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
    $shop_id          = $_['shop_id'];
    //排序字段
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
        $order_begin_time = -28800; //默认后面很长时间
    }
    if (!$order_end_time && $order_begin_time)
    {
        $order_end_time = 1922354460; //默认当前时间
    }
    if (!$begin_time && $end_time)
    {
        $begin_time = -28800; //默认时间戳开始时间
    }
    if (!$end_time && $begin_time)
    {
        $end_time = 1922354460; //默认后面很长时 间
    }
    $sort_name = $_['sort_name'];
    $desc      = $_['desc'];
    switch ($sort_name) {
        case 'order_id':
            $sort['order_id'] = (int)$desc;
            break;
        case 'order_time'://反结账
            $sort['order_time'] = (int)$desc;
            break;
        case 'order_fee'://反结账
            $sort['order_fee'] = (int)$desc;
            break;
        case 'order_payable'://反结账
            $sort['order_payable'] = (int)$desc;
            break;
        case 'paid_price'://反结账
            $sort['paid_price'] = (int)$desc;
            break;
        case 'refunds_time'://反结账
            $sort['refunds_time'] = (int)$desc;
            break;
        case 'refunds_fail_time'://反结账
            $sort['refunds_fail_time'] = (int)$desc;
            break;
        case 'close_time'://反结账
            $sort['close_time'] = (int)$desc;
            break;
        case 'checkout_time'://反结账
            $sort['checkout_time'] = (int)$desc;
            break;
        case 'pay_time'://反结账
            $sort['pay_time'] = (int)$desc;
            break;
        default:
            break;
    }
    if(!$shop_id)
    {
        $shop_id    = \Cache\Login::GetShopId();
    }
    LogDebug("shop_id:[$shop_id]");
    $total      = 0; //总单合计
    if($seat_name){
        $seat       = new \DaoMongodb\Seat;
        $seat_info  = $seat->GetSeatByName($shop_id,$seat_name);
        foreach ($seat_info as $s){
            $seat_id    = $s->seat_id;
            $seat_id_list [] =$seat_id;
        }
    }
    $pirce_list = [];
    $mgo        = new \DaoMongodb\Order;

    $order_list = $mgo->GetOrderAllList(
        [
            'shop_id'          => $shop_id,
            'order_id'         => $order_id,
            'seat_id_list'     => $seat_id_list,
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
    //LogDebug($pirce_list);
    if ($pirce_list == null) {
        $pirce_list = [];
    }

    foreach ($order_list as &$v)
    {
        $customer                      = new \DaoMongodb\Customer;
        $customer_info                 = $customer->QueryById($v->customer_id);
        $v->customer_name              = $customer_info->usernick;
        $v->customer_phone             = $customer_info->phone;
        $employee                      = new \DaoMongodb\Employee;
        $employee_info                 = $employee->GetEmployeeInfo($shop_id, $v->employee_id);
        $v->employee_name              = $employee_info->real_name;
        $employee_info2                = $employee->GetEmployeeInfo($shop_id, $v->status_info->employee_id);
        $v->status_info->employee_name = $employee_info2->real_name;
        $seat                          = new \DaoMongodb\Seat;
        if($v->seat_id)
        {
            $seat_info     = $seat->GetSeatById($v->seat_id);
            $v->seat_name  = $seat_info->seat_name;
        }else {
            $v->seat_name = $v->plate;
        }
    }
    //LogDebug($order_list['customer_name']);
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


    $page_all = ceil($total/$page_size);//总共页数
    $resp = (object)[
       'order_list'          => $order_list,
       'total'               => $total,                                  //总单数
       'all_order_fee'       => round($all_order_fee,2),        //订单金额总价
       'get_real_order'      => round($order_payable,2),        //实收总价
       'order_average_price' => round($order_average_price,2),  //订单平均价
       'order_people_price'  => round($order_people_price,2),    //客单价
       'order_status'        => $order_status,
       'page_all'            => $page_all,
       'page_no'             => $page_no
    ];

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
//pad端新订单列表和退款列表
function GetOrderPending(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }


    $order_status_list = [1,2,8];
    $shop_id = $_['shop_id'];
    $shop_info = new \DaoMongodb\Shop;
    if (!$shop_id) {
        $shop_id = \Cache\Login::GetShopId();
    }
    $shop_s = $shop_info->GetShopById($shop_id);
    if(!$shop_s->shop_id)
    {
        return errcode::SHOP_NOT_WEIXIN;
    }
    LogDebug("shop_id:[$shop_id]");
    $sort['order_time'] = -1;
    $mgo        = new \DaoMongodb\Order;
    $infos      = $mgo->GetOrderList(
        [
            'shop_id'           => $shop_id,
            'order_status_list' => $order_status_list,
        ],
        [],
        $sort
    );
    $order_info = [];
    foreach ($infos as $v)
    {
        if($v->order_status == NewOrderStatus::PAY || $v->order_status == NewOrderStatus::NOPAY)
        {
            $type = 0; //新订单
        }
        if($v->order_status == NewOrderStatus::REFUNDING)
        {
            $type = 1; //退款订单
        }
        $info['order_id']      = $v->order_id;
        $info['serial_number'] = $v->order_water_num;
        $info['type']          = $type;
        $info['serial_time']   = date('Y-m-d H:i:s',$v->order_time);
        $info['price']         = $v->order_fee;
        if ($v->order_status == NewOrderStatus::NOPAY)
        {
            $info['is_pay'] = false;
        }
        if ($v->order_status == NewOrderStatus::PAY || $v->order_status == NewOrderStatus::REFUNDING)
        {
            $info['is_pay'] = true;
        }
        array_push($order_info, $info);
    }
    $resp = (object)[
        'pendding' => $order_info,
    ];

    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_["order_info"]))
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
elseif(isset($_["get_order_pendding"]))
{
    $ret = GetOrderPending($resp);
}
$result = (object)array(
    'ret' => $ret,
    'data' => $resp
);

if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}
?><?php /******************************以下为html代码******************************/?>

