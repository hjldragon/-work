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
Permission::PageCheck();
//$_=$_REQUEST;
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

$ret = -1;
$resp = (object)array();
if (isset($_['department_save']))
{
    $ret = SaveDepartment($resp);
}elseif (isset($_['department_del']))
{
    $ret = DeleteDepartment($resp);
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