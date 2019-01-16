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
require_once("mgo_seat.php");
// Permission::PageCheck();
//$_=$_REQUEST;
function GetOrderInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id  = $_['order_id'];
    $mgo       = new \DaoMongodb\Order;
    $info      = $mgo->GetOrderById($order_id);
    $food      = new \DaoMongodb\MenuInfo;
    $seat      = new \DaoMongodb\Seat;
    $seat_info = $seat->GetSeatById($info->seat_id);
    foreach ($info->food_list as &$v) {
        $food_info          = $food->GetFoodinfoById($v->food_id);
        $accessory          = \Cache\Food::Get($food_info->accessory);
        $v->accessory_price = $accessory->food_price;
        $v->food_img        = $food_info->food_img_list;
        $v->accessory_num   = $food_info->accessory_num;
        $v->sale_off        = PageUtil::GetFoodSaleOff($food_info);
        //因为数据里面个能存在相同的菜品
//        switch ($v->food_attach_list->spec_value) {
//            case '大':
//                $name = $v->food_name.'1';
//                break;
//            case '中':
//               $name = $v->food_name.'2';
//                break;
//            case '小':
//               $name = $v->food_name.'3';
//                break;
//            default:
//                break;
//                }
//
//      if($a[$name] && $a[$name]->food_attach_list->spec_value == $v->food_attach_list->spec_value){
//
//        $a[$name]->pack_num += $v->pack_num;
//        $a[$name]->food_num += $v->food_num;
//     }else{
//        $a[$name] = $v;
//     }
    }
    //$info->food_list = array_values($a);

    if($info->order_status == NewOrderStatus::REFUNDING){
        $info->order_status = 3;
    }
    if($info->order_status == NewOrderStatus::PAY && $info->is_appraise == 0){
        $info->order_status = 6;
    }
    if($info->order_status == NewOrderStatus::PAY && $info->is_appraise == 1){
        $info->order_status = 2;
    }
    $info->food_img  = $v->food_img;
    $info->seat_name = $seat_info->seat_name;

    // $info->seat_name = \Cache\Seat::GetSeatName($info->seat_id);
 /*   $info->seat = \Cache\Seat::Get($info->seat_id);
    if($info->seat){
        $info->seat->seat_price = Util::FenToYuan($info->seat->seat_price);
    }

    if(OrderStatus::PENDING == $info->order_status
        && time() - $info->order_time > Cfg::instance()->order_timeout_sec
    ){
        $info->order_status = OrderStatus::TIMEOUT;
    }*/
 //die;
    $resp = (object)array(
        'info' => $info
    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
// 根据条件查找订单列表
function GetOrderList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $where['customer_id'] = $_['customer_id'];
    $where['shop_id']     = $_['shop_id'];
    if(!$where['shop_id'] )
    {
        LogErr("param err");
        return errcode::SHOP_ID_NOT;
    }
    // 订单状态
    if (isset($_['order_status_list'])) {
        $where['order_status_list']  =json_decode($_['order_status_list']);
    }

    $mgo = new \DaoMongodb\Order;
    $order_list = $mgo->GetOrderList(
        $where,
        ["order_time" => -1]
    );

    $list_all = [];
    foreach ($order_list as $key => &$value) {

        $food_list_all = [];
        $foodlist      = [];
        $list          = [];

        if($value->order_status != NewOrderStatus::KNOT && !$value->order_status != NewOrderStatus::GUAZ){
            foreach ($value->food_list as $food_list) {
                $food_list_all['food_id']        = $food_list->food_id;
                $food                            = \Cache\Food::Get($food_list->food_id);
                $food_img                        = $food->food_img_list;
                $food_list_all['food_name']      = $food_list->food_name;
                $food_list_all['food_num']       = $food_list->food_num;
                $food_list_all['food_price']     = $food_list->food_price;
                $food_list_all['food_price_num'] = $food_list->food_price_num;
                array_push($foodlist, $food_list_all);
            }

            $list['order_id']       = $value->order_id;
            $list['food_list']      = $foodlist;
            $list['food_num_all']   = $value->food_num_all;
            $list['food_price_all'] = $value->order_fee;
            $list['order_time']     = $value->order_time;
            $list['food_img']       = $food_img;
            $list['order_status']   = $value->order_status;
            if($value->order_status == NewOrderStatus::REFUNDING){
                $list['order_status'] = 3;
            }
            if($value->order_status == NewOrderStatus::PAY && $value->is_appraise == 0){
                $list['order_status'] = 6;
            }
            if($value->order_status == NewOrderStatus::PAY && $value->is_appraise == 1){
                $list['order_status'] = 2;
            }
            if($value->order_status == NewOrderStatus::CLOSER){
                $list['order_status'] = 7;
            }
            $list['drawback_reason'] = $value->drawback_reason;
            array_push($list_all, $list);
        }
    }
    $resp = (object)array(
        'list' =>$list_all
    );
    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();
if (isset($_["get_order_info"])) {
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
