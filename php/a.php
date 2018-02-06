<?php
ob_start();
require_once("current_dir_env.php");
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once("const.php");
require_once("cache.php");
require_once("cfg.php");
ob_end_clean();
function Index(&$resp)
{ 
    $text    = $_REQUEST["text"];
    $openid  = $_REQUEST["openid"];
    //$shop_id = \Cache\Login::GetShopId();
    $shop_id = "SH21";
    $msg = "";
    if(!$openid || !$text)
    {
        LogErr("param err");
        $msg = "系统忙...";
        alt($msg);
    }
    
    if(!$shop_id)
    {
        LogErr("no login");
        $msg = "系统忙...";
        alt($msg);
    }

    $resp = (object)array(
        'text'    => $text,
        'openid'  => $openid,
        'shop_id' => $shop_id
    );

    $url = "http://wx.jzzwlcm.com/wx_send.php?".http_build_query($resp);
    header("Location: $url");
    exit();
}

function alt($msg){
echo <<<eof
<script>
alert("$msg");
</script>
eof;
exit(0);
}

Index($resp);



?>