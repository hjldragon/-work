<?php
require_once("current_dir_env.php");
require_once("redis_login.php");

$_ = $_REQUEST; //PageUtil::DecSubmitData(); //
LogDebug($_);


function GetPublicKey(&$resp)
{
    $resp = (object)array(
        'publickey' => Cfg::instance()->rsa->publickey
    );
    // LogDebug($resp);
    return 0;
}

function SaveRandKey(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token = $_["token"];
    $key_enc = $_["key_enc"];
    LogDebug("key_end:[$key_enc]");

    $key = "";
    if(!openssl_private_decrypt(base64_decode($key_enc), $key, Cfg::instance()->rsa->privatekey))
    {
        LogErr("rsa err");
        return errcode::PARAM_ERR;
    }
    LogDebug("key:[$key]");

    $redis = new \DaoRedis\Login();
    $redis->SaveKey($token, $key);

    $resp = (object)array(
    );
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['publickey']))
{
    $ret = GetPublicKey($resp);
}
elseif(isset($_['save_key']))
{
    $ret = SaveRandKey($resp);
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
