<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取商品信息
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_spec.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_category.php");
require_once("/www/public.sailing.com/php/mart/mgo_stat_goods_byday.php");
use \Pub\Mongodb as Mgo;

//取商品销量
function GetGoodsSoldNum($goods_id, $start=null, $end=null)
{
    $mgo_stat = new Mgo\StatGoods;
    if(!$start)
    {
        $start = 0;
    }
    if(!$end)
    {
        $end = date("Ymd");
    }
    $info = $mgo_stat->GetGoodsStatByTime($goods_id, $start, $end);
    return $info['all_sold_num']?:0;
}


function GetGoodsList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $category_id    = $_['category_id'];
    $page_size      = $_['page_size'];
    $page_no        = $_['page_no'];
    $sale_off       = $_['sale_off'];
    $goods_name     = $_['goods_name'];
    $is_draft       = $_['is_draft'];
    $is_recycle     = $_['is_recycle'];
    $sale_num_begin = $_['sale_num_begin'];
    $sale_num_end   = $_['sale_num_end'];
    $min_price      = $_['min_price'];
    $max_price      = $_['max_price'];
    $sortby         = $_['sortby']; //(排序1:价格,2:销量,3:库存)
    $sort           = $_['sort'];   //(1:正序,-1:倒序)
    if(!$page_size)
    {
       $page_size = 10000;//如果没有传默认10000条
    }
    if(!$page_no)
    {
       $page_no = 1; //第一页开始
    }
    if($min_price && !$max_price)
    {
        $max_price = 99999999;
    }
    if(!$min_price && $max_price)
    {
        $min_price = 0.1;
    }
    $mgo  = new Mgo\Goods;
    $cond = [
        'category_id' => $category_id,
        'sale_off'    => $sale_off,
        'goods_name'  => $goods_name,
        'is_draft'    => $is_draft,
        'is_recycle'  => $is_recycle
    ];

    $goods_list = $mgo->GetGoodsList($cond);
    $ago_spec = new Mgo\GoodsSpec;
    $ago_cate = new Mgo\GoodsCategory;
    $time = time();
    //$list = (object)array();
    foreach($goods_list as &$v)
    {
        if($v->sale_off == SaleOffGoods::NO && $v->sale_off_way == SaleGoodsSet::SETTIME)//如果商品为下架状态,商品上架方式为自定义
        {
            $goods_sale_time = $v->goods_sale_time;

            if($goods_sale_time <= $time)
            {
               GoodsSaleOff($v->goods_id);//商品变上架
            }

        }
        $v->goods_price = array();
        $min_pri = 999999;//最低价格
        $stock_num = 0;//库存
        $price_search = 0;
        // 规格

        if($v->spec_id_list)
        {
            foreach ($v->spec_id_list as $value)
            {
                $spec = null;
                $spec = $ago_spec->GetSpecById($value);
                if($spec)
                {
                    if($min_price && $max_price)
                    {
                        if($spec->price >= $min_price && $spec->price <= $max_price)
                        {
                            $price_search = 1;
                        }

                    }
                    array_push($v->goods_price, $spec);
                    if($spec->price < $min_pri)
                    {
                        $min_pri = $spec->price;
                    }
                    $stock_num += $spec->stock_num;
                }
            }
        }
        if($min_price && $max_price && $price_search == 0)
        {
            continue;
        }

        $v->min_price = $min_pri;
        $v->stock_num = $stock_num;
        $cate_id  = end($v->category_id);
        $cateinfo = $ago_cate->GetCateById($cate_id);
        $v->category_name = $cateinfo->category_name;
        $v->sale_num = GetGoodsSoldNum($v->goods_id, $sale_num_begin, $sale_num_end); //销量
        $list[] = $v;
    }
    if(1 == $sortby)//价格排序
    {
        //$sortby = "min_price";
        if(1 == $sort)
        {
            usort($list, function($a, $b){
                return ($a->min_price - $b->min_price);
            });
        }
        else
        {
            usort($list, function($a, $b){
                return ($b->min_price - $a->min_price);
            });
        }
    }
    if(2 == $sortby)//销量排序
    {
        //$sortby = "sale_num";
        if(1 == $sort)
        {
            usort($list, function($a, $b){
                return ($a->sale_num - $b->sale_num);
            });
        }
        else
        {
            usort($list, function($a, $b){
                return ($b->sale_num - $a->sale_num);
            });
        }
    }
    if(3 == $sortby)//库存排序
    {
        //$sortby = "stock_num";
        if(1 == $sort)
        {
            usort($list, function($a, $b){
                return ($a->stock_num - $b->stock_num);
            });
        }
        else
        {
            usort($list, function($a, $b){
                return ($b->stock_num - $a->stock_num);
            });
        }
    }
    $total = count($list);
    $list = array_slice($list, ($page_no-1)*$page_size, $page_size);
    $resp = (object)array(
        'goods_list' => $list,
        'total'      => $total
    );

    LogInfo("--ok--");
    return 0;
}

function GoodsSaleOff($goods_id)
{
    $goods_id_list[] = $goods_id;
    $sale_off  = 0;

    $mongodb = new Mgo\Goods;
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
    $goods_id = $_['goods_id'];
    if(!$goods_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo = new Mgo\Goods;

    $info = $mgo->GetGoodsById($goods_id);
    $ago_spec = new Mgo\GoodsSpec;
    $ago_cate = new Mgo\GoodsCategory;

    $info->goods_price = array();
    // 规格
    if($info->spec_id_list)
    {
        foreach ($info->spec_id_list as $value)
        {
            $spec = null;
            $spec = $ago_spec->GetSpecById($value);
            if($spec)
            {
                array_push($info->goods_price, $spec);
            }
        }
    }

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

$ret = -1;
$resp = (object)array();
if(isset($_["get_goods_list"]))
{
    $ret = GetGoodsList($resp);
}elseif(isset($_["get_goods_info"]))
{
    $ret = GetGoodsInfo($resp);
}

$result = (object)array(
    'ret' => $ret,
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

