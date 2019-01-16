<?php
/*
 * [Rocky 2017-05-05 15:23:40]
 * 保存图片文件
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");


//Permission::PageCheck();
$_=$_REQUEST;
function ApkFileUploaded(&$resp=NULL)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        $msg = "err";
        return errcode::PARAM_ERR;
    }
    $apkfile  = $_FILES["apkfile"];
    if($apkfile["error"] > 0)
    {
        LogErr("file upload err: " . json_encode($apkfile));
        return errcode::FILE_UPLOAD_ERR;
    }
    $apkname  = $apkfile['name'];

    $ext = pathinfo($apkname,PATHINFO_EXTENSION);
    $allow_ext=array("apk");
    if(!in_array($ext,$allow_ext))
    {
        LogErr("is not apk file,it:" . $ext);
        return errcode::FILE_UPLOAD_ERR;
    }
    $destfile = PageUtil::GetApkName($apkname);
    if("" == $destfile)
    {
        LogErr("get dest file err: [$destfile]");
        return -1;
    }

    $ret = move_uploaded_file($apkfile['tmp_name'], $destfile);
    if(!$ret)
    {
        LogErr("upload err");
        return errcode::FILE_UPLOAD_ERR;
    }

    $resp = (object)array(
        'apk_name' => $apkname,
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_["apk_upload"]))
{
    $ret =  ApkFileUploaded($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>