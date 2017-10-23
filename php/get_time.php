<?php
/*
 * [Rocky 2016-01-27 16:44:13]
 * 取当前时间
 */
require_once("current_dir_env.php");

// LogErr(Cfg::instance()->log);

function GetSysTime(&$resp)
{
    $resp = (object)array(
        msec => Util::GetMsec()  // 毫秒
    );
    LogInfo("--ok--, size=[$size]");
    return 0;
}

$ret = 0;
$resp = (object)array();
$ret =  GetSysTime($resp);
$html = json_encode((object)array(
    ret   => $ret,
    data  => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
