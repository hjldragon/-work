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
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
require_once("ChuanglanSmsHelper/ChuanglanSmsApi.php");
require_once("/www/public.sailing.com/php/send_sms.php");

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
    //var_dump($employee_info);
    $authorize = $employee->authorize;
    $auth = [];
    if($authorize)
    {
       if (($authorize & 1) != 0){// 1:商家运营平台
            $auth[] = 1;
        }
        if (($authorize & 2) != 0){// 2:平板智能点餐机
            $auth[] = 2;
        }
        if (($authorize & 4) != 0) {// 4:智能收银机
            $auth[] = 4;
        }
        if (($authorize & 8) != 0) {// 8:掌柜通
            $auth[] = 8;
        }
        if (($authorize & 16) != 0) {//16.自助点餐机
            $auth[] = 16;
        }
    }
    $employee_info['authorize']       = $auth;
    $user                             = new \DaoMongodb\User;
    //LogDebug($userid);
    $userinfo                         = $user->QueryById($userid);
    $user_info                        = [];
    $user_info['real_name']           = $userinfo->real_name;
    $user_info['identity']            = $userinfo->identity;
    $user_info['health_certificate']  = $userinfo->health_certificate;
    $user_info['is_weixin']           = $userinfo->is_weixin;
    $user_info['phone']               = $userinfo->phone;
    $user_info['sex']                 = $userinfo->sex;
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
    ShopPermissionCheck::PageCheck(ShopPermissionCode::SEE_EMPLOYEE);
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
    LogInfo("--ok--");
    return 0;
}

//邀请员工第一步获取手机号无图形验证码并获取手机号用户信息,第二部邀请成功在save中。这是第一步
function InviteGetUserInfo(&$resp)
{
    ShopPermissionCheck::PageCheck(ShopPermissionCode::INVITE_EMPLOYEE);
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $token      = $_['token'];
    $phone      = $_['phone'];
    if (!preg_match('/^\d{11}$/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }
    $src        = $_['src'];
    if(!$src)
    {
        $src  = Src::SHOP;
    }
    $user       = new \DaoMongodb\User;
    $mgo        = new DaoMongodb\Employee;
    $shop_id    = \Cache\Login::GetShopId();
    $userinfo   = $user->QueryByPhone($phone,$src);

    if(!$userinfo->phone)
    {
        LogErr('user not zc');
        return errcode::USER_NOT_ZC;
    }
    $srctype = $_['srctype'];
    if($srctype != 2)
    {
        $phone_code = $_['phone_code'];//手机验证码
        $result     = PageUtil::VerifyPhoneCode($token, $phone, $phone_code);

        //验证手机结果
        if ($result != 0)
        {
            LogDebug($result);
            return $result;
        }
    }

    $user                             = new DaoMongodb\User;
    $userid                           = $userinfo->userid;
    $user_info                        = [];
    $user_info['real_name']           = $userinfo->real_name;
    $user_info['identity']            = $userinfo->identity;
    $user_info['sex']                 = $userinfo->sex;
    $user_info['$health_certificate'] = $userinfo->health_certificate;
    $user_info['$is_weixin']          = $userinfo->is_weixin;



    if(!$shop_id)
    {
        LogErr('no shop_id:'.$shop_id);
        return errcode::USER_NOLOGIN;
    }
    $info                 = $mgo->QueryByUserId($shop_id,$userinfo->userid);
    if($info->userid)
    {
        $invitation = 1;
    }else{
        $invitation = 0;
    }
    $resp = (object)[
        'userid'    => $userid,
        'invitation'=> $invitation,
        'user_info' => $userinfo,

    ];
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//无图形验证码发送手机验证码并验证该店铺是否已被邀请
function GetInvitePhoneCode(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token      = $_['token'];
    $phone      = $_['phone'];
    $user       = new \DaoMongodb\User;
    $mgo        = new \DaoMongodb\Employee;
    $shop_id    = \Cache\Login::GetShopId();

    $userinfo   = $user->QueryByPhone($phone, UserSrc::SHOP);
    if(!$userinfo->phone)
    {
        LogDebug("not user message");
        return errcode::USER_NOT_ZC;
    }

    $employee_info  = $mgo->QueryByUserId($shop_id, $userinfo->userid);
    if($employee_info->userid)
    {
        LogErr('employee is invite');
        return errcode::EMPLOYEE_IS_EXIT;
    }


    if (!preg_match('/^\d{11}$/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }
    $code   = mt_rand(100000, 999999);
    Sms::GetSms($phone,$code);//发送手机验证码
    $redis            = new \DaoRedis\Login();
    $data             = new \DaoRedis\LoginEntry();
    $data->phone      = $phone;
    $data->token      = $token;
    $data->phone_code = $code;
    $data->code_time  = time() + 5 * 60;
    $redis->Save($data);
    $resp = (object)[
    ];
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
}elseif (isset($_['get_invite_phone_code'])) {

    $ret = GetInvitePhoneCode($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret' => $ret,
    'data' => $resp
);

if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}
?><?php /******************************以下为html代码******************************/?>
