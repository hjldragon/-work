<?php
/*
 * [rockyshi 2014-09-30 13:19:06]
 * 取文件信息
 *
 */
require_once("current_dir_env.php");
require_once("page_util.php");

Permission::AdminCheck();


function GetFileList(&$resp)
{
    $_ = PageUtil::DecSubmitData();
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $path = Util::PathNormalize($_["path"]);
    if($path === "")
    {
        LogErr("path err: [{$_["path"]}] [$path]");
        return errcode::FILE_PATH_ERR;
    }
    $info = Util::GetFileList($path);
    $list = Util::ArrayJoin($info->dir, $info->file);
    // LogDebug($info);
    LogDebug($path);

    $resp = (object)array(
        'list' => $list,
        'path' => $path     // 返回规整化路径
    );
    LogInfo("--ok--");
    return 0;
}

function GetFileInfo(&$resp)
{
    $_ = PageUtil::DecSubmitData();
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $filename = $_["filename"];

    $is_read  = 0;
    $is_write = 0;
    $is_exec  = 0;
    if(is_readable($filename))
    {
        $is_read = 1;
    }
    if(is_writable($filename))
    {
        $is_write = 1;
    }
    if(is_executable($filename))
    {
        $is_exec = 1;
    }


    $info = Util::GetFileInfo($filename);
    if(is_dir($filename))
    {
        LogDebug("is dir: [$filename]");
        $content = "[{$filename}]是个目录";
    }
    else
    {
        LogDebug("is file: [$filename]");
        $content = file_get_contents($filename);
    }
    // LogDebug($info);

    $resp = (object)array(
        content  => $content,
        attr     => $info,
        is_read  => $is_read,
        is_write => $is_write,
        is_exec  => $is_exec
    );
    LogInfo("--ok--");
    return 0;
}

function SaveFile(&$resp)
{
    $_ = PageUtil::DecSubmitData();
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $filename = $_["filename"];
    $content  = $_["content"];
    $need_bak = $_["need_bak"];
    if(is_dir($filename))
    {
        LogErr("is dir: [$filename]");
        return errcode::FILE_IS_DIR;
    }
    if($need_bak && file_exists($filename))
    {
        // $filename = getcwd() . "/$filename";
        $filename = realpath($filename);
        $grid = DbPool::GetMongoDb()->getGridFS("bak");
        $id = $grid->storeFile($filename,
                               array(
                                    'filename' => $filename,
                                    'ctime'    => time(),
                                    'type'     => "logfile"
                               )
        );
        LogDebug("bak ok: id=[$id], filename=[$filename]");
    }

    $ret = file_put_contents($filename, $content);
    if($content !== "" && $ret == "")
    {
        LogErr("file_put_contents err: [$filename]");
        return errcode::FILE_WRITE_ERR;
    }
    $resp = (object)array(
        write_size => $ret
    );
    LogInfo("--ok--, ret=[$ret], filename=[$filename]");
    return 0;
}

$_ = PageUtil::DecSubmitData();
// LogDebug($_);
$ret = -1;
$resp = (object)array();
if($_["filelist"])
{
    $ret =  GetFileList($resp);
}
elseif($_["fileinfo"])
{
    $ret =  GetFileInfo($resp);
}
elseif($_["filesave"])
{
    $ret =  SaveFile($resp);
}
$html = json_encode((object)array(
    'ret'   => $ret,
    // 'data'  => $resp
    'crypt' => 1, // 是加密数据标记
    'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
