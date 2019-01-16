<?php
// 公共目录
// const PHP_PUBLIC_DIR = '/www/public.sailing.com/php';
define('PHP_PUBLIC_DIR', '/www/public.sailing.com/php');

// 如是引用了public目录下的文件，则运行的当前路径装是public目录
// 这里修改为项目目录
chdir("{$_SERVER['DOCUMENT_ROOT']}/" . dirname($_SERVER['SCRIPT_NAME']));
if(is_file("{$_SERVER['DOCUMENT_ROOT']}/php/current_dir_env.php"))
{
    require_once("{$_SERVER['DOCUMENT_ROOT']}/php/current_dir_env.php");
}
else
{
    ob_start();
    set_include_path("{$_SERVER['DOCUMENT_ROOT']}/php/:".PHP_PUBLIC_DIR."/:".PHP_PUBLIC_DIR."/weixin/");
    date_default_timezone_set('Asia/Shanghai');   // 2015-01-28 20:49:45
    require_once("global.php");
    ob_end_clean();
}
