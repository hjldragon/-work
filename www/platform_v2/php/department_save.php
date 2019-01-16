<?php
/*
 * 客户信息保存类
 */
require_once("current_dir_env.php");
require_once("cache.php");
require_once("db_pool.php");
require_once("redis_id.php");
require_once("mgo_department.php");
require_once("mgo_employee.php");
require_once("mgo_pl_department.php");
require_once("mgo_ag_department.php");
require_once("mgo_platformer.php");
require_once("mgo_ag_employee.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");

//保存部门信息
function SaveDepartment(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mongodb       = new \DaoMongodb\Department;
    $department_id = $_['department_id'];
    if (!$department_id)
    {
        $department_id = \DaoRedis\Id::GenDepartmentId();
        LogDebug($department_id);
    }
    $department_name = $_['department_name'];
    $shop_id         = \Cache\Login::GetShopId();
    if (!$shop_id)
    {
        LogDebug($shop_id);
        return errcode::USER_NOLOGIN;
    }
    $info = $mongodb->QueryByDepartmentName($shop_id, $department_name);
    if ($info->department_name)
    {
        return errcode::DEPARTMENT_IS_EXIST;
    }

    $entry                  = new \DaoMongodb\DepartmentEntry;
    $entry->department_id   = $department_id;
    $entry->department_name = $department_name;
    $entry->shop_id         = $shop_id;
    $entry->delete          = 0;
    $ret                    = $mongodb->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[];
    LogInfo("save ok");
    return 0;
}
//删除部门信息
function DeleteDepartment(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $department_id = $_['department_id'];
    $shop_id       = \Cache\Login::GetShopId();
    if (!$shop_id)
    {
        LogDebug($shop_id);
        return errcode::USER_NOLOGIN;
    }
    $employee = new \DaoMongodb\Employee;
    $info     = $employee->GetDepartmentEmployee($shop_id, $department_id);
    if ($info)
    {
        LogErr("Delete err ,Department have employee");
        return errcode::DEPARTMENT_NOT_DEL;
    }
    $department                = new \DaoMongodb\DepartmentEntry;
    $mgo                       = new \DaoMongodb\Department;
    $department->department_id = $department_id;
    $department->delete        = 1;
    $ret                       = $mgo->Save($department);
    if (0 != $ret)
    {
        LogErr("Delete err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    LogInfo("delete ok");
    return 0;
}
//保存平台部门信息
function SavePlDepartment(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mongodb          = new \DaoMongodb\PLDepartment;
    $pl_department_id = $_['pl_department_id'];
    $platform_id      = PlatformID::ID;
    if (!$pl_department_id)
    {
        PlPermissionCheck::PageCheck(PlPermissionCode::ADD_DEPARTMENT);
        $pl_department_id = \DaoRedis\Id::GenPlDepartmentId();
        LogDebug($pl_department_id);
    }else{
        PlPermissionCheck::PageCheck(PlPermissionCode::EDIT_DEPARTMENT);
    }

    $pl_department_name = $_['pl_department_name'];

    $info = $mongodb->QueryByDepartmentName($platform_id, $pl_department_name);
    if ($info->pl_department_name)
    {
        LogErr("pl_department_name:".$pl_department_name." is  exist");
        return errcode::NAME_IS_EXIST;
    }

    $entry                  = new \DaoMongodb\PLDepartmentEntry;
    $entry->pl_department_id   = $pl_department_id;
    $entry->pl_department_name = $pl_department_name;
    $entry->platform_id        = $platform_id;
    $entry->delete             = 0;
    $ret                        = $mongodb->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
        'pl_department_id' =>$pl_department_id
    ];
    LogInfo("save ok");
    return 0;
}
//删除平台部门信息
function DeletePlDepartment(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::DEL_DEPARTMENT);
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $pl_department_id = $_['pl_department_id'];
    if(!$pl_department_id)
    {
        LogErr("no canshu");
        return errcode::PARAM_ERR;
    }
    $employee = new \DaoMongodb\Platformer;
    $info     = $employee->GetDepartmentPlatformer($pl_department_id);
    if ($info)
    {
        LogErr("Delete err ,pl_department have employee");
        return errcode::DEPARTMENT_NOT_DEL;
    }
    $department                   = new \DaoMongodb\PLDepartmentEntry;
    $mgo                          = new \DaoMongodb\PLDepartment;
    $department->pl_department_id = $pl_department_id;
    $department->delete           = 1;
    $ret                          = $mgo->Save($department);
    if (0 != $ret)
    {
        LogErr("Delete err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    LogInfo("delete ok");
    return 0;
}
//保存代理商部门信息
function SaveAgDepartment(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mongodb          = new \DaoMongodb\AGDepartment;
    $ag_department_id = $_['ag_department_id'];
    $agent_id         = $_['agent_id'];
    if(!$agent_id)
    {
        LogErr("no agent_id");
        return errcode::PARAM_ERR;
    }
    if (!$ag_department_id)
    {
        AgPermissionCheck::PageCheck(AgentPermissionCode::ADD_DEPARTMENT);
        $ag_department_id = \DaoRedis\Id::GenAgDepartmentId();

    }else{
        AgPermissionCheck::PageCheck(AgentPermissionCode::EDIT_DEPARTMENT);
    }

    $ag_department_name = $_['ag_department_name'];

    $info = $mongodb->QueryByDepartmentName($agent_id, $ag_department_name);
    if ($info->ag_department_name)
    {
        LogErr("pl_department_name:".$ag_department_name." is  exist");
        return errcode::NAME_IS_EXIST;
    }

    $entry                     = new \DaoMongodb\AGDepartmentEntry;
    $entry->ag_department_id   = $ag_department_id;
    $entry->ag_department_name = $ag_department_name;
    $entry->agent_id           = $agent_id;
    $entry->delete             = 0;
    $ret                        = $mongodb->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
        'ag_department_id' =>$ag_department_id
    ];
    LogInfo("save ok");
    return 0;
}
//删除代理商部门信息
function DeleteAgDepartment(&$resp)
{
    AgPermissionCheck::PageCheck(AgentPermissionCode::DEL_DEPARTMENT);
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $ag_department_id = $_['ag_department_id'];
    if(!$ag_department_id)
    {
        LogErr("no ag_department_id");
        return errcode::PARAM_ERR;
    }
    $employee = new \DaoMongodb\AGEmployee;
    $info     = $employee->GetDepartmentPlatformer($ag_department_id);
    if ($info)
    {
        LogErr("Delete err ,pl_department have employee");
        return errcode::DEPARTMENT_NOT_DEL;
    }
    $department                   = new \DaoMongodb\AGDepartmentEntry;
    $mgo                          = new \DaoMongodb\AGDepartment;
    $department->ag_department_id = $ag_department_id;
    $department->delete           = 1;
    $ret                          = $mgo->Save($department);
    if (0 != $ret)
    {
        LogErr("Delete err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    LogInfo("delete ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if (isset($_['department_save']))
{
    $ret = SaveDepartment($resp);
}elseif (isset($_['department_del']))
{
    $ret = DeleteDepartment($resp);
}elseif (isset($_['pl_department_save']))
{
    $ret = SavePlDepartment($resp);
}elseif (isset($_['pl_department_del']))
{
    $ret = DeletePlDepartment($resp);
}elseif (isset($_['ag_department_save']))
{
    $ret = SaveAgDepartment($resp);
}elseif (isset($_['ag_department_del']))
{
    $ret = DeleteAgDepartment($resp);
}

else
{
    LogErr("param err");
    $ret = errcode::PARAM_ERR;
}
$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
//    'crypt' => 1, // 是加密数据标记
//    'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>