<?php
declare(encoding='UTF-8');
namespace Wx;
ini_set('date.timezone','Asia/Shanghai');
require_once "WxUtil.php";



$openid = \Wx\Util::GetOpenid();

echo "openid:$openid";

$url = "http://of.jzzwlcm.com/a.php?openid=$openid";
header("Location: $url");

?>