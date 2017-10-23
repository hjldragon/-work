         <?php
/*
 * [Rocky 2017-06-19 19:02:01]
 * 员工信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_employee.php");


Permission::EmployeePermissionCheck(
     Permission::CHK_SHOP_ADMIN
);

// 修改或注册用户
// 注：$userinfo->userid中返回注册的id
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
    $userid             = $_['user_id'];
    $real_name          = $_['real_name'];
    $phone              = $_['phone'];
    $employee_no        = $_['employee_no'];
    $depart             = $_['depart'];
    $duty               = $_['duty'];
    $permission         = json_decode($_['permission']);
    $passwd             = $_['passwd'];
    $section            = $_['section'];
    $health_certificate = $_['health_certificate'];
    $remark             = $_['remark'];
    $is_freeze          = $_['is_freeze'];

    if(!$userid)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    // 只包含*时，不设置
    if("" != $passwd && 0 == preg_match("/[^*]/", $passwd))
    {
        $passwd = null;
    }

    $shop_id = \Cache\Login::GetShopId();

    $userinfo = new \DaoMongodb\UserEntry();
    $userinfo->userid     = $userid;
    $userinfo->phone      = $phone;
    //$userinfo->username = $shop_id . "-" . $userid;
    $userinfo->password   = $passwd;
    $userinfo->property   |= UserProperty::SHOP_USER; // 店铺用户
    $userinfo->delete     = 0;
    $ret = EmployeeUserSetting($userinfo);
    if(0 != $ret)
    {
        LogErr("register user err, ret=[$ret]");
        return errcode::USER_SETTING_ERR;
    }

    $mongodb = new \DaoMongodb\Employee;
    $entry   = new \DaoMongodb\EmployeeEntry;

    $entry->userid             = $userid;
    $entry->shop_id            = $shop_id;
    $entry->real_name          = $real_name;
    $entry->phone              = $phone;
    $entry->employee_no        = $employee_no;
    $entry->duty               = $duty;
    $entry->permission         = $permission;
    $entry->remark             = $remark;
    $entry->health_certificate = $health_certificate;
    $entry->section            = $section;
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
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $user_id_list  = json_decode($_['user_id_list']);

    if(!$user_id_list)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $mongodb = new \DaoMongodb\Employee;
    $ret = $mongodb->BatchDelete($user_id_list);
    if(0 != $ret)
    {
        LogErr("BatchDelete err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
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
    $userid  = $_['userid'];
    $type = $_['type'];

    if(!$userid)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $mongodb = new \DaoMongodb\Employee;
    $ret = $mongodb->BatchFreeze($userid,$type);
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
