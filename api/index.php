<?php
/*
 * [rockyshi 2014-10-04]
 * 用户信息表
 *
 */
ob_start();
require_once("current_dir_env.php");
ob_end_clean();


// 允许跨域访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');


// function main()
// {

    $_ = $GLOBALS['_'];  // 使用加密签名后的数据

    if(!$_['opr']) {
        $_ = $_REQUEST;  // 直接是http提交的数据
    }
    $opr = $_['opr'];
    if (2 == $_['srctype']) {
        require("./opr_shop/current_dir_env.php");
        require("./opr_shop/index.php");
        exit(0); // return;
    }
    //else if (3 == $_['srctype']) {
    //    require("./opr_dining/current_dir_env.php");
    //    //require("./opr_dining/index.php");
    //    exit(0); // return;
    //}
    else {
        $GLOBALS['need_json_obj'] = true;
    }

    require("./opr/current_dir_env.php");
    switch ($opr) {
        case 'login_save'://接口名称
            require("./opr/opr_login_save.php");
            break;
        case 'order_info'://接口名称
            require("./opr/opr_order_info.php");
            break;
        case 'get_order_list'://接口名称
            require("./opr/opr_get_order_list.php");
            break;
        case 'made_order_status'://接口名称
            require("./opr/opr_made_order_status.php");
            break;
        case 'get_reservation_all'://接口名称
            require("./opr/opr_get_reservation_all.php");
            break;
        case 'save_reservation'://接口名称
            require("./opr/opr_save_reservation.php");
            break;
        case 'food_list'://接口名称
            require("./opr/opr_food_list.php");
            break;
        case 'get_seat_list'://接口名称
            require("./opr/opr_get_seat_list.php");
            break;
        case 'get_shop_info'://接口名称
            require("./opr/opr_get_shop_info.php");
            break;
        case 'get_order_pendding'://接口名称
            require("./opr/opr_get_order_pendding.php");
            break;
        case 'order_save'://接口名称
            require("./opr/opr_order_save.php");
            break;
        case 'order_post_pay'://接口名称
            require("./opr/opr_order_post_pay.php");
            break;
        case 'order_advance'://接口名称
            require("./opr/opr_order_advance.php");
            break;
        case 'get_shop_qrcode'://接口名称
            require("./opr/opr_get_shop_qrcode.php");
            break;
        case 'save_reservation_state'://接口名称
            require("./opr/opr_save_reservation_state.php");
            break;
        case 'sysnews_list'://接口名称
            require("./opr/opr_sysnews_list.php");
            break;
        case 'sync_base_settings'://接口名称
            require("./opr/opr_sync_base_settings.php");
            break;
        case 'check_new_version'://接口名称
            require("./opr/opr_check_new_version.php");
            break;
        case 'post_feedback'://接口名称
            require("./opr/opr_post_feedback.php");
            break;
        case 'get_food_type_list'://接口名称
            require("./opr/opr_get_food_type_list.php");
            break;
        case 'get_print_info'://接口名称
            require("./opr/opr_get_print_info.php");
            break;
        case 'get_rsa_pubkey':
            require("./opr/opr_get_rsa_pubkey.php");
            require("rsa_info.php");
            break;
        case 'save_data_key':
            require("./opr/opr_save_data_key.php");
            require("rsa_info.php");
            break;
        case 'get_img':
            require("./opr/opr_get_img.php");
            break;
        case 'get_news_content'://接口名称
            require("./opr/opr_get_news_content.php");
            break;
        case 'qr_login':
            require("./opr/opr_qr_login.php");
            //require("../wx.jzzwlcm.com/wx_login.php");
            break;
        default:
            if (is_readable("./opr/opr_{$opr}.php")) {
                require("./opr/opr_{$opr}.php");
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
// }
// main();

?>