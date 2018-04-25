<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");

function Input()
{
    $_                      = &$GLOBALS["_"];
    $_['srctype']           = 3;
    $_['order_from']        = Order::$order_from_get[$_['device_type']];
    $data                   = json_decode($_['data']);
    $_['order_remark']      = $data->remark;
    $_['seat_name']         = $data->table_name;
    $_['plate']             = $data->plate;
    $_['customer_num']      = $data->people;
    //$_['total_price']       = $data->total_price;
    $_['order_sure_status'] = Order::$order_sure_status[$data->ispay];
    //LogDebug($data->menu);
    foreach ($data->menu as &$menu) {
        $menu->weight   = Order::$weight[$menu->weight];
        $menu->istake   = Order::$istake[$menu->istake];
        $menu->isgive   = Order::$isgive[$menu->isgive];
        $menu->food_num = $menu->count;
        unset($menu->count);
    }
    $_['food_list']  = $data->menu;
    $_['order_save'] = true;
    //LogDebug($_);
    require("order_save.php");

}
function Output(&$obj)
{
    //LogDebug($obj);
    echo json_encode($obj);
}
Input();

?>