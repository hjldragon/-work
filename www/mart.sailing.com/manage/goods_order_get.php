<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取商品订单类别信息
 */
require_once("current_dir_env.php");
require_once("const.php");
require_once("/www/public.sailing.com/php/mgo_platform.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_evaluation.php");
require_once("/www/public.sailing.com/php/mart/KdApiSearchDemo.php");
require_once("/www/public.sailing.com/php/page_util.php");
use \Pub\Mongodb as Mgo;
//15天自动收货
\Pub\PageUtil::CheckGoodsOrder();
function GetOrderList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_status_list  = json_decode($_['order_status_list']);
    $create_begin_time  = $_['create_begin_time'];
    $create_end_time    = $_['create_end_time'];
    $goods_order_from   = $_['goods_order_from'];
    $invoice_status     = $_['invoice_status'];
    $goods_order_id     = $_['goods_order_id'];
    $express_company_id = $_['express_company_id'];
    $deliver_begin_time = $_['deliver_begin_time'];
    $deliver_end_time   = $_['deliver_end_time'];
    $sortby             = $_['sortby'];
    $sort               = $_['sort'];
    $page_size          = $_['page_size'];
    $page_no            = $_['page_no'];

    if (!$create_begin_time && $create_end_time)
    {
        $create_begin_time = -28800;
    }
    if (!$create_end_time && $create_begin_time)
    {
        $create_end_time = 1922354460;
    }
    if (!$deliver_begin_time && $deliver_end_time)
    {
        $deliver_begin_time = -28800;
    }
    if (!$deliver_end_time && $deliver_begin_time)
    {
        $deliver_end_time = 1922354460;
    }
    if(!$sort)
    {
        $sort = -1;
    }
    switch ($sortby) {
        case 1:
            $sort_by['order_time'] = (int)$sort;
            break;
        case 2:
            $sort_by['order_time'] = (int)$sort;
            break;
        case 3:
            $sort_by['order_fee'] = (int)$sort;
            break;
        case 4:
            $sort_by['deliver_time'] = (int)$sort;
            break;
        default:
            $sort_by['order_time'] = -1;
            break;
    }
    $total = 0;
    $pirce_list = [];
    $mgo = new Mgo\GoodsOrder;
    $order_list = $mgo->GetGoodsOrderList(
        [
            'goods_order_id'     => $goods_order_id,
            'begin_time'         => $create_begin_time,
            'end_time'           => $create_end_time,
            'deliver_begin_time' => $deliver_begin_time,
            'deliver_end_time'   => $deliver_end_time,
            'order_status_list'  => $order_status_list,
            'invoice_status'     => $invoice_status,
            'express_company_id' => $express_company_id,
            'goods_order_from'   => $goods_order_from
        ],
        $sort_by,
        $page_size,
        $page_no,
        $total,
        $pirce_list
    );
    $order_price_total = round($pirce_list['all_order_fee'],2);
    //$ec_ago = new Mgo\ExpressCompany;

    foreach ($order_list as $key => &$value)
    {
        if($value->express_company_id)
        {
            $ec_info = \Cache\ExpressCompany::Get($value->express_company_id);
            $value->express_company_name = $ec_info->express_company_name;
        }
        if($value->agent_id)
        {
            $value->goods_order_from = 1;
        }
        else
        {
            $value->goods_order_from = 2;
        }
        if($value->pay_way != GoodsOrderPayWay::BALANCE)
        {
            $value->rebates = 0;
        }

    }

    $resp = (object)array(
        'list'  => $order_list,
        'total' => $total,
        'order_price_total'=>$order_price_total

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
        // $v->spec_type = $goods_info->spec_type;
        // if(!$goods_img)
        // {
        //     $goods_img = $goods_info->goods_img_list;
        // }
        // $spec = $ago_spec->GetSpecById($v->spec_id);
        // $v->time_unit = $spec->time_unit;
        // $v->terminal  = $spec->terminal;
        // $v->time      = $spec->time;
    }

    if($info->express_company_id)
    {
        $ec_info = \Cache\ExpressCompany::Get($info->express_company_id);
        $info->express_company_name  = $ec_info->express_company_name;
        $info->express_company_phone = $ec_info->express_company_phone;
    }
    if($info->agent_id)
    {
        $info->goods_order_from = 1;
    }
    else
    {
        $info->goods_order_from = 2;
    }

    $ge_mgo = new Mgo\GoodsEvaluation;
    $ge_info = $ge_mgo->GetEvaByOrderList($goods_order_id);
    if(count($ge_info)>0)
    {
        foreach ($ge_info as &$value)
        {
            //追评
            $data = $ge_mgo->GetGoodsEvaluationByToId($value->id);
            if($data->id)
            {
                $value->to_id      = $data->to_id;
                $value->to_content = $data->content;
                $value->to_time    = $data->ctime;
            }
            $goods = \Cache\Goods::Get($value->goods_id);
            $value->goods_name = $goods->goods_name;
        }
        $info->evaluate_list = $ge_info;
    }

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


if(isset($_["get_order_list"]))
{
    $ret = GetOrderList($resp);
}
elseif(isset($_["get_order_info"]))
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
