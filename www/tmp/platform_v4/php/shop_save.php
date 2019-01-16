<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 新建店铺生成的数据文件
 */
require_once("current_dir_env.php");
require_once("mgo_shop.php");
require_once("mgo_category.php");
require_once("mgo_employee.php");
require_once("permission.php");
require_once("mgo_position.php");
require_once("mgo_platformer.php");
require_once("mgo_ag_employee.php");
require_once("mgo_authorize.php");
require_once("/www/public.sailing.com/php/mgo_stat_platform_byday.php");
require_once("mgo_stat_shop_byday.php");
require_once("mgo_stat_agent_byday.php");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("/www/public.sailing.com/php/mgo_from.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
require_once("/www/public.sailing.com/php/mgo_resources.php");
use \Pub\Mongodb as Mgo;
function SaveShopinfo(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $real_name          = $_['real_name'];
    $phone              = $_['phone'];
    $email              = $_['email'];
    $password           = $_['password'];
    $re_password        = $_['re_password'];

    $shop_id            = $_['shop_id'];
    $shop_name          = $_['shop_name'];
    $shop_logo          = $_['shop_logo'];
    $shop_area          = $_['shop_area'];
    $address            = $_['address'];
    $shop_model         = json_decode($_['shop_model']);
    $telephone          = $_['telephone'];
    $province           = $_['province'];
    $city               = $_['city'];
    $area               = $_['area'];
    $agent_id           = $_['agent_id'];
    $from               = $_['from'];
    $employee_name      = $_['employee_name'];
    $platform           = $_['platform'];
    $shop_bs_save       = $_['shop_bs_save'];
    if(!$shop_name  || !$shop_area || !$address || !$telephone)
    {
        LogErr("same canshu is err");
        return errcode::PARAM_ERR;
    }
    if(!$shop_bs_save)
    {
        if($platform)
        {
            if($shop_id)
            {
                PlPermissionCheck::PageCheck(PlPermissionCode::EDIT_SHOP);
            }else{
                PlPermissionCheck::PageCheck(PlPermissionCode::ADD_SHOP);
            }

        }else{
            if($shop_id)
            {
                AgPermissionCheck::PageCheck(AgentPermissionCode::EDIT_SHOP);
            }else{
                AgPermissionCheck::PageCheck(AgentPermissionCode::ADD_SHOP);
            }
        }
    }


    $mgo                = new \DaoMongodb\Shop;
    $entry              = new \DaoMongodb\ShopEntry;
    $platformer_status  = new Mgo\StatPlatform;
    $ag_employee        = new \DaoMongodb\AGEmployee;
    $agent_mgo          = new \DaoMongodb\Agent;
    $from_mgo           = new Mgo\From;
    $from_info          = $from_mgo->GetByFromName($from);
    $agent_info         = $agent_mgo->QueryById($agent_id);

    if($agent_info->agent_type != AgentType::GUILDAGENT)
    {
        $province_arr = ['北京市','天津市','上海市','重庆市'];
        $city_arr     = ['深圳市','广州市','哈尔滨市','长春市','大连市','沈阳市','西安市','青岛市','济南市','武汉市','南京市','成都市',
            '杭州市','宁波市','厦门市'];
        if(in_array($agent_info->agent_province, $province_arr) || in_array($agent_info->agent_city, $city_arr))
        {
            if($agent_info->agent_area != $area)
            {
                LogErr('area is different');
                return errcode::PROVINCE_ERR;
            }

        }
        if($agent_id != PlAgentId::ID)
        {

            if($agent_info->agent_province)
            {
                if($agent_info->agent_province != $province)
                {
                    LogErr('province is different');
                    return errcode::PROVINCE_ERR;
                }
            }
            if ($agent_info->agent_city)
            {
                if($agent_info->agent_city != $city)
                {
                    LogErr('city is different');
                    return errcode::PROVINCE_ERR;
                }
            }
        }
    }


    if(!$shop_id)
    {
        $ag_employee_info   = $ag_employee->GetEmployeeByName($agent_id, $employee_name);
        if(!$ag_employee_info->ag_employee_id)
        {
            LogErr('no employee');
            return errcode::NO_EMPLOYEE;
        }
        $shop_id = \DaoRedis\Id::GenShopId();
        //创建的用户列表信息
        if(!$real_name || !$phone || !$password)
        {
            LogErr("param err");
            return errcode::PARAM_ERR;
        }
        // 验证手机格式

        if (!preg_match('/^\d{11}$/', $phone)) {
            LogErr("Phone err");
            return errcode::PHONE_ERR;
        }
        // 验证2次输入密码
        if ($password != $re_password) {
            LogErr("password err");
            return errcode::PASSWORD_TWO_SAME;
        }
        $user_entry = new \DaoMongodb\UserEntry;
        $user_mgo   = new \DaoMongodb\User;

        $user       = $user_mgo ->QueryByPhone($phone, UserSrc::SHOP);
        if($user->phone)
        {
            LogErr("User have create");
            return errcode::USER_HAD_REG;
        }

        $userid     = \DaoRedis\Id::GenUserId();
        $user_entry->ctime     = time();
        $user_entry->userid    = $userid;
        $user_entry->real_name = $real_name;
        $user_entry->phone     = $phone;
        $user_entry->password  = $password;
        $user_entry->email     = $email;
        $user_entry->src       = UserSrc::SHOP;

        $ret = $user_mgo->Save($user_entry);
        if(0 != $ret)
        {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }

        // 添加管理员
        $employee = (object)array();
        $ret = InviteEmployee($shop_id, $userid, $employee);
        if(0 != $ret)
        {
            LogErr("Employee Entry err");
            return errcode::SYS_ERR;
        }
        // 创建固定品类
        $ret = EntryCategory($shop_id);
        if(0 != $ret)
        {
            LogErr("Category Entry err");
            return errcode::SYS_ERR;
        }
        $ret = EntryPosition($shop_id);
        if(0 != $ret)
        {
            LogErr("Position Entry err");
            return errcode::SYS_ERR;
        }
        // 创建固定资源
        $ret = EntryResources($shop_id);
        if(0 != $ret)
        {
            LogErr("Resources Entry err");
            return errcode::SYS_ERR;
        }
        // 创建登录授权(默认数量为2)
        // $au_mgo                 = new \DaoMongodb\Authorize;
        // $data                   = new \DaoMongodb\AuthorizeEntry;
        // $data->shop_id          = $shop_id;
        // $data->pc_num           = AuthorizeNum::NUM;
        // $data->pad_num          = AuthorizeNum::NUM;
        // $data->cashier_num      = AuthorizeNum::NUM;
        // $data->app_num          = AuthorizeNum::NUM;
        // $data->machine_num      = AuthorizeNum::NUM;
        // $data->used_pc_num      = 0;
        // $data->used_pad_num     = 0;
        // $data->used_cashier_num = 0;
        // $data->used_app_num     = 0;
        // $data->used_machine_num = 0;

        // $ret = $au_mgo->Save($data);
        // if(0 != $ret)
        // {
        //     LogErr("Save err");
        //     return errcode::SYS_ERR;
        // }

        //新建的店铺信息数据

        $entry->ctime            = time();
        $entry->logo_img_time    = time();
        $entry->is_freeze        = 0;
        $entry->business_status  = 0; //工商认证状态变成0
        $entry->order_clear_time = 24*60*60; //默认订单清理时间24小时(用于后厨通)
        $entry->agent_id         = $agent_id;
        $entry->agent_type       = $agent_info->agent_type;
        $entry->from_employee    = $ag_employee_info->ag_employee_id;
        //新建店铺的统计和店铺自动生成的数据
        //保存新增商户统计数据
        $day          = date('Ymd',time());
        $platform_id  = 1;//现在只有一个运营平台id
        if($agent_info->agent_type == AgentType::AREAAGENT)
        {
            $no_region_shop_num = 1;
        }else{
            $no_region_shop_num = 0;
        }
        if($agent_info->agent_type == AgentType::GUILDAGENT)
        {
            $no_industry_shop_num = 1;
        }else{
            $no_industry_shop_num = 0;
        }
        $platformer_status->SellNumAdd($platform_id, $day,
            [
                'no_region_shop_num'   => $no_region_shop_num,
                'no_industry_shop_num' => $no_industry_shop_num
            ]);
        //保存代理商记录数据
        $mongodb     = new \DaoMongodb\StatAgent;
        $day         = date('Ymd',time());
        //新增的店铺
        $num['new_shop_num'] = 1;
        $mongodb->SellAgentNumAdd($agent_id, $day, $num);

    }
    $entry->shop_id       = $shop_id;
    $entry->shop_name     = $shop_name;
    $entry->shop_logo     = $shop_logo;
    $entry->shop_area     = $shop_area;
    $entry->address       = $address;
    $entry->shop_model    = $shop_model;
    $entry->telephone     = $telephone;
    $entry->province      = $province;
    $entry->city          = $city;
    $entry->area          = $area;
    $entry->from          = $from_info->from_id;
    $entry->agent_id      = $agent_id;

    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
        'shop_id' => $shop_id,
        'userid'  => $userid
    );
    LogInfo("save ok");
    return 0;
}

function GetAgentByShopCity($province, $city=null, $area=null)
{
    $agentmgo = new \DaoMongodb\Agent;
    $agent = $agentmgo->GetAgentByCity([
        'agent_province' => $province,
        'agent_city'     => $city,
        'agent_area'     => $area
    ]);
    if(!$agent->agent_id)
    {
        $agent = $agentmgo->GetAgentByCity([
            'agent_province' => $province,
            'agent_city'     => $city
        ]);
    }
    if(!$agent->agent_id)
    {
        $agent = $agentmgo->GetAgentByCity([
            'agent_province' => $province
        ]);
    }
    return $agent->agent_id;
}

function SaveShopFoodUnit(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $food_unit_list = json_decode($_['food_unit_list']);
    $shop_id = $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;

    $entry->shop_id        = $shop_id;
    $entry->food_unit_list = $food_unit_list;

    LogDebug($entry);

    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save Attach err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save atache ok");
    return 0;
}

function ShopOpr(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $opr = $_['opr'];
    $shop_id = $_['shop_id'];

    if(ShopIsSuspend::NO != $opr
        && ShopIsSuspend::BY_SYS_ADMIN != $opr
        && ShopIsSuspend::BY_SHOP_ADMIN != $opr)
    {
        LogErr("param err, opr:[$opr]");
        return errcode::PARAM_ERR;
    }

    $mgo = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;

    $entry->shop_id = $shop_id;
    $entry->suspend = $opr;

    LogDebug($entry);

    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save Attach err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save atache ok");
    return 0;
}

function CategorySave($info)
{

    $mongodb = new \DaoMongodb\Category;
    $entry   = new \DaoMongodb\CategoryEntry;

    $entry->category_id   = $info["category_id"];
    $entry->category_name = $info["category_name"];
    $entry->shop_id       = $info['shop_id'];
    $entry->type          = $info["type"];
    $entry->opening_time  = [1,2,3,4];
    $entry->parent_id     = $info["parent_id"];
    $entry->delete        = 0;
    $entry->entry_type    = 1;
    $entry->entry_time    = $info["entry_time"];

    $ret = $mongodb->Save($entry);
    if(0 != $ret)
    {
        LogErr("{$entry->category_name} Save err");
        return errcode::SYS_ERR;
    }
    LogInfo("{$entry->category_name} save ok");
    return 0;
}
// 自动创建系统固定商品分类
function EntryCategory($shop_id)
{
    $info["category_id"]   = \DaoRedis\Id::GenCategoryId();
    $info["category_name"] = "菜品";
    $info["type"]          = 1;
    $info["parent_id"]     = 0;
    $info["entry_time"]    = time();
    $info["shop_id"]       = $shop_id;
    $ret = CategorySave($info);
    if(0 != $ret)
    {
        LogErr("{$info["category_name"]} err");
        return errcode::SYS_ERR;
    }

    $data["category_id"]   = \DaoRedis\Id::GenCategoryId();
    $data["category_name"] = "酒水";
    $data["type"]          = 3;
    $data["parent_id"]     = 0;
    $data["entry_time"]    = time()+1;
    $data["shop_id"]       = $shop_id;
    $ret = CategorySave($data);
    if(0 != $ret)
    {
        LogErr("{$data["category_name"]} err");
        return errcode::SYS_ERR;
    }

    $inf["category_id"]   = \DaoRedis\Id::GenCategoryId();
    $inf["category_name"] = "餐具";
    $inf["type"]          = 2;
    $inf["parent_id"]     = 0;
    $inf["entry_time"]    = time()+2;
    $inf["shop_id"]       = $shop_id;
    $ret = CategorySave($inf);
    if(0 != $ret)
    {
        LogErr("{$inf["category_name"]} err");
        return errcode::SYS_ERR;
    }

    $da["category_id"]   = \DaoRedis\Id::GenCategoryId();
    $da["category_name"] = "餐盒";
    $da["type"]          = 2;
    $da["parent_id"]     = $inf["category_id"];
    $da["entry_time"]    = time()+3;
    $da["shop_id"]       = $shop_id;
    $ret = CategorySave($da);
    if(0 != $ret)
    {
        LogErr("{$da["category_name"]} err");
        return errcode::SYS_ERR;
    }

    LogInfo("save ok");
    return 0;
}

function InviteEmployee($shop_id, $userid, &$info)
{
    $employee_id          = \DaoRedis\Id::GenEmployeeId();
    $mgo                  = new \DaoMongodb\User;
    $userinfo             = $mgo->QueryById($userid);
    $entry                = new DaoMongodb\EmployeeEntry;
    $mgo                  = new DaoMongodb\Employee;
    $entry->userid        = $userid;
    $entry->delete        = 0;
    $entry->shop_id       = $shop_id;
    $entry->phone         = $userinfo->phone;
    $entry->real_name     = $userinfo->real_name;
    $entry->employee_id   = $employee_id;
    $entry->is_admin      = 1;
    $entry->entry_time    = time();
    $ret                  = $mgo->Save($entry);
    if ($ret != 0)
    {
        LogErr("Save err, ret=[$ret]");
        return errcode::SYS_ERR;
    }
    $info = $entry;
    LogInfo("--ok--");
    return 0;
}
//保存自动创建的职位
function PositionSave($info)
{

    $mongodb = new \DaoMongodb\Position;
    $entry   = new \DaoMongodb\PositionEntry;

    $entry->position_id         = $info["position_id"];
    $entry->position_name       = $info["position_name"];
    $entry->position_permission = $info['position_permission'];
    $entry->position_note       = $info["position_note"];
    $entry->shop_id             = $info["shop_id"];
    $entry->entry_type          = 1;
    $entry->delete              = 0;
    $entry->is_start            = 1;
    $entry->ctime               = $info['ctime'];
    $entry->is_edit             = $info['is_edit'];


    $ret = $mongodb->Save($entry);
    if(0 != $ret)
    {
        LogErr("{$entry->position_name} Save err");
        return errcode::SYS_ERR;
    }
    LogInfo("{$entry->position_name} save ok");
    return 0;
}
// 自动创建系统固定职位
function EntryPosition($shop_id)
{

//    $info["position_id"]         = \DaoRedis\Id::GenPositionId();
//    $info["position_name"]       = "管理员";
//    $info["position_permission"] = (object)[
//        "010101" => 1,
//        "010102" => 1,
//        "010103" => 1,
//        "010104" => 1,
//        "010105" => 1,
//        "010106" => 1,
//        "010107" => 1,
//        "010108" => 1,
//        "010201" => 1,
//        "010202" => 1,
//        "010203" => 1,
//        "010204" => 1,
//        "010301" => 1,
//        "010302" => 1,
//        "010303" => 1,
//        "010304" => 1,
//        "010305" => 1,
//        "010306" => 1,
//        "010307" => 1,
//        "010401" => 1,
//        "010402" => 1,
//        "010403" => 1,
//        "010404" => 1,
//        "010405" => 1,
//        "010406" => 1,
//        "010501" => 1,
//        "010502" => 1,
//        "010503" => 1,
//        "010504" => 1,
//        "010505" => 1,
//        "010506" => 1,
//        "010601" => 1,
//        "010602" => 1,
//        "010603" => 1,
//        "010604" => 1,
//        "010701" => 1,
//        "010702" => 1,
//        "010703" => 1,
//        "010704" => 1,
//        "010801" => 1,
//        "010802" => 1,
//        "010803" => 1,
//        "010901" => 1,
//        "010902" => 1,
//        "010903" => 1,
//        "011001" => 1,
//        "011002" => 1,
//        "011003" => 1,
//        "011101" => 1,
//        "011102" => 1,
//        "011201" => 1,
//        "011202" => 1,
//        "011301" => 1,
//        "011302" => 1,
//        "011303" => 1,
//        "011304" => 1,
//        "011305" => 1,
//        "011306" => 1
//    ];
//    $info["shop_id"]             = $shop_id;
//    $info['ctime']               = time();
//    $info['is_edit']             = 0;
//    $ret                         = PositionSave($info);;
//    if (0 != $ret)
//    {
//        LogErr("{$info["position_name"]} err");
//        return errcode::SYS_ERR;
//    }
    $info["position_id"]         = \DaoRedis\Id::GenPositionId();
    $info["position_name"]       = "收银经理";
    $info["position_permission"] = (object)[
        "010101" => 1,
        "010102" => 1,
        "010103" => 1,
        "010104" => 1,
        "010105" => 1,
        "010106" => 1,
        "010107" => 1,
        "010108" => 1,
        "010109" => 1,
        "010201" => 1,
        "010202" => 1,
        "010203" => 1,
        "010204" => 1,
        "010301" => 1,
        "010302" => 1,
        "010303" => 1,
        "010304" => 1,
        "010305" => 1,
        "010306" => 1,
        "010307" => 1,
        "010308" => 1,
        "010401" => 1,
        "010402" => 1,
        "010403" => 1,
        "010404" => 1,
        "010405" => 1,
        "010406" => 1,
        "010501" => 1,
        "010502" => 0,
        "010503" => 0,
        "010504" => 0,
        "010505" => 0,
        "010506" => 0,
        "010601" => 1,
        "010602" => 0,
        "010603" => 0,
        "010604" => 0,
        "010701" => 1,
        "010702" => 0,
        "010703" => 0,
        "010704" => 0,
        "010801" => 1,
        "010802" => 1,
        "010803" => 1,
        "010901" => 1,
        "010902" => 1,
        "010903" => 1,
        "011001" => 1,
        "011002" => 1,
        "011003" => 1,
        "011004" => 1,
        "011101" => 1,
        "011102" => 0,
        "011201" => 1,
        "011202" => 1,
        "011301" => 1,
        "011302" => 1,
        "011303" => 1,
        "011304" => 0,
        "011305" => 0,
        "011306" => 0
    ];
    $info["shop_id"]             = $shop_id;
    $info['ctime']               = time()+1;
    $info['is_edit']             = 1;
    $ret                         = PositionSave($info);;
    if (0 != $ret)
    {
        LogErr("{$info["position_name"]} err");
        return errcode::SYS_ERR;
    }

    $info["position_id"]         = \DaoRedis\Id::GenPositionId();
    $info["position_name"]       = "普通专员";
    $info["position_permission"] = (object)[
        "010101" => 1,
        "010102" => 1,
        "010103" => 1,
        "010104" => 1,
        "010105" => 1,
        "010106" => 1,
        "010107" => 1,
        "010108" => 1,
        "010109" => 1,
        "010201" => 1,
        "010202" => 0,
        "010203" => 0,
        "010204" => 0,
        "010301" => 1,
        "010302" => 1,
        "010303" => 0,
        "010304" => 0,
        "010305" => 0,
        "010306" => 0,
        "010307" => 0,
        "010401" => 1,
        "010402" => 1,
        "010403" => 0,
        "010404" => 0,
        "010405" => 0,
        "010406" => 0,
        "010501" => 0,
        "010502" => 0,
        "010503" => 0,
        "010504" => 0,
        "010505" => 0,
        "010506" => 0,
        "010601" => 0,
        "010602" => 0,
        "010603" => 0,
        "010604" => 0,
        "010701" => 0,
        "010702" => 0,
        "010703" => 0,
        "010704" => 0,
        "010801" => 0,
        "010802" => 0,
        "010803" => 0,
        "010901" => 0,
        "010902" => 0,
        "010903" => 0,
        "011001" => 0,
        "011002" => 0,
        "011003" => 0,
        "011101" => 0,
        "011102" => 0,
        "011201" => 0,
        "011202" => 0,
        "011301" => 0,
        "011302" => 0,
        "011303" => 0,
        "011304" => 0,
        "011305" => 0,
        "011306" => 0
    ];
    $info["shop_id"]             = $shop_id;
    $info['ctime']               = time()+2;
    $info['is_edit']             = 1;
    $ret                         = PositionSave($info);;
    if (0 != $ret)
    {
        LogErr("{$info["position_name"]} err");
        return errcode::SYS_ERR;
    }

    LogInfo("save ok");
    return 0;
}
//更换代理商
function ShopChangeAgent(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id = $_['agent_id'];
    $shop_id  = $_['shop_id'];

    if(!$shop_id || !$agent_id){
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo   = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;

    $entry->shop_id   = $shop_id;
    $entry->agent_id  = $agent_id;
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
//店铺冻结
function ShopFreeze(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id          = $_['shop_id'];
    $is_freeze        = $_['is_freeze'];
    $platform         = $_['platform'];

    if($platform)
    {
        if($is_freeze == IsFreeze::YES)
        {
            PlPermissionCheck::PageCheck(PlPermissionCode::FREEZE_SHOP);
        }else{
            PlPermissionCheck::PageCheck(PlPermissionCode::START_SHOP);
        }
    }else{
        if($is_freeze == IsFreeze::YES)
        {
            AgPermissionCheck::PageCheck(AgentPermissionCode::FREEZE_SHOP);
        }else{
            AgPermissionCheck::PageCheck(AgentPermissionCode::START_SHOP);
        }

    }

    if(!$shop_id){
        LogErr("no shop_id");
        return errcode::PARAM_ERR;
    }

    $entry = new \DaoMongodb\ShopEntry;
    $mgo   = new \DaoMongodb\Shop;
    $entry->shop_id          = $shop_id;
    $entry->is_freeze        = $is_freeze;

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

function SaveShopAuthorize(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $pc_num           = $_['pc_num'];
    $pad_num          = $_['pad_num'];
    $cashier_num      = $_['cashier_num'];
    $app_num          = $_['app_num'];
    $machine_num      = $_['machine_num'];
    $shop_id          = $_['shop_id'];

    $mgo = new \DaoMongodb\Authorize;
    $entry = new \DaoMongodb\AuthorizeEntry;

    $entry->shop_id          = $shop_id;
    $entry->pc_num           = $pc_num;
    $entry->pad_num          = $pad_num;
    $entry->cashier_num      = $cashier_num;
    $entry->app_num          = $app_num;
    $entry->machine_num      = $machine_num;
    //LogDebug($entry);
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
//微信和支付宝对公账户保存
function SavePaySet(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::SHOP_CAT_VERIFY);
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id       = $_['shop_id'];
    $sub_mch_id    = $_['sub_mch_id'];
    $alipay_num    = $_['alipay_num'];
    $public_key    = $_['public_key'];
    $private_key   = $_['private_key'];
    if(!$shop_id)
    {
        LogErr("shop_id err");
        return errcode::SHOP_NOT_WEIXIN;
    }
    $mgo        = new \DaoMongodb\Shop;
    $entry      = new \DaoMongodb\ShopEntry;
    if($sub_mch_id)
    {
        $weixin_pay_set->sub_mch_id = $sub_mch_id;
        $entry->shop_id                        = $shop_id;
        $entry->weixin_pay_set                 = $weixin_pay_set;
        $entry->weixin_seting                  = 1;
    }elseif($alipay_num)
    {
        $alipay_set->alipay_app_id             = $alipay_num;
        $alipay_set->public_key                = $public_key;
        $alipay_set->private_key               = $private_key;
        $entry->shop_id                        = $shop_id;
        $entry->alipay_set                     = $alipay_set;
        $entry->alipay_seting                  = 1;
    }else{
        LogErr('no weixin sub_mch_id or alipay num');
        return errcode::PARAM_ERR;
    }

    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array();
    LogInfo("save ok");
    return 0;
}

// 自动创建店铺资源
function EntryResources($shop_id)
{
    $info["resources_id"]   = \DaoRedis\Id::GenResourcesId();
    $info["shop_id"]        = $shop_id;
    $info["resources_type"] = NewSrcType::SHOUYINJI;
    $ret = ResourcesSave($info);
    if(0 != $ret)
    {
        LogErr("{$info["resources_id"]} err");
        return errcode::SYS_ERR;
    }
    $info["resources_id"]   = \DaoRedis\Id::GenResourcesId();
    $info["shop_id"]        = $shop_id;
    $info["resources_type"] = NewSrcType::SHOUYINJI;
    $ret = ResourcesSave($info);
    if(0 != $ret)
    {
        LogErr("{$info["resources_id"]} err");
        return errcode::SYS_ERR;
    }
    $info["resources_id"]   = \DaoRedis\Id::GenResourcesId();
    $info["shop_id"]        = $shop_id;
    $info["resources_type"] = NewSrcType::SELFHELP;
    $ret = ResourcesSave($info);
    if(0 != $ret)
    {
        LogErr("{$info["resources_id"]} err");
        return errcode::SYS_ERR;
    }
    $info["resources_id"]   = \DaoRedis\Id::GenResourcesId();
    $info["shop_id"]        = $shop_id;
    $info["resources_type"] = NewSrcType::SELFHELP;
    $ret = ResourcesSave($info);
    if(0 != $ret)
    {
        LogErr("{$info["resources_id"]} err");
        return errcode::SYS_ERR;
    }
    $info["resources_id"]   = \DaoRedis\Id::GenResourcesId();
    $info["shop_id"]        = $shop_id;
    $info["resources_type"] = NewSrcType::PAD;
    $ret = ResourcesSave($info);
    if(0 != $ret)
    {
        LogErr("{$info["resources_id"]} err");
        return errcode::SYS_ERR;
    }
    $info["resources_id"]   = \DaoRedis\Id::GenResourcesId();
    $info["shop_id"]        = $shop_id;
    $info["resources_type"] = NewSrcType::PAD;
    $ret = ResourcesSave($info);
    if(0 != $ret)
    {
        LogErr("{$info["resources_id"]} err");
        return errcode::SYS_ERR;
    }
    $info["resources_id"]   = \DaoRedis\Id::GenResourcesId();
    $info["shop_id"]        = $shop_id;
    $info["resources_type"] = NewSrcType::APP;
    $ret = ResourcesSave($info);
    if(0 != $ret)
    {
        LogErr("{$info["resources_id"]} err");
        return errcode::SYS_ERR;
    }
    $info["resources_id"]   = \DaoRedis\Id::GenResourcesId();
    $info["shop_id"]        = $shop_id;
    $info["resources_type"] = NewSrcType::APP;
    $ret = ResourcesSave($info);
    if(0 != $ret)
    {
        LogErr("{$info["resources_id"]} err");
        return errcode::SYS_ERR;
    }
    LogInfo("save ok");
    return 0;
}

function ResourcesSave($info)
{
    $mongodb = new Mgo\Resources;
    $entry   = new Mgo\ResourcesEntry;
    $entry->resources_id     = $info['resources_id'];
    $entry->shop_id          = $info['shop_id'];
    $entry->resources_type   = $info['resources_type'];
    $entry->valid_begin_time = 0;
    $entry->valid_end_time   = 9999999999;
    $entry->ctime            = time();
    $entry->last_use_time    = 0;
    $ret = $mongodb->Save($entry);
    if(0 != $ret)
    {
        LogErr("{$entry->resources_id} Save err");
        return errcode::SYS_ERR;
    }
    LogInfo("{$entry->resources_type} save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['shop_save']))
{
    $ret = SaveShopinfo($resp);
}
else if(isset($_['save_shop_user']))
{
    $ret = SaveUserinfo($resp);
}
else if(isset($_['save_shop_sign']))
{
    $ret = SaveShopSigninfo($resp);
}
else if(isset($_['opr_shop']))
{
    $ret = ShopOpr($resp);
}elseif(isset($_['shop_change_agent']))
{
    $ret = ShopChangeAgent($resp);
}elseif(isset($_['bs_status_save']))
{
    $ret = ShopBsStatus($resp);
}elseif(isset($_['shop_is_freeze']))
{
    $ret = ShopFreeze($resp);
}
else if(isset($_['save_authorize']))
{
    $ret = SaveShopAuthorize($resp);
}
else if(isset($_['save_pay_set']))
{
    $ret = SavePaySet($resp);
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
