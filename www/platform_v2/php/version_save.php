<?php
/*
 * [Rocky 2018-03-16 16:44:02]
 *
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("redis_login.php");
require_once("mgo_version.php");
//保存最新版本号数据

function SaveLastVersion(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $version_name        = $_['version_name'];
    $version_desc        = $_['version_desc'];
    $version_url         = $_['version_url'];
    $version_code        = $_['version_code'];
    $force_update        = $_['force_update'];
    $srctype             = $_['srctype'];
    $platform            = $_['platform'];

    if(!$version_url || !$platform)
    {
        LogErr("no version name or version code");
        return errcode::PARAM_ERR;
    }
    if(!preg_match('/^([\d]+)\.([\d]+)\.([\d]+)$/',$version_code))
    {
        LogErr("no version code err");
        return errcode::PARAM_ERR;
    }

    if($srctype == NewSrcType::SHOUYINJI || $srctype == NewSrcType::SELFHELP || $srctype == NewSrcType::PAD)
    {
         $domain = Cfg::instance()->GetMainDomain();
         $url    = "http://dl.$domain/apk/$version_url";
    }else{
       $url      =  $version_url;
     }
    $version_id  = \DaoRedis\Id::GenVersionId();
    $version_day = time();
    $entry       = new \DaoMongodb\VersionEntry;
    $mgo         = new \DaoMongodb\Version;
    $total       = 0;
    $mgo->GetAllNum($total);

    $entry->version_id       = $version_id;
    $entry->srctype          = $srctype;
    $entry->version_day      = $version_day;
    $entry->force_update     = $force_update;
    $entry->version_code     = $version_code;
    $entry->version_name     = $version_name;
    $entry->version_desc     = $version_desc;
    $entry->version_url      = $url;
    $entry->platform         = $platform;
    $entry->delete           = 0;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $fileapk = $_FILES["version_url"];
    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

$ret  = -1;
$resp = (object)array();

if(isset($_['version_save']))
{
    $ret = SaveLastVersion($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret' => $ret,
    'data' => $resp
);
if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}
?><?php /******************************以下为html代码******************************/?>

