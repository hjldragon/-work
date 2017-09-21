<?php
/*
 * [rockyshi 2014-09-30]
 * 取配置信息
 *
 */
require_once("current_dir_env.php");
require_once("page_util.php");


// 取配置信息
function GetCfgInfo(&$resp)
{
    $_ = PageUtil::DecSubmitData();
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $resp = (object)array(
        'db' => (object)array(
            'mysql'   => Cfg::instance()->db->mysql,
            'mongodb' => Cfg::instance()->db->mongodb
        ),
        'log' => Cfg::instance()->log,
        'dir' => Cfg::instance()->dir
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$_ = PageUtil::DecSubmitData();
LogDebug($_);
$ret = 0;
$resp = null;
$ret =  GetCfgInfo($resp);
$html = json_encode((object)array(
    'ret'   => $ret,
    //'data'  => $resp
    'crypt' => 1, // 是加密数据标记
    'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
