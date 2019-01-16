<?php
require_once("current_dir_env.php");
require_once "WxUtil.php";

$_ = $_REQUEST;

function GetSignInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $info = \Wx\Util::GetTicket();
    // $_['jsapi_ticket'] = $info->ticket;
    // $_['timestamp'] = (int)$_['timestamp'];

    $data = [
        'noncestr' => $_['noncestr'],
        'jsapi_ticket' => $info->ticket,
        'timestamp' => (int)$_['timestamp'],
        'url' => $_['url'],
    ];

    LogDebug(json_encode($data));

    $sign  = \Wx\Util::GetSha1Sign($data);  // 签名
    $resp = (object)array(
        'appid' => \Wx\Cfg::APPID,  // 公众账号ID
        'sign'  => $sign
    );
    LogDebug($info);
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}



$ret = -1;
$resp = (object)array();
if (isset($_["get_sign"]))
{
    $ret = GetSignInfo($resp);
}

$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp,//<<<<<<<未加密返回数据
    //'crypt' => 1, // 是加密数据标记
    //'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>

