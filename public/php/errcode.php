<?php
/*
 * [Rocky 2017-12-26 11:37:52]
 * 全局错误定义
 */
class errcode
{
    const OK                    = 0;            // 正确
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
    const CFG_WRITE_ERR         = -20002;       // 配置文件不存在
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
    const WEIXIN_NO_LOGIN       = -20062;        //未绑定微信账号,请使用账号密码登录
    const WEIXIN_NO_BINDING     = -20063;        //此微信已绑定账号,不能重复绑定
    const WEIXIN_NO_REBINDING   = -20064;        //账号与微信不符,不能解除绑定
    const INVOICING_NOT         = -20065;        //用户没开票
    const FOOD_ERR              = -20066;        //餐品出错
    const INVOICE_IS_ERR        = -20067;        //发票错误
    const FEE_MONEY_ERR         = -20068;        //减免金额出错
    const CODE_NOT_SET          = -20069;        //二维码未设置
    const NEWS_NOT_CONTENT      = -20070;        // 系统消息无内容
    const NEWS_ID_NOT_EP        = -20071;        // 消息id不能为空
    const FOOD_NO_SPC           = -20072;        // 该餐品无份量规格
    const ADDRESS_REPEAT        = -20073;        // 地址已存在
    const RESERVATION_TIME_GO   = -20074;        // 预约时间不在当前时间之后
    const AGENT_NO_EXIST        = -20075;        // 区域商代理不存在
    const NEWS_IS_SEND          = -20076;        // 系统消息已发送
    const PASSWORD_SAME         = -20077;        // 原密码和新密码一样
    const EMPLOYEE_NOT_LOGIN    = -20078;        // 该员工无登录权限
    const AGENT_IS_EXIST        = -20079;        // 代理商区域重复
    const AGENT_NO_CHANGE       = -20080;        // 此代理商下有店铺，不能修改
    const ADMIN_IS_EXIST        = -20081;        // 管理员重复(一个账号只能创建一个店铺)
    const NOT_AUTHORIZATION     = -20082;        // 未获得授权,请联系店铺管理人员设置
    const ADMIN_PASWORD_ERR     = -20083;        // 管理员账号或密码不正确<<<<<<<<<<<<<<<<<
    const GET_LIST_ERR          = -20084;        // 加载失败,请重新操作



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
    const SEAT_IS_EXIST           = -30035;       // 餐桌号不存在
    const SHOP_SUSPEND            = -30036;       // 系统暂停使用
    const RESERVATION_NOT_EXIST   = -30037;       // 预约不存在
    const FOOD_SALE_OFF           = -30038;       // 餐品已下架
    const POSITION_NOT_DEL        = -30039;       // 此职位下有员工，不能删除
    const NO_CATE                 = -30040;       // 没有此分类名
    const IMG_NOT_MORE            = -30043;       // 图片更换不到一个月
    const SHOP_IS_FREEZE          = -30044;       // 此店铺已被冻结
    const AGENT_IS_FREEZE         = -30045;       // 此代理商已被冻结


    //{{ 微信端移来
    const PAY_ERR                 = -30041;       // 支付取消或失败
    const PAY_NEED_PASSWD         = -30042;       // 支付需要密码
    //}}

    //{{ costomer端移来(代码重设置)
    const ORDER_IS_URGE     = -30052;       // 订单已催单
    const ORDER_URGE_TIME   = -30053;       // 催单时间还未到
    const ORDER_NOT_PRINTED = -30054;       // 订单还未下单
    const SHOP_ID_NOT       = -20036;       // 没有餐馆ID
    const WX_NO_SUPPORT     = -30055;       // 未开通微信支付
    //}}

    //{{ 后台服务出错码
    const SVC_ERR_SYS               = -100001;       // 内部错误
    const SVC_ERR_PARAM             = -100002;       // 参数错误
    const SVC_ERR_USER_REGISTER     = -100003;       // 注册用户出错
    const SVC_ERR_USER_NOT_EXIST    = -100004;       // 用户不存在
    const SVC_ERR_DB                = -100005;       // 数据库出错
    const SVC_ERR_RSA_ENC           = -100006;       // rsa加密出错
    const SVC_ERR_RSA_DEC           = -100007;       // rsa解密出错
    const SVC_ERR_USER_NOT_LOGIN    = -100008;       // 用户未登录
    const SVC_ERR_LOGIN_ERR         = -100009;       // 用户登录出错
    const SVC_ERR_WS_CMD_UNKNOWN    = -100010;       // 未知的websocket处理命令
    const SVC_ERR_DATA_SEND         = -100011;       // 数据发送出错
    const SVC_ERR_CHANNEL_NOT_EXIST = -100012;       // 订阅频道不存在
    const SVC_ERR_CONNECT_CLOSED    = -100013;       // 连接已断开
    const SVC_ERR_DATA_SIGN         = -100020;       // 签名出错
    //}}

    ///////////////////////////////////////////////////////////////////
    //
    //     无特殊要求，上定义不要改变，新加的出错码写下在，按10递增
    //
    ///////////////////////////////////////////////////////////////////

    //const xxx         = -200010       // 出错描述

    /*
     * 注意同步修改 js/cfg.js --> errcode
     */

    static private $ret2msg = [
        "-10001" => "系统出错",
        "-10002" => "参数出错",
        "-10003" => "系统忙",
        "-10005" => "用户不存在",
        "-10006" => "此用户名已被注册",
        "-10007" => "登录密码出错",
        "-10008" => "密码错误",
        "-10009" => "数据已有变动（刷新后再修改）",
        "-10010" => "不是当前用户的数据",
        "-10011" => "key已被使用",
        "-10012" => "用户名不能为空",
        "-10013" => "文件不存在",
        "-10014" => "创建zip压缩文件出错",
        "-10015" => "文件上传出错",
        "-10016" => "打开压缩文件出错",
        "-10017" => "文件格式出错（不是备份文件）",
        "-10018" => "备份文件中数据出错",
        "-10019" => "备份文件密码出错",
        "-10020" => "用户未登录",
        "-10021" => "用户已经登录过",
        "-10022" => "通讯用key不存在",
        "-10023" => "数据库操作出错",
        "-10026" => "手机号已被使用",
        "-10028" => "设置登录用户出错",
        "-10029" => "邮箱已被使用",
        "-10030" => "不是绑定的手机号码",
        "-20001" => "配置文件不存在",
        "-20002" => "配置文件不存在",
        "-20003" => "货品编号已被使用",
        "-20004" => "类别名已存在",
        "-20005" => "货品不存在",
        "-20006" => "原密码错误",
        "-20007" => "是个目录",
        "-20008" => "备份文件出错",
        "-20009" => "路径出错",
        "-20010" => "文件写入出错",
        "-20011" => "操作权限不足",
        "-20012" => "文件不存在",
        "-20013" => "日志操作出错",
        "-20014" => "日志不存在",
        "-20030" => "数据库操作出错",
        "-20031" => "升级包出错",
        "-20032" => "同批次文件中存在相同文件",
        "-20033" => "没有组文件",
        "-20034" => "名称已存在",
        "-20035" => "酒店不存在",
        "-20037" => "手机号码不正确",
        "-20038" => "邮箱不正确",
        "-20039" => "邮箱密文不正确",
        "-20040" => "验证码不正确",
        "-20041" => "标签名为空",
        "-20042" => "手机验证不正确",
        "-20043" => "手机发送失败",
        "-20044" => "发送消息数超出限制",
        "-20045" => "2次输入的密码不一样",
        "-20046" => "邮箱发送失败",
        "-20047" => "部门名称重复",
        "-20048" => "身份证号码不正确",
        "-20049" => "手机验证过程出错",
        "-20050" => "用户没有注册",
        "-20051" => "订单中含有该餐桌",
        "-20060" => "该员工已经邀请过了",
        "-20061" => "俩次输入的电话号码不一样",
        "-20062" => "未绑定微信账号,请使用账号密码登录",
        "-20063" => "此微信已绑定账号,不能重复绑定",
        "-20064" => "账号与微信不符,不能解除绑定",
        "-20065" => "用户没开票",
        "-20066" => "餐品出错",
        "-20067" => "发票错误",
        "-20068" => "减免金额出错",
        "-20069" => "二维码未设置",
        "-20070" => "系统消息无内容",
        "-20071" => "消息id不能为空",
        "-20072" => "该餐品无份量规格",
        "-20073" => "地址已存在",
        "-20074" => "预约时间不在当前时间之后",
        "-20075" => "区域商代理不存在",
        "-20076" => "系统消息已发送",
        "-20077" => "原密码和新密码一样",
        "-20078" => "该员工无登录权限",
        "-20079" => "代理商区域重复",
        "-20080" => "此代理商下有店铺，不能修改",
        "-20081" => "管理员重复(一个账号只能创建一个店铺)",
        "-20082" => "未获得授权,请联系店铺管理人员设置",
        "-20083" => "管理员账号或密码不正确",
        "-20084" => "加载失败,请重新操作",
        "-30010" => "图片过多",
        "-30011" => "餐品名称已存在",
        "-30012" => "订单状态出错",
        "-30013" => "请在微信中打开",
        "-30014" => "店铺不存在",
        "-30015" => "订单处于不可更改阶段，可重新下单",
        "-30016" => "订单已确认，不能修改",
        "-30017" => "订单已支付，不能修改",
        "-30018" => "订单已完成，不能修改",
        "-30019" => "订单已作废，不能修改",
        "-30020" => "订单超时，不能修改",
        "-30021" => "订单已出单，不能修改",
        "-30022" => "订单出错，不能修改",
        "-30023" => "餐桌号不存在",
        "-30024" => "店铺不存在",
        "-30025" => "餐品存量不够",
        "-30026" => "订单操作出错",
        "-30027" => "餐品不存在",
        "-30028" => "订单不存在",
        "-30029" => "订单已有变动，请刷新后再操作",
        "-30030" => "手机验证码超时",
        "-30031" => "手机验证码超时",
        "-30032" => "此分类下有商品，不能删除",
        "-30033" => "此部门下有员工，不能删除",
        "-30034" => "此部门员工已被冻结",
        "-30035" => "餐桌号不存在",
        "-30036" => "系统暂停使用",
        "-30037" => "预约不存在",
        "-30038" => "餐品已下架",
        "-30039" => "此职位下有员工，不能删除",
        "-30040" => "没有此分类名",
        "-30043" => "图片更换不到一个月",
        "-30044" => "此店铺已被冻结",
        "-30045" => "此代理商已被冻结",
        "-30041" => "支付取消或失败",
        "-30042" => "支付需要密码",
        "-30052" => "订单已催单",
        "-30053" => "催单时间还未到",
        "-30054" => "订单还未下单",
        "-20036" => "没有餐馆ID",
        "-30055" => "未开通微信支付",
    ];

    static public function toString($ret)
    {
        return self::$ret2msg[$ret];
    }
};

?>
