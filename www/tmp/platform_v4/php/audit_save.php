<?php
require_once("current_dir_env.php");
require_once("mgo_shop.php");
require_once("permission.php");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("/www/public.sailing.com/php/mgo_audit_person.php");
require_once("/www/public.sailing.com/php/mgo_business.php");
require_once("mgo_ag_employee.php");
require_once("mgo_ag_position.php");
require_once("mgo_agent_apply.php");
require_once("mgo_weixin.php");
require_once("/www/wx.jzzwlcm.com/wx_template.php");
require_once("/www/public.sailing.com/php/mgo_stat_platform_byday.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;

//判断是否通过工商信息的验证结果
function SaveBusinessStatus(&$resp)
{

    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id                     = $_['shop_id'];
    $agent_id                    = $_['agent_id'];
    $shop_name_status            = $_['shop_name_status'];
    $agent_name_status           = $_['agent_name_status'];
    $legal_person_status         = $_['legal_person_status'];
    $legal_card_status           = $_['legal_card_status'];
    $legal_card_photo_status     = $_['legal_card_photo_status'];
    $business_num_status         = $_['business_num_status'];
    $business_date_status        = $_['business_date_status'];
    $business_photo_status       = $_['business_photo_status'];
    $repast_permit_num_status    = $_['repast_permit_num_status'];
    $repast_permit_photo_status  = $_['repast_permit_photo_status'];
    $business_scope_status       = $_['business_scope_status'];


    $shop_name_reason            = $_['shop_name_reason'];
    $agent_name_reason           = $_['agent_name_reason'];
    $legal_person_reason         = $_['legal_person_reason'];
    $legal_card_reason           = $_['legal_card_reason'];
    $legal_card_photo_reason     = $_['legal_card_photo_reason'];
    $business_num_reason         = $_['business_num_reason'];
    $business_date_reason        = $_['business_date_reason'];
    $business_photo_reason       = $_['business_photo_reason'];
    $repast_permit_num_reason    = $_['repast_permit_num_reason'];
    $repast_permit_photo_reason  = $_['repast_permit_photo_reason'];
    $business_scope_reason       = $_['business_scope_reason'];
    $business_sever_money        = $_['business_sever_money'];
    $water_num                   = $_['water_num'];
    $userid                      = $_['userid'];
    $platform                    = $_['platform'];
    //LogDebug($_);
    if($platform)
    {
        if($shop_id)
        {
            PlPermissionCheck::PageCheck(PlPermissionCode::SHOP_AUDIT);
        }else{
            PlPermissionCheck::PageCheck(PlPermissionCode::AGENT_AUDIT);
        }
    }


    $ag_employee    = new \DaoMongodb\AGEmployee;
    $pl_mgo         = new \DaoMongodb\Platformer;
    $pl_position    = new \DaoMongodb\PLPosition;
    $ag_position    = new \DaoMongodb\AGPosition;
    $entry          = new Mgo\AuditPersonEntry;
    $mgo            = new Mgo\AuditPerson;
    $shop_entry     = new \DaoMongodb\ShopEntry;
    $shop_mgo       = new \DaoMongodb\Shop;
    $agent_mgo      = new \DaoMongodb\Agent;
    $agent_entry    = new \DaoMongodb\AgentEntry;
    $business_entry = new Mgo\BusinessEntry;
    $business_mgo   = new Mgo\Business;
    $platformer     = new Mgo\StatPlatform;
    $ag_role        = new Mgo\AgRole;
    $pl_role        = new Mgo\PlRole;
    $agent_apply    = new DaoMongodb\AgentApply;
    $apply_entry    = new DaoMongodb\AgentApplyEntry;
    $wx_mgo         = new \DaoMongodb\Weixin;

    $audit_person_list = [1,2,3,4,5,6];//<<<<<<<<<<<<审核独立的角色
    if(!$userid)
    {
        $userid           = \Cache\Login::GetUserid();
    }

    if($shop_id)
    {
        $info             =  $ag_employee->QueryByUserId($userid);
        $ag_role_info     = $ag_role->QueryById($info->ag_role_id);
        $ag_position_info = $ag_position->GetPositionById($ag_role_info->ag_position_id);
        $ag_employee_id   = $info->ag_employee_id;
        $ag_position_id   = $info->ag_position_id;
        $shop_info        = $shop_mgo->GetShopById($shop_id);
        $audit_person     = $ag_position_info->audit_person;
        if($info->is_admin  == 1)
        {
            $audit_person = 1;
        }

        if(($shop_info->audit_plan !=  $audit_person) || $shop_info->business_status == 3)
        {
            LogErr("this user can do audit now");
            return errcode::SHOP_IS_AUDIT;
        }

        if(!$info->is_admin)
        {
            if(!in_array( $audit_person,$audit_person_list) && $info->is_freeze != IsFreeze::YES)
            {
                LogDebug('employee no do permission');
                return errcode::USER_PERMISSION_ERR;
            }
        }else{
            if($shop_info->audit_plan != 1)
            {
                LogDebug('admin can not audit');
                return errcode::USER_PERMISSION_ERR;
            }
        }
    }elseif($agent_id){
        $platform_id      = PlatformID::ID;
        $pl_info          =  $pl_mgo->QueryByUserId($userid, $platform_id);
        $pl_role_info     = $pl_role->QueryById($pl_info->pl_role_id);
        $pl_position_id   = $pl_role_info->pl_position_id;
        $pl_position_info = $pl_position->GetPositionById($pl_position_id);
        $platformer_id    = $pl_info->platformer_id;
        $agent_info       = $agent_mgo->QueryById($agent_id);
        $audit_person     = $pl_position_info->audit_person;
        if(($agent_info->audit_plan !=  $audit_person)|| $agent_info->business_status == 3)
        {
            LogErr("this user can do audit now");
            return errcode::SHOP_IS_AUDIT;
        }

        if(!$pl_info->is_admin)
        {
            if(!in_array( $audit_person,$audit_person_list) && $pl_info->is_freeze != IsFreeze::YES)
            {
                LogDebug('platformer no do permission');
                return errcode::USER_PERMISSION_ERR;
            }
        }else{
            if($agent_info->audit_plan != 1)
            {
                LogDebug('admin can not audit');
                return errcode::USER_PERMISSION_ERR;
            }
        }
    }else{
        LogErr("shop_id or agent_id is empty");
        return errcode::PARAM_ERR;
    }

//    if($business_sever_money && $water_num)
//    {
//        $is_take_money = IsTakeBusinessMoney::YES;
//    }else{
//        $is_take_money = IsTakeBusinessMoney::NO;
//    }
    if($shop_id)
    {
        if($shop_name_status == 0 || $legal_person_status == 0 || $legal_card_status == 0 || $legal_card_photo_status == 0
            || $business_num_status == 0 || $business_date_status == 0 || $business_photo_status == 0
            || $repast_permit_num_status == 0 || $repast_permit_photo_status == 0 || $business_scope_status == 0
        || $is_take_money = IsTakeBusinessMoney::NO)
        {
            $audit_code  = AuditCode::NO;
        }else{
            $audit_code = AuditCode::YES;
        }
    }elseif($agent_id)
    {
        if($agent_name_status == 0 || $legal_person_status == 0 || $legal_card_status == 0 || $legal_card_photo_status == 0
            || $business_num_status == 0 || $business_date_status == 0 || $business_photo_status == 0 ||
            $business_scope_status == 0 || $is_take_money = IsTakeBusinessMoney::NO)
        {
            $audit_code  = AuditCode::NO;
        }else{
            $audit_code = AuditCode::YES;
        }
    }


    $business_entry->shop_id              = $shop_id;
    $business_entry->agent_id             = $agent_id;
    $business_entry->business_sever_money = $business_sever_money;
    $business_entry->water_num            = $water_num;
    //$business_entry->is_take_money        = $is_take_money;
    $ret_business = $business_mgo->Save($business_entry);

    $entry->audit_id                     = \DaoRedis\Id::GenAuditId();;
    $entry->shop_id                      = $shop_id;
    $entry->agent_id                     = $agent_id;
    $entry->shop_name_status             = $shop_name_status;
    $entry->shop_name_reason             = $shop_name_reason;
    $entry->agent_name_status            = $agent_name_status;
    $entry->agent_name_reason            = $agent_name_reason;
    $entry->legal_person_status          = $legal_person_status;
    $entry->legal_person_reason          = $legal_person_reason;
    $entry->legal_card_status            = $legal_card_status;
    $entry->legal_card_reason            = $legal_card_reason;
    $entry->legal_card_photo_status      = $legal_card_photo_status;
    $entry->legal_card_photo_reason      = $legal_card_photo_reason;
    $entry->business_num_status          = $business_num_status;
    $entry->business_num_reason          = $business_num_reason;
    $entry->business_date_status         = $business_date_status;
    $entry->business_date_reason         = $business_date_reason;
    $entry->business_photo_status        = $business_photo_status;
    $entry->business_photo_reason        = $business_photo_reason;
    $entry->repast_permit_num_status     = $repast_permit_num_status;
    $entry->repast_permit_num_reason     = $repast_permit_num_reason;
    $entry->repast_permit_photo_status   = $repast_permit_photo_status;
    $entry->repast_permit_photo_reason   = $repast_permit_photo_reason;
    $entry->business_scope_status        = $business_scope_status;
    $entry->business_scope_reason        = $business_scope_reason;
    $entry->ag_employee_id               = $ag_employee_id;
    $entry->platformer_id                = $platformer_id;
    $entry->pl_position_id               = $pl_position_id;
    $entry->ag_position_id               = $ag_position_id;
    $entry->audit_code                   = $audit_code;
    $entry->audit_time                   = time();
    $entry->delete                       = 0;

    $ret = $mgo->Save($entry);

    if($audit_code == AuditCode::YES)
    {
        switch ($audit_person){
            case '1':
                $audit_plan = 2;
                break;
            case '2':
                $audit_plan = 3;
                break;
            case '3':
                $audit_plan = 4;
                break;
            case '4':
                $audit_plan = 5;
                break;
            case '5':
                if($business_sever_money && $water_num)
                {
                     $audit_plan = 6;
                }else{
                    $business_status = 3;
                    $audit_plan      = 5;
                    if($agent_id)
                    {
                        //改变该代理商之前的申请信息的状态
                        $apply_info = $agent_apply->GetInfoByAgentId($agent_id);
                        $apply_entry->apply_id        = $apply_info->apply_id;
                        $apply_entry->apply_status    = ApplyStatus::APPLYBUSPASS;
                        $apply_entry->bs_pass_time    = time();
                        $ret = $agent_apply->Save($apply_entry);
                        if(0 != $ret)
                        {
                            LogErr("SAVE err");
                            return errcode::SYS_ERR;
                        }
                        $msg     = '您提交的欣吃货代理商工商信息审核不通过，请完善工商信息，联系电话：400-0020-158。';
                        $msg_ret = Util::SmsSend($apply_info->telephone, $msg);
                        LogDebug($msg_ret);
                        if(0 != $msg_ret)
                        {
                            LogErr("err code".$msg_ret);
                        }
                        if($apply_info->wx_id)
                        {
                            $info = $agent_apply->GetInfoByAgentId($agent_id);
                            $wx_info = $wx_mgo->QueryById($apply_info->wx_id);
                            //发送微信消息
                            WxTemplate::SendTemplate($wx_info->openid,$info);
                        }
                    }
                }
                break;
            case '6':
                $business_status = 2;
                //统计数据
                $platform_id = PlatformID::ID;//现在只有一个运营平台id
                $agent_info  = $agent_mgo->QueryById($agent_id);
                $shop_info   = $shop_mgo->GetShopById($shop_id);
                if($agent_id)
                {
                    if($agent_info->agent_type == AgentType::AREAAGENT)
                    {
                        $no_region_agent_num  = -1;
                        $region_agent_num     = 1;
                    }else{
                        $no_region_agent_num   = 0;
                        $region_agent_num      = 0;
                    }
                    if($agent_info->agent_type == AgentType::GUILDAGENT)
                    {
                        $no_industry_agent_num = -1;
                        $industry_agent_num    = 1;
                    }else{
                        $no_industry_agent_num = 0;
                        $industry_agent_num    = 0;
                    }
                    $day = date('Ymd',$agent_info->ctime);
                    $platformer->SellNumAdd($platform_id, $day,
                        [
                            'region_agent_num'      => $region_agent_num,
                            'industry_agent_num'    => $industry_agent_num,
                            'no_region_agent_num'   => $no_region_agent_num,
                            'no_industry_agent_num' => $no_industry_agent_num
                        ]);
                    //改变该代理商之前的申请信息的状态
                    $apply_info = $agent_apply->GetInfoByAgentId($agent_id);
                    $apply_entry->apply_id        = $apply_info->apply_id;
                    $apply_entry->apply_status    = ApplyStatus::APPLYBUSTHOUR;
                    $apply_entry->bs_pass_time    = time();
                    $ret = $agent_apply->Save($apply_entry);
                    if(0 != $ret)
                    {
                        LogErr("SAVE err");
                        return errcode::SYS_ERR;
                    }
                    $msg     = '恭喜您，您提交的欣吃货代理商工商信息审核通过，祝合作愉快。';
                    $msg_ret = Util::SmsSend($apply_info->telephone, $msg);
                    LogDebug($msg_ret);
                    if(0 != $msg_ret)
                    {
                        LogErr("err code".$msg_ret);
                    }
                    if($apply_info->wx_id)
                    {
                        $info = $agent_apply->GetInfoByAgentId($agent_id);
                        $wx_info = $wx_mgo->QueryById($apply_info->wx_id);
                        //发送微信消息
                        WxTemplate::SendTemplate($wx_info->openid,$info);
                    }
                }else{
                    if($shop_info->agent_type == AgentType::AREAAGENT)
                    {
                        $no_region_shop_num  = -1;
                        $region_shop_num     = 1;
                    }else{
                        $no_region_shop_num   = 0;
                        $region_shop_num      = 0;
                    }
                    if($shop_info->agent_type == AgentType::GUILDAGENT)
                    {
                        $no_industry_shop_num = -1;
                        $industry_shop_num    = 1;
                    }else{
                        $no_industry_shop_num = 0;
                        $industry_shop_num    = 0;
                    }
                    $day = date('Ymd',$shop_info->ctime);
                    $platformer->SellNumAdd($platform_id, $day,
                        [
                            'region_shop_num'      => $region_shop_num,
                            'industry_shop_num'    => $industry_shop_num,
                            'no_region_shop_num'   => $no_region_shop_num,
                            'no_industry_shop_num' => $no_industry_shop_num
                        ]);
                }

                break;
            default:
                break;
        }
    } else{
           $audit_plan      = 1;
           $business_status = 3;
           if($agent_id)
           {
               //改变该代理商之前的申请信息的状态
               $apply_info = $agent_apply->GetInfoByAgentId($agent_id);
               $apply_entry->apply_id        = $apply_info->apply_id;
               $apply_entry->apply_status    = ApplyStatus::APPLYBUSPASS;
               $apply_entry->bs_pass_time    = time();
               $ret = $agent_apply->Save($apply_entry);
               if(0 != $ret)
               {
                   LogErr("SAVE err");
                   return errcode::SYS_ERR;
               }
               $msg     = '您提交的欣吃货代理商工商信息审核不通过，请完善工商信息，联系电话：400-0020-158。';
               $msg_ret = Util::SmsSend($apply_info->telephone, $msg);
               LogDebug($msg_ret);
               if(0 != $msg_ret)
               {
                   LogErr("err code".$msg_ret);
               }
               if($apply_info->wx_id)
               {
                   $info = $agent_apply->GetInfoByAgentId($agent_id);
                   $wx_info = $wx_mgo->QueryById($apply_info->wx_id);
                   //发送微信消息
                   WxTemplate::SendTemplate($wx_info->openid,$info);
               }
           }

    }
   if($shop_id)
   {

       $shop_entry->shop_id         = $shop_id;
       $shop_entry->business_status = $business_status;
       $shop_entry->audit_plan      = $audit_plan;
       $ret_shop = $shop_mgo->Save($shop_entry);
       if (0 != $ret_shop) {
           LogErr("Save err");
           return errcode::SYS_ERR;
       }
   }else{
       $agent_entry->agent_id        = $agent_id;
       $agent_entry->business_status = $business_status;
       $agent_entry->audit_plan      = $audit_plan;
       $ret_agent = $agent_mgo->Save($agent_entry);
       if (0 != $ret_agent) {
           LogErr("Save err");
           return errcode::SYS_ERR;
       }
   }

    if (0 != $ret|| $ret_business) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

//超级管理员的一键审核
function OnePowerSaveBusiness(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id                     = $_['shop_id'];
    $agent_id                    = $_['agent_id'];
    $userid                      = $_['userid'];

    $pl_mgo         = new \DaoMongodb\Platformer;
    $entry          = new Mgo\AuditPersonEntry;
    $mgo            = new Mgo\AuditPerson;
    $shop_entry     = new \DaoMongodb\ShopEntry;
    $shop_mgo       = new \DaoMongodb\Shop;
    $agent_mgo      = new \DaoMongodb\Agent;
    $agent_entry    = new \DaoMongodb\AgentEntry;
    $business_entry = new Mgo\BusinessEntry;
    $business_mgo   = new Mgo\Business;
    $platformer     = new Mgo\StatPlatform;
    $agent_apply    = new DaoMongodb\AgentApply;
    $apply_entry    = new DaoMongodb\AgentApplyEntry;
    $wx_mgo         = new \DaoMongodb\Weixin;

    if(!$userid)
    {
        $userid           = \Cache\Login::GetUserid();
    }
    //获取超级管理员信息
    $platform_id      = PlatformID::ID;
    $pl_info          =  $pl_mgo->QueryByUserId($userid, $platform_id);
    if($pl_info->is_admin != 1)
    {
        LogErr('the platformer is not admin');
        return errcode::USER_PERMISSION_ERR;
    }
    $platformer_id    = $pl_info->platformer_id;

    $business_entry->shop_id              = $shop_id;
    $business_entry->agent_id             = $agent_id;
   //$business_entry->is_take_money        = IsTakeBusinessMoney::YES;
    $ret_business = $business_mgo->Save($business_entry);
    $entry->audit_id                     = \DaoRedis\Id::GenAuditId();
    if($agent_id)
    {
        $entry->agent_name_status        = 1;
    }elseif ($shop_id)
    {
        $entry->shop_name_status         = 1;
    }
    $entry->shop_id                      = $shop_id;
    $entry->agent_id                     = $agent_id;
    $entry->legal_person_status          = 1;
    $entry->legal_card_status            = 1;
    $entry->legal_card_photo_status      = 1;
    $entry->business_num_status          = 1;
    $entry->business_date_status         = 1;
    $entry->business_photo_status        = 1;
    $entry->repast_permit_num_status     = 1;
    $entry->repast_permit_photo_status   = 1;
    $entry->business_scope_status        = 1;
    $entry->platformer_id                = $platformer_id;
    $entry->audit_code                   = AuditCode::YES;
    $entry->audit_time                   = time();
    $entry->delete                       = 0;
    $ret = $mgo->Save($entry);

     //统计数据
   $platform_id = PlatformID::ID;//现在只有一个运营平台id
   $agent_info  = $agent_mgo->QueryById($agent_id);
   $shop_info   = $shop_mgo->GetShopById($shop_id);
   if($agent_id)
   {

        if($agent_info->agent_type == AgentType::AREAAGENT)
        {
            $no_region_agent_num  = -1;
            $region_agent_num     = 1;
        }else{
            $no_region_agent_num   = 0;
            $region_agent_num      = 0;
        }
        if($agent_info->agent_type == AgentType::GUILDAGENT)
        {
            $no_industry_agent_num = -1;
            $industry_agent_num    = 1;
        }else{
            $no_industry_agent_num = 0;
            $industry_agent_num    = 0;
        }
        $day = date('Ymd',$agent_info->ctime);
        $platformer->SellNumAdd($platform_id, $day,
            [
                'region_agent_num'      => $region_agent_num,
                'industry_agent_num'    => $industry_agent_num,
                'no_region_agent_num'   => $no_region_agent_num,
                'no_industry_agent_num' => $no_industry_agent_num
            ]);
       //改变该代理商之前的申请信息的状态
       $apply_info = $agent_apply->GetInfoByAgentId($agent_id);
       if($apply_info->apply_id) {
           $apply_entry->apply_id     = $apply_info->apply_id;
           $apply_entry->apply_status = ApplyStatus::APPLYBUSTHOUR;
           $apply_entry->bs_pass_time = time();

           $ret = $agent_apply->Save($apply_entry);
           if (0 != $ret) {
               LogErr("SAVE err");
               return errcode::SYS_ERR;
           }
       }
       $msg     = '恭喜您，您提交的欣吃货代理商工商信息审核通过，祝合作愉快。';
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
   }else{
        if($shop_info->agent_type == AgentType::AREAAGENT)
        {
            $no_region_shop_num  = -1;
            $region_shop_num     = 1;
        }else{
            $no_region_shop_num   = 0;
            $region_shop_num      = 0;
        }
        if($shop_info->agent_type == AgentType::GUILDAGENT)
        {
            $no_industry_shop_num = -1;
            $industry_shop_num    = 1;
        }else{
            $no_industry_shop_num = 0;
            $industry_shop_num    = 0;
        }
        $day = date('Ymd',$shop_info->ctime);
        $platformer->SellNumAdd($platform_id, $day,
            [
                'region_shop_num'      => $region_shop_num,
                'industry_shop_num'    => $industry_shop_num,
                'no_region_shop_num'   => $no_region_shop_num,
                'no_industry_shop_num' => $no_industry_shop_num
            ]);
    }


    if($shop_id)
    {

        $shop_entry->shop_id         = $shop_id;
        $shop_entry->business_status = 2;
        $shop_entry->audit_plan      = 6;
        $ret_shop = $shop_mgo->Save($shop_entry);
        if (0 != $ret_shop) {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
    }else{
        $agent_entry->agent_id        = $agent_id;
        $agent_entry->business_status = 2;
        $agent_entry->audit_plan      = 6;
        $ret_agent = $agent_mgo->Save($agent_entry);
        if (0 != $ret_agent) {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['save_business_status']))
{
    $ret = SaveBusinessStatus($resp);
}elseif(isset($_['one_power_audit']))
{
    $ret = OnePowerSaveBusiness($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
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

