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
$opr = $_['opr'];
if($opr)
{
    /*
     * 使用加密签名后的数据
     */
    //LogDebug($_);
    switch ($opr) {
        case 'login'://接口名称
            $_['login'] = true;
            require("login_save.php");
            break;
        case 'setting_user'://接口名称
            $_['setting_user'] = true;
            require("login_save.php");
            break;
        case 'get_phone_code':
            $_['get_phone_code'] = true;
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
        case 'foodlist':
            $_['foodlist'] = true;
            require("menu_get.php");
            break;
        case 'menu_save':
            $_['save'] = true;
            require("menu_save.php");
            break;
        default:
            LogDebug("unknown opr:[$opr]");
            break;
    }
}
else
{
    /*
     * http直接发过来的数据（不使用加密签名）
     */
    LogDebug($_);
    $opr = $_REQUEST['opr'];
    switch ($opr) {
        case 'get_rsa_pubkey':
            require("./opr/opr_get_rsa_pubkey.php");
            require("rsa_info.php");
            break;
        default:
            if(is_readable("{$opr}.php"))
            {
                require("{$opr}.php");
            }
            else
            {
                LogDebug("unknown opr:[$opr]");
                $html =json_encode((object)array(
                    'ret' => -1,
                    'data'=> (object)[
                        'msg' => "unknown err"
                    ]
                ));
                echo $html;//传送给前台
            }
            break;
    }

}
?>