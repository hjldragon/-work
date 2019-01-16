<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_record.inc");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

function GetVendorRecordList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_id        = $_['vendor_id'];
    $use_name         = $_['use_name'];

    if(!$vendor_id)
    {
        LogDebug('no vendor id');
        return errcode::PARAM_ERR;
    }

    $mgo       = new VendorMgo\VendorRecord;
    $total    = 0;
    $list = $mgo->GetListTotal(
        [
            'vendor_id'      => $vendor_id,
            'use_name'        => $use_name,
        ],
        $total
    );


    $resp = (object)[
        'list'  => $list,
    ];

    LogInfo("--ok--");
    return 0;
}



$ret = -1;
$resp = (object)array();

if(isset($_["get_list"]))
{
    $ret = GetVendorRecordList($resp);
}elseif(isset($_["get_vendor_info"]))
{
    $ret = GetVendorInfo($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

