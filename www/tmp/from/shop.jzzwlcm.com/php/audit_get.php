<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取餐桌位置信息
 */
require_once("current_dir_env.php");
require_once("/www/shop.sailing.com/php/page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("/www/public.sailing.com/php/mgo_audit_person.php");
require_once("/www/public.sailing.com/php/mgo_business.php");
use \Pub\Mongodb as Mgo;
//获取审核进度
function GetAuditList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id  = (string)$_['shop_id'];
    if(!$shop_id)
    {
        LogErr('no shop_id');
        return errcode::PARAM_ERR;
    }
    $mgo       = new Mgo\AuditPerson;
    //$bs_mgo    = new Mgo\Business;
    $list      = $mgo->GetAuditList(['shop_id'=>$shop_id]);
//    $info      = array_pop($list);
//    $bs_info   = $bs_mgo->GetByShopId($shop_id);
//
//    $info->business_sever_money = $bs_info->business_sever_money;
//    $info->water_num  = $bs_info->water_num;

    $resp = (object)array(
        'info' => array_pop($list)
    );

    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["get_audit_list"]))
{
    $ret = GetAuditList($resp);
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

