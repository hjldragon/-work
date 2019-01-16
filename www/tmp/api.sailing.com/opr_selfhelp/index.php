<?php
/*
 * [rockyshi 2014-10-04]
 *
 */
ob_start();
require_once("current_dir_env.php");
ob_end_clean();

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

// $html_out_callback = function($ret, $data, $opt){
//     echo (object)array(
//         'ret'  => $ret,
//         'msg'  => errcode::toString($ret),
//         'data' => $resp
//     );
// };
//$html_out_callback = function($out){
//    echo json_encode($out);
//};

    /*
     * 使用加密签名后的数据
     */
  //LogDebug($_);
    switch ($opr) {
        case 'login'://接口名称
            $_['login'] = true;
            require("login_save.php");
            break;
        case 'selfhelp_save'://接口名称
            $_['selfhelp_save'] = true;
            require("selfhelp_save.php");
            break;
        case 'selfhelp_unbinding'://接口名称
            $_['selfhelp_unbinding'] = true;
            require("selfhelp_save.php");
            break;
        case 'shopid_unbinding':
            $_['shopid_unbinding'] = true;
            require("selfhelp_save.php");
            break;
        case 'get_selfhelp_info':
            $_['get_selfhelp_info'] = true;
            require("selfhelp_get.php");
            break;
        case 'version_get'://接口名称
            $_['version_get'] = true;
            require("version_get.php");
            break;
        case 'selfhelp_food_list'://接口名称
            $_['selfhelp_food_list'] = true;
            require("menu_get.php");
            break;
        case 'order_save'://接口名称
            $_['order_save'] = true;
            require("order_save.php");
            break;
        case 'login_wx'://接口名称
            $_['login_wx'] = true;
            require_once("login_save.php");
            break;
        case 'get_print_info'://接口名称
            $_['get_print_info'] = true;
            require_once("printer_get.php");
            break;
        case 'get_rsa_pubkey':
            $_REQUEST['publickey'] = true;
            require("rsa_info.php");
            break;
        case 'save_data_key':
            $_REQUEST['save_key'] = true;
            require("rsa_info.php");
            break;
        case 'qr_login':
            require("selfhelp_qr_login.php");
            break;
        case 'login_query':
            $_['login_query'] = true;
            require("login_save.php");
            break;
        case 'login_out':
            $_['logout'] = true;
            require("login_save.php");
            break;
        //支付接口
        case 'micropay':
            if($_['qr_name'] == 'wx')
            {
                require_once("selfhelp_wx_micropay.php");
            }elseif ($_['qr_name'] == 'alipay')
            {
                require_once("selfhelp_alipay_micropay.php");
            }else{
                $html = json_encode((object)[
                    'ret'  => -1,
                    'data' => (object)[
                        'msg' => "this pay way can not use"
                    ]
                ]);
                echo $html;//传送给前台
            }
            break;
            //查询支付接口
        case 'payquery':
            if($_['qr_name'] == 'wx')
            {
                require_once("selfhelp_wx_payquery.php");
            }elseif ($_['qr_name'] == 'alipay')
            {
                require_once("selfhelp_alipay_payquery.php");
            }else{
                $html = json_encode((object)[
                    'ret'  => -1,
                    'data' => (object)[
                        'msg' => "this pay way can not use"
                    ]
                ]);
                echo $html;//传送给前台
            }
            break;
        case 'qrpay':
            if($_['qr_name'] == 'wx_qrpay')
            {
                require_once("opr_selfhelp_wx_qrpay.php");
            }elseif ($_['qr_name'] == 'alipay_qrpay')
            {
                require_once("opr_selfhelp_alipay_qrpay.php");
            }else{
                $html = json_encode((object)[
                    'ret'  => -1,
                    'data' => (object)[
                        'msg' => "this pay way can not use"
                    ]
                ]);
                echo $html;//传送给前台
            }
            break;
        default:
            if (is_readable("./opr_selfhelp/opr_{$opr}.php")) {
                require("./opr_selfhelp/opr_{$opr}.php");
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