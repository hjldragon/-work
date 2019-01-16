<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");

function Input()
{
    $_                    = &$GLOBALS["_"];
    $_['get_shop_qrcode'] = true;
    $_['srctype']         = 3;
    //LogDebug($_);
    require("shop_get.php");

}
function Output(&$obj)
{
    //LogDebug($obj);
    echo json_encode($obj);
}
Input();

?>