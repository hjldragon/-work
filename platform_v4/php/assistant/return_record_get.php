<?php
/*
 * [Rocky 2017-05-12 20:16:27]
 * 订单信息保存类
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_return_record.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

function GetReturnRecordList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id        = $_['shop_id'];
    $return_status  = $_['return_status'];
    $category_id    = $_['category_id'];

    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::PARAM_ERR;
    }
    $return_entry = new VendorMgo\ReturnRecordEntry;
    $return_mgo   = new VendorMgo\ReturnRecord;
    $goods_mgo    = new VendorMgo\VendorGoods;

    $return_list = $return_mgo->GetListTotal(
        [
            'shop_id'       => $shop_id,
            'return_status' => $return_status,
            'category_id'   => $category_id
        ]
    );
   foreach ($return_list as &$v)
   {
       $goods_info        = $goods_mgo->GetVendorGoodsById($v->vendor_goods_id);
       $v->goods_name     = $goods_info->vendor_goods_name;
       $v->goods_bar_code = $goods_info->goods_bar_code;
   }

    $resp = (object)[
        'list'  => $return_list,
    ];

    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['get_return_list']))
{
    $ret = GetReturnRecordList($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

