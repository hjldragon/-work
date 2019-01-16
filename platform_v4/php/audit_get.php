<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取餐桌位置信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("/www/public.sailing.com/php/mgo_audit_person.php");
require_once("/www/public.sailing.com/php/mgo_business.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
require_once("/www/public.sailing.com/php/mgo_ag_role.php");
require_once("/www/public.sailing.com/php/mgo_pl_role.php");
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
    $agent_id = (string)$_['agent_id'];
    $platform =  $_['platform'];
    if($platform)
    {
        if($shop_id)
        {
            PlPermissionCheck::PageCheck(PlPermissionCode::AUDIT_PLAN);
        }else{
            PlPermissionCheck::PageCheck(PlPermissionCode::PL_AGENT_AUDIT_SEE);
        }

    }else{
         if($shop_id)
         {
             AgPermissionCheck::PageCheck(AgentPermissionCode::BUSINESS_SEE);
         }
    }

    $ag_employee = new \DaoMongodb\AGEmployee;
    $pl_mgo      = new \DaoMongodb\Platformer;
    $ag_position = new \DaoMongodb\AGPosition;
    $pl_position = new \DaoMongodb\PLPosition;
    $ag_role     = new Mgo\AgRole;
    $pl_role     = new Mgo\PlRole;
    $bs_mgo      = new Mgo\Business;
    $mgo         = new Mgo\AuditPerson;
    if($shop_id)
    {
        $list    = $mgo->GetAuditList(['shop_id'=>$shop_id]);
        $bs_info = $bs_mgo->GetByShopId($shop_id);
        foreach ($list as &$v)
        {
            $ag_employee_info    = $ag_employee->QueryById($v->ag_employee_id);
            $ag_role_info        = $ag_role->QueryById($ag_employee_info->ag_role_id);
            $ag_position_info    = $ag_position->GetPositionById($ag_role_info->ag_position_id);
            if($ag_employee_info->is_admin == 1)
            {
                $ag_name          = '超级管理员';
                $ag_position_name = '超级管理员';
            }else{
                $ag_name          = $ag_employee_info->real_name;
                $ag_position_name = $ag_position_info->ag_position_name;
            }

            $v->real_name        = $ag_name;
            $v->audit_person     = $ag_position_info->audit_person;

            $v->position_name    = $ag_position_name;
            if($v->audit_person == BusinessPlan::CW)
            {
                $v->business_sever_money = $bs_info->business_sever_money;
                $v->water_num            = $bs_info->water_num;
            }
        }
    }elseif($agent_id){
        $list    = $mgo->GetAuditList(['agent_id'=>$agent_id]);
        $bs_info = $bs_mgo->GetByAgentId($agent_id);
        foreach ($list as &$v)
        {
            $pl_info             = $pl_mgo->QueryById($v->platformer_id);
            $pl_role_info        = $pl_role->QueryById($pl_info->pl_role_id);
            $pl_position_info    = $pl_position->GetPositionById($pl_role_info->pl_position_id);

            $v->real_name        = $pl_info->pl_name;
            $v->audit_person     = $pl_position_info->audit_person;
            if($v->audit_person == BusinessPlan::CW)
            {
                $v->business_sever_money = $bs_info->business_sever_money;
                $v->water_num            = $bs_info->water_num;
            }

        }
    }else{
        LogErr("shop_id or agent_id is empty");
        return errcode::PARAM_ERR;
    }

    $resp = (object)array(
        'audit_list' => $list
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

