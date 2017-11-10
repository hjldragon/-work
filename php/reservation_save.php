<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 店铺信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_reservation.php");
require_once("permission.php");
require_once("redis_id.php");
Permission::PageCheck();
function SaveReservation(&$resp)
{

    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $reservation_id = $_['reservation_id'];
    if (!$reservation_id)
    {
        $reservation_id = \DaoRedis\Id::GenReservationId();
    }
    $customer_name      = $_['customer_name'];
    $employee_id        = $_['employee_id'];
    $customer_phone     = $_['customer_phone'];
    $customer_num       = $_['customer_num'];
    $seat_id            = $_['seat_id'];
    $reservation_status = $_['reservation_status'];
    $reservation_time   = $_['reservation_time'];
    $sign_time          = $_['sign_time'];
    $shop_id            = \Cache\Login::GetShopId();
    if(!$shop_id)
    {
        return errcode::USER_NOLOGIN;
    }

    $mgo   = new \DaoMongodb\Reservation;
    $entry = new \DaoMongodb\ReservationEntry;

    $entry->reservation_id     = $reservation_id;
    $entry->customer_name      = $customer_name;
    $entry->employee_id        = $employee_id;
    $entry->customer_phone     = $customer_phone;
    $entry->shop_id            = $shop_id;
    $entry->customer_num       = $customer_num;
    $entry->seat_id            = $seat_id;
    $entry->reservation_status = $reservation_status;
    $entry->reservation_time   = $reservation_time;
    $entry->sign_time          = $sign_time;
    $entry->delete             = 0;
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    LogInfo("save ok");
    return 0;
}

function DeletePosition(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $position_id  = $_['position_id'];
    $shop_id = \Cache\Login::GetShopId();
    LogDebug($_);
    if (!$shop_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $mongodb = new \DaoMongodb\Position;
    $ret     = $mongodb->BatchDelete($shop_id,$position_id);
    if (0 != $ret)
    {
        LogErr("BatchDelete err");
        return errcode::SYS_ERR;
    }

    $resp = (object)[
    ];
    LogInfo("delete ok");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_['save_reservation']))
{
    $ret = SaveReservation($resp);
}elseif(isset($_['del_position']))
{
    $ret = DeletePosition($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
