<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取店铺信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_department.php");
require_once("permission.php");
require_once("mgo_employee.php");
require_once("mgo_position.php");

Permission::PageCheck($GLOBALS['srctype']);

function GetAllDepartment(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = \Cache\Login::GetShopId();

    $mgo             = new \DaoMongodb\Department;
    $department_list = $mgo->GetDepartmentList($shop_id);
    //LogDebug($department_list);
    $employee = new \DaoMongodb\Employee;
    $list_all = [];
    foreach ($department_list as $id)
    {
        $list                    = [];
        $list['department_id']   = $id->department_id;
        $list['department_name'] = $id->department_name;
        $employee_list           = $employee->GetDepartmentEmployee($shop_id,$id->department_id);
        $all_employee            = [];
        foreach ($employee_list as $all)
        {
            $employee_list_all                    = [];
            $employee_list_all['employee_id']     = $all->employee_id;
            $employee_list_all['real_name']       = $all->real_name;
            array_push($all_employee, $employee_list_all);
        }
        $list['employee_list'] = $all_employee;
        array_push($list_all, $list);
    }

    $resp = (object)[
        'department_list' => $list_all,
    ];
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
function GetDepartmentInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
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
    $mgo             = new \DaoMongodb\Department;
    $department_info = $mgo->QueryByDepartmentId($shop_id,$department_id);
    LogDebug($department_info);
    $department['department'] = $department_info->department_name;

    $resp = (object)array(
        'department_name' => $department['department']
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
function GetDepartmentEmployee(&$resp)
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
    $employee      = new \DaoMongodb\Employee;
    $employee_list = $employee->GetDepartmentEmployee($shop_id,$department_id);
    foreach ($employee_list as &$v)
    {
        $position           = new \DaoMongodb\Position;
        $position_info      = $position->GetPositionById($shop_id, $v->position_id);
        $v->position_name   = $position_info->position_name;
        $department         = new \DaoMongodb\Department;
        $department_info    = $department->QueryByDepartmentId($shop_id, $department_id);
        $v->department_name = $department_info->department_name;
    }

    LogDebug($employee_list);

    $resp = (object)[
        'employee_list' => $employee_list,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["get_department_list"]))
{
    $ret = GetAllDepartment($resp);
} elseif(isset($_["get_department_info"]))
{
    $ret = GetDepartmentInfo($resp);
} elseif(isset($_["get_department_employee"]))
{
    $ret = GetDepartmentEmployee($resp);
}
$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>