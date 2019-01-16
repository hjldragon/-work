<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 店铺信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_ag_position.php");
require_once("permission.php");
require_once("mgo_ag_employee.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
require_once("/www/public.sailing.com/php/mgo_ag_role.php");
use \Pub\Mongodb as Mgo;
function SaveAgPosition(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $ag_position_id         = $_['ag_position_id'];
    $ag_position_name       = $_['ag_position_name'];
    $ag_position_permission = json_decode($_['ag_position_permission']);
    $ag_position_note       = $_['ag_position_note'];
    $agent_id               = $_['agent_id'];


    $mgo               = new \DaoMongodb\AGPosition;
    $entry             = new \DaoMongodb\AGPositionEntry;
    $ag_position_info  = $mgo->QueryByName($agent_id,$ag_position_name);
    if($ag_position_info->ag_position_name && $ag_position_info->ag_position_id != $ag_position_id)
    {
        LogDebug($ag_position_name,'this shop have this position name:'.$ag_position_info->ag_position_name);
        return errcode::NAME_IS_EXIST;
    }
    if (!$ag_position_id)
    {
        AgPermissionCheck::PageCheck(AgentPermissionCode::ADD_AG_POSITION);
        $ag_position_id       = \DaoRedis\Id::GenAgPositionId();
        $ctime                = time();
        $entry->audit_person  = 0;
    }else{
        AgPermissionCheck::PageCheck(AgentPermissionCode::EDIT_AG_POSITION);
    }
    if($ag_position_info->entry_type != 1)
    {
        $entry->entry_type             = 2;
    }
    $entry->ag_position_id         = $ag_position_id;
    $entry->ag_position_name       = $ag_position_name;
    $entry->ag_position_permission = $ag_position_permission;
    $entry->ag_position_note       = $ag_position_note;
    $entry->agent_id               = $agent_id;
    $entry->delete                 = 0;
    $entry->is_edit                = 0;
    $entry->ctime                  = $ctime;

    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
        'ag_position_id' => $ag_position_id
    ];
    LogInfo("save ok");
    return 0;
}

function DeleteAgPosition(&$resp)
{
    AgPermissionCheck::PageCheck(AgentPermissionCode::DEL_AG_POSITION);
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $ag_position_id  = $_['ag_position_id'];
    $agent_id        = $_['agent_id'];
    $mongodb         = new \DaoMongodb\AGPosition;
    $ag_role         = new Mgo\AgRole;
    $infos            = $ag_role->GetListByPositionId($ag_position_id);
    if (count($infos) > 0)
    {
        LogErr("Delete err ,ag_role have ag_position");
        return errcode::POSITION_NOT_DEL;
    }
    $ret             = $mongodb->BatchDelete($agent_id,$ag_position_id);
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
$ret = -1;
$resp = (object)array();
if(isset($_['ag_position_save']))
{
    $ret = SaveAgPosition($resp);
}elseif(isset($_['del_ag_position']))
{
    $ret = DeleteAgPosition($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
