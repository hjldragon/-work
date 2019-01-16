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

    // 是在线支付的
    static function IsOnline($pay_way)
    {
        return $pay_way == PayWay::WEIXIN;
    }
}
//支付状态
class PayStatus
{
    const NOPAY     = 1;    // 未支付
    const PAY       = 2;    // 已支付
    const GUAZ      = 3;    // 挂账
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
//职级权限值
class Position
{
    const ALLBACKSTAGE   = 1;      // 后台全部权限
    const ALLWEB         = 2;      // 前端全部权限
    const ORDERING       = 4;      // 使用点餐
    const GIVING         = 8;      // 赠送
    const NEW_ORDER      = 16;     // 使用新订单管理
    const USRPREDETET    = 32;     // 使用预定
    const USRHISTORORDER = 64;     // 使用历史订单管理
    const CHECKOUT       = 128;    // 结账
    const ORDEROUT       = 256;    // 下单并结账
    const CLOSEOUT       = 512;    // 关闭并结账
    const USROUT         = 1024;   // 使用退款申请
    const FCHECKOUT      = 2048;   // 使用反结账
    const REFUND         = 4096;   // 退款
    const CLOSEROREDER   = 8192;   // 关闭订单
    const INVOICE        = 16384;  // 开发票
    const REDDASHED      = 32768;  // 红冲
    const USERSILVER     = 65536;  // 使用收银
    const GUAZHANG       = 131072; // 挂账
    const MALING         = 262144; // 抹零
    const SETTING        = 524288; // 基础设置
    //时候拥有后台管理权限
    public static function IsAdmin($position_permission)
    {
        return (Position::ALLBACKSTAGE & $position_permission) != 0;
    }


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
// 数据来源:
class NewSrctype
{
    const SHOUYINJI  = 1;    // 智能收银机
    const SELFHELP   = 2;    // 自助点餐机
    const WX         = 3;    // 扫码点餐,
    const PAD        = 4;    // 平板智能点餐机
    const APP        = 5;    // 掌柜通
    const MINI       = 6;    // 小程序
}

//销售方式
class SALEWAY
{
    const EAT     = 1;             // 在店吃
    const SINCE   = 2;             // 自提
    const PACK    = 3;             // 打包
    const TAKEOUT = 4;             // 外卖
}
// 运营平台id固定
class PlatformID
{
    const ID       = "1";   // 暂时运营平台只有一个，固定id为1
}

//店铺消息设置
class ShopNewsDay
{
    const NUM   = 5;             // 店铺每日群发消息限制数
}
//是否属于已确定订单
class IsCoonfirm
{
    const Yes   = 1;             // 属于已确定订单
    const NO    = 0;             // 属于为确定订单
}
//店铺是否支持自动下单
class AutoOrder
{
    const Yes   = 1;             // 1是
    const NO    = 0;             // 0不是
}

// 项目配置id
class EnvId
{
    const APP      = 1;   // 手机app配置id
}
// 绑定来源:1客户端,2商户端
class Src
{
    const CUSTOMER      = 1;   // 1客户端
    const SHOP          = 2;    //2商户端
    const PLATFORM      = 3;    //3平台端
}
//是否已绑定微信
class IsWeixin
{
    const Yes   = 1;             // 1绑定
    const NO    = 0;             // 0未班的
}
// 数据来源:
class WxSrctype
{
    const PC          = 1;    // 1PC,
    const APP         = 2;    //2app
    const SHOUYINJI   = 3;    // 1收银机,
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
//厨房制作状态
class KitchenStatus
{
    const WAITMAKE  = 1;             // 等待制作
    const FINISH    = 2;             // 完成制作
}
//订单来源
class OrderFrom
{
    const SHOUYIN  = 1;   // 1:门店前台(收银机)
    const SELF     = 2;   // 2:手持设备(自助点餐机)
    const WECHAT   = 3;   // 3:扫码自助(微信点餐)
    const PAD      = 4;   // 4:pad
    const APP      = 5;   // 5:APP(掌柜通)
    const MINI     = 6;   // 6:小程序
}
class AgentType
{
    const AREAAGENT  = 1;      // 区域代理商
    const GUILDAGENT = 2;      // 行业代理商
}
//员工是否冻结
class IsFreeze
{
    const NO          = 0;    // 不冻结
    const YES         = 1;    // 冻结
}
class OrderSureStatus
{
    const NOSURE    = 1;    // 未下单
    const SURE      = 2;    // 下单
    const SUREPAY   = 3;    // 下单并支付
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
class ApplyStatus
{
    const APPLY          = 1;    // 提交初审
    const APPLYPASS      = 2;    // 初审不通过
    const APPLYTHOUR     = 3;    // 代理商初审通过
    const APPLYBUS       = 4;    // 提交代理商工商
    const APPLYBUSPASS   = 5;    // 代理商工商审核不通过
    const APPLYBUSTHOUR  = 6;    // 代理商工商审核通过
}
?>
