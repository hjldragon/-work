<?php
/*
 * [Rocky 2017-06-19 19:02:01]
 * 运营代理商员工信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_platformer.php");
require_once("mgo_user.php");
require_once("redis_id.php");


Permission::PageLoginCheck();
//$_=$_REQUEST;
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
    $re_password      = $_['re_password'];
    $remark           = $_['remark'];
    $pl_position_id   = $_['pl_position_id'];
    $pl_department_id = $_['pl_department_id'];

    if(!$phone || !$password  || !$pl_name || !$pl_position_id || !$pl_department_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    // 验证手机格式
    if (!preg_match('/^\d{11}$/', $phone)) {
        LogErr("Phone err");
        return errcode::PHONE_ERR;
    }
    // 验证2次输入密码
    if ($password != $re_password) {
        LogErr("password err");
        return errcode::PASSWORD_TWO_SAME;
    }
    $mgo = new \DaoMongodb\Platformer;

    if($platformer_id)
    {
        $platformer = $mgo->QueryById($platformer_id);
        $userid     = $platformer->userid;
    }
    $usermgo  = new \DaoMongodb\User;
    $userinfo = new \DaoMongodb\UserEntry;
    $entry    = new \DaoMongodb\PlatformerEntry;
    // 根据所填号码查用户表
    $user = $usermgo->QueryByPhone($phone, UserSrc::PLATFORM);
    // 如果用户表有数据再去运营人员表及代理商员工表差
    if($user->userid && $platformer->userid != $user->userid)
    {
        LogErr("Phone err");
        return errcode::USER_HAD_REG;
        // 如果是创建或编辑时手机号改变判断手机号是否重复注册
        // if(!$platformer_id || )
        // {
        //     $agepmgo = new \DaoMongodb\AGEmployee;
        //     $plinfo = $mgo->QueryByUserId($user->userid);
        //     $aginfo = $agepmgo->QueryByUserId($user->userid);
        //     if($plinfo->userid || $aginfo->userid)
        //     {

        //     }
        // }
        // $userid = $user->userid;
    }

    if(!$platformer_id){
        $platformer_id = \DaoRedis\Id::GenPlatformerId();
        $entry->ctime  = time();
    }
    if(!$userid){
        $userid = \DaoRedis\Id::GenUserId();
        $userinfo->ctime   = time();
    }

    $userinfo->userid     = $userid;
    $userinfo->phone      = $phone;
    //$userinfo->username = $shop_id . "-" . $userid;
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
    $entry->pl_position_id   = $pl_position_id;
    $entry->pl_department_id = $pl_department_id;
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
        'platformer_id'=>$platformer_id
    );
    LogInfo("save ok");
    return 0;
}
// 删除员工
function DeletePlatformer(&$resp)
{
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