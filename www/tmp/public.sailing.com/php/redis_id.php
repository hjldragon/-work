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
        LogDebug("$type: $ret");
        return $ret;
    }

    static public function GenUserId()
    {
        return (int)self::GenId("user");
    }

    static public function GenLoginId()
    {
        return "LO" . self::GenId("login");
    }

    static public function GenFoodId()
    {
        return "FO" . self::GenId("food");
    }

    static public function GenCategoryId()
    {
        return "CA" . self::GenId("category");
    }

    static public function GenPrstringerId()
    {
        return "PS" . self::GenId("prstringer");
    }
    static public function GenPrinterId()
    {
        return "PR" . self::GenId("printer");
    }
    static public function GenShopId()
    {
        return "SH" . self::GenId("shop");
    }

    static public function GenOrderId()
    {
        return "SL" . self::GenId("order");
    }
    static public function GenOrderStatusId()
    {
        return "OS" . self::GenId("order_status");
    }
    // 订单中餐品id
    // Rocky 2017-07-10 12:25:32
    static public function GenOrderFoodId()
    {
        return "OF" . self::GenId("order_food");
    }

    static public function GenCustomerId()
    {
        return "CU" . self::GenId("customer");
    }

    static public function GenSeatId()
    {
        return "SE" . self::GenId("seat");
    }

    static public function GenSpecId()
    {
        return "SP" . self::GenId("spec");
    }

    static public function GenSubSpecId()
    {
        return "SS" . self::GenId("sub_spec");
    }

    static public function GenInvoiceId()
    {
        return "IN" . self::GenId("invoice");
    }
    static public function GenDepartmentId()
    {
        return "DE" . self::GenId("department");
    }
    static public function GenEmployeeId()
    {
        return "EM" . self::GenId("employee");
    }
    static public function GenPositionId()
    {
        return "PO" . self::GenId("position");
    }
    static public function GenReservationId()
    {
        return "RE" . self::GenId("reservation");
    }
    static public function GenNewsId()
    {
        return "NE" . self::GenId("news");
    }
    static public function GenNewsReadyId()
    {
        return "NS" . self::GenId("newsready");
    }
    static public function GenOrderWNId()
    {
        return "IN" . self::GenId("invoice");
    }
    static public function GenAgentId()
    {
        return "AG" . self::GenId("agent");
    }
    static public function GenAGEmployeeId()
    {
        return "AGEM" . self::GenId("ag_employee");
    }
    static public function GenPlatformerId()
    {
        return "PL" . self::GenId("platformer");
    }
    static public function GenAgentApplyId()
    {
        return "AA" . self::GenId("agent_apply");
    }
    static public function GenFeedbackId()
    {
        return "FB" . self::GenId("user_feedback");
    }
    static public function GenArticleId()
    {
        return "AE" . self::GenId("article");
    }
    static public function GenPlDepartmentId()
    {
        return "PD" . self::GenId("pl_department");
    }
    static public function GenGoodsId()
    {
        return "GD" . self::GenId("goods");
    }
    static public function GenGoodsCategoryId()
    {
        return "GC" . self::GenId("goods_category");
    }
    static public function GenPlPositionId()
    {
        return "PP" . self::GenId("pl_position");
    }
    static public function GenAgDepartmentId()
    {
        return "AD" . self::GenId("ag_department");
    }
    static public function GenAgPositionId()
    {
        return "AP" . self::GenId("ag_position");
    }
    static public function GenShareId()
    {
        return "SA" . self::GenId("share");
    }
    static public function GenVersionId()
    {
        return "VN" . self::GenId("version");
    }
    static public function GenEvaluationId()
    {
        return "EV" .self::GenId("evaluation");
    }
    static public function GenWeixinId()
    {
        return "WX" .self::GenId("weixin");
    }
    static public function GenAlipayId()
    {
        return "AL" .self::GenId("alipay");
    }
    static public function GenAgentPayId()
    {
        return "PR" .self::GenId("pay_record");
    }
    static public function GenAuditId()
    {
        return "AI" .self::GenId("audit_person");
    }
    static public function GenCityId()
    {
        return "CT" .self::GenId("city");
    }
    static public function GenExpressCompanyId()
    {
        return "EC" .self::GenId("express_company");
    }
    static public function GenAddressId()
    {
        return "ADS" .self::GenId("address");
    }
    static public function GenFreightId()
    {
        return "GF" .self::GenId("freight");
    }
    static public function GenFromId()
    {
        return "FI" .self::GenId("from");
    }
    static public function GenAgentSedId()
    {
        return "FI" .self::GenId("AS");
    }
    static public function GenGoodsOrderId()
    {
        return "GO" .self::GenId("goods_order");
    }
    static public function GenGoodEvaluationId()
    {
        return "GEV" .self::GenId("goods_evaluation");
    }
    static public function GenResourcesId()
    {
        return "RES" . self::GenId("resources");
    }
    static public function GenTermBindingId()
    {
        return "TB" .self::GenId("term_binding");
    }
    static public function GenPlRoleId()
    {
        return "PR" .self::GenId("pl_role");
    }
    static public function GenAgRoleId()
    {
        return "AR" .self::GenId("ag_role");
    }
    static public function KitchenStallId()
    {
        return "KS" .self::GenId("kitchen_stall");
    }
    static public function VendorId()
    {
        return "VI" .self::GenId("vendor");
    }
}


?>
