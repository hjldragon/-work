<?php
/*
 * [rockyshi 2014-05-05 13:18:27]
 * 常量定义
 */

// define('ERR', -1);

class errcode
{
    const SYS_ERR               = -10001;       // 系统出错
    const PARAM_ERR             = -10002;       // 参数出错
    const SYS_BUSY              = -10003;       // 系统忙
    const USER_NO_EXIST         = -10005;       // 用户不存在
    const USER_HAD_REG          = -10006;       // 此用户名已被注册
    const USER_PASSWD_ERR       = -10007;       // 登录密码出错
    const DATA_PASSWD_ERR       = -10008;       // 密码错误
    const DATA_CHANGE           = -10009;       // 数据已有变动（刷新后再修改）
    const DATA_OWNER_ERR        = -10010;       // 不是当前用户的数据
    const DATA_KEY_USED         = -10011;       // key已被使用
    const USER_NAME_EMPTY       = -10012;       // 用户名不能为空
    const FILE_NOT_EXIST        = -10013;       // 文件不存在
    const CREATE_ZIPFILE_ERR    = -10014;       // 创建zip压缩文件出错
    const FILE_UPLOAD_ERR       = -10015;       // 文件上传出错
    const OPEN_ZIPFILE_ERR      = -10016;       // 打开压缩文件出错
    const NO_BAK_FILE           = -10017;       // 文件格式出错（不是备份文件）
    const BAKFILE_DATA_ERR      = -10018;       // 备份文件中数据出错
    const BAKFILE_PASSWD_ERR    = -10019;       // 备份文件密码出错
    const USER_NOLOGIN          = -10020;       // 用户未登录
    const USER_LOGINED          = -10021;       // 用户已经登录过
    const DATA_KEY_NOT_EXIST    = -10022;       // 通讯用key不存在
    const DB_OPR_ERR            = -10023;       // 数据库操作出错
    const PHONE_IS_EXIST        = -10026;       // 手机号已被使用
    const USER_SETTING_ERR      = -10028;       // 设置登录用户出错
    const EMAIL_IS_EXIST        = -10029;       // 邮箱已被使用
    const NOT_BIND_PHONE        = -10030;       // 不是绑定的手机号码
    const CFG_NO_EXIST          = -20001;       // 配置文件不存在
    const CFG_WRITE_ERR         = -20002;       // 配置文件写入出错
    const GOODS_SERIAL_USED     = -20003;       // 货品编号已被使用
    const CLASS_NAME_USED       = -20004;       // 类别名已存在
    const GOODS_NOT_EXIST       = -20005;       // 货品不存在
    const USER_OLD_PASSWD_ERR   = -20006;       // 原密码错误
    const FILE_IS_DIR           = -20007;       // 是个目录
    const FILE_BAK_ERR          = -20008;       // 备份文件出错
    const FILE_PATH_ERR         = -20009;       // 路径出错
    const FILE_WRITE_ERR        = -20010;       // 文件写入出错
    const USER_PERMISSION_ERR   = -20011;       // 操作权限不足
    const FILE_NO_EXIST         = -20012;       // 文件不存在
    const LOG_OPR_ERR           = -20013;       // 日志操作出错
    const LOG_NO_EXIST          = -20014;       // 日志不存在
    const DB_ERR                = -20030;       // 数据库操作出错
    const UPDATE_PACK_ERR       = -20031;       // 升级包出错
    const BATCH_FILE_NOT_UNIQ   = -20032;       // 同批次文件中存在相同文件
    const NOT_GROUP_FILE        = -20033;       // 没有组文件
    const NAME_IS_EXIST         = -20034;       // 名称已存在
    const HOTEL_NOT_EXIST       = -20035;       // 酒店不存在
    const PHONE_ERR             = -20037;       // 手机号码不正确
    const EMAIL_ERR             = -20038;       // 邮箱不正确
    const MAIL_CODE_ERR         = -20039;       // 邮箱密文不正确
    const COKE_ERR              = -20040;       // 验证码不正确
    const SHOP_LABEL_ERR        = -20041;       // 标签名为空
    const PHONE_COKE_ERR        = -20042;       // 手机验证不正确
    const PHONE_SEND_FAIL       = -20043;       // 手机发送失败
    const NEWS_NUM_MAX          = -20044;       // 发送消息数超出限制
    const PASSWORD_TWO_SAME     = -20045;       // 2次输入的密码不一样
    const EMAIL_SEND_FAIL       = -20046;       // 邮箱发送失败
    const DEPARTMENT_IS_EXIST   = -20047;       // 部门名称重复
    const IDCARD_ERR            = -20048;       // 身份证号码不正确
    const PHONE_VERIFY_ERR      = -20049;        //手机验证过程出错
    const USER_NOT_ZC           = -20050;        //用户没有注册
    const ORDER_SEAT_NO         = -20051;        //订单中含有该餐桌
    const EMPLOYEE_IS_EXIT      = -20060;        //该员工已经邀请过了
    const PHONE_TWO_NOT         = -20061;        //俩次输入的电话号码不一样
    const WEIXIN_NO_LOGIN       = -20062;        //此微信未绑定,不能登录
    const WEIXIN_NO_BINDING     = -20063;        //此微信已绑定账号,不能重复绑定
    const WEIXIN_NO_REBINDING   = -20064;        //账号与微信不符,不能解除绑定
    const INVOICING_NOT         = -20065;        //用户没开票
    const FOOD_ERR              = -20066;        //餐品出错
    const INVOICE_IS_ERR        = -20067;        //发票错误
    const FEE_MONEY_ERR         = -20068;        //减免金额出错
    const CODE_NOT_SET          = -20069;        //二维码未设置
    const NEWS_NOT_CONTENT      = -20070;        // 系统消息无内容
    const NEWS_ID_NOT_EP        = -20071;        // 消息id不能为空




                                  // 2017-05-02
    const FOOD_IMG_TOOMANY        = -30010;       // 图片过多
    const FOOD_EXIST              = -30011;       // 餐品名称已存在
    const ORDER_STATUS_ERR        = -30012;       // 订单状态出错
    const BROWSER_NOT_WEIXIN      = -30013;       // 请在微信中打开
    const SHOP_NOT_WEIXIN         = -30014;       // 店铺不存在
    const ORDER_NOT_MODIFY        = -30015;       // 订单处于不可更改阶段，可重新下单
    const ORDER_ST_CONFIRMED      = -30016;       // 订单已确认，不能修改
    const ORDER_ST_PAID           = -30017;       // 订单已支付，不能修改
    const ORDER_ST_FINISH         = -30018;       // 订单已完成，不能修改
    const ORDER_ST_CANCEL         = -30019;       // 订单已作废，不能修改
    const ORDER_ST_TIMEOUT        = -30020;       // 订单超时，不能修改
    const ORDER_ST_PRINTED        = -30021;       // 订单已出单，不能修改
    const ORDER_ST_ERR            = -30022;       // 订单出错，不能修改
    const SEAT_NOT_EXIST          = -30023;       // 餐桌号不存在
    const SHOP_NOT_EXIST          = -30024;       // 店铺不存在
    const FOOD_NOT_ENOUGH         = -30025;       // 餐品存量不够
    const ORDER_OPR_ERR           = -30026;       // 订单操作出错
    const FOOD_NOT_EXIST          = -30027;       // 餐品不存在
    const ORDER_NOT_EXIST         = -30028;       // 订单不存在
    const ORDER_HAD_CHANGE        = -30029;       // 订单已有变动，请刷新后再操作
    const PHONE_CODE_LAPSE        = -30030;       // 手机验证码超时
    const MAIL_TIME_LAPSE         = -30031;       // 手机验证码超时
    const CATE_NOT_DEL            = -30032;       // 此分类下有商品，不能删除
    const DEPARTMENT_NOT_DEL      = -30033;       // 此部门下有员工，不能删除
    const EMPLOYEE_IS_FREEZE      = -30034;       // 此部门员工已被冻结
    const SEAT_IS_EXIST           = -30035;       // 餐桌号已存在
    const SHOP_SUSPEND            = -30036;       // 系统暂停使用
    const RESERVATION_NOT_EXIST   = -30037;       // 预约不存在

    /*
     * 注意同步修改 js/cfg.js --> errcode
     */
};

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
//职级权限值
class Position
{
    const ALLBACKSTAGE   = 1;       // 后台全部权限
    const ALLWEB         = 2;       // 前端全部权限
    const ORDERING       = 4;       // 使用点餐
    const GIVING         = 8;       // 赠送
    const NEW_ORDER      = 16;      // 使用新订单管理
    const USRPREDETET    = 32;      // 使用预定
    const USRHISTORORDER = 64;      // 使用历史订单管理
    const CHECKOUT       = 128;     // 结账
    const ORDEROUT       = 256;     // 下单并结账
    const CLOSEOUT       = 512;     // 关闭并结账
    const USROUT         = 1024;    // 使用退款申请
    const FCHECKOUT      = 2048;    // 使用反结账
    const REFUND         = 4096;    // 退款
    const CLOSEROREDER   = 8192;    // 关闭订单
    const INVOICE        = 16384;   // 开发票
    const REDDASHED      = 32768;   // 红冲
    const USERSILVER     = 65536;   // 使用收银
    const GUAZHANG       = 131072;  // 挂账
    const MALING         = 262144;  // 抹零
    const CLOSEREFUND    = 524288;  // 关闭并退款
    const HISTORYPAY     = 1048576; // 结账(历史订单)
    const SETTING        = 2097152; // 基础设置
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
?>
