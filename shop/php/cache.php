<?php
/*
 * [Rocky 2016-04-19 10:36:08]
 *
 */
declare(encoding='UTF-8');
namespace Cache;
require_once("const.php");
require_once("/www/shop.sailing.com/php/redis_login.php");
require_once("mgo_user.php");
require_once("mgo_weixin.php");
require_once("mgo_shop.php");
require_once("mgo_category.php");
require_once("mgo_seat.php");
require_once("mgo_printer.php");
require_once("mgo_employee.php");
require_once("mgo_customer.php");
require_once ("mgo_position.php");
require_once ("mgo_menu.php");
// 登录相关信息
class Login
{
    private static $cache = null;

    static function Get($token)
    {
        if(null != self::$cache)
        {
            return self::$cache;
        }

        $redis = new \DaoRedis\Login();
        self::$cache = $redis->Get($token);
        LogDebug("load logininfo --> cache, data:" . json_encode(self::$cache));
        return self::$cache;
    }

    static function Token()
    {
        return self::$cache->token?:"";
    }

    static function GetKey()
    {
        return self::$cache->key?:"";
    }

    static function GetUserid()
    {
        return self::$cache->userid?:"";
    }

    static function GetUsername()
    {
        return self::$cache->username?:"";
    }

    // 当前登录用户所在店
    static function GetShopId()
    {
        return self::$cache->shop_id?:0;
    }

    static function Clear()
    {
        self::$cache = null;
    }

    // 登录用户
    static function UserInfo()
    {
        return UsernInfo::Get(self::$cache->userid);
    }
}

// 由用户名取相关信息
class UsernameInfo
{
    private static $cache = null;

    static function Get($username)
    {
        if(self::$cache[$username])
        {
            return self::$cache[$username];
        }

        $mgo = new \DaoMongodb\User;
        $info = $mgo->QueryByName($username);
        self::$cache[$username] = $info;
        LogDebug("load userinfo/name --> cache, data:" . json_encode(self::$cache));
        return $info;
    }

    static function GetPasswdPrompt($username)
    {
        $info = self::Get($username);
        return $info->passwd_prompt;
    }
}

// 用户信息
class UsernInfo
{
    private static $cache = [];

    static function Get($userid)
    {
        if(!$userid && self::$cache[$userid])
        {
            return self::$cache[$userid];
        }

        $mgo = new \DaoMongodb\User;
        $info = $mgo->QueryById($userid);
        if($userid != $info->userid)
        {
            LogErr("userinfo err, userid:[$userid], " . json_encode($info));
            return null;
        }
        self::$cache[$userid] = $info;
        LogDebug("load userinfo/id --> cache, data:" . json_encode(self::$cache));
        return $info;
    }
}

// shop信息
class Shop
{
    private static $cache = [];

    static function Get($shop_id)
    {
        if(!$shop_id)
        {
            return null;
        }
        $info = &self::$cache[$shop_id];
        if(null != $info)
        {
            return $info;
        }
        $mgo = new \DaoMongodb\Shop;
        $info = $mgo->GetShopById($shop_id);
        if($shop_id != $info->shop_id)
        {
            LogErr("shopinfo err, shop_id:[$shop_id], " . json_encode($info));
            return null;
        }
        self::$cache[$shop_id] = $info;
        // LogDebug("load shopinfo --> cache, data:" . json_encode($info)); // 日志太长
        return $info;
    }


    // 当前登录用户所在店
    static function GetShopName($shop_id)
    {
        $info = self::Get($shop_id);
        if(!$info)
        {
            return "";
        }
        return $info->shop_name?:"";
    }
}

//  food_category
class Category
{
    private static $cache = [];

    static function Get($category_id)
    {
        $info = &self::$cache[$category_id];
        if(null != $info)
        {
            return $info;
        }
        $mgo = new \DaoMongodb\Category;
        $info = $mgo->GetCategoryById($category_id);
        if($category_id != $info->category_id)
        {
            LogErr("categoryinfo err, category_id:[$category_id], " . json_encode($info));
            return null;
        }
        self::$cache[$category_id] = $info;
        LogDebug("load categoryinfo --> cache, data:" . json_encode($info));
        return $info;
    }

    // 当前登录用户所在店
    static function GetCategoryName($category_id)
    {
        $info = self::Get($category_id);
        if(!$info)
        {
            return "";
        }
        return $info->category_name?:"";
    }

    static function GetCategoryList($shop_id)
    {
        $mgo = new \DaoMongodb\Category;
        $list = $mgo->GetList($shop_id);
        return $list;
    }
}


// 用户信息
class Customer
{
    private static $cache = [];

    static function Get($customer_id)
    {
        if(self::$cache[$customer_id])
        {
            return self::$cache[$customer_id];
        }

        $mgo = new \DaoMongodb\Customer;
        $info = $mgo->QueryById($customer_id);
        if(!$info->customer_id)
        {
            return null;
        }
        self::$cache[$customer_id] = $info;
        LogDebug("load Customer/id --> cache, data:" . json_encode(self::$cache));
        return $info;
    }

    static function IsVip($customer_id)
    {
        $info = self::Get($customer_id);
        if(null == $info)
        {
            return false;
        }
        return $info->is_vip == \IsVipCustomer::YES;
    }
}

// seat信息

// 餐桌信息
class Seat
{
    private static $cache = null;

    static function Get($seat_id)
    {
        $info = &self::$cache[$seat_id];
        if(null != $info)
        {
            return $info;
        }
        $mgo = new \DaoMongodb\Seat;
        $info = $mgo->GetSeatById($seat_id);
        if($seat_id != $info->seat_id)
        {
            LogErr("seatinfo err, seat_id:[$seat_id], " . json_encode($info));
            return null;
        }
        self::$cache[$seat_id] = $info;
        LogDebug("load seatinfo --> cache, data:" . json_encode($info));
        return $info;
    }

    // 当前登录用户所在店
    static function GetSeatName($seat_id)
    {
        $info = self::Get($seat_id);
        if(!$info)
        {
            return "";
        }
        return $info->seat_name?:"";
    }
}


// printer信息
class Printer
{
    private static $cache = [];

    static function Get($printer_id)
    {
        $info = &self::$cache[$printer_id];
        if(null != $info)
        {
            return $info;
        }
        $mgo = new \DaoMongodb\Printer;
        $info = $mgo->GetPrinterById($printer_id);
        if($printer_id != $info->printer_id)
        {
            LogErr("printer err, printer_id:[$printer_id], " . json_encode($info));
            return null;
        }
        self::$cache[$printer_id] = $info;
        LogDebug("load printer --> cache, data:" . json_encode($info));
        return $info;
    }
}


// 餐品信息
class Food
{
    private static $cache = [];

    static function Get($food_id)
    {
        $info = &self::$cache[$food_id];
        if(null != $info)
        {
            return $info;
        }
        $mgo = new \DaoMongodb\MenuInfo;
        $info = $mgo->GetFoodinfoById($food_id);
        if($food_id != $info->food_id)
        {
            LogErr("food err, food_id:[$food_id], " . json_encode($info));
            return null;
        }
        self::$cache[$food_id] = $info;
        LogDebug("load food --> cache, data:" . json_encode($info));
        return $info;
    }
}

// 订单信息
class Order
{
    private static $cache = [];

    static function Get($order_id)
    {
        $info = &self::$cache[$order_id];
        if(null != $info)
        {
            return $info;
        }
        $mgo = new \DaoMongodb\Order;
        $info = $mgo->GetOrderById($order_id);
        if($order_id != $info->order_id)
        {
            LogErr("order err, order_id:[$order_id], " . json_encode($info));
            return null;
        }
        self::$cache[$order_id] = $info;
        // LogDebug("load order --> cache, data:" . json_encode($info)); // 内容太长，关闭
        return $info;
    }

    static function Clear($order_id)
    {
        unset(self::$cache[$order_id]);
    }
}

// 员工信息
class Employee
{
    private static $cache = [];

    static function Get($userid)
    {
        $info = &self::$cache[$userid];
        if(null != $info)
        {
            return $info;
        }
        $mgo = new \DaoMongodb\Employee;
        $info = $mgo->GetEmployeeById($userid);
        if($userid != $info->userid)
        {
            LogErr("employee err, userid:[$userid], " . json_encode($info));
            return null;
        }
        self::$cache[$userid] = $info;
        LogDebug("load employee --> cache, data:" . json_encode($info));
        return $info;
    }
    static function GetInfo($userid,$shop_id)
    {
        $key = "$userid#$shop_id";
        $info = &self::$cache[$key];

        if(null != $info)
        {
            return $info;
        }
        $mgo = new \DaoMongodb\Employee;
        $info = $mgo->QueryByShopId($userid, $shop_id);
        if($userid != $info->userid && $shop_id != $info->shop_id)
        {
            LogErr("employee err, userid:[$userid], shopid:[$shop_id], " . json_encode($info));
            return null;
        }
        self::$cache[$key] = $info;
        LogDebug("load employee --> cache, data:" . json_encode($info));
        return $info;
    }
}
//权限信息
class Position{
    private static $cache=[];

    static  function Get($shop_id, $position_id)
    {
        $info=&self::$cache[$position_id];
        if(null != $info){
            return $info;
        }
        $mgo = new \DaoMongodb\Position;
        $info = $mgo->GetPositionById($shop_id, $position_id);
        if($position_id != $info->position_id && $shop_id != $info->shop_id)
        {
            //如果没有留言id给提示错误信息
            LogErr("position err,content_id:[$position_id],".json_encode($info));
            return null;
        }
        self::$cache[$position_id] = $info;
        //返回正确信息
        LogDebug("load content --> cache , data:".json_encode($info));
        return $info;

    }
}

// 微信用户信息
class Weixin
{
    private static $cache = [];

    static function Get($openid, $src)
    {
        $key = "$openid#$src";
        $info = &self::$cache[$key];
        if(null != $info)
        {
            return $info;
        }
        $mgo = new \DaoMongodb\Weixin;
        $info = $mgo->QueryByOpenId($openid, $src);
        if($openid != $info->openid)
        {
            LogErr("weixin_user err, openid:[$openid], " . json_encode($info));
            return null;
        }
        self::$cache[$key] = $info;
        LogDebug("load weixin_user --> cache, data:" . json_encode($info));
        return $info;
    }
    static function GetUser($userid)
    {
        $info = &self::$cache[$userid];
        if(null != $info)
        {
            return $info;
        }
        $mgo = new \DaoMongodb\Weixin;
        $info = $mgo->QueryByUserId($userid);
        if($userid != $info->userid)
        {
            LogErr("weixin_user err, userid:[$userid], " . json_encode($info));
            return null;
        }
        self::$cache[$userid] = $info;
        LogDebug("load weixin_user --> cache, data:" . json_encode($info));
        return $info;
    }

}

?>
