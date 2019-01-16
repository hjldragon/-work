<?php
/*
 * [Rocky 2018-03-16 16:44:02]
 *
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("redis_login.php");
require_once("mgo_version.php");
//获取最新版本号数据
function GetLastVersion(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $version_code           = $_['version_code'];
    $srctype                = $_['srctype'];
    $platform               = $_['platform'];
    LogDebug($_);
    if(!$version_code || !$srctype  || !$platform )
    {
        LogErr("some word have no");
        return errcode::PARAM_ERR;
    }
    $sort['version_day']    = -1;//用于找最新的数据

    $mgo       = new \DaoMongodb\Version;
    $infos     = $mgo->GetVersionLast($srctype, $platform, $sort);

    foreach ($infos as &$v)
    {
        $ret = versionCompare($version_code, $v->version_code);
        if($ret)
        {
            $v->need_update = true;
        }else{
            $v->need_update = false;
        }

        if($v->force_update)
        {
            $v->force_update = true;
        }else{
            $v->force_update = false;
        }
        $v->phone = "400-0020-158";
    }

    $info = $infos[0];
    if(!$info)
    {
        LogDebug('version is empty');
        return errcode::VERSION_ERR;
    }
    $resp = (object)array(
        'info' => $info
    );

    LogInfo("get ok");
    return 0;
}

//判断版本号的大小
function VersionCompare($v1, $v2){

    if (!$v1 || !$v2)
    {
        LogErr('version code is err');
        return errcode::PARAM_ERR;
    }
    $aMat = preg_match('/^([\d]+)\.([\d]+)\.([\d]+)$/',$v1);
    $bMat = preg_match('/^([\d]+)\.([\d]+)\.([\d]+)$/',$v2);
    if (!$aMat || !$bMat)
    {
        LogErr('version code is err');
        return errcode::PARAM_ERR;
    }
    // 移除最后的.0
    $a = explode(".", rtrim($v1, "."));
    $b = explode(".", rtrim($v2, "."));
    foreach ($a as $key => $aVal)
    {
        if (isset($b[$key])){

            if ($aVal > $b[$key])
            {
               return 0;
            }
            if ($aVal < $b[$key])
            {
                return 1;
            }
        }else{
            return 0;
        }
    }
}

$ret  = -1;
$resp = (object)array();
if(isset($_['version_get']))
{
    $ret = GetLastVersion($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
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

