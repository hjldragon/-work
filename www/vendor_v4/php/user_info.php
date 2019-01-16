<?php
/*
 * [rockyshi 2014-10-04]
 * 用户信息表
 *
 */
require_once("current_dir_env.php");
require_once("mgo_user.php");
require_once("mgo_employee.php");
require_once("redis_login.php");
require_once("mgo_shop.php");
require_once("mgo_position.php");
require_once("mgo_department.php");
require_once("redis_id.php");
require_once("class.smtp.php");
require_once("class.phpmailer.php");
require_once("/www/public.sailing.com/php/cache.php");
//Permission::PageCheck();
//$_=$_REQUEST;
function GetUserList(&$resp)
{
    $_ = $GLOBALS["_"];
    //LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $dao = new \DaoMongodb\User;
    $list = $dao->GetUserList($list);
    $ret = [];
    foreach($list as $i => &$user) {
        $employee = \Cache\Employee::Get($user->userid);
        if(null == $employee)
        {
            $employee = (object)array();
        }
        array_push($ret, [
            'userid'    => $user->userid,
            'username'  => $user->username,
            'shop_id'   => $employee->shop_id,
            'shop_name' => \Cache\Shop::GetShopName($employee->shop_id),
            'ctime'     => $user->ctime,
        ]);
    }

    $resp = (object)array(
        'list' => $ret
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetUserInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid = $_["user_id"];

    $dao = new \DaoMongodb\User;
    $userinfo = $dao->QueryById($userid);
    if($userinfo->userid != $userid)
    {
        LogErr("dao->QueryById err, userid=[$userid]");
        return -1;
    }

    // 是店铺用户，取店铺信息
    $shopinfo = (object)array();;
    $employee = (object)array();;
    if(($userinfo->property & UserProperty::SHOP_USER) != 0)
    {
        $employee = \Cache\Employee::Get($userinfo->userid);
        if($employee)
        {
            $shopinfo = \Cache\Shop::Get($employee->shop_id);
        }
    }

    // if("" != $userinfo->password)
    // {
    //     $userinfo->password = str_repeat("*", strlen($userinfo->password));
    // }
    $userinfo->password = "";

    $resp = (object)array(
        'logininfo'    => \Cache\Login::Get(),
        'userinfo'     => $userinfo,
        'shopinfo'     => $shopinfo,
        'employeeinfo' => $employee,
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function DelUser(&$resp)
{
    Permission::AdminCheck();

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $user_id_list = json_decode($_["userlist"]);

    LogDebug($user_id_list);

    $dao = new \DaoMongodb\User;
    $ret = $dao->BatchDeleteById($user_id_list);
    if($ret < 0)
    {
        LogErr("dao->BatchDeleteById err, ret=[$ret]");
        return $ret;
    }
    LogInfo("--ok--");
    return 0;
}
// 修改或注册用户
function UserSetting(&$resp)
{
    Permission::AdminCheck();

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid       = $_["user_id"];
    $new_username = $_["new_username"];
    $new_password = $_["new_password"];
    $new_prompt   = $_["new_prompt"];
    $phone        = $_["phone"];
    $shop_id      = \Cache\Login::GetShopId();
    if("" == $new_username)
    {
        LogErr("param err, new_username:[$new_username]");
        return errcode::USER_NAME_EMPTY;
    }

    if(!$userid)
    {
        $userid = \DaoRedis\Id::GenUserId();
    }
    $dao = new \DaoMongodb\User;
    // 是否已注册过
    $ret = $dao->IsExist([
        'userid'   => $userid,
        'username' => $new_username,
        'phone'    => $phone,
    ]);

    if(null != $ret && $ret->userid != $userid)
    {
        if($ret->username)
        {
            LogErr("username exist:[{$ret->username}]");
            return errcode::USER_HAD_REG;
        }
        else if($ret->phone)
        {
            LogErr("phone exist:[{$ret->phone}");
            return errcode::PHONE_IS_EXIST;
        }
    }

    $entry = new \DaoMongodb\UserEntry;
    $entry->userid        = $userid;
    $entry->username      = $new_username;
    $entry->password      = $new_password;
    $entry->passwd_prompt = $new_prompt;
    $entry->ctime         = time();
    $entry->delete        = 0;
    $entry->phone         = $phone;
    if($shop_id)
    {
        $entry->property |= UserProperty::SHOP_USER;
    }
    LogDebug($entry);

    $ret = $dao->Save($entry);
    if($ret < 0)
    {
        LogErr("Update err, ret=[$ret]");
        return $ret;
    }

    if($shop_id)
    {
        // 登记到员工表
        $mgo_employee              = new \DaoMongodb\Employee;
        $employee_info             = new \DaoMongodb\EmployeeEntry;
        $employee_info->userid     = $userid;
        $employee_info->shop_id    = $shop_id;
        $employee_info->duty       = EmployeeDuty::SYS_SHOP_ADMIN;
        $employee_info->real_name  = ""; //$shopinfo->shop_name;
        $employee_info->delete     = 0;
        $employee_info->permission = EmployeePermission::AllPermission();

        $ret = $mgo_employee->Save($employee_info);
        if(0 != $ret)
        {
            LogErr("Save err");
            return $ret;
        }
    }

    $resp = (object)array(
        'userid' => $userid
    );
    LogDebug($resp);

    LogInfo("register ok, userid=[{$userid}], ip:[{$_SERVER['REMOTE_ADDR']}]");
    return 0;
}
//修改用户密码
function EditUserPassword(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid = $_['userid'];
    if(!$userid)
    {
        $userid = \Cache\Login::GetUserid();
    }

//    if (!$userid)
//    {
//        LogErr("user not login");
//        return errcode::USER_NOLOGIN;
//    }

    $old_password       = md5($_['old_password']);
    $new_password       = $_['new_password'];
    $new_password_again = $_['new_password_again'];
    $mgo      = new \DaoMongodb\User;
    $entry    = new \DaoMongodb\UserEntry;
    $userinfo = $mgo->QueryById($userid);
    $password = $userinfo->password;
//    LogDebug($old_password);
//    LogDebug($password);
//    LogDebug($new_password);
//    LogDebug($new_password_again);

    if ($old_password !== md5($password))
    {
        LogErr("old_ps and read_ps is different");
        return errcode::USER_PASSWD_ERR;
    }
    if($password === $new_password)
    {
        LogErr("password is same");
        return errcode::PASSWORD_SAME;
    }
    if ($new_password != $new_password_again)
    {
        LogErr("password two is different");
        return errcode::PASSWORD_TWO_SAME;
    }

    $entry->userid   = $userid;
    $entry->password = $new_password;
    $ret = $mgo->Save($entry);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)[];
    LogInfo("save ok");
    return 0;

}
//编辑用户个人中心信息
function EditUserInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid             = \Cache\Login::GetUserid();
    $real_name          = $_['real_name'];
    $identity           = $_['identity'];
    if($identity){
        if (!preg_match('/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/', $identity))
        {
            LogErr("idcard err");
            return errcode::IDCARD_ERR;
        }
    }
    if((int)$identity == 0)
    {
        return errcode::IDCARD_ERR;
    }
    $sex                = $_['sex'];
    $health_certificate = $_['health_certificate'];
    $is_weixin          = $_['is_weixin'];
    //默认未未绑定微信
    if(!$is_weixin)
    {
        $is_weixin = 0;
    }
    $mgo                = new \DaoMongodb\User;
    $user               = new \DaoMongodb\UserEntry;
    $user->userid             = $userid;
    $user->real_name          = $real_name;
    $user->identity           = $identity;
    $user->sex                = $sex;
    $user->is_weixin          = $is_weixin;
    $user->health_certificate = $health_certificate;
    $ret                      = $mgo->Save($user);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[];
    LogInfo("save ok");
    return 0;

}
//获取个人中心个人信息
function GetUserEditInfo(&$resp)
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

    $dao      = new \DaoMongodb\User;
    $userinfo = $dao->QueryById($userid);
    if ($userinfo->userid != $userid)
    {
        LogErr("dao->QueryById err, userid=[$userid]");
        return -1;
    }
    $employee = new \DaoMongodb\Employee;
    $list =$employee->GetEmployeeById($userid);
    $info = [];
    foreach ($list as $v)
    {   
        if($v->is_admin != 1){
            $shop_id                     = $v->shop_id;
            $position_id                 = $v->position_id;
            $department_id               = $v->department_id;
            $shop                        = new \DaoMongodb\Shop;
            $shop_info                   = $shop->GetShopById($shop_id);
            $shop_name                   = $shop_info->shop_name;
            $position                    = new \DaoMongodb\Position;
            $position_info               = $position->GetPositionById($shop_id, $position_id);
            $position_name               = $position_info->position_name;
            $department                  = new \DaoMongodb\Department;
            $department_info             = $department->QueryByDepartmentId($shop_id, $department_id);
            $department_name             = $department_info->department_name;
            $list_all['shop_name']       = $shop_name;
            $list_all['position_name']   = $position_name;
            $list_all['department_name'] = $department_name;
            array_push($info, $list_all);
        }
    }
    $resp = (object)[
        'userinfo'       => $userinfo,
        'work_info_list' => $info,
    ];
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
function GetUser(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid = $_['shop_userid'];
    if(!$userid)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $dao      = new \DaoMongodb\User;
    $userinfo = $dao->QueryById($userid);
    if ($userinfo->userid != $userid)
    {
        LogErr("dao->QueryById err, userid=[$userid]");
        return -1;
    }
    $resp = (object)[
        'userinfo'       => $userinfo,
    ];
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = null;
if($_["list"])
{
    $ret =  GetUserList($resp);
}
elseif($_["info"])
{
    $ret =  GetUserInfo($resp);
}
elseif($_["delete"])
{
    $ret =  DelUser($resp);
}
elseif($_["user_setting"])
{
    $ret =  UserSetting($resp);
}elseif (isset($_['save_password']))
{
    $ret = EditUserPassword($resp);
}elseif(isset($_['edit_user_info']))
{
    $ret = EditUserInfo($resp);
}else if(isset($_['get_user_info']))
{
    $ret = GetUserEditInfo($resp);
}else if(isset($_['get_user']))
{
    $ret = GetUser($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
    //'crypt' => 1, // 是加密数据标记
    //'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
