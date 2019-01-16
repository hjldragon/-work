<?php
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/page_util.php");
require_once("mgo_user.php");
require_once("mgo_employee.php");
require_once("mgo_platformer.php");
require_once("mgo_shop.php");
require_once("/www/public.sailing.com/php/weixin/wx_util.inc");
require_once("redis_login.php");
require_once("redis_id.php");
use \Pub\Mongodb as Mgo;

function LoginWx(&$resp)
{
    $req = \Wx\Util::GetAccessToken();
    $openid = $req->openid;
    $access_token = $req->access_token;

    if(!$openid)
    {
        LogErr("Openid err");
        return errcode::PARAM_ERR;
    }
    $mgo = new \DaoMongodb\Weixin;
    //先找微信绑定的店铺账号
    $weixin = $mgo->QueryByOpenId($openid, Src::SHOP, WxSrcType::PC);
    $userid = $weixin->userid;

    if(!$userid)
    {
        // 再找微信绑定的平台账号
        $wx = $mgo->QueryByOpenId($openid, Src::PLATFORM);
        $userid = $wx->userid;
        if(!$userid)
        {
            $wx_id      = $weixin->id?$weixin->id:$wx->id;
            $headimgurl = $weixin->headimgurl?$weixin->headimgurl:$wx->headimgurl;
            $nickname   = $weixin->nickname?$weixin->nickname:$wx->nickname;
            if(!$wx_id)
            {
                $wxinfo = $mgo->QueryByOpenId($openid, Src::NO);
                $wx_id = $wxinfo->id;
                // 微信获取用户信息
                $user = \Wx\Util::GetUserInfo($access_token,$openid);
                $user->id  = $wx_id;
                $user->src = Src::NO;
                // 存入通用端微信信息
                $ret = WeixinSave($user, $wx_id);
                //LogDebug($ret);
                if(0 != $ret){
                    LogErr("WeixinSave err");
                    return errcode::PARAM_ERR;
                }
                $headimgurl = $user->headimgurl;
                $nickname = $user->nickname;
            }

            $resp = (object)array(
                'wx_id'      => $wx_id,
                'headimgurl' => $headimgurl,
                'nickname'   => $nickname
            );
            return errcode::WEIXIN_NO_LOGIN;
        }
        $resp = (object)array(
            'userid' => $userid,
            'platform_id'=> PlatformID::ID
        );
        return 0;
    }

    $resp = (object)array(
        'userid' => $userid
    );
    return 0;
}


function WeixinSave($info, &$wx_id=null)
{
    if(!$info)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $id          = $info->id;
    $userid      = $info->userid;
    $headimgurl  = $info->headimgurl;
    $nickname    = $info->nickname;
    $sex         = $info->sex;
    $openid      = $info->openid;
    $city        = $info->city;
    $country     = $info->country;
    $province    = $info->province;
    $src         = $info->src;
    $srctype     = $info->srctype;


    if(!$id)
    {
        $id = \DaoRedis\Id::GenWeixinId();
    }
    $entry = new \DaoMongodb\WeixinEntry;
    $mongodb = new \DaoMongodb\Weixin;

    $entry->id          = $id;
    $entry->userid      = $userid;
    $entry->headimgurl  = $headimgurl;
    $entry->nickname    = $nickname;
    $entry->sex         = $sex;
    $entry->openid      = $openid;
    $entry->city        = $city;
    $entry->province    = $province;
    $entry->country     = $country;
    $entry->src         = $src;
    $entry->delete      = 0;
    $entry->srctype     = $srctype;
    $entry->lastmodtime = time();
    $ret = $mongodb->Save($entry);
    LogDebug($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $wx_id = $id;
    LogInfo("save ok");
    return 0;
}

$_ = $_REQUEST;
$type = (int)$_['type'];
$resp = (object)array();
$ret = -1;
$ret = LoginWx($resp);
$token = $_COOKIE["token"];
if(!$token)
{
    $token = 'T3' . Util::GetRandString(14);
    setcookie("token", $token);
}
if($resp->userid)
{
    $redis = new \DaoRedis\Login();
    $login_info  = new \DaoRedis\LoginEntry();

    $login_info->token    = $token;
    $login_info->userid   = $userid;
    $login_info->login    = 1;

    $redis->Save($login_info);
}
$resp->ret   = $ret;
$resp->type  = $type;
$resp->token = $token;
$main_domain = Cfg::instance()->GetMainDomain();
$model = "operators";
if(0 != $ret)
{
    $model = "login";
}
$url = "http://platform.$main_domain:8084/assistant/#/$model?".http_build_query($resp);//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<正式环境去掉端口号

header("HTTP/1.1 302 See Other");
header("Location: $url");