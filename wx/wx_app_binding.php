<?php
require_once("current_dir_env.php");
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once "WxUtil.php";
require_once("mgo_user.php");
require_once("weixin_save.php");
require_once("cache.php");
require_once("redis_id.php");
require_once("cfg.php");
require_once("page_util.php");
require_once("const.php");
require_once("mgo_env.php");


function BindingSave(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $appid      = $_['appid'];
    $secret     = $_['secret'];
    $mch_id     = $_['mch_id'];
    $key        = $_['key'];
    $publickey  = $_['publickey'];
    $privatekey = $_['privatekey'];
    $srctype    = $_['srctype'];
    $code       = $_['code'];
    $userid     = $_['userid'];

    if(!$appid || !$secret  || !$code || !$userid || !$srctype)
    {
        LogErr("env some param is empty");
        return errcode::PARAM_ERR;
    }

    if($srctype == 2)
    {
        $env_id = EnvId::APP;
    }else{
        $env_id = 2;//还未定义用于扩展
    }
    //先获取用户openid
    $req = \Pub\Wx\Util::GetAppOpenid($appid, $secret, $code);
    LogDebug($req);
    if(!$req->openid)
    {
        LogErr("weixin code err:$req->errcode");
        return $req->errcode;
    }
    $weixin       = new \DaoMongodb\Weixin;
    $access_token = $req->access_token;
    $openid       = $req->openid;
    $src          = Src::SHOP;//来源是属于商户
    //判断用户是否已绑定
    $weixin_info  = $weixin->QueryByOpenId($openid, $src, $srctype);
    if($weixin_info->userid)
    {
        LogErr("User is binding");
        return errcode::WEIXIN_NO_BINDING;
    }
    //如果没有绑定就保存用户信息
    $user       = \Pub\Wx\Util::GetUserInfo($access_token,$openid);//获取微信用户信息
//    if($openid != $user->openid){
//        LogErr("User openid is not same,err");
//        return errcode::PARAM_ERR;
//    }
    $user->id        = $weixin_info->id;
    $user->userid    = $userid;
    $user->src       = $src;
    $user->srctype   = $srctype;
    //LogDebug($user);
    $wx   = WeixinSave($user);
    if(0 != $wx){
        LogErr("WeixinSave err");
        return errcode::DB_ERR;
    }
    $mgo   = new \DaoMongodb\User;
    $entry = new \DaoMongodb\UserEntry;
    $entry->userid      = $userid;
    $entry->lastmodtime = time();
    $entry->is_weixin   = IsWeixin::Yes;
    $save = $mgo->Save($entry);
    if(0 != $save)
    {
        LogErr("UserSave err");
        return errcode::DB_ERR;
    }
    //以上都执行成功后保存配置信息
    $env_mgo               = new \DaoMongodb\Env;
    $env_entry             = new \DaoMongodb\EnvEntry;
    $env_entry->env_id     = $env_id;
    $env_entry->appid      = $appid;
    $env_entry->secret     = $secret;
    $env_entry->mch_id     = $mch_id;
    $env_entry->publickey  = $publickey;
    $env_entry->key        = $key;
    $env_entry->privatekey = $privatekey;
    $wx_env = $env_mgo->Save($env_entry);
    if(0 != $wx_env){
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    return 0;
}

function ReBindingSave(&$resp)
{

    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid      = $_['userid'];
    $srctype     = $_['srctype'];
    LogDebug($_);
    if(!$userid)
    {
        LogErr("no userid");
        return errcode::PARAM_ERR;
    }

    $src    = Src::SHOP;
    $weixin = \Cache\Weixin::GetWeixinUser($userid , $src, $srctype);

    if($weixin->userid != $userid || !$weixin->userid)
    {
        LogErr("WeixinUser err");
        return errcode::WEIXIN_NO_REBINDING;
    }

    $user->id     = $weixin->id;
    $user->userid = '';
    $user->src    = $src;
    $user->srctype= $srctype;
    $wx = WeixinSave($user);
    if(0 != $wx){
        LogErr("WeixinSave err");
        return errcode::DB_ERR;
    }
    $mgo   = new \DaoMongodb\User;
    $entry = new \DaoMongodb\UserEntry;

    $entry->userid      = $userid;
    $entry->lastmodtime = time();
    $entry->is_weixin   = IsWeixin::NO;

    $save = $mgo->Save($entry);
    if(0 != $save)
    {
        LogErr("UserSave err");
        return errcode::DB_ERR;
    }
    $resp = (object)[
    ];
    return 0;

}
$ret = -1;
$resp = (object)array();
if(isset($_['binding_wx']))
{
    $ret = BindingSave($resp);

}elseif(isset($_['unbundle_wx']))
{
    $ret = ReBindingSave($resp);
}else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret' => $ret,
    'data' => $resp
);
if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}
?><?php /******************************以下为html代码******************************/?>



