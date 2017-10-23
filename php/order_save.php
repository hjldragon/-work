<?php
/*
 * [Rocky 2017-05-12 20:16:27]
 * 订单信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_order.php");
require_once("mgo_menu.php");
require_once("mgo_stat_food_byday.php");
require_once("redis_id.php");
require_once("const.php");

Permission::EmployeePermissionCheck(
     Permission::CHK_ORDER_W
);


// 计算订单费用(填充各字段)
function CalOrderFee($orderinfo)
{
    // 各个菜累加
    $food_num_all = 0;
    $food_price_all = (float)0;
    foreach($orderinfo->food_list as $i => $item)
    {
        $food_num_all += (int)$item->food_num;
        $food_price_all += (float)$item->food_price_sum;
    }

    // 座位费用
    $seat_fee = (float)$orderinfo->seat_price * (int)$orderinfo->customer_num;

    // 计算订单费用
    $order_fee = $food_price_all + $seat_fee;

    $orderinfo->food_num_all   = $food_num_all;
    $orderinfo->food_price_all = $food_price_all;
    $orderinfo->order_fee      = $order_fee;
    $orderinfo->order_payable  = (float)$order_fee - (float)$orderinfo->order_waiver_fee; // 应付费用 = 订单费用 - 减免费用
}

function SaveOrderInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id         = (string)$_['order_id'];
    $customer_id      = (int)$_['customer_id'];
    $dine_way         = (int)$_['dine_way'];
    $pay_way          = (int)$_['pay_way'];
    $customer_num     = (int)$_['customer_num'];
    $food_num_all     = (int)$_['food_num_all'];
    $food_price_all   = (float)$_['food_price_all'];
    $order_waiver_fee = (float)$_['order_waiver_fee'];
    $order_payable    = (float)$_['order_payable'];
    $order_status     = $_['order_status'];
    $order_remark     = $_['order_remark'];

    if(!$order_id)
    {
        LogErr("param err, order_id:[$order_id]");
        return errcode::PARAM_ERR;
    }

    // 查看当前订单状态
    $ret = PageUtil::OrderCanModify($order_id);
    if(0 != $ret)
    {
        LogErr("order err, id:[$order_id], ret:[$ret]");
        return $ret;
    }

    $mgo = new \DaoMongodb\Order;
    $entry = \Cache\Order::Get($order_id); // 先取出原来信息 // 以后再考滤下事务性[XXX]
    if(!$entry)
    {
        $entry = new \DaoMongodb\OrderEntry;
    }

    $entry->order_id         = $order_id;
    $entry->dine_way         = $dine_way;
    $entry->pay_way          = $pay_way;
    $entry->customer_num     = $customer_num;
    $entry->order_waiver_fee = $order_waiver_fee;
    $entry->order_remark     = $order_remark;

    CalOrderFee($entry);
    LogDebug($entry);

    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    // 更新缓存
    \Cache\Order::Clear($order_info->order_id);

    $resp = (object)array(
        'order_id' => $entry->order_id,
        'order_time' => $entry->order_time,
        'order_status' => $entry->order_status,
    );
    LogInfo("save ok");
    return 0;
}

function ModifyOrderStatus(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id     = $_['order_id'];
    $order_status = $_['order_status'];

    // // 查看当前订单状态
    // $ret = PageUtil::OrderCanModify($order_id);
    // if(0 != $ret)
    // {
    //     LogErr("order err, id:[$order_id], ret:[$ret]");
    //     return $ret;
    // }

    $orderinfo = \Cache\Order::Get($order_id);

    // // 已确认的订单
    // if(OrderStatus::HadConfirmed($orderinfo->order_status))
    // {
    //     if(OrderStatus::CONFIRMED == $order_status
    //         || OrderStatus::CANCEL == $order_status)
    //     {
    //         LogErr("order opr err, order_id:[$order_id]");
    //         return errcode::ORDER_OPR_ERR;
    //     }
    // }

    // 检查餐品库存够不够
    if(OrderStatus::HadConfirmed($order_status))
    {
        $food = PageUtil::CheckFoodStockNum($orderinfo->shop_id, $orderinfo->food_list);
        if(null != $food)
        {
            $resp = (object)array(
                'food_id' => $food->food_id,
                'food_name' => $food->food_name,
            );
            LogErr("not enough, food_id:[{$food->food_id}], food_num_day:[{$food_num_day}], food_sold_num:[{$food_sold_num}]");
            return errcode::FOOD_NOT_ENOUGH;
        }
    }

    $shop_id = \Cache\Login::GetShopId();
    if(!$shop_id || $shop_id != $orderinfo->shop_id)
    {
        LogErr("shop err, shop_id[$shop_id]");
        return errcode::SYS_ERR;
    }

    $mgo = new \DaoMongodb\Order;
    $entry = new \DaoMongodb\OrderEntry;

    $entry->order_id     = $order_id;
    $entry->order_status = $order_status;

    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    // 更新缓存
    \Cache\Order::Clear($order_id);

    // 通知到golang服务器，再转到店服务器（以便打印处理等）
    $url = Cfg::instance()->orderingsrv->webserver_url;
    //LogDebug("post to:[$url]");
    $ret_json = PageUtil::HttpPostJsonData($url, [
        'OrderId' => (string)$order_id,
        'Opr' => "OrderInfo1496644465",
    ]);

    $ret = json_decode($ret_json);
    if(0 != $ret->Ret)
    {
        LogErr("post err: {$ret->Msg}, url:[$url], ret_json:[$ret_json]");
        return errcode::SYS_ERR;
    }

    // 增加餐品售出数
    PageUtil::UpdateFoodDauSoldNum($order_id);

    $resp = (object)array(
        'order_status' => $order_status
    );
    LogInfo("modift ok");
    return 0;
}

function SaveOrderFoodinfoInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id = $_['order_id'];
    $food_info = json_decode($_['food_info']);

    // 查看当前订单状态
    $ret = PageUtil::OrderCanModify($order_id);
    if(0 != $ret)
    {
        LogErr("order err, id:[$order_id], ret:[$ret]");
        return $ret;
    }

    /*
     * 查看当前订单中是否已有些餐品，有就修改，否则插入新的
     */
    $orderinfo = \Cache\Order::Get($order_id);
    if(!$orderinfo)
    {
        LogErr("no order:[{$order_id}]");
        return errcode::ORDER_NOT_EXIST;
    }
    $order_foodinfo = null;
    foreach($orderinfo->food_list as $i => $item)
    {
        if($item->id == $food_info->id)
        {
            $order_foodinfo = $item;
            break;
        }
    }
    LogDebug($order_foodinfo);
    if(null == $order_foodinfo)
    {
        $order_foodinfo = new \DaoMongodb\OrderFoodInfo;
        $info = \Cache\Food::Get($food_info->food_id);
        if(!$info)
        {
            LogErr("no food:[{$food_info->food_id}]");
            return errcode::FOOD_NOT_EXIST;
        }

        $food_price = Util::FenToYuan($info->food_price);
        if(\Cache\Customer::IsVip($orderinfo->customer_id) && $info->food_vip_price > 0)
        {
            $food_price = Util::FenToYuan($info->food_vip_price);
        }

        // 注：其它信息下面填
        $order_foodinfo->id            = \DaoRedis\Id::GenOrderFoodId();
        $order_foodinfo->food_id       = $info->food_id;
        $order_foodinfo->food_name     = $info->food_name;
        $order_foodinfo->food_category = $info->food_category;
        $order_foodinfo->food_unit     = $info->food_unit;
        $order_foodinfo->food_price    = $food_price;

        array_push($orderinfo->food_list, $order_foodinfo);
        LogDebug("add food:" . json_encode($order_foodinfo));
    }

    $food_num = (int)$food_info->food_num;
    if(0 == $food_num)
    {
        LogErr("param err, food_num:[{$food_num}]");
        return errcode::PARAM_ERR;
    }

    $unit_num = (float)$food_info->unit_num;
    if(0 == $unit_num)
    {
        $unit_num = 1;
    }

    $food_price_sum = $order_foodinfo->food_price * $food_num * $unit_num;

    $order_foodinfo->food_price_sum   = $food_price_sum;
    $order_foodinfo->food_num         = $food_num;
    $order_foodinfo->unit_num         = $unit_num;
    $order_foodinfo->food_attach_list = $food_info->food_attach_list;

    CalOrderFee($orderinfo);

    $mgo = new \DaoMongodb\Order;
    $ret = $mgo->Save($orderinfo);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
        'orderinfo' => $orderinfo,
        'order_foodinfo' => $order_foodinfo,
    );
    LogDebug($resp);
    LogInfo("ok");
    return 0;
}

function DeleteOrderFood(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id = $_['order_id'];
    $order_food_id = $_['order_food_id'];

    $orderinfo = \Cache\Order::Get($order_id);
    if(!$orderinfo)
    {
        LogErr("no food:[{$order_id}]");
        return errcode::ORDER_NOT_EXIST;
    }

    foreach($orderinfo->food_list as $i => &$item)
    {
        if($item->id == $order_food_id)
        {
            unset($orderinfo->food_list[$i]);
            LogDebug($i);
            break;
        }
    }

    $orderinfo->order_time = time();
    CalOrderFee($orderinfo);

    $mgo = new \DaoMongodb\Order;
    $ret = $mgo->Save($orderinfo);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
        'orderinfo' => $orderinfo
    );
    LogDebug($resp);
    LogInfo("ok");
    return 0;
}

// 手动打印
function OrderPrintManual(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id = $_['order_id'];
    $printer_id = $_['printer_id'];


    // 通知到golang服务器，再转到店服务器（以便打印处理等）
    $url = Cfg::instance()->orderingsrv->webserver_url;
    //LogDebug("post to:[$url]");
    $ret_json = PageUtil::HttpPostJsonData($url, [
        'OrderId' => (string)$order_id,
        'PrinterId' => (string)$printer_id,
        'Opr' => "WebOrderPrintManual1500264796",
    ]);

    $ret = json_decode($ret_json);
    if(0 != $ret->Ret)
    {
        LogErr("post err: {$ret->Msg}, url:[$url], ret_json:[$ret_json]");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("ok");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_['modify_status']))
{
    $ret = ModifyOrderStatus($resp);
}
else if(isset($_['order_save']))
{
    $ret = SaveOrderInfo($resp);
}
else if(isset($_['order_foodinfo_save']))
{
    $ret = SaveOrderFoodinfoInfo($resp);
}
else if(isset($_['order_food_delete']))
{
    $ret = DeleteOrderFood($resp);
}
else if(isset($_['order_print_manual']))
{
    $ret = OrderPrintManual($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}


$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
