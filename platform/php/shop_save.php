<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 新建店铺生成的数据文件
 */
require_once("current_dir_env.php");
require_once("mgo_shop.php");
require_once("mgo_category.php");
require_once("permission.php");
require_once("mgo_position.php");
require_once("mgo_platformer.php");
require_once("mgo_ag_employee.php");
require_once("mgo_authorize.php");
require_once("mgo_stat_platform_byday.php");
require_once("mgo_stat_shop_byday.php");
require_once("mgo_stat_agent_byday.php");

require_once("redis_id.php");
//Permission::PageCheck();
// 创建店铺第一步:创建用户
function SaveUserinfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $userid      = $_['shop_userid'];
    $real_name   = $_['real_name'];
    $phone       = $_['phone'];
    $password    = $_['password'];
    $re_password = $_['re_password'];
    $sex         = $_['sex'];

    if(!$real_name || !$phone || !$password || !isset($sex))
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    // 验证手机格式
    if (!preg_match('/^1[34578]\d{9}$/', $phone)) {
        LogErr("Phone err");
        return errcode::PHONE_ERR;
    }
    // 验证2次输入密码
    if ($password != $re_password) {
        LogErr("password err");
        return errcode::PASSWORD_TWO_SAME;
    }
    $mgo = new \DaoMongodb\User;
    $user = $mgo->QueryByPhone($phone, UserSrc::SHOP);
    if($user->userid && $user->userid != $userid)
    {
        LogErr("Phone err");
        return errcode::USER_HAD_REG;
    }
    $entry = new \DaoMongodb\UserEntry;
    if(!$userid)
    {
      $userid = \DaoRedis\Id::GenUserId();
      $entry->ctime     = time();
    }
    $entry->userid    = $userid;
    $entry->real_name = $real_name;
    $entry->phone     = $phone;
    $entry->password  = $password;
    $entry->sex       = $sex;
    $entry->src       = UserSrc::SHOP;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
   
    $resp = (object)array(
        'userid' => $userid
    );
    LogInfo("save ok");
    return 0;
}
// 创建店铺第二步:创建店铺
function SaveShopinfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id    = $_['shop_id'];
    $userid     = $_['shop_userid'];
    $shop_name  = $_['shop_name'];
    $shop_logo  = $_['shop_logo'];
    $shop_area  = $_['shop_area'];
    $address    = $_['address'];
    $shop_model = $_['shop_model'];
    $telephone  = $_['telephone'];
    $province   = $_['province'];
    $city       = $_['city'];
    $area       = $_['area'];
    $agent_id   = $_['agent_id'];
    if(!$shop_name || !$shop_logo || !$shop_area || !$address || !$shop_model || !$telephone || !$userid)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo        = new \DaoMongodb\Shop;
    $entry      = new \DaoMongodb\ShopEntry;
    $platformer = new \DaoMongodb\StatPlatform;
    if(!$shop_id)
    {
        $shop_id = \DaoRedis\Id::GenShopId();
        $entry->ctime         = time();
        $entry->is_signing    = 0;
        $entry->logo_img_time = time();
        $entry->is_freeze     = 0;
        $entry->business_status= 0; //工商认证状态变成0
    }
    $info = $mgo->GetShopById($shop_id);
    $img  = $info->shop_logo;
    if($shop_logo != $img)
    {
       if(($info->logo_img_time+30*24*60*60)>=time())
       {
           LogErr("The logo was changed less than a month.");
           return errcode::IMG_NOT_MORE;
       }
    }
    $entry->shop_id    = $shop_id;
    $entry->shop_name  = $shop_name;
    $entry->shop_logo  = $shop_logo;
    $entry->shop_area  = $shop_area;
    $entry->address    = $address;
    $entry->shop_model = $shop_model;
    $entry->telephone  = $telephone;
    $entry->province   = $province;
    $entry->city       = $city;
    $entry->area       = $area;
    $entry->agent_id   = $agent_id;
    

    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    if(!$_['shop_id'])
    {   

        //保存新增商户统计数据
        $day          = date('Ymd',time());
        $platform_id  = 1;//现在只有一个运营平台id
        $new_shop_num = 1;
        $platformer->SellNumAdd($platform_id, $day, ['new_shop_num'=>$new_shop_num]);
        
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

        // 创建登录授权(默认数量为2)
        $au_mgo = new \DaoMongodb\Authorize;
        $data = new \DaoMongodb\AuthorizeEntry;
        $data->shop_id          = $shop_id;
        $data->pc_num           = 2;
        $data->pad_num          = 2;
        $data->cashier_num      = 2;
        $data->app_num          = 2;
        $data->used_pc_num      = 0;
        $data->used_pad_num     = 0;
        $data->used_cashier_num = 0;
        $data->used_app_num     = 0;

        $ret = $au_mgo->Save($data);
        if(0 != $ret)
        {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
    }
    $resp = (object)array(
        'shop_id' => $shop_id
    );
    LogInfo("save ok");
    return 0;
}
// 创建店铺三步:修改店铺签约信息
function SaveShopSigninfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $platformer_id  = $_['platformer_id'];
    $ag_employee_id = $_['ag_employee_id'];
    $shop_id        = $_['shop_id'];
    $agent_type     = $_['agent_type'];
    $agent_id       = $_['agent_id'];
    $from           = $_['from'];
    $from_salesman  = $_['from_salesman'];
    if(!$shop_id || !$agent_type || !$from || !$from_salesman)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    if((!$ag_employee_id && !$platformer_id) || ($ag_employee_id && $platformer_id))
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo   = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;
    //保存新增商户数据
    $platformer  = new \DaoMongodb\StatPlatform;
    $mongodb     = new \DaoMongodb\StatAgent;
    $day         = date('Ymd',time());
    $platform_id = 1;//现在只有一个运营平台id
    if(2 == $agent_type || $agent_id)
    {
        $entry->agent_id = $agent_id;
        //保存区域数据
        $num['new_shop_num'] = 1;
        $mongodb->SellAgentNumAdd($agent_id, $day, $num);
        //保存平台数据
        $industry_shop_num = 1;
        $platformer->SellNumAdd($platform_id, $day, ['industry_shop_num'=> $industry_shop_num]);
    }
    else
    {
        $shopinfo = $mgo->GetShopById($shop_id);
        // $agentmgo = new \DaoMongodb\Agent;
        // $agent = $agentmgo->GetAgentByCity([
        //     'agent_province' => $shopinfo->province,-
        //     'agent_city'     => $shopinfo->city,
        //     'agent_area'     => $shopinfo->area
        // ]);
        // $entry->agent_id = $agent->agent_id;
        $agent_r_id      = GetAgentByShopCity($shopinfo->province, $shopinfo->city, $shopinfo->area);
        if($agent_r_id)
        {
            $num['new_shop_num'] = 1;
            $mongodb->SellAgentNumAdd($agent_r_id , $day, $num);
            //保存平台数据
            $region_shop_num     = 1;
            $platformer->SellNumAdd($platform_id, $day, ['region_shop_num' => $region_shop_num]);
        }
        else
        {
           $agent_r_id = "0";
        }
        $entry->agent_id = $agent_r_id;
        //保存行业数据
        
    }

    $entry->shop_id       = $shop_id;
    $entry->agent_type    = $agent_type;
    $entry->from          = $from;
    $entry->from_salesman = $from_salesman;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    // 更新缓存
    \Cache\Shop::Clear($shop_id);
    // 平台员工保存输入记录
    if($platformer_id)
    {
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
    }
    // 代理商员工保存输入记录
    if($ag_employee_id)
    {
        $age_mgo = new \DaoMongodb\AGEmployee;
        $age_info = new \DaoMongodb\AGEmployeeEntry;
        $age_info->ag_employee_id  = $ag_employee_id;
        $age_info->from_record     = $from;
        $age_info->salesman_record = $from_salesman;
        $ret = $age_mgo->Save($age_info);
        if(0 != $ret)
        {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
    }

    $resp = (object)array(
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
    Permission::EmployeePermissionCheck(
        Permission::CHK_FOOD_W
    );
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
    $employee_id = \DaoRedis\Id::GenEmployeeId();
    $mgo = new \DaoMongodb\User;
    $userinfo = $mgo->QueryById($userid);
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

    $info["position_id"]         = \DaoRedis\Id::GenPositionId();
    $info["position_name"]       = "管理员";
    $info["position_permission"] = 3;
    $info["shop_id"]             = $shop_id;
    $info['ctime']               = time();
    $info['is_edit']             = 0;
    $ret                         = PositionSave($info);;
    if (0 != $ret)
    {
        LogErr("{$info["position_name"]} err");
        return errcode::SYS_ERR;
    }
    $info["position_id"]         = \DaoRedis\Id::GenPositionId();
    $info["position_name"]       = "店长";
    $info["position_permission"] = 3;
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
    $info["position_name"]       = "收银员";
    $info["position_permission"] = 2;
    $info["shop_id"]             = $shop_id;
    $info['ctime']               = time()+2;
    $info['is_edit']             = 1;
    $ret                         = PositionSave($info);;
    if (0 != $ret)
    {
        LogErr("{$info["position_name"]} err");
        return errcode::SYS_ERR;
    }

    $info["position_id"]         = \DaoRedis\Id::GenPositionId();
    $info["position_name"]       = "服务员";
    $info["position_permission"] = 2;
    $info["shop_id"]             = $shop_id;
    $info['ctime']               = time()+3;
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
// 设置PAD端基础设置到服务器
function SyncBaseSettings(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id = \DaoRedis\Id::GenShopId();
    $shop_name     = $_['shop_name'];
    $shop_logo     = $_['shop_logo'];
    $shop_area     = $_['shop_area'];
    $address       = $_['address'];
    $shop_model    = $_['shop_model'];
    $telephone     = $_['telephone'];
    $token         = $_['token'];
    if(!$shop_name || !$shop_logo || !$shop_area || !$address || !$shop_model || !$telephone){
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;

    $entry->shop_id        = $shop_id;
    $entry->shop_name      = $shop_name;
    $entry->shop_logo      = $shop_logo;
    $entry->shop_area      = $shop_area;
    $entry->address        = $address;
    $entry->shop_model     = $shop_model;
    $entry->telephone      = $telephone;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    // 添加管理员
    $employee = (object)array();
    $ret = InviteEmployee($shop_id, $employee);
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
    $redis = new \DaoRedis\Login();
    $info = new \DaoRedis\LoginEntry();

    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->shop_id  = $shop_id;
    LogDebug($info);

    $ret = $redis->Save($info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
        'employeeinfo' => $employee,
        'shopinfo' => $entry
    );
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
//店铺工商信息认证
function ShopBsStatus(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id          = $_['shop_id'];
    $business_status  = $_['business_status'];
    if(!$shop_id){
        LogErr("no shop_id");
        return errcode::PARAM_ERR;
    }
    $mgo   = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;

    $entry->shop_id          = $shop_id;
    $entry->business_status  = $business_status;
    $entry->shop_sh_time     = time();

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
    if(!$shop_id){
        LogErr("no shop_id");
        return errcode::PARAM_ERR;
    }
    $mgo   = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;

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
    $shop_id          = $_['shop_id'];

    $mgo = new \DaoMongodb\Authorize;
    $entry = new \DaoMongodb\AuthorizeEntry;

    $entry->shop_id          = $shop_id;
    $entry->pc_num           = $pc_num;
    $entry->pad_num          = $pad_num;
    $entry->cashier_num      = $cashier_num;
    $entry->app_num          = $app_num;

    LogDebug($entry);

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
