<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
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

    $shop_id           = $_['shop_id'];
    $sort               = $_['sort'];
    $vendor_name        = $_['vendor_name'];
    $vendor_status_list = json_decode($_['vendor_status_list']);
    $page_no            = $_['page_no'];
    $page_size          = $_['page_size'];

    if(!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if(!$page_no)
    {
        $page_no = 1; //第一页开始
    }


    $vendor_mgo   = new VendorMgo\Vendor;
    $aisle_mgo    = new VendorMgo\Aisle;
    $goods_mgo    = new VendorMgo\VendorGoods;


    $total       = 0;//分页总数
    $vendor_list = $vendor_mgo->GetAllList(
        [
             'shop_id'            => $shop_id,
             'vendor_name'        => $vendor_name,
             'vendor_status_list' => $vendor_status_list
        ],
        $page_size,
        $page_no,
        [],
        $total
    );
    $all = [];
    foreach ($vendor_list as &$v)
    {
        if($v->status == VendorStatus::FAULT)//故障设备跳过
        {
            continue;
        }
        $aisle_list = $aisle_mgo->ListByVendorId($v->vendor_id);
        $stock_num  = 0;
        $out_num    = 0;
        $aisle_all  = [];
        foreach ($aisle_list as &$a)
        {
            $goods_info = $goods_mgo->GetVendorGoodsById($a->vendor_goods_id);
            if($a->vendor_goods_id){
                if($a->goods_num == 0)
                {
                    $stock_num++;
                    $aisle_info['aisle_name'] = $a->aisle_name;
                    $aisle_info['goods_num']  = $a->goods_num;
                    array_push($aisle_all,$aisle_info);
                }
                if($a->goods_num == 0 && $goods_info->goods_stock == 0)
                {
                    $out_num++;
                }
            }
        }
        $info['vendor_id']     = $v->vendor_id;
        $info['vendor_name']   = $v->vendor_name;
        $info['province']      = $v->province;
        $info['city']          = $v->city;
        $info['area']          = $v->area;
        $info['address']       = $v->address;
        $info['vendor_status'] = $v->vendor_status;
        $info['stock_num']     = $stock_num;
        $info['out_num']       = $out_num;
        $info['stockout_list'] = $aisle_all;
        array_push($all,$info);
    }
    foreach($all as $key=>$value){
        $ids[$key]  = $value["vendor_id"];
        if($sort == 2)
        {
            $names[$key]= $value["stock_num"];
        }elseif ($sort == 3)
        {
            $names[$key]= $value["out_num"];
        }
    }
    array_multisort($all,$ids,$names);


    $resp = (object)[
        'vendor_list'  => $all,
        'total'        => $total,
        'page_size'    => $page_size,
        'page_no'      => $page_no,

    ];

    LogInfo("--ok--");
    return 0;
}
//获取该店铺所有设备复杂人数据
function GetVendorPerson(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id          = $_['shop_id'];


    $vendor_mgo   = new VendorMgo\Vendor;
    $total        = 0;
    $list  = $vendor_mgo->GetListTotal(['shop_id'=>$shop_id]);
    $all = [];
    foreach ($list as &$v)
    {
        $info['vendor_person'] = $v->vendor_person;
        $info['person_phone']  = $v->person_phone;
        array_push($all,$info);
    }


    $resp = (object)[
        'person_list'  => assoc_unique($all,'person_phone'),//去掉电话号码相同的人
    ];

    LogInfo("--ok--");
    return 0;
}
function assoc_unique($arr, $key) {
    $tmp_arr = array();
    foreach ($arr as $k => $v) {
        if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
            unset($arr[$k]);
        } else {
            $tmp_arr[] = $v[$key];
        }
    }
    return $arr;

}
$ret = -1;
$resp = (object)array();

if(isset($_["get_vendor_list"]))
{
    $ret = GetVendorList($resp);
}
elseif(isset($_["get_vendor_person"]))
{
    $ret = GetVendorPerson($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

