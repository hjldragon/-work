<?php
/*
 * [Rocky 2017-05-12 20:16:27]
 * 订单信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_order.php");
require_once("mgo_spec.php");
require_once("mgo_seat.php");
require_once("mgo_menu.php");
require_once("mgo_stat_food_byday.php");
require_once("redis_id.php");
require_once("const.php");
require_once("cache.php");

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

function SaveOrderInfo(&$resp)
{
    $_ = $GLOBALS["_"];

    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id       = (string)$_['order_id'];
    $customer_id    = (int)$_['customer_id'];
    $shop_id        = (string)$_['shop_id'];
    $dine_way       = (int)$_['dine_way'];
    $pay_way        = (int)$_['pay_way'];
    $customer_num   = (int)$_['customer_num'];
    $seat_id        = (string)$_['seat_id'];
    $order_status   = (int)$_['order_status'];
    $food_num_all   = (int)$_['food_num_all'];
    $food_price_all = (float)$_['food_price_all'];
    $food_list      = $_['food_list'];
    $seat_price     = $_['seat_price'];
    $order_fee      = $_['order_fee'];
    $seat_id        = $_['seat_id'];
    $invoice_type   = $_['invoice_type'];
    $invoice_id     = $_['invoice_id'];
    $lastmodtime    = $_['lastmodtime'];

    //var_dump($food_list);die;
    if(!$shop_id || !$seat_id || 0 == count($food_list))
    {
        LogErr("param err, shop_id:[$shop_id], seat_id:[$seat_id]");
        return errcode::SYS_ERR;
    }

    $shopinfo = \Cache\Shop::Get($shop_id);
    if(!$shopinfo)
    {
        LogErr("get shopinfo err, shop_id:[$shop_id]");
        return errcode::SHOP_NOT_EXIST;
    }
    // 餐位费
    if($shopinfo->seat_id != IsSeatEnable::NO){
        $seatinfo = \Cache\Seat::Get($seat_id);
        $seat_price = $seatinfo->price * $customer_num;
    }

    // 检查店铺是否正常
    if(ShopIsSuspend::IsSuspend($shopinfo->suspend))
    {
        LogErr("shop suspend, shop_id:[$shop_id]");
        return errcode::SHOP_SUSPEND;
    }

    $db_orderinfo = \Cache\Order::Get($order_id);
    if(null != $db_orderinfo)
    {
        if(OrderStatus::PENDING == $db_orderinfo->order_status
            && time() - $db_orderinfo->order_time > Cfg::instance()->order_timeout_sec)
        {
            $db_orderinfo->order_status = OrderStatus::TIMEOUT;
        }

        // 查看当前订单状态
        $ret = OrderStatusCheck($db_orderinfo->order_status);
        if(0 != $ret)
        {
            LogErr("order err, id:[$order_id], ret:[$ret]");
            return $ret;
        }

        if($lastmodtime != $db_orderinfo->lastmodtime)
        {
            LogErr("order change, id:[$order_id], [$lastmodtime] -- [{$db_orderinfo->lastmodtime}]");
            return errcode::ORDER_HAD_CHANGE;
        }
    }

    // 检查餐品库存够不够
    $food = PageUtil::CheckFoodStockNum($shop_id, $food_list);
    if(null != $food)
    {
        $resp = (object)array(
            'food_id' => $food->food_id,
            'food_name' => $food->food_name,
        );
        LogErr("not enough, food_id:[{$food->food_id}], food_num_day:[{$food_num_day}], food_sold_num:[{$food_sold_num}]");
        return errcode::FOOD_NOT_ENOUGH;
    }

    // 取餐品信息
    foreach($food_list as $i => &$item)
    {
        $db_food_info = \Cache\Food::Get($item->food_id);

        if(!$db_food_info)
        {
            LogErr("food err:[{$item->food_id}]");
            return errcode::SYS_ERR;
        }
        $item->food_unit = $db_food_info->food_unit;


        $food_price = getPrice($item,$customer_id,$price);//计算单种菜品价格
        if(null == $food_price){
            LogErr("price error");
            return errcode::ORDER_ST_ERR;
        }
        $item->food_price = $price;
        $item->food_price_sum = $food_price;
        $price_all += $food_price;//累计菜品总价
    }
	
    // if($price_all + $seat_price != $order_fee){
    //     LogErr("price_all error");
    //     return errcode::ORDER_ST_ERR;
    // }
	
    if(IsInvoice::NO != $invoice_type && $invoice_id){
        $db_invoice_info = \Cache\Invoice::Get($invoice_id);
        if(null == $db_invoice_info){
            LogErr("invoice err:[{$invoice_id}]");
            return errcode::SYS_ERR;
        }
        $invoice->type           = $db_invoice_info->type;
        $invoice->title_type     = $db_invoice_info->title_type;
        $invoice->invoice_title  = $db_invoice_info->invoice_title;
        $invoice->duty_paragraph = $db_invoice_info->duty_paragraph;
        $invoice->phone          = $db_invoice_info->phone;
        $invoice->address        = $db_invoice_info->address;
        $invoice->bank_name      = $db_invoice_info->bank_name;
        $invoice->bank_account   = $db_invoice_info->bank_account;
        $invoice->email          = $db_invoice_info->email;
    }
    if(!$order_id)
    {
        $order_id = \DaoRedis\Id::GenOrderId();
    }
	
    if(!$order_status)
    {
        $order_status = OrderStatus::PENDING;
    }

    $mgo = new \DaoMongodb\Order;
    $entry = \Cache\Order::Get($order_id); // 先取出原来信息 // 以后再考滤下事务性[XXX]
    
    if(!$entry)
    {
        $entry = new \DaoMongodb\OrderEntry;
    }

    $entry->order_id       = $order_id;
    $entry->customer_id    = $customer_id;
    $entry->shop_id        = $shop_id;
    $entry->dine_way       = $dine_way;
    $entry->pay_way        = $pay_way;
    $entry->customer_num   = $customer_num;
    $entry->seat_id        = $seat_id;
    $entry->order_status   = $order_status;
    $entry->food_list      = $food_list;
    $entry->order_time     = time();
    $entry->food_num_all   = $food_num_all;
    $entry->food_price_all = $food_price_all;
    $entry->delete         = 0;
    $entry->seat_price     = $seat_price;
    $entry->order_fee      = $price_all;
    $entry->invoice        = $invoice;
    $entry->invoice_type   = $invoice_type;
    $entry->order_payable  = (float)$price_all + (float)$seat_price  - (float)$entry->order_waiver_fee;//应付价格=菜品总价+餐位费-减免费
    //$entry->order_payable  = (float)$order_fee - (float)$entry->order_waiver_fee; // order_waiver_fee可能由服务员修改（与客人端无关）
    $entry->lastmodtime    = time();

    $ret = $mgo->Save($entry);
    
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    // 通知到golang服务器，再转到店管理员页，以提醒服务员处理
    // （注：选在线支付的，在支付完成后，再做相应处理）
//  $url = Cfg::instance()->orderingsrv->webserver_url;
//  //LogDebug("post to:[$url]");
//  $ret = PageUtil::HttpPostJsonData($url, [
//      'OrderId' => (string)$order_id,
//      'Opr' => "OrderInfo1496644465",
//  ]);
//  LogDebug("post, ret:[$ret], url:[$url]");
//
//  $ret = json_decode($ret);
//  if(0 != $ret->Ret)
//  {
//      LogErr("post err: {$ret->Msg}, url:[$url], ret_json:[$ret_json]");
//      return errcode::SYS_ERR;
//  }

    $resp = (object)array(
        'order_id' => $entry->order_id,
        'order_time' => $entry->order_time,
        'order_status' => $entry->order_status,
        'order_fee' => $entry->order_fee,
        'order_waiver_fee' => $entry->order_waiver_fee,
        'order_payable' => $entry->order_payable,
        'lastmodtime' => $entry->lastmodtime,
    );
    LogInfo("save ok");
    return 0;
}



//计算菜品价格
function getPrice($food,$customer_id,&$price){
    $db_food_info = \Cache\Food::Get($food['food_id']);
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
	
    //是否使用规格价格
    if(FoodPriceType::SPEC == $db_food_info->food_price->type){
        $mgo = new \DaoMongodb\Spec;
        
        foreach ($food['spec'] as $key => $value) {
		
           if(IsPrice::YES == $value['type']){
            	
                $spec = $mgo->GetSpecPriceById($value['id']);
           }
        }
        
        
        if(null == $spec){
            LogErr("SpecPrice err");
            return null;
        }
        //取最小价格
        $price = 99999999;
        foreach ($name as  $item) {
            if($spec->$item < $price && $spec->$item > 0){
                $price = $spec->$item;
            }
        }
    }else{
        $price = 99999999;
        foreach ($name as  $item) {
            if($db_food_info->food_price->$item < $price && $db_food_info->food_price->$item > 0){
                $price = $db_food_info->food_price->$item;
            }
        }
    }
    
    if(99999999 == $price){
        LogErr("SpecPrice err");
        return null;
    }
    
    $accessory_price = 0;
    //是否有餐盒费及是否有打包
    if($db_food_info->accessory && $food['pack_num']){
        $accessory = \Cache\Food::Get($db_food_info->accessory);
        $accessory_price = $accessory->food_price->original_price * (float)$food['pack_num'];
    }
    $food_price = $price * (float)$food['food_num'] + $accessory_price;
   
    return $food_price;
}

$ret = -1; 
$resp = (object)array();
if(isset($_['save']))
{  
    
    $ret = SaveOrderInfo($resp);
}



$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>




          