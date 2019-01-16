<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 新建店铺生成的数据文件
 */
require_once("current_dir_env.php");
require_once("mgo_shop.php");
require_once("mgo_category.php");
require_once("mgo_authorize.php");
require_once("permission.php");
require_once("mgo_position.php");
require_once("redis_id.php");
require_once("redis_login.php");
require_once("mgo_authorize.php");
require_once("/www/public.sailing.com/php/mgo_stat_platform_byday.php");
require_once("mgo_department.php");
require_once("mgo_resources.php");
use \Pub\Mongodb as Mgo;
function aa()//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<手动加店铺默认资源
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = $_['shop_id'];
    EntryResources($shop_id);
    return 0;
}
function SaveShopinfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid   = \Cache\Login::GetUserid();
    $emmgo    = new DaoMongodb\Employee;
    $eminfo   = $emmgo->GetAdminByUserId($userid);
    if($eminfo->userid)
    {
        LogErr("Admin err");
        return errcode::ADMIN_IS_EXIST;
    }
    $shop_id       = \DaoRedis\Id::GenShopId();
    $shop_name     = $_['shop_name'];
    $shop_logo     = $_['shop_logo'];
    $shop_area     = $_['shop_area'];
    $address       = $_['address'];
    $shop_model    = $_['shop_model'];
    $telephone     = $_['telephone'];
    $token         = $_['token'];
    $province      = $_['province'];
    $city          = $_['city'];
    $area          = $_['area'];
    if(!$shop_name  || !$shop_area || !$address || !$shop_model || !$telephone){
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo   = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;
    //保存新增商户统计数据
    $platformer   = new Mgo\StatPlatform;
    $day          = date('Ymd',time());
    $platform_id  = 1;//现在只有一个运营平台id
    $new_shop_num = 1;
    $platformer->SellNumAdd($platform_id, $day, ['new_shop_num'=>$new_shop_num]);

    $entry->shop_id        = $shop_id;
    $entry->shop_name      = $shop_name;
    $entry->shop_logo      = $shop_logo;
    $entry->shop_area      = $shop_area;
    $entry->address        = $address;
    $entry->shop_model     = $shop_model;
    $entry->telephone      = $telephone;
    $entry->province       = $province;
    $entry->city           = $city;
    $entry->area           = $area;
    $entry->logo_img_time  = time();
    $entry->ctime          = time();
    $entry->suspend        = 0; //正常使用
    $entry->business_status= 0; //工商认证状态变成0
    $entry->is_signing     = 0; //0未签约
    $entry->order_clear_time= 24*60*60; //默认订单清理时间24小时(用于后厨通)
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
    // 创建固定资源
    $ret = EntryResources($shop_id);
    if(0 != $ret)
    {
        LogErr("Resources Entry err");
        return errcode::SYS_ERR;
    }
    // 创建登录授权(默认数量为2)
    // $au_mgo = new \DaoMongodb\Authorize;
    // $data   = new \DaoMongodb\AuthorizeEntry;

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
    $employee = (object)array();
    $shopinfo = array();
    // 再查员工表
    $mgo          = new \DaoMongodb\Shop;
    $employee_mgo = new \DaoMongodb\Employee;
    $position     = new \DaoMongodb\Position;
    $employee = $employee_mgo->GetEmployeeById($userid);
    foreach ($employee as $key => $value) {
        if($value->shop_id){
            $shop = $mgo->GetShopById($value->shop_id);
            //$shop =  \Cache\Shop::Get($value->shop_id);
            if($shop->shop_id){
                $shop_info['shop_id']        = $shop->shop_id;
                $shop_info['shop_name']      = $shop->shop_name;
                $shop_info['shop_logo']      = $shop->shop_logo;
                $shop_info['is_freeze']      = $shop->is_freeze;
                if($value->is_admin != 1){
                    $position_info                    = $position->GetPositionById($shop->shop_id, $value->position_id);
                    $department                       = new \DaoMongodb\Department;
                    $department_info                  = $department->QueryByDepartmentId($shop->shop_id, $value->department_id);
                    $shop_info['position_name']       = $position_info->position_name;
                    $shop_info['position_permission'] = $position_info->position_permission;
                    $shop_info['department_name']     = $department_info->department_name;
                    $shop_info['is_myshop']           = 0;
                    //是否是管理员0:不是,1:是
                    $shop_info['employee_is_admin']   = 0;
                }else{
                    $shop_info['position_name']  = '超级管理员';
                    $shop_info['department_name']= '--';
                    $shop_info['is_myshop']      = 1;
                    //是否是管理员
                    $shop_info['employee_is_admin'] = 1;
                }
                array_push($shopinfo, $shop_info);
            }
        }
    }
    $resp = (object)array(
        'employeeinfo' => $employee,
        'shopinfo'     => $entry,
        'shop_list'    => $shopinfo
    );
    LogInfo("save ok");
    return 0;
}

function SaveShopFoodAttach(&$resp)
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
    $food_attach_list = json_decode($_['food_attach_list']);
    $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;

    $entry->shop_id          = $shop_id;
    $entry->food_attach_list = $food_attach_list;

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
    $shop_id = \Cache\Login::GetShopId();

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

function SaveShopAuthorize(&$resp)
{
    // Permission::EmployeePermissionCheck(
    //     Permission::CHK_FOOD_W
    // );
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $used_pc_num      = $_['used_pc_num'];
    $used_pad_num     = $_['used_pad_num'];
    $used_cashier_num = $_['used_cashier_num'];
    $used_app_num     = $_['used_app_num'];
    $used_machine_num = $_['used_machine_num'];
    $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Authorize;
    $entry = new \DaoMongodb\AuthorizeEntry;

    $entry->shop_id          = $shop_id;
    $entry->used_pc_num      = $used_pc_num;
    $entry->used_pad_num     = $used_pad_num;
    $entry->used_cashier_num = $used_cashier_num;
    $entry->used_app_num     = $used_app_num;
    $entry->used_machine_num = $used_machine_num;

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

function ShopOpr(&$resp)
{
    // Permission::EmployeePermissionCheck(
    //     Permission::CHK_FOOD_W
    // );
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
    //LogDebug($entry);
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

function InviteEmployee($shop_id, &$info)
{
    $employee_id = \DaoRedis\Id::GenEmployeeId();
    $userinfo = \Cache\Login::UserInfo();
    $entry                = new DaoMongodb\EmployeeEntry;
    $mgo                  = new DaoMongodb\Employee;
    $entry->userid        = $userinfo->userid;
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
    $entry->valid_end_time   = LongTime::AFTERTIME;
    $entry->last_use_time    = 0;
    $entry->ctime            = time();
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
if(isset($_['save']))
{
    $ret = SaveShopinfo($resp);
}
else if(isset($_['save_food_attach_list']))
{
    $ret = SaveShopFoodAttach($resp);
}
else if(isset($_['save_food_unit_list']))
{
    $ret = SaveShopFoodUnit($resp);
}
else if(isset($_['opr_shop']))
{
    $ret = ShopOpr($resp);
}
else if(isset($_['save_authorize']))
{
    $ret = SaveShopAuthorize($resp);
}
else if(isset($_['aa']))//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
{
    $ret = aa();
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
