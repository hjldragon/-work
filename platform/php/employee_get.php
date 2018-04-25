<?php
/*
 * [Rocky 2017-06-20 00:10:18]
 * 员工信息表
 *
 */
require_once("current_dir_env.php");
require_once("mgo_employee.php");
require_once("mgo_shop.php");
require_once("redis_id.php");
require_once("cache.php");
require_once("mgo_department.php");
require_once("mgo_position.php");
require_once("mgo_user.php");
Permission::PageCheck();

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
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetEmployeeOneInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = \Cache\Login::GetShopId();
    if (!$shop_id)
    {
        LogDebug($shop_id);
        return errcode::USER_NOLOGIN;
    }
    $shop        = new \DaoMongodb\Shop;
    $shop_info   = $shop->GetShopById($shop_id);
    $employee_id = $_['employee_id'];

    $mgo                              = new \DaoMongodb\Employee;
    $employee                         = $mgo->GetEmployeeInfo($shop_id, $employee_id);
    $userid                           = $employee->userid;
    $employee_info                    = [];
    $employee_info['department_id']   = $employee->department_id;
    $department                       = new \DaoMongodb\Department;
    $department_info                  = $department->QueryByDepartmentId($shop_id, $employee->department_id);
    $employee_info['department_name'] = $department_info->department_name;
    $employee_info['employee_id']     = $employee->employee_id;
    $employee_info['position_id']     = $employee->position_id;
    $position                         = new \DaoMongodb\Position;
    $position_info                    = $position->GetPositionById($shop_id, $employee->position_id);
    $employee_info['position_name']   = $position_info->position_name;

    $user                             = new \DaoMongodb\User;
    LogDebug($userid);
    $userinfo                         = $user->QueryById($userid);
    $user_info                        = [];
    $user_info['real_name']           = $userinfo->real_name;
    $user_info['identity']            = $userinfo->identity;
    $user_info['health_certificate']  = $userinfo->health_certificate;
    $user_info['is_weixin']           = $userinfo->is_weixin;
    $user_info['phone']               = $userinfo->phone;
    $resp = (object)[
        'shop_name'     => $shop_info->shop_name,
        'employee_info' => $employee_info,
        'userinfo'      => $user_info,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetEmployeeAllList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = \Cache\Login::GetShopId();
    LogDebug($shop_id);
    if (!$shop_id)
    {
        LogDebug($shop_id);
        return errcode::USER_NOLOGIN;
    }

    $mgo  = new \DaoMongodb\Employee;
    $list = $mgo->GetEmployeeList($shop_id);
    foreach ($list as &$v)
    {
        $department         = new \DaoMongodb\Department;
        $department_info    = $department->QueryByDepartmentId($shop_id, $v->department_id);
        $position           = new \DaoMongodb\Position;
        $position_info      = $position->GetPositionById($shop_id, $v->position_id);
        $userinfo           = new \DaoMongodb\User;
        $user_info          = $userinfo->QueryById($v->userid);
        $v->position_name   = $position_info->position_name;
        $v->department_name = $department_info->department_name;
        $v->is_weixin       = $user_info->is_weixin;

    }
    $resp = (object)[
        'employee_list' => $list,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

//邀请员工第一步获取手机号无图形验证码并获取手机号用户信息,第二部邀请成功在save中。这是第一步
function InviteGetUserInfo(&$resp)
{

    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $token      = $_['token'];
    $phone      = $_['phone'];
    if (!preg_match('/^1[34578]\d{9}$/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }
    $user       = new \DaoMongodb\User;
    $userinfo   = $user->QueryByPhone($phone);
    if(!$userinfo->phone)
    {
        return errcode::USER_NOT_ZC;
    }
    $phone_code = $_['phone_code'];//手机验证码
    $result     = PageUtil::VerifyPhoneCode($token, $phone, $phone_code);

    //验证手机结果
    if ($result != 0)
    {
        LogDebug($result);
        return $result;
    }
    $user                             = new DaoMongodb\User;
    $userinfo                         = $user->QueryByPhone($phone);
    $userid                           = $userinfo->userid;
    $user_info                        = [];
    $user_info['real_name']           = $userinfo->real_name;
    $user_info['identity']            = $userinfo->identity;
    $user_info['sex']                 = $userinfo->sex;
    $user_info['health_certificate']  = $userinfo->health_certificate;
    $user_info['is_weixin']           = $userinfo->is_weixin;

    $resp = (object)[
        'userid'    => $userid,
        'user_info' => $userinfo,
    ];
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
}elseif(isset($_["get_employee_info"]))
{
    $ret = GetEmployeeOneInfo($resp);
}elseif(isset($_["get_employee_list"]))
{
    $ret = GetEmployeeAllList($resp);
}else if(isset($_['get_user_info']))
{
    $ret = InviteGetUserInfo($resp);
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