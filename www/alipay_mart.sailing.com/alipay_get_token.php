
<?php
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once("AlipayUtil.php");
require_once("AopClient.php");
require_once("cache.php");
require_once("SignData.php");
require_once("AlipaySystemOauthTokenRequest.php");



function GetToken()
{
    $_ = $_REQUEST;
    LogDebug($_);
    $code = $_['auth_code'];
    if(!$code)
    {
        $scope = 'auth_user';
        $code = \Alipay\Util::GetCode($scope);
    }
    $aop = new AopClient ();
    $aop->gatewayUrl         = \Alipay\Cfg::ALIPAY_GATEWAY_URL;
    $aop->appId              = Cfg::instance()->alipay->appid;
    $aop->rsaPrivateKey      = Cfg::instance()->alipay->privatekey;
    $aop->alipayrsaPublicKey = Cfg::instance()->alipay->publickey;
    $aop->apiVersion         = '1.0';
    $aop->signType           = 'RSA2';
    $aop->postCharset        = 'UTF-8';
    $aop->format             = 'json';
    $request = new AlipaySystemOauthTokenRequest();
    $request->setGrantType("authorization_code");
    $request->setCode($code);
    $result = $aop->execute($request);
    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
    $data = (object)[];
    $data->access_token = $result->$responseNode->access_token;
    $data->user_id = $result->$responseNode->user_id;
    LogDebug($result);
    return $data;
}
?>