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
require_once("mgo_seat.php");
require_once("mgo_customer.php");
require_once("mgo_employee.php");
require_once("mgo_order_status.php");
require_once("mgo_menu.php");

function GetOrderInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id = $_['order_id'];
    $shop_id  = $_['shop_id'];
    if(!$shop_id)
    {
        $shop_id = \Cache\Login::GetShopId();
    }
    $mgo  = new \DaoMongodb\Order;
    $info = $mgo->GetOrderById($order_id);

    if(!$info->order_id)
    {
        LogErr("order_info is null");
        return errcode::ORDER_NOT_EXIST;
    }
    $order_status  = new \DaoMongodb\OrderStatus;
    $menu          = new \DaoMongodb\MenuInfo;
    $status_info   = $order_status->GetOrderById($order_id);
    $customer      = new \DaoMongodb\Customer;
    $customer_info = $customer->QueryById($info->customer_id);
    $customer_name = $customer_info->usernick;
    $seat          = new \DaoMongodb\Seat;
    $seat_info     = $seat->GetSeatById($info->seat_id);
    $employee      = new \DaoMongodb\Employee;
    $employee_info = $employee->GetEmployeeInfo($shop_id, $info->employee_id);
    $employee_name = $employee_info->real_name;

    $status_infos = [];
    foreach ($status_info as &$s)
    {
        $employee_info2          = $employee->GetEmployeeInfo($shop_id, $s->employee_id);
        $employee_name2          = $employee_info2->real_name;
        $customer_info2          = $customer->QueryById($s->customer_id);
        $customer_phone          = $customer_info2->phone;
        $customer_name2          = $customer_info2->usernick;
        $infos['order_status']   = $s->order_status;
        $infos['customer_phone'] = $customer_phone;
        $infos['employee_name']  = $employee_name2;
        $infos['customer_name']  = $customer_name2;
        $infos['made_time']      = $s->made_time;
        $infos['made_reson']     = $s->made_reson;
        $infos['apply_time']     = $s->apply_time;
        $infos['made_cz_reson']  = $s->made_cz_reson;
        $infos['order_money']    = $info->order_fee;
        array_push($status_infos,$infos);
     }
    if($info->seat_id){
        $info->seat->seat_id              = $seat_info->seat_id;
        $info->seat->seat_name            = $seat_info->seat_name;
        $info->seat->seat_type            = $seat_info->seat_type;
        $info->seat->seat_region          = $seat_info->seat_region;
        $info->seat->price                = $info->seat_price;
        $info->seat->num                  = 1;  //餐位费数量1
    }else{
        $info->seat->seat_name            = $info->plate;
    }
    $all_num         = 0;
    $accessory_price = 0;
    foreach ($info->food_list as &$food)
    {
        $all_num += $food->food_num;
        $food->all_price = $food->food_num*$food->food_price;
        $db_food_info = \Cache\Food::Get($food->food_id);
        if($food->istake == 1)
        {
            //是否有餐盒费及是否有打包
            if($db_food_info->accessory && $food->food_num){
                $accessory = \Cache\Food::Get($db_food_info->accessory);
             $food->accessory_price = $accessory->food_price * (int)$food->food_num*(float)$db_food_info->accessory_num;
            }
        }else{
            $food->accessory_price = 0;
        }
    }
    $total_count      = $all_num + $info->seat->num; //餐品总数
    $accessory_price +=  $info->food_list->accessory_price;//餐盒总计
    if(!$employee_name)
    {
        $employee_name = $employee_name2;
    }
    $info->employee_name                  = $employee_name;
    if($info->order_from == 1)
    {
        $info->customer_name = $employee_name;
    }else{
        $info->customer_name = $customer_name;
    }
    $info->status_info      = $status_infos;
    $info->total_count      = $total_count;
    $info->accessory_price  = $accessory_price;
    if(!$info->paid_price)
    {
        $info->paid_price = $info->order_payable;
    }
    $resp = (object)array(
        'order_info' => $info,
    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_["app_order_info"]))
{
    $ret = GetOrderInfo($resp);
}
elseif(isset($_["get_order_stat"]))
{
    $ret = GetPhoneOrderStat($resp);
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

