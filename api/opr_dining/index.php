<?php
/*
 * [rockyshi 2014-10-04]
 *
 */
//ob_start();

//require_once("current_dir_env.php");
set_include_path(dirname(__FILE__) . "/" . ":./opr:/www/dining.jzzwlcm.com/php/" );
date_default_timezone_set('Asia/Shanghai');   // 2015-01-28 20:49:45
require_once("global.php");
//ob_end_clean();

// 允许跨域访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
$_ = $GLOBALS['_'];  // 使用加密签名后的数据

if(!$_['opr']) {
    $_ = $_REQUEST;  // 直接是http提交的数据
}
$opr = $_['opr'];
    /*
     * 使用加密签名后的数据
     */
    //LogDebug($_);
    switch ($opr) {
        case 'app_food_list'://接口名称
            $_['app_food_list'] = true;
            require("menu_get.php");
            break;
        case 'app_seat_list'://接口名称
            $_['app_seat_list'] = true;
            require("seat_get.php");
            break;
        case 'app_order_save'://接口名称
            $_['app_order_save'] = true;
            require("order_save.php");
            break;
        case 'app_order_info'://接口名称
            $_['app_order_info'] = true;
            require("order_get.php");
            break;
        case 'app_order_pay'://接口名称
            $_['app_order_pay'] = true;
            require("order_save.php");
            break;
        case 'app_seat_info'://接口名称
            $_['app_seat_info'] = true;
            require("seat_get.php");
            break;
        case 'app_img_get'://接口名称
            require_once("opr_app_get_img.php");
            require("img_get.php");
            break;
        default:
            if (is_readable("./opr_dining/opr_{$opr}.php")) {
                require("./opr_dining/opr_{$opr}.php");
            } else {
                //LogDebug("unknown opr:[$opr]");
                $html = json_encode((object)[
                    'ret'  => -1,
                    'data' => (object)[
                        'msg' => "unknown err"
                    ]
                ]);
                echo $html;//传送给前台
            }
            break;

}
?>