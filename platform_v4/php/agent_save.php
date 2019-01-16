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
require_once("redis_login.php");
require_once("mgo_stat_agent_byday.php");
require_once("/www/public.sailing.com/php/mgo_stat_platform_byday.php");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("/www/public.sailing.com/php/mgo_pay_record.php");
require_once("/www/public.sailing.com/php/mgo_from.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;
function SaveAgentInfo(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $agent_id       = $_['agent_id'];
    $phone          = $_['phone'];
    $password       = $_['password'];
    $re_password    = $_['re_password'];
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
    $employee_name  = $_['employee_name'];
    $agent_logo     = $_['agent_logo'];
    $is_freeze      = $_['is_freeze'];
    $bs_agent_save  = $_['bs_agent_save'];

    if(!$bs_agent_save) {

    if ($agent_id) {
        PlPermissionCheck::PageCheck(PlPermissionCode::AGENT_EDIT);
    } else {
        PlPermissionCheck::PageCheck(PlPermissionCode::PL_NEW_AGENT);
    }

   }
    if(!$agent_name|| !$address || !$telephone || !$from || !$real_name || !$email)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $entry      = new \DaoMongodb\AgentEntry;
    $mgo        = new \DaoMongodb\Agent;
    $platformer = new Mgo\StatPlatform;
    $pl_mgo     = new \DaoMongodb\Platformer;
    $ag_emmgo   = new \DaoMongodb\AGEmployee;
    $ag_em_entry= new \DaoMongodb\AGEmployeeEntry;
    $user_mgo   = new \DaoMongodb\User;
    $user_entry = new \DaoMongodb\UserEntry;
    $from_mgo   = new Mgo\From;
    $from_info  =  $from_mgo->GetByFromName($from);
    $time       = time();
    $day        = date('Ymd',$time);

    $platformer_info   = $pl_mgo->QueryByPlName($employee_name);
    if(!$platformer_info->platform_id)
    {
        LogErr('no employee');
        return errcode::NO_EMPLOYEE;
    }
    if(!$agent_id)
    {
        $agent_id = \DaoRedis\Id::GenAgentId();
        // 区域代理商区域不能重复
        if($agent_type == AgentType::AREAAGENT)
        {
            $province_arr = ['北京市','天津市','上海市','重庆市'];
            $city_arr = ['深圳市','广州市','哈尔滨市','长春市','大连市','沈阳市','西安市','青岛市','济南市','武汉市','南京市','成都市',
                '杭州市','宁波市','厦门市', ];
            if(in_array($agent_province, $province_arr) || in_array($agent_city, $city_arr))
            {
                $area = $agent_area;
            }
            $agent = $mgo->GetAgentByCity([
                'agent_province' => $agent_province,
                'agent_city'     => $agent_city,
                'agent_area'     => $area
            ], $agent_type);
//            if($agent->agent_id && $agent->agent_id != $agent_id)
//            {
//                LogErr("City err");
//                return errcode::AGENT_IS_EXIST;
//            }
            if($agent->agent_city)
            {
                LogErr('city is different');
                return errcode::AGENT_IS_EXIST;
            }
        }
        //die;
        //保存新增账号
        if (!preg_match('/^\d{11}$/', $phone))
        {
            LogErr("Phone err");
            return errcode::PHONE_ERR;
        }
        // 验证2次输入密码
        if ($password != $re_password || !$password)
        {
            LogErr("Phone err");
            return errcode::PASSWORD_TWO_SAME;
        }
        $user       = $user_mgo ->QueryByPhone($phone, UserSrc::PLATFORM);
        if($user->phone)
        {
            LogErr("User have create");
            return errcode::USER_HAD_REG;
        }
        $userid     = \DaoRedis\Id::GenUserId();
        $user_entry->ctime     = time();
        $user_entry->userid    = $userid;
        $user_entry->phone     = $phone;
        $user_entry->password  = $password;
        $user_entry->real_name = $real_name;
        $user_entry->email     = $email;
        $user_entry->src       = UserSrc::PLATFORM;

        $ret = $user_mgo->Save($user_entry);
        if(0 != $ret)
        {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }

        //统计数据
        $platform_id = PlatformID::ID;//现在只有一个运营平台id
        if($agent_type == AgentType::AREAAGENT)
        {
            $no_region_agent_num = 1;
        }else{
            $no_region_agent_num = 0;
        }
        if($agent_type == AgentType::GUILDAGENT)
        {
            $no_industry_agent_num = 1;
        }else{
            $no_industry_agent_num = 0;
        }
        $platformer->SellNumAdd($platform_id, $day,
            [
                'no_region_agent_num'   => $no_region_agent_num,
                'no_industry_agent_num' => $no_industry_agent_num
            ]);


        //新增代理商的数据
        $is_freeze              = 0;
        $entry->ctime           = $time;
        $entry->business_status = ShopBusiness::NOAPPLY;
        $entry->audit_plan      = 1;
        $entry->agent_type      = $agent_type;
        $entry->agent_level     = $agent_level;
        $entry->agent_province  = $agent_province;
        $entry->agent_city      = $agent_city;
        $entry->agent_area      = $agent_area;
        // 自动添加代理商固定角色
        $ret = EntryAgPosition($agent_id);
        if(0 != $ret)
        {
            LogErr("AgPosition Entry err");
            return errcode::SYS_ERR;
        }
        // 添加代理商管理员
        $ag_em_entry->entry_time     = $time;
        $ag_em_entry->ag_employee_id = \DaoRedis\Id::GenAGEmployeeId();
        $ag_em_entry->userid         = $userid;
        $ag_em_entry->agent_id       = $agent_id;
        $ag_em_entry->real_name      = $real_name;
        $ag_em_entry->is_admin       = 1;
        $ag_em_entry->is_freeze      = IsFreeze::NO;
        $ret = $ag_emmgo->Save($ag_em_entry);
        if(0 != $ret)
        {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
    }
    $ag_info               = $ag_emmgo->GetAdminByAgentId($agent_id);
    $user_entry->userid    = $ag_info->userid;
    $user_entry->real_name = $real_name;
    $user_entry->email     = $email;

    $ret_user  = $user_mgo->Save($user_entry);

    $entry->agent_id        = $agent_id;
    $entry->agent_name      = $agent_name;
    $entry->telephone       = $telephone;
    $entry->email           = $email;
    $entry->address         = $address;
    $entry->agent_logo      = $agent_logo;
    $entry->from            = $from_info->from_id;
    $entry->is_freeze       = $is_freeze;
    $entry->from_employee   = $platformer_info->platformer_id;
    $ret = $mgo->Save($entry);
    if(0 != $ret || 0 != $ret_user)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
        'agent_id'=>$agent_id
    );
    LogInfo("save ok");
    return 0;
}
//代理商冻结
function AgentFreeze(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::AGENT_FREEZE);
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
    $userid = [];
    foreach ($agent_id as $value)
    {
        $admin = $ag_emmgo->GetAdminByAgentId($value);
        array_push($userid, $admin->userid);
    }
    //删除管理员账号数据
    $user = new \DaoMongodb\User;
    $ret  = $user->BatchDeleteById($userid);
    if(0 != $ret)
    {
        LogErr("Admin Delete err");
        return errcode::SYS_ERR;
    }
    //删除员工数据
    $ret = $ag_emmgo->BatchDeleteByAgent($agent_id);
    if(0 != $ret)
    {
        LogErr("Employee Delete err");
        return errcode::SYS_ERR;
    }
    //删除代理商
    $mongodb  = new \DaoMongodb\Agent;
    $ret      = $mongodb->BatchDeleteById($agent_id);
    if(0 != $ret)
    {
        LogErr("Agent Delete err");
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
//代理商充值
function SaveAgentMoney(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $agent_id       = $_['agent_id'];
    $money          = $_['money'];
    //$agent_level    = $_['agent_level'];
    if(!$agent_id || !$money)
    {
        LogErr("no agent_id or money");
        return errcode::PARAM_ERR;
    }

    //$info      = \Cache\Agent::Get($agent_id);
    //$old_money = $info->money;
    //$new_money = $old_money+$money;

    $pay_entry = new Mgo\PayRecordEntry;
    $pay_mgo   = new Mgo\PayRecord;
    $record_id = \DaoRedis\Id::GenAgentPayId();
    $pay_entry->record_id    = $record_id;
    $pay_entry->agent_id     = $agent_id;
    $pay_entry->ctime        = time();
    $pay_entry->record_money = $money;
    $pay_entry->pay_money    = 0;
    $pay_entry->pay_status   = CZPayStatus::NEEDPAY;
    $pay_entry->delete       = 0;
    $pay_entry->pay_way      = CZPayWay::NOWAY;
    $ret = $pay_mgo->Save($pay_entry);

//    $entry      = new \DaoMongodb\AgentEntry;
//    $mgo        = new \DaoMongodb\Agent;
//
//    $entry->agent_id    = $agent_id;
//    $entry->agent_level = $agent_level;
//    $entry->money       = $new_money;
//    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
        'record_id'    => $record_id,
        'record_money' => $money
    );
    LogInfo("save ok");
    return 0;
}
//保存代理商自动创建的职位
function AgPositionSave($info)
{

    $mongodb = new \DaoMongodb\AGPosition;
    $entry   = new \DaoMongodb\AGPositionEntry;

    $entry->ag_position_id         = $info["ag_position_id"];
    $entry->ag_position_name       = $info["ag_position_name"];
    $entry->ag_position_permission = $info['ag_position_permission'];
    $entry->agent_id               = $info["agent_id"];
    $entry->ctime                  = $info['ctime'];
    $entry->audit_person           = $info['audit_person'];
    $entry->entry_type             = 1;
    $entry->is_edit                = 0;
    $entry->delete                 = 0;

    $ret = $mongodb->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogInfo("save ok");
    return 0;
}
//自动创建系统代理商6个固定职位
function EntryAgPosition($agent_id)
{

    $info["ag_position_id"]         = \DaoRedis\Id::GenAgPositionId();
    $info["ag_position_name"]       = "销售人员";
    $info["agent_id"]               = $agent_id;
    $info['ctime']                  = time();
    $info['audit_person']           = 1;
    $info["ag_position_permission"] = (object)[
        "010101" => 1,
        "010102" => 1,
        "010103" => 1,
        "010104" => 1,
        "010105" => 0,
        "010106" => 0,
        "020101" => 1,
        "020102" => 0,
        "020103" => 0,
        "020104" => 0,
        "020105" => 0,
        "030101" => 1,
        "030102" => 1,
        "030103" => 1,
        "030104" => 1,
        "030105" => 1,
        "040101" => 1,
        "040102" => 1,
        "040103" => 1,
        "040104" => 1,
        "040105" => 1,
        "050101" => 1,
        "050102" => 0,
        "050103" => 0,
        "050104" => 0,
        "060101" => 1,
        "060102" => 0,
        "060103" => 0,
        "060104" => 0,
        "060105" => 0,
        "060106" => 0,
        "070101" => 1,
        "070102" => 0,
        "070103" => 0,
        "070104" => 0,
        "080101" => 1,
        "080102" => 0,
        "080103" => 0,
        "080104" => 0,
        "090101" => 0,
        "090102" => 0,
        "090103" => 0,
        "090104" => 0,
        "090105" => 0,

    ];
    $ret                            = AgPositionSave($info);
    if (0 != $ret)
    {
        LogErr("{$info["position_name"]} err");
        return errcode::SYS_ERR;
    }

    $info["ag_position_id"]          = \DaoRedis\Id::GenAgPositionId();
    $info["ag_position_name"]        = "销售经理";
    $info["agent_id"]                = $agent_id;
    $info['ctime']                   = time();
    $info['audit_person']            = 2;
    $info['ag_position_permission']  = (object)[
        "010101" => 1,
        "010102" => 1,
        "010103" => 1,
        "010104" => 1,
        "010105" => 1,
        "010106" => 1,
        "020101" => 1,
        "020102" => 0,
        "020103" => 0,
        "020104" => 0,
        "020105" => 0,
        "030101" => 1,
        "030102" => 1,
        "030103" => 1,
        "030104" => 1,
        "030105" => 1,
        "040101" => 1,
        "040102" => 1,
        "040103" => 1,
        "040104" => 1,
        "040105" => 1,
        "050101" => 1,
        "050102" => 0,
        "050103" => 0,
        "050104" => 0,
        "060101" => 1,
        "060102" => 0,
        "060103" => 0,
        "060104" => 0,
        "060105" => 0,
        "060106" => 0,
        "070101" => 1,
        "070102" => 0,
        "070103" => 0,
        "070104" => 0,
        "080101" => 1,
        "080102" => 0,
        "080103" => 0,
        "080104" => 0,
        "090101" => 0,
        "090102" => 0,
        "090103" => 0,
        "090104" => 0,
        "090105" => 0,
    ];
    $ret                             = AgPositionSave($info);
    if (0 != $ret)
    {
        LogErr("{$info["position_name"]} err");
        return errcode::SYS_ERR;
    }

    $info["ag_position_id"]         = \DaoRedis\Id::GenAgPositionId();
    $info["ag_position_name"]       = "运营人员";
    $info["agent_id"]               = $agent_id;
    $info['ctime']                  = time();
    $info['audit_person']           = 3;
    $info["ag_position_permission"] = (object)[
        "010101" => 1,
        "010102" => 1,
        "010103" => 1,
        "010104" => 1,
        "010105" => 1,
        "010106" => 0,
        "020101" => 1,
        "020102" => 0,
        "020103" => 0,
        "020104" => 0,
        "020105" => 0,
        "030101" => 1,
        "030102" => 1,
        "030103" => 1,
        "030104" => 1,
        "030105" => 1,
        "040101" => 1,
        "040102" => 1,
        "040103" => 1,
        "040104" => 1,
        "040105" => 1,
        "050101" => 1,
        "050102" => 0,
        "050103" => 0,
        "050104" => 0,
        "060101" => 1,
        "060102" => 0,
        "060103" => 0,
        "060104" => 0,
        "060105" => 0,
        "060106" => 0,
        "070101" => 1,
        "070102" => 0,
        "070103" => 0,
        "070104" => 0,
        "080101" => 1,
        "080102" => 0,
        "080103" => 0,
        "080104" => 0,
        "090101" => 0,
        "090102" => 0,
        "090103" => 0,
        "090104" => 0,
        "090105" => 0,

    ];
    $ret                            = AgPositionSave($info);
    if (0 != $ret)
    {
        LogErr("{$info["position_name"]} err");
        return errcode::SYS_ERR;
    }

    $info["ag_position_id"]         = \DaoRedis\Id::GenAgPositionId();
    $info["ag_position_name"]       = "运营经理";
    $info["agent_id"]               = $agent_id;
    $info['ctime']                  = time();
    $info['audit_person']           = 4;
    $info["ag_position_permission"] = (object)[
        "010101" => 1,
        "010102" => 1,
        "010103" => 1,
        "010104" => 1,
        "010105" => 1,
        "010106" => 1,
        "020101" => 1,
        "020102" => 0,
        "020103" => 0,
        "020104" => 0,
        "020105" => 0,
        "030101" => 1,
        "030102" => 1,
        "030103" => 1,
        "030104" => 1,
        "030105" => 1,
        "040101" => 1,
        "040102" => 1,
        "040103" => 1,
        "040104" => 1,
        "040105" => 1,
        "050101" => 1,
        "050102" => 0,
        "050103" => 0,
        "050104" => 0,
        "060101" => 1,
        "060102" => 0,
        "060103" => 0,
        "060104" => 0,
        "060105" => 0,
        "060106" => 0,
        "070101" => 1,
        "070102" => 0,
        "070103" => 0,
        "070104" => 0,
        "080101" => 1,
        "080102" => 0,
        "080103" => 0,
        "080104" => 0,
        "090101" => 0,
        "090102" => 0,
        "090103" => 0,
        "090104" => 0,
        "090105" => 0,

    ];
    $ret                            = AgPositionSave($info);;
    if (0 != $ret)
    {
        LogErr("{$info["position_name"]} err");
        return errcode::SYS_ERR;
    }

    $info["ag_position_id"]         = \DaoRedis\Id::GenAgPositionId();
    $info["ag_position_name"]       = "财务人员";
    $info["agent_id"]               = $agent_id;
    $info['ctime']                  = time();
    $info['audit_person']           = 5;
    $info["ag_position_permission"] = (object)[
        "010101" => 1,
        "010102" => 1,
        "010103" => 0,
        "010104" => 0,
        "010105" => 0,
        "010106" => 0,
        "020101" => 1,
        "020102" => 0,
        "020103" => 0,
        "020104" => 1,
        "020105" => 0,
        "030101" => 1,
        "030102" => 1,
        "030103" => 1,
        "030104" => 1,
        "030105" => 1,
        "040101" => 1,
        "040102" => 1,
        "040103" => 1,
        "040104" => 1,
        "040105" => 1,
        "050101" => 1,
        "050102" => 0,
        "050103" => 0,
        "050104" => 0,
        "060101" => 1,
        "060102" => 0,
        "060103" => 0,
        "060104" => 0,
        "060105" => 0,
        "060106" => 0,
        "070101" => 1,
        "070102" => 0,
        "070103" => 0,
        "070104" => 0,
        "080101" => 1,
        "080102" => 0,
        "080103" => 0,
        "080104" => 0,
        "090101" => 0,
        "090102" => 0,
        "090103" => 0,
        "090104" => 0,
        "090105" => 0,

    ];
    $ret                            = AgPositionSave($info);
    if (0 != $ret)
    {
        LogErr("{$info["position_name"]} err");
        return errcode::SYS_ERR;
    }

    $info["ag_position_id"]         = \DaoRedis\Id::GenAgPositionId();
    $info["ag_position_name"]       = "财务经理";
    $info["agent_id"]               = $agent_id;
    $info['ctime']                  = time();
    $info['audit_person']           = 6;
    $info["ag_position_permission"] = (object)[
        "010101" => 1,
        "010102" => 1,
        "010103" => 0,
        "010104" => 0,
        "010105" => 0,
        "010106" => 0,
        "020101" => 1,
        "020102" => 1,
        "020103" => 1,
        "020104" => 1,
        "020105" => 0,
        "030101" => 1,
        "030102" => 1,
        "030103" => 1,
        "030104" => 1,
        "030105" => 1,
        "040101" => 1,
        "040102" => 1,
        "040103" => 1,
        "040104" => 1,
        "040105" => 1,
        "050101" => 1,
        "050102" => 0,
        "050103" => 0,
        "050104" => 0,
        "060101" => 1,
        "060102" => 0,
        "060103" => 0,
        "060104" => 0,
        "060105" => 0,
        "060106" => 0,
        "070101" => 1,
        "070102" => 0,
        "070103" => 0,
        "070104" => 0,
        "080101" => 1,
        "080102" => 0,
        "080103" => 0,
        "080104" => 0,
        "090101" => 0,
        "090102" => 0,
        "090103" => 0,
        "090104" => 0,
        "090105" => 0,

    ];
    $ret                            = AgPositionSave($info);
    if (0 != $ret)
    {
        LogErr("{$info["position_name"]} err");
        return errcode::SYS_ERR;
    }
    LogInfo("save ok");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_['agent_save']))
{
    $ret = SaveAgentInfo($resp);

}elseif(isset($_['save_agent']))
{
    $ret = SaveAgent($resp);

}elseif(isset($_['agent_freeze']))
{
    $ret = AgentFreeze($resp);
}elseif(isset($_['agent_del']))
{
    $ret = DeleteAgent($resp);
}elseif(isset($_['agent_money_save']))
{
    $ret = SaveAgentMoney($resp);
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
