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
require_once("mgo_stat_platform_byday.php");
//Permission::PageCheck();
Permission::PageCheck($_['srctype']);
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
    $mgo   = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;
    //保存新增商户统计数据
    $platformer   = new \DaoMongodb\StatPlatform;
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
    $entry->ctime          = time();
    $entry->suspend        = 0; //正常使用
    $entry->business_status= 0; //工商认证状态变成0
    $entry->is_signing     = 0; //0未签约
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
    $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Authorize;
    $entry = new \DaoMongodb\AuthorizeEntry;

    $entry->shop_id          = $shop_id;
    $entry->used_pc_num      = $used_pc_num;
    $entry->used_pad_num     = $used_pad_num;
    $entry->used_cashier_num = $used_cashier_num;
    $entry->used_app_num     = $used_app_num;

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
