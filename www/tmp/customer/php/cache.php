<?php
/*
 * [Rocky 2016-04-19 10:36:08]
 *
 */
declare(encoding='UTF-8');
namespace Cache;
require_once("current_dir_env.php");
require_once("redis_login.php");
require_once("mgo_customer.php");
require_once("mgo_shop.php");
require_once("mgo_seat.php");
require_once("mgo_order.php");
require_once("mgo_category.php");
require_once("mgo_user.php");
require_once("mgo_evaluation.php");
require_once("mgo_menu.php");
require_once("mgo_invoice.php");
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
}
//User用户登录信息
class User
{
    private static $cache = null;

    static function Get($userid)
    {
        if(self::$cache[$userid])
        {
            return self::$cache[$userid];
        }

        $mgo = new \DaoMongodb\User;
        $info = $mgo->QueryById($userid);
        if(!$info->userid)
        {
            return null;
        }
        self::$cache[$userid] = $info;
        LogDebug("load userid/id --> cache, data:" . json_encode(self::$cache));
        return $info;
    }
}
// 用户信息
class Customer
{
    private static $cache = null;
    private static $openid2info = null;
    private static $useridinfo = null;

    static function Get($customer_id)
    {
        if(self::$cache[$customer_id])
        {
            return self::$cache[$customer_id];
        }

        $mgo  = new \DaoMongodb\Customer;
        $info = $mgo->QueryById($customer_id);
        if(!$info->customer_id)
        {
            return null;
        }
        self::$cache[$customer_id] = $info;
        LogDebug("load Customer/id --> cache, data:" . json_encode(self::$cache));
        return $info;
    }

    static function GetInfoByOpenidShopid($openid, $shop_id)
    {
        $key = "$openid#$shop_id";
        if(self::$openid2info[$key])
        {
            return self::$openid2info[$key];
        }

        $mgo  = new \DaoMongodb\Customer;
        $info = $mgo->QueryByOpenidShopid($openid, $shop_id);
        if(!$info->customer_id)
        {
            return null;
        }
        self::$openid2info[$key] = $info;
        LogDebug("load Customer[$key] --> openid2info, data:" . json_encode(self::$openid2info));
        //打印$key=sdfwfnwe142#4,这是opendid和餐桌seat号，后面的data是用户登录信息
        return $info;
    }
    static function GetInfoByUseridShopid($userid, $shop_id)
    {
        $key = "$userid#$shop_id";
        if(self::$openid2info[$key])
        {
            return self::$openid2info[$key];
        }

        $mgo  = new \DaoMongodb\Customer;
        $info = $mgo->QueryByUseridShopid($userid, $shop_id);
        if(!$info->customer_id)
        {
            return null;
        }
        self::$useridinfo[$key] = $info;
        LogDebug("load Customer[$key] --> openid2info, data:" . json_encode(self::$useridinfo));
        //打印$key=sdfwfnwe142#4,这是opendid和餐桌seat号，后面的data是用户登录信息
        return $info;
    }
}
// shop信息
class Shop
{
    private static $cache = null;

    static function Get($shop_id)
    {
        if(null != self::$cache && $shop_id == $cache->shop_id)
        {
            return self::$cache;
        }

        $mgo = new \DaoMongodb\Shop();
        self::$cache = $mgo->GetShopById($shop_id);
        if($shop_id != self::$cache->shop_id)
        {
            LogErr("get shopinfo err, shop_id:[$shop_id]");
            self::$cache = null;
            return null;
        }
        // LogDebug("load shopinfo --> cache, data:" . json_encode(self::$cache)); // 日志太长，关闭
        return self::$cache;
    }

    // 当前登录用户所在店
    static function GetShopName($shop_id)
    {
        self::Get($shop_id);//调用上面的GET，直接只获取商品的名称
        if(!self::$cache)
        {
            return "";
        }
        return self::$cache->shop_name?:"";
    }
}
// 餐桌信息
class Seat
{
    private static $cache = null;

    static function Get($seat_id)
    {
        if(null != self::$cache && $seat_id == $cache->seat_id)
        {
            return self::$cache;
        }

        $mgo = new \DaoMongodb\Seat();
        //self::$cache = $mgo->GetSeatById($seat_id);
        $seat_info = $mgo->GetSeatById($seat_id);

        if($seat_id != $seat_info->seat_id)
        {
            LogErr("get seatinfo err, seat_id:[$seat_id]");
            self::$cache = null;
            return null;
        }
        self::$cache = $seat_info;
        LogDebug("load seatinfo --> cache, data:" . json_encode(self::$cache));
        return $seat_info;
    }

    // 当前登录用户所在店
    static function GetSeatName($seat_id)
    {
        self::Get($seat_id);
        if(!self::$cache)
        {
            return "";
        }
        return self::$cache->seat_name?:"";
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
        // LogDebug("load food --> cache, data:" . json_encode($info));// 日志太长，关闭
        return $info;
    }
    static function GetSpecInfo($food_id)
    {
        $mgo = new \DaoMongodb\Spec;
        return $mgo->GetSpecList($food_id);
    }
}
// 订单信息
class Order
{
    private static $cache = [];

    static function Get($order_id)
    {
        if(!$order_id)
        {
            return null;
        }
        $info = &self::$cache[$order_id];
        if(null != $info)
        {
            return $info;
        }
        $mgo  = new \DaoMongodb\Order;
        $info = $mgo->GetOrderById($order_id);
        if($order_id != $info->order_id)
        {
            LogErr("order err, order_id:[$order_id], " . json_encode($info));
            return null;
        }
        self::$cache[$order_id] = $info;
        LogDebug("load order --> cache, data:" . json_encode($info));
        return $info;
    }
}
// 发票信息
class Invoice
{
    private static $cache = [];

    static function Get($userid)
    {
        if(!$userid)
        {
            return null;
        }
        $info = &self::$cache[$userid];
        if(null != $info)
        {
            return $info;
        }

        $mgo  = new \DaoMongodb\Invoice;
        $info = $mgo->GetInvoiceByUserid($userid);
        if($userid != $info->userid)
        {
            LogErr("invoice err, invoice_id:[$userid], " . json_encode($info));
            return null;
        }
        self::$cache[$userid] = $info;
        LogDebug("load invoice --> cache, data:" . json_encode($info));
        return $info;
    }
}
//菜单分类信息
class Category
{
    private static $cache = null;

    static function GetCategoryList($shop_id)
    {
        if(self::$cache[$shop_id])
        {
            return self::$cache[$shop_id];
        }

        $mgo  = new \DaoMongodb\Category;
        $info = $mgo->GetList($shop_id);
        self::$cache[$shop_id] = $info;
        // LogDebug("load category/shop_id --> cache, data:" . json_encode(self::$cache)); // 日志太长，关闭
        return $info;
    }

    static function Get($category_id)
    {
        $info = &self::$cache[$category_id];
        if(null != $info)
        {
            return $info;
        }

        $mgo  = new \DaoMongodb\Category;
        $info = $mgo->GetCategoryById($category_id);
        if($category_id != $info->category_id)
        {
            LogErr("category err, category_id:[$category_id], " . json_encode($info));
            return null;
        }
        LogDebug("load category --> cache, data:" . json_encode($info));
        return $info;
    }
}
//餐品评价信息
class Evaluation
{
    private static $cache = null;

    static function GetEvaluationList($food_id,$page_sie,$page_no)
    {
        if(self::$cache[$food_id])
        {
            return self::$cache[$food_id];
        }

        $mgo  = new \DaoMongodb\Evaluation;
        $info = $mgo->GetFoodIdByList($food_id, $page_sie, $page_no);
        self::$cache[$food_id] = $info;
        //LogDebug("load Evaluation/food_id --> cache, data:" . json_encode(self::$cache));
        return $info;
    }
}
?>
