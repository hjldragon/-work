<?php
/*
 * [rockyshi 2014-10-08]
 * 保存日志配置等
 *
 */
require_once("current_dir_env.php");
require_once("page_util.php");

// 保存日志配置（在配置文件中）
function Save(&$resp)
{
    $_ = PageUtil::DecSubmitData();
  //    `   msg_queue_exists($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $path  = $_["path"];
    $level = $_["level"];

    if($path != "")
    {
        Cfg::instance()->log->path = $path;
    }
    if($level != "")
    {
        Cfg::instance()->log->level = $level;
    }

    $ret = Cfg::instance()->Save();
    if($ret < 0)
    {
        LogErr("Cfg::instance()->Save err, ret=[$ret]");
        return $ret;
    }

    LogInfo("ok");
    return 0;
}

function Clear(&$resp)
{
    $_ = PageUtil::DecSubmitData();
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $path = Cfg::instance()->log->path;
    $ret = file_put_contents($path, " ");
    if($ret == "")
    {
        LogErr("file_put_contents err, path:[$path], ret:[$ret]");
        return errcode::LOG_OPR_ERR;
    }
    LogInfo("--ok--, ret=[$ret]");
    return 0;
}

$_ = PageUtil::DecSubmitData();
LogDebug($_);
$ret = 0;
$resp = null;
if($_["save"] == "path")
{
    $ret =  Save($resp);
}
else if($_["save"] == "level")
{
    $ret =  Save($resp);
}
else if($_["save"] == "clear")
{
    $ret =  Clear($resp);
}
$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => ""
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
