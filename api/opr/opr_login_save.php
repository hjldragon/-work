<?php
/*
 *
 */
require_once("current_dir_env.php");


function Input()
{
    $GLOBALS['no_need_code'] = true; // 不是android外包时处理

    $_ = &$GLOBALS["_"];
    $_["phone"]        = $_['account_phone'];
    $_["password_md5"] = md5($_["password"]);
    $_['login']        = true;
    require("login_save.php");
    LogDebug($_);
}


function Output(&$obj)
{
    $obj = (object)$obj;
    $shop_list = [];
    LogDebug($obj->data->shopinfo);
    foreach ($obj->data->shopinfo as $info)
    {
        $domain        = Cfg::instance()->GetMainDomain();
        $shop_icon_url = "http://api.$domain/?opr=get_img&imgname={$info['shop_logo']}";
        $shop_list[] = (object)[
            'shop_id'   => $info['shop_id'],
            'shop_name' => $info['shop_name'],
            'shop_icon' => $shop_icon_url,
        ];
    }
    $obj->data = (object)[
        'shop' => $shop_list
    ];
    // unset($obj->data->logininfo);
    // unset($obj->data->userinfo);
    // unset($obj->data->shopinfo);
    $html =  json_encode($obj);
    echo $html;
    LogDebug($html);
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