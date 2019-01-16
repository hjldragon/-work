<?php
/*
 * [Rocky 2017-12-28 17:48:23]
 * 检测key
 *
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");


function CheckKey(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $resp = (object)[
    ];
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = null;
if($_["check_key"])
{
    $ret =  CheckKey($resp);
}
else
{
    LogErr("param err");
    $ret =  errcode::PARAM_ERR;
    $resp = $resp = (object)[
        "msg" => "param err",
    ];
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
