<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 菜单规格保存类
 */
require_once("current_dir_env.php");
require_once("mgo_seat.php");
require_once("redis_id.php");
require_once("const.php");
 

function SaveSeatinfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
   
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    if(!PageUtil::LoginCheck())
    {
        LogDebug("not login, token:{$_['token']}");
        return errcode::USER_NOLOGIN;
    }
    $seat_id     = (string)$_['seat_id'];
    $name        = (string)$_['name'];
    $seat_size   = (int)$_['seat_size'];
    $price       = (float)$_['price'];
    $shop_id     = (string)$_['shop_id'];
    $seat_region = (string)$_['seat_region'];
    $seat_type   = (string)$_['seat_type'];
    $seat_shape  = (string)$_['seat_shape'];
    $price_type  = (int)$_['price_type'];
    $consume_min = (float)$_['consume_min'];


    if(!$Seat_id)
    {
        $Seat_id = \DaoRedis\Id::GenSeatId();
    }
    $entry = new \DaoMongodb\SeatEntry;
    $mongodb = new \DaoMongodb\Seat;
    $entry->seat_id     = $seat_id;
    $entry->name        = $name;
    $entry->seat_size   = $seat_size;
    $entry->price       = $price;
    $entry->shop_id     = $shop_id;
    $entry->delete      = 0;
    $entry->seat_region = $seat_region;
    $entry->seat_type   = $seat_type;
    $entry->seat_shape  = $seat_shape;
    $entry->price_type  = $price_type;
    $entry->consume_min = $consume_min;
    $ret = $mongodb->Save($entry);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($entry);

    $resp = (object)array(
    );
    
    LogInfo("save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['save']))
{
    $ret = SaveSeatinfo($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>


