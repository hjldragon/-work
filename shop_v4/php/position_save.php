<?php

require_once("current_dir_env.php");
require_once("mgo_position.php");
require_once("permission.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
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
    if($position_id)
    {
        ShopPermissionCheck::PageCheck(ShopPermissionCode::EDIT_POSITION);
    }else{
        ShopPermissionCheck::PageCheck(ShopPermissionCode::ADD_POSITION);
    }

    if(!$shop_id)
    {
        LogDebug('no shop id,user not login');
        return errcode::USER_NOLOGIN;
    }

    if (!$position_id)
    {
        $position_info       = $mgo->QueryByName($shop_id,$position_name,$position_id);
        if($position_info->position_name)
        {
            LogDebug($position_name,'this shop have this position name:'.$position_info->position_name);
            return errcode::NAME_IS_EXIST;
        }
        $position_id = \DaoRedis\Id::GenPositionId();
        $ctime       = time();
    }
    $position_name_info = $mgo->GetPositionByName($shop_id,$position_name);
    if($position_name_info->position_name && $position_id != $position_name_info->position_id)
    {
        LogDebug('name is have');
        return errcode::NAME_IS_EXIST;
    }
    $position_info2   = $mgo->GetPositionById($shop_id,$position_id);

    if ($position_info2->entry_type == \PositionType::SYSTEMTYPEONE)
    {    //系统录入的名称不能被修改
        if ($position_name != $position_info2->position_name)
        {
            LogDebug('this position is can edit');
            return errcode::SYS_DEFAULT;
        }
        $entry_type = 1;
    } else {
        $entry_type = 2;
    }
    $entry->position_id         = $position_id;
    $entry->position_name       = $position_name;
    $entry->position_permission = $position_permission;
    $entry->position_note       = $position_note;
    $entry->shop_id             = $shop_id;
    $entry->delete              = 0;
    $entry->is_edit             = 1;
    $entry->is_start            = 1;
    $entry->entry_type          = $entry_type;
    $entry->ctime               = $ctime;
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
        'position_id' => $position_id
    ];
    LogInfo("save ok");
    return 0;
}

function DeletePosition(&$resp)
{
    ShopPermissionCheck::PageCheck(ShopPermissionCode::DEL_POSITION);
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
        LogDebug('this position is can not del');
       return errcode::USER_PERMISSION_ERR;
    }
    $employee = new \DaoMongodb\Employee;
    $employee_list = $employee->GetEmployeeList($shop_id,['position_id'=>$position_id]);
    if(count($employee_list)>0)
    {
        LogDebug('have employee is use');
        return errcode::POSITION_IS_USE;
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
//启用权限
function StartPosition(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $position_id = $_['position_id'];
    $shop_id     = \Cache\Login::GetShopId();
    $is_start    = $_['is_start'];

    if(!$position_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo = new \DaoMongodb\Position;
    $employee = new \DaoMongodb\Employee;
    $employee_list = $employee->GetEmployeeList($shop_id,['position_id'=>$position_id]);
    if(count($employee_list)>0)
    {
        LogDebug('have employee is use');
        return errcode::POSITION_IS_USE;
    }

    $ret = $mgo->BatchIsStart($position_id,$shop_id, $is_start);
    if(0 != $ret)
    {
        LogErr("Batch Start err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("freeze ok");
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
}elseif(isset($_['start_position']))
{
    $ret = StartPosition($resp);
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
