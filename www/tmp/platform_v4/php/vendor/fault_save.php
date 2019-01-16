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

//故障信息(<<<<<临时用来创建数据目前还未有公众号)
function SaveInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_id          = $_['vendor_id'];
    $deal_name          = $_['deal_name'];
    $shop_id            = $_['shop_id'];

    $entry = new VendorMgo\FaultEntry;
    $mgo   = new VendorMgo\Fault;

    $fault_id                = \DaoRedis\Id::GenFaultId();
    $entry->shop_id          = $shop_id;
    $entry->fault_id         = $fault_id;
    $entry->vendor_id        = $vendor_id;
    $entry->deal_name        = $deal_name;
    $entry->fault_time       = time();
    $entry->deal_time        = time();
    $entry->send_time        = time();
    $entry->is_deal          = 1;
    $entry->is_inform        = IsInform::NO;
    $entry->deal_remark      = 'SBHEHEDA';
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

//处理故障发送运营通知信息
function SendFault(&$resp)
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
    $vendor = new VendorMgo\Vendor;

    $fault_info  = $mgo->QueryById($fault_id);
    $vendor_info = $vendor->QueryById($fault_info->vendor_id);
    //通知给设备的负责人//<<<<<<<<<<<<<产品未给短信内容占时屏蔽
//    $msg     = '设备故障问题已通知请注意，呵呵，我是赛领科技DSB人工智能。';
//    $msg_ret = Util::SmsSend($vendor_info->person_phone, $msg);
//    if(0 != $msg_ret)
//    {
//        LogErr("send err code".$msg_ret);
//    }

    //派送到微信公众号上(处理故障的流程有问题)
    $entry->fault_id         = $fault_id;
    $entry->send_time        = time();
    $entry->is_inform        = IsInform::YES;

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
}else if(isset($_['send_fault']))
{
    $ret = SendFault($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

