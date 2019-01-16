<?php
declare(encoding='UTF-8');
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once "WxUtil.php";
require_once("user_save.php");
require_once("weixin_save.php");
require_once("cache.php");
require_once("cfg.php");


$req = \Wx\Util::GetOpenid();
$openid = $req->openid;
if(!$openid){
    LogErr("Openid err");
    echo "系统忙...1";exit(0);
}
$access_token = $req->access_token;
$weixin = \Cache\Weixin::Get($openid,1);
if(!$weixin->userid){
    $user = \Wx\Util::GetUserInfo($access_token,$openid);
    if($openid != $user->openid){
        LogErr("User err");
        echo "系统忙...2";exit(0);
    }
    // if($user->headimgurl){
    //     $data = file_get_contents($user->headimgurl);
    //     $filemd5 = md5($data);
    //     $destfile = Cfg::GetUserImgFullname($filemd5);
    //     file_put_contents($destfile, $data);
    // }
    if($user->headimgurl){
        $filemd5 = md5($user->headimgurl);
        $destfile = Cfg::GetUserImgFullname($filemd5);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch,CURLOPT_URL,$user->headimgurl);
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
    $info['usernick'] = $user->nickname;
    $info['sex'] = $user->sex;
    $userinfo = UserSave($info);
    if(0 != $userinfo->ret){
    	LogErr("UserSave err");
        echo "系统忙...3";exit(0);
    }
    $user->id     = $weixin->id;
	$user->userid = $userinfo->data->userid;
    $user->src    = 1;
	$ret = WeixinSave($user);
    LogDebug($ret);
	if(0 != $ret){
    	LogErr("WeixinSave err");
        echo "系统忙...4";exit(0);
    }
    $userid = $user->userid;
 }else{
 	$userid = $weixin->userid;
 }



 // 跳回调用页，并带上openid
$main_domain = Cfg::instance()->GetMainDomain();
$url = "http://customer.$main_domain/index.php?userid=$userid&{$_SERVER['QUERY_STRING']}";

header("HTTP/1.1 302 See Other");
header("Location: $url");
?>