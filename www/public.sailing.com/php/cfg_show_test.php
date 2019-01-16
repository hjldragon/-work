<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<style type="text/css">
body {
    font-size: 16px;
}
input {
    font-size: 24px;
}
</style>
<?php
require_once("current_dir_env.php");
if($_REQUEST['passwd'] == "sailing")
{
    echo "<pre>";
    echo '__FILE__: [' . __FILE__ . "]\n";
    echo 'DOCUMENT_ROOT: [' . $_SERVER["DOCUMENT_ROOT"] . "]\n";
    echo 'include_path: [' . get_include_path() . "]\n";
    echo 'getcwd(): [' . getcwd() . "]\n";
    // ini_get_all()
    $inipath = php_ini_loaded_file();
    echo "{$inipath}->post_max_size: [" . ini_get('post_max_size') . "]\n";
    echo "{$inipath}->upload_max_filesize: [" . ini_get('upload_max_filesize') . "]\n";
    echo "<hr>";
    echo "当前配置(cfg.php):\n";
    $cfg = Cfg::instance();
    $cfg->rsa->privatekey = "*";
    echo json_encode($cfg, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    // exit(0);
}
else
{
    echo <<<eof
<form>
请输入密码: <input name="passwd" value="{$_REQUEST['passwd']}"> <input type="submit" value="提交">
</form>
eof;
}
?>
