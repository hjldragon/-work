<?php
/*
 * [Rocky 2017-05-12 20:16:27]
 * 订单信息保存类
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_fault_deal.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

//故障信息(<<<<<临时用来创建数据<<<<<<<<<<<<需要和设备管理人配合联调改变设备状态)
function SaveInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_id          = $_['vendor_id'];
    $shop_id            = $_['shop_id'];

    $entry = new VendorMgo\FaultEntry;
    $mgo   = new VendorMgo\Fault;

    $fault_id                = \DaoRedis\Id::GenFaultId();
    $entry->shop_id          = $shop_id;
    $entry->fault_id         = $fault_id;
    $entry->vendor_id        = $vendor_id;
    $entry->fault_time       = time();
    $entry->deal_status      = DealStatus::NODEAL;
    $entry->is_inform        = IsInform::NO;
    $entry->is_reminder      = IsInform::NO;
    $entry->is_send          = IsInform::NO;
    $entry->delete           = 0;

    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    return 0;
}

//派单
function SaveSendFault(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $fault_id           = $_['fault_id'];
    $deal_name          = $_['deal_name'];
    $name_phone         = $_['name_phone'];
    if(!$fault_id)
    {
        LogDebug('no fault_id');
        return errcode::PARAM_ERR;
    }
    $entry  = new VendorMgo\FaultEntry;
    $mgo    = new VendorMgo\Fault;

    $entry->fault_id         = $fault_id;
    $entry->is_send          = 1;
    $entry->send_time        = time();
    $entry->deal_name        = $deal_name;
    $entry->name_phone       = $name_phone;
    $entry->deal_status      = DealStatus::DEALING;

    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    return 0;
}

//催单
function SaveReminderFault(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $fault_id           = $_['fault_id'];

    if(!$fault_id)
    {
        LogDebug('no fault_id');
        return errcode::PARAM_ERR;
    }

    $entry  = new VendorMgo\FaultEntry;
    $mgo    = new VendorMgo\Fault;
    $fault_info = $mgo->QueryById($fault_id);

    if($fault_info->is_reminder == 1)
    {
        LogDebug('is reminder');
        return errcode::VENDOR_FAULT_REMI;
    }

    $entry->fault_id         = $fault_id;
    $entry->is_reminder      = 1;

    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    return 0;
}

//处理完成
function DealSaveInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $fault_id          = $_['fault_id'];
    $deal_remark       = $_['deal_remark'];
    $deal_status       = $_['deal_status'];

    if(!$fault_id)
    {
        LogDebug('no fault id');
        return errcode::PARAM_ERR;
    }

    $entry = new VendorMgo\FaultEntry;
    $mgo   = new VendorMgo\Fault;


    $entry->fault_id         = $fault_id;
    $entry->deal_time        = time();
    $entry->deal_remark      = $deal_remark;
    if($deal_status == DealStatus::NODEAL)//<<<<<处理不了就恢复之前中间流程处理的数据
    {
        $entry->deal_remark      = DealStatus::NODEAL;
        $entry->is_send          = 0;
        $entry->deal_name        = "";
        $entry->name_phone       = "";
        $entry->send_time        = 0;
        $entry->is_reminder      = 0;
    }elseif($deal_status == DealStatus::DEAL){
        $entry->deal_remark      = DealStatus::DEAL;
    }else{
        LogDebug('deal status no one or three,is err');
        return errcode::PARAM_ERR;
    }


    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_['save']))
{
    $ret = SaveInfo($resp);
}
else if(isset($_['is_send_fault']))
{
    $ret = SaveSendFault($resp);
}
else if(isset($_['is_reminder']))
{
    $ret = SaveReminderFault($resp);
}
else if(isset($_['save_deal']))
{
    $ret = DealSaveInfo($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

