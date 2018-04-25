<?php
/*
 * [Rocky 2017-05-12 20:16:27]
 * 订单信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_order.php");
require_once("mgo_seat.php");
require_once("mgo_menu.php");
require_once("mgo_stat_food_byday.php");
require_once("redis_id.php");
require_once("const.php");
require_once("cache.php");
require_once("page_util.php");
require_once("mgo_order_status.php");
require_once("mgo_stat_platform_byday.php");
require_once("mgo_stat_shop_byday.php");
//$_=$_REQUEST;
function OrderStatusCheck($order_status)
{
    LogDebug($order_status);
    switch($order_status){
        case OrderStatus::PENDING:
            return 0; // 待处理的订单可以修改
            break;
        case OrderStatus::CONFIRMED:
        case OrderStatus::POSTPONED:
            return errcode::ORDER_ST_CONFIRMED;
            break;
        case OrderStatus::PAID:
            return errcode::ORDER_ST_PAID;
            break;
        case OrderStatus::FINISH:
            return errcode::ORDER_ST_FINISH;
            break;
        case OrderStatus::CANCEL:
            return errcode::ORDER_ST_CANCEL;
            break;
        case OrderStatus::TIMEOUT:
            return errcode::ORDER_ST_TIMEOUT;
            break;
        case OrderStatus::PRINTED:
            return errcode::ORDER_ST_PRINTED;
            break;
        default:
            return errcode::ORDER_STATUS_ERR;
            break;
    }
    return 0;
}
//下单
function SaveOrderInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id          = $_['order_id'];
    $customer_id       = $_['customer_id'];
    $shop_id           = $_['shop_id'];
    $dine_way          = $_['dine_way'];
    $pay_way           = $_['pay_way'];


    $customer_num      = $_['customer_num'];
    $seat_id           = $_['seat_id'];
    $order_status      = $_['order_status'];
    $food_num_all      = $_['food_num_all'];
    $food_price_all    = $_['food_price_all'];
    $food_list         = json_decode($_['food_list']);
    $is_invoicing      = $_['is_invoicing'];
    $invoice_type      = $_['invoice_type'];
    $userid            = $_['userid'];
    $order_from        = $_['order_from'];
    $status_info       = $_['status_info'];
    $checkout_time     = $_['checkout_time'];
    $refunds_time      = $_['refunds_time'];
    $pay_time          = $_['pay_time'];
    $refunds_fail_time = $_['refunds_fail_time'];
    $order_remark      = $_['order_remark'];
    //$maling_price      = $_['maling_price'];

    if(!$order_id){
        //非新建必须有的数据
        if(!$shop_id || !$seat_id || !$customer_id || 0 == count($food_list))
        {
            LogErr("param err, shop_id:[$shop_id], seat_id:[$seat_id]");
            return errcode::SYS_ERR;
        }
        $order_id        = \DaoRedis\Id::GenOrderId();
        $order_water_num = date('Ymd', time()) . \DaoRedis\Id::GenOrderWNId();
        if(!$order_status)
        {
            $order_status = OrderStatus::PENDING; //未支付状态
            $pay_status   = 1;

        }
        if($order_status == OrderStatus::PRINTED)
        {
            $pay_status   = 3;
        }

        if(!$pay_way || $pay_way == 1)
        {
            $pay_way = 0;   //未确定
        }
        if(!$dine_way)
        {
            $dine_way = 1;   //在店吃
        }
            $is_appraise = 0;   //未评价
    }
    $shopinfo = \Cache\Shop::Get($shop_id);
    if(!$shopinfo)
    {
        LogErr("get shopinfo err, shop_id:[$shop_id]");
        return errcode::SHOP_NOT_EXIST;
    }

    // 检查店铺是否正常
    if(ShopIsSuspend::IsSuspend($shopinfo->suspend))
    {
        LogErr("shop suspend, shop_id:[$shop_id]");
        return errcode::SHOP_SUSPEND;
    }
    //IPD端订单下单处理功能
    //$db_orderinfo = \Cache\Order::Get($order_id);
   // if($db_orderinfo)
   // {
    /*    if(OrderStatus::PENDING == $db_orderinfo->order_status
            && time() - $db_orderinfo->order_time > Cfg::instance()->order_timeout_sec)
        {
            $db_orderinfo->order_status = OrderStatus::TIMEOUT;
        }*/
        // 查看当前订单状态
   /*     $ret = OrderStatusCheck($db_orderinfo->order_status);
        if(0 != $ret)
        {
            LogErr("order err, id:[$order_id], ret:[$ret]");
            return $ret;
        }*/

   /*     if($lastmodtime != $db_orderinfo->lastmodtime)
        {
            LogErr("order change, id:[$order_id], [$lastmodtime] -- [{$db_orderinfo->lastmodtime}]");
            return errcode::ORDER_HAD_CHANGE;
        }*/
   // }

    if($food_list){
        $need_food_list = [];
        //因为一个订单中相同的菜品可能会存在多个(打包,赠送情况)
        foreach ($food_list as $v)
        {
            $need_food_list[$v->food_id]->food_num += $v->food_num;
            $need_food_list[$v->food_id]->food_id   = $v->food_id;
        }
        // 检查餐品库存够不够
        $food = PageUtil::CheckFoodStockNum($shop_id, $need_food_list);
        foreach ($food as $v) {
            $food_id   = $v->food_id;
            $food_name = $v->food_name;
        }
        if(null != $food){
            $resp = (object)array(
                'food_id'   => $food_id,
                'food_name' => $food_name,
            );
            LogErr("not enough, food_id:[{$food_id}]");
            return errcode::FOOD_NOT_ENOUGH;
        }
        // 取餐品信息
        $food_all = [];
        foreach ($food_list as $i => &$item) {
            $db_food_info = \Cache\Food::Get($item->food_id);
            if(!$db_food_info){
                LogErr("food err:[{$item->food_id}]");
                return errcode::FOOD_ERR;
            }
            // 检查菜品是否已下架
            $sale_off = PageUtil::GetFoodSaleOff($db_food_info);
            if(1 == $sale_off)
            {
                LogErr("Food Sale_off:[{$item->food_id}]");
                return errcode::FOOD_SALE_OFF;
            }
            $food_price = getPrice($item, $customer_id, $price);//计算单种菜品价格
           //$price_one  = GetFoodPrice($item, $customer_id, $price);//菜品单价
            if(null === $food_price){
                LogErr("price error");
                return errcode::ORDER_ST_ERR;
            }
            //获取餐品列表信息保存到订单中
            $food_list_all                   = (object)array();
            $food_list_all->food_id          = $db_food_info->food_id;
            $food_list_all->food_name        = $db_food_info->food_name;
            $food_list_all->food_price       = $price;
            $food_list_all->food_category    = $db_food_info->category_id;
            $food_list_all->food_price_sum   = $food_price;
            $food_list_all->food_attach_list = $item->spec;
            $food_list_all->food_unit        = $db_food_info->food_unit;
            $food_list_all->food_num         = $item->food_num;
            $food_list_all->pack_num         = $item->pack_num;
            if($item->pack_num)
            {
                $food_list_all->is_pack         = 1;
                $food_list_all->is_send         = 0;
            }
            $food_list_all->food_remark      = $item->food_remark;
            array_push($food_all, $food_list_all);
            $price_all += $food_price;//累计菜品总价
        }

        // 餐位费
        $seatinfo = \Cache\Seat::Get($seat_id);
        if(SeatPriceType::NO != $seatinfo->price_type){
            switch ($seatinfo->price_type) {
                case SeatPriceType::NUM:
                    $seat_price = $seatinfo->price * $customer_num;
                    break;
                case SeatPriceType::FIXED:
                    $seat_price = $seatinfo->price;
                    break;
                case SeatPriceType::RATIO:
                    $seat_price = $seatinfo->price/100 * (float)$price_all;
                    break;
                default:
                    $seat_price = 0;
                    break;
            }
        } else {
            $seat_price = 0;
        }
        $all_price = round($price_all +round($seat_price,2),2);
        //与前端传的总价对比
        if($all_price!= round((float)$food_price_all,2)){
            LogErr("price_all error");
            return errcode::ORDER_ST_ERR;
        }
    }else{
        $food_list = null;
    }
    //LogDebug($all_price);
	// 如果提供了发票就将发票信息保存到订单信息中
    if($is_invoicing != 0){
        $invoice = (object)array();
        $db_invoice_info = \Cache\Invoice::Get($userid);
        if(!$db_invoice_info){
            LogErr("invoice err:[{$userid}]");
            return errcode::INVOICE_IS_ERR;
        }
        if($invoice_type == 1)
        {
            $invoice = $db_invoice_info->paperindinvoice;
            $invoice->type = (int)$invoice_type;
        }
        if($invoice_type == 2)
        {
            $invoice = $db_invoice_info->paperunitinvoice;
            $invoice->type = (int)$invoice_type;
        }
        if($invoice_type == 3)
        {
            $invoice = $db_invoice_info->eleindinvoice;
            $invoice->type = (int)$invoice_type;
        }
        if($invoice_type == 4)
        {
            $invoice = $db_invoice_info->eleunitinvoice;
            $invoice->type = (int)$invoice_type;
        }


/*        if(IsInvoice::NO != (int)$invoice_type && $invoice_id){
            $db_invoice_info = \Cache\Invoice::Get($userid);
            if(!$db_invoice_info){
                LogErr("invoice err:[{$invoice_id}]");
                return errcode::INVOICE_IS_ERR;
            }
            $invoice->type           = $db_invoice_info->type;
            $invoice->title_type     = $db_invoice_info->title_type;
            $invoice->invoice_id     = $db_invoice_info->invoice_id;
            $invoice->invoice_title  = $db_invoice_info->invoice_title;
            $invoice->duty_paragraph = $db_invoice_info->duty_paragraph;
            $invoice->phone          = $db_invoice_info->phone;
            $invoice->address        = $db_invoice_info->address;
            $invoice->bank_name      = $db_invoice_info->bank_name;
            $invoice->bank_account   = $db_invoice_info->bank_account;
            $invoice->email          = $db_invoice_info->email;
            $invoice->invoice_type   = $invoice_type;
        }else{
            $invoice->invoice_type   = $invoice_type;
        }*/
    }else{
        $invoice = null;
    }
    $mgo = new \DaoMongodb\Order;
    $entry = \Cache\Order::Get($order_id); // 先取出原来信息 // 以后再考滤下事务性[XXX]
    if(!$entry)
    {
        $entry = new \DaoMongodb\OrderEntry;
    }

    if(!$order_id)
    {
        //保存商户消费额度统计数据
        $platformer     = new \DaoMongodb\StatPlatform;
        $day            = date('Ymd',time());
        $platform_id    = 1;//现在只有一个运营平台id
        $qr_order_num   = 1; //扫码点餐及下一次单就属于点一次
        $platformer->SellNumAdd($platform_id, $day, ['qr_order_num'=>$qr_order_num]);
        //保存平台消费者数据统计
        $shop_stat       = new \DaoMongodb\StatShop;
        $sc_qr_order_num = 1; //扫码点餐及下一次单就属于点一次
        $shop_stat->SellShopNumAdd($shop_id, $day, [
            'qr_order_num'  => $sc_qr_order_num
            ], $shopinfo->agent_id);
    }
    if(null !== $price_all)
    {
        $order_fee = $price_all + $seat_price;
    }

    $entry->order_id          = $order_id;
    $entry->customer_id       = $customer_id;
    $entry->shop_id           = $shop_id;
    $entry->order_from        = $order_from;
    $entry->order_water_num   = $order_water_num; //流水号
    $entry->dine_way          = $dine_way;
    $entry->pay_way           = $pay_way;
    $entry->customer_num      = $customer_num;
    $entry->seat_id           = $seat_id;
    $entry->order_status      = $order_status;
    $entry->status_info       = $status_info;
    $entry->food_list         = $food_all;
    $entry->order_time        = time();
    $entry->checkout_time     = $checkout_time;
    $entry->refunds_time      = $refunds_time;
    $entry->refunds_fail_time = $refunds_fail_time;
    $entry->order_remark      = $order_remark;
    $entry->food_num_all      = $food_num_all;
    $entry->food_price_all    = $price_all;
    // $entry->maling_price      = $maling_price;
    $entry->is_invoicing      = $is_invoicing;
    $entry->order_sure_status = 1;   //都属于未下单
    $entry->is_appraise       = $is_appraise;
    $entry->order_from        = 3;//<<<<<<这里是用在手机端的方法所以来源是扫码
    $entry->pay_time          = $pay_time;
    $entry->pay_status        = $pay_status;
    $entry->seat_price        = $seat_price;
    $entry->invoice           = $invoice;
    $entry->is_confirm        = 0;
    $entry->order_fee         = $order_fee;
    $entry->order_payable     = $order_fee;//应付价格=菜品总价+餐位费-减免费 order_waiver_fee可能由服务员修改（与客人端无关
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    // 通知到golang服务器，再转到店管理员页，以提醒服务员处理
    // （注：选在线支付的，在支付完成后，再做相应处理）
    $lastmodtime = time();
    $ret_json    = PageUtil::NotifyOrderChange($shop_id, $order_id, $order_status, $lastmodtime);
    $ret_json_obj = json_decode($ret_json);
    if(0 != $ret_json_obj->ret)
    {
        LogErr("Order err");
        return errcode::SYS_BUSY;
    }

    $resp = (object)array(
        'order_id'         => $entry->order_id,
        'order_time'       => $entry->order_time,
        'order_status'     => $entry->order_status,
        'order_fee'        => $entry->order_fee,
        'order_waiver_fee' => $entry->order_waiver_fee,
        'order_payable'    => $entry->order_payable,
        'pay_time'         => $entry->pay_time
    );
    
    LogInfo("save ok");
    return 0;
}
//计算点餐菜品总价格(餐品单价及数量和餐盒费总计)
function getPrice($food, $customer_id, &$price){
    $db_food_info = \Cache\Food::Get($food->food_id);
    if(!$db_food_info)
    {
        LogErr("FoodInfo err");
        return null;
    }
    $db_customer_info = \Cache\Customer::Get($customer_id);
    if(!$db_customer_info)
    {
        LogErr("Customer err");
        return null;
    }
    //如果是配件直接返回价格
    if($db_food_info->type == 2)
    {
        $price = $db_food_info->food_price;
        return $db_food_info->food_price * (int)$food->food_num;
    }
    //不是配件的返回价格
    $using = $db_food_info->food_price->using;
    if (($using & PriceType::FESTIVAL) != 0){// 节日价
        $name[] = 'festival_price';
    }
    if (($using & PriceType::VIP) != 0 && $db_customer_info->is_vip){// 会员价
        $name[] = 'vip_price';
    }
    if (($using & PriceType::DISCOUNT) != 0) {// 折扣价
        $name[] = 'discount_price';
    }
    if (($using & PriceType::ORIGINAL) != 0) {// 普通价

        $name[] = 'original_price';
    }
    //算出餐品的价格,spec_type = 0(无规格的价格),1,2,3大中小
        foreach ($db_food_info->food_price->price as $key => $value) {
           if($food->spec_type == $value->spec_type){
               //取最小价格
               $price = 99999999;
               foreach ($name as  $item) {
                   if($value->$item < $price && $value->$item >= 0){
                       $price = $value->$item;
                   }
               }
           }
        }
    if(99999999 == $price){
        LogErr("SpecPrice err");
        return null;
    }
    $accessory_price = 0;
    //是否有餐盒费及是否有打包
    if($db_food_info->accessory && $food->pack_num){
        $accessory = \Cache\Food::Get($db_food_info->accessory);
        $accessory_price = $accessory->food_price * (int)$food->pack_num*(float)$db_food_info->accessory_num;
    }
    //算出餐品及餐盒的总价
    $food_price = $price * (int)$food->food_num + $accessory_price;
    return $food_price;
}
//菜品单价
function GetFoodPrice($food, $customer_id, &$price){
    $db_food_info = \Cache\Food::Get($food->food_id);
    if(!$db_food_info)
    {
        LogErr("FoodInfo err");
        return null;
    }
    $db_customer_info = \Cache\Customer::Get($customer_id);
    if(!$db_customer_info)
    {
        LogErr("Customer err");
        return null;
    }
    //如果是配件直接返回价格
    if($db_food_info->type == 2)
    {
        return $db_food_info->food_price;
    }
    //不是配件的返回价格
    $using = $db_food_info->food_price->using;
    if (($using & PriceType::FESTIVAL) != 0){// 节日价
        $name[] = 'festival_price';
    }
    if (($using & PriceType::VIP) != 0 && $db_customer_info->is_vip){// 会员价
        $name[] = 'vip_price';
    }
    if (($using & PriceType::DISCOUNT) != 0) {// 折扣价
        $name[] = 'discount_price';
    }
    if (($using & PriceType::ORIGINAL) != 0) {// 普通价

        $name[] = 'original_price';
    }
    //算出餐品的价格,spec_type = 0(无规格的价格),1,2,3大中小
    foreach ($db_food_info->food_price->price as $key => $value) {
        if($food->spec_type == $value->spec_type){
            //取最小价格
            $price = 99999999;
            foreach ($name as  $item) {
                if($value->$item < $price && $value->$item > 0){
                    $price = $value->$item;
                }
            }
        }
    }
    if(99999999 == $price){
        LogErr("SpecPrice err");
        return null;
    }

    return $price;
}
//删除订单
function DeleteOrder(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $order_id     = $_['order_id'];
    $order_status = $_['order_status'];
    if($order_status == 1 || $order_status == 3)
    {
       return errcode::ORDER_OPR_ERR;
    }

    $entry     = new \DaoMongodb\OrderEntry;
    $mongodb   = new \DaoMongodb\Order();
    $ret       = $mongodb->Delete($order_id);

    if (0 != $ret) {
        LogErr("delete err");
        return errcode::SYS_ERR;
    }
    LogDebug($entry);

    $resp = (object)[
    ];
    LogInfo("delete ok");
    return 0;

}
//取消订单
function CancelOrder(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id   = $_['order_id'];
    $order_info = \Cache\Order::Get($order_id);

    if($order_info)
    {
        if($order_info->order_sure_status != \OrderSureStatus::NOSURE)
        {
            return errcode::ORDER_ST_PRINTED;
        }
    }
    $order_status        = NewOrderStatus::CLOSER;
    $entry               = new \DaoMongodb\OrderEntry;
    $mongodb             = new \DaoMongodb\Order();
    $entry->order_id     = $order_id;
    $entry->order_status = $order_status;
    $entry->close_time   = time();
    $ret                 = $mongodb->Save($entry);

    if (0 != $ret) {
        LogErr("delete err");
        return errcode::SYS_ERR;
    }
    //LogDebug($entry);
    $resp = (object)[
        'order_id'         => $entry->order_id,
        'close_time'       => $entry->close_time,
        'order_status'     => $entry->order_status
    ];
    LogInfo("close order ok");
    //微信取消订单发错推送
    $ret_json = PageUtil::NotifyOrderChange($order_info->shop_id, $order_id, $order_status, time());
    //LogDebug($ret_json);
    $ret_json_obj = json_decode($ret_json);
    //LogDebug($ret_json_obj);
    if(0 != $ret_json_obj->ret)
    {
        LogErr("Order send pad err");
    }
    return 0;

}
//催单
function UrgeOrder(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id   = $_['order_id'];
    $order_info = \Cache\Order::Get($order_id);
    if($order_info){
        //先做判断是否已经下单
//        if($order_info->is_confirm == \OrderSureStatus::NOSURE){
//            LogErr("Order is confirm");
//            return errcode::ORDER_ST_PRINTED;
//        }
        //是否已经催单
        if($order_info->order_time + 10 * 60 <= time()){
            if($order_info->is_urge != 1){
                $is_urge = 1;
            } else {
                LogErr("Order is ugre");
                return errcode::ORDER_IS_URGE;
            }
        } else {
            LogErr("Order uger time not");
            return errcode::ORDER_URGE_TIME;
        }
    }else{
        LogErr("Order err");
         return errcode::ORDER_NOT_EXIST;
    }
    $entry               = new \DaoMongodb\OrderEntry;
    $mongodb             = new \DaoMongodb\Order;
    $entry->order_id     = $order_id;
    $entry->is_urge      = $is_urge;
    $ret                 = $mongodb->Save($entry);

    if (0 != $ret) {
        LogErr("save ok");
        return errcode::SYS_ERR;
    }
    //LogDebug($order_info);
    $ret_json = PageUtil::NotifyOrderRemind($order_info);
    LogDebug("[$ret_json]");

    $ret_json_obj = json_decode($ret_json);
    if(0 != $ret_json_obj->ret)
    {
        LogErr("Order err");
        return errcode::SYS_BUSY;
    }
    $resp = (object)[
        'order_id'         => $entry->order_id,
        'is_urge'          => $entry->is_urge
    ];
    LogInfo("ok");
    return 0;

}
//申请退款
function RefundOrder(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id                         = $_['order_id'];
    $made_reson                       = $_['made_reson'];
    $customer_id                      = $_['customer_id'];

    $entry          = new \DaoMongodb\OrderEntry;
    $mongodb        = new \DaoMongodb\Order;
    $order_status   = new \DaoMongodb\OrderStatus;
    $order          = new \DaoMongodb\OrderStatusEntry;

    $entry->order_id     = $order_id;
    $entry->order_status = 8;
    $ret  = $mongodb->Save($entry);

    $order->id           = \DaoRedis\Id::GenOrderStatusId();
    $order->order_id     = $order_id;
    $order->delete       = 0;
    $order->order_status = 8;
    $order->apply_time   = time();
    $order->made_reson   = $made_reson;
    $order->customer_id  = $customer_id;
    //$entry->is_confirm                = 1;//因为是pad端操作，所以属于未确认订单

    $ret2 = $order_status->Save($order);
    if (0 != $ret  ||  0 != $ret2) {
        LogErr("delete err");
        return errcode::SYS_ERR;
    }
    //LogDebug($entry);
    $order_info = $mongodb->GetOrderById($order_id);
    //给收银端退款申请通知
    $ret_json = PageUtil::NotifyOrderChange($order_info->shop_id, $order_id, $order_info->order_status, time());
    //LogDebug($ret_json);
    $ret_json_obj = json_decode($ret_json);
    //LogDebug($ret_json_obj);
    if(0 != $ret_json_obj->ret)
    {
        LogErr("Order send pad err");
    }
    $resp = (object)[

    ];
    LogInfo("delete ok");
    return 0;

}
$ret = -1;
$resp = (object)array();
if(isset($_['save']))
{
    $ret = SaveOrderInfo($resp);
}elseif(isset($_['delete_order']))
{
    $ret = DeleteOrder($resp);
}elseif(isset($_['cancel_order']))
{
    $ret = CancelOrder($resp);
}elseif(isset($_['urge_order']))
{
    $ret = UrgeOrder($resp);
}elseif(isset($_['refund_order']))
{
    $ret = RefundOrder($resp);
}else{
    LogErr("param no");
    return errcode::PARAM_ERR;
}
$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>



