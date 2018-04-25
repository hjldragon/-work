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
    //$need_update         = $_['need_update'];
    //$force_update        = $_['force_update'];
    $cur_version_code    = (int)$_['cur_version_code'];
    $version_name        = $_['version_name'];
    $version_desc        = $_['version_desc'];
    $version_url         = $_['version_url'];
    if(!$version_url)
    {
        LogErr("no version url name");
        return errcode::PARAM_ERR;
    }
    $version_id  = \DaoRedis\Id::GenVersionId();
    $version_day = time();
    $total       = 0;
    $entry       = new \DaoMongodb\VersionEntry;
    $mgo         = new \DaoMongodb\Version;
    $mgo->GetAllNum($total);
    if($cur_version_code<$total)//通过PAD端传过来的版本号对比现在的版本号,如果小就升级。。。
    {
        $need_update = 1;
    }else{
        $need_update = 0;
    }
    $entry->version_id       = $version_id;
    $entry->version_day      = $version_day;
    $entry->need_update      = $need_update;
    $entry->force_update     = 0;
    $entry->version_code     = $total+1;//每更新保存一次,版本号次数就+1
    $entry->version_name     = $version_name;
    $entry->version_desc     = $version_desc;
    $entry->version_url      = $version_url;
    $entry->delete           = 0;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

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

