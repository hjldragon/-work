<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 新建代理商生成的数据文件
 */
require_once("current_dir_env.php");
require_once("mgo_user.php");
require_once("mgo_ag_employee.php");
require_once("permission.php");
require_once("mgo_agent.php");
require_once("mgo_shop.php");
require_once("mgo_platformer.php");
require_once("redis_id.php");
require_once("redis_login.php");
require_once("mgo_stat_agent_byday.php");
require_once("mgo_stat_platform_byday.php");


function SaveAgentInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $agent_id       = $_['agent_id'];
    $platformer_id  = $_['platformer_id']; // 登录管理员id
    $phone          = $_['phone'];
    $password       = $_['password'];
    $re_password    = $_['re_password'];
    //$parent_id      = $_['parent_id'];
    $agent_type     = $_['agent_type'];
    $agent_level    = $_['agent_level'];
    $agent_name     = $_['agent_name'];
    $telephone      = $_['telephone'];
    $real_name      = $_['real_name'];
    $email          = $_['email'];
    $address        = $_['address'];
    $agent_province = $_['agent_province'];
    $agent_city     = $_['agent_city'];
    $agent_area     = $_['agent_area'];
    $from           = $_['from'];
    $from_salesman  = $_['from_salesman'];
    $time           = time();
    if(!$phone || !$agent_type || !$agent_level || !$agent_name || !$real_name || !$address || !$telephone || !$from || !$platformer_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    if (!preg_match('/^1[34578]\d{9}$/', $phone))
    {
        LogErr("Phone err");
        return errcode::PHONE_ERR;
    }
    if(!$agent_id)
    {
        // 验证2次输入密码
        if ($password != $re_password || !$password)
        {
            LogErr("Phone err");
            return errcode::PASSWORD_TWO_SAME;
        }
    }
    
    $ag_emmgo = new \DaoMongodb\AGEmployee;
    $usermgo  = new \DaoMongodb\User;
    if($agent_id)
    {   
        $agentinfo = \Cache\Agent::Get($agent_id);
        //代理商下有店铺不能修改类型及地址
        if($agentinfo->agent_type != $agent_type || ($agent_province && $agent_province != $agentinfo->agent_province) || ($agent_city && $agent_city != $agentinfo->agent_city) || ($agent_area && $agent_area != $agentinfo->agent_area))
        {
            $agent_id_list[] = $agent_id;
            $shop     = new \DaoMongodb\Shop;
            $count    = $shop->GetShopCountByAgentId($agent_id_list);
            if($count > 0)
            {
                LogErr("Shop in Agent");
                return errcode::AGENT_NO_CHANGE;
            } 
        }
        $eminfo = $ag_emmgo->GetAdminByAgentId($agent_id);
        $userid = $eminfo->userid;
        $ag_employee_id = $eminfo->ag_employee_id;
    }
    
    $user = $usermgo->QueryByPhone($phone, UserSrc::PLATFOR);
    if($user->userid && $user->userid != $userid)
    {   
        LogErr("Phone err");
        return errcode::USER_HAD_REG;
    }
    $mgo   = new \DaoMongodb\Agent;
    // 区域代理商区域不能重复
    if(1 == $agent_type)
    {   
        if(!$agent_province)
        {
            LogErr("Province param err");
            return errcode::PARAM_ERR;
        }
        $agent = $mgo->GetAgentByCity([
            'agent_province' => $agent_province,
            'agent_city'     => $agent_city,
            'agent_area'     => $agent_area
        ]);
        if($agent->agent_id && $agent->agent_id != $agent_id)
        {
            LogErr("City err");
            return errcode::AGENT_IS_EXIST;
        }
    }
    $entry      = new \DaoMongodb\AgentEntry;
    $platformer = new \DaoMongodb\StatPlatform;
    if(!$agent_id)
    {
        $agent_id = \DaoRedis\Id::GenAgentId();
        $entry->ctime           = $time;
        $entry->business_status = 0;
        $entry->is_freeze       = 0;

        //保存新增代理商数据
        $day         = date('Ymd',time());
        $platform_id = 1;//现在只有一个运营平台id
        if($agent_type == AgentType::AREAAGENT)
        {
            $region_agent_num = 1;
        }else{
            $region_agent_num = 0;
        }
        if($agent_type == AgentType::GUILDAGENT)
        {
            $industry_agent_num = 1;
        }else{
            $industry_agent_num = 0;
        }
        $platformer->SellNumAdd($platform_id, $day,
          [
              'region_agent_num' => $region_agent_num,
              'industry_agent_num'=>$industry_agent_num
          ]);

    }

    $entry->agent_id        = $agent_id;
    $entry->parent_id       = $parent_id;
    $entry->agent_type      = $agent_type;
    $entry->agent_level     = $agent_level;
    $entry->agent_name      = $agent_name;
    $entry->telephone       = $telephone;
    $entry->email           = $email;
    $entry->address         = $address;
    $entry->agent_province  = $agent_province;
    $entry->agent_city      = $agent_city;
    $entry->agent_area      = $agent_area;
    $entry->from            = $from;
    $entry->from_salesman   = $from_salesman;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    //添加登录用户
    $info    = new \DaoMongodb\UserEntry;
    if(!$userid){
        $userid = \DaoRedis\Id::GenUserId();
        $info->ctime    = $time;
    }
    $info->userid    = $userid;
    $info->phone     = $phone;
    $info->password  = $password;
    $info->real_name = $real_name;
    $info->src       = UserSrc::PLATFOR; // 运营端用户
    $ret = $usermgo->Save($info);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    // 添加管理员
    $agem_info      = new \DaoMongodb\AGEmployeeEntry;
    if(!$ag_employee_id)
    {
        $ag_employee_id = \DaoRedis\Id::GenAGEmployeeId();
        $agem_info->entry_time = $time;
    }
    
    $agem_info->ag_employee_id = $ag_employee_id;
    $agem_info->userid         = $userid;
    $agem_info->agent_id       = $agent_id;
    $agem_info->real_name      = $real_name;
    $agem_info->is_admin       = 1;
    $ret = $ag_emmgo->Save($agem_info);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    // 保存输入记录
    $pl_mgo = new \DaoMongodb\Platformer;
    $pl_info = new \DaoMongodb\PlatformerEntry;
    $pl_info->platformer_id   = $platformer_id;
    $pl_info->from_record     = $from;
    $pl_info->salesman_record = $from_salesman;

    $ret = $pl_mgo->Save($pl_info);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    // 创建固定品类
   //  $ret = EntryCategory($agent_id);
   //  if(0 != $ret)
   //  {
   //      LogErr("Category Entry err");
   //      return errcode::SYS_ERR;
   //  }
   //  $ret = EntryPosition($agent_id);
   //  if(0 != $ret)
   //  {
   //      LogErr("Position Entry err");
   //      return errcode::SYS_ERR;
   //  }
   
    $resp = (object)array(
        'ag_employeeinfo' => $agem_info,
        'agentinfo' => $entry
    );
    LogInfo("save ok");
    return 0;
}
//提交工商认证信息
function SaveAgentBusiness(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $agent_id   = $_['agent_id'];
    $mgo        = new \DaoMongodb\Agent;
    $entry      = new \DaoMongodb\AgentEntry;
    $agent_info = $mgo->QueryById($agent_id);
    if(!$agent_info->agent_id)
    {
        LogErr('agent is not exist,agent_id:'.$agent_id);
        return errcode::AGENT_NO_EXIST;
    }
    $agentbusiness = (object)array();
    $agentbusiness->company_name     = $_['company_name'];
    $agentbusiness->legal_person     = $_['legal_person'];
    $agentbusiness->legal_card       = $_['legal_card'];
    $agentbusiness->legal_card_photo = json_decode($_['legal_card_photo']);
    $agentbusiness->business_num     = $_['business_num'];
    $agentbusiness->business_date    = json_decode($_['business_date']);
    $agentbusiness->business_photo   = $_['business_photo'];
    $agentbusiness->business_scope   = $_['business_scope'];

    $entry->agent_id        = $agent_id;
    $entry->agent_business  = $agentbusiness;
    $entry->bs_submit_time  = time();
    $entry->business_status = 1;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(

    );
    LogInfo("save ok");
    return 0;
}
//提交工商认证状态
function SaveAgentBusinessStatus(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $agent_id          = $_['agent_id'];
    $business_status   = $_['business_status'];

    $mgo        = new \DaoMongodb\Agent;
    $entry      = new \DaoMongodb\AgentEntry;

    $agent_info = $mgo->QueryById($agent_id);
    if(!$agent_info->agent_id)
    {
        LogErr('agent is not exist,agent_id:'.$agent_id);
        return errcode::AGENT_NO_EXIST;
    }


    $entry->agent_id        = $agent_id;
    $entry->business_status = $business_status;
    $entry->business_time   = time();
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}
//代理商冻结
function AgentFreeze(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $is_freeze  = $_['is_freeze'];
    $agent_id   = $_['agent_id'];

    if(!$agent_id){
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo   = new \DaoMongodb\Agent;
    $entry = new \DaoMongodb\AgentEntry;

    $entry->agent_id  = $agent_id;
    $entry->is_freeze = $is_freeze;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}
//代理商删除
function DeleteAgent(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id = json_decode($_['agent_id']);
    $shop     = new \DaoMongodb\Shop;
    $count    = $shop->GetShopCountByAgentId($agent_id);
    if($count > 0)
    {
        LogErr("Shop in Agent");
        return errcode::AGENT_NO_CHANGE;
    }
    $ag_emmgo = new \DaoMongodb\AGEmployee;
    $ret      = $ag_emmgo->BatchDeleteByAgent($agent_id);
    if(0 != $ret)
    {
        LogErr("Delete err");
        return errcode::SYS_ERR;
    }
    $mongodb  = new \DaoMongodb\Agent;
    $ret      = $mongodb->BatchDeleteById($agent_id);
    if(0 != $ret)
    {
        LogErr("Delete err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("delete ok");
    return 0;
}

function SaveAgent(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id       = $_['agent_id'];
    $telephone      = $_['telephone'];
    $real_name      = $_['real_name'];
    $email          = $_['email'];
    $address        = $_['address'];
    if(!$real_name || !$address || !$telephone)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo   = new \DaoMongodb\Agent;
    $entry = new \DaoMongodb\AgentEntry;
    $entry->agent_id        = $agent_id;
    $entry->telephone       = $telephone;
    $entry->email           = $email;
    $entry->address         = $address;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $ag_emmgo = new \DaoMongodb\AGEmployee;
    $usermgo  = new \DaoMongodb\User;
    $eminfo = $ag_emmgo->GetAdminByAgentId($agent_id);
    //添加登录用户
    $info    = new \DaoMongodb\UserEntry;
    $info->userid    = $eminfo->userid;
    $info->real_name = $real_name;
    $ret = $usermgo->Save($info);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    // 添加管理员
    $agem_info = new \DaoMongodb\AGEmployeeEntry;
    $agem_info->ag_employee_id = $eminfo->ag_employee_id;
    $agem_info->userid         = $eminfo->userid;
    $agem_info->agent_id       = $agent_id;
    $agem_info->real_name      = $real_name;
    $ret = $ag_emmgo->Save($agem_info);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
   
    $resp = (object)array(
        
    );
    LogInfo("save ok");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_['agent_save']))
{
    $ret = SaveAgentInfo($resp);

}elseif(isset($_['agent_business_save']))
{
    $ret = SaveAgentBusiness($resp);

}elseif(isset($_['save_agent']))
{
    $ret = SaveAgent($resp);

}elseif(isset($_['agent_business_status_save']))
{
    $ret = SaveAgentBusinessStatus($resp);
}elseif(isset($_['agent_freeze']))
{
    $ret = AgentFreeze($resp);
}elseif(isset($_['agent_del']))
{
    $ret = DeleteAgent($resp);
}

else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
