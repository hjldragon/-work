<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_fault_deal.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

function GetFaultList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id          = $_['shop_id'];
    $is_send          = $_['is_send'];
    $deal_status      = $_['deal_status'];
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

    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::PARAM_ERR;
    }

    $fault_mgo    = new VendorMgo\Fault;
    $vendor_mgo   = new VendorMgo\Vendor;

    $vendor_list = $vendor_mgo->GetListTotal(['vendor_name'=>$vendor_name]);
    $vendor_all  = [];
    foreach ($vendor_list as &$s)
    {
        array_push($vendor_all,$s->vendor_id);
    }
    $total = 0;//分页总数
    $list  = $fault_mgo->GetList(
        [
             'shop_id'        => $shop_id,
             'vendor_id_list' => $vendor_all,
             'is_send'        => $is_send,
             'deal_status'    => $deal_status,
        ],
        $page_size,
        $page_no,
        [],
        $total
    );
    $all = [];
    foreach ($list as &$v)
    {
     $vendor_info           = $vendor_mgo->QueryById($v->vendor_id);
     $info['fault_id']      = $v->fault_id;
     $info['fault_time']    = $v->fault_time;
     $info['vendor_num']    = $vendor_info->vendor_num;
     $info['vendor_name']   = $vendor_info->vendor_name;
     $info['address']       = $vendor_info->address;
     $info['vendor_status'] = $vendor_info->vendor_status;
     $info['deal_name']      = $v->deal_name;
     array_push($all,$info);
    }

    $resp = (object)[
        'fault_list'  => $all,
        'total'       => $total,
        'page_size'   => $page_size,
        'page_no'     => $page_no
    ];

    LogInfo("--ok--");
    return 0;
}

function GetFaultInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $fault_id          = $_['fault_id'];


    if(!$fault_id)
    {
        LogDebug('no fault_id');
        return errcode::PARAM_ERR;
    }

    $fault_mgo    = new VendorMgo\Fault;

   $fault_info = $fault_mgo->QueryById($fault_id);

    $info->deal_name   = $fault_info->deal_name;
    $info->name_phone  = $fault_info->name_phone;
    $info->send_time   = $fault_info->send_time;
    $info->fault_time  = $fault_info->fault_time;
    $info->now_time    = time();
    $info->deal_status = $fault_info->deal_status;
    $resp = (object)[
        'fault_info'  => $info,

    ];

    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();

if(isset($_["get_fault_list"]))
{
    $ret = GetFaultList($resp);
}
else if(isset($_["get_fault_info"]))
{
    $ret = GetFaultInfo($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

