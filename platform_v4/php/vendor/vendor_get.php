<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_fault_deal.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_order.inc");
require_once("mgo_shop.php");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

function GetVendorList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $ownership        = $_['ownership'];
    $vendor_status    = $_['vendor_status'];
    $shop_id          = $_['shop_id'];
    $vendor_name      = $_['vendor_name'];
    $page_no          = $_['page_no'];
    $page_size        = $_['page_size'];
    if(!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if(!$page_no)
    {
        $page_no = 1; //第一页开始
    }


    $mgo       = new VendorMgo\Vendor;
    $fault     = new VendorMgo\Fault;
    $shop      = new  DaoMongodb\Shop;
    if($ownership)
    {
        $shop_all  = [];
        if($ownership == "赛领科技信息有限公司" || $ownership == "赛领科技")
        {
            $shop_id_seek = PlShopId::ID;
            array_push($shop_all,$shop_id_seek);
        }
        $shop_list = $shop->GetAllShopList(['shop_name'=>$ownership]);
        foreach ($shop_list as &$s)
        {
            array_push($shop_all,$s->shop_id);
        }
    }


    $total       = 0;//分页总数
    $vendor_list = $mgo->GetAllList(
        [
             'shop_id_list'    => $shop_all,
             'shop_id'         => $shop_id,
             'vendor_status'   => $vendor_status,
             'vendor_name'     => $vendor_name
        ],
        $page_size,
        $page_no,
        [],
        $total
    );
    foreach ($vendor_list as &$v)
    {
        $shop_info     = $shop->GetShopById($v->shop_id);
        $v->ownership  = $shop_info->shop_name;
    }

    if($vendor_status == VendorStatus::FAULT)
    {
        $num  = 0;
        foreach ($vendor_list as &$value)
        {
            $list             = $fault->GetAllList(['vendor_id'=>$value->vendor_id], [], $num);//默认排序故障最新时间
            $value->fault_num = $num;
            $value->fault_id  = $list[0]->fault_id;//最新故障时间的故障id
            $value->is_inform = $list[0]->is_inform;
            $shop_info        = $shop->GetShopById($v->shop_id);
            $value->ownership = $shop_info->shop_name;
        }
    }

    //列表顶部数据
    $vendor_date = [];
    $all_total   = 0;//<<<<设备总数
    $list = $mgo->GetAllList(
        [
        ],
        $page_size,
        $page_no,
        [],
        $all_total
    );
    $sell_all = 0;
    $top_all  = 0;
    $fal_all  = 0;
    foreach ($list as &$v)
    {
        if($v->sell_status == SellStatus::SELL)
        {
            $sell_all++;
        }elseif ($v->sell_status == SellStatus::TOP)
        {
            $top_all++;
        }elseif($v->vendor_status == VendorStatus::FAULT)
        {
            $fal_all++;
        }

    }
    $vendor_date['all_total'] = $all_total;
    $vendor_date['sell_all']  = $sell_all;
    $vendor_date['top_all']   = $top_all;
    $vendor_date['fal_all']   = $fal_all;
    $resp = (object)[
        'vendor_list'  => $vendor_list,
        'total'        => $total,
        'page_size'    => $page_size,
        'page_no'      => $page_no,
        'vendor_date'  => $vendor_date
    ];

    LogInfo("--ok--");
    return 0;
}

function GetVendorInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_id        = $_['vendor_id'];
    if(!$vendor_id)
    {
        LogDebug('no vendor id');
        return errcode::PARAM_ERR;
    }

    $mgo              = new VendorMgo\Vendor;
    $shop             = new DaoMongodb\Shop;
    $aisle            = new VendorMgo\Aisle;
    $info             = $mgo->QueryById($vendor_id);
    $aisle_list       = $aisle->ListByVendorId($vendor_id);
    $goods_all_num    = 0;
    $out_aisle_num    = 0;
    $all_user_num     = 0;
    foreach ($aisle_list as &$v)
    {
        $goods_all_num += $v->goods_num;
        if($v->goods_num == 0)
        {
            $out_aisle_num++;
        }
        //目前每个货道的容量是固定的<<<<<<
       $all_user_num = $v->aisle_capacity*count($aisle_list);
    }
    if($info->shop_id !=  PlShopId::ID)
    {
        $shop_info  = $shop->GetShopById($info->shop_id);
        $shop_name  = $shop_info->shop_name;
    }else{
        $shop_name  = '赛领科技信息有限公司';
    }

    $info->ownership     = $shop_name;
    $info->all_use_num   = $all_user_num;
    $info->goods_all_num = $goods_all_num;
    $info->out_aisle_num = $out_aisle_num;


    $resp = (object)[
        'info'  => $info ,
    ];

    LogInfo("--ok--");
    return 0;
}

//设备中货到流水列表（即订单列表）
function GetOrderAisleList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_id        = $_['vendor_id'];
    $shop_id          = $_['shop_id'];
    $order_name       = $_['order_name'];

    if(!$vendor_id)
    {
        LogErr('no vendor id');
        return errcode::PARAM_ERR;
    }

    $order_mgo  = new VendorMgo\VendorOrder;
    $aisle_mgo  = new VendorMgo\Aisle;
    $goods_mgo  = new VendorMgo\VendorGoods;
    $order_list = $order_mgo->GetListTotal(
        [
            'shop_id'         => $shop_id,
            'order_name'      => $order_name,
            'vendor_id'       => $vendor_id,
            'order_status'    => VendorOrderStatus::PAY //<<<<<<<<<<都是支付成功的数据流水
        ]
    );


    $list = [];
    foreach ($order_list as &$v)
    {
        foreach ($v->goods_list as &$g)
        {
          $goods_info = $goods_mgo->GetVendorGoodsById($g->vendor_goods_id);
          $aisle_info = $aisle_mgo->QueryById($g->aisle_id);
          $goods['vendor_order_id']  = $v->vendor_order_id;
          $goods['aisle_id']         = $g->aisle_id;
          $goods['vendor_id']        = $v->vendor_id;
          $goods['goods_name']       = $g->goods_name;
          $goods['goods_bar_code']   = $goods_info->goods_bar_code;
          $goods['goods_num']        = $g->goods_num;
          $goods['aisle_num']        = $aisle_info->goods_num;
          $goods['pay_time']         = $v->pay_time;
          array_push($list,$goods);
        }
    }

    $resp = (object)[
        'list'  => $list,
    ];

    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();

if(isset($_["get_vendor_list"]))
{
    $ret = GetVendorList($resp);
}elseif(isset($_["get_vendor_info"]))
{
    $ret = GetVendorInfo($resp);
}elseif(isset($_["vendor_aisle_order"]))
{
    $ret = GetOrderAisleList($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);


