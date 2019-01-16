<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");

function Input()
{
    $_                  = &$GLOBALS["_"];
    $_['order_advance'] = true;
    $_['srctype']       = 3;
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