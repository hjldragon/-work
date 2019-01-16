<?php
declare(encoding='UTF-8');
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once("current_dir_env.php");
require_once "WxUtil.php";
require_once("user_save.php");
require_once("weixin_save.php");
require_once("cache.php");
require_once("mgo_agent_apply.php");
require_once("cfg.php");


$req          = \Pub\Wx\Util::GetOpenid();
$openid       = $req->openid;
$access_token = $req->access_token;
$_            = $_REQUEST;
$src          = $_['src'];//代理商申请页面有该参数
LogDebug($req);
if(!$openid){
    LogErr("Openid err");
    echo "系统忙...";exit(0);
}
function WXSrcThree($src, $openid, $access_token)
{
    $weixin = \Cache\Weixin::Get($openid,$src);
    if(!$weixin->id)
    {
        $user = \Pub\Wx\Util::GetUserInfo($access_token,$openid);
        if($openid != $user->openid){
            LogErr("User err");
            echo "系统忙...";exit(0);
        }
        $id           = \DaoRedis\Id::GenWeixinId();
        $user->id     = $id;
        $user->src    = $src;
        $ret = WeixinSave($user);
        //LogDebug($ret);
        if(0 != $ret){
            LogErr("WeixinSave err");
            echo "系统忙...";exit(0);
        }
    }else{
        $id = $weixin->id;
    }

    $apply = new \DaoMongodb\AgentApply;
    $agent_apply_list = $apply->GetInfoByWxIdList(['wx_id'=>$id],['sub_time'=> -1]);
    // 跳回调用页，并带上openid
    $main_domain = Cfg::instance()->GetMainDomain();


    if($agent_apply_list[0]->apply_status)
    {
        $url = "http://platform.$main_domain/php/gz_login.php?wx_id=$id&status=1";
    }else{
        $url = "http://platform.$main_domain/php/gz_login.php?wx_id=$id";
    }


    return $url;
}

function WxTz($openid, $access_token)
{

    $weixin = \Cache\Weixin::Get($openid,Src::CUSTOMER);
    if(!$weixin->userid){
        $user = \Pub\Wx\Util::GetUserInfo($access_token,$openid);
        if($openid != $user->openid)
        {
            LogErr("User err");
            echo "系统忙...";exit(0);
        }
        // if($user->headimgurl){
        //     $data = file_get_contents($user->headimgurl);
        //     $filemd5 = md5($data);
        //     $destfile = Cfg::GetUserImgFullname($filemd5);
        //     file_put_contents($destfile, $data);
        // }
        if($user->headimgurl)
        {
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
        if($filemd5)
        {
            $info['user_avater'] = $filemd5;
        }
        $info['usernick'] = $user->nickname;
        $info['sex'] = $user->sex;
        $userinfo = UserSave($info);
        if(0 != $userinfo->ret)
        {
            LogErr("UserSave err");
            echo "系统忙...";exit(0);
        }
        $user->id     = $weixin->id;
        $user->userid = $userinfo->data->userid;
        $user->src    = Src::CUSTOMER;
        $ret = WeixinSave($user);
        //LogDebug($ret);
        if(0 != $ret)
        {
            LogErr("WeixinSave err");
            echo "系统忙...";exit(0);
        }
        $userid = $user->userid;
    }
    else
    {
        $userid = $weixin->userid;
    }

    // 跳回调用页，并带上openid
    $main_domain = Cfg::instance()->GetMainDomain();
    $url = "http://customer.$main_domain/index.php?userid=$userid&{$_SERVER['QUERY_STRING']}";
    return $url;

}

if($src)
{
    $url = WXSrcThree($src, $openid, $access_token);
}
else
{
    $url  = WxTz($openid, $access_token);
}
header("HTTP/1.1 302 See Other");
header("Location: $url");

?>