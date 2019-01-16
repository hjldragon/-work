<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("mgo_shop.php");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

function GetAisleList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_id        = $_['vendor_id'];
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


    $aisle_mgo       = new VendorMgo\Aisle;
    $vendor_goods    = new VendorMgo\VendorGoods;
    $total    = 0;//分页总数
    $list = $aisle_mgo->GetAllList(
        [
             'vendor_id'       => $vendor_id,
        ],
        $page_size,
        $page_no,
        [],
        $total
    );
    foreach ($list as &$v)
    {
        $goods_info = $vendor_goods->GetVendorGoodsById($v->vendor_goods_id);

        $v->img            = $goods_info->goods_img_list;
        $v->goods_name     = $goods_info->vendor_goods_name;
        $v->goods_bar_code = $goods_info->goods_bar_code;
        $v->goods_price    = $goods_info->goods_price;
        $v->goods_stock    = $goods_info->goods_stock;
    }

    $resp = (object)[
        'aisle_list'  => $list,
        'total'       => $total,
        'page_size'   => $page_size,
        'page_no'     => $page_no
    ];

    LogInfo("--ok--");
    return 0;
}

//缺货设备列表
function GetStockList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $ownership        = $_['ownership'];
    $vendor_name      = $_['vendor_name'];
    $shop_id          = $_['shop_id'];
    $vendor_id        = $_['vendor_id'];
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


    $vendor_mgo    = new VendorMgo\Vendor;
    $aisle_mgo     = new VendorMgo\Aisle;
    $goods_mgo     = new VendorMgo\VendorGoods;
    $shop_mgo      = new DaoMongodb\Shop;

    if($vendor_name || $ownership)
    {
        $shop_all  = [];
        if($ownership == "赛领科技信息有限公司" || $ownership == "赛领科技")
        {
            $shop_id_seek = PlShopId::ID;
            array_push($shop_all,$shop_id_seek);
        }
        $shop_list = $shop_mgo->GetAllShopList(['shop_name'=>$ownership]);
        foreach ($shop_list as &$s)
        {
            array_push($shop_all,$s->shop_id);
        }
        $vendor_seek   = $vendor_mgo->GetListTotal(//根据模糊搜索寻找缺货设备
            [
                'vendor_name'  => $vendor_name,
                'shop_id_list' => $shop_all,
                'vendor_status'=> VendorStatus::STOCKOUT,
            ]);
        $vendor_list   = [];
        foreach ($vendor_seek as &$v)
        {
            array_push($vendor_list,$v->vendor_id);
        }

    }

    $total      = 0;
    $aisle_list = $aisle_mgo->GetAllList(
        [
           'vendor_id'       => $vendor_id,
           'vendor_id_list'  => $vendor_list,
           //'shop_id_list'    => $shop_all,
           'shop_id'         => $shop_id,
           'goods_num'       => 0
        ],
        $page_size,
        $page_no,
        [],
        $total
    );

    $list = [];
   foreach ($aisle_list as &$v)
   {
     $vendor_info           = $vendor_mgo->QueryById($v->vendor_id);
     $goods_info            = $goods_mgo->GetVendorGoodsById($v->vendor_goods_id);
     $shop_info             = $shop_mgo->GetShopById($vendor_info->shop_id);
     $menu['vendor_id']     = $vendor_info->vendor_id;
     $menu['vendor_name']   = $vendor_info->vendor_name;
     $menu['address']       = $vendor_info->address;
     $menu['vendor_name']   = $vendor_info->vendor_name;
     $menu['goods_name']    = $goods_info->vendor_goods_name;
     $menu['stockout_time'] = $v->stockout_time;
     $menu['vendor_person'] = $vendor_info->vendor_person;
     if($vendor_info->shop_id == PlShopId::ID)
     {
         $menu['ownership']   = '赛领科技';
     }else{
         $menu['ownership']   = $shop_info->shop_name;
     }
     $menu['is_inform']       = $v->is_inform;
     $menu['aisle_id']        = $v->aisle_id;
     array_push($list,$menu);
   }
    $data      = [];
    $total_all = 0;
    $vendor_mgo->GetListTotal(
        [
            'shop_id'       => $shop_id,
            'vendor_status' => VendorStatus::STOCKOUT
        ],
        $total_all
    );
    $stock_num = 0;
    $aisle_mgo->GetListTotal([
        'shop_id'   => $shop_id,
        'goods_num' => 0,
    ],
        $stock_num
    );
    $out_num = 0;
    $goods_mgo->GetListTotal([
        'shop_id'     => $shop_id,
        'goods_stock' => 0
    ],
        $out_num);
    $data['stock_goods'] = $stock_num;
    $data['stock_total'] = $total_all;
    $data['out_goods']   = $out_num;
    $resp = (object)[
        'list'         => $list,
        'total'        => $total,
        'page_size'    => $page_size,
        'page_no'      => $page_no,
        'data'         => $data
    ];

    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();

if(isset($_["get_aisle_list"]))
{
    $ret = GetAisleList($resp);
}
elseif(isset($_["get_stock_list"]))
{
    $ret = GetStockList($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

