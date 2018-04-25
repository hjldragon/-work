<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{
    $_ = &$GLOBALS["_"];
    $_['get_order_pendding']  = true;
    $_['srctype']             = 3;
    //LogDebug($_);
    require("order_get.php");

}
function Output(&$obj)
{
    echo json_encode($obj);
}
Input();

?>