<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取店铺信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_shop.php");
require_once("mgo_employee.php");
require_once("/www/public.sailing.com/php/mgo_stall.php");
use \Pub\Mongodb as Mgo;

function GetShopInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = (string)$_['shop_id'];

    $mgo         = new \DaoMongodb\Shop;
    $stall_mgo   = new Mgo\Stall;
    $employee    = new \DaoMongodb\Employee;

    $userid      = \Cache\Login::GetUserid();
    if(!$userid){
        LogDebug('no userid');
        return errcode::USER_NOLOGIN;
    }
    $employee_info = $employee->QueryByShopId($userid, $shop_id);
    $stall_info    = $stall_mgo->GetInfoByStart($shop_id, $employee_info->employee_id);
    $info          = $mgo->GetShopById($shop_id);

    $set_info = [];
    $set_info['shop_id']           = $info->shop_id;
    $set_info['refresh_time']      = $info->refresh_time;
    $set_info['is_show_wait_time'] = $info->is_show_wait_time;
    $set_info['is_change_urge']    = $info->is_change_urge;
    $set_info['stall_id']          = $stall_info->stall_id;
    $set_info['is_stall']          = $stall_info->is_stall;
    $set_info['is_over_time']      = $info->is_over_time;
    $set_info['kitchen_seat_list'] = $info->kitchen_seat_list;
    $set_info['order_clear_time']  = $info->order_clear_time;

    $resp = (object)array(
        'info' => $set_info
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function time2string($second){
//    $day = floor($second/(3600*24));
//    $second = $second%(3600*24);
//    $hour = floor($second/3600);
    $second = $second%3600;
    $minute = floor($second/60);
    $second = $second%60;
    // 不用管怎么实现的，能用就ok
    return /*$day.'天'.$hour.'小时'.*/$minute.':'.$second;
}

$ret = -1;
$resp = (object)array();
if(isset($_["get_set_info"]))
{
    $ret = GetShopInfo($resp);
} else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
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

