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
require_once("mgo_user.php");
require_once("mgo_employee.php");
require_once("mgo_order_status.php");
require_once("mgo_seat.php");
require_once("page_util.php");
require_once("mgo_stat_platform_byday.php");
require_once("mgo_stat_shop_byday.php");

//app点餐下单操作
function SaveOrderInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id           = $_['shop_id'];
    $customer_num      = $_['customer_num'];
    //$is_order          = $_['is_order'];
    $phone             = $_['phone'];
    $seat_name         = $_['seat_name'];
    $food_list         = json_decode($_['food_list']);
    $order_from        = $_['order_from'];
    $order_remark      = $_['order_remark'];
    $plate             = $_['plate'];
    //$pay_way           = $_['pay_way'];
    $order_id          = \DaoRedis\Id::GenOrderId();
    $order_water_num   = date('Ymd', time()) . \DaoRedis\Id::GenOrderWNId();
    $pay_status        = 1;   //未支付状态
    $dine_way          = 1;   //默认在店吃
    $order_status      = NewOrderStatus::NOPAY; //未支付状态
    $is_appraise       = 0;   //未评价
    //$order_sure_status = 2;   //已下单
    $shopinfo = \Cache\Shop::Get($shop_id);
    if (!$shopinfo) {
        LogErr("get shopinfo err, shop_id:[$shop_id]");
        return errcode::SHOP_NOT_EXIST;
    }
    if ($food_list) {
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
            $num       = $v->stock_num_day;
        }
        if (null != $food) {
            $resp = (object)[
                'food_id'     => $food_id,
                'food_name'   => $food_name,
                'surplus_num' => $num,   //提示限量中剩余的库存
            ];
            LogErr("not enough, food_id:[{$food_id}]");
            return errcode::FOOD_NOT_ENOUGH;
        }
        // 取餐品信息
        $food_all = [];
        foreach ($food_list as $i => &$item) {
            $db_food_info = \Cache\Food::Get($item->food_id);
            if (!$db_food_info || !$item->food_id) {
                LogErr("food err:[{$item->food_id}]");
                return errcode::FOOD_ERR;
            }
            $sale_off = PageUtil::GetFoodSaleOff($db_food_info);
            if(1 == $sale_off)
            {
                $resp = (object)[
                        'food_list' => [
                        'food_name'   => $item->name,]
                ];
                LogErr("Food Sale_off:[{$item->food_id}]");
                return errcode::FOOD_SALE_OFF;
            }
            //先判断该餐品是否属于有规格的餐品，没有提示错误
            if($db_food_info->food_price->type != 2 && $item->weight)
            {
                LogErr("food no spc");
                return errcode::FOOD_NO_SPC;
            }
            $food_price = getPrice($item, $price);//计算单种菜品总价格(包含如果有打包就有打包费,数量也在里面）及菜品单价
            if (null === $food_price) {             //如果菜品没有价格就报错
                LogErr("price error");
                return errcode::ORDER_OPR_ERR;
            }
//            //与传的总价对比
//            if($all_price!= round((float)$food_price_all,2)){
//                LogErr("price_all error");
//                return errcode::ORDER_ST_ERR;
//            }
            if ($item->isgive) {
//                if($_['srctype'] == 3)
//                {
//                    Permission::PadUserPermissionCheck(Position::GIVING);
//                }
                $food_price = 0;
            }
            //获取餐品列表信息保存到订单中
            $food_list_all                   = (object)[];
            $food_list_all->food_id          = $db_food_info->food_id;
            $food_list_all->food_name        = $db_food_info->food_name;
            $food_list_all->food_price       = $price;
            $food_list_all->food_category    = $db_food_info->category_id;
            $food_list_all->food_price_sum   = $food_price;
            $food_list_all->food_attach_list = $item->attribute;
            $food_list_all->food_unit        = $db_food_info->food_unit;
            $food_list_all->food_num         = $item->food_num;
            if($item->istake)
            {
                $food_list_all->pack_num         = $item->food_num;
            }
            $food_list_all->is_pack          = $item->istake;
            $food_list_all->is_send          = $item->isgive;
            $food_list_all->send_remark      = $item->giveremark;//赠送理由
            array_push($food_all, $food_list_all);
            $price_all += $food_price;//累计菜品总价
            $count_all += $item->food_num;
        }
        //LogDebug($plate);
        //如果使用的餐牌号就不使用餐桌费用了
        if (!$plate) {
            // 餐位费
            $mgo_seat = new \DaoMongodb\Seat;
            $seatinfo = $mgo_seat->GetSeatID($shop_id, $seat_name);
            if (!$seatinfo->seat_id) {
                LogErr('no seat_id');
                return errcode::SEAT_NOT_EXIST;
            }
            //$seatinfo = \Cache\Seat::Get($seat_id);
            if (SeatPriceType::NO != $seatinfo->price_type) {
                switch ($seatinfo->price_type) {
                    case SeatPriceType::NUM:
                        $seat_price = $seatinfo->price * $customer_num;
                        break;
                    case SeatPriceType::FIXED:
                        $seat_price = $seatinfo->price;
                        break;
                    case SeatPriceType::RATIO:
                        $seat_price = $seatinfo->price / 100 * (float)$price_all;
                        break;
                    default:
                        $seat_price = 0;
                        break;
                }
            } else {
                $seat_price = 0;
            }
        } else {
            $seat_price = 0;
        }
        $all_price = round($price_all,2) + round($seat_price, 2);
    } else {
        $food_list = null;
    }
    $mgo                      = new \DaoMongodb\Order;
    $employee                 = new \DaoMongodb\Employee;
    $employee_info            = $employee->GetEmployeeByPhone($shop_id, $phone);
    $entry                    = new \DaoMongodb\OrderEntry;
    $entry->order_id          = $order_id;
    $entry->order_remark      = $order_remark;
    $entry->shop_id           = $shop_id;
    $entry->order_from        = $order_from;
    $entry->order_water_num   = $order_water_num; //流水号
    $entry->dine_way          = $dine_way;
    //$entry->pay_way           = $pay_way;
    $entry->customer_num      = $customer_num;
    $entry->seat_id           = $seatinfo->seat_id;
    $entry->order_status      = $order_status;
    $entry->food_list         = $food_all;
    $entry->order_time        = time();
    $entry->order_remark      = $order_remark;
    $entry->employee_id       = $employee_info->employee_id;
    $entry->food_num_all      = $count_all;
    $entry->food_price_all    = $price_all;
    $entry->delete            = 0;
    $entry->is_appraise       = $is_appraise;
    $entry->plate             = $plate;
    $entry->pay_status        = $pay_status;
    $entry->seat_price        = $seat_price;
    $entry->order_fee         = $all_price;
    $entry->order_payable     = $all_price;
    $entry->is_confirm        = 1;//因为是app端要直接跳转到历史列表里面，所以属于已确定订单
    //$entry->order_payable     = (float)$price_all + (float)$seat_price - (float)$entry->order_waiver_fee;//应付价格=菜品总价+餐位费-减免费 order_waiver_fee可能由服务员修改（与客人端无关）
    //LogDebug($entry);
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
        'order_id'    => $entry->order_id,
    ];
    // 增加餐品售出数
    PageUtil::UpdateFoodDauSoldNum($entry->order_id);
    //LogDebug($resp);
    LogInfo("save ok");
    return 0;
}
//计算点餐菜品总价格(餐品单价及数量和餐盒费总计)
function getPrice($food,&$price){
    $db_food_info = \Cache\Food::Get($food->food_id);
    if(!$db_food_info)
    {
        LogErr("FoodInfo err");
        return null;
    }
    //如果是打包就算出餐盒费用及餐盒数量的总价
    if($food->istake == 1)
    {
        $accessory_price = 0;
        //是否有餐盒费及是否有打包
        if($db_food_info->accessory && $food->food_num){
            $accessory = \Cache\Food::Get($db_food_info->accessory);
            $accessory_price = $accessory->food_price * (int)$food->food_num*(float)$db_food_info->accessory_num;
        }
    }else{
        $accessory_price = 0;
    }
    //如果是配件直接返回价格 //<<<<<<<<<pad端应该没有配件选择的
    if($db_food_info->type == 2)
    {
        return $db_food_info->food_price;
    }

    //算出餐品的价格,spec_type = 0(无规格的价格),1,2,3大中小
    foreach ($db_food_info->food_price->price as $key => $value) {
        if($food->weight == $value->spec_type){
            //取出规格中的普通价格
            $price = $value->original_price;
        }
    }
    if(99999999 == $price){
        LogErr("SpecPrice err");
        return null;
    }
    //算出餐品及餐盒的总价
    $food_price = (float)$price * $food->food_num + (float)$accessory_price;
    return $food_price;
}
//app端结账操作
function SaveOrderPay(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id       = $_['order_id'];
    $pay_way        = $_['pay_way'];
    $shop_id        = $_['shop_id'];
    $paid_price     = $_['paid_price'];
    $customer_name  = $_['customer_name'];
    $customer_phone = $_['customer_phone'];
    $cause          = $_['cause'];
    //LogDebug($_);
    //如果有顾客信息就保存到顾客表，并标明属于app端用户
    if ($customer_phone)
    {
        $customer       = new \DaoMongodb\Customer;
        $customer_entry = new \DaoMongodb\CustomerEntry;
        $customer_info  = $customer->QueryByPhone($shop_id,$customer_phone);
        if($customer_info->customer_id)
        {
            $customer_id  =  $customer_info->customer_id;
        }else{
            $customer_id                     = DaoRedis\Id::GenCustomerId();
            $customer_entry->shop_id         = $shop_id;
            $customer_entry->customer_id     = $customer_id;
            $customer_entry->phone           = $customer_phone;
            $customer_entry->usernick        = $customer_name;
            $customer_entry->is_pad_customer = 0; //不是pad端用户
            $ret2 = $customer->Save($customer_entry);
            if (0 != $ret2) {
                LogErr("Save err");
                return errcode::SYS_ERR;
            }
        }
    }else{
        $customer_id = null; //用于保存
    }
    $shopinfo = \Cache\Shop::Get($shop_id);
    if (!$shopinfo->shop_id)
    {
        LogErr("get shopinfo err, shop_id:[$shop_id]");
        return errcode::SHOP_NOT_EXIST;
    }

    $mgo        = new \DaoMongodb\Order;
    $order_info = $mgo->GetOrderById($order_id);
    if (!$order_info->order_id) {
        return errcode::ORDER_NOT_EXIST;
    }
    $entry           = new \DaoMongodb\OrderEntry;
    $entry->order_id = $order_id;
    $entry->shop_id  = $shop_id;
    $entry->pay_way  = $pay_way;
    $status_entry               = new \DaoMongodb\OrderStatusEntry;
    $status                     = new \DaoMongodb\OrderStatus;
    if ($pay_way == 5)
    {
        $entry->pay_status   = 1;
        $entry->order_status = 7;
        $paid_price          = 0;
        //保存挂账信息
        $status_entry->id           = \DaoRedis\Id::GenOrderStatusId();
        $status_entry->customer_id  = $customer_id;
        $status_entry->order_id     = $order_id;
        $status_entry->order_status = 7;
        $status_entry->employee_id  = $order_info->employee_id;
        $status_entry->delete       = 0;
        $status_entry->made_time    = time();
        $status_entry->made_cz_reson= $cause;
        $status->Save($status_entry);
    } else {
        $entry->pay_status   = 2;
        $entry->order_status = 2;
        $entry->pay_time     = time();
        //保存结账状态信息
        $status_entry->id           = \DaoRedis\Id::GenOrderStatusId();
        $status_entry->order_status = 2;
        $status_entry->employee_id  = $order_info->employee_id;
        $status_entry->delete       = 0;
        $status_entry->order_id     = $order_id;
        $status_entry->made_time    = time();
        $status->Save($status_entry);
    }
    //保存商户消费额度统计数据
    $platformer     = new \DaoMongodb\StatPlatform;
    $day            = date('Ymd',time());
    $platform_id    = 1;//现在只有一个运营平台id
    $consume_amount = (float)$paid_price;
    $customer_num   = $order_info->customer_num;
    $platformer->SellNumAdd($platform_id, $day, ['consume_amount'=>$consume_amount,'customer_num'=>$customer_num]);
    //保存平台消费者数据统计
    $shop_stat      = new \DaoMongodb\StatShop;
    $shop_stat->SellShopNumAdd($shop_id, $day, ['consume_amount'=>$consume_amount,'customer_num'=>$customer_num], $shopinfo->agent_id);
    //改变并保存订单信息
    $entry->order_status     = 2;
    $entry->customer_id      = $customer_id;
    //$entry->is_confirm       = 1;//因为是pad端操作，所以属于已确认订单
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    //LogDebug($resp);
    LogInfo("save ok");
    return 0;
}
//预结账
function OrderAdvance(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id          = $_['order_id'];
    $shop_id           = $_['shop_id'];
    $shopinfo = \Cache\Shop::Get($shop_id);
    if (!$shopinfo) {
        LogErr("get shopinfo err, shop_id:[$shop_id]");
        return errcode::SHOP_NOT_EXIST;
    }
    $userid        = \Cache\Login::GetUserid();
    $employee      = new \DaoMongodb\Employee;
    $employee_info = $employee->QueryByUserId($shop_id, $userid);
    $employee_id   = $employee_info->employee_id;

    $mgo        = new \DaoMongodb\Order;
    $order_info = $mgo->GetOrderById($order_id);
    if(!$order_info->order_id)
    {
        LogErr("no order_id");
        return errcode::ORDER_NOT_EXIST;
    }
   //判断订单状态是否是未支付,因为只有未支付才使用预结账
   if($order_info->order_status != 1)
   {
       LogDebug($order_info->order_status);
       return errcode::ORDER_OPR_ERR;
   }
    $entry              = new \DaoMongodb\OrderEntry;
    $entry->order_id    = $order_id;

    $entry->shop_id      = $shop_id;
    $entry->is_advance   = 1;
    $entry->employee_id  = $employee_id;//<<<<<<<<<<此处的员工id是用来保存预结账或其他未定的情况,如果有订单状态变化的都是保存到订单状态表的
    $entry->is_confirm   = 1;//因为是pad端操作，所以属于已确认订单
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[

    ];
    //LogDebug($resp);
    LogInfo("save ok");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_['modify_status']))
{
    $ret = ModifyOrderStatus($resp);
}
elseif(isset($_['app_order_save']))
{
    $ret = SaveOrderInfo($resp);
}
elseif(isset($_['app_order_pay']))
{
    $ret = SaveOrderPay($resp);
}
elseif(isset($_['order_advance']))
{
    $ret = OrderAdvance($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret' => $ret,
    'data' => $resp
);
//LogDebug($result);
if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}
?><?php /******************************以下为html代码******************************/?>

