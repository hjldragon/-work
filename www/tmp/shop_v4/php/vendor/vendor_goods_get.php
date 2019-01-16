<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取商品信息bn
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods_category.inc");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;


function GetGoodsList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $category_id = $_['category_id'];
    $shop_id     = $_['shop_id'];
    $page_size   = $_['page_size'];
    $page_no     = $_['page_no'];
    $sale_off    = $_['sale_off'];
    $search      = $_['search'];
    $sortby      = $_['sortby']; //(排序1:价格,2:序号)
    $sort        = $_['sort'];   //(1:正序,-1:倒序)

    switch ($sortby) {
        case 1:
            $sort_by['goods_price'] = (int)$sort;
            break;
        case 2://反结账
            $sort_by['vendor_goods_id'] = (int)$sort;
            break;
        default:
            $sort_by = [];
            break;
    }
    $mgo  = new VendorMgo\VendorGoods;
    $cond = [
        'category_id' => $category_id,
        'sale_off'    => $sale_off,
        'search'      => $search,
        'shop_id'     => $shop_id
    ];
    $total = 0;
    $goods_list = $mgo->GetVendorGoodsList($cond, $sort_by, $page_size, $page_no, $total);
    $ago_cate = new VendorMgo\VendorGoodsCategory;
    $time = time();
    //$list = (object)array();
    foreach($goods_list as &$v)
    {
        $category_id  = end($v->category_id);
        $cateinfo = $ago_cate->GetCateById($category_id);
        $v->category_name = $cateinfo->category_name;
    }
    $resp = (object)array(
        'goods_list' => $goods_list,
        'total'      => $total
    );

    LogInfo("--ok--");
    return 0;
}

function GoodsSaleOff($goods_id)
{
    $goods_id_list[] = $goods_id;
    $sale_off  = 0;

    $mongodb = new VendorMgo\VendorGoods;
    $ret     = $mongodb->SetSale($goods_id_list, $sale_off);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    return 0;
}

function GetGoodsInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $vendor_goods_id = $_['vendor_goods_id'];
    if(!$vendor_goods_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo = new VendorMgo\VendorGoods;

    $info = $mgo->GetVendorGoodsById($vendor_goods_id);
    $ago_cate = new VendorMgo\VendorGoodsCategory;

    $info->category = array();
    if($info->category_id)
    {
        foreach ($info->category_id as $item)
        {
            $cate = null;
            $cate = $ago_cate->GetCateById($item);
            if($spec)
            {
                array_push($info->category, $cate);
            }
        }
    }

    $resp = (object)array(
        'info' => $info
    );

    LogInfo("--ok--");
    return 0;
}

function GetAisleGoodsList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_id     = $_['vendor_id'];
    $search        = $_['search'];
    if(!$vendor_id)
    {
        LogDebug('no vendor_id');
        return errcode::PARAM_ERR;
    }
    $vendor_mgo  = new VendorMgo\Vendor;
    $goods_mgo   = new VendorMgo\VendorGoods;
    $vendor_info = $vendor_mgo->QueryById($vendor_id);

    $cond =
        [
            'shop_id'     => $vendor_info->shop_id,
            'search'      => $search,
        ];
    $total      = 0;
    $goods_list = $goods_mgo->GetListTotal($cond, $total);
    $all        = [];

    foreach($goods_list as &$v)
    {
        $info['vendor_goods_id']   = $v->vendor_goods_id;
        $info['vendor_goods_name'] = $v->vendor_goods_name;
        $info['goods_spec']        = $v->goods_spec;
        $info['goods_img_list']    = $v->goods_img_list;
        array_push($all,$info);
    }
    $resp = (object)array(
        'goods_list' => $all,
    );

    LogInfo("--ok--");
    return 0;
}

//获取公众号的商品列表
function GetOAGoodsList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id     = $_['shop_id'];
    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::PARAM_ERR;
    }

    $mgo      = new VendorMgo\VendorGoods;
    $cate_mgo = new VendorMgo\VendorGoodsCategory;
    $all      = [];
    $cate_list  = $cate_mgo->GetListByShop($shop_id);

    $good_all = $mgo->GetListTotal(['shop_id'=>$shop_id]);
    $g_all = [];
    $num   = 0;
    foreach ($good_all as &$a)
    {
        $in['vendor_goods_id']   = $a->vendor_goods_id;
        $in['vendor_goods_name'] = $a->vendor_goods_name;
        $in['goods_spec']        = $a->goods_spec;
        $in['goods_stock']       = $a->goods_stock;
        $num  ++;
        array_push($g_all,$in);
    }
    array_push($all, [
        'category_id'   => "",
        'category_name' => '全部',
        'all_num'       => $num,
        'goods_list'    => $g_all
    ]);


    foreach ($cate_list as &$c)
    {
        if($c->parent_id != (string)0)
        {
            continue;
        }
        $goods_list = $mgo->GetByCate($shop_id, $c->category_id);
        $cate_goods = [];
        $all_num    = 0;
        foreach($goods_list as &$v)
        {
            $info['vendor_goods_id']   = $v->vendor_goods_id;
            $info['vendor_goods_name'] = $v->vendor_goods_name;
            $info['goods_spec']        = $v->goods_spec;
            $info['goods_stock']       = $v->goods_stock;
            $all_num ++;
            array_push($cate_goods,$info);
        }

        array_push($all, [
            'category_id'   => $c->category_id,
            'category_name' => $c->category_name,
            'all_num'       => $all_num,
            'goods_list'     => $cate_goods
        ]);
    }

    $resp = (object)array(
        'list' => $all,
    );

    LogInfo("--ok--");
    return 0;
}
//根据条形码获取商品信息
function GetGoodsByCode(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $goods_bar_code = $_['goods_bar_code'];
    $shop_id        = $_['shop_id'];
    if(!$goods_bar_code || !$shop_id)
    {
        LogErr("no goods_bar_code");
        return errcode::PARAM_ERR;
    }
    $mgo  = new VendorMgo\VendorGoods;
    $info = $mgo->GetVendorGoodsByCode($shop_id, $goods_bar_code);

    $resp = (object)array(
        'info' => $info
    );

    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_["get_goods_list"]))
{
    $ret = GetGoodsList($resp);
}elseif(isset($_["get_goods_info"]))
{
    $ret = GetGoodsInfo($resp);
}elseif(isset($_["get_aisle_goods"]))
{
    $ret = GetAisleGoodsList($resp);
}elseif(isset($_["get_oa_goods"]))
{
    $ret = GetOAGoodsList($resp);
}elseif(isset($_["get_goods_bycode"]))
{
    $ret = GetGoodsByCode($resp);
}else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);


