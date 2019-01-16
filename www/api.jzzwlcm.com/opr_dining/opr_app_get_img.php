<?php
/*
 *
 */
ob_start();
//require_once("current_dir_env.php");
set_include_path(dirname(__FILE__) . "/" . ":./opr:/www/dining.jzzwlcm.com/php/" );
date_default_timezone_set('Asia/Shanghai');   // 2015-01-28 20:49:45
require_once("global.php");
ob_end_clean();


function Input()
{
    $_ = &$_REQUEST; //&$GLOBALS["_"];
    $_['img'] = 1;
    LogDebug($_);
}


function Output(&$obj)
{
}

Input();
?>