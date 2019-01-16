<?php
/*
 */
require_once("current_dir_env.php");
require_once("mgo_pl_position.php");
require_once("mgo_platformer.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
require_once("/www/public.sailing.com/php/mgo_pl_role.php");
use \Pub\Mongodb as Mgo;

function SavePlPosition(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $pl_position_id         = $_['pl_position_id'];
    $pl_position_name       = $_['pl_position_name'];
    $pl_position_permission = json_decode($_['pl_position_permission']);
    $pl_position_note       = $_['pl_position_note'];
    $platform_id            = PlatformID::ID;
    if($pl_position_id)
    {
        PlPermissionCheck::PageCheck(PlPermissionCode::EDIT_POSITION);
    }else{
        PlPermissionCheck::PageCheck(PlPermissionCode::ADD_POSITION);
    }


    $mgo               = new \DaoMongodb\PLPosition;
    $entry             = new \DaoMongodb\PLPositionEntry;
    $pl_position_info  = $mgo->QueryByName($platform_id,$pl_position_name);
    if($pl_position_info->pl_position_name && $pl_position_info->pl_position_id != $pl_position_id)
    {
        LogDebug($pl_position_name,'this shop have this position name:'.$pl_position_info->pl_position_name);
        return errcode::NAME_IS_EXIST;
    }
    if (!$pl_position_id)
    {
        $pl_position_id    = \DaoRedis\Id::GenPlPositionId();
        $entry->ctime      = time();
        $entry->delete     = 0;
        $entry->entry_type = 2;
    }
    $entry->pl_position_id         = $pl_position_id;
    $entry->pl_position_name       = $pl_position_name;
    $entry->pl_position_permission = $pl_position_permission;
    $entry->pl_position_note       = $pl_position_note;
    $entry->platform_id            = $platform_id;
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
        'pl_position_id' =>$pl_position_id
    ];
    LogInfo("save ok");
    return 0;
}

function DeletePlPosition(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::DEL_POSITION);
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $pl_position_id  = $_['pl_position_id'];
    $platform_id     = PlatformID::ID;
    $mongodb         = new \DaoMongodb\PLPosition;
    $pl_role         = new Mgo\PlRole;
    $infos            = $pl_role->GetListByPositionId($pl_position_id);

    if (count($infos) > 0)
    {
        LogErr("Delete err ,pl_role have pl_position");
        return errcode::POSITION_NOT_DEL;
    }
    $ret             = $mongodb->BatchDelete($platform_id,$pl_position_id);
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
if(isset($_['pl_position_save']))
{
    $ret = SavePlPosition($resp);
}elseif(isset($_['del_pl_position']))
{
    $ret = DeletePlPosition($resp);
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
