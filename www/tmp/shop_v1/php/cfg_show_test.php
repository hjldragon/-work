<?php
require_once("current_dir_env.php");
if($_REQUEST['passwd'] == "sailing")
{
    echo "<pre>";
    $cfg = Cfg::instance();
    $cfg->rsa->privatekey = "*";
    echo json_encode($cfg, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    exit(0);
}
?>

<body>
<style type="text/css">
input {
    font-size: 24px;
}
</style>
<form>
请输入密码: <input name="passwd"> <input type="submit" value="提交">
</form>