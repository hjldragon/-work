<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取商品订单类别信息
 */
require_once("current_dir_env.php");
require_once("const.php");
require_once("/www/public.sailing.com/php/mart/mgo_freight.php");
require_once("/www/public.sailing.com/php/mgo_platform.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_spec.php");
require_once("/www/public.sailing.com/php/mart/KdApiSearchDemo.php");
require_once("/www/public.sailing.com/php/page_util.php");
use \Pub\Mongodb as Mgo;
//15天自动收货
\Pub\PageUtil::CheckGoodsOrder();
function GetOrderFreight(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $city = $_['city'];
    if(!$city)
    {
        LogErr("city err");
        return errcode::PARAM_ERR;
    }
    $goods_list = json_decode($_["goods_list"]);
    if(!$goods_list)
    {
        LogErr("goodsinfo err");
        return errcode::PARAM_ERR;
    }
    $platform_id = PlatformID::ID;
    $platform = new Mgo\Platform;
    $platform_info = $platform->GetPlatformById($platform_id);
    $freight = new Mgo\Freight;
    $freight_info = $freight->GetFreightByCity($platform_id, $city);
    if($freight_info->freight_id)
    {
        $first_fee    = $freight_info->first_fee;
        $add_fee      = $freight_info->add_fee;
        $first_weight = $freight_info->first_weight;
        $add_weight   = $freight_info->add_weight;
    }
    else
    {
        $first_fee    = $platform_info->first_fee;
        $add_fee      = $platform_info->add_fee;
        $first_weight = $platform_info->first_weight;
        $add_weight   = $platform_info->add_weight;
    }
    $goods_freight = 0;
    $goods_weight = 0;
    $goods = new Mgo\Goods;
    foreach ($goods_list as $item)
    {
        $goodsinfo = $goods->GetGoodsById($item->goods_id);
        if($goodsinfo->goods_type == 2 || $goodsinfo->freight_type == 1)// 虚拟商品或免运费
        {
            continue;
        }
        if($goodsinfo->freight_type == 3)// 按件
        {
            $goods_freight += (float)$goodsinfo->freight * $item->goods_num;
        }
        if($goodsinfo->freight_type == 2)// 按重量
        {
            $goods_weight += $goodsinfo->freight*$item->goods_num;
        }
    }
    if($goods_weight > 0)
    {
        $weight = $goods_weight - $first_weight;
        if($weight > 0)
        {
            $goods_freight += round($weight/$add_weight)*$add_fee + $first_fee;
        }
        else
        {
            $goods_freight += $first_fee;
        }

    }


    $resp = (object)array(
        'goods_freight' => $goods_freight
    );
    LogInfo("--ok--");
    return 0;
}

function GetOrderList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $where['agent_id'] = $_['agent_id'];
    $where['shop_id']  = $_['shop_id'];
    $page_size         = $_['page_size'];
    $page_no           = $_['page_no'];

    if(!$where['shop_id'] && !$where['agent_id'])
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    // 订单状态
    if (isset($_['order_status_list'])) {
        $where['order_status_list']  = json_decode($_['order_status_list']);
    }
    $where['uesr_delete'] = 1;
    $total = 0;
    $mgo = new Mgo\GoodsOrder;
    $order_list = $mgo->GetGoodsOrderList(
        $where,
        ["order_time" => -1],
        $page_size,
        $page_no,
        $total
    );
    $goods_ago = new Mgo\Goods;
    $list_all = [];
    foreach ($order_list as $key => &$value) {

        $goods_list_all = [];
        $goodslist      = [];
        $list           = [];
        $goods_img      = null;
        foreach ($value->goods_list as $item)
        {
            $goods_list_all['goods_id']  = $item->goods_id;
            $goods = $goods_ago->GetGoodsById($item->goods_id);
            $goods_list_all['goods_img'] = $goods->goods_img_list;
            if(!$goods_img)
            {
                $goods_img = $goods->goods_img_list;
            }
            $goods_list_all['goods_name'] = $item->goods_name;
            $goods_list_all['goods_num']  = $item->goods_num;
            $goods_list_all['spec_id']    = $item->spec_id;
            if($value->agent_id && $value->pay_way == GoodsOrderPayWay::BALANCE)
            {
                $goods_list_all['goods_price']     = $item->rebates_price;
                $goods_list_all['goods_price_sum'] = $item->rebates_price_sum;
            }
            else
            {
                $goods_list_all['goods_price']     = $item->goods_price;
                $goods_list_all['goods_price_sum'] = $item->goods_price_sum;

            }
            array_push($goodslist, $goods_list_all);
        }

        $list['goods_price_all'] = $value->order_fee;
        $list['goods_order_id']  = $value->goods_order_id;
        $list['goods_list']      = $goodslist;
        $list['goods_num_all']   = $value->goods_num_all;
        $list['order_time']      = $value->order_time;
        $list['goods_img']       = $goods_img;
        $list['order_status']    = $value->order_status;
        $list['invoice_status']  = $value->invoice_status;
        $list['is_urge']         = (int)$value->is_urge;
        $list['no_deliver']      = (int)$value->no_deliver;
        array_push($list_all, $list);

    }

    $resp = (object)array(
        'list'  => $list_all,
        'total' => $total
    );
    LogInfo("--ok--");
    return 0;
}

function GetOrderInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $goods_order_id  = $_['goods_order_id'];
    $mgo      = new Mgo\GoodsOrder;
    $info     = $mgo->GetGoodsOrderById($goods_order_id);
    $goods    = new Mgo\Goods;
    $ago_spec = new Mgo\GoodsSpec;
    foreach ($info->goods_list as &$v) {
        $goods_info   = $goods->GetGoodsById($v->goods_id);
        $v->goods_img = $goods_info->goods_img_list;
        $v->spec_type = $goods_info->spec_type;
        if(!$goods_img)
        {
            $goods_img = $goods_info->goods_img_list;
        }
        if($info->agent_id && $info->pay_way == GoodsOrderPayWay::BALANCE)
        {
            $v->goods_price     = $v->rebates_price;
            $v->goods_price_sum = $v->rebates_price_sum;
        }
        $spec = $ago_spec->GetSpecById($v->spec_id);
        $v->time_unit = $spec->time_unit;
        $v->terminal  = $spec->terminal;
        $v->time      = $spec->time;
    }

    $info->goods_img  = $goods_img;
    $resp = (object)array(
        'info' => $info
    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetExpressInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $goods_order_id  = $_['goods_order_id'];
    $mgo      = new Mgo\GoodsOrder;
    $info     = $mgo->GetGoodsOrderById($goods_order_id);
    if(!$info->express_company_id || !$info->express_num)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $exinfo = \Cache\ExpressCompany::Get($info->express_company_id);
    if(!$exinfo->express_company_name || !$exinfo->express_company_code)
    {
        LogErr("express_company_id err");
        return errcode::EXPRESS_COMPANY_ERR;
    }
    $logisticResult=getOrderTracesByJson($exinfo->express_company_code,$info->express_num);
    $info = json_decode($logisticResult);
    $info->express_company_logo  = $exinfo->express_company_logo;
    $info->express_company_phone = $exinfo->express_company_phone;
    $info->express_company_name  = $exinfo->express_company_name;
    $resp = (object)array(
        'info' => $info
    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();

if(isset($_["order_freight"]))
{
    $ret = GetOrderFreight($resp);
}
elseif(isset($_["get_orderlist"]))
{
    $ret = GetOrderList($resp);
}
elseif(isset($_["get_orderinfo"]))
{
    $ret = GetOrderInfo($resp);
}
elseif(isset($_["get_express"]))
{
    $ret = GetExpressInfo($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
//LogDebug($html);
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
