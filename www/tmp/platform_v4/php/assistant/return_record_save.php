<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_return_record.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

function SavePhotoReturnRecord(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }



    $vendor_goods_id   = $_['vendor_goods_id'];
    $shop_id           = $_['shop_id'];
    $goods_num         = $_['goods_num'];

    $return_id = DaoRedis\Id::GenReturnRecordId();
    if(!$vendor_goods_id && !$shop_id)
    {
        LogDebug('no goods id or shop id');
        return errcode::PARAM_ERR;
    }
    if(!$goods_num)
    {
        $goods_num = 1;//<<<<<<<现在没有功能处可以输入商品数量
    }
    $return_entry = new VendorMgo\ReturnRecordEntry;
    $return_mgo   = new VendorMgo\ReturnRecord;
    $goods_mgo    = new VendorMgo\VendorGoods;

    $goods_info   = $goods_mgo->GetVendorGoodsById($vendor_goods_id);

    $return_entry->return_id       = $return_id;
    $return_entry->shop_id         = $shop_id;
    $return_entry->vendor_goods_id = $vendor_goods_id;
    $return_entry->goods_num       = $goods_num;
    $return_entry->category_id     = $goods_info->category_id;
    $return_entry->return_status   = ReturnStatus::RETURNING;
    $return_entry->delete          = 0;


    $ret  = $return_mgo->Save($return_entry);
    if (0 != $ret)
    {
        LogErr("return Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)[];

    return 0;
}

function SaveReturnRecord(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }



    $return_id         = $_['return_id'];
    $use_name          = $_['use_name'];

    if(!$return_id )
    {
        LogDebug('no return id');
        return errcode::PARAM_ERR;
    }

    $return_entry = new VendorMgo\ReturnRecordEntry;
    $return_mgo   = new VendorMgo\ReturnRecord;


    $return_entry->return_id       = $return_id;
    $return_entry->use_name        = $use_name;
    $return_entry->return_status   = ReturnStatus::RETURNED;
    $return_entry->return_time     = time();
    $return_entry->return_address  = '默认店铺';//<<<<<<<<<没有地址处理都是默认当前店铺

    $ret  = $return_mgo->Save($return_entry);
    if (0 != $ret)
    {
        LogErr("return Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)[];

    return 0;
}
$ret = -1;
$resp = (object)array();

if(isset($_["photo_return_save"]))
{
    $ret = SavePhotoReturnRecord($resp);
}else if(isset($_["return_save"]))
{
    $ret = SaveReturnRecord($resp);
}else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

