<?php
/*
 *
 */
require_once("current_dir_env.php");


function Input()
{
    $_ = &$_REQUEST; //&$GLOBALS["_"];
    $_['publickey'] = true;
    $_['srctype']   = 4;
    LogDebug($_);
}


function Output(&$obj)
{
    $html = json_encode($obj);
    echo $html;
    LogDebug($html);
}

Input();
?>