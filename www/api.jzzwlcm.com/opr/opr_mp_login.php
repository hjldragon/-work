<?php
/*
 *
 */
error_reporting(E_ALL & ~E_NOTICE);
set_include_path(dirname(__FILE__) . "/..:/www/wx.jzzwlcm.com/"."/:/www/public.sailing.com/php/");
date_default_timezone_set('Asia/Shanghai');   // 2015-01-28 20:49:45
require_once("global.php");

function Input()
{


    $_             = &$GLOBALS["_"];
    $_['mp_login'] = true;
    require("wx_mealpos_login.php");
    //LogDebug($_);
}


function Output(&$obj)
{

}


Input();


/*
<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
$a = [
    'a1' => 1
];
//$b = &$a;
$b = $a;
$b['a2'] = 2;
var_dump($a);
// array(1) {
//   ["a1"]=>
//   int(1)
// }


$a = (object)[
    'a1' => 1
];
$b = $a;
// $b['a2'] = 2;
$b->a2 = 2;
var_dump($a);
// object(stdClass)#1 (2) {
//   ["a1"]=>
//   int(1)
//   ["a2"]=>
//   int(2)
// }
*/
?>