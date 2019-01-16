<?php
/*
 */
require_once("current_dir_env.php");
require_once("mgo_agent_apply.php");
require_once("mgo_agent.php");
require_once("redis_id.php");
require_once("redis_login.php");
require_once("mgo_user.php");
require_once("mgo_ag_employee.php");
require_once("permission.php");
require_once("mgo_agent.php");
require_once("mgo_shop.php");
require_once("mgo_platformer.php");
require_once("redis_login.php");
require_once("mgo_stat_agent_byday.php");
require_once("mgo_weixin.php");
require_once("/www/public.sailing.com/php/mgo_stat_platform_byday.php");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("/www/public.sailing.com/php/mgo_pay_record.php");
require_once("/www/public.sailing.com/php/mgo_from.php");
require_once("/www/wx.jzzwlcm.com/wx_template.php");
use \Pub\Mongodb as Mgo;

function SaveAgentApply(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $apply_name     = $_['apply_name'];
    $telephone      = $_['telephone'];
    $company        = $_['company'];
    $email          = $_['email'];
    $address        = $_['address'];
    $agent_type     = $_['agent_type'];
    $agent_level    = $_['agent_level'];
    $agent_province = $_['agent_province'];
    $agent_city     = $_['agent_city'];
    $agent_area     = $_['agent_area'];
    $wx_id          = $_['wx_id'];

    $agent_mgo = new \DaoMongodb\Agent;
    $entry     = new \DaoMongodb\AgentApplyEntry;
    $mgo       = new \DaoMongodb\AgentApply;
    $user_mgo  = new \DaoMongodb\User;
    //申请不能重复提交
    $list     = $mgo->ListByPhone($telephone);
    foreach ($list as $v)
    {
        if($v->apply_status != ApplyStatus::APPLYPASS)
        {
            LogErr('apply is again');
            return errcode::APPLY_IS_EXIST;
        }
    }

    //判断区域是否重复
    if($agent_type == AgentType::AREAAGENT) {
        $province_arr = ['北京市', '天津市', '上海市', '重庆市'];
        $city_arr     = ['深圳市', '广州市', '哈尔滨市', '长春市', '大连市', '沈阳市', '西安市', '青岛市', '济南市', '武汉市', '南京市', '成都市',
            '杭州市', '宁波市', '厦门市',];
        if (in_array($agent_province, $province_arr) || in_array($agent_city, $city_arr)) {
            $area = $agent_area;
        }
        $agent = $agent_mgo->GetAgentByCity([
            'agent_province' => $agent_province,
            'agent_city'     => $agent_city,
            'agent_area'     => $area
        ], $agent_type);
        if ($agent->agent_city) {
            LogErr('city is different');
            return errcode::AGENT_IS_EXIST;
        }
        $apply_info = $mgo->GetInfoByCity([
            'agent_province' => $agent_province,
            'agent_city'     => $agent_city,
            'agent_area'     => $area
        ], $agent_type);
        if ($apply_info->agent_city) {
            LogErr('city is different');
            return errcode::AGENT_IS_EXIST;
        }
    }

    if(!$apply_name || !$telephone || !$company)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $req_p = PageUtil::GetPhone($telephone);
    if(!$req_p)
    {
        LogErr('telephone is not verify');
        return errcode::PHONE_ERR;
    }
    //判断此用户是否已经注册过了
    $user       = $user_mgo ->QueryByPhone($telephone, UserSrc::PLATFORM);
    if($user->phone)
    {
        LogErr("User have create");
        return errcode::USER_HAD_REG;
    }

    if($email)
    {
         $req = PageUtil::GetEmail($email);
         if(!$req)
         {
             LogErr('emial is not verify');
             return errcode::EMAIL_ERR;
         }
    }

    $apply_id = \DaoRedis\Id::GenAgentApplyId();


    $entry->apply_name      = $apply_name;
    $entry->agent_type      = $agent_type;
    $entry->agent_level     = $agent_level;
    $entry->agent_province  = $agent_province;
    $entry->agent_city      = $agent_city;
    $entry->agent_area      = $agent_area;
    $entry->wx_id           = $wx_id;
    $entry->apply_id        = $apply_id;
    $entry->company         = $company;
    $entry->telephone       = $telephone;
    $entry->email           = $email;
    $entry->address         = $address;
    $entry->sub_time        = time();
    $entry->apply_status    = ApplyStatus::APPLY;
    $entry->delete          = 0;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $msg     = '您的账号'.$telephone.'申请已提交成功，欣吃货工作人员会尽快联系您，登录密码将通过短信的方式发送给您，请注意查收，谢谢!';
    $msg_ret = Util::SmsSend($telephone, $msg);

    if(0 != $msg_ret)
    {
        LogErr("err code".$msg_ret);
    }
    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}
//代理商申请处理
function ChangeAgentApply(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $apply_id      = $_['apply_id'];
    $apply_status  = (int)$_['apply_status'];
    $platformer_id = $_['platformer_id'];

    if(!$apply_status)
    {
        LogErr("type is null");
        return errcode::PARAM_ERR;
    }
    $mongodb    = new \DaoMongodb\Agent;
    $entry      = new \DaoMongodb\AgentEntry;
    $platformer = new Mgo\StatPlatform;
    $ag_emmgo   = new \DaoMongodb\AGEmployee;
    $ag_em_entry= new \DaoMongodb\AGEmployeeEntry;
    $user_mgo   = new \DaoMongodb\User;
    $user_entry = new \DaoMongodb\UserEntry;
    $from_mgo   = new Mgo\From;
    $pl_mgo     = new \DaoMongodb\Platformer;
    $apply_entry= new \DaoMongodb\AgentApplyEntry;
    $apply_mgo  = new \DaoMongodb\AgentApply;
    $wx_mgo     = new \DaoMongodb\Weixin;

    $apply_entry->apply_id        = $apply_id;
    $apply_entry->apply_status    = $apply_status;
    $apply_entry->sub_pass_time   = time();
    $ret = $apply_mgo->Save($apply_entry);
    if(0 != $ret)
    {
        LogErr("SAVE err");
        return errcode::SYS_ERR;
    }

    $info              = $apply_mgo->GetInfoById($apply_id);
    $time              = time();
    $day               = date('Ymd',$time);
    $from              = '网络';//<<<<<<<<<有疑问处,名称是可以改变的
    $from_info         =  $from_mgo->GetByFromName($from);
    $platformer_info   = $pl_mgo->QueryById($platformer_id);
    if($info->apply_status == ApplyStatus::APPLYPASS)
    {
            $msg     = '感谢您申请赛领欣吃货代理商，很遗憾地通知您本次申请未通过，关注“赛领欣吃货”公众号获取更多精彩。';
            $msg_ret = Util::SmsSend($info->telephone, $msg);
            LogDebug($msg_ret);
            if(0 != $msg_ret)
            {
                LogErr("err code".$msg_ret);
            }
            if($info->wx_id)
            {
                $wx_info = $wx_mgo->QueryById($info->wx_id);
                //发送微信消息
                WxTemplate::SendTemplate($wx_info->openid,$info);
            }
    }
    if($info->apply_status == ApplyStatus::APPLYTHOUR)
     {
            //创建代理商数据
            $agent_id   = \DaoRedis\Id::GenAgentId();
            $userid     = \DaoRedis\Id::GenUserId();
            //保存新增账号
            $user_entry->ctime     = time();
            $user_entry->userid    = $userid;
            $user_entry->phone     = $info->telephone;
            $user_entry->password  = '888888';
            $user_entry->real_name = $info->apply_name;
            $user_entry->src       = UserSrc::PLATFORM;
            $user_entry->email     = $info->email;
            $ret = $user_mgo->Save($user_entry);
            if(0 != $ret)
            {
                LogErr("Save err");
                return errcode::SYS_ERR;
            }

            //统计数据
            $platform_id = PlatformID::ID;//现在只有一个运营平台id
            if($info->agent_type == AgentType::AREAAGENT)
            {
                $no_region_agent_num = 1;
            }else{
                $no_region_agent_num = 0;
            }
            if($info->agent_type == AgentType::GUILDAGENT)
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
            $entry->agent_type      = $info->agent_type;
            $entry->agent_level     = $info->agent_level;
            $entry->agent_province  = $info->agent_province;
            $entry->agent_city      = $info->agent_city;
            $entry->agent_area      = $info->agent_area;
            $entry->from            = $from_info->from_id;
            $entry->is_freeze       = $is_freeze;
            $entry->agent_id        = $agent_id;
            $entry->agent_name      = $info->company;
            $entry->telephone       = $info->telephone;
            $entry->email           = $info->email;
            $entry->address         = $info->address;
            $entry->from_employee   = $platformer_info->platformer_id;
            $ret = $mongodb->Save($entry);
            if(0 != $ret)
            {
                LogErr("AgPosition Entry err");
                return errcode::SYS_ERR;
            }
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
            $ag_em_entry->real_name      = $info->apply_name;
            $ag_em_entry->is_admin       = 1;
            $ag_em_entry->is_freeze      = IsFreeze::NO;
            $ret = $ag_emmgo->Save($ag_em_entry);
            if(0 != $ret)
            {
                LogErr("Save err");
                return errcode::SYS_ERR;
            }
            $apply_entry->apply_id    = $info->apply_id;
            $apply_entry->agent_id    = $agent_id;
            $ret = $apply_mgo->Save($apply_entry);
            if(0 != $ret)
            {
                LogErr("Save err");
                return errcode::SYS_ERR;
            }
            $msg     = '您申请的欣吃货代理商初审通过，请用代理商账户：'.$info->telephone.'，密码：888888，登录欣吃货代理商平台提交您的详细资料，点击http://platform.xinchihuo.com.cn 登录,谢谢。';
            $msg_ret = Util::SmsSend($info->telephone, $msg);
            LogDebug($msg_ret);
            if(0 != $msg_ret)
            {
                LogErr("err code".$msg_ret);
                //echo  '短信发送失败';
            }
         if($info->wx_id)
         {

             $wx_info = $wx_mgo->QueryById($info->wx_id);
             //发送微信消息
             WxTemplate::SendTemplate($wx_info->openid,$info);
         }
        }

    $resp = (object)array(
    );
    LogInfo("change ok");
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
$ret  = -1;
$resp = (object)array();
if(isset($_['agent_apply_save']))
{
    $ret = SaveAgentApply($resp);

}elseif(isset($_['change_apply_status']))
{
    $ret = ChangeAgentApply($resp);
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
