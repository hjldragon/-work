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


    $total       = 0;
    $mgo         = new \DaoMongodb\Version;
    $mgo->GetAllNum($total);
    if(!$total)
    {
        LogErr("no any version code");
        return errcode::PARAM_ERR;
    }
    $info = $mgo->GetByCode($total);

    $resp = (object)array(
        'info' => $info
    );
    LogInfo("save ok");
    return 0;
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

