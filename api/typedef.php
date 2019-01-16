<?php
class Order
{
    static public $order_status = [
        0 => null,
        3 => 7,
        4 => 3,
        5 => 4,
        6 => 5,
        7 => 6,
        1 => 1,
        2 => 2,
    ];
    static public $dine_way = [
        0 => null,
        1 => 1,
        2 => 3,
        3 => 2,
        4 => 4
    ];
    static public $is_invoicing = [
        0 => null,
        1 => 0,
        2 => 1
    ];
    static public $pay_way = [
        0 => null,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 5,
        5 => 5,
    ];
    static public $invoice_gave = [
        1 => '已开票',
        0 => "未开票"
    ];
    static public $kitchen_status = [
        1 => '等待制作',
        2 => "制作完成"
    ];
    static public $type_name = [
        3 => "已反结",
        4 => "退款成功",
        5 => "退款失败",
        6 => "已关闭",
        7 => "挂账",
        1 => '未支付',
        2 => "已支付",
    ];
    static public $pay_type = [
        1 => "现金支付",
        2 => "微信支付",
        3 => "支付宝支付",
        4 => "银行卡支付",
        5 => "挂账",
    ];
    static public $sale_type = [
        1 => "在店吃",
        2 => "自提",
        3 => "打包",
        4 => "外卖",
    ];
    static public $pay_status = [
        1 => "未支付",
        2 => "已支付",
        3 => "挂账"
    ];
    static public $order_from = [
        1 => "智能收银机",
        2 => "自助点餐机",
        3 => "扫码点餐",
        4 => "平板智能点餐",
        5 => "掌柜通",
        6 => "小程序",
    ];
    static public $order_sure_status = [
        true  => 3,
        false => 2
    ];
    static public $istake = [
        true  => 1,
        false => 0
    ];
    static public $isgive = [
        true  => 1,
        false => 0
    ];
    static public $is_ps = [
        1 => true,
        0 => false
    ];

    static public $weight = [
        "大" => 1,
        "中" => 2,
        "小" => 3,
         ""  => 0,
        null => 0
    ];
    static public $weight_pad = [
        1    => "大" ,
        2    => "中" ,
        3    => "小" ,
        0    => "",
        null => 0
    ];
    static public $save_orderpay = [
        0 => 1,
        1 => 4,
        2 => 5,
        3 => 2,
        4 => 3
    ];
    static public $order_from_get = [
        1 => 1,
        0 => 4,
    ];
}

class Reserve
{
    static public $reservation_status = [
        1 => 0,
        2 => 1,
        3 => 2,
    ];
    static public $reservation_sex = [
        1 => '男',
        2 => '女'
    ];
    static public $sex = [
       '男'=>1,
       '女'=>2
    ];
    static public $status = [
        0 => 1,
        1 => 2,
        2 => 3,
    ];
    static public $source = [
        1 => "收银台"
    ];
}

class Shopinfo
{
    static public $erasure = [
        1  => 0,
        2  => 1,
        -1 => -1
    ];
    static public $auto_order = [
        1    => true,
        0    => false,
        null => null,
    ];
    static public $custom_screen = [
        1    => true,
        0    => false,
        null => false,
    ];
    static public $bookkeeping = [
        1 => true,
        0 => false
    ];
    static public $type = [
        1 => "点菜单(后厨)",
        2 => "作废单(后厨)",
        3 => "点菜单(消费者)",
        4 => "结账单",
        5 => "预结账单"
    ];
    static public $save_auto_order = [
        "true"  => 1,
        "false" => 0
    ];
    static public $save_custom_screen = [
       "true"  => 1,
       "false" => 0
    ];
    static public $sort = [
        0  => 2,
        1  => 1
    ];
    static public $shop_sort = [
        2  => 0,
        1  => 1
    ];
}
?>