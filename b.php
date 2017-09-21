<?php
/*
 * [rockyshi 2014-10-04]
 * 用户信息表
 *
 */
require_once("current_dir_env.php");
Log::instance()->SetLevel(4);
Log::instance()->SetFile("/home/log/ordering/log.txt");
LogErr("xxxxxxxxx");
if(!$_REQUEST['openid'])
{
    $url = "http://wx.jzzwlcm.com/wx_openid.php";
    header("Location: $url");
    exit();
}
LogErr($_REQUEST['openid']);
// echo "<pre>";
echo $_REQUEST['openid'];
// exit();

?><?php /******************************以下为html代码******************************/?>
<?=$html?>
