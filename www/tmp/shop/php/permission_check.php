<?php
/*
 * [Rocky 2017-05-22 14:57:13]
 * 权限检测
 */
require_once("current_dir_env.php");
require_once("/www/shop.sailing.com/php/page_util.php");
// require_once("permission.php");


function PermissionCheck(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $login = $_['login'];
    $admin = $_['admin'];

    $chk = 0;
    if(1 == $login)
    {
        $chk = $chk | Permission::CHK_LOGIN;
    }
    if(1 == $admin)
    {
        $chk = $chk | Permission::CHK_ADMIN;
    }
    LogDebug($chk);

    $check = Permission::Check($chk);

    $resp = (object)array(
        'check' => $check    // 权限没问题返回0
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_["check"]))
{
    $ret = PermissionCheck($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
