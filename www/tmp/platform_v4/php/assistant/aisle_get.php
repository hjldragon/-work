<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("mgo_shop.php");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

function GetAisleList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_id        = $_['vendor_id'];
    $normal           = $_['normal'];
    if(!$vendor_id)
    {
        LogDebug('no vendor_id');
        return errcode::PARAM_ERR;
    }

    $aisle_mgo       = new VendorMgo\Aisle;
    $vendor_goods    = new VendorMgo\VendorGoods;
    $total           = 0;//分页总数
    $list = $aisle_mgo->GetListTotal(
        [
             'vendor_id'       => $vendor_id,
        ],
        $total
    );
    $all = [];
    foreach ($list as &$v)
    {
        if($normal)
        {
            if(!$v->vendor_goods_id || $v->goods_num < 1)
            {
                continue;
            }
        }

        $goods_info = $vendor_goods->GetVendorGoodsById($v->vendor_goods_id);
        if($v->vendor_goods_id)
        {
            $info['is_set']    = 1;
        }else
        {
            $info['is_set']    = 0;
        }
            $info['aisle_id']       = $v->aisle_id;
            $info['aisle_name']     = $v->aisle_name;
            $info['goods_name']     = $goods_info->vendor_goods_name;
            $info['goods_spec']     = $goods_info->goods_spec;
            $info['goods_num']      = $v->goods_num;
            $info['aisle_capacity'] = $v->aisle_capacity;
            $info['goods_img']      = $goods_info->goods_img_list;
            $info['vendor_goods_id']= $goods_info->vendor_goods_id;
            $info['goods_stock']    = $goods_info->goods_stock;
            array_push($all,$info);



    }

    $resp = (object)[
        'aisle_list'  => $all,
    ];

    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();

if(isset($_["get_aisle_list"]))
{
    $ret = GetAisleList($resp);
}else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

