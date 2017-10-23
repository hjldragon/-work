<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 菜单规格保存类
 */
require_once("current_dir_env.php");
require_once("mgo_seat.php");
require_once("redis_id.php");
require_once("const.php");
//$_=$_REQUEST;
function SaveSeatInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $seat_id     = $_['seat_id'];//这是唯一的
    $seat_name   = $_['seat_name'];
    $seat_size   = $_['seat_size'];
    $price       = $_['price'];
    $seat_region = $_['seat_region'];
    $seat_type   = $_['seat_type'];
    $seat_shape  = $_['seat_shape'];
    $price_type  = $_['price_type'];
    $consume_min = $_['consume_min'];
    if (!$seat_id)
    {
        $seat_id = \DaoRedis\Id::GenSeatId();
    }

    //$url = 'http://www.ob.com:8080/php/img_get.php?get_seat_qrcode=1&shop_id=4&seat_id=5';二维码地址
    $entry              = new \DaoMongodb\SeatEntry;
    $mgo                = new \DaoMongodb\Seat;
    $entry->seat_id     = $seat_id;
    $entry->shop_id     = \Cache\Login::GetShopId();
    $entry->seat_name   = $seat_name;
    $entry->seat_size   = $seat_size;
    $entry->price       = $price;
    $entry->delete      = 0;
    $entry->seat_region = $seat_region;
    $entry->seat_type   = $seat_type;
    $entry->seat_shape  = $seat_shape;
    $entry->price_type  = $price_type;
    $entry->consume_min = $consume_min;
    $ret = $mgo->Save($entry);

    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($entry);
    $resp = (object)[];
    LogInfo("save ok");
    return 0;
}

//删除餐桌号可以多选
function DeleteSeatInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $seat_id_list = json_decode($_['seat_id_list']);
    //$seat_id_list = explode(',',$_['seat_id_list']); //<<<<<<<<<<<<<<<<<<<测试时的代码
    LogDebug($_['seat_id_list']);
    if (!$seat_id_list) {
        return errcode::SEAT_NOT_EXIST;
    }

    $entry   = new \DaoMongodb\SeatEntry;
    $mongodb = new \DaoMongodb\Seat;

    $ret     = $mongodb->BatchDelete($seat_id_list);

    if (0 != $ret) {
        LogErr("delete err");
        return errcode::SYS_ERR;
    }
    LogDebug($entry);

    $resp = (object)[
    ];
    LogInfo("delete ok");
    return 0;

}

$ret = -1;
$resp = (object)array();
if(isset($_['seat_save']))
{
    $ret = SaveSeatInfo($resp);
}elseif (isset($_['seat_delete']))
{
    $ret = DeleteSeatInfo($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>


