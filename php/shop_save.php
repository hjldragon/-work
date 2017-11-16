<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 店铺信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_shop.php");
require_once("mgo_category.php");
require_once("permission.php");
require_once("mgo_position.php");
require_once("redis_id.php");
Permission::PageCheck();
function SaveShopinfo(&$resp)
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
    $address_num   = $_['address_num'];
    $address       = $_['address'];
    $shop_model    = $_['shop_model'];
    if(!$shop_name || !$shop_logo || !$shop_area || !$address_num){
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;

    $entry->shop_id        = $shop_id;
    $entry->shop_name      = $shop_name;
    $entry->shop_logo      = $shop_logo;
    $entry->address_num    = $address_num;
    $entry->shop_area      = $shop_area;
    $entry->address        = $address;
    $entry->shop_model     = $shop_model;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    // 添加管理员
    $ret = InviteEmployee($shop_id);
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
    $resp = (object)array(
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
    $inf["category_name"] = "配件";
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

function InviteEmployee($shop_id)
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
