<?php
require_once("current_dir_env.php");
require_once("mgo_order.php");
require_once("mgo_shop.php");
require_once("const.php");
require_once("mgo_agent.php");
require_once("/www/public.sailing.com/php/mgo_stat_platform_byday.php");
require_once("mgo_stat_shop_byday.php");
require_once("/www/public.sailing.com/php/mgo_selfhelp.php");
require_once("redis_pay.php");
require_once "WxUtil.php";
use \Pub\Mongodb as Mgo;

function pay()
{

//     // <hr>GLOBALS['HTTP_RAW_POST_DATA']
//     $xml =<<<eof
//     <xml><appid><![CDATA[wxaaceede0e7695fcf]]></appid>
//     <attach><![CDATA[{"order_id":100000000023}]]></attach>
//     <bank_type><![CDATA[CMB_CREDIT]]></bank_type>
//     <cash_fee><![CDATA[1]]></cash_fee>
//     <device_info><![CDATA[WEB]]></device_info>
//     <fee_type><![CDATA[CNY]]></fee_type>
//     <is_subscribe><![CDATA[Y]]></is_subscribe>
//     <mch_id><![CDATA[1464120802]]></mch_id>
//     <nonce_str><![CDATA[324c948af2ec9760745db78fd8ac15c8]]></nonce_str>
//     <openid><![CDATA[oVQGs1Imf8L2EBcn2N0DyJRKQ8pc]]></openid>
//     <out_trade_no><![CDATA[1495991051]]></out_trade_no>
//     <result_code><![CDATA[SUCCESS]]></result_code>
//     <return_code><![CDATA[SUCCESS]]></return_code>
//     <sign><![CDATA[92318D3A2F4ADE9818D383693EA89C64]]></sign>
//     <sub_mch_id><![CDATA[1467121102]]></sub_mch_id>
//     <time_end><![CDATA[20170529010447]]></time_end>
//     <total_fee>1</total_fee>
//     <trade_type><![CDATA[JSAPI]]></trade_type>
//     <transaction_id><![CDATA[4004402001201705293101872675]]></transaction_id>
//     </xml>
// eof;
//     $xml =<<<eof
//     <xml><appid><![CDATA[wxaaceede0e7695fcf]]></appid>
//     <attach><![CDATA[{"order_id":"214"}]]></attach>
//     <bank_type><![CDATA[CFT]]></bank_type>
//     <cash_fee><![CDATA[1]]></cash_fee>
//     <device_info><![CDATA[WEB]]></device_info>
//     <fee_type><![CDATA[CNY]]></fee_type>
//     <is_subscribe><![CDATA[N]]></is_subscribe>
//     <mch_id><![CDATA[1464120802]]></mch_id>
//     <nonce_str><![CDATA[ce2a99c493958f18629008753fe5cd03]]></nonce_str>
//     <openid><![CDATA[oVQGs1CM07N3qgJNTyQMAIkmxEMw]]></openid>
//     <out_trade_no><![CDATA[1512465763_214]]></out_trade_no>
//     <result_code><![CDATA[SUCCESS]]></result_code>
//     <return_code><![CDATA[SUCCESS]]></return_code>
//     <sign><![CDATA[DF73867CE255EC53A05534F2F2645C8A]]></sign>
//     <sub_mch_id><![CDATA[1467121102]]></sub_mch_id>
//     <time_end><![CDATA[20171205172257]]></time_end>
//     <total_fee>1</total_fee>
//     <trade_type><![CDATA[JSAPI]]></trade_type>
//     <transaction_id><![CDATA[4200000026201712059263000413]]></transaction_id>
//     </xml>
// eof;

//     // Array
//     // (
//     //     [appid] => wxaaceede0e7695fcf
//     //     [attach] => {"order_id":100000000023}
//     //     [bank_type] => CMB_CREDIT
//     //     [cash_fee] => 1
//     //     [device_info] => WEB
//     //     [fee_type] => CNY
//     //     [is_subscribe] => Y
//     //     [mch_id] => 1464120802
//     //     [nonce_str] => 324c948af2ec9760745db78fd8ac15c8
//     //     [openid] => oVQGs1Imf8L2EBcn2N0DyJRKQ8pc
//     //     [out_trade_no] => 1495991051
//     //     [result_code] => SUCCESS
//     //     [return_code] => SUCCESS
//     //     [sign] => 92318D3A2F4ADE9818D383693EA89C64
//     //     [sub_mch_id] => 1467121102
//     //     [time_end] => 20170529010447
//     //     [total_fee] => 1
//     //     [trade_type] => JSAPI
//     //     [transaction_id] => 4004402001201705293101872675
//     // )

    $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
    $ary = \Pub\Wx\Util::FromXml($xml);
    //LogDebug($ary);
    if($_REQUEST['debug'])
    {
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        echo "<pre>";
        print_r($ary);
        echo "<hr>";
        print_r(Cfg::instance());
    }


    $sign = \Pub\Wx\Util::GetSign($ary);
    if($sign !== $ary['sign'] || "SUCCESS" != $ary['return_code'])
    {
        LogErr("sign err, sign:[$sign], return_code:{$ary['return_code']}");
        return errcode::SYS_ERR;
    }


    $order_info = json_decode($ary['attach']);
    if(!$order_info || !$order_info->order_id)
    {
        LogErr("param err");
        return;
    }

    // 检查当前订单是否可修改
    // 支付成功，修改订单状态
    //
    $mgo        = new \DaoMongodb\Order;
    $entry      = new \DaoMongodb\OrderEntry;
    $shop       = new \DaoMongodb\Shop;
    $selfhelp   = new Mgo\Selfhelp;
    $agent_mgo  = new \DaoMongodb\Agent;
    $platformer = new Mgo\StatPlatform;

    $order         = $mgo->GetOrderById($order_info->order_id);
    $selfhelp_info = $selfhelp->GetExampleById($order->selfhelp_id);
    $self_type     = $selfhelp_info->using_type;
    $shopinfo      = $shop->GetShopById($order->shop_id);
    $agent_info    = $agent_mgo->QueryById($shopinfo->agent_id);

    if($shopinfo->auto_order == AutoOrder::Yes) //用于判断店铺端设置是否支付自动下单,因为没设置的话需要Pad来确认
    {
        $is_confirm        = IsCoonfirm::Yes;  //如果PAD端设置自动下单开通属于了确定
        $kitchen_status    = KitchenStatus::WAITMAKE;
        // 更新餐品日销售量
        // 增加餐品售出数<<<<<<<<<2018.7.11,产品和测试说与自动下单没关系了,只要支付成功都扣库存
        //PageUtil::UpdateFoodDauSoldNum($order_info->order_id);
    }else{
        if($order->is_confirm != IsCoonfirm::Yes)
        {
            $is_confirm = IsCoonfirm::NO;  //如果PAD端设置自动下单为开通属于未确定
        }
    }
//    //自助点餐机过来的数据
//    if($order_info->srctype == NewSrctype::SELFHELP)
//    {
//        // 增加餐品售出数
//        PageUtil::UpdateFoodDauSoldNum($order_info->order_id);
//    }
    $paid_price = $ary['total_fee']/100;
    $entry->order_id         = $order_info->order_id;
    $entry->order_status     = OrderStatus::PAID;
    $entry->pay_way          = PayWay::WEIXIN;
    $entry->pay_status       = PayStatus::PAY;
    $entry->is_confirm       = $is_confirm;
    $entry->paid_price       = $paid_price;
    $entry->order_waiver_fee = $order->order_payable - $paid_price;
    $entry->pay_time         = time();
    $entry->is_ganged        = $self_type;
    $entry->kitchen_status   = $kitchen_status;
    //LogDebug($entry);
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    //保存统计数据
    PageUtil::PayOrderBoard($order, $shopinfo, $agent_info, $paid_price);

    $redis = new \DaoRedis\Pay();
    $info  = new \DaoRedis\PayEntry();
    $info->order_id       = $order_info->order_id;
    $info->out_trade_no   = $ary['out_trade_no'];
    $info->is_pay         = 1;
    $info->pay_price      = $paid_price;
    $save = $redis->Save($info);
    if(0 != $save)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    if($shopinfo->auto_order == AutoOrder::Yes || $order->selfhelp_id)
    {
        $what = [
            "chose_food"      => 1,
            "back_chose_food" => 1,
            "pay_order"       => 1
        ];
        // 因为属于自动自动下单,所以直接发送到打印机上面（打印机变动通知）
        $ret_json1 =  PageUtil::NotifyOrderPrint($order->shop_id, $order_info->order_id, $what);
        $ret_json_obj1 = json_decode($ret_json1);
        if(0 != $ret_json_obj1->ret)
        {
            LogErr("order printer change send err");
            //return errcode::SYS_BUSY;
        }
    }
    //发送短信
    $order_s  = $mgo->GetOrderById($order_info->order_id);
    PageUtil::PayOrderGetMessage($order_s, $shopinfo);
    // 更新缓存1
    \Cache\Order::Clear($order_info->order_id);
    $token = $order_info->token;
    //LogDebug($token);
    if($token)
    {
        // 因微信直接发来的消息是没有经过加解密解析过程，所
        // 在cache中没有记录token相关数据，这里手动加载
        \Cache\Login::Get($token);
        $ret_json2 = PageUtil::NotifyWxPay($order_info->order_id, $entry->paid_price, $token);
        $ret_json_obj2 = json_decode($ret_json2);
        //LogDebug($ret_json_obj->ret);
        if(0 != $ret_json_obj2->ret)
        {
            LogErr("weixin pay send err");
            //return errcode::SYS_BUSY;
        }
    }

    // 订单变动通知
    $ret_json3     = PageUtil::NotifyOrderChange($shopinfo->shop_id, $order_info->order_id, $entry->order_status, $entry->pay_time);
    $ret_json_obj3 = json_decode($ret_json3);
    LogDebug($ret_json_obj3->ret);
    if(0 != $ret_json_obj3->ret)
    {
        LogErr("Order Send err");
    }

    // 更新餐品日销售量
    // 增加餐品售出数
    if($order->order_from != OrderFrom::SHOUYIN && $order->order_from != OrderFrom::PAD && $order->order_sure_status != OrderSureStatus::SURE)
    {
        PageUtil::UpdateFoodDauSoldNum($order_info->order_id);
    }

    //LogDebug($ret);
    return 0;
}

function main()
{
    $ret = pay();
    if(0 == $ret)
    {
        $ret_xml =<<<eof
<xml>
   <return_code><![CDATA[SUCCESS]]></return_code>
   <return_msg><![CDATA[OK]]></return_msg>
</xml>
eof;
        echo $ret_xml;
    }
    else
    {

        $ret_xml =<<<eof
<xml>
   <return_code><![CDATA[FAIL]]></return_code>
   <return_msg><![CDATA[出错]]></return_msg>
</xml>
eof;
        echo $ret_xml;
    }
}

main();

?>
