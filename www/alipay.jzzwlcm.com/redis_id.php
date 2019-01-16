<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * id操作
 */
declare(encoding='UTF-8');
namespace DaoRedis;
require_once("db_pool.php");
require_once("redis_public.php");


class Id
{
    static private function Tablename()
    {
        return DB_ID;  // 注意各个表使用序号
    }

    static private function GenId($type)
    {
        $db = \DbPool::GetRedis(self::Tablename());
        // $ret = $db->incr($type);
        $ret = $db->incrby($type, mt_rand(1, 9));  // 递增一随机值
        LogDebug("$ret");
        return $ret;
    }

    static public function GenUserId()
    {
        return (int)self::GenId("userid");
    }

    static public function GenLoginId()
    {
        return (int)self::GenId("login");
    }

    static public function GenFoodId()
    {
        return (int)self::GenId("food");
    }

    static public function GenCategoryId()
    {
        return (int)self::GenId("category");
    }

    static public function GenPrinterId()
    {
        return (int)self::GenId("printer");
    }

    static public function GenShopId()
    {
        return (int)self::GenId("shop");
    }
    static public function GenBroadcastId()
    {
        return (int)self::GenId("broadcast");
    }
    static public function GenEvaluationId()
    {
        return (int)self::GenId("evaluation");
    }
    static public function GenOrderId()
    {
        return (int)self::GenId("order");
    }

    // 订单中餐品id
    // Rocky 2017-07-10 12:25:32
    static public function GenOrderFoodId()
    {
        return (int)self::GenId("order_food");
    }

    static public function GenCustomerId()
    {
        return self::GenId("customer");
    }

    static public function GenSeatId()
    {
        return (int)self::GenId("seat");
    }

    static public function GenSpecId()
    {
        return (int)self::GenId("spec");
    }

    static public function GenSubSpecId()
    {
        return (int)self::GenId("sub_spec");
    }

    static public function GenInvoiceId()
    {
        return (int)self::GenId("invoice");
    }

    static public function GenWeixinId()
    {
        return (int)self::GenId("weixin");
    }
    static public function GenAlipayId()
    {
        return "AL" .self::GenId("alipay");
    }

}


?>
