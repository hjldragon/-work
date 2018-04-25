<?php
declare(encoding='UTF-8');
namespace Wx;
ini_set('date.timezone','Asia/Shanghai');
require_once "WxUtil.php";
$_ = $_REQUEST;
$url = $_['url'];
$req = \Wx\Util::GetOpenid();
$openid = $req->openid;
$url = $url.'&openid='.$openid;
header("HTTP/1.1 302 See Other");
header("Location: $url");
?>

