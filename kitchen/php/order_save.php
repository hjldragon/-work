<?php
/*
 * [Rocky 2017-05-12 20:16:27]
 * 订单信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_order.php");
require_once("mgo_menu.php");
require_once("redis_id.php");
require_once("const.php");
require_once("mgo_user.php");
require_once("mgo_employee.php");
require_once("mgo_order.php");
require_once("page_util.php");
require_once("page_util.php");
require_once("/www/public.sailing.com/php/mgo_selfhelp.php");
use \Pub\Mongodb as Mgo;

//点击改变的菜品状态
function SaveOrderFoodStatus(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $order_list     = json_decode($_['order_list']);
    $made_status    = $_['made_status'];
    $mgo            = new \DaoMongodb\Order;

    foreach ($order_list as $v)
    {
        $ret = $mgo->BatchFoodStatus($v->order_id, $v->id, $made_status);
        if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
        }

    }

//
//    $ret = $mgo->BatchFoodStatus($order_id_list, $id_list, $made_status);
//    if (0 != $ret) {
//        LogErr("Save err");
//        return errcode::SYS_ERR;
//    }
    $resp = (object)[
    ];
    return 0;
}
//批量改变订单催单状态
function SaveOrderUrge(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $order_id_list     = json_decode($_['order_id_list']);
    $is_urge           = $_['is_urge'];
    $mgo               = new \DaoMongodb\Order;

    $ret = $mgo->OrderUrge($order_id_list, $is_urge);

    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['save_food_status']))
{
    $ret = SaveOrderFoodStatus($resp);
}elseif (isset($_['save_order_urge']))
{
    $ret = SaveOrderUrge($resp);
}

else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
    'data' => $resp
);
LogDebug($result);
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

