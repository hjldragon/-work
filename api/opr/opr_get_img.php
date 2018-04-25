<?php
/*
 *
 */
ob_start();
require_once("current_dir_env.php");
ob_end_clean();
function Input()
{
    $_        = &$_REQUEST; //&$GLOBALS["_"];
    $_['img'] = true;
    require("img_get.php");
}
Input();
?>