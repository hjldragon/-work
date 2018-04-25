<?php
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once("current_dir_env.php");
require_once "WxUtil.php";
require_once("cfg.php");
require_once("cache.php");
require_once("redis_login.php");
require_once("page_util.php");
require_once("const.php");


$_ = $_REQUEST;

function login($token, &$userid)
{
    if(!$token)
    {
        LogErr("token err");
        return errcode::PARAM_ERR;
    }
    $req   = \Wx\Util::GetOpenid();
    $openid = $req->openid;
    $weixin = \Cache\Weixin::Get($openid, 2); // <<<<<<<<<<<<<<<<<< 2
    $userid = $weixin->userid;
    if(!$userid)
    {
        LogErr("WeixinUser err");
        return errcode::WEIXIN_NO_LOGIN;
    }
    $redis = new \DaoRedis\Login();
    $info = new \DaoRedis\LoginEntry();

    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->userid   = $userid;
    $info->username = '';
    $info->shop_id  = '';
    $info->login    = 1;
    LogDebug($info);

    $ret = $redis->Save($info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        // echo "系统忙...";exit(0);
    }
    return 0;
}


$token = $_['token'];
$userid = null;
$ret = login($token, $userid);
if(0!= $ret)
{   
    $data = 10;
    if($ret == errcode::WEIXIN_NO_LOGIN)
    {
        $data = 2;
    }
    require("wx_codetip.php");
    exit(0);
}
$tokendata = \Cache\Login::Get($token); //存缓存

// 发送能知到服务端
$url = Cfg::instance()->orderingsrv->webserver_url;

// 正常情况下，使用下面的（这里为调试兼容，暂时保留）
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
                        'userid' => $userid
                    ]
                ]
            ],
        ])
    ]
);
LogDebug("[$ret_json_str]");

// 发退登录通知
$ret_json_str = PageUtil::HttpPostJsonEncData(
    $url,
    [
        'name' => "cmd_publish",
        'param' => json_encode([
            'opr'   => "login_qrcode",
            'param' => [
                'token' => "$token",
                'data' => [
                    'info' =>[
                        'ret' => $ret,
                        'userid' => $userid
                    ]
                ]
            ],
        ])
    ]
);
LogDebug("[$ret_json_str]");
$ret_json_obj = json_decode($ret_json_str);
if(0 != $ret_json_obj->ret)
{
    $data = 10;
}
else
{
    $data = 1;
}
require("wx_codetip.php");
?>

