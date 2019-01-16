<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");

function Input()
{
    $_                   = &$GLOBALS["_"];
    //LogDebug($_['pay_type']);
    $_['pay_way']        = Order::$save_orderpay[$_['pay_type']];
    //LogDebug($_['pay_way']);
    $_['paid_price']     = $_['price'];
    $_['srctype']        = 3;
    $_['order_post_pay'] = true;
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