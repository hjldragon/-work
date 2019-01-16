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


Permission::EmployeePermissionCheck(
     Permission::CHK_REPORT_R
);

// 订单统计
function GetOrderStat(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $is_byday = $_["byday"];
    $is_bymon = $_["bymon"];
    $is_byyear = $_["byyear"];
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
    //LogDebug("begin_time:[$begin_time], end_time:[$end_time]");
    $begin_time_sec = strtotime($begin_time);
    $end_time_sec = strtotime($end_time) + 3600*24 - 1; // 一天的最后一秒

    $shop_id = \Cache\Login::GetShopId();
    //LogDebug("shop_id:[$shop_id]");

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
if(isset($_["orderstat"]))
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
