<?php
ini_set('date.timezone','Asia/Shanghai');
require_once "WxUtil.php";
$code = $_REQUEST['code'];
var_dump($code);
if($code){
    setcookie('code',$code);
}

var_dump($_COOKIE['code']);die;
?>