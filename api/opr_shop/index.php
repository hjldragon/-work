<?php
/*
 * [rockyshi 2014-10-04]
 *
 */
ob_start();
require_once("current_dir_env.php");
ob_end_clean();

// 允许跨域访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
$opr = $_['opr'];
    /*
     * 使用加密签名后的数据
     */
    //LogDebug($_);
    switch ($opr) {
        case 'login'://接口名称
            $GLOBALS['no_need_code'] = true;
            $_['login'] = true;
            require("login_save.php");
            break;
        case 'app_user_setting'://接口名称
            $_['app_user_setting'] = true;
            require("login_save.php");
            break;
        case 'get_phone_code':
            $_['get_phone_code'] = true;
            require("login_save.php");
            break;
        case 'app_set_user':
            $_['app_set_user'] = true;
            require("login_save.php");
            break;
        case 'login_shop':
            $_['login_shop'] = true;
            require("login_save.php");
            break;
        case 'shop_save':
            $_['save'] = true;
            require("shop_save.php");
            break;
        case 'get_shopinfo_base':
            $_['get_shopinfo_base'] = true;
            require("shopinfo_get.php");
            break;
        case 'get_shop_business':
            $_['get_shop_business'] = true;
            require("shopinfo_get.php");
            break;
        case 'save_shop_business':
            $_['save_shop_business'] = true;
            require("shopinfo_save.php");
            break;
        case 'get_shop_bs_status':
            $_['get_shop_bs_status'] = true;
            require("shopinfo_get.php");
            break;
        case 'shopinfo_save':
            $_['shopinfo_save'] = true;
            require("shopinfo_save.php");
            break;
        case 'get_shopinfo_edit':
            $_['get_shopinfo_edit'] = true;
            require("shopinfo_get.php");
            break;
        case 'save_label':
            $_['save_label'] = true;
            require("shopinfo_save.php");
            break;
        case 'get_shop_label':
            $_['get_shop_label'] = true;
            require("shopinfo_get.php");
            break;
        case 'list':
            $_['list'] = true;
            require("category_get.php");
            break;
        case 'foodlist':
            $_['foodlist'] = true;
            require("menu_get.php");
            break;
        case 'menu_save':
            $_['save'] = true;
            require("menu_save.php");
            break;
        case 'foodinfo':
            $_['foodinfo'] = true;
            require("menu_get.php");
            break;
        case 'del_food':
            $_['del_food'] = true;
            require("menu_save.php");
            break;
        case 'type_list':
            $_['type_list'] = true;
            require("category_get.php");
            break;
        case 'category_save':
            $_['save'] = true;
            require("category_save.php");
            break;
        case 'category_delete':
            $_['delete'] = true;
            require("category_save.php");
            break;
        case 'get_seat_list':
            $_['get_seat_list'] = true;
            require("seat_get.php");
            break;
        case 'seat_save':
            $_['seat_save'] = true;
            require("seat_save.php");
            break;
        case 'seat_delete':
            $_['seat_delete'] = true;
            require("seat_save.php");
            break;
        case 'get_department_employee':
            $_['get_department_employee'] = true;
            require("department_get.php");
            break;
        case 'freeze_employee':
            $_['freeze_employee'] = true;
            require("employee_save.php");
            break;
        case 'get_employee_list':
            $_['get_employee_list'] = true;
            require("employee_get.php");
            break;
        case 'get_employee_info':
            $_['get_employee_info'] = true;
            require("employee_get.php");
            break;
        case 'save_employee_info':
            $_['save_employee_info'] = true;
            require("employee_save.php");
            break;
        case 'get_user_info':
            $_['get_user_info'] = true;
            require("employee_get.php");
            break;
        case 'shop_employee_save':
            $_['shop_employee_save'] = true;
            require("employee_save.php");
            break;
        case 'del_employee':
            $_['del_employee'] = true;
            require("employee_save.php");
            break;
        case 'department_save':
            $_['department_save'] = true;
            require("department_save.php");
            break;
        case 'department_del':
            $_['department_del'] = true;
            require("department_save.php");
            break;
        case 'get_department_list':
            $_['get_department_list'] = true;
            require("department_get.php");
            break;
        case 'get_department_info':
            $_['get_department_info'] = true;
            require("department_get.php");
            break;
        case 'edit_user_info':
            $_['edit_user_info'] = true;
            require("user_info.php");
            break;
        case 'get_shop_user_info':
            $_['get_user_info'] = true;
            require("user_info.php");
            break;
        case 'get_position_list':
            $_['get_position_list'] = true;
            require("position_get.php");
            break;
        case 'get_position_info':
            $_['get_position_info'] = true;
            require("position_get.php");
            break;
        case 'save_position':
            $_['save_position'] = true;
            require("position_save.php");
            break;
        case 'del_position':
            $_['del_position'] = true;
            require("position_save.php");
            break;
        case 'save_password':
            $_['save_password'] = true;
            require("user_info.php");
            break;
        case 'save_new_passwd':
            $_['save_new_passwd'] = true;
            require("login_save.php");
            break;
        case 'post_feedback':
            $_['user_feedback_save'] = true;
            require("user_feedback_save.php");
            //require("");
            break;
        case 'logout':
            $_['logout'] = true;
            require("login_save.php");
            break;
        case 'check_app_version':
            $_['version_get'] = true;
            require("version_get.php");
//            $b = (object)['ret'=>0,'data'=>(object)[
//                "need_update"   =>false,
//                "force_update" =>false,
//                "last_version_code"     => '1.0.0',
//                "last_version_name"     => '呵呵',
//                "last_version_desc"     => '呵呵哒,这里是死代码',
//                "last_version_url"      => 'url.apk',
//
//            ]];
//            echo json_encode($b);
            break;
        case 'about_us':
            $_['about_us'] = true;
            //<<<<<<<临时数据
            $b = (object)['ret'=>0,'data'=>(object)[
                "title"   =>"赛领v1.0.1",
                "content" =>"修改了一些Bug",
                "img"     =>"610a0b14f5c74256a028ed6c2a2596a2.jpg"
            ]];
            echo json_encode($b);
            //require("");
            break;
        case 'sysnews_list':
            $_['sysnews_list'] = true;
            require("news_get.php");
            break;
        case 'get_order_stat':
            $_['get_order_stat'] = true;
            require("order_get.php");
            break;
        case 'accessory':
            $_['accessory'] = true;
            require("menu_get.php");
            break;
        case 'get_authorize':
            $_['get_shop_authorize'] = true;
            require("shop_get.php");
            break;
        case 'save_authorize':
            $_['save_authorize'] = true;
            require("shop_save.php");
            break;
         case 'app_user_agreement':
            require("app_user_agreement.php");
            break;
        case 'code':
            require("code.php");
            break;
       case 'login_wx_app':
           $_['login_wx_app'] = true;
            require("login_save.php");
            break;
        case 'app_new_url':
            require("app_new_url.php");
            break;
        case 'get_rsa_publickey':
            $_REQUEST['publickey'] = true;
            require("rsa_info.php");
            break;
        case 'save_key':
            $_REQUEST['save_key'] = true;
            require("rsa_info.php");
            break;
        case 'upload':
            $_REQUEST['upload'] = true;
            require("img_save.php");
            break;
        case 'get_food_qrcode':
            $_REQUEST['get_food_qrcode'] = true;
            require("img_get.php");
            break;
        default:
//            if (is_readable("./opr_shop/app_{$opr}.php")) {
//                require("./opr_shop/app_{$opr}.php");
//            } else {
                LogDebug("unknown opr:[$opr]");
                $html = json_encode((object)[
                    'ret'  => -1,
                    'data' => (object)[
                        'msg' => "unknown err"
                    ]
                ]);
                echo $html;//传送给前台
            //}
            break;

}
?>