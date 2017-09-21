/*
 * 配置文件
 * QQ:15586350 [rockyshi 2014-04-02]
 */


var Cfg = {
    cookie : {
        expires : 7     /* 默认单位为天 */
    }
};

// 对应于const.inc中定义（注意一致[XXX]）
var errcode = new function()
{
    this.code = {
        "-1"     : "系统出错",
        "-10001" : "系统出错",
        "-10002" : "参数出错",
        "-10005" : "用户不存在(请联系管理员)",
        "-10006" : "此用户名已被注册",
        "-10007" : "登录密码出错",
        "-10008" : "密码错误",
        "-10009" : "数据已有变动（刷新后再修改）",
        "-10010" : "不是当前用户的数据",
        "-10011" : "key已被使用",
        "-10012" : "用户名不能为空",
        "-10013" : "文件不存在",
        "-10014" : "创建zip压缩文件出错",
        "-10015" : "文件上传出错",
        "-10016" : "打开压缩文件出错",
        "-10017" : "文件格式出错（不是备份文件）",
        "-10018" : "备份文件中数据出错",
        "-10018" : "备份文件密码出错",
        "-10020" : "用户未登录",
        "-10021" : "用户已经登录过",
        "-10022" : "KEY过期，请再次尝试当前操作。",
        "-10023" : "数据库操作出错",
        "-20001" : "配置文件不存在",
        "-20002" : "配置文件写入出错",
        "-20003" : "货品编号已被使用",
        "-20004" : "类别名已存在",
        "-20005" : "货品不存在",
        "-20006" : "原密码错误",
        "-20007" : "是个目录",
        "-20008" : "备份文件出错",
        "-20009" : "路径出错",
        "-20010" : "文件写入出错",
        "-20011" : "操作权限不足",
        "-20012" : "文件不存在",
        "-20013" : "日志操作出错",
        "-20014" : "日志不存在",
        "-20030" : "数据库操作出错",
        "-20031" : "升级包出错",
        "-20032" : "同批次文件中存在相同文件",
        "-20033" : "没有组文件",
        "-20034" : "名称已存在",
        "-30010" : "图片过多",
        "-30011" : "餐品名称已存在",
        "-30012" : "订单出错",
        "-30013" : "请在微信中打开",
        "-30014" : "店铺不存在",
        "-30015" : "订单不可更改，可重新下单",
        "-30016" : "订单已出单，修改请联服务员",
        "-30017" : "订单已支付，不能修改",
        "-30018" : "订单已完成，不能修改",
        "-30019" : "订单已作废，不能修改",
        "-30020" : "订单超时，不能修改",
        "-30021" : "订单已出单，不能修改",
        "-30022" : "订单出错",
        "-30023" : "餐桌号不存在",
        "-30024" : "店铺不存在",
        "-30025" : "餐品存量不够",
        "-30026" : "订单操作出错",
        "-30027" : "餐品不存在",
        "-30028" : "订单不存在",
        "-30029" : "订单已有变动，请刷新后再操作",
        "-30030" : "系统暂停使用",

        /*
         * 注意同步修改 const.inc --> errcode
         */
        "0" : ""
    };

    this.toString = function(code)
    {
        return this.code[code] || "未知错误[" + code + "]";
    }
};

// 用户属性位
var UserProperty = new function()
{
    this.SYS_ADMIN     = 1;    // 系统管理员
    this.SHOP_USER     = 2;    // 店铺用户
    this.COMPANY_USER  = 4;    // 是公司用户

    this.IsSysAdmin = function(property)
    {
        property = parseInt(property);
        return (property & this.SYS_ADMIN) != 0;
    }

    this.IsShopUser = function(property)
    {
        property = parseInt(property);
        return (property & this.SHOP_USER) != 0;
    }

    this.IsCompanyUser = function(property)
    {
        property = parseInt(property);
        return (property & this.COMPANY_USER) != 0;
    }
}

//用餐方式
var DineWay = new function()
{
    this.code = {
        "0" : "-",
        "1" : "在店吃",
        "2" : "打包"
    };

    this.toString = function(code)
    {
        code = parseInt(code);
        return this.code[code] || "未知[" + code + "]";
    }
}

//支付方式
var PayWay = new function()
{
    this.UNKNOWN  = 0; // 未知
    this.CASH     = 1; // 现金
    this.WEIXIN   = 2; // 微信
    this.code = {
        "0" : "-",
        "1" : "现金",
        "2" : "微信"
    };

    //是在线支付返回true
    this.IsOnline = function(pay_way)
    {
        return this.WEIXIN == pay_way;
    }

    this.toString = function(code)
    {
        code = parseInt(code||0);
        return this.code[code]||'未知[' + code + ']';
    }
}

//订单状态(const.php-->OrderStatus)
var OrderStatus = new function()
{
    this.PENDING   = 0;     // 待处理
    this.CONFIRMED = 1;     // 已确认
    this.PAID      = 2;     // 已支付
    this.FINISH    = 3;     // 已完成
    this.CANCEL    = 4;     // 已作废
    this.TIMEOUT   = 5;     // 订单超时
    this.PRINTED   = 6;     // 已出单
    this.ERR       = 7;     // 订单出错
    this.POSTPONED = 8;     // 叫起（即确认下单，但延迟出餐）
    this.code = {
        0 : "待处理",
        1 : "已确认",
        2 : "已支付",
        3 : "已完成",
        4 : "已作废",
        5 : "超时",
        6 : "已出单",
        7 : "出错",
        8 : "叫起",
    };

    this.toString = function(code)
    {
        code = parseInt(code||0);
        return this.code[code]||'未知[' + code + ']';
    }

    // 友好提示
    this.toNoteString = function(code)
    {
        var str = {
            0 : "等待服务员处理",
            1 : "服务员已确认",
            2 : "客人已支付",
            3 : "用餐完成",
            4 : "单号已作废",
            5 : "单号超时",
            6 : "单号已出单",
            7 : "单号出错",
            8 : "叫起",
        };
        code = parseInt(code);
        return str[code] || this.toString(code);
    }

    // 待处理订单
    this.IsPending = function(status)
    {
        return this.PENDING == status;
    }

    // 是已结束的订单返回true
    this.IsEnd = function(status)
    {
        return this.CANCEL == status
                || this.TIMEOUT == status
                || this.ERR == status
                || this.FINISH == status;
    }

    // 是已确认状态
    this.HadConfirmed = function(status)
    {
        return this.CONFIRMED == status
                || this.PAID == status
                || this.PRINTED == status
                || this.POSTPONED == status;
    }

    // 超时
    this.IsTimeout = function(order_time)
    {
        return Util.GetTimestamp() - parseInt(order_time||0) > 3600 * 24;
    }

    // 订单处理于可修改状态
    this.CanModify = function(status)
    {
        status = parseInt(status||0);
        return status == this.PENDING;
    }
}

// 是否会员
var IsVipCustomer = new function()
{
    this.YES = 1;
    this.NO  = 0;
    this.code = {
        1 : "是",
        0 : "否"
    };

    this.toString = function(code)
    {
        code = parseInt(code||0);
        return this.code[code]||'未知[' + code + ']';
    }
}

// 餐桌状态
var SeatStatus = new function()
{
    this.VACANT = 0; // 空闲
    this.INUSE  = 1; // 使用中
    this.ALERT  = 2; // 有提示
    this.code = {
        0 : "空闲",
        1 : "使用中",
        2 : "有提示"
    };

    this.toString = function(code)
    {
        code = parseInt(code||0);
        return this.code[code]||'未知[' + code + ']';
    }
}

// 本餐品是否需要服务员确认
var NeedWaiterConfirm = new function()
{
    this.NO  = 0;   // 不需要服务员确认
    this.YES = 1;   // 需要服务员确认
}
