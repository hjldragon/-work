<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 店铺信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_pl_position.php");
require_once("permission.php");
require_once("mgo_platformer.php");
require_once("redis_id.php");
//Permission::PageCheck();
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
    $permission = 0;
    foreach ($pl_position_permission as $v)
    {
        $permission = $permission | $v;
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
        $pl_position_id = \DaoRedis\Id::GenPlPositionId();
        $ctime          = time();
    }
    $entry->pl_position_id         = $pl_position_id;
    $entry->pl_position_name       = $pl_position_name;
    $entry->pl_position_permission = $permission;
    $entry->pl_position_note       = $pl_position_note;
    $entry->platform_id            = $platform_id;
    $entry->delete                 = 0;
    $entry->ctime                  = $ctime;
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
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $pl_position_id  = $_['pl_position_id'];
    $platform_id     = PlatformID::ID;
    $mongodb         = new \DaoMongodb\PLPosition;
    $platformer      = new \DaoMongodb\Platformer;
    $info            = $platformer->GetPositionPlatformer($pl_position_id);
    if ($info)
    {
        LogErr("Delete err ,pl_position have employee");
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
