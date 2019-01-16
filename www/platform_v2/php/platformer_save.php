<?php
/*
 * [Rocky 2017-06-19 19:02:01]
 * 运营代理商员工信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_platformer.php");
require_once("mgo_user.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");

function SavePlatformer(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $platformer_id    = $_['platformer_id'];
    $pl_name          = $_['pl_name'];
    $phone            = $_['phone'];
    $password         = $_['password'];
    $pl_role_id       = $_['pl_role_id'];
    $remark           = $_['remark'];
    $pl_department_id = $_['pl_department_id'];
    $is_freeze        = $_['is_freeze'];
    $platformer_num   = $_['platformer_num'];

    if($platformer_id){
        PlPermissionCheck::PageCheck(PlPermissionCode::EDIT_PLATFORME);
    }else{
        PlPermissionCheck::PageCheck(PlPermissionCode::ADD_PLATFORMER);
    }


    $mgo      = new \DaoMongodb\Platformer;
    $usermgo  = new \DaoMongodb\User;
    $userinfo = new \DaoMongodb\UserEntry;
    $entry    = new \DaoMongodb\PlatformerEntry;



    if(!$phone || !$password  || !$pl_name  || !$pl_department_id)
    {
        LogErr("some canshu is empty");
        return errcode::PARAM_ERR;
    }

        // 验证手机格式
        if (!preg_match('/^\d{11}$/', $phone)) {
            LogErr("Phone err");
            return errcode::PHONE_ERR;
        }
//        // 验证2次输入密码
//        if ($password != $re_password) {
//            LogErr("password err");
//            return errcode::PASSWORD_TWO_SAME;
//        }


    if($platformer_id)
    {
        $platformer = $mgo->QueryById($platformer_id);
        $userid     = $platformer->userid;
    }else{
        if(!$platformer_num)
        {
            LogErr("platformer_num is empty");
            return errcode::PARAM_ERR;
        }
        $platformer_id     = \DaoRedis\Id::GenPlatformerId();
        $entry->ctime      = time();
        $userid            = \DaoRedis\Id::GenUserId();
        $userinfo->ctime   = time();
    }

    // 根据所填号码查用户表
    $user = $usermgo->QueryByPhone($phone, UserSrc::PLATFORM);
    // 如果用户表有数据再去运营人员表及代理商员工表差
    if($user->userid && $userid != $user->userid)
    {
        LogErr("User have create");
        return errcode::USER_HAD_REG;
    }

    $userinfo->userid     = $userid;
    $userinfo->phone      = $phone;
    $userinfo->real_name  = $pl_name;
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
    $entry->platformer_id    = $platformer_id;
    $entry->pl_name          = $pl_name;
    $entry->platform_id      = PlatformID::ID;
    //$entry->pl_position_id   = $pl_position_id;
    $entry->pl_department_id = $pl_department_id;
    $entry->is_freeze        = $is_freeze;
    $entry->remark           = $remark;
    $entry->platformer_num   = $platformer_num;
    $entry->pl_role_id       = $pl_role_id;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
        'platformer_id' => $platformer_id
    );
    LogInfo("save ok");
    return 0;
}
// 删除员工
function DeletePlatformer(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::DEL_PLATFORME);
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $platformer_id  = $_['platformer_id'];
    if (!$platformer_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $mongodb = new \DaoMongodb\Platformer;
    $info = $mongodb->QueryById($platformer_id);
    $user = new \DaoMongodb\User;
    $userid = [];
    array_push($userid, $info->userid);
    $ret = $user->BatchDeleteById($userid);
    if (0 != $ret)
    {
        LogErr("UserDelete err");
        return errcode::SYS_ERR;
    }

    $ret = $mongodb->BatchDeleteById($platformer_id);
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
function FreezePlatformer(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $platformer_id = $_['platformer_id'];
    $is_freeze     = $_['is_freeze'];
    if($is_freeze == IsFreeze::YES)
    {
        PlPermissionCheck::PageCheck(PlPermissionCode::FREEZE_PLATFORME);
    }else{
        PlPermissionCheck::PageCheck(PlPermissionCode::START_PLATFORME);
    }
    if(!$platformer_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mongodb = new \DaoMongodb\Platformer();
    $ret = $mongodb->BatchFreeze($platformer_id, $is_freeze);
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

function SaveSpecLable(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $spec_label = json_decode($_["spec_label"]);
    if (!$spec_label)
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
    // 保存输入记录
    $mgo   = new \DaoMongodb\Platformer;
    $entry = new \DaoMongodb\PlatformerEntry;
    $entry->platformer_id   = $info->platformer_id;
    $entry->spec_record     = $spec_label;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['platformer_save']))
{
    $ret = SavePlatformer($resp);
}
elseif(isset($_['platformer_del']))
{
    $ret = DeletePlatformer($resp);
}
elseif(isset($_['platformer_freeze']))
{
    $ret = FreezePlatformer($resp);
}else if(isset($_['save_spec_label']))
{
    $ret = SaveSpecLable($resp);
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