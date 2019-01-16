<?php
/*
 * [Rocky 2017-05-12 20:16:27]
 * 订单信息保存类
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("/www/public.sailing.com/php/page_util.php");
require_once("mgo_shop.php");
use Pub\Vendor\Mongodb as VendorMgo;

//断货通知
function SendOutMessage(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_id           = $_['vendor_id'];

    if(!$vendor_id)
    {
        LogDebug('no vendor_id');
        return errcode::PARAM_ERR;
    }
    $entry  = new VendorMgo\VendorEntry;
    $mgo    = new VendorMgo\Vendor;

    $entry->vendor_id     = $vendor_id;
    $entry->vendor_status = VendorStatus::STOCKOUT;
    $entry->stockout_time = time();

    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    //通知给设备的负责人//<<<<<<<<<<<<<产品未给短信内容占时屏蔽
//    $msg     = '设备故障问题已通知请注意，呵呵，我是赛领科技DSB人工智能。';
//    $msg_ret = Util::SmsSend($vendor_info->person_phone, $msg);
//    if(0 != $msg_ret)
//    {
//        LogErr("send err code".$msg_ret);
//    }
    $resp = (object)[
    ];
    return 0;
}
$ret = -1;
$resp = (object)array();
if (isset($_['save_vendor_status']))
{
    $ret = SendOutMessage($resp);
}else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

