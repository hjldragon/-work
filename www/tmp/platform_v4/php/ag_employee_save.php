
<?php
/*
 * [Rocky 2017-06-19 19:02:01]
 * 运营平台员工信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_ag_employee.php");
require_once("mgo_user.php");
require_once("redis_id.php");
require_once("mgo_platformer.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");

function SaveAGEmployee(&$resp)
{
	$_ = $GLOBALS["_"];
	if(!$_)
	{
		LogErr("param err");
		return errcode::PARAM_ERR;
	}

    $agent_id         = $_['agent_id'];
	$ag_employee_id   = $_['ag_employee_id'];
	$real_name        = $_['real_name'];
	$phone            = $_['phone'];
	$password         = $_['password'];
	$remark           = $_['remark'];
	$ag_role_id       = $_['ag_role_id'];
	$ag_department_id = $_['ag_department_id'];
	$is_freeze        = $_['is_freeze'];
    $employee_num     = $_['employee_num'];


	if(!$phone || !$password  || !$real_name || !$ag_role_id || !$ag_department_id || !$employee_num)
	{
		LogErr("param err");
		return errcode::PARAM_ERR;
	}
	// 验证手机格式
	if (!preg_match('/^\d{11}$/', $phone)) {
		LogErr("Phone err");
		return errcode::PHONE_ERR;
	}

	$mgo      = new \DaoMongodb\AGEmployee;
	$usermgo  = new \DaoMongodb\User;
	$userinfo = new \DaoMongodb\UserEntry;
	$entry    = new \DaoMongodb\AGEmployeeEntry;
    if($ag_employee_id)
    {

        AgPermissionCheck::PageCheck(AgentPermissionCode::EDIT_AG_EMPLOYEE);
        $ag_employee_info = $mgo->QueryById($ag_employee_id);
        $userid           = $ag_employee_info->userid;

    }else{
        AgPermissionCheck::PageCheck(AgentPermissionCode::ADD_AG_EMPLOYEE);
        $ag_employee_id    = \DaoRedis\Id::GenAGEmployeeId();
        $entry->ctime      = time();
        $userid            = \DaoRedis\Id::GenUserId();
        $userinfo->ctime   = time();
	}

    //修改的时候查询该手机号码是否已经被注册过了
    $user = $usermgo->QueryByPhone($phone, UserSrc::PLATFORM);
    if($user->phone && $user->userid != $userid)
    {
        LogErr("User have create");
        return errcode::USER_HAD_REG;
    }

	$userinfo->userid     = $userid;
	$userinfo->phone      = $phone;
	$userinfo->real_name  = $real_name;
	$userinfo->password   = $password;
	$userinfo->src        = UserSrc::PLATFORM; // 运营端用户
	$userinfo->delete     = 0;
	$ret = $usermgo->Save($userinfo);
	if(0 != $ret)
	{
		LogErr("register user err, ret=[$ret]");
		return errcode::USER_SETTING_ERR;
	}

	$entry->userid           = $userid;
	$entry->ag_employee_id   = $ag_employee_id;
	$entry->real_name        = $real_name;
	$entry->agent_id         = $agent_id;
	$entry->ag_role_id       = $ag_role_id;
	$entry->ag_department_id = $ag_department_id;
	$entry->is_freeze        = $is_freeze;
	$entry->remark           = $remark;
    $entry->employee_num     = $employee_num;
	$ret = $mgo->Save($entry);
	if(0 != $ret)
	{
		LogErr("Save err");
		return errcode::SYS_ERR;
	}
	$resp = (object)array(
		'ag_employee_id'=>$ag_employee_id
	);
	LogInfo("save ok");
	return 0;
}
// 删除员工
function DeleteAGEmployee(&$resp)
{
    AgPermissionCheck::PageCheck(AgentPermissionCode::DEL_AG_EMPLOYEE);
	$_ = $GLOBALS["_"];
	if (!$_)
	{
		LogErr("param err");
		return errcode::PARAM_ERR;
	}
	$ag_employee_id  = $_['ag_employee_id'];
	$agent_id        = $_['agent_id'];
	if (!$ag_employee_id)
	{
		LogErr("param err");
		return errcode::PARAM_ERR;
	}

	$mongodb = new \DaoMongodb\AGEmployee;
    $info = $mongodb->QueryById($ag_employee_id);
    $user = new \DaoMongodb\User;
    $userid = [];
    array_push($userid, $info->userid);
    $ret = $user->BatchDeleteById($userid);
    if (0 != $ret)
    {
        LogErr("UserDelete err");
        return errcode::SYS_ERR;
    }
	$ret = $mongodb->BatchDelete($ag_employee_id,$agent_id);
	if (0 != $ret)
	{
		LogErr("BatchDelete err");
		return errcode::SYS_ERR;
	}

	$resp = (object)[
	];
	LogInfo("delete ok");
	return 0;
}

// 冻结员工
function FreezeAGEmployee(&$resp)
{
	$_ = $GLOBALS["_"];
	if(!$_)
	{
		LogErr("param err");
		return errcode::PARAM_ERR;
	}
	$ag_employee_id  = $_['ag_employee_id'];
	$agent_id        = $_['agent_id'];
	$is_freeze       = $_['is_freeze'];
    if($is_freeze == IsFreeze::YES)
    {
        AgPermissionCheck::PageCheck(AgentPermissionCode::FREEZE_AG_EMPLOYEE);
    }else{
        AgPermissionCheck::PageCheck(AgentPermissionCode::START_AG_EMPLOYEE);
    }
	if(!$ag_employee_id)
	{
		LogErr("no ag_employee_id");
		return errcode::PARAM_ERR;
	}
	$mongodb = new \DaoMongodb\AGEmployee;
	$ret = $mongodb->BatchFreeze($ag_employee_id, $agent_id, $is_freeze);
	if(0 != $ret)
	{
		LogErr("BatchDelete err");
		return errcode::SYS_ERR;
	}

	$resp = (object)array(
	);
	LogInfo("freeze ok");
	return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_['ag_employee_save']))
{
	$ret = SaveAGEmployee($resp);
}
elseif(isset($_['ag_employee_del']))
{
	$ret = DeleteAGEmployee($resp);
}
elseif(isset($_['ag_employee_freeze']))
{
	$ret = FreezeAGEmployee($resp);
}
else
{
	LogErr("param err");
	$ret = errcode::PARAM_ERR;
}

$html = json_encode((object)array(
		'ret' => $ret,
		'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>

