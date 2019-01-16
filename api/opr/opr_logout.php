<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{
    $_            = &$GLOBALS["_"];
    $_['logout']  = true;
    require("login_save.php");
    //LogDebug($_);
}
function Output(&$obj)
{
    echo json_encode($obj);
}
Input();
?>