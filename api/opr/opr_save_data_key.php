<?php
/*
 *
 */
require_once("current_dir_env.php");


function Input()
{
    $_ = &$_REQUEST; //&$GLOBALS["_"];
    $_['save_key'] = true;
    $_['srctype']  = 3;
    LogDebug($_);
}


function Output(&$obj)
{
    $html =  json_encode($obj);
    echo $html;
    LogDebug($html);
}

Input();
?>