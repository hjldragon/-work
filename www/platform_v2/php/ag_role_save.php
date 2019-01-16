<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 店铺信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_platformer.php");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("/www/public.sailing.com/php/mgo_ag_role.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;


function AgRoleSave(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $role_name      = $_['role_name'];
    $agent_id       = $_['agent_id'];
    $ag_role_id     = $_['ag_role_id'];
    $ag_position_id = $_['ag_position_id'];

    $mgo         = new Mgo\AgRole;
    $entry       = new Mgo\AgRoleEntry;
    if($ag_role_id)
    {
        AgPermissionCheck::PageCheck(AgentPermissionCode::EDIT_AG_ROLE);
    }else{
        AgPermissionCheck::PageCheck(AgentPermissionCode::ADD_AG_ROLE);
    }

    if(!$ag_role_id)
    {
        $ag_role_id = \DaoRedis\Id::GenAgRoleId();
    }
    $info  =  $mgo->QueryByRoleName($role_name, $agent_id);
    if($info->role_name && $ag_role_id != $info->ag_role_id)
    {
        LogDebug('role name is have');
        return errcode::NAME_IS_EXIST;
    }
    $entry->ag_role_id       = $ag_role_id;
    $entry->agent_id         = $agent_id;
    $entry->role_name        = $role_name;
    $entry->ag_position_id   = $ag_position_id;
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

function DeleteAgRole(&$resp)
{
    AgPermissionCheck::PageCheck(AgentPermissionCode::DEL_AG_ROLE);
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $ag_role_id     = $_['ag_role_id'];
    $mgo            = new Mgo\AgRole;
    $ag_mgo         = new \DaoMongodb\AGEmployee;
    $ag_list = $ag_mgo->GetPositionAgEmployee($ag_role_id);
    if(count($ag_list) > 0)
    {
        LogDebug('role is have ag employee,can not del');
        return errcode::ROLE_IS_USE;
    }
    $ret            = $mgo->BatchDeleteById($ag_role_id);
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
if(isset($_['save_ag_role']))
{
    $ret = AGRoleSave($resp);
}elseif(isset($_['delete_ag_role']))
{
    $ret = DeleteAgRole($resp);
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

