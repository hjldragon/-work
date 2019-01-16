<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取餐品信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_employee.php");
require_once("/www/public.sailing.com/php/mgo_stall.php");
require_once("/www/public.sailing.com/php/redis_id.php");
use \Pub\Mongodb as Mgo;

function SaveStallInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id      = $_['shop_id'];
    $stall_list   = json_decode($_['stall_list']);
    $userid       = \Cache\Login::GetUserid();
    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::SHOP_ID_NOT;
    }
    if(!$userid){
        LogDebug('no userid');
        return errcode::USER_NOLOGIN;
    }
    $entry     = new Mgo\StallEntry;
    $mgo       = new Mgo\Stall;
    $employee  = new \DaoMongodb\Employee;

    $employee_info = $employee->QueryByShopId($userid, $shop_id);

    foreach ($stall_list as $stall)
    {

        if($stall->type == KsType::SAVE)//type等于1的时候都是编辑和新增//后厨协议定义的
        {
            if(!$stall->food_id_list)
            {
                LogDebug('food id list is empty');
                return errcode::PARAM_ERR;
            }

            if(empty($stall->stall_id))
            {
                $stall_id      = \DaoRedis\Id::KitchenStallId();
            }else{
                $stall_id      = $stall->stall_id;
            }

            $entry->shop_id      = $shop_id;
            $entry->stall_id     = $stall_id;
            $entry->stall_name   = $stall->stall_name;
            $entry->food_id_list = $stall->food_id_list;
            $entry->employee_id  = $employee_info->employee_id;
            $entry->is_stall     = StallStart::NO;
            $entry->delete       = 0;
            $ret = $mgo->Save($entry);
            if(0 != $ret)
            {
                LogErr("Save err");
                return errcode::SYS_ERR;
            }
        }elseif($stall->type == KsType::DEL)//type等于2的时候就是删除
        {
            $entry->shop_id      = $shop_id;
            $entry->stall_id     = $stall->stall_id;
            $entry->delete       = 1;
            $ret = $mgo->Save($entry);
            if(0 != $ret)
            {
                LogErr("Save err");
                return errcode::SYS_ERR;
            }
        }else{
            LogDebug('no need type num in stall list');
            return errcode::PARAM_ERR;
        }

    }
    LogInfo("save ok");

    $resp = (object)array(
    );

    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["save_stall"]))
{
    $ret = SaveStallInfo($resp);
}else
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
//\Pub\PageUtil::HtmlOut($ret, $resp);
?><?php /******************************以下为html代码******************************/?>

