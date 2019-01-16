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
    const PENDING   = 0;    // 待处理
    const CONFIRMED = 1;    // 已确认
    const PAID      = 2;    // 已支付
    const FINISH    = 3;    // 已完成
    const CANCEL    = 4;    // 已作废
    const TIMEOUT   = 5;    // 订单超时
    const PRINTED   = 6;    // 已出单
    const ERR       = 7;    // 订单出错
    const POSTPONED = 8;    // 叫起（即确认下单，但延迟出餐）

    // 是已确认状态
    static function HadConfirmed($status)
    {
        return OrderStatus::CONFIRMED == $status ||
                OrderStatus::PAID == $status ||
                OrderStatus::POSTPONED == $status;
    }
}
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
//支付状态
class PayStatus
{
    const NOPAY     = 1;    // 未支付
    const PAY       = 2;    // 已支付
    const GUAZ      = 3;    // 挂账
}
//订单确认状态
class OrderSureStatus
{
    const NOSURE    = 1;    // 未下单
    const SURE      = 2;    // 下单
    const SUREPAY   = 3;    // 下单并支付
    // 是已确认状态
//    static function HadConfirmed($status)
//    {
//        return OrderSureStatus::SURE == $status ||
//            OrderSureStatus::SUREPAY == $status;
//    }
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

    // 是在线支付的
    static function IsOnline($pay_way)
    {
        return $pay_way == PayWay::WEIXIN;
    }
}
// 是否会员
class IsVipCustomer
{
    const YES = 1;
    const NO  = 0;
}
// 员工职务
class EmployeeDuty
{
    const UNKNOWN         = 0;   // 待定
    //const SYS_SHOP_ADMIN  = 1;   // 店铺系统管理员
    const SYS_SHOP_ADMIN  = 195;   // 店铺系统管理员
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
    const MAIL_URL      = "http://www.ob.com:8080/php/bind_mail.php?";  //邮箱绑定地址

    static function IsSuspend($suspend)
    {
        return (ShopIsSuspend::BY_SYS_ADMIN == $suspend
                || ShopIsSuspend::BY_SHOP_ADMIN == $suspend);
    }
}
// 菜品价格类型
class PriceType
{
    const ORIGINAL   = 1;   // 普通价格
    const DISCOUNT   = 2;   // 折扣价格
    const VIP        = 4;   // vip价格
    const FESTIVAL   = 8;   // 节日价格
}
// 营业时间段
class OpenTime
{
    const MORNING  = 1;   // 早市
    const NOON     = 2;   // 午市
    const NIGHT    = 3;   // 晚市
    const SUPPER   = 4;   // 夜宵
}
class SetPayWay
{
    const USEOUR   = 1;   // 使用个人码
    const USEOTHER = 2;   // 使用微信/支付宝的支付

}

class PositionType
{
    const SYSTEMTYPEONE   = 1;      // 系统默认创建的职级的type
}
class EmployeeFreeze
{
    const FREEZE   = 1;             // 员工已冻结
}
//支付设置
class PaySetingWay
{
    const PAYONE   = 1;             // 个人码支付方式
    const PAYTWO   = 2;             // 微信/支付宝端支付方式

}
//店铺消息设置
class ShopNewsDay
{
    const NUM   = 5;             // 店铺每日群发消息限制数
}
//图片类型
class ImgType
{
    const NONE   = 0;             // 无定义
    const USER   = 1;             // 用户图片
}
// 餐桌餐位费结算方式
class SeatPriceType
{
    const NO       = 0;   // 不收餐位费
    const NUM      = 1;   // 按人数
    const FIXED    = 2;   // 固定数
    const RATIO    = 3;   // 按订单总额比例
}
// 运营平台id固定
class PlatformID
{
    const ID       = "1";   // 暂时运营平台只有一个，固定id为1
}

// 用户端
class UserSrc
{
    const CUSTOMER = 1;   // 客户端
    const SHOP     = 2;   // 商户平台端
    const PLATFORM = 3;   // 运营平台端
}
//平台员工职级权限值
class PlPosition
{
    const ALL            = 1;       // 运营平台全部权限
    const BOARD          = 2;       // 首页权限
    const AGENT          = 4;       // 代理商管理权限
    const SHOP           = 8;       // 商户管理权限
    const PLATFORMER     = 16;      // 组织管理权限
    const NEWS           = 32;      // 消息管理权限
    const GOODS          = 64;      // 商品管理权限
    //未使用区域
    const CHECKOUT       = 128;     // 权限7
    const ORDEROUT       = 256;     // 权限8
    const CLOSEOUT       = 512;     // 权限9
    const USROUT         = 1024;    // 权限10

    //拥有管理权限
    public static function IsAdmin($position_permission)
    {
        return (Position::ALL & $position_permission) != 0;
    }

}
class AgentType
{
    const AREAAGENT  = 1;      // 区域代理商
    const GUILDAGENT = 2;      // 行业代理商

}
// 数据来源:
class SrcType
{
    const PC          = 1;    // PC,
    const APP         = 2;    // app
    const SHOUYINJI   = 3;    // 收银机,
    const SELFHELP    = 4;    // 自助点餐机
    const PAD         = 5;    // PAD端
}
// 消息发送对象
class SendType
{
    const ALL           = 1;    // 全部
    const SHOP          = 2;    // 商户,
    const AGENT         = 3;    // 代理商

}
// 默认分配名额
class AuthorizeNum
{
    const NUM         = 2;    // 默认分配2
}
//是否通过审核人的深
class AuditCode
{
    const NO          = 0;    // 不通过
    const YES         = 1;    // 通过
}
//员工是否冻结
class IsFreeze
{
    const NO          = 0;    // 不冻结
    const YES         = 1;    // 冻结
}

//工商信息状态
class ShopBusiness
{
    const NOAPPLY     = 0;    // 未认证
    const APPLY       = 1;    // 待认证
    const SUCCESSFUL  = 2;    // 认证成功
    const FAIL        = 3;    // 认证失败
}
//工商信息审核进度
class BusinessPlan
{
    const XS          = 1;    // 1.销售人员
    const XSJL        = 2;    // 2.销售经理
    const YY          = 3;    // 3,运营人员
    const YYJL        = 4;    // 4.运营经理
    const CW          = 5;    // 5.财务人员
    const CWL         = 6;    // 6.财务经理
}

//代理商折扣等级
class AgentVip
{
    const ONE        = 1;    // 一级
    const TWO        = 2;    // 二级
    const THREE      = 3;    // 三级

}


//是否收取了工商管理服务费
class IsTakeBusinessMoney
{
    const NO          = 0;    // 未收取
    const YES         = 1;    // 收取
}
//充值金额状态
class CZPayStatus
{
    const NEEDPAY     = 0;    // 待充值
    const PAY         = 1;    // 充值成功
    const NOPAY       = 2;    // 充值失败
}

//充值金额的支付方式
class CZPayWay
{
    const NOWAY       = 0;    // 待确定
    const ALIPAY      = 1;    // 支付宝
    const WX          = 2;    // 微信
    const BANK        = 3;    // 网银支付
}
//代理商运营报表选择方式
class UseDataWay
{
    const WEEK       = 1;    // 周末
    const MONTH      = 2;    // 月
    const YEAR       = 3;    // 年
}
//工商信息状态
class BusinessType
{
    const NOAPPLY     = 0;    // 未认证
    const APPLY       = 1;    // 待认证
    const SUCCESSFUL  = 2;    // 认证成功
    const FAIL        = 3;    // 认证失败
    const ALL         = 4;    // 全部状态
}
// 商品上架时间设置
class SaleGoodsSet
{
    const NOSET     = 0;   // 未设置
    const SETTIME   = 1;   // 自定义时间戳
}
// 商品上下架状态
class SaleOffGoods
{
    const YES  = 0;   // 上架
    const NO   = 1;   // 下架
}

class GoodsOrderStatus
{
    const NOPAY          = 1;    // 待付款
    const WAITDELIVER    = 2;    // 待发货
    const WAITCOLLECT    = 3;    // 待收货
    const WAITEVALUATION = 4;    // 待评价
    const BEEVALUATION   = 5;    // 已评价
    const REFUNDING      = 6;    // 退款中
    const FAILREFUND     = 7;    // 退款失败
    const SUCCESSREFUND  = 8;    // 已退款
    const CLOSER         = 9;    // 已关闭
}

//商品订单的支付方式
class GoodsOrderPayWay
{
    const NOWAY   = 0;    // 待确定
    const BALANCE = 1;    // 余额支付
    const WX      = 2;    // 微信
    const ALIPAY  = 3;    // 支付宝
}

//地址类型
class AddressType
{
    const PLATFORM = 1;    // 平台发货
    const AGENT    = 2;    // 代理商收货
    const SHOP     = 3;    // 店铺收货
}

?>
