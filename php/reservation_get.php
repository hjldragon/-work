<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_reservation.php");
require_once("mgo_seat.php");
require_once("mgo_employee.php");

//Permission::PageCheck();

function GetAllReservationList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $customer_name      = $_['customer_name'];
    $employee_name      = $_['employee_name'];
    $customer_phone     = $_['customer_phone'];
    $reservation_status = $_['reservation_status'];
    $begin_time         = $_["begin_time"];  //开始时间
    $end_time           = $_["end_time"];    //结束时间
    $page_size          = $_['page_size'];
    $page_no            = $_['page_no'];
    $reservation_time   = $_['reservation_time']; //PAD端的时间搜索
    //排序字段
    $sort_reservation_time = (int)$_['sort_reservation_time'];
    $sort_customer_num     = (int)$_['sort_customer_num'];
    $sort_sign_time        = (int)$_['sort_sign_time'];
    if ($sort_reservation_time)
    {
        $sort['reservation_time'] = (int)$sort_reservation_time;
    }
    if ($sort_customer_num)
    {
        $sort['customer_num'] = (int)$sort_customer_num;
    }
    if ($sort_sign_time)
    {
        $sort['sign_time'] = (int)$sort_sign_time;
    }
    if (!$page_size)
    {
        $page_size = 100000;//如果没有传默认10条
    }
    if (!$page_no)
    {
        $page_no = 1; //第一页开始
    }

    if($reservation_time)
    {
        $begin_time = strtotime($reservation_time);
        $end_time   = $begin_time+86399;
    }
    if (!$begin_time && $end_time)
    {
        $begin_time = -28800; //默认时间戳开始时间
    }
    if (!$end_time && $begin_time)
    {
        $end_time = 1922354460; //默认无穷大时间戳
    }

    LogDebug("begin_time:$begin_time, end_time:$end_time");
    $shop_id      = $_['shop_id'];
    if(!$shop_id)
    {
        $shop_id  = \Cache\Login::GetShopId();
    }
    LogDebug("shop_id:[$shop_id]");
    $total      = 0; //总计
    if($employee_name){
        $employee       = new \DaoMongodb\Employee;
        $employee_info  = $employee->GetEmployeeByName($shop_id, $employee_name);
        if(count($employee_info)>0){
            foreach ($employee_info as $s){
                $employee_id    = $s->employee_id;
                $employee_id_list [] =$employee_id;
            }
        }else{
            $employee_id_list = ['0'];
        }
    }
    $mgo        = new \DaoMongodb\Reservation;
    LogDebug($_);
    $reservation_list = $mgo->GetReservationAllList(
        [
            'shop_id'            => $shop_id,
            'customer_name'      => $customer_name,
            'customer_phone'     => $customer_phone,
            'employee_id_list'   => $employee_id_list,
            'reservation_status' => $reservation_status,
            'reservation_time'   => $reservation_time,
            'begin_time'         => $begin_time,
            'end_time'           => $end_time,
        ],
        $sort,
        $page_size,
        $page_no,
        $total
    );
    foreach ($reservation_list as &$v)
    {
        $employee         = new \DaoMongodb\Employee;
        $employee_info    = $employee->GetEmployeeInfo($shop_id,$v->employee_id);
        if($v->reservation_status != 1)
        {
            $employee_info2   = $employee->GetEmployeeInfo($shop_id,$v->reserve_info->employee_id);
            $v->reserve_info->employee_name = $employee_info2->real_name;
        }
        $v->employee_name = $employee_info->real_name;
        $seat             = new \DaoMongodb\Seat;
        $seat_info        = $seat->GetSeatById($v->seat_id);
        $v->seat_name     = $seat_info->seat_name;
        $v->seat_type     = $seat_info->seat_type;
        if($v->reservation_status == 2 ){
            $v->sign_time = $v->reserve_info->made_time;
        }
    }
    $resp = (object)[
        'reservation_list' => $reservation_list,
        'total'            => $total,
        'page_size'        => $page_size,
        'page_no'          => $page_no
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["get_reservation_all"]))
{
    $ret = GetAllReservationList($resp);
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
