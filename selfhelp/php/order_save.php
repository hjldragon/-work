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
require_once("page_util.php");
require_once("/www/public.sailing.com/php/mgo_selfhelp.php");
use \Pub\Mongodb as Mgo;

//点餐下单操作
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
    $dine_way          = $_['dine_way'];
    $selfhelp_id       = $_['selfhelp_id'];
    $food_list         = json_decode($_['food_list']);
    $order_remark      = $_['order_remark'];
    $order_id          = \DaoRedis\Id::GenOrderId();
    $userid            = \Cache\Login::GetUserid();
    $order_water_num   = date('Ymd', time()) . \DaoRedis\Id::GenOrderWNId();
    $pay_status        = PayStatus::NOPAY;   //未支付
    $pay_way           = PayWay::UNKNOWN;   //未确定
    $order_status      = NewOrderStatus::NOPAY; //未支付状态
    $is_appraise       = APPRAISE::NO;   //未评价
    $customer_phone    = $_['customer_phone'];
    //LogDebug($food_list);
    $selfhelp          = new Mgo\Selfhelp;
    $selfhelp_info     = $selfhelp->GetExampleById($selfhelp_id);
    if($selfhelp_info->is_print == ISPRINT::NO)
    {

        $req_p             = PageUtil::GetPhone($customer_phone);
        if(!$req_p)
        {
            LogErr('telephone is not verify');
            return errcode::PHONE_ERR;
        }
    }
    //LogDebug($_);
    $shopinfo = \Cache\Shop::Get($shop_id);
    if (!$shopinfo) {
        LogErr("get shopinfo err, shop_id:[$shop_id]");
        return errcode::SHOP_NOT_EXIST;
    }
    //LogDebug($food_list);
    if ($food_list) {
        $need_food_list = [];
        //因为一个订单中相同的菜品可能会存在多个(打包,赠送情况)
          foreach ($food_list as $v)
          {
              $need_food_list[$v->food_id]->food_num += $v->food_num;
              $need_food_list[$v->food_id]->food_id   = $v->food_id;
          }
        // 检查餐品库存够不够
        //LogDebug($shop_id);
        //LogDebug($need_food_list);
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
            //LogDebug($item);
            //判断菜品是否已下架
            $db_food_info = \Cache\Food::Get($item->food_id);
            if (!$db_food_info || !$item->food_id) {
                LogErr("food err:[{$item->food_id}]");
                return errcode::FOOD_ERR;
            }
            $sale_off = PageUtil::GetFoodSaleOff($db_food_info);
            if(1 == $sale_off)
            {
                $resp = (object)[
                     'food_id'     => $item->food_id,
                     'food_name'   => $item->food_name
                ];
                LogErr("Food Sale_off:[{$item->food_id}]");
                return errcode::FOOD_SALE_OFF;
            }
            //如果是属于打包就属于外卖
            if($dine_way == SALEWAY::TAKEOUT)
            {
                $item->istake = 1;
            }else{
                $item->istake = 0;
            }
            $item->isgive = 0;

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
            //LogDebug($price);
            //LogDebug($food_price);
            if ($item->isgive) {
                $food_price = 0;
            }
            //LogDebug($item);
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
            $food_list_all->made_status      = MadeStatus::WAIT;
            array_push($food_all, $food_list_all);
            $price_all += $food_price;//累计菜品总价
            $count_all += $item->food_num;
        }

        $all_price = round($price_all,2);
        //LogDebug($all_price);
    } else {
        $food_all = null;
    }
    $mgo                      = new \DaoMongodb\Order;
    $employee                 = new \DaoMongodb\Employee;
    $entry                    = new \DaoMongodb\OrderEntry;
    $employee_info            = $employee->GetEmployeeByPhone($shop_id, $userid);

    $entry->order_id          = $order_id;
    $entry->order_remark      = $order_remark;
    $entry->shop_id           = $shop_id;
    $entry->order_water_num   = $order_water_num; //流水号
    $entry->dine_way          = $dine_way;
    $entry->pay_way           = $pay_way;
    $entry->customer_num      = $customer_num;
    $entry->order_status      = $order_status;
    $entry->food_list         = $food_all;
    $entry->order_time        = time();
    $entry->order_remark      = $order_remark;
    $entry->employee_id       = $employee_info->employee_id;
    $entry->food_num_all      = $count_all;
    $entry->food_price_all    = $price_all;
    $entry->delete            = 0;
    $entry->is_appraise       = $is_appraise;
    $entry->order_from        = OrderFrom::APP;//<<<<<<这里是用在自主点餐机端的方法所以来源是扫码
    $entry->pay_status        = $pay_status;
    $entry->order_fee         = $all_price;
    $entry->order_payable     = $all_price;
    $entry->is_confirm        = IsCoonfirm::Yes;//因为可能直接走订单模块所以属于已确认
    $entry->selfhelp_id       = $selfhelp_id;
    $entry->is_ganged         = $selfhelp_info->using_type;
    $entry->customer_phone    = $customer_phone;
    $entry->kitchen_status    = KitchenStatus::WAITMAKE;//等待制作


    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($all_price);
    if ($all_price <= 0)
    {
        LogErr('money can not zero');
        return errcode::PLAY_NOT_ZERO;
    }
    $resp = (object)[
        'order_id'    => $entry->order_id,
        'all_price'   => $all_price
    ];
    // 增加餐品售出数
//    PageUtil::UpdateFoodDauSoldNum($entry->order_id);
//    LogDebug($resp);
//    LogInfo("food sold num save ok");
    //收银端推送给收银端
    $ret_json = PageUtil::NotifyOrderChange($shop_id, $order_id, $order_status, time());
    LogDebug($ret_json);
    $ret_json_obj = json_decode($ret_json);
    LogDebug($ret_json_obj);
    if(0 != $ret_json_obj->ret)
    {
        LogErr("Order send selfhelp err");
    }
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
   LogDebug($db_food_info);
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
//    //如果是配件直接返回价格 //<<<<<<<<<pad端应该没有配件选择的
    if($db_food_info->type == 2)
    {
        $price = $db_food_info->food_price;
        return $db_food_info->food_price*$food->food_num;
    }
    //不是配件的返回价格
    $using = $db_food_info->food_price->using;
    if (($using & PriceType::FESTIVAL) != 0){// 节日价
        $name[] = 'festival_price';
    }
    if (($using & PriceType::DISCOUNT) != 0) {// 折扣价
        $name[] = 'discount_price';
    }
    if (($using & PriceType::ORIGINAL) != 0) {// 普通价

        $name[] = 'original_price';
    }
    LogDebug($name);
    //算出餐品的价格,spec_type = 0(无规格的价格),1,2,3大中小
    foreach ($db_food_info->food_price->price as $key => $value) {
        LogDebug($value);
        LogDebug($food->weight);
        if($food->weight == $value->spec_type){
            //取最小价格
            $price = 99999999;
            foreach ($name as  $item) {
                LogDebug($item);
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
    //算出餐品及餐盒的总价
    $food_price = (float)$price * $food->food_num + (float)$accessory_price;
    return $food_price;
}
// PAD端检查餐品库存够不够,不够返回所有的不足的餐品
function CheckFoodStockNum($shop_id, $need_food_list)
{
    $food_id_list = [];
    foreach ($need_food_list as $id)
    {
        $food_id_list[] = $id->food_id;
    }
    // 读出当前餐品每天备货量

    $mgo_food = new \DaoMongodb\MenuInfo;
    $list = $mgo_food->GetOrderFoodList(
        $shop_id,
        [
            'food_id_list' => $food_id_list,
        ]
    );
    $id2stock_num_day = [];
    foreach($list as $i => $v)
    {
        if($v->stock_num_day == 0)
        {
            $v->stock_num_day = 99999;
        }
        $id2stock_num_day[$v->food_id] = (int)$v->stock_num_day;
    }
    // 读出当前已售出量
    $mgo_stat = new \DaoMongodb\StatFood;
    $today = date("Ymd");
    $list_two = $mgo_stat->GetStatList([
        'food_id_list' => $food_id_list,
        'shop_id'      => $shop_id,
        'begin_day'    => $today,
        'end_day'      => $today,
    ]);
    // food_id --> 已售出量
    $id2food_sold_num = [];
    foreach($list_two as $i => $v)
    {
        $id2food_sold_num[$v->food_id] = $v->sold_num;
    }
    // 查看餐品存量
    $all_food = [];
    $food_one = [];
    foreach($need_food_list as $food)
    {

        //每日限售量
        $stock_num_day = (int)$id2stock_num_day[$food->food_id];
        //当日出售量
        $food_sold_num = (int)$id2food_sold_num[$food->food_id];
        // 库存够吗？
        if($food->food_num > $stock_num_day - $food_sold_num)
        {

            $food_one['food_name'] = $food->food_name;
            $food_one['food_id']   = $food->food_id;
            $food_one['stock_num_day']   = $stock_num_day - $food_sold_num;
            array_push($all_food,$food_one);
        }
    }
    return $all_food;
}
// PAD端检查餐品是否下架,如果下架返回所有下架的餐品
function CheckFoodStockSale($shop_id, $need_food_list)
{
    $food_id_list = [];
    foreach ($need_food_list as $id)
    {
        $food_id_list[] = $id->food_id;
    }
    // 读出当前餐品每天备货量

    $mgo_food = new \DaoMongodb\MenuInfo;
    $list = $mgo_food->GetOrderFoodList(
        $shop_id,
        [
            'food_id_list' => $food_id_list,
        ]
    );

    $all_food = [];
    $food_one = [];
    foreach($list as $i => $v)
    {
        if($v->sale_off == 1)
        {
            $food_one['food_name'] = $v->food_name;
            $food_one['food_id']   = $v->food_id;
            $food_one['sale_off']   = $v->sale_off;
            array_push($all_food,$food_one);
        }
    }

    return $all_food;
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

else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
    'data' => $resp
);
LogDebug($result);
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

