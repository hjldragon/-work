<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 商品信息保存类
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

//保存商品信息
function SaveVendorGoodsInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_goods_id   = $_["vendor_goods_id"];
    $vendor_goods_name = trim($_["vendor_goods_name"]);
    $sale_off          = $_["sale_off"];
    $goods_img_list    = json_decode($_["goods_img_list"]);
    $goods_spec        = $_["goods_spec"];
    $category_id       = json_decode($_["category_id"]);
    $from_company      = $_["from_company"];
    $goods_price       = $_["goods_price"];
    $goods_cost_price  = $_["goods_cost_price"];
    $goods_stock       = $_["goods_stock"];
    $goods_bar_code    = $_["goods_bar_code"];
    $shop_id           = $_["shop_id"];
    $is_weight         = $_["is_weight"];
    $goods_weight      = $_["goods_weight"];

    if(!$vendor_goods_id)//新增
    {
        if(!$vendor_goods_name || !$shop_id || !$category_id || !$goods_price || !$goods_cost_price || !$goods_stock || !$goods_spec)
        {
            LogErr("param err");
            return errcode::PARAM_ERR;
        }
        $ctime = time();
        $vendor_goods_id = \DaoRedis\Id::GenVendorGoodsId();
    }
    if(!$goods_stock)
    {
        $goods_stock = 0;
    }

    $entry = new VendorMgo\VendorGoodsEntry;

    $entry->vendor_goods_id   = $vendor_goods_id;
    $entry->vendor_goods_name = $vendor_goods_name;
    $entry->sale_off          = $sale_off;
    $entry->goods_img_list    = $goods_img_list;
    $entry->goods_spec        = $goods_spec;
    $entry->category_id       = $category_id;
    $entry->from_company      = $from_company;
    $entry->goods_price       = $goods_price;
    $entry->goods_cost_price  = $goods_cost_price;
    $entry->goods_stock       = $goods_stock;
    $entry->goods_bar_code    = $goods_bar_code;
    $entry->shop_id           = $shop_id;
    $entry->ctime             = $ctime;
    $entry->is_weight         = $is_weight;
    $entry->goods_weight      = $goods_weight;

    $ret = VendorMgo\VendorGoods::My()->Save($entry);

    if (0 != $ret) {
        LogErr("goods_id:[$vendor_goods_id] Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}
function DeleteGoods(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $goods_id_list = json_decode($_['goods_id_list']);

    $mongodb = new VendorMgo\VendorGoods;
    $ret = $mongodb->BatchDelete($goods_id_list);
    if(0 != $ret)
    {
        LogErr("Delete err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("delete ok");
    return 0;
}
// 批量上、下架操作
function SetSaleOff(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $goods_id_list = json_decode($_['goods_id_list']);
    $sale_off  = $_['sale_off'];

    $mongodb = new VendorMgo\VendorGoods;
    $ret     = $mongodb->SetSale($goods_id_list, $sale_off);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($ret);

    $resp = (object)array(
    );
    LogInfo("set sale_off:[{$sale_off}] ok: ");
    return 0;
}

// 设置商品热卖
function SetHot(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $goods_id = $_['goods_id'];
    $is_hot   = $_['is_hot'];
    if(!$goods_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mongodb = new VendorMgo\Goods;
    if(1 == $is_hot)
    {
        $total = 0;
        $cond = ["is_hot"=>1];
        $info = $mongodb->GetGoodsList($cond, $total);
        if($total >= 8)
        {
            LogErr("goods num:[{$total}]");
            return errcode::GOOD_HOT_TOPLIMIT;
        }
    }
    $entry = new VendorMgo\GoodsEntry;
    $entry->goods_id = $goods_id;
    $entry->is_hot   = $is_hot;
    $ret = $mongodb->Save($entry);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("set sale_off:[{$sale_off}] ok: ");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['vendor_goods_save']))
{
    $ret = SaveVendorGoodsInfo($resp);
}elseif(isset($_['goods_del']))
{
    $ret = DeleteGoods($resp);
}
elseif(isset($_['goods_sale_off']))
{
    $ret = SetSaleOff($resp);
}
elseif(isset($_['goods_set_hot']))
{
    $ret = SetHot($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);


