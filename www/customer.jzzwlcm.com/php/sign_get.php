<?php
require_once("current_dir_env.php");
set_include_path("/www/wx.jzzwlcm.com/");
require_once "WxUtil.php";

function GetSignInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $info = \Pub\Wx\Util::GetTicket();
    $data['noncestr']     = $_['noncestr'];
    $data['timestamp']    = $_['timestamp'];
    $data['url']          = $_['url'];
    $data['jsapi_ticket'] = $info->ticket;
    $appid = \Pub\Wx\Cfg::APPID;  // 公众账号ID

    // 因为跳转后，日志设置丢失，这里可以重设来查看
    // Log::instance()->SetFile("/log/customer.jzzwlcm.com/log.txt");
    // Log::instance()->SetLevel(4);
    // LogDebug($data);

    $sign  = \Pub\Wx\Util::GetSha1Sign($data);  // 签名
    // var_dump($sign);die;
    LogDebug($sign);
    $resp = (object)array(
        'appid' => $appid,
        'sign'  => $sign
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}



$ret = -1;
//$_ = $_REQUEST;
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

