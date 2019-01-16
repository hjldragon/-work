<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_record.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

function GetVendorRecordList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id        = $_['shop_id'];

    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::PARAM_ERR;
    }
    $vendor_mgo       = new VendorMgo\Vendor;
    $record_mgo       = new VendorMgo\VendorRecord;
    $aisle_mgo        = new VendorMgo\Aisle;
    $goods_mgo        = new VendorMgo\VendorGoods;
    $vendor_list      = $vendor_mgo->GetListTotal(
        ['shop_id'=>$shop_id]
    );
    foreach ($vendor_list as &$v)
    {
        $id_list[] = $v->vendor_id;
    }
    $record_list = $record_mgo->GetListTotal(
        [
            'vendor_id_list'  => $id_list,
        ]
    );
   foreach ($record_list as &$r)
   {
        foreach ($r->aisle_list as &$rs)
        {
           $aisle_info      = $aisle_mgo->QueryById($rs->aisle_id);
           $rs->aisle_name  = $aisle_info->aisle_name;
           $goods_info      = $goods_mgo->GetVendorGoodsById($rs->vendor_goods_id);
           $rs->goods_name  = $goods_info->vendor_goods_name;
        }
   }

    $resp = (object)[
        'list'     => $record_list,

    ];

    LogInfo("--ok--");
    return 0;
}



$ret = -1;
$resp = (object)array();

if(isset($_["get_record_list"]))
{
    $ret = GetVendorRecordList($resp);
}else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

