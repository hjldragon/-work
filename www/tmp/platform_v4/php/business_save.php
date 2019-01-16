<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 店铺信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_shop.php");
require_once("permission.php");
require_once("redis_id.php");
require_once("mgo_agent_apply.php");
require_once("/www/public.sailing.com/php/mgo_business.php");
require_once("/www/public.sailing.com/php/mgo_audit_person.php");
require_once("mgo_ag_employee.php");
require_once("mgo_ag_position.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
require_once("mgo_agent_apply.php");
require_once("mgo_weixin.php");
require_once("/www/wx.jzzwlcm.com/wx_template.php");
use \Pub\Mongodb as Mgo;

//提交工商信息工商管理信息
function SaveBusinessInfo(&$resp)
{

    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }


    $shop_id       = $_['shop_id'];
    $agent_id      = $_['agent_id'];
    $platform      = $_['platform'];
    $apply         = $_['apply'];
    $myself_bs     = $_['myself_bs'];
    $add           = $_['add'];
    if(!$add){
        if($platform)
        {
            if($shop_id)
            {
                if($apply)
                {
                    PlPermissionCheck::PageCheck(PlPermissionCode::SHOP_APPLY_CER);
                }else{
                    PlPermissionCheck::PageCheck(PlPermissionCode::PL_SHOP_SAVE_BS);
                }
            }else{
                if($apply)
                {
                    PlPermissionCheck::PageCheck(PlPermissionCode::APPLY_CER);
                }else{
                    PlPermissionCheck::PageCheck(PlPermissionCode::PL_SAVE_BS);
                }
            }

        }else{
            if($myself_bs)
            {
                AgPermissionCheck::PageCheck(AgentPermissionCode::AG_BS_SAVE);
            }else{
                if($apply)
                {
                    AgPermissionCheck::PageCheck(AgentPermissionCode::SHOP_APPLY_CER);
                }else{
                    AgPermissionCheck::PageCheck(AgentPermissionCode::AG_SHOP_SAVE_BS);
                }
            }

        }
    }

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
    $agent_apply= new DaoMongodb\AgentApply;
    $apply_entry= new DaoMongodb\AgentApplyEntry;
    $wx_mgo     = new \DaoMongodb\Weixin;

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
    $ret                         = $mgo->Save($entry);
    if (0!= $ret) {
        LogErr("bussniese Save err");
        return errcode::SYS_ERR;
    }
     if($shop_id)
     {
         $shop_entry->shop_id         = $shop_id;
         $shop_entry->apply_time      = time();
         $shop_entry->business_status = ShopBusiness::APPLY;
         $shop_info = $shop->GetShopById($shop_id);
         if(!$shop_info->audit_plan)
         {
             $shop_entry->audit_plan   = BusinessPlan::XS;
         }
         $shop_ret = $shop->Save($shop_entry);
         if (0!=$shop_ret) {
             LogErr("shop info Save err");
             return errcode::SYS_ERR;
         }
     }


    if($agent_id)
    {
        $agent_entry->agent_id        = $agent_id;
        $agent_entry->business_status = ShopBusiness::APPLY;
        $agent_ret  = $agent_mgo->Save($agent_entry);
        if (0!=$agent_ret) {
            LogErr("agent info Save err");
            return errcode::SYS_ERR;
        }

        $apply_info = $agent_apply->GetInfoByAgentId($agent_id);
        if($apply_info->apply_id){
            $apply_entry->apply_id        = $apply_info->apply_id;
            $apply_entry->apply_status    = ApplyStatus::APPLYBUS;
            $apply_entry->bs_time         = time();
            $ret = $agent_apply->Save($apply_entry);
            if(0 != $ret)
            {
                LogErr("agent apply SAVE err");
                return errcode::SYS_ERR;
            }
        }

        $msg     = '您提交的欣吃货代理商工商信息正在审核中，我们将在2-5个工作日为您审核完成。';
        $msg_ret = Util::SmsSend($apply_info->telephone, $msg);
        LogDebug($msg_ret);
        if(0 != $msg_ret)
        {
            LogErr("err code".$msg_ret);
            //echo  '短信发送失败';
        }
        if($apply_info->wx_id)
        {
            $info = $agent_apply->GetInfoByAgentId($agent_id);
            $wx_info = $wx_mgo->QueryById($apply_info->wx_id);
            //发送微信消息
            WxTemplate::SendTemplate($wx_info->openid,$info);
        }
    }


    $resp = (object)array();
    LogInfo("save ok");
    return 0;
}


$ret   = -1;
$resp  = (object)array();
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