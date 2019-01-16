<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");

function Input()
{
    $_ = &$GLOBALS["_"];
    $_['order_id']              = $_['order_num'];
    $_['srctype']               = 3;
    $_['made_order_status']     = true;

    $new_menu                   = json_decode($_['menu']);
    foreach ($new_menu  as &$menu) {
        $menu->weight   = Order::$weight[$menu->weight];
        $menu->istake   = Order::$istake[$menu->istake];
        $menu->isgive   = Order::$isgive[$menu->isgive];
        $menu->food_num = $menu->count;
        unset($menu->count);
    }
    $_['food_list'] = $new_menu ;
    //LogDebug($_);
    require("order_save.php");

}
function Output(&$obj)
{
    //LogDebug($obj);
    if($obj->ret == 0)
    {
        $obj->data->oper_ret = 0;
    }else{
       $obj->data->oper_desc = "操作失败";
     }
    echo json_encode($obj);
    //LogDebug($html);
}
Input();

?>