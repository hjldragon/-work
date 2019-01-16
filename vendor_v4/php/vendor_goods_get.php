<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取商品信息bn
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods_category.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;


function GetVendorGoods(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $vendor_id     = $_['shop_id'];

    $vendor_mgo    = new VendorMgo\Vendor;
    $goods_mgo     = new VendorMgo\VendorGoods;
    $cate_mgo      = new VendorMgo\VendorGoodsCategory;
    $aisle_mgo     = new VendorMgo\Aisle;
    $vendor_info   = $vendor_mgo->QueryById($vendor_id);
    //获取店铺信息
    $shopinfo                      = [];
    $shopinfo['shop_id']           = $vendor_info->vendor_id;
    $shopinfo['shop_name']         = $vendor_info->vendor_name;

    $cate_list  =  $cate_mgo->GetListByShop($vendor_info->shop_id);
    $aisle_list =  $aisle_mgo->ListByVendorId($vendor_id);
    $item = [];
    foreach ($aisle_list as &$a)
    {
        if(!$a->vendor_goods_id)
        {
            continue;
        }
        if (!isset($item[$a->vendor_goods_id])) {
            $item[$a->vendor_goods_id] = $a;
        } else {
            $item[$a->vendor_goods_id]->goods_num += $a->goods_num;

        }
    }
    $new_aisle = array_values($item);
    $menuinfo  = [];
    foreach ($cate_list as $c)
    {
        $food_list = [];
        foreach ($new_aisle as $v)
        {
            $goods_info = $goods_mgo->GetVendorGoodsById($v->vendor_goods_id);
            $cate_id    = end($goods_info->category_id);
            if($cate_id == $c->category_id){
                $food['food_id']       = $goods_info->vendor_goods_id;
                $food['food_name']     = $goods_info->vendor_goods_name;
                $food['food_img_list'] = $goods_info->goods_img_list;
                $food['food_price']    = $goods_info->goods_price;
                $food['spec']          = $goods_info->goods_spec;
                $food['food_num']      = $v->goods_num;
                array_push($food_list,$food);
            }
        }
        if(count($food_list) > 0)
        {
            array_push($menuinfo,[
                'category_id'   => $c->category_id,
                'category_name' => $c->category_name,
                'food_list'    => $food_list
            ]);
        }

    }

    $resp = (object)array(
        'shopinfo'   => $shopinfo,
        'menuinfo'   => $menuinfo,
    );
    LogInfo("--ok--");
    return 0;
}



$ret = -1;
$resp = (object)array();
if(isset($_["get_home_data"]))
{
    $ret = GetVendorGoods($resp);
}else{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

\Pub\PageUtil::HtmlOut($ret, $resp);