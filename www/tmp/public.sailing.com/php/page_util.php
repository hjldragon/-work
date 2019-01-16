<?php
/*
 * 关于页面类操作工具代码
 * [rockyshi 2014-08-20]
 *
 */
namespace Pub;
require_once("/www/public.sailing.com/php/errcode.php");
require_once("/www/public.sailing.com/php/mart/mgo_stat_goods_byday.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_spec.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
require_once("mgo_resources.php");
require_once("redis_id.php");
use \Pub\Mongodb as Mgo;

class PageUtil{

// 输出到前端（默认输出后中止运行）
static function HtmlOut($ret, $data, $opt=[])
{
    $exit = true;
    if(isset($opt['exit']))
    {
        $exit = $opt['exit'];
    }

    $html_out_callback = null;
    if(isset($opt['html_out_callback']))
    {
        $html_out_callback = $opt['html_out_callback'];
    }
    else if(isset($GLOBALS['html_out_callback']))
    {
        $html_out_callback = $GLOBALS['html_out_callback'];
    }

    $crypt = "";
    if(isset($opt['crypt']))
    {
        $crypt = $opt['crypt'];
    }

    $out = [
        "ret" => $ret,
        "msg" => \errcode::toString($ret),
        // "data" => $data,
        // 'crypt' => 1, // 是加密数据标记
        // 'data'  => PageUtil::EncRespData(json_encode($resp))
    ];
    if(1 == $crypt)
    {
        $out['crypt'] = 1;
        $out['data'] = PageUtil::EncRespData(json_encode($resp));
    }
    else
    {
        $out['data'] = $data;
    }
    // 直接到回调中，由回调处理；
    if(is_callable($html_out_callback))
    {
        $html_out_callback($ret, $data, $opt);
        return;
    }
    // 直接输出
    echo json_encode($out);
    if($exit)
    {
        exit(0);
    }
    return $out;
}

//检查商品库存够不够（有不不够的商品时，返回其商品信息，满足要求时返回null）
static function CheckGoodsStockNum($need_goods_list)
{
    $spec_id_list = [];
    $goodsinfo = [];
    foreach ($need_goods_list as $id)
    {
        $spec_id_list[] = $id->spec_id;
    }

    if(count($spec_id_list)==0)
    {
        return $goodsinfo;
    }

    $spec_mgo  = new Mgo\GoodsSpec;
    $list = $spec_mgo->GetSpecList(
        [
            'spec_id_list' => $spec_id_list,
        ]
    );
    $id2stock_num = [];
    foreach($list as $i => $v)
    {
        $id2stock_num[$v->spec_id] = (int)$v->stock_num;
    }

    foreach($need_goods_list as $i => $item)
    {
        $stock_num = (int)$id2stock_num[$item->spec_id];

        if($item->goods_num > $stock_num)
        {
            //如果库存不足计算出菜品中限量的剩余的库存
            foreach($list as  &$v)
            {
                if($v->spec_id == $item->spec_id)
                {
                    $goods = \Cache\Goods::Get($item->goods_id);
                    $v->goods_name = $goods->goods_name;
                    array_push($goodsinfo, $v);
                }
            }
        }
    }
    return $goodsinfo;
}

//检查商品是否下架
static function CheckGoodsSaleOff($need_goods_list)
{
    $goodsinfo = [];
    $goods = new Mgo\Goods;
    foreach ($need_goods_list as $id)
    {
        $data = (object)array();
        $info = $goods->GetGoodsById($id->goods_id);
        if($info->sale_off == 1)//如果商品为下架状态
        {
            $goods_sale_time = $info->goods_sale_time;
            if($info->sale_off_way == 1)//商品上架方式为自定义
            {
                if($goods_sale_time > $time)
                {
                    $data->goods_id = $info->goods_id;
                    $data->goods_name = $info->goods_name;
                }
            }
            else
            {
                $data->goods_id = $info->goods_id;
                $data->goods_name = $info->goods_name;
            }
        }

        if($data->goods_id)
        {
            array_push($goodsinfo, $data);
        }
    }
    return $goodsinfo;
}

// 增加商品售出数、减库存级虚拟商品处理
static function UpdateGoodsDauSoldNum($goods_order_id)
{
    LogDebug($goods_order_id);
    $orderinfo = \Cache\GoodsOrder::Get($goods_order_id);
    $mgo_stat = new Mgo\StatGoods;
    $mgo_spec = new Mgo\GoodsSpec;
    $mgo_res  = new Mgo\Resources;
    $day = date("Ymd");
    $time = time();
    foreach($orderinfo->goods_list as $i => $goods)
    {
        $goodsinfo = null;
        $specinfo  = null;
        $mgo_stat->SellNumAdd($goods->goods_id, $day, $goods->goods_num);
        $goodsinfo = \Cache\Goods::Get($goods->goods_id);
        $num = 0;
        $num = 0 - $goods->goods_num;
        $mgo_spec->StockNumDec($goods->spec_id, $num);
        if($goodsinfo->spec_type == 4 && $orderinfo->shop_id)
        {
            $specinfo = $mgo_spec->GetSpecById($goods->spec_id);
            switch ($specinfo->time_unit) {//时长单位(1.日,2.月,3.季,4.年)
                case 1:
                    $spec_time = 60*60*24;
                    $time_unit = '天';
                    break;
                case 2:
                    $spec_time = 60*60*24*30;
                    $time_unit = '月';
                    break;
                case 3:
                    $spec_time = 60*60*24*30*3;
                    $time_unit = '季';
                    break;
                case 4:
                    $spec_time = 60*60*24*30*12;
                    $time_unit = '年';
                    break;
                default:
                    LogErr($goods->goods_id."specinfo time_unit err");
                    return errcode::SYS_ERR;
                    break;
            }
            $time_long = $specinfo->time*$goods->goods_num;
            $spec_times = $specinfo->time * $spec_time;
            $res_entry = new Mgo\ResourcesEntry;
            $res_entry->resources_id     = \DaoRedis\Id::GenResourcesId();
            $res_entry->shop_id          = $orderinfo->shop_id;
            $res_entry->resources_type   = $specinfo->terminal;
            $res_entry->ctime            = $time;
            $res_entry->valid_begin_time = $time;
            $res_entry->valid_end_time   = $spec_times*$goods->goods_num + $time;
            $res_entry->time_long        = $time_long.$time_unit;
            $res_entry->last_use_time    = 0;
            LogDebug($res_entry);
            $ret = $mgo_res->Save($res_entry);
            if(0 != $ret)
            {
                LogErr("resources save err");
                return errcode::SYS_ERR;
            }
        }

    }
}

static function GetAgentRebates($agent_id)
{
    $agent_mgo     = new \DaoMongodb\Agent;
    $agent_cfg_mgo = new \Pub\Mongodb\AgentCfg;
    $city_mgo      = new \Pub\Mongodb\City;
    $agent_info    = $agent_mgo->QueryById($agent_id);
    $agent_level   = $agent_info->agent_level;
    $agent_cfg     = $agent_cfg_mgo->GetListCityLevel($agent_info->agent_type);

    $rebates       = [];
    if($agent_cfg)
    {
        foreach ($agent_cfg as $key => $value)
        {
            $rebates[$value->agent_level]['software'] = $value->software_rebates;
            $rebates[$value->agent_level]['hardware'] = $value->hardware_rebates;
            $rebates[$value->agent_level]['supplies'] = $value->supplies_rebates;
        }
        $rebates['agent_level'] = $agent_level;
    }
    return $rebates;
}


static function GetOrderPrice($goods_list, $agent_id=null, &$info=[])
{
    if($agent_id)
    {
        $rebates = self::GetAgentRebates($agent_id);
        $agent_level = $rebates['agent_level'];
    }
    $goods_mgo = new Mgo\Goods;
    $spec_mgo  = new Mgo\GoodsSpec;
    $time = time();
    $order_goods_list  = [];
    $goods_num_all     = 0;// 商品总数量
    $goods_price_all   = 0;// 商品总价格
    $rebates_price_all = 0;// 商品折扣总价格
    $order_rebates     = 1;// 折扣
    $spec_change       = 0;// 判断商品规格是否改变(如有改变，报错并返回改变的商品信息)
    foreach ($goods_list as $item)
    {
        $goods = (object)array();
        $goods_num_all += $item->goods_num;
        $goodsinfo = $goods_mgo->GetGoodsById($item->goods_id);
        if(!in_array($item->spec_id, $goodsinfo->spec_id_list))
        {
            $spec_change = 1;
            $item->goods_name = $goodsinfo->goods_name;
            array_push($info, $item);
        }
        $spec = $spec_mgo->GetSpecById($item->spec_id);
        if($item->spec_name != $spec->spec_name || $item->package != $spec->package || $item->time != $spec->time || $item->time_unit != $spec->time_unit || $item->terminal != $spec->terminal)
        {
            $spec_change = 1;
            $item->goods_name = $goodsinfo->goods_name;
            array_push($info, $item);
        }
        if($spec_change == 1)
        {
            continue;
        }

        $goods->goods_id    = $item->goods_id;
        $goods->goods_name  = $goodsinfo->goods_name;
        $goods->goods_num   = $item->goods_num;
        $goods->spec_id     = $item->spec_id;
        $goods->goods_price = $spec->price;//商品原价
        $goods->spec_name   = $spec->spec_name;
        $goods->package     = $spec->package;
        $goods->time        = $spec->time;
        $goods->time_unit   = $spec->time_unit;
        $goods->terminal    = $spec->terminal;
        $goods->invoice     = $goodsinfo->invoice;
        $sale_time = $spec->sale_time;//商品促销时间
        if($sale_time)
        {
            if($sale_time[0] <= $time && $time <= $sale_time[1])
            {
                $goods->goods_price = $spec->sale_price;//商品促销价
            }
        }
        $goods->goods_price_sum = (float)$goods->goods_price*$goods->goods_num;//商品总价
        $goods->invoice_price   = $goods->goods_price_sum;
        $goods_price_all += $goods->goods_price_sum;
        if(count($rebates) > 0)
        {
            switch ($goodsinfo->cate_type) {
                case 1:
                    $goods_rebates = (float)$rebates[$agent_level]['hardware']/10;
                    break;
                case 2:
                    $goods_rebates  = (float)$rebates[$agent_level]['supplies']/10;
                    break;
                case 3:
                    $goods_rebates  = (float)$rebates[$agent_level]['software']/10;
                    break;
                default:
                    $goods_rebates  = 1;
                    break;
            }
            if($goods_rebates < $order_rebates)
            {
                $order_rebates = $goods_rebates;
            }
            $goods->rebates_price = round((float)$goods->goods_price*$goods_rebates,2);//商品折扣价
            $goods->rebates_price_sum = (float)$goods->rebates_price*$goods->goods_num;//商品折扣总价
            $goods->invoice_price   = $goods->rebates_price_sum;
            $rebates_price_all += $goods->rebates_price_sum;
        }
        array_push($order_goods_list, $goods);
    }
    if($spec_change == 1)
    {
        LogErr("goods spec change");
        return null;
    }
    $data['order_goods_list']  = $order_goods_list;
    $data['goods_num_all']     = $goods_num_all;
    $data['goods_price_all']   = $goods_price_all;
    $data['rebates_price_all'] = $rebates_price_all;
    $data['order_rebates']     = $order_rebates * 10;
    //LogDebug($data);
    return $data;
}

static function  CheckGoodsOrder()
{
    $mgo = new Mgo\GoodsOrder;
    $list = $mgo->GetList(['pay_time'=>time() - 15*24*60*60,'order_status'=>3]);
    $id_list = [];
    foreach ($list as $key => $value)
    {
        array_push($id_list, $value->goods_order_id);
    }
    LogDebug($id_list);
    if(count($id_list)>0)
    {
        $mgo->AutomaticCollectGoods($id_list);
    }
}
}
?>