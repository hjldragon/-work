<?php
/*
 * [rockyshi 2014-10-04]
 * 取系统信息
 *
 */
require_once("current_dir_env.php");
require_once("page_util.php");

LogErr(Cfg::instance()->log);

function GetLogContent(&$resp)
{
    $_ = PageUtil::DecSubmitData();
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $path = Cfg::instance()->log->path;
    if(!file_exists($path))
    {
        LogErr("file not exist: [$path]");
        return errcode::LOG_NO_EXIST;
    }

    $len = (int)$_['logsize'];   // KB为单位
    $len = $len > 0 ? $len * 1024 : 4096;
    $size = filesize($path);
    $begin = $size - $len;
    $content = file_get_contents($path, 0, null, $begin, $len);
    $content = nl2br(htmlspecialchars($content));

    $resp = (object)array(
        content => $content,
        size    => $size
    );
    LogInfo("--ok--, size=[$size]");
    return 0;
}

$_ = PageUtil::DecSubmitData();
LogDebug($_);
$ret = 0;
$resp = (object)array();
if($_["get"] == "content")
{
    $ret =  GetLogContent($resp);
}
$html = json_encode((object)array(
    ret   => $ret,
    // data  => $resp
    crypt => 1, // 是加密数据标记
    data  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
