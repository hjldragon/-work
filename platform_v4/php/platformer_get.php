<?php
/*
 * [Rocky 2017-06-20 00:10:18]
 * 运营平台员工信息表
 *
 */
require_once("current_dir_env.php");
require_once("mgo_platformer.php");
require_once("redis_id.php");
require_once("cache.php");
require_once("mgo_pl_department.php");
require_once("mgo_pl_position.php");
require_once("mgo_user.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
require_once("/www/public.sailing.com/php/mgo_pl_role.php");
use \Pub\Mongodb as Mgo;
function GetPlatformerInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $platformer_id = $_['platformer_id'];

    $mgo         = new \DaoMongodb\Platformer;
    $platformer  = $mgo->QueryById($platformer_id);
    if(!$platformer->platformer_id)
    {
       LogErr('[platformer_id] err');
       return errcode::USER_NO_EXIST;
    }
    if(1 == $platformer->is_admin)
    {
        $platformer->position_name = '超级管理员';
    }
    else
    {
        $department                  = new \DaoMongodb\PLDepartment;
        $department_info             = $department->QueryByDepartmentId($platformer->pl_department_id);
        $platformer->department_name = $department_info->pl_department_name;
        $pl_role                     = new Mgo\PlRole;
        $role_info                   = $pl_role->QueryById($platformer->pl_role_id);
        $platformer->position_name   = $role_info ->role_name;
    }
    if($platformer->from_record)
    {
        $from_record = array_unique(array_filter($platformer->from_record));
        $platformer->from_record = array_values($from_record);
    }
    if($platformer->salesman_record)
    {
        $salesman_record = array_unique(array_filter($platformer->salesman_record));
        $platformer->salesman_record = array_values($salesman_record);
    }
    $user = new \DaoMongodb\User;
    $user_info = $user->QueryById($platformer->userid);
    $platformer->phone    = $user_info->phone;
    $platformer->password = $user_info->password;
    $resp = (object)[
        'platformer' => $platformer
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetPlatformerList(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::PLATFORM_LIST);
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $page_size        = $_['page_size'];
    $page_no          = $_['page_no'];
    $sort_name        = $_['sort_name'];
    $pl_name          = $_['real_name'];
    $is_freeze        = $_['is_freeze'];
    $desc             = $_['desc'];
    $pl_department_id = $_['pl_department_id'];

        switch ($sort_name) {
            case 'platformer_id':
                $sort['_id'] = (int)$desc;
                break;
            case 'is_freeze'://反结账
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
    $mgo  = new \DaoMongodb\Platformer;
    $user = new \DaoMongodb\User;
    $total= 0;
    $list = $mgo->GetplatformerList(
        [
            'is_freeze'        => $is_freeze,
            'pl_name'          => $pl_name,
            'pl_department_id' => $pl_department_id
        ],
        $page_size,
        $page_no,
        $sort,
        $total
    );
    $info = [];
    foreach ($list as $v)
    {   
//        if($v->is_admin == 1)
//        {
//            continue;
//        }

        $department            = new \DaoMongodb\PLDepartment;
        $department_info       = $department->QueryByDepartmentId($v->pl_department_id);
        $pl_role               = new Mgo\PlRole;
        $role_info             = $pl_role->QueryById($v->pl_role_id);
        $user_info             = $user->QueryById($v->userid);
        $v->phone              = $user_info->phone;
        $v->role_name          = $role_info->role_name;
        $v->pl_department_name = $department_info->pl_department_name;
        array_push($info, $v);
    }
    $resp = (object)[
        'platformer_list' => $info,
        'total'           => $total,
        'page_size'       => $page_size,
        'page_no'         => $page_no,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetPlatformerNameList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $mgo  = new \DaoMongodb\Platformer;
    $platform_id = PlatformID::ID;

    $list = $mgo->GetPlatformId($platform_id);
    $all  = array();
    foreach ($list as $value) {
        $info = (object)array();
        $info->platformer_id  = $value->platformer_id;
        $info->pl_name        = $value->pl_name;
        array_push($all, $info);
    }

    $resp = (object)[
        'platformer_list' => $all,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetSpecLable(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid = \Cache\Login::GetUserid();
    if(!$userid)
    {
        return errcode::USER_NOLOGIN;
    }

    $mgo = new \DaoMongodb\Platformer;
    $info = $mgo->QueryByUserId($userid, PlatformID::ID);
    if (!$info->platformer_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $spec_lable = [];
    if($info->spec_record)
    {
        $spec_record = array_unique(array_filter($info->spec_record));
        $spec_lable  = array_values($spec_record);
    }
    $resp = (object)[
        'info' => $spec_lable
    ];
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

//获取平台所有销售人员
function GetPlatformerSalesmanList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $audit_person    = [1,2];
    $mgo             = new \DaoMongodb\Platformer;
    $pl_position     = new \DaoMongodb\PLPosition;
    $role            = new Mgo\PlRole;

    $position_list   = $pl_position->GetPositionByAudit($audit_person);
    $id_list = [];
    foreach ($position_list as $v)
    {
        $id = $v->pl_position_id;
        array_push($id_list,$id);
    }
    $role_id_list = [];
    $role_list    = $role->GetIdList($id_list);
    foreach ($role_list as $vs)
    {
        $ids = $vs->pl_role_id;
        array_push($role_id_list,$ids);
    }

    $all_list = $mgo->GetByPsIdList($role_id_list);

    $new_list = [];
    foreach ($all_list as $v_one)
    {
        $list['pl_name']         = $v_one->pl_name;
        $list['platformer_id']   = $v_one->platformer_id;
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
if(isset($_["get_platformer_info"]))
{
    $ret = GetPlatformerInfo($resp);
}elseif(isset($_["get_platformer_list"]))
{
    $ret = GetPlatformerList($resp);
}elseif(isset($_["get_platformer_name"]))
{
    $ret = GetPlatformerNameList($resp);
}else if(isset($_['get_spec_label']))
{
    $ret = GetSpecLable($resp);
}else if(isset($_['get_pl_salesman_list']))
{
    $ret = GetPlatformerSalesmanList($resp);
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