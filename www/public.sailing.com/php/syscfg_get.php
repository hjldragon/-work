<?php
require_once("current_dir_env.php");
// $_ = $_REQUEST;

function GetWebCfg(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $resp = (object)[
        'env' => Cfg::GetRunningEnv(), // 'dev',test','beta','product'
        'primary_domain' => Cfg::GetPrimaryDomain(),
        'websocket_url' => Cfg::GetWebSocketUrl(),
    ];
    LogDebug($resp);
    LogDebug("--end--");
    return 0;
}


$ret = -1;
$resp = (object)[];
if('get_web_cfg' == $_['opr'])
{
    $ret = GetWebCfg($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    $resp = [
        'errmsg' => "unknown param: {$_['opr']}"
    ];
    LogErr("param err:[{$_['opr']}]");
}

Pub\PageUtil::HtmlOut($ret, $resp);
