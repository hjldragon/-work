<?php
/*
 * [Rocky 2017-06-20 00:10:18]
 * 代理商员工信息表
 *
 */
require_once("current_dir_env.php");
require_once("mgo_ag_employee.php");
require_once("redis_id.php");
require_once("cache.php");
require_once("mgo_ag_department.php");
require_once("mgo_ag_position.php");
require_once("mgo_user.php");
require_once("mgo_agent.php");
require_once("/www/public.sailing.com/php/mgo_ag_role.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;
function GetAGEmployeeInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $ag_employee_id = $_['ag_employee_id'];

    $mgo         = new \DaoMongodb\AGEmployee;
    $agemployee  = $mgo->QueryById($ag_employee_id);
    if(!$agemployee->ag_employee_id)
    {
       LogErr('[ag_employee_id] err');
       return errcode::USER_NO_EXIST;
    }
    if(1 == $agemployee->is_admin)
    {
        $agemployee->position_name = '超级管理员';
    }
    else
    {
        $department                     = new \DaoMongodb\AGDepartment;
        $department_info                = $department->QueryByDepartmentId($agemployee->ag_department_id);
        $agemployee->ag_department_name = $department_info->ag_department_name;
   /*     $position                       = new \DaoMongodb\AGPosition;
        $position_info                  = $position->GetPositionById($agemployee->ag_position_id);
        $agemployee->ag_position_name   = $position_info->ag_position_name;*/
        $ag_role                       = new Mgo\AgRole;
        $ag_role_info                  = $ag_role->QueryById($agemployee->ag_role_id);
        $agemployee->ag_role_name      = $ag_role_info->role_name;
    }
    if($agemployee->from_record)
    {
        $from_record = array_unique(array_filter($agemployee->from_record));
        $agemployee->from_record = array_values($from_record);
    }
    if($agemployee->salesman_record)
    {
        $salesman_record = array_unique(array_filter($agemployee->salesman_record));
        $agemployee->salesman_record = array_values($salesman_record);
    }
    $agent                  = new \DaoMongodb\Agent;
    $agent_info             = $agent->QueryById($agemployee->agent_id);
    $agemployee->agent_name = $agent_info->agent_name;
    $userinfo = \Cache\UsernInfo::Get($agemployee->userid);
    $agemployee->phone    = $userinfo->phone;
    $agemployee->password = $userinfo->password;
    
    $resp = (object)[
        'agemployee' => $agemployee
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetAGEmployeeList(&$resp)
{
    AgPermissionCheck::PageCheck(AgentPermissionCode::AG_EMPLOYEE_LIST);
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $agent_id         = $_['agent_id'];
    $real_name        = $_['real_name'];
    $is_freeze        = $_['is_freeze'];
    $page_size        = $_['page_size'];
    $page_no          = $_['page_no'];
    $sort_name        = $_['sort_name'];
    $desc             = $_['desc'];
    $ag_department_id = $_['ag_department_id'];

    switch ($sort_name) {
        case 'ag_employee_id':
            $sort['_id'] = (int)$desc;
            break;
        case 'is_freeze':
            $sort['is_freeze'] = (int)$desc;
            break;
        default:
            break;
    }
    if(!$page_size)
    {
        $page_size  = 10;
    }
    if(!$page_no)
    {
        $page_no = 1;
    }
    $mgo        = new \DaoMongodb\AGEmployee;
    $position   = new \DaoMongodb\AGPosition;
    $department = new \DaoMongodb\AGDepartment;
    $ag_role    = new Mgo\AgRole;
    $total= 0;
    $list = $mgo->GetAgEmployeeList($agent_id,
        [
            'is_freeze'        => $is_freeze,
            'ag_department_id' => $ag_department_id,
            'real_name'        => $real_name,
        ],
        $page_size,
        $page_no,
        $sort,
        $total
        );

    $info = [];
    foreach ($list as $v)
    {
        $department_info       = $department->QueryByDepartmentId($v->ag_department_id);
//        $position_info         = $position->GetPositionById($v->ag_position_id);
//        $v->ag_position_name   = $position_info->ag_position_name;

        $ag_role_info          = $ag_role->QueryById($v->ag_role_id);
        $v->ag_role_name       = $ag_role_info->role_name;
        $v->ag_department_name = $department_info->ag_department_name;
        $userinfo              = \Cache\UsernInfo::Get($v->userid);
        $v->phone              = $userinfo->phone;
        array_push($info, $v);
    }
    $resp = (object)[
        'ag_employee_list'=> $info,
        'total'           => $total,
        'page_size'       => $page_size,
        'page_no'         => $page_no,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

//获取代理商所有销售人员
function GetAGSalesmanList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $agent_id     = $_['agent_id'];
    //$audit_person     = json_decode($_['audit_person']);
    $audit_person = [1,2];
    $mgo          = new \DaoMongodb\AGEmployee;
    $position     = new \DaoMongodb\AGPosition;
    $role         = new Mgo\AgRole;

    $position_list   = $position->GetPositionByAudit($agent_id,$audit_person);
    $id_list      = [];
    $role_id_list = [];

    foreach ($position_list as $v)
    {
        $id = $v->ag_position_id;
        array_push($id_list,$id);
    }
    $role_list = $role->GetIdList($id_list);
    foreach ($role_list as $vs)
    {
        $ids = $vs->ag_role_id;
        array_push($role_id_list,$ids);
    }

    $all_list  = $mgo->GetAgEmployeeByPsIdList($agent_id, $role_id_list);
    $new_list = [];
    foreach ($all_list as $v_one)
    {
        $list['ag_employee_name'] = $v_one->real_name;
        $list['ag_employee_id']   = $v_one->ag_employee_id;
        array_push($new_list,$list);
    }

    $resp = (object)[
        'list' => $new_list
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//通过姓名搜索所有销售人员
function GetAGSalesmanListByName(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $agent_id         = $_['agent_id'];
    $ag_employee_name = $_['ag_employee_name'];
    $audit_person     = [1,2];

    $mgo          = new \DaoMongodb\AGEmployee;
    $position     = new \DaoMongodb\AGPosition;
    $role         = new Mgo\AgRole;

    $position_list   = $position->GetPositionByAudit($agent_id,$audit_person);
    $id_list    = [];
    foreach ($position_list as $v)
    {
        $id = $v->ag_position_id;
        array_push($id_list,$id);
    }
    $role_id_list = [];
    $role_list    = $role->GetIdList($id_list);
    foreach ($role_list as $vs)
    {
        $ids = $vs->ag_role_id;
        array_push($role_id_list,$ids);
    }
    $all_list = $mgo->GetAgEmployeeByNameList($ag_employee_name,$role_id_list);
    $new_list = [];
    foreach ($all_list as $v_one)
    {
        $list['ag_employee_name'] = $v_one->real_name;
        $list['ag_employee_id']   = $v_one->ag_employee_id;
        array_push($new_list,$list);
    }
    $resp = (object)[
        'list' => $new_list
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_["get_ag_employee_info"]))
{
    $ret = GetAGEmployeeInfo($resp);
}elseif(isset($_["get_ag_employee_list"]))
{
    $ret = GetAGEmployeeList($resp);
}elseif(isset($_["get_ag_salesman_list"]))
{
    $ret = GetAGSalesmanList($resp);
}elseif(isset($_["get_salesman_list_byname"]))
{
    $ret = GetAGSalesmanListByName($resp);
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
?>