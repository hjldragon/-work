<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 菜单信息保存类
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("redis_id.php");
require_once("mgo_goods.php");
require_once("mgo_goods_category.php");

function SaveGoods(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $goods_id     = $_['goods_id'];
    $goods_name   = $_['goods_name'];
    $goods_price  = $_['goods_price'];
    $goods_num    = $_['goods_num'];
    $goods_img    = $_['goods_img'];
    $sale_off     = $_['sale_off'];
    $describe     = $_['describe'];
    $category_id  = $_['category_id'];

    $goods_cate  = new \DaoMongodb\GoodsCategory;
    $mongodb     = new \DaoMongodb\Goods;
    $entry       = new \DaoMongodb\GoodsEntry;
    if(!$goods_id)
    {
        if(!$goods_name ||!$goods_price || !$goods_img ||!$describe ||!$category_id)
        {
            LogErr("no some Required fields");
            return errcode::PARAM_ERR;
        }
        $goods_id = \DaoRedis\Id::GenGoodsId();
        $mongodb->GetGoodsMaxSort($category_id,$max);
        if(!$max)
        {
            $goods_sort = 1;
        }else{
            $goods_sort = $max["max_goods_sort"]+1;
        }
        if($category_id)
        {
            $cate_info = $goods_cate->GetCategoryById($category_id);
            if(!$cate_info->category_id)
            {
                LogErr("no goods_category");
                return errcode::NO_CATE;
            }
        }
    }

    if($sale_off == 0)
    {
        $sale_off = 0;
    }else{
        $sale_off = 1;
    }

    $entry->goods_id     = $goods_id;
    $entry->sale_off     = $sale_off;
    $entry->goods_name   = $goods_name;
    $entry->goods_price  = $goods_price;
    $entry->goods_num    = $goods_num;
    $entry->goods_img    = $goods_img;
    $entry->sale_off     = $sale_off;
    $entry->describe     = $describe;
    $entry->entry_time   = time();
    $entry->delete       = 0;
    $entry->category_id  = $category_id;
    $entry->goods_sort   = $goods_sort;

    $ret = $mongodb->Save($entry);

    if(0 != $ret)
    {
        LogErr("Save err");
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
    
    $mongodb = new \DaoMongodb\Goods;
    $ret = $mongodb->Delete($goods_id_list);
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
    $sale_off   = $_['sale_off'];

    $mongodb = new \DaoMongodb\Goods;
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
// 修改商品排序
function GoodsSort(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $goods_id     = $_['goods_id'];
    $goods_sort   = $_['goods_sort'];

    $mongodb     = new \DaoMongodb\Goods;
    $entry       = new \DaoMongodb\GoodsEntry;
    $goods_info  = $mongodb->GetGoodsinfoById($goods_id);
    if(!$goods_info->goods_id)
    {
        LogErr("no goods_id");
        return errcode::PARAM_ERR;
    }
    $sort        = $goods_info->goods_sort;
    $category_id = $goods_info->category_id;

    if($goods_sort > $sort)
    {
         $value    = -1;
         $min_sort = $sort;
         $max_sort = $goods_sort;
    }
    else
    {
        $value    = 1;
        $min_sort = $goods_sort;
        $max_sort = $sort;
    }
    $ret     = $mongodb->GoodsSortChange($category_id, $min_sort, $max_sort, $value);
    if(0 != $ret)
    {
        LogErr("Change err");
        return errcode::SYS_ERR;
    }
    $entry->goods_id    = $goods_id;
    $entry->goods_sort  = $goods_sort;

    $ret  = $mongodb->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['goods_save']))
{
    $ret = SaveGoods($resp);
}
elseif(isset($_['goods_del']))
{
    $ret = DeleteGoods($resp);
}
elseif(isset($_['goods_sale_off']))
{
    $ret = SetSaleOff($resp);
}
elseif(isset($_['goods_sort_change']))
{
    $ret = GoodsSort($resp);
}

else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
