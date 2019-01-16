<?php
// 公共目录
// const PHP_PUBLIC_DIR = '/www/public.sailing.com/php';
define('PHP_PUBLIC_DIR', '/www/public.sailing.com/php');
ob_start();
set_include_path(__DIR__."/:/www/public.sailing.com/php/:/www/public.sailing.com/php/weixin/");
date_default_timezone_set('Asia/Shanghai');   // 2015-01-28 20:49:45
require_once("global.php");
ob_end_clean();
?>
