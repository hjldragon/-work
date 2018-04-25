<?php
/*
 * [Rocky 2016-04-19 10:36:08]
 *
 */
declare(encoding='UTF-8');
namespace Cache;
require_once("current_dir_env.php");
require_once("redis_login.php");
require_once("mgo_stat_food_byday.php");
require_once("mgo_weixin.php");
require_once("mgo_order.php");
require_once("mgo_shop.php");
require_once("mgo_customer.php");

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

        $redis = new \DaoRedis\Login;
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
            $info = null;
            return null;
        }
        LogDebug("load order --> cache, data:" . json_encode($info));
        return $info;
    }

    static function Clear($order_id)
    {
        unset(self::$cache[$order_id]);
    }
}

// 微信用户信息
class Weixin
{
    private static $cache = [];

    static function Get($openid, $src)
    {
        $info = &self::$cache[$openid];
        if(null != $info)
        {
            return $info;
        }
        $mgo = new \DaoMongodb\Weixin;
        $info = $mgo->QueryByOpenId($openid, $src);

        if($openid != $info->openid)
        {
            LogErr("weixin_user err, openid:[$openid], " . json_encode($info));
            $info = null;
            return null;
        }
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
            $info = null;
            return null;
        }
        LogDebug("load weixin_user --> cache, data:" . json_encode($info));
        return $info;
    }

    static function GetWeixinUser($userid, $src)
    {
        $info = &self::$cache[$userid];
        if(null != $info)
        {
            return $info;
        }
        $mgo = new \DaoMongodb\Weixin;
        $info = $mgo->QueryByUserIdSrc($userid, $src);

        if($userid != $info->userid)
        {
            LogErr("weixin_user err, userid:[$userid], " . json_encode($info));
            $info = null;
            return null;
        }
        LogDebug("load weixin_user --> cache, data:" . json_encode($info));
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

        LogDebug("load shopinfo --> cache, data:" . json_encode(self::$cache));
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
// 用户信息
class Customer
{
    private static $cache = null;

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

}

?>
