<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 商品信息保存类
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_spec.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_praise.php");
use \Pub\Mongodb as Mgo;

//保存商品信息
function SaveGoodsInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $goods_id        = $_["goods_id"];
    $edit_type       = $_["edit_type"];
    $goods_name      = $_["goods_name"];
    $goods_type      = $_["goods_type"];
    $sale_off        = $_["sale_off"];
    $is_draft        = $_["is_draft"];
    $category_id     = json_decode($_["category_id"]);
    $goods_img_list  = json_decode($_["goods_img_list"]);
    $spec_type       = $_["spec_type"];
    $sale_num        = $_["sale_num"];
    $sale_off_way    = $_["sale_off_way"];
    $goods_sale_time = $_["goods_sale_time"];
    $freight_type    = $_["freight_type"];
    $freight         = $_["freight"];
    $invoice         = $_["invoice"];
    $goods_describe  = json_decode($_["goods_describe"]);
    $desc_img_pc     = json_decode($_["desc_img_pc"]);
    $desc_img_phone  = json_decode($_["desc_img_phone"]);
    $spec_price      = json_decode($_["spec_price"]);
    $cate_type       = $_['cate_type'];

    if (!$goods_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    if($edit_type)//新增
    {
        $ctime = time();
    }
    $spec = new Mgo\GoodsSpecEntry;
    if($spec_price)
    {
        $spec_id_list = [];
        foreach ($spec_price as $item)
        {
            unset($entry_time);
            $spec_id = $item->spec_id;
            if(!$spec_id)
            {
                $spec_id    = \DaoRedis\Id::GenSpecId();
                $entry_time = time();
            }
            $spec->spec_id    = $spec_id;
            $spec->spec_name  = $item->spec_name;
            $spec->package    = $item->package;
            $spec->goods_id   = $goods_id;
            $spec->price      = $item->price;
            $spec->stock_num  = $item->stock_num;
            $spec->sale_price = $item->sale_price;
            $spec->sale_time  = $item->sale_time;
            $spec->time       = $item->time;
            $spec->time_unit  = $item->time_unit;
            $spec->terminal   = $item->terminal;
            $spec->ctime      = $item->entry_time;

            $ret = Mgo\GoodsSpec::My()->Save($spec);
            if (0 != $ret) {
                LogErr("spec_id:[$spec_id] Save err");
                return errcode::SYS_ERR;
            }
            array_push($spec_id_list, $spec_id);
        }
    }

    $entry = new Mgo\GoodsEntry;

    $entry->goods_id        = $goods_id;
    $entry->goods_name      = $goods_name;
    $entry->goods_type      = $goods_type;
    $entry->sale_off        = $sale_off;
    $entry->is_draft        = $is_draft;
    $entry->category_id     = $category_id;
    $entry->goods_img_list  = $goods_img_list;
    $entry->spec_type       = $spec_type;
    $entry->sale_num        = $sale_num;
    $entry->sale_off_way    = $sale_off_way;
    $entry->goods_sale_time = $goods_sale_time;
    $entry->freight_type    = $freight_type;
    $entry->freight         = $freight;
    $entry->invoice         = $invoice;
    $entry->goods_describe  = $goods_describe;
    $entry->desc_img_pc     = $desc_img_pc;
    $entry->desc_img_phone  = $desc_img_phone;
    $entry->spec_id_list    = $spec_id_list;
    $entry->cate_type       = $cate_type;
    $entry->ctime           = $ctime;
    $ret = Mgo\Goods::My()->Save($entry);

    if (0 != $ret) {
        LogErr("goods_id:[$goods_id] Save err");
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

    $mongodb = new Mgo\Goods;
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
// 批量上、下架/回收站、还原操作
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
    $type      = $_['type'];

    $mongodb = new Mgo\Goods;
    $ret     = $mongodb->SetSale($goods_id_list, $sale_off, $type);

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
    $mongodb = new Mgo\Goods;
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
    $entry = new Mgo\GoodsEntry;
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

// 商品评价
function SetPraise(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $goods_id  = $_['goods_id'];
    $shop_id   = $_['shop_id'];
    $agent_id  = $_['agent_id'];
    $is_praise = $_['is_praise'];
    $type      = $_['type'];
    if(!$goods_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    if(!$shop_id && !$agent_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $entry = new Mgo\GoodsPraiseEntry;
    $entry->goods_id  = $goods_id;
    $entry->shop_id   = $shop_id;
    $entry->agent_id  = $agent_id;
    $entry->is_praise = $is_praise;
    $entry->type      = $type;
    $ret = Mgo\GoodsPraise::My()->Save($entry);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("set Praise: ok: ");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_['save_goods']))
{
    $ret = SaveGoodsInfo($resp);
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
elseif(isset($_['praise_save']))
{
    $ret = SetPraise($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
    'data' => $resp
);

if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}
?><?php /******************************以下为html代码******************************/?>

