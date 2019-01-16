<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取餐桌位置信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/mgo_platform.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_spec.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_category.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_praise.php");
require_once("/www/public.sailing.com/php/mart/mgo_stat_goods_byday.php");
require_once("/www/public.sailing.com/php/mgo_agent_cfg.php");
require_once("/www/public.sailing.com/php/mgo_city.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_evaluation.php");
require_once("/www/public.sailing.com/php/page_util.php");
require_once("mgo_agent.php");
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

function GetHome(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id = $_['agent_id'];
    $shop_id  = $_['shop_id'];
    $platform_id = PlatformID::ID;

    if($agent_id)
    {
        $rebates = \Pub\PageUtil::GetAgentRebates($agent_id);
        $agent_level = $rebates['agent_level'];
        $agentinfo = \Cache\Agent::Get($agent_id);
        unset($agentinfo->pay_password);
    }
    if($shop_id)
    {
        $shopinfo = \Cache\Shop::Get($shop_id);
    }
    $mgo = new Mgo\Platform;
    $platform = $mgo->GetPlatformById($platform_id);

    $mgo = new Mgo\Goods;

    $goods_list = $mgo->GetGoodsList();
    $ago_spec = new Mgo\GoodsSpec;
    $praise_mgo = new Mgo\GoodsPraise;
    // $ago_cate = new Mgo\GoodsCategory;
    // $cate = $ago_cate->GetList();
    $time = time();
    $end = date("Ymd",$time);
    $start = date("Ym01",strtotime($end));
    $list = array();
    $list[0]->category_name = "热卖商品";
    $list[1]->category_name = "硬件";
    $list[2]->category_name = "软件";
    $list[3]->category_name = "耗材";
    foreach($goods_list as &$v)
    {
        if($v->sale_off == SaleOffGoods::NO)//如果商品为下架状态
        {
            $goods_sale_time = $v->goods_sale_time;
            if($v->sale_off_way == SaleGoodsSet::SETTIME)//商品上架方式为自定义
            {
                if($goods_sale_time > $time)
                {
                    continue;
                }
                else
                {
                    GoodsSaleOff($v->goods_id);//商品变上架
                }
            }
            else
            {
                continue;
            }

        }

        $v->goods_price = array();
        $min_price = 999999;//最低价格
        $goods_rebates = 1;//折扣
        if(count($rebates) > 0)
        {
            switch ($v->cate_type) {
                case GoodsCateType::HARDWARE:
                    $goods_rebates = (float)$rebates[$agent_level]['hardware']/10;
                    $rebates_one   = (float)$rebates[1]['hardware']/10;
                    $rebates_two   = (float)$rebates[2]['hardware']/10;
                    $rebates_three = (float)$rebates[3]['hardware']/10;
                    break;
                case GoodsCateType::CONSUMABLES:
                    $goods_rebates  = (float)$rebates[$agent_level]['supplies']/10;
                    $rebates_one    = (float)$rebates[1]['supplies']/10;
                    $rebates_two    = (float)$rebates[2]['supplies']/10;
                    $rebates_three  = (float)$rebates[3]['supplies']/10;
                    break;
                case GoodsCateType::SOFTWARE:
                    $goods_rebates  = (float)$rebates[$agent_level]['software']/10;
                    $rebates_one    = (float)$rebates[1]['software']/10;
                    $rebates_two    = (float)$rebates[2]['software']/10;
                    $rebates_three  = (float)$rebates[3]['software']/10;
                    break;
                default:
                    break;
            }
        }

        // 规格
        if($v->spec_id_list)
        {
            foreach ($v->spec_id_list as $value)
            {
                $spec = null;
                $spec = $ago_spec->GetSpecById($value);
                if($spec)
                {
                    $sale_time = $spec->sale_time;
                    if($sale_time)
                    {
                        if($sale_time[0] > $time || $sale_time[1] < $time)
                        {
                            unset($spec->sale_price);
                        }
                    }

                    if($spec->price < $min_price)
                    {
                        $min_price  = $spec->price;
                        $sale_price = $spec->sale_price;
                    }
                    $spec->original_price = $spec->price;                //原价
                    $spec->price = round((float)$spec->price*$goods_rebates,2);   //折扣价
                    if(null != $sale_price)
                    {
                        $spec->original_sale_price = $spec->sale_price;               //促销价
                        $spec->sale_price = round((float)$spec->sale_price*$goods_rebates,2);  //促销折扣价
                    }
                    array_push($v->goods_price, $spec);
                }
            }
        }
        //全规格中最低价格
        $v->min_price = round((float)$min_price*$goods_rebates,2);
        if(null != $sale_price)
        {
            $v->sale_price  = round((float)$sale_price*$goods_rebates,2);
            $v->price_one   = round((float)$sale_price*$rebates_one,2);
            $v->price_two   = round((float)$sale_price*$rebates_two,2);
            $v->price_three = round((float)$sale_price*$rebates_three,2);
        }
        else
        {
            $v->price_one   = round((float)$min_price*$rebates_one,2);
            $v->price_two   = round((float)$min_price*$rebates_two,2);
            $v->price_three = round((float)$min_price*$rebates_three,2);
        }

        $v->month_sale_num = GetGoodsSoldNum($v->goods_id, $start, $end); //月销量
        $v->sale_num   = GetGoodsSoldNum($v->goods_id); //总销量
        $v->praise_num = $praise_mgo->GetGoodsAllCount($v->goods_id);//点赞数
        if($shop_id || $agent_id)
        {
            //是否点赞
            $praise = $praise_mgo->GetPraiseByCustomer($agent_id, $shop_id, $v->goods_id, PraiseType::PRAISE);
            if($praise->goods_id){
                $v->is_praise = $praise->is_praise;
            }else{
                $v->is_praise = 0;
            }
            //是否收藏
            $collect = $praise_mgo->GetPraiseByCustomer($agent_id, $shop_id, $v->goods_id, PraiseType::COLLECT);
            if($collect->goods_id){
                $v->is_collect = $collect->is_praise;
            }else{
                $v->is_collect = 0;
            }
        }
        if($v->is_hot == 1)//热卖商品
        {
            $list[0]->goods_list[] = $v;
        }
        if($v->cate_type == GoodsCateType::HARDWARE)//硬件
        {
            $list[1]->goods_list[] = $v;
        }
        elseif($v->cate_type == GoodsCateType::SOFTWARE)//软件
        {
            $list[2]->goods_list[] = $v;
        }
        elseif($v->cate_type == GoodsCateType::CONSUMABLES)//耗材
        {
            $list[3]->goods_list[] = $v;
        }
    }
    $resp = (object)array(
        'platform'  => $platform,
        'list'      => $list,
        'shopinfo'  => $shopinfo,
        'agentinfo' => $agentinfo
    );

    LogInfo("--ok--");
    return 0;
}

function GetPraiseGoods(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id = $_['agent_id'];
    $shop_id  = $_['shop_id'];
    if(!$agent_id && !$shop_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    //取出所收藏的商品id
    $praise_mgo = new Mgo\GoodsPraise;
    $praise_goods = $praise_mgo->GetPraiseList(
        [
            'shop_id'  => $shop_id,
            'agent_id' => $agent_id,
            'type'     => 2 // 收藏
        ]
    );
    $praise_goods_id = [];
    foreach ($praise_goods as $value)
    {
        if($value->goods_id)
        {
            array_push($praise_goods_id, $value->goods_id);
        }
    }
    //无收藏时
    if(count($praise_goods_id) <= 0)
    {
        $resp = (object)array(
            'list'   => []
        );
        LogInfo("--ok--");
        return 0;
    }


    $mgo = new Mgo\Goods;
    $goods_list = $mgo->GetGoodsList(['goods_id_list'=>$praise_goods_id]);
    $ago_spec = new Mgo\GoodsSpec;
    $praise_mgo = new Mgo\GoodsPraise;
    // $ago_cate = new Mgo\GoodsCategory;
    // $cate = $ago_cate->GetList();

    $time = time();
    $end = date("Ymd",$time);
    $start = date("Ym01",strtotime($end));
    $list = array();
    $eva_mgo  = new Mgo\GoodsEvaluation;
    foreach($goods_list as &$v)
    {
        if($v->sale_off == SaleOffGoods::NO)//如果商品为下架状态
        {
            $goods_sale_time = $v->goods_sale_time;
            if($v->sale_off_way == SaleGoodsSet::SETTIME)//商品上架方式为自定义
            {
                if($goods_sale_time > $time)
                {
                    continue;
                }
                else
                {
                    GoodsSaleOff($v->goods_id);//商品变上架
                }
            }
            else
            {
                continue;
            }

        }
        $v->goods_price = array();
        $min_price = 999999;//最低价格
        $goods_rebates = 1;//折扣
        if(count($rebates) > 0)
        {
            switch ($v->cate_type) {
                case GoodsCateType::HARDWARE:
                    $goods_rebates = (float)$rebates[$agent_level]['hardware']/10;
                    $rebates_one   = (float)$rebates[1]['hardware']/10;
                    $rebates_two   = (float)$rebates[2]['hardware']/10;
                    $rebates_three = (float)$rebates[3]['hardware']/10;
                    break;
                case GoodsCateType::CONSUMABLES:
                    $goods_rebates  = (float)$rebates[$agent_level]['supplies']/10;
                    $rebates_one    = (float)$rebates[1]['supplies']/10;
                    $rebates_two    = (float)$rebates[2]['supplies']/10;
                    $rebates_three  = (float)$rebates[3]['supplies']/10;
                    break;
                case GoodsCateType::SOFTWARE:
                    $goods_rebates  = (float)$rebates[$agent_level]['software']/10;
                    $rebates_one    = (float)$rebates[1]['software']/10;
                    $rebates_two    = (float)$rebates[2]['software']/10;
                    $rebates_three  = (float)$rebates[3]['software']/10;
                    break;
                default:
                    break;
            }
        }

        // 规格
        if($v->spec_id_list)
        {
            foreach ($v->spec_id_list as $value)
            {
                $spec = null;
                $spec = $ago_spec->GetSpecById($value);
                if($spec)
                {
                    $sale_time = $spec->sale_time;
                    if($sale_time)
                    {
                        if($sale_time[0] > $time || $sale_time[1] < $time)
                        {
                            unset($spec->sale_price);
                        }
                    }
                    //array_push($v->goods_price, $spec);
                    if($spec->price < $min_price)
                    {
                        $min_price  = $spec->price;
                        $sale_price = $spec->sale_price;
                    }
                    $spec->original_price = $spec->price;                //原价
                    $spec->price = round((float)$spec->price*$goods_rebates,2);   //折扣价
                    if(null != $sale_price)
                    {
                        $spec->original_sale_price = $spec->sale_price;               //促销价
                        $spec->sale_price = round((float)$spec->sale_price*$goods_rebates,2);  //促销折扣价
                    }
                    array_push($v->goods_price, $spec);
                }
            }
        }

        $v->min_price = round((float)$min_price*$goods_rebates,2);
        if(null != $sale_price)
        {
            $v->sale_price  = round((float)$sale_price*$goods_rebates,2);
            $v->price_one   = round((float)$sale_price*$rebates_one,2);
            $v->price_two   = round((float)$sale_price*$rebates_two,2);
            $v->price_three = round((float)$sale_price*$rebates_three,2);
        }
        else
        {
            $v->price_one   = round((float)$min_price*$rebates_one,2);
            $v->price_two   = round((float)$min_price*$rebates_two,2);
            $v->price_three = round((float)$min_price*$rebates_three,2);
        }

        $v->month_sale_num = GetGoodsSoldNum($v->goods_id, $start, $end); //月销量
        $v->praise_num = $praise_mgo->GetGoodsAllCount($v->goods_id);//点赞数
        //是否点赞
        $praise = $praise_mgo->GetPraiseByCustomer($agent_id, $shop_id, $v->goods_id, PraiseType::PRAISE);
        if($praise->goods_id){
            $v->is_praise = $praise->is_praise;
        }else{
            $v->is_praise = 0;
        }
        //是否收藏
        $collect = $praise_mgo->GetPraiseByCustomer($agent_id, $shop_id, $v->goods_id, PraiseType::COLLECT);
        if($collect->goods_id){
            $v->is_collect = $collect->is_praise;
        }else{
            $v->is_collect = 0;
        }
        $goodsrate = GetGoodsRate($v->goods_id);
        $v->good_star = $goodsrate['star'];//星数评分
        array_push($list, $v);
    }

      $resp = (object)array(
        'list'      => $list
    );
    LogInfo("--ok--");
    return 0;
}

//好评率
function GetGoodsRate($goods_id)
{
    $all_stat = [];
    $data = [];
    $mgo     = new Mgo\GoodsEvaluation;
    $all     = $mgo->GetGoodsAllCount($goods_id, null, $all_stat);
    $is_good = $mgo->GetGoodsAllCount($goods_id, 1);
    if($all > 0){
        $data['good']  = round($is_good / $all, 2);
        $star_num = $all_stat['all_star_num'];
        $data['star'] = round($star_num / $all, 2);
    }
    else
    {
        $data['good'] = 1;
        $data['star'] = 5;
    }
    return $data;
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
if(isset($_["get_goods_info"]))
{
    $ret = GetGoodsInfo($resp);
}elseif(isset($_["get_home"]))
{
    $ret = GetHome($resp);
}elseif (isset($_["get_praise_goods"]))
{
    $ret = GetPraiseGoods($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
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

