<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 店铺信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_shop.php");
require_once("permission.php");
require_once("redis_id.php");
require_once("mgo_agent.php");
require_once("/www/public.sailing.com/php/mgo_business.php");
require_once("/www/public.sailing.com/php/mgo_audit_person.php");
use \Pub\Mongodb as Mgo;
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
//提交工商信息工商管理信息
function SaveBusinessInfo(&$resp)
{
    ShopPermissionCheck::PageCheck(ShopPermissionCode::EDIT_SHOP_BUSINESS);
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id       = $_['shop_id'];
    $agent_id      = $_['agent_id'];

    if(!$shop_id && !$agent_id)
    {
        LogErr("no shop_id or agent_id");
        return errcode::PARAM_ERR;
    }
    $corporate_num    = $_['corporate_num'];
    $legal_person     = $_['legal_person'];
    if (!$legal_person) {
        LogErr("legal_person  is empty");
        return errcode::PARAM_ERR;
    }
    $legal_card        = $_['legal_card'];
    if (!$legal_card) {
        LogErr("legal_card  is empty");
        return errcode::PARAM_ERR;
    }
    $legal_card_photo   = json_decode($_['legal_card_photo']);
    if (!$legal_card_photo) {
        LogErr("legal_card_photo  is empty");
        return errcode::PARAM_ERR;
    }
    $business_num        = $_['business_num'];
    if (!$business_num) {
        LogErr("business_num  is empty");
        return errcode::PARAM_ERR;
    }
    $business_date       = json_decode($_['business_date']);
    if (!$business_date) {
        LogErr("business_date is empty");
        return errcode::PARAM_ERR;
    }
    $business_photo      = $_['business_photo'];
    if (!$business_photo) {
        LogErr("business_photo  is empty");
        return errcode::PARAM_ERR;
    }
   if(!$agent_id)
   {
       $repast_permit_num   = $_['repast_permit_num'];
       if (!$repast_permit_num) {
           LogErr("repast_permit_num  is empty");
           return errcode::PARAM_ERR;
       }
       $repast_permit_photo   = $_['repast_permit_photo'];
       if (!$repast_permit_photo) {
           LogErr("repast_permit_photo  is empty");
           return errcode::PARAM_ERR;
       }
   }

    $taxpayer_num    = $_['taxpayer_num'];
    $taxpayer_photo  = $_['taxpayer_photo'];
    $business_scope  = $_['business_scope'];
    if (!$business_scope) {
        LogErr("business_scope  is empty");
        return errcode::PARAM_ERR;
    }
    $merchant_num  = $_['merchant_num'];

    $entry      = new Mgo\BusinessEntry;
    $mgo        = new Mgo\Business;
    $shop_entry = new DaoMongodb\ShopEntry;
    $shop       = new DaoMongodb\Shop;
    $agent_entry= new DaoMongodb\AgentEntry;
    $agent_mgo  = new DaoMongodb\Agent;

    $entry->agent_id             = $agent_id;
    $entry->shop_id              = $shop_id;
    $entry->corporate_num        = $corporate_num;
    $entry->legal_person         = $legal_person;
    $entry->legal_card           = $legal_card;
    $entry->legal_card_photo     = $legal_card_photo;
    $entry->business_num         = $business_num;
    $entry->business_date        = $business_date;
    $entry->business_photo       = $business_photo;
    $entry->repast_permit_num    = $repast_permit_num;
    $entry->repast_permit_photo  = $repast_permit_photo;
    $entry->taxpayer_num         = $taxpayer_num;
    $entry->taxpayer_photo       = $taxpayer_photo;
    $entry->business_scope       = $business_scope;
    $entry->merchant_num         = $merchant_num;
    $entry->delete               = 0;

    $ret      = $mgo->Save($entry);

    $shop_entry->shop_id         = $shop_id;
    $shop_entry->apply_time      = time();
    $shop_entry->business_status = ShopBusiness::APPLY;
    $shop_entry->audit_plan      = BusinessPlan::XS;
    $shop_ret = $shop->Save($shop_entry);

    $agent_entry->agent_id        = $agent_id;
    $agent_entry->business_status = ShopBusiness::APPLY;
    $agent_ret  = $agent_mgo->Save($agent_entry);
    //提交一次就删除所有的审核记录
    $audit_mgo     = new Mgo\AuditPerson;
    $audit_ret     = $audit_mgo->DeleteByShop($shop_id);
    if (0 != $ret || 0!=$shop_ret ||$audit_ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array();
    LogInfo("save ok");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_['business_save']))
{
    $ret = SaveBusinessInfo($resp);
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