<?php
/*
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/mgo_pl_role.php");
require_once("mgo_platformer.php");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;


function PlRoleSave(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $role_name      = $_['role_name'];
    $pl_role_id     = $_['pl_role_id'];
    $pl_position_id = $_['pl_position_id'];
    if($pl_role_id)
    {
        PlPermissionCheck::PageCheck(PlPermissionCode::EDIT_ROLE);
    }else{
        PlPermissionCheck::PageCheck(PlPermissionCode::ADD_ROLE);
    }

    $mgo         = new Mgo\PlRole;
    $entry       = new Mgo\PlRoleEntry;

    if(!$pl_role_id)
    {
        $pl_role_id  = \DaoRedis\Id::GenPlRoleId();
    }

    $info  =  $mgo->QueryByRoleName($role_name);
    if($info->role_name && $pl_role_id != $info->pl_role_id)
    {
        LogDebug('role name is have');
        return errcode::NAME_IS_EXIST;
    }
    $entry->pl_role_id       = $pl_role_id;
    $entry->role_name        = $role_name;
    $entry->pl_position_id   = $pl_position_id;
    $entry->delete           = 0;

    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

function DeletePlRole(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::DEL_ROLE);
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $pl_role_id     = $_['pl_role_id'];
    $mgo            = new Mgo\PlRole;
    $pl_mgo         = new \DaoMongodb\Platformer;
    $pl_list = $pl_mgo->GetPositionPlatformer($pl_role_id);
    if(count($pl_list) > 0)
    {
        LogDebug('role is have platformer,can not del');
        return errcode::ROLE_IS_USE;
    }
    $ret = $mgo->BatchDeleteById($pl_role_id);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("delete ok");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_['save_pl_role']))
{
    $ret = PlRoleSave($resp);
}elseif(isset($_['delete_pl_role']))
{
    $ret = DeletePlRole($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
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

