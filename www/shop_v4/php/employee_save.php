         <?php
/*
 * [Rocky 2017-06-19 19:02:01]
 * 员工信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_employee.php");
require_once("mgo_user.php");
require_once("redis_id.php");
require_once("mgo_authorize.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
function EmployeeUserSetting($userinfo)
{
    $shop_id = &$shopinfo->shop_id;

    if(!$userinfo->userid)
    {
        $userinfo->userid = \DaoRedis\Id::GenUserId();
    }

    $dao = new \DaoMongodb\User;

    // 是否已注册过
    $ret = $dao->IsExist([
        'userid'   => $userinfo->userid,
        'username' => $userinfo->username,
        'phone'    => $userinfo->phone,
    ]);
    //LogDebug($userlist);
    if(null != $ret && $ret->userid != $userinfo->userid)
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
    $userinfo->property |= UserProperty::SHOP_USER;
    LogDebug($userinfo);

    $ret = $dao->Save($userinfo);
    if($ret < 0)
    {
        LogErr("Update err, ret=[$ret]");
        return $ret;
    }

    return 0;
}

function SaveEmployee(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid             = $_['userid'];
    $employee_id        = $_['employee_id'];
    $real_name          = $_['real_name'];
    $phone              = $_['phone'];
    $duty               = $_['duty'];
    $passwd             = $_['passwd'];
    $section            = $_['section'];
    $health_certificate = $_['health_certificate'];
    $remark             = $_['remark'];
    $is_freeze          = $_['is_freeze'];
    $identity           = $_['identity'];
    $sex                = $_['sex'];
    $authorize          = $_['authorize'];

    if(!$phone)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    // 只包含*时，不设置
    if("" != $passwd && 0 == preg_match("/[^*]/", $passwd))
    {
        $passwd = null;
    }
    $userinfo = new \DaoMongodb\UserEntry;
    
    if(!$userid){
        $userid = \DaoRedis\Id::GenUserId();
        $userinfo->ctime   = time();
    }

    $userinfo->userid             = $userid;
    $userinfo->phone              = $phone;
    $userinfo->password           = $passwd;
    $userinfo->identity           = $identity;
    $userinfo->sex                = $sex;
    $userinfo->email              = $email;
    $userinfo->health_certificate = $health_certificate;
    $userinfo->mtime              = time();
    $userinfo->delete             = 0;
    $ret = $userinfo->Save($userinfo); 
    //$ret = EmployeeUserSetting($userinfo);
    if(0 != $ret)
    {
        LogErr("register user err, ret=[$ret]");
        return errcode::USER_SETTING_ERR;
    }
    if(!$employee_id)
    {
        $employee_id = \DaoRedis\Id::GenEmployeeId();
    }

    $mongodb = new \DaoMongodb\Employee;
    $entry   = new \DaoMongodb\EmployeeEntry;

    $entry->userid             = $userid;
    $entry->employee_id        = $employee_id;
    $entry->real_name          = $real_name;
    $entry->phone              = $phone;
    $entry->duty               = $duty;
    //$entry->permission         = $permission;
    $entry->remark             = $remark;
    //$entry->health_certificate = $health_certificate;
    $entry->section            = $section;
    //$entry->identity           = $identity;
    //$entry->sex                = $sex;
    //$entry->email              = $email;
    $entry->authorize          = $authorize;
    $entry->is_freeze          = 0;
    $entry->lastmodtime        = time();
    LogDebug($entry);

    $ret = $mongodb->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
        'lastmodtime' => $entry->lastmodtime
    );
    LogInfo("save ok");
    return 0;
}
// 删除员工
function DeleteEmployee(&$resp)
{
    ShopPermissionCheck::PageCheck(ShopPermissionCode::DEL_EMPLOYEE);
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $employee_id  = $_['employee_id'];
    $shop_id      = \Cache\Login::GetShopId();
    LogDebug($_);
    if (!$employee_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $mongodb = new \DaoMongodb\Employee;
    $em_info = $mongodb->GetEmployeeInfo($shop_id, $employee_id);
    $ret = $mongodb->BatchDelete($employee_id, $shop_id);
    if (0 != $ret)
    {
        LogErr("BatchDelete err");
        return errcode::SYS_ERR;
    }
    // 删除员工其店铺对应的登录授权已用名额改变
    $authorize = $em_info->authorize;
    if($authorize)
    {   
        $mgo   = new \DaoMongodb\Authorize;
        $entry = new \DaoMongodb\AuthorizeEntry;
        $info  = $mgo->GetAuthorizeByShop($shop_id);
        $entry->shop_id = $shop_id;
        if (($authorize & 1) != 0){// pc
            $entry->used_pc_num = $info->used_pc_num - 1;
        }
        if (($authorize & 2) != 0){// pad
            $entry->used_pad_num = $info->used_pad_num - 1;
        }
        if (($authorize & 4) != 0) {// 收银
            $entry->used_cashier_num = $info->used_cashier_num - 1;
        }
        if (($authorize & 8) != 0) {// app
            $entry->used_app_num = $info->used_app_num - 1;
        }
        if (($authorize & 16) != 0) {// app
            $entry->used_machine_num = $info->used_machine_num - 1;
        }
        $ret = $mgo->Save($entry);
        if(0 != $ret)
        {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
    }
    $resp = (object)[
    ];
    LogInfo("delete ok");
    return 0;
}
// 冻结员工
function FreezeEmployee(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $employee_id = $_['employee_id'];
    $shop_id     = \Cache\Login::GetShopId();
    $is_freeze        = $_['is_freeze'];
    if($is_freeze == IsFreeze::YES)
    {
        ShopPermissionCheck::PageCheck(ShopPermissionCode::FREEZE_EMLOYEE);
    }else{
        ShopPermissionCheck::PageCheck(ShopPermissionCode::START_EMPLOYEE);
    }
    if(!$employee_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $mongodb = new \DaoMongodb\Employee;
    $ret = $mongodb->BatchFreeze($employee_id,$shop_id, $is_freeze);
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
//第一步邀请获取验证信息并获取用户信息在get中,邀请员工第二部,保存信息。这是第二部
function InviteEmployee(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid               = $_['user_id'];

    if(!$userid)
    {
        return errcode::PARAM_ERR;
    }
    $department_id        = $_['department_id'];
    $position_id          = $_['position_id'];
    $authorize            = $_['authorize'];
    $shop_id              = \Cache\Login::GetShopId();
    if(!$shop_id)
    {
        LogErr('no shop_id');
        return errcode::USER_NOLOGIN;
    }
    $entry                = new DaoMongodb\EmployeeEntry;
    $mgo                  = new DaoMongodb\Employee;
    $info                 = $mgo->QueryByUserId($shop_id,$userid);
    if($info->userid)
    {
        LogErr('this employee is invitation');
        return errcode::EMPLOYEE_IS_EXIT;
    }
    $user      = new DaoMongodb\User;
    $user_info = $user->QueryById($userid);
    $employee_id          = \DaoRedis\Id::GenEmployeeId();
    $entry->userid        = $userid;
    $entry->delete        = 0;
    $entry->is_freeze     = 0;
    $entry->is_admin      = 0;
    $entry->shop_id       = $shop_id;
    $entry->phone         = $user_info->phone;
    $entry->real_name     = $user_info->real_name;
    $entry->employee_id   = $employee_id;
    $entry->department_id = $department_id;
    $entry->position_id   = $position_id;
    $entry->authorize     = $authorize;
    $entry->entry_time    = time();

    $ret                  = $mgo->Save($entry);
    if ($ret != 0)
    {
        LogErr("Save err, ret=[$ret]");
        return errcode::SYS_ERR;
    }
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//编辑员工接口
function SaveEmployeeInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $employee_id   = $_['employee_id'];
    $department_id = $_['department_id'];
    $position_id   = $_['position_id'];
    $authorize     = $_['authorize'];
    ShopPermissionCheck::PageCheck(ShopPermissionCode::EDIT_EMPLOYEE);
    if (!$employee_id) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $mongodb              = new \DaoMongodb\Employee;
    $entry                = new \DaoMongodb\EmployeeEntry;
    $entry->employee_id   = $employee_id;
    $entry->department_id = $department_id;
    $entry->position_id   = $position_id;
    $entry->authorize     = $authorize;
    $entry->lastmodtime   = time();
    //LogDebug($entry);

    $ret = $mongodb->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)[
    ];
    LogInfo("save ok");
    return 0;
}

         $ret = -1;
$resp = (object)array();
if(isset($_['save']))
{
    $ret = SaveEmployee($resp);
}
else if(isset($_['del_employee']) || isset($_['del']))
{
    $ret = DeleteEmployee($resp);
}
else if(isset($_['freeze_employee']))
{
    $ret = FreezeEmployee($resp);
}
else if(isset($_['shop_employee_save']))
{
    $ret = InviteEmployee($resp);
}
else if(isset($_['save_employee_info']))
{
    $ret = SaveEmployeeInfo($resp);
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
