<?php
// 公共目录
// const PHP_PUBLIC_DIR = '/www/public.sailing.com/php';
define('PHP_PUBLIC_DIR', '/www/public.sailing.com/php');
ob_start();
set_include_path(__DIR__."/:".__DIR__."/php/:".PHP_PUBLIC_DIR."/:".PHP_PUBLIC_DIR."/weixin/");
///set_include_path(dirname(__FILE__) . "/" . ":/www/platform.jzzwlcm.com/php/"."/:/www/public.sailing.com/php/");
date_default_timezone_set('Asia/Shanghai');   // 2015-01-28 20:49:45
require_once("global.php");
ob_end_clean();
?>
