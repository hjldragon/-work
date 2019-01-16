<?php
require_once("current_dir_env.php");
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once "WxUtil.php";
require_once("mgo_user.php");
require_once("weixin_save.php");
require_once("cache.php");
require_once("cfg.php");
require_once("page_util.php");
require_once("const.php");


function BindingSave($weixin, $userid, $openid, $access_token)
{
    if($weixin->userid)
    {
        LogErr("WeixinUser err");
        return errcode::WEIXIN_NO_BINDING;
    }
    if(!$openid)
    {
        LogErr("openid err");
        return errcode::PARAM_ERR;
    }
    $user = \Pub\Wx\Util::GetUserInfo($access_token,$openid);

    if($openid != $user->openid){
        LogErr("User err");
        return errcode::PARAM_ERR;
    }
    $user->id     = $weixin->id;
    $user->userid = $userid;
    $user->src    = Src::SHOP;
    $user->srctype= WxSrcType::PC;
    $wx = WeixinSave($user);
    if(0 != $wx){
        LogErr("WeixinSave err");
        return errcode::DB_ERR;
    }
    $mgo = new \DaoMongodb\User;
    $entry = new \DaoMongodb\UserEntry;
    $entry->userid      = $userid;
    $entry->lastmodtime = time();
    $entry->is_weixin   = 1;
    $save = $mgo->Save($entry);
    if(0 != $save)
    {
        LogErr("UserSave err");
        return errcode::DB_ERR;
    }
    return 0;
}

function ReBindingSave($weixin, $userid)
{
    if($weixin->userid != $userid || !$weixin->userid)
    {
        LogErr("WeixinUser err");
        return errcode::WEIXIN_NO_REBINDING;
    }
    $user->id = $weixin->id;
    $user->userid = 0;
    $user->src    = Src::SHOP;
    $user->srctype= WxSrcType::PC;

    $wx = WeixinSave($user);
    if(0 != $wx){
        LogErr("WeixinSave err");
        return errcode::DB_ERR;
    }
    $mgo = new \DaoMongodb\User;
    $entry = new \DaoMongodb\UserEntry;
    $entry->userid      = $userid;
    $entry->lastmodtime = time();
    $entry->is_weixin   = 0;

    $save = $mgo->Save($entry);
    if(0 != $save)
    {
        LogErr("UserSave err");
        return errcode::DB_ERR;
    }
    return 0;

}

$ret = -1;
$_ = $_REQUEST;
$userid       = (int)$_['userid'];
$token        = $_['token'];
$type         = (int)$_['type'];
$req          = \Pub\Wx\Util::GetOpenid();
$openid       = $req->openid;
$access_token = $req->access_token;
$weixin = \Cache\Weixin::Get($openid, Src::SHOP, WxSrcType::PC);
$data = 0;
if($type)
{
    $ret = BindingSave($weixin, $userid, $openid, $access_token);
    $data = 3;
}
else
{
    $ret = ReBindingSave($weixin, $userid);
    $data = 5;
}

if(0 == $ret)
{
    $tokendata = \Cache\Login::Get($token);
    $url = Cfg::instance()->orderingsrv->webserver_url;
    $ret_json_str = PageUtil::HttpPostJsonEncData(
        $url,
        [
            'name' => "cmd_publish",
            'param' => json_encode([
                'opr'   => "once",
                'param' => [
                    'topic' => "login_qrcode@$token",
                    'data' => [
                        'info' =>[
                            'ret' => $ret,
                            'type' => $type
                        ]
                    ]
                ],
            ])
        ]
    );

    $ret_json_obj = json_decode($ret_json_str);
    if(0 != $ret_json_obj->ret)
    {
        $data = 10;
    }
}
else
{
    if($ret == errcode::WEIXIN_NO_BINDING)
    {
        $data = 4;
    }
    if($ret == errcode::WEIXIN_NO_REBINDING)
    {
        $data = 6;
    }
}


require("wx_codetip.php");
?>

