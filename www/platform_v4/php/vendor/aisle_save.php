
<?php
/*
 * [Rocky 2017-05-12 20:16:27]
 * 订单信息保存类
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_record.inc");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("/www/public.sailing.com/php/page_util.php");
require_once("mgo_shop.php");
use Pub\Vendor\Mongodb as VendorMgo;

//保存设备货道信息<<<<<<<公众号
function SaveVendorAisle(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }


    $aisle_id          = $_['aisle_id'];
    $vendor_goods_id   = $_['vendor_goods_id'];
    $is_sale           = $_['is_sale'];
    $goods_num         = $_['goods_num'];
    $use_name          = $_['use_name'];
    if(!$aisle_id)
    {
        LogDebug('no aisle id');
        return errcode::PARAM_ERR;
    }
    $entry        = new VendorMgo\AisleEntry;
    $mgo          = new VendorMgo\Aisle;
    $record_entry = new VendorMgo\VendorRecordEntry;
    $record_mgo   = new VendorMgo\VendorRecord;

    $entry->aisle_id        = $aisle_id;
    $entry->vendor_goods_id = $vendor_goods_id;
    $entry->is_sale         = $is_sale;
    $entry->goods_num       = $goods_num;

    $ret  = $mgo->Save($entry);
    if (0 != $ret)
    {
            LogErr("aisle Save err");
            return errcode::SYS_ERR;
    }
    $info  = $VendorMgo->QueryById($aisle_id);
    //补货成功保存记录
    $record_entry->record_id       = DaoRedis\Id::GenVendorRecordId();
    $record_entry->aisle_id        = $aisle_id;
    $record_entry->vendor_goods_id = $vendor_goods_id;
    $record_entry->goods_num       = $goods_num;
    $record_entry->record_time     = time();
    $record_entry->use_name        = $use_name;
    $record_entry->vendor_id       = $info->vendor_id;

    $ret_one  = $record_mgo->Save($record_entry);
    if (0 != $ret_one)
    {
        LogErr("record Save err");
        return errcode::SYS_ERR;
    }
    //减掉总有库存<<<<<
    $resp = (object)[];

    return 0;
}

//通知补货
function SendOutGoods(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $aisle_id           = $_['aisle_id'];
    if(!$aisle_id)
    {
        LogDebug('no aisle_id');
        return errcode::PARAM_ERR;
    }
    $entry  = new VendorMgo\AisleEntry;
    $mgo    = new VendorMgo\Aisle;
    $vendor = new VendorMgo\Vendor;

    $aisle_info  = $mgo->QueryById($aisle_id);
    $vendor_info = $vendor->QueryById($aisle_info->vendor_id);
    //通知给设备的负责人//<<<<<<<<<<<<<产品未给短信内容占时屏蔽
//    $msg     = '设备故障问题已通知请注意，呵呵，我是赛领科技DSB人工智能。';
//    $msg_ret = Util::SmsSend($vendor_info->person_phone, $msg);
//    if(0 != $msg_ret)
//    {
//        LogErr("send err code".$msg_ret);
//    }

    //派送到微信公众号上
    $entry->fault_id         = $aisle_id;
    $entry->is_send          = IsInform::YES;

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
if(isset($_['aisle']))//<<<<后台未用此接口
{
    $ret = SaveVendorAisle($resp);
}else if(isset($_['save_aisle']))
{
    $ret = SendOutGoods($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);
