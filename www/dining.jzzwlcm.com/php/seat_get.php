<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取餐桌位置信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_seat.php");
require_once("mgo_order.php");

//Permission::PageCheck();
 

function GetOrderListBySeat($shop_id, $seat_id)
{
    $mgo = new \DaoMongodb\Order;
    $order_list = $mgo->GetOrderList(
        [
            'shop_id'=>$shop_id,
            'seat_id'=>$seat_id,
            'begin_time'=>time() - 3600*5, // 5小时前到现在  // <<<<<<<<<<<<可配置
            'end_time'=>time(),
            'order_status_list' => [
                OrderStatus::PENDING,
                OrderStatus::CONFIRMED,
                OrderStatus::POSTPONED,
                OrderStatus::PAID,
                OrderStatus::PRINTED,
            ]
        ]
    );
    // LogDebug($order_list);
    return $order_list;
}

// 餐桌状态
function GetSeatStatus($order_list, $shop_id, $seat_id)
{
    // 最新近的订单
    if(!$order_list)
    {
        $order_list = GetOrderListBySeat($shop_id, $seat_id);
    }

    // LogDebug($order_list);

    // 判断餐桌状态
    // 当一张桌上有多个订单，当其中一个订单是需要服务员处理的
    // 时(PENDING)，直接返回
    $status = SeatStatus::VACANT;
    foreach($order_list as $key => $order)
    {
        // LogDebug("seat_id:$seat_id, order_id:{$order->order_id}, order_status:{$order->order_status}");
        switch($order->order_status)
        {
            case OrderStatus::PENDING:
                // 是在线支付的，不提醒  [XXX]
                if(PayWay::IsOnline($order->pay_way))
                {
                    continue;
                }
                return SeatStatus::ALERT; // 优先级最高
            case OrderStatus::CONFIRMED:
            case OrderStatus::POSTPONED:
            case OrderStatus::PAID:
            case OrderStatus::PRINTED:
            //case OrderStatus::ERR:
                $status = SeatStatus::INUSE;
                break;
        }
    }
    return $status;
}

function GetSeatInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $seat_id = (string)$_['seat_id'];
    $shop_id = $_['shop_id'];
    if(!$shop_id)
    {
        $shop_id = \Cache\Login::GetShopId();
    }
    $info = \Cache\Seat::Get($seat_id);

    if(null == $info)
    {
        LogErr("seat err, seat_id:[$seat_id]");
        return errcode::PARAM_ERR;
    }

    $info->seat_price = Util::FenToYuan($info->seat_price);
    $info->seat_status = GetSeatStatus(null, $shop_id, $seat_id);

    $resp = (object)array(
        'seat_info' => $info
    );
   // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetSeatList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
LogDebug($_);
    $shop_id = \Cache\Login::GetShopId();
    $seat_name =$_['seat_name'];
    $seat_size=$_['seat_size'];
    //LogDebug($seat_size);
    $mgo = new \DaoMongodb\Seat;
    $list = $mgo->GetList($shop_id,[
        'seat_name'=>$seat_name,
        'seat_size'=>$seat_size
    ]);
    LogDebug($list);
    foreach($list as $i => &$item)
    {
        $order_list = GetOrderListBySeat($shop_id, $item->seat_id);
        $item->seat_price = Util::FenToYuan($item->seat_price);
        $item->order_list = $order_list;
        $item->seat_status = GetSeatStatus($order_list, $shop_id, $item->seat_id);
    }
    $resp = (object)array(
        'list' => $list
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

//获取所有餐桌列表
function GetSeatListAll(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $seat_name = $_['seat_name'];
    $shop_id   = $_['shop_id'];
    //$shop_id   = \Cache\Login::GetShopId();
    if (!$shop_id) {
        LogErr("shop_id err or maybe not login");
        return errcode::SEAT_NOT_EXIST;
    }
    $mgo       = new \DaoMongodb\Seat;
    $seat_list = $mgo->GetSeatByName($shop_id,$seat_name);
    $seatlist  = [];
    foreach ($seat_list as $list) {
        $listall['seat_id']      = $list->seat_id;
        $listall['seat_name']    = $list->seat_name;
        $listall['seat_region']  = $list->seat_region;
        $listall['seat_type']    = $list->seat_type;
        $listall['seat_shape']   = $list->seat_shape;
        $listall['seat_size']    = $list->seat_size;
        $listall['price']        = $list->price;
        $listall['price_type']   = $list->price_type;
        $listall['consume_min']  = $list->consume_min;
        //$seatlist['qr_code']     = $list->qr_code;
        array_push($seatlist, $listall);
    }

    $resp = (object)[
        'seatlist' => $seatlist,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["app_seat_info"]))
{
    $ret = GetSeatInfo($resp);
}
elseif(isset($_["list"]))
{
    $ret = GetSeatList($resp);
}elseif (isset($_['app_seat_list']))
{
    $ret = GetSeatListAll($resp);
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

