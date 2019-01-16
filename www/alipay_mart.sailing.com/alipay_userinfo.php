<?php
declare(encoding='UTF-8');
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once("current_dir_env.php");
require_once("AlipayUtil.php");
require_once("AopClient.php");
require_once("cache.php");
require_once("SignData.php");
require_once("AlipayUserInfoShareRequest.php");
require_once("alipay_get_token.php");
require_once("mgo_alipay.php");
require_once("user_save.php");
require_once("alipay_save.php");

$req     = GetToken();
$alipay_id = $req->user_id;
if(!$alipay_id){
    LogErr("alipay_id err");
    echo "系统忙...";exit(0);
}
$access_token = $req->access_token;
$mgo = new \DaoMongodb\Alipay;
$alipay_user = $mgo->QueryByAlipayid($alipay_id, 1);
//LogDebug($alipay_user);
if(!$alipay_user->userid){
    $user = GetUserInfo($access_token);
    if($alipay_user->alipay_id != $user->user_id){
        LogErr("User err");
        echo "系统忙...";exit(0);
    }
    // if($user->headimgurl){
    //     $data = file_get_contents($user->headimgurl);
    //     $filemd5 = md5($data);
    //     $destfile = Cfg::GetUserImgFullname($filemd5);
    //     file_put_contents($destfile, $data);
    // }
    if($user->avatar){
        $filemd5 = md5($user->avatar);
        $destfile = Cfg::GetUserImgFullname($filemd5);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch,CURLOPT_URL,$user->avatar);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $file_content = curl_exec($ch);
        curl_close($ch);
        $downloaded_file = fopen($destfile, 'w');
        fwrite($downloaded_file, $file_content);
        fclose($downloaded_file);
    }
    if($filemd5){
        $info['user_avater'] = $filemd5;
    }
    $info['usernick'] = $user->nick_name;
    $sex = SEX::$sex[$user->gender];
    $info['sex'] = $sex;
    $userinfo = UserSave($info);
    if(0 != $userinfo->ret){
    	LogErr("UserSave err");
        echo "系统忙...";exit(0);
    }
    $user->id     = $alipay_user->id;
	$user->userid = $userinfo->data->userid;
    $user->src    = 1;
    $user->sex    = $sex;
	$ret = AlipaySave($user);
    LogDebug($ret);
	if(0 != $ret){
    	LogErr("AlipaySave err");
        echo "系统忙...";exit(0);
    }
    $userid = $user->userid;
 }else{
 	$userid = $alipay_user->userid;
 }


 function GetUserInfo($access_token)
 {
    $aop = new AopClient ();
    $aop->gatewayUrl         = \Alipay\Cfg::ALIPAY_GATEWAY_URL;
    $aop->appId              = Cfg::instance()->alipay->appid;
    $aop->rsaPrivateKey      = Cfg::instance()->alipay->privatekey;
    $aop->alipayrsaPublicKey = Cfg::instance()->alipay->publickey;
    $aop->apiVersion         = '1.0';
    $aop->signType           = 'RSA2';
    $aop->postCharset        = 'UTF-8';
    $aop->format             = 'json';
    $request = new AlipayUserInfoShareRequest();
    $result = $aop->execute($request, $access_token);

    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";

    return $result->$responseNode;
 }



 // 跳回调用页
$main_domain = Cfg::instance()->GetMainDomain();
$url = "http://customer.$main_domain/index.php?userid=$userid&{$_SERVER['QUERY_STRING']}";

header("HTTP/1.1 302 See Other");
header("Location: $url");
?>

