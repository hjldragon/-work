<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 店铺信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_reservation.php");
require_once("permission.php");
require_once("redis_id.php");
//Permission::PageCheck();
function SaveReservation(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $reservation_id   = $_['reservation_id'];
    $customer_name    = $_['customer_name'];
    $customer_sex     = $_['customer_sex'];
    $account          = $_['account'];   //创建人账号
    $customer_phone   = $_['customer_phone'];
    if (!preg_match('/^\d{11}$/', $customer_phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }
    $customer_num     = $_['customer_num'];
    $seat_name        = $_['seat_name'];
    $reservation_time = $_['reservation_time'];
    if($reservation_time<time())
    {
        return errcode::RESERVATION_TIME_GO;
    }
    $note             = $_['remark'];
    $shop_id          = $_['shop_id'];
    if (!$shop_id)
    {
        return errcode::SHOP_NOT_WEIXIN;
    }
    $seat          = new \DaoMongodb\Seat;
    $seat_info     = $seat->GetSeatID($shop_id,$seat_name);
    if(!$seat_info->seat_name)
    {
     return errcode::SEAT_IS_EXIST;
    }
    $employee      = new \DaoMongodb\Employee;
    $employee_info = $employee->GetEmployeeByPhone($shop_id, $account);
    $mgo           = new \DaoMongodb\Reservation;
    $entry         = new \DaoMongodb\ReservationEntry;
    if (!$reservation_id)
    {
        $reservation_id = \DaoRedis\Id::GenReservationId();
        $entry->ctime   = time();
    }
    $entry->reservation_id     = $reservation_id;
    $entry->customer_name      = $customer_name;
    $entry->employee_id        = $employee_info->employee_id;
    $entry->customer_phone     = $customer_phone;
    $entry->shop_id            = $shop_id;
    $entry->customer_num       = $customer_num;
    $entry->seat_id            = $seat_info->seat_id;
    $entry->reservation_status = 1;      //预约后显示未签到状态
    $entry->reservation_time   = $reservation_time;
    $entry->customer_sex       = $customer_sex;
    $entry->source             = 1;      //<<<<<<<<<<<<现在只有1种收银台来源
    $entry->note               = $note;
    $entry->delete             = 0;
    $ret                       = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    LogInfo("save ok");
    return 0;
}

function SaveReservationState(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $reservation_id     = $_['reservation_id'];
    $customer_num       = $_['arrive_total_people'];//签到人数
    $reservation_status = $_['reservation_status'];
    $account            = $_['account'];
    $reson              = $_['reson'];
    LogDebug($_);
    $mgo                = new \DaoMongodb\Reservation;
    $employee           = new \DaoMongodb\Employee;
    $reservation        = $mgo->GetReservationById($reservation_id);
    if (!$reservation->shop_id || !$reservation_id)
    {
        return errcode::RESERVATION_NOT_EXIST;
    }
    $employee_info = $employee->GetEmployeeByPhone($reservation->shop_id, $account);
    if ($reservation_status == 2)
    {
        $reserve_info->reservation_status = $reservation_status;
        $reserve_info->reserve_num        = $customer_num;      //<<<<<<<<现在的功能人数预约是固定的
        $reserve_info->made_time          = time();
        $reserve_info->employee_id        = $employee_info->employee_id;
    }
    if ($reservation_status == 3)
    {
        $reserve_info->reservation_status = $reservation_status;
        $reserve_info->reserve_num        = $customer_num;
        $reserve_info->made_time          = time();
        $reserve_info->employee_id        = $employee_info->employee_id;
        $reserve_info->reson              = $reson;
    }

    $entry                     = new \DaoMongodb\ReservationEntry;
    $entry->reservation_id     = $reservation_id;
    $entry->reservation_status = $reservation_status;
    $entry->reserve_info       = $reserve_info;
    $ret                       = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    LogInfo("save ok");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_['save_reservation']))
{
    $ret = SaveReservation($resp);
}elseif(isset($_['save_reservation_state']))
{
    $ret = SaveReservationState($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
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