<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 新建店铺生成的数据文件
 */
require_once("current_dir_env.php");
require_once("mgo_shop.php");
require_once("redis_id.php");
require_once("mgo_employee.php");
require_once("/www/public.sailing.com/php/mgo_stall.php");
use \Pub\Mongodb as Mgo;
//后厨基础设置
function SaveShopSetInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id             = $_['shop_id'];
    $is_change_urge      = $_['is_change_urge'];
    $is_show_wait_time   = $_['is_show_wait_time'];
    $refresh_time        = $_['refresh_time'];
    $is_over_time        = $_['is_over_time'];
    $stall_id            = $_['stall_id'];
    $order_clear_time    = $_['order_clear_time'];
    $kitchen_seat_list   = json_decode($_['kitchen_seat_list']);

    if(!$shop_id || $order_clear_time < 0){
        LogErr("no shop id or time is less zero");
        return errcode::PARAM_ERR;
    }
    $mgo         = new \DaoMongodb\Shop;
    $entry       = new \DaoMongodb\ShopEntry;
    $stall_entry = new Mgo\StallEntry;
    $stall_mgo   = new Mgo\Stall;
    $employee    = new \DaoMongodb\Employee;
    $userid      = \Cache\Login::GetUserid();
    if(!$userid){
        LogDebug('no userid');
        return errcode::USER_NOLOGIN;
    }
    $employee_info = $employee->QueryByShopId($userid, $shop_id);
    //保存档口数据,一台机器只保存一个档口数据，如果写入全部，所有数据都不启用
    if($stall_id)
    {
        //1.先遍历出其他的已启用的数据变为关闭
        $list     = $stall_mgo->GetListByShop($shop_id, $employee_info->employee_id);
        foreach ($list as $v)
        {
            if($v->is_stall == StallStart::START)
            {
                $stall_entry->shop_id  = $v->shop_id;
                $stall_entry->stall_id = $v->stall_id;
                $stall_entry->is_stall = StallStart::NO;
                $ret = $stall_mgo->Save($stall_entry);
                if (0 != $ret)
                {
                    LogErr("stall info save err");
                }
            }
        }
        //2.改变这个ID为启用的
        $stall_entry->shop_id  = $shop_id;
        $stall_entry->stall_id = $stall_id;
        $stall_entry->is_stall = StallStart::START;
        $ret = $stall_mgo->Save($stall_entry);
        if (0 != $ret)
        {
            LogErr("stall info save err");
        }

    }else{
        //没有id的情况删除所有设置
        $list     = $stall_mgo->GetListByShop($shop_id, $employee_info->employee_id);
        foreach ($list as $v)
        {
            if($v->is_stall == StallStart::START)
            {
                $stall_entry->shop_id  = $v->shop_id;
                $stall_entry->stall_id = $v->stall_id;
                $stall_entry->is_stall = StallStart::NO;
                $ret = $stall_mgo->Save($stall_entry);
                if (0 != $ret)
                {
                    LogErr("stall info save err");
                }
            }
        }
    }

    $entry->shop_id           = $shop_id;
    $entry->is_change_urge    = $is_change_urge;
    $entry->is_show_wait_time = $is_show_wait_time;
    $entry->refresh_time      = $refresh_time;
    $entry->stall_id          = $stall_id;
    $entry->is_over_time      = $is_over_time;
    $entry->is_stall          = $is_stall;
    $entry->kitchen_seat_list = $kitchen_seat_list;
    $entry->order_clear_time = $order_clear_time;
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
if(isset($_['save_set']))
{
    $ret = SaveShopSetInfo($resp);
}else
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
