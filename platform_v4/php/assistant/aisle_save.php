<?php
/*
 * [Rocky 2017-05-12 20:16:27]
 * 设备类
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("/www/public.sailing.com/php/page_util.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_record.inc");
use Pub\Vendor\Mongodb as VendorMgo;

//编辑货道信息
function SaveAisle(&$resp)
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
    $goods_num         = $_['goods_num'];//用于减货数量<<<<加货的话只能用补货完成来操作
    if(!$aisle_id)
    {
        LogDebug('no aisle id');
        return errcode::PARAM_ERR;
    }
    $aisle_entry  = new VendorMgo\AisleEntry;
    $aisle_mgo    = new VendorMgo\Aisle;
    $vendor_mgo   = new VendorMgo\Vendor;
    $vendor_entry = new VendorMgo\VendorEntry;
    $goods_mgo    = new VendorMgo\VendorGoods;
    $goods_entry  = new VendorMgo\VendorGoodsEntry;

    $aisle_info   = $aisle_mgo->QueryById($aisle_id);
    if($goods_num > $aisle_info->goods_num)
    {
        LogDebug('aisle less goods num more than old aisle is goods num');
        return errcode::PARAM_ERR;
    }
    $aisle_entry->aisle_id        = $aisle_id;
    if($is_sale)
    {
        $aisle_entry->vendor_goods_id = "";
    }else{
        $aisle_entry->vendor_goods_id = $vendor_goods_id;
    }

    if($vendor_goods_id && $vendor_goods_id != $aisle_info->vendor_goods_id)
    {
        $goods_num          = 0;
    }
    $aisle_entry->goods_num = $goods_num;

    //以下情况将设备变为补货状态
    if($is_sale || ($vendor_goods_id && $vendor_goods_id != $aisle_info->vendor_goods_id) || $goods_num == 0)
    {

        $vendor_entry->vendor_id     = $aisle_info->vendor_id;
        $vendor_entry->vendor_status =  VendorStatus::STOCKOUT;
        $vendor_ret  = $vendor_mgo->Save($vendor_entry);
        if (0 != $vendor_ret)
        {
            LogErr("vendor Save err");
            return errcode::SYS_ERR;
        }
    }

    $ret  = $aisle_mgo->Save($aisle_entry);
    if (0 != $ret)
    {
        LogErr("aisle Save err");
        return errcode::SYS_ERR;
    }
    //恢复现有的商品库存<<<<<只有减库存的时候才存在
    $less_num   = $aisle_info->goods_num - $goods_num;
    $goods_info = $goods_mgo->GetVendorGoodsById($aisle_info->vendor_goods_id);
    $goods_entry->vendor_goods_id = $goods_info->vendor_goods_id;
    $goods_entry->goods_stock     = $goods_info->goods_stock + $less_num;
    $goods_mgo->Save($goods_entry);
    $resp = (object)[];

    return 0;
}

//完成补货
function SaveAisleRecord(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }


    $list              = json_decode($_['list']);
    $use_name          = $_['use_name'];
    $vendor_id         = $_['vendor_id'];
    if(count($list)<0)
    {
        LogDebug('list is empty');
        return errcode::PARAM_ERR;
    }
    $aisle_entry       = new VendorMgo\AisleEntry;
    $aisle_mgo         = new VendorMgo\Aisle;
    $record_entry      = new VendorMgo\VendorRecordEntry;
    $record_mgo        = new VendorMgo\VendorRecord;
    $vendor_mgo        = new VendorMgo\Vendor;

    $aisle_list  = [];
    foreach ($list as &$v)
    {
        $aisle_info  = $aisle_mgo->QueryById($v->aisle_id);
        $aisle_entry->aisle_id        = $v->aisle_id;
        $aisle_entry->vendor_goods_id = $v->vendor_goods_id;
        $aisle_entry->goods_num       = $v->goods_num;
        $ret  =  $aisle_mgo->Save($aisle_entry);
        if (0 != $ret)
        {
            LogErr("aisle Save err");
            return errcode::SYS_ERR;
        }
        $aisle      = (object)[];
        $aisle->aisle_id        = $v->aisle_id;
        $aisle->goods_num       = $v->goods_num - $aisle_info->goods_num;
        $aisle->vendor_goods_id = $v->vendor_goods_id;
        array_push($aisle_list,$aisle);
    }
    $vendor_info = $vendor_mgo->QueryById($vendor_id);
   //保存一次补货记录
    $record_entry->record_id       = DaoRedis\Id::GenVendorRecordId();
    $record_entry->aisle_list      = $aisle_list;
    $record_entry->vendor_id       = $vendor_id;
    $record_entry->record_time     = time();
    $record_entry->use_name        = $use_name;
    $record_entry->shop_id         = $vendor_info->shop_id;
    $record_entry->delete          = 0;
    $ret_one  = $record_mgo->Save($record_entry);
    if (0 != $ret_one)
    {
        LogErr("record Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[];

    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_['save_aisle']))
{
    $ret = SaveAisle($resp);
}else if(isset($_['aisle_record']))
{
    $ret = SaveAisleRecord($resp);
}else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

