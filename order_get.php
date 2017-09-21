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


// Permission::PageCheck();
// $_=$_REQUEST;



function GetOrderInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id = $_['order_id'];

    $mgo = new \DaoMongodb\Order;
    $info = $mgo->GetOrderById($order_id);

    // $info->seat_name = \Cache\Seat::GetSeatName($info->seat_id);
    $info->seat = \Cache\Seat::Get($info->seat_id);
    if($info->seat)
    {
        $info->seat->seat_price = Util::FenToYuan($info->seat->seat_price);
    }

    if(OrderStatus::PENDING == $info->order_status
        && time() - $info->order_time > Cfg::instance()->order_timeout_sec)
    {
        $info->order_status = OrderStatus::TIMEOUT;
    }

    $resp = (object)array(
        'info' => $info
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

// 根据条件查找订单列表
function GetOrderList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $where['customer_id'] = $_['customer_id'];
    // 订单状态
    if(isset($_['order_status'])){
        $where['order_status'] = $_['order_status'];
    }

    $mgo = new \DaoMongodb\Order;
    $order_list = $mgo->GetOrderList(
        $where,
        [],
        ["lastmodtime"=>-1]
    );

    foreach ($order_list as $key => &$value)
    {
        $value->seat_name = \Cache\Seat::GetSeatName($value->seat_id);
        if(OrderStatus::PENDING == $value->order_status
            && time() - $value->order_time > Cfg::instance()->order_timeout_sec)
        {
            $value->order_status = OrderStatus::TIMEOUT;
        }
    }

    $resp = (object)array(
        'list' => $order_list
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

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
