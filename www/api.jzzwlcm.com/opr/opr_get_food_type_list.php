<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{
    $_ = &$GLOBALS["_"];
    $_['get_food_type_list']  = true;
    $_['srctype']             = 3;
    require("printer_get.php");
    //LogDebug($_);
}
function Output(&$obj)
{
    echo json_encode($obj);
}
Input();

?>