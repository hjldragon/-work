<?php
header('Content-Type:text/html;charset=utf-8');
require_once("current_dir_env.php");
require_once("cache.php");
require_once("/www/shop.sailing.com/php/redis_login.php");


//LoginSave();
function Login()
{
    $_      = $_REQUEST;
    $token  = $_['token'];
    $openid = $_['openid'];
    if(!$token || !$openid)
    {
        LogErr("token err");
        echo "系统忙...";exit(0);
    }

    $weixin = \Cache\Weixin::Get($openid,Src::SHOP, WxSrcType::APP);

    if(!$weixin->userid)
    {
        LogErr("WeixinUser err");
        echo "此微信未注册,不能登录";exit(0);
    }
    $redis = new \DaoRedis\Login();
    $info = new \DaoRedis\LoginEntry();

    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->userid   = $weixin->userid;
    $info->username = '';
    $info->shop_id  = '';
    $info->login    = 1;
    LogDebug($info);

   $ret = $redis->Save($info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        echo "系统忙...";exit(0);
    }


    // // 登录成功，发送通知到前端
    $url = 'http://192.168.5.117:13010/wbv';  // cfg.php --> orderingsrv -->webserver_url
    $ret_json = PageUtil::HttpPostJsonData($url, [
        'name' => "cmd_publish",
        'param' => json_encode([
            'opr'   => "general",
            'param' => [
                'topic' => "login_qrcode@$token",
                'data'=> [
                    'info' => $info
                ]
            ],
        ])
    ]);
    LogDebug($ret_json);

    echo '登录成功...';
}

function LoginSave()
{
    $_      = $_REQUEST;
    $token  = $_['token'];
    if(!$token)
    {
        LogErr("token err");
        echo "系统忙...";exit(0);
    }
    $redis = new \DaoRedis\Login();
    $info  = new \DaoRedis\LoginEntry();
    $openid = $redis->Get($token)->openid;
    if(!$openid)
    {
        LogErr("openid err");
        echo "系统忙...";exit(0);
    }
    $weixin = \Cache\Weixin::Get($openid, Src::SHOP, WxSrcType::APP);
    if(!$weixin->userid)
    {
        LogErr("WeixinUser err");
        echo "此微信未注册,不能登录";exit(0);
    }
    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->userid   = $weixin->userid;
    $info->username = '';
    $info->shop_id  = '';
    $info->login    = 1;
    LogDebug($info);

   $ret = $redis->Save($info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        echo "系统忙...";exit(0);
    }



    // // 登录成功，发送通知到前端
    $url = 'http://192.168.5.117:13010/wbv';  // cfg.php --> orderingsrv -->webserver_url
    $ret_json = PageUtil::HttpPostJsonData($url, [
        'name' => "cmd_publish",
        'param' => json_encode([
            'opr'   => "general",
            'param' => [
                'topic' => "login_qrcode@$token",
                'data'=> [
                    'info' => $info
                ]
            ],
        ])
    ]);
    LogDebug($ret_json);

    echo '登录成功...';
}