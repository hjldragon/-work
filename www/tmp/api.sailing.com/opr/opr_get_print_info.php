<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{

    $_ = &$GLOBALS["_"];
    $_['get_print_info'] = true;
    $_['srctype']        = 3;
    $_['order_id']       = $_['order_num'];
    require("printer_get.php");
    //LogDebug($_);
}
function Output(&$obj)
{
    echo json_encode($obj);
}
Input();

?>