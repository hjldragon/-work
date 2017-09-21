<?php
/*
 * [rockyshi 2014-10-04]
 * 用户信息表
 *
 */
require_once("current_dir_env.php");

$openid = $_REQUEST['openid'];
if(!$openid)
{
    $url = "http://wx.jzzwlcm.com/wx_openid.php";
    header("Location: $url");
    exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!--
微信号：shizw2008
-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="./js/jquery-1.7.2.min.js"></script>
<script type="text/javascript">
</script>
<script type="text/javascript">
$(function() {
    location.href = "menu.php";
});
</script>