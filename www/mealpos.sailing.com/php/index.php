<?php
require_once("current_dir_env.php");


function LoginUrl(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id  = $_["shop_id"];
    $token    = $_["token"];

    if(!$shop_id || !$token)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $domain = Cfg::GetPrimaryDomain();
    $url = "http://wx.$domain/wx_mealpos_login.php?shop_id={$shop_id}&token={$token}";
    LogDebug($url);
    $resp = (object)array(
        'url' => $url
    );
    return 0;

}

$ret = -1;
$resp = (object)array();

if(isset($_["login_url"]))
{
    $ret = LoginUrl($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
//LogDebug($html);
?><?php /******************************以下为html代码******************************/?>
<?=$html?>