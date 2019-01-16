<?php
/*
 *
 */
ob_start();
require_once("current_dir_env.php");
ob_end_clean();
function Input()
{
    $GLOBALS['no_need_code'] = true; // 不是android外包时处理

    $_                = &$GLOBALS["_"];
    $_['login_mp']    = true;
    require("login_save.php");
    //LogDebug($_);
}


function Output(&$obj)
{
    $html =  json_encode($obj);
    echo $html;
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