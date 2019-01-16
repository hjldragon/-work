<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 店铺信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_position.php");
require_once("permission.php");
require_once("redis_id.php");
Permission::PageLoginCheck();
function SavePosition(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $position_id         = $_['position_id'];
    $mgo                 = new \DaoMongodb\Position;
    $entry               = new \DaoMongodb\PositionEntry;
    $position_name       = $_['position_name'];
    $position_permission = json_decode($_['position_permission']);
    $position_note       = $_['position_note'];
    $shop_id             = \Cache\Login::GetShopId();
    if(!$shop_id)
    {
        return errcode::USER_NOLOGIN;
    }
    $permission = 0;
    foreach ($position_permission as $v)
    {
        $permission = $permission | $v;
    }
    $position_info       = $mgo->QueryByName($shop_id,$position_name,$position_id);
    if($position_info->position_name)
    {
        LogDebug($position_name,'this shop have this position name:'.$position_info->position_name);
        return errcode::NAME_IS_EXIST;
    }
    if (!$position_id)
    {
        $position_id = \DaoRedis\Id::GenPositionId();
        $ctime       = time();
    }
    $position_info2   = $mgo->GetPositionById($shop_id,$position_id);

    if ($position_info2->entry_type == \PositionType::SYSTEMTYPEONE)
    {    //系统录入的名称不能被修改
        if ($position_name != $position_info2->position_name)
        {
            return errcode::PARAM_ERR;
        }
        $entry_type = 1;
    } else {
        $entry_type = 2;
    }
    $entry->position_id         = $position_id;
    $entry->position_name       = $position_name;
    $entry->position_permission = $permission;
    $entry->position_note       = $position_note;
    $entry->shop_id             = $shop_id;
    $entry->delete              = 0;
    $entry->is_edit             = 1;
    $entry->entry_type          = $entry_type;
    $entry->ctime               = $ctime;
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    LogInfo("save ok");
    return 0;
}

function DeletePosition(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $position_id  = $_['position_id'];
    $shop_id = \Cache\Login::GetShopId();
    LogDebug($_);
    if (!$shop_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $mongodb = new \DaoMongodb\Position;
    $position_info2   = $mongodb->GetPositionById($shop_id,$position_id);
    if ($position_info2->entry_type == \PositionType::SYSTEMTYPEONE)
    {
        //系统默认的不能被删除
       return errcode::USER_PERMISSION_ERR;
    }
    $ret     = $mongodb->BatchDelete($shop_id,$position_id);
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
if(isset($_['save_position']))
{
    $ret = SavePosition($resp);
}elseif(isset($_['del_position']))
{
    $ret = DeletePosition($resp);
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
