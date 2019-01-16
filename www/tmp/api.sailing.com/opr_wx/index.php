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
$_ = $GLOBALS['_'];  // 使用加密签名后的数据

// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<调试错误日志用的
//Log::instance()->SetFile("/log/api.jzzwlcm.com/log.txt");
//Log::instance()->SetLevel(4);
//LogErr("xxxxxxxxxxx");



if(!$_['opr']) {
    $_ = $_REQUEST;  // 直接是http提交的数据
}
$opr = $_['opr'];
    /*
     * 使用加密签名后的数据
     */
    //LogDebug($_);
    switch ($opr) {
        case 'app_binding_wx':
            $_['binding_wx'] = true;
            require("wx_app_binding.php");
            break;
        case 'app_unbundle_wx':
            $_['unbundle_wx'] = true;
            require("wx_app_binding.php");
            break;
        default:
            if (is_readable("./opr_wx/opr_{$opr}.php")) {
                require("./opr_wx/opr_{$opr}.php");
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