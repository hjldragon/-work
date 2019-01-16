<?php
require_once("current_dir_env.php");
require_once("mgo_order.php");
require_once("mgo_shop.php");
require_once("const.php");
require_once("mgo_stat_platform_byday.php");
require_once("mgo_stat_shop_byday.php");

function pay()
{

    // [gmt_create] => 2018-04-04 14:34:51
    // [charset] => UTF-8
    // [seller_email] => liuxiao@xinchihuo.com.cn
    // [subject] => 小米姑娘二家66
    // [sign] => X8IAaXpzJZneFTq7gdWnIA+KG8fbf1OLztQs0QteADdpjnkCirspjvqbaDrw1q8TdMKIeJmtpHS/Z/bj/8lRU3LdbQQk9zTDC7+ki904xgitUNSPweeG1cl+MaQAfnrkss65/Q4lF73X/IgWQi457BjgoaqdM0QeYxEMln70JnjocUDduRvLdLHLaMNMhPa6Eh0UCGWAXxUE00avvCnGX4k8VN548kmj2P+nGWlkxelue8+7E/IMC6woyy8Fr9cTELvQ189mYIN89aRTr+pHPOXKs2OD5AXifXFaduF8dCWzHmgH5X7/nz3/KUbJVrc8idnU2vbSB270CDf/SlPjIg==
    // [body] => {"order_id":"SL2900"}
    // [buyer_id] => 2088412587094002
    // [invoice_amount] => 0.02
    // [notify_id] => 124703c25f0d9dda90a86c55f0da9a2g05
    // [fund_bill_list] => [{"amount":"0.02","fundChannel":"PCREDIT"}]
    // [notify_type] => trade_status_sync
    // [trade_status] => TRADE_SUCCESS
    // [receipt_amount] => 0.02
    // [app_id] => 2018031302365379
    // [buyer_pay_amount] => 0.02
    // [sign_type] => RSA2
    // [seller_id] => 2088031549791447
    // [gmt_payment] => 2018-04-04 14:34:55
    // [notify_time] => 2018-04-04 14:34:55
    // [version] => 1.0
    // [out_trade_no] => 1522823634_SL2900
    // [total_amount] => 0.02
    // [trade_no] => 2018040421001004000515880005
    // [auth_app_id] => 2018031302365379
    // [buyer_logon_id] => 176****9266
    // [point_amount] => 0.00



    $data = $_POST;
    LogDebug($data);
    if("TRADE_SUCCESS" != $data['trade_status'])
    {
        LogErr("trade_status:{$data['trade_status']}");
        return errcode::SYS_ERR;
    }

    $order_info = json_decode($data['body']);
    if(!$order_info || !$order_info->order_id)
    {
        LogErr("param err");
        return;
    }
    LogDebug($order_info->order_id);

    // 检查当前订单是否可修改

    //
    // 支付成功，修改订单状态
    //
    $mgo      = new \DaoMongodb\Order;
    $entry    = new \DaoMongodb\OrderEntry;
    $shop     = new \DaoMongodb\Shop;
    $order    = $mgo->GetOrderById($order_info->order_id);
    $shopinfo = $shop->GetShopById($order->shop_id);
    if($shopinfo->auto_order == AutoOrder::Yes) //用于判断店铺端设置是否支付自动下单,因为没设置的话需要Pad来确认
    {
        $is_confirm        = IsCoonfirm::Yes;  //如果PAD端设置自动下单开通属于了确定
        if($order->order_status == OrderStatus::PAID)
        {
            $what = [
                "chose_food"      => 1,
                "back_chose_food" => 1,
                "pay_order"       =>1
            ];
        }else{
            $what = [];
        }
        // 因为属于自动自动下单,所以直接发送到打印机上面（打印机变动通知）
        $ret_json =  PageUtil::NotifyOrderPrint($order->shop_id, $order_info->order_id, $what);
        $ret_json_obj = json_decode($ret_json);
        if(0 != $ret_json_obj->ret)
        {
            LogErr("order printer change send err");
            //return errcode::SYS_BUSY;
        }
        //如果属于直接确认单，就保存统计数据
        $platformer     = new \DaoMongodb\StatPlatform;
        $day            = date('Ymd',time());
        $platform_id    = PlatformID::ID;//现在只有一个运营平台id
        $consume_amount = (float)$data['total_amount'];
        $customer_num   = $order->customer_num;
        $platformer->SellNumAdd($platform_id, $day, ['consume_amount'=>$consume_amount,'customer_num'=>$customer_num]);
        //保存平台消费者数据统计
        $shop_stat      = new \DaoMongodb\StatShop;
        $shop_stat->SellShopNumAdd($shopinfo->shop_id, $day, ['consume_amount'=>$consume_amount,'customer_num'=>$customer_num], $shopinfo->agent_id);

        // 更新餐品日销售量
        // 增加餐品售出数
        PageUtil::UpdateFoodDauSoldNum($order_info->order_id);

    }else{
        if($order->is_confirm != IsCoonfirm::Yes)
        {
            $is_confirm = IsCoonfirm::NO;  //如果PAD端设置自动下单为开通属于未确定
        }
    }
    $entry->order_id       = $order_info->order_id;
    $entry->order_status   = OrderStatus::PAID;
    $entry->pay_way        = PayWay::ALIPAY;
    $entry->pay_status     = PayStatus::PAY;
    $entry->is_confirm     = $is_confirm;
    $entry->paid_price     = $data['total_amount'];
    $entry->pay_time       = time();
    LogDebug($entry);
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    // 更新缓存
    \Cache\Order::Clear($order_info->order_id);

    $token = $order_info->token;
    if($token)
    {   
        \Cache\Login::Get($token);
        $ret_json = PageUtil::NotifyWxPay($order_info->order_id, $token);
        $ret_json_obj = json_decode($ret_json);
        if(0 != $ret_json_obj->ret)
        {
            LogErr("alipay send err");
            //return errcode::SYS_BUSY;
        }
    }

    // 更新餐品日销售量
    // 增加餐品售出数
    PageUtil::UpdateFoodDauSoldNum($order_info->order_id);


    LogDebug($ret);
    return 0;
}

function main()
{
    LogDebug("begin...");
    $ret = pay();
    if(0 == $ret)
    {
        $ret_xml = "success";
        echo $ret_xml;
    }
}

main();

?>
