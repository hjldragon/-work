<?php
/*
 * [rockyshi 2014-05-05 13:18:27]
 * 常量定义
 */
require_once("/www/public.sailing.com/php/errcode.php");
// errcode代码统一定义到: /www/public.sailing.com/php/errcode.php
// class errcode
// {
// };

const MAX_FOODIMG_NUM = 5;  // 餐品介绍图片最大数

// 用户属性位
class UserProperty
{
    const SYS_ADMIN     = 1;    // 系统管理员
    const SHOP_USER     = 2;    // 店铺用户
    const COMPANY_USER  = 4;    // 是公司用户

    public static function IsAdmin($property)
    {
        return (UserProperty::SYS_ADMIN & $property) != 0;
    }
}

// js/cfg.js-->OrderStatus
class OrderStatus
{
    const PENDING   = 1;    // 待处理(未支付）
    const CONFIRMED = 2;    // 待支付
    const PAID      = 3;    // 已支付
    const FINISH    = 4;    // 已完成
    const CANCEL    = 5;    // 已作废
    const TIMEOUT   = 6;    // 订单超时
    const PRINTED   = 7;    // 已出单
    const ERR       = 8;    // 订单出错
    const POSTPONED = 9;    // 叫起（即确认下单，但延迟出餐）

    // 是已确认状态
    static function HadConfirmed($status)
    {
        return OrderStatus::CONFIRMED == $status ||
                OrderStatus::PAID == $status ||
                OrderStatus::POSTPONED == $status;
    }
}
//订单确认状态
class OrderSureStatus
{
    const NOSURE    = 1;    // 未下单
    const SURE      = 2;    // 下单
    const SUREPAY   = 3;    // 下单并支付
}
// 打印机规格
// js/cfg.js-->PrinterSpec
class PrinterSize
{
    const SPEC_80MM = 1;      // 80mm
    const SPEC_58MM = 2;      // 58mm
}

// 打印机规格
// js/cfg.js-->PrinterCategory
class PrinterCategory
{
    const PRINT_ALL      = 1;      // 全单打印（适用于给客人的小票）
    const PRINT_FOODNAME = 2;      // 只打印菜名（适用于后厨打印等）
    const PRINT_SPECIFY  = 3;      // 只打印指定菜类别时（如酒水、凉菜等指定类型菜单）
}

// 餐桌状态
class SeatStatus
{
    const VACANT = 0; // 空闲
    const INUSE  = 1; // 使用中
    const ALERT  = 2; // 有提示
}

// 支付方式
class PayWay
{
    const UNKNOWN   = 0;    // 未确定
    const CASH      = 1;    // 现金
    const WEIXIN    = 2;    // 微信支付
    const APAY      = 3;    // 支付宝支付
    const BANK      = 4;    // 银行卡支付
    const GUAZ      = 5;    // 挂账支付
    const EATAFTER  = 6;    // 餐后支付
}

// 是否会员
class IsVipCustomer
{
    const YES = 1;
    const NO  = 0;
}
// 是否支持餐后付
class ISMEALAFTER
{
    const YES = 1;
    const NO  = 0;
}
// 员工职务
class EmployeeDuty
{
    const UNKNOWN         = 0;   // 待定
    const SYS_SHOP_ADMIN  = 1;   // 店铺系统管理员
    const BOSS            = 2;   // 老板
    const GENERAL_MANAGER = 3;   // 总经理
    const SHOP_MANAGER    = 4;   // 店长
    const FOREMAN         = 5;   // 领班
    const WAITER          = 6;   // 服务员


    static function IsShopAdmin($duty)
    {
        return EmployeeDuty::SYS_SHOP_ADMIN == $duty;
    }
}


// 员工权限
// "permission" : {
//         "order_read" : 1,
//         "order_write" : 0,
//         "food_read" : 1,
//         "food_write" : 0,
//         "report_read" : 1
// }
class EmployeePermission
{
    static function AllPermission()
    {
        return [
            "order_read"  => 1,
            "order_write" => 1,
            "food_read"   => 1,
            "food_write"  => 1,
            "report_read" => 1
        ];
    }
    //
    static function HasOrderRead($permission)
    {
        return !!$permission["order_read"];
    }
    static function HasOrderWrite($permission)
    {
        return !!$permission["order_write"];
    }

    //
    static function HasFoodRead($permission)
    {
        return !!$permission["food_read"];
    }
    static function HasFoodWrite($permission)
    {
        return !!$permission["food_write"];
    }

    //
    static function HasReportRead($permission)
    {
        return !!$permission["report_read"];
    }
}


// 本餐品是否需要服务员确认
class NeedWaiterConfirm
{
    const NO   = 0;   // 不需要服务员确认
    const YES  = 1;   // 需要服务员确认
}

// 店铺是否暂停
class ShopIsSuspend
{
    const NO            = 0;    // 正常使用
    const BY_SYS_ADMIN  = 1;    // 被系统管理员暂停
    const BY_SHOP_ADMIN = 2;    // 被店铺管理员暂停

    static function IsSuspend($suspend)
    {
        return (ShopIsSuspend::BY_SYS_ADMIN == $suspend
                || ShopIsSuspend::BY_SHOP_ADMIN == $suspend);
    }
}
// 分类类别
class CateType
{
    const TYPEONE     = 1;   // 一般类品分类
    const TYPETWO     = 2;   // 配件
    const TYPETHREE   = 3;   // 酒水
}

// 菜品时间设置
class SaleFoodSetTime
{
    const SETTIME   = 1;   // 自定义时间戳
    const SETWEEK   = 2;   // 自定义周期
}

// 菜品规格是否影响菜品价格
class IsPrice
{
    const NO   = 1;   // 对价格无影响
    const YES  = 2;   // 对价格有影响
}

// 菜品是否有不同规格
class IsPecs
{
    const NO   = 0;   // 不使用用不同规格
    const YES  = 1;   // 使用不同规格
}

// 菜品价格使用类型
class FoodPriceType
{
    const THIS   = 1;   // 不使用规格价格
    const SPEC   = 2;   // 使用规格价格
}

// 菜品价格类型
class PriceType
{
    const ORIGINAL   = 1;   // 普通价格
    const DISCOUNT   = 2;   // 折扣价格
    const VIP        = 4;   // vip价格
    const FESTIVAL   = 8;   // 节日价格
}

// 店铺是否启用餐位费
class IsSeatEnable
{
    const NO   = 0;   // 不启用
    const YES  = 1;   // 启用
}

// 发票材质类型
class IsInvoice
{
    const NO       = 0;   // 不开发票
    const PAPER    = 1;   // 纸质
    const ELECTRON = 2;   // 电子
}

// 餐桌餐位费结算方式
class SeatPriceType
{
    const NO       = 0;   // 不收餐位费
    const NUM      = 1;   // 按人数
    const FIXED    = 2;   // 固定数
    const RATIO    = 3;   // 按订单总额比例
}
//订单状态
class NewOrderStatus
{
    const NOPAY     = 1;    // 未支付
    const PAY       = 2;    // 已支付
    const KNOT      = 3;    // 反结
    const REFUND    = 4;    // 退款成功
    const REFUNDFAIL= 5;    // 退款失败
    const CLOSER    = 6;    // 已关闭
    const GUAZ      = 7;    // 挂账
    const REFUNDING = 8;    // 退款中
}

//点赞收藏状态
class PraiseType
{
    const PRAISE     = 1;    // 点赞
    const COLLECT    = 2;    // 收藏
}

//图片类型
class ImgType
{
    const NONE   = 0;             // 无定义
    const USER   = 1;             // 用户图片
}

//销售方式
class SALEWAY
{
    const EAT     = 1;             // 在店吃
    const SINCE   = 2;             // 自提
    const PACK    = 3;             // 打包
    const TAKEOUT = 4;             // 外卖
}
//厨房制作状态
class KitchenStatus
{
    const WAITMAKE  = 1;             // 等待制作
    const FINISH    = 2;             // 完成制作
}
//后厨制作状态
class MadeStatus
{
    const WAIT    = 1;             // 等待中
    const CONF    = 2;             // 配菜中
    const MADE    = 3;             // 制作中
    const MAKE_OK = 4;             // 制作完成(待传)
    const GIVE    = 5;             // 已传

}
?>
