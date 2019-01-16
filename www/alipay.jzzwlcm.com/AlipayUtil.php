<?php
/*
 * [Rocky 2017-05-26 02:13:18]
 * 支付宝操作
 */
// declare(encoding='UTF-8');
namespace Alipay;
require_once("cfg.php");

class Cfg
{
    const ALIPAY_GATEWAY_URL    = "https://openapi.alipay.com/gateway.do";
    const ALIPAY_URL_AUTHORIZE  = "https://openauth.alipay.com/oauth2/publicAppAuthorize.htm";
    const ALIPAY_URL_AUTH       = "https://openauth.alipay.com/oauth2/appToAppAuth.htm";
}

class Util
{
    // 发送post
    public static function HttpPost($url, $content, $timeout=60)
    {
        // $content = http_build_query($array, '', '&');
        $context = [
            'http' => [
                'timeout' => $timeout,
                'method'  => 'POST',
                'header'  => 'Content-type:application/x-www-form-urlencoded',
                'header'  => 'Content-type: application/x-www-form-urlencoded \r\n'
                           . 'Content-Length: ' . strlen($content) . '\r\n',
                'content' => $content,
            ]
        ];
        return file_get_contents($url, false, stream_context_create($context));
    }

     // 获取code
    public static function GetCode($scope = 'auth_userinfo')
    {
        $redirect_uri = urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
        $url = Cfg::ALIPAY_URL_AUTHORIZE
             . "?app_id=" . \Cfg::instance()->alipay->appid
             . "&scope=$scope"
             . "&redirect_uri=$redirect_uri";
        $code = $_REQUEST['auth_code'];
        if($code)
        {
            return $code;
        }
        header("Location:".$url);
        exit();
    }
}

?>
