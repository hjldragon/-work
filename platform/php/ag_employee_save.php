
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


//Permission::PageCheck();
//$_=$_REQUEST;
function SaveAGEmployee(&$resp)
{
	$_ = $GLOBALS["_"];
	if(!$_)
	{
		LogErr("param err");
		return errcode::PARAM_ERR;
	}
	$ag_employee_id   = $_['ag_employee_id'];
	$real_name        = $_['real_name'];
	$phone            = $_['phone'];
	$password         = $_['password'];
	$re_password      = $_['re_password'];
	$remark           = $_['remark'];
	$ag_position_id   = $_['ag_position_id'];
	$ag_department_id = $_['ag_department_id'];
	$agent_id         = $_['agent_id'];

	if(!$phone || !$password  || !$real_name || !$ag_position_id || !$ag_department_id)
	{
		LogErr("param err");
		return errcode::PARAM_ERR;
	}
	// 验证手机格式
	if (!preg_match('/^1[34578]\d{9}$/', $phone)) {
		LogErr("Phone err");
		return errcode::PHONE_ERR;
	}
	// 验证2次输入密码
	if ($password != $re_password) {
		LogErr("password err");
		return errcode::PASSWORD_TWO_SAME;
	}
	$mgo = new \DaoMongodb\AGEmployee;

	if($ag_employee_id)
	{
		$ag_employee_info = $mgo->QueryById($ag_employee_id);
		$userid           = $ag_employee_info->userid;
	}
	$usermgo  = new \DaoMongodb\User;
	$userinfo = new \DaoMongodb\UserEntry;
	$entry    = new \DaoMongodb\AGEmployeeEntry;
	// 根据所填号码查用户表
	$user = $usermgo->QueryByPhone($phone, UserSrc::PLATFOR);
	// 如果用户表有数据再去运营人员表及代理商员工表差
	if($user->userid && $ag_employee_info->userid != $user->userid)
	{
		LogErr("Phone err");
		return errcode::USER_HAD_REG;
	}

	if(!$ag_employee_id){
		$ag_employee_id = \DaoRedis\Id::GenAGEmployeeId();
		$entry->ctime   = time();
	}
	if(!$userid){
		$userid = \DaoRedis\Id::GenUserId();
		$userinfo->ctime   = time();
	}

	$userinfo->userid     = $userid;
	$userinfo->phone      = $phone;
	//$userinfo->username = $shop_id . "-" . $userid;
	$userinfo->password   = $password;
	$userinfo->src        = UserSrc::PLATFOR; // 运营端用户
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
	$entry->ag_position_id   = $ag_position_id;
	$entry->ag_department_id = $ag_department_id;
	$entry->is_freeze        = 0;
	$entry->remark           = $remark;
	//LogDebug($entry);
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
	$ret     = $mongodb->BatchDelete($ag_employee_id,$agent_id);
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

