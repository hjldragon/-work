<?php
ini_set('date.timezone','Asia/Shanghai');
require_once("WxCfg.php");
// require_once "WxUtil.php";
// $code = $_REQUEST['code'];
// var_dump($code);
// if($code){
//     setcookie('code',$code);
// }

// var_dump($_COOKIE['code']);die;
// print_r($_REQUEST);
// die;
$url = $_REQUEST['url'];
$scope = $_REQUEST['scope']?:'snsapi_base';
$code = $_REQUEST['code'];
$debug = $_REQUEST['debug'];

if(!$url)
{
    echo "param err, url:[$url]";
    exit(0);
}

if(!$code)
{
    $redirect_uri = urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    $auth_url = \Pub\Wx\Cfg::WX_URL_AUTHORIZE
         . "?appid=" . \Pub\Wx\Cfg::APPID
         . "&redirect_uri=$redirect_uri"
         . "&response_type=code&scope=$scope&state=1236&connect_redirect=1#wechat_redirect";
    header("Location: $auth_url");
    exit();
}

if(strchr($url, '?') === false)
{
    $url .= '?';
}
$url .= "&code=$code";

if($debug)
{
    echo "$url";
    exit(0);
}

// print_r($_REQUEST);
// echo "$url";
header("Location: $url");
