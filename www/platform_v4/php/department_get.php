<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取店铺信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_pl_department.php");
require_once("permission.php");
require_once("mgo_platformer.php");
require_once("mgo_pl_position.php");
require_once("mgo_ag_position.php");
require_once("mgo_ag_department.php");
require_once("mgo_ag_employee.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");

//平台部门下的所有员工
function GetAllPlDepartment(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::DEPARTMENT_SEE);
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $platform_id     = PlatformID::ID;
    $mgo             = new \DaoMongodb\PLDepartment;
    $department_list = $mgo->GetDepartmentList($platform_id);
    $employee        = new \DaoMongodb\Platformer;
    $list_all        = [];
    foreach ($department_list as $id)
    {
        $list                       = [];
        $list['pl_department_id']   = $id->pl_department_id;
        $list['pl_department_name'] = $id->pl_department_name;
        $employee_list              = $employee->GetDepartmentPlatformer($id->pl_department_id);
        $all_employee               = [];
        foreach ($employee_list as $all)
        {
            $employee_list_all                    = [];
            $employee_list_all['platformer_id']   = $all->platformer_id;
            $employee_list_all['pl_name']         = $all->pl_name;
            array_push($all_employee, $employee_list_all);
        }
        $list['employee_list'] = $all_employee;
        array_push($list_all, $list);
    }

    $resp = (object)[
        'pl_department_list' => $list_all,
    ];
    LogInfo("--ok--");
    return 0;
}

function GetPlDepartmentInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $pl_department_id = $_['pl_department_id'];
    $mgo              = new \DaoMongodb\PLDepartment;
    $department_info  = $mgo->QueryByDepartmentId($pl_department_id);

    $department['pl_department'] = $department_info->pl_department_name;
    $resp = (object)array(
        'pl_department_name' => $department['pl_department']
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetPlDepartmentPlatformer(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $pl_department_id = $_['pl_department_id'];

    $employee   = new \DaoMongodb\Platformer;
    $position   = new \DaoMongodb\PLPosition;
    $department = new \DaoMongodb\PLDepartment;
    $user       = new \DaoMongodb\User;
    $employee_list = $employee->GetDepartmentPlatformer($pl_department_id);

    foreach ($employee_list as &$v)
    {
        $position_info         = $position->GetPositionById($v->pl_position_id);
        $user_info             = $user->QueryById($v->userid);
        $v->phone              = $user_info->phone;
        $v->pl_position_name   = $position_info->pl_position_name;
        $department_info       = $department->QueryByDepartmentId($pl_department_id);
        $v->pl_department_name = $department_info->pl_department_name;
    }
    $resp = (object)[
        'platformer_list' => $employee_list,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//代理商部门下的所有员工
function GetAllAgDepartment(&$resp)
{
    AgPermissionCheck::PageCheck(AgentPermissionCode::DEPARTMENT_LIST);
   $_ = $GLOBALS["_"];
   if (!$_)
   {
       LogErr("param err");
       return errcode::PARAM_ERR;
   }

   $agent_id        = $_['agent_id'];
   $mgo             = new \DaoMongodb\AGDepartment;
   $department_list = $mgo->GetDepartmentList($agent_id);
   $employee        = new \DaoMongodb\AGEmployee;
   $list_all        = [];
   foreach ($department_list as $id)
   {
       $list                       = [];
       $list['ag_department_id']   = $id->ag_department_id;
       $list['ag_department_name'] = $id->ag_department_name;
       $employee_list              = $employee->GetDepartmentPlatformer($id->ag_department_id);
       $all_employee               = [];
       foreach ($employee_list as $all)
       {
           $employee_list_all                    = [];
           $employee_list_all['platformer_id']   = $all->platformer_id;
           $employee_list_all['ag_name']         = $all->ag_name;
           array_push($all_employee, $employee_list_all);
       }
       $list['employee_list'] = $all_employee;
       array_push($list_all, $list);
   }

   $resp = (object)[
       'ag_department_list' => $list_all,
   ];
   LogInfo("--ok--");
   return 0;
}

function GetAgDepartmentInfo(&$resp)
{
   $_ = $GLOBALS["_"];
   if(!$_)
   {
       LogErr("param err");
       return errcode::PARAM_ERR;
   }
   $ag_department_id = $_['ag_department_id'];
   $mgo              = new \DaoMongodb\AGDepartment;
   $department_info  = $mgo->QueryByDepartmentId($ag_department_id);

   $department['ag_department'] = $department_info->ag_department_name;
   $resp = (object)array(
       'ag_department_name' => $department['ag_department']
   );
   // LogDebug($resp);
   LogInfo("--ok--");
   return 0;
}

function GetAgDepartmentAgent(&$resp)
{
   $_ = $GLOBALS["_"];
   if (!$_)
   {
       LogErr("param err");
       return errcode::PARAM_ERR;
   }
   $ag_department_id = $_['ag_department_id'];

   $employee      = new \DaoMongodb\AGEmployee;
   $user          = new \DaoMongodb\User;
   $employee_list = $employee->GetDepartmentPlatformer($ag_department_id);

   foreach ($employee_list as &$v)
   {
       $position              = new \DaoMongodb\AGPosition;
       $position_info         = $position->GetPositionById($v->ag_position_id);
       $v->ag_position_name   = $position_info->ag_position_name;
       $department            = new \DaoMongodb\AGDepartment;
       $department_info       = $department->QueryByDepartmentId($ag_department_id);
       $v->ag_department_name = $department_info->ag_department_name;
       $user_info             = $user->QueryById($v->userid);
       $v->phone              = $user_info->phone;
   }
   $resp = (object)[
       'ag_employee_list' => $employee_list,
   ];
   //LogDebug($resp);
   LogInfo("--ok--");
   return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_["get_pl_department_list"]))
{
    $ret = GetAllPlDepartment($resp);
} elseif(isset($_["get_pl_department_info"]))
{
    $ret = GetPlDepartmentInfo($resp);
} elseif(isset($_["get_department_platformer"]))
{
    $ret = GetPlDepartmentPlatformer($resp);
}elseif(isset($_["get_ag_department_list"]))
{
    $ret = GetAllAgDepartment($resp);
} elseif(isset($_["get_ag_department_info"]))
{
    $ret = GetAgDepartmentInfo($resp);
} elseif(isset($_["get_department_agent"]))
{
    $ret = GetAgDepartmentAgent($resp);
}
$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>