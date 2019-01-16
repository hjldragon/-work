<?php
require_once("current_dir_env.php");
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once "WxUtil.php";
require_once("cache.php");
require_once("cfg.php");
require_once("page_util.php");
require_once("const.php");
require_once("WxUnifiedorder.php");

function WxSend()
{
    $_ = $_REQUEST;
    $text    = $_['text'];
    $openid  = $_['openid'];
    $shop_id = $_['shop_id'];
    if (!$text || !$openid || !$shop_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shopinfo = \Cache\Shop::Get($shop_id);
    $appid = $shopinfo->weixin_pay_set->appid;
    $secret = $shopinfo->weixin_pay_set->secret;
    if (!$appid || !$secret)
    {
        LogErr("shop appid err");
        return errcode::PARAM_ERR;
    }
    $info = \Wx\Util::GetTokenByShop($appid, $secret);
    $access_token = $info->access_token;
    $unifiedorder = new \Wx\Unifiedorder();
    $content->content = $text;
    $ret = $unifiedorder->SubmitSend($openid, $content, $access_token);
    $ret = json_decode($ret);
    LogDebug($ret);
    return $ret->errcode;
}

$ret = -1;
$ret = WxSend();

if(0 != $ret)
{  
    echo <<<eof
    <script>
    alert("发送失败");
    </script>
eof;
    exit(0);
}

?>

