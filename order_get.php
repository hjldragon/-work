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
require_once("mgo_menu.php");


// Permission::PageCheck();
// $_=$_REQUEST;


function GetOrderInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id = $_['order_id'];

    $mgo = new \DaoMongodb\Order;
    $info = $mgo->GetOrderById($order_id);

    // $info->seat_name = \Cache\Seat::GetSeatName($info->seat_id);
    $info->seat = \Cache\Seat::Get($info->seat_id);
    if ($info->seat) {
        $info->seat->seat_price = Util::FenToYuan($info->seat->seat_price);
    }

    if (OrderStatus::PENDING == $info->order_status
        && time() - $info->order_time > Cfg::instance()->order_timeout_sec
    ) {
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
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $where['customer_id'] = $_['customer_id'];

    $where['shop_id'] = $_['shop_id'];
    if(!$where['shop_id'])
    {
        LogErr("param err");
        return errcode::SHOP_ID_NOT;
    }
    // 判断时候输入订单状态来获取
    if (isset($_['order_status_list'])) {
        //$where['order_status_list'] = json_encode($_['order_status_list']);
        $where['order_status_list'] = explode(',', $_['order_status_list']);//<<<<<<<后端测试用的代码
    }
        //var_dump($where);
    $mgo = new \DaoMongodb\Order;
    $order_list = $mgo->GetOrderList(
        $where,
        [],
        ["lastmodtime" => -1]
    );
    $list = [];
    $list_all = [];
    foreach ($order_list as $key => &$value) {
//        $value->seat_name = \Cache\Seat::GetSeatName($value->seat_id);
//        if(OrderStatus::PENDING == $value->order_status
//            && time() - $value->order_time > Cfg::instance()->order_timeout_sec)
//        {
//            $value->order_status = OrderStatus::TIMEOUT;
//        }

        $food_list_all = [];
        $foodlist = [];
        foreach ($value->food_list as $food_list) {
            $food_list_all['food_id'] = $food_list->food_id;
            $food = \Cache\Food::Get($food_list->food_id);
            $food_img = $food->food_img_list;
            $food_list_all['food_name'] = $food_list->food_name;
            $food_list_all['food_num'] = $food_list->food_num;
            $food_list_all['food_price'] = $food_list->food_price;
            $food_list_all['food_price_num'] = $food_list->food_price_num;
            array_push($foodlist, $food_list_all);
        }
        $list['order_id'] = $value->order_id;
        $list['food_list'] = $foodlist;
        $list['food_num_all'] = $value->food_num_all;
        $list['food_price_all'] = $value->food_price_all;
        $list['order_time'] = $value->order_time;
        $list['food_img'] = $food_img;
        $list['order_status'] = $value->order_status;
        $list['drawback_reason'] = $value->drawback_reason;

        array_push($list_all, $list);
    }

    $resp = (object)array(
        'list' => $list_all
    );

    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if (isset($_["orderinfo"])) {
    $ret = GetOrderInfo($resp);
} elseif (isset($_["get_order_list"])) {
    $ret = GetOrderList($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>
