<?php
/*
 * [Rocky 2017-06-20 00:10:18]
 * 员工信息表
 *
 */
require_once("current_dir_env.php");
require_once("mgo_employee.php");
require_once("redis_id.php");
require_once("cache.php");

Permission::PageCheck();
// $_=$_REQUEST;
// LogDebug($_);


function GetEmployeeInfo(&$resp)
{
    $_ = $GLOBALS["_"];

    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $user_id = $_['user_id'];

    $info = \Cache\Employee::Get($user_id);

    $userinfo = (object)[
        'password' => "*"
    ];

    $resp = (object)array(
        'info' => $info,
        'user' => $userinfo,
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetEmployeeList(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid = $_['employee_id'];

    $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Employee;
    $list = $mgo->GetEmployeeList($shop_id, [
        'userid' => $userid
    ]);

    $resp = (object)array(
        'list' => $list
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["info"]))
{
    $ret = GetEmployeeInfo($resp);
}
elseif(isset($_["list"]))
{
    $ret = GetEmployeeList($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}


$html = json_encode((object)array(
    'ret' => $ret,
    'data'  => $resp
));
echo $html;
LogDebug($html);
// $callback = $_['callback'];
// $js =<<< eof
// $callback($html);
// eof;
?>