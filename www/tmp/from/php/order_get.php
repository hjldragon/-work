<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_order.php");
require_once("mgo_seat.php");
require_once("mgo_customer.php");
require_once("mgo_employee.php");
require_once("mgo_menu.php");
require_once("mgo_order_status.php");
require_once("/www/public.sailing.com/php/mgo_selfhelp.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;

function GetOrderInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id = $_['order_id'];
    $shop_id  = $_['shop_id'];
    if(!$shop_id)
    {
        $shop_id = \Cache\Login::GetShopId();
    }
    $mgo  = new \DaoMongodb\Order;
    $info = $mgo->GetOrderById($order_id);

    if(!$info->order_id)
    {
        LogErr("order_info is null");
        return errcode::ORDER_NOT_EXIST;
    }
    $order_status  = new \DaoMongodb\OrderStatus;
    $status_info   = $order_status->GetOrderById($order_id);
    $customer      = new \DaoMongodb\Customer;
    $customer_info = $customer->QueryById($info->customer_id);
    $customer_name = $customer_info->usernick;
    $seat          = new \DaoMongodb\Seat;
    $seat_info     = $seat->GetSeatById($info->seat_id);
    $employee      = new \DaoMongodb\Employee;
    $employee_info = $employee->GetEmployeeInfo($shop_id, $info->employee_id);
    $employee_name = $employee_info->real_name;
    $status_infos = [];

    foreach ($status_info as &$s)
    {
        $employee_info2          = $employee->GetEmployeeInfo($shop_id, $s->employee_id);
        $employee_name2          = $employee_info2->real_name;
        $customer_info2          = $customer->QueryById($s->customer_id);
        $customer_phone          = $customer_info2->phone;
        $customer_name2          = $customer_info2->usernick;
        $infos['order_status']   = $s->order_status;
        $infos['customer_phone'] = $customer_phone;
        $infos['employee_name']  = $employee_name2;
        $infos['customer_name']  = $customer_name2;
        $infos['made_time']      = $s->made_time;
        $infos['made_reson']     = $s->made_reson;
        $infos['apply_time']     = $s->apply_time;
        $infos['made_cz_reson']  = $s->made_cz_reson;
        $infos['order_money']    = $info->order_fee;
        array_push($status_infos,$infos);
     }
    if($info->seat_id){
        $info->seat->seat_id              = $seat_info->seat_id;
        $info->seat->seat_name            = $seat_info->seat_name;
        $info->seat->seat_type            = $seat_info->seat_type;
        $info->seat->seat_region          = $seat_info->seat_region;
        $info->seat->price                = $info->seat_price;
        $info->seat->num                  = 1;  //餐位费数量1
    }else{
        $info->seat->seat_name            = $info->plate;
    }
    $all_num     = 0;
    $info_pack   = (object)[];
    foreach ($info->food_list as $k=>&$food)
    {
        if($food->food_num == 0)//<<<<<<<<<<<<<<<<<<<<<<<用写菜品是否是打包的餐盒数据,显示在列表中
        {
            unset($info->food_list[$k]);
        }
        $all_num += $food->food_num;
        $food->food_img = \Cache\Food::Get($food->food_id)->food_img_list[0];
    }
    $total_count = $all_num + $info->seat->num; //餐品总数
    if(!$employee_name)
    {
        $employee_name = $employee_name2;
    }
    $info->employee_name                  = $employee_name;
    if($info->order_from == 1)
    {
        $info->customer_name = $employee_name;
    }else{
        $info->customer_name = $customer_name;
    }
    $info->status_info     = $status_infos;
    $info->total_count     = $total_count;
    if(!$info->paid_price)
    {
        $info->paid_price = $info->order_payable;
    }
    $resp = (object)array(
        'order_info' => $info,
    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
function GetOrderList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id   = $_['order_id'];
    $seat_id    = $_['seat_id'];
    $begin_time = $_["begin_time"];
    $end_time   = $_["end_time"];

    if(!$begin_time)
    {
        $begin_time = date("Y-m-d");
    }
    if(!$end_time)
    {
        $end_time = $begin_time;
    }
    LogDebug("begin_time:$begin_time, end_time:$end_time");
    $begin_time_sec = strtotime($begin_time);
    $end_time_sec = strtotime($end_time) + 3600*24 - 1; // 一天的最后一秒

    $shop_id = \Cache\Login::GetShopId();
    LogDebug("shop_id:[$shop_id]");

    $mgo = new \DaoMongodb\Order;
    $order_list = $mgo->GetOrderList(
        [
            'shop_id'    => $shop_id,
            'order_id'   => $order_id,
            'seat_id'    => $seat_id,
            'begin_time' => $begin_time_sec,
            'end_time'   => $end_time_sec
        ],
        ["food_list"=>0],
        ["_id"=>-1]
    );

    //取餐桌信息
    foreach($order_list as $key => &$value)
    {
        $value->seat = \Cache\Seat::Get($value->seat_id);
        if(!$value->seat)
        {
            $value->seat = [];
        }
        else
        {
            $value->seat->seat_price = Util::FenToYuan($value->seat->seat_price);
        }

    }
    $resp = (object)array(
        'list' => $order_list
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
function GetOrderAllList(&$resp)
{

    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $order_id         = $_['order_id'];
    $is_invoicing     = $_['is_invoicing'];
    $dine_way         = $_['dine_way'];
    $pay_way          = $_['pay_way'];
    $order_status     = $_['order_status'];
    $seat_name        = $_['seat_name'];
    $order_begin_time = $_["order_begin_time"];
    $order_end_time   = $_["order_end_time"];//订单时间
    $begin_time       = $_["begin_time"];  //各种状态开始时间
    $end_time         = $_["end_time"];    //各种状态结束时间
    $page_size        = $_['page_size'];
    $page_no          = $_['page_no'];
    $shop_id          = $_['shop_id'];
    $order_status_list= json_decode($_['order_status_list']);//筛选
    $kitchen_status   = $_['kitchen_status'];
    $type_id  = $_['type_id'];
    LogDebug($_);
    LogDebug($type_id);
    if($_['srctype'] == 3)
    {
        ShopPermissionCheck::PageCheck(ShopPermissionCode::SY_ORDER_LIST,$type_id);
        if($order_status == NewOrderStatus::NOPAY)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::SY_ORDER_NO_PAY,$type_id);
        }elseif ($order_status == NewOrderStatus::PAY)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::SY_ORDER_PAY,$type_id);
        }elseif ($order_status == NewOrderStatus::CLOSER)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::SY_ORDER_CLOSE,$type_id);
        }elseif ($order_status == NewOrderStatus::GUAZ)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::SY_ORDER_GUAZ,$type_id);
        }elseif ($order_status == NewOrderStatus::KNOT)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::SY_ORDER_FJ,$type_id);
        }elseif ($order_status == NewOrderStatus::REFUND)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::SY_ORDER_ROUND_Y,$type_id);
        }elseif ($order_status == NewOrderStatus::REFUNDFAIL)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::SY_ORDER_ROUND_F,$type_id);
        }
    }else{
        ShopPermissionCheck::PageCheck(ShopPermissionCode::ORDER_LIST_ALL,$type_id);
        if($order_status == NewOrderStatus::NOPAY)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::NO_PAY_ORDER,$type_id);
        }elseif ($order_status == NewOrderStatus::PAY)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::PAY_ORDER,$type_id);
        }elseif ($order_status == NewOrderStatus::CLOSER)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::CLOSE_ORDER,$type_id);
        }elseif ($order_status == NewOrderStatus::GUAZ)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::GUAZ_ORDER,$type_id);
        }elseif ($order_status == NewOrderStatus::KNOT)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::KNOT_ORDER,$type_id);
        }elseif ($order_status == NewOrderStatus::REFUND)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::ROUND_S_ORDER,$type_id);
        }elseif ($order_status == NewOrderStatus::REFUNDFAIL)
        {
            ShopPermissionCheck::PageCheck(ShopPermissionCode::ROUND_F_ORDER,$type_id);
        }
    }
    if (!$order_begin_time && $order_end_time)
    {
        $order_begin_time = -28800; //默认后面很长时间
    }
    if (!$order_end_time && $order_begin_time)
    {
        $order_end_time = 1922354460; //默认当前时间
    }
    if (!$begin_time && $end_time)
    {
        $begin_time = -28800; //默认时间戳开始时间
    }
    if (!$end_time && $begin_time)
    {
        $end_time = 1922354460; //默认后面很长时 间
    }
    $sort_name = $_['sort_name'];
    $desc      = $_['desc'];
//    LogDebug($_);
    switch ($sort_name) {
        case 'order_id':
            $sort['order_id'] = (int)$desc;
            break;
        case 'order_time'://反结账
            $sort['order_time'] = (int)$desc;
            break;
        case 'order_fee'://反结账
            $sort['order_fee'] = (int)$desc;
            break;
        case 'order_payable'://反结账
            $sort['order_payable'] = (int)$desc;
            break;
        case 'paid_price'://反结账
            $sort['paid_price'] = (int)$desc;
            break;
        case 'refund_time'://反结账
            $sort['refunds_time'] = (int)$desc;
            break;
        case 'refunds_fail_time'://反结账
            $sort['refunds_fail_time'] = (int)$desc;
            break;
        case 'close_time'://反结账
            $sort['close_time'] = (int)$desc;
            break;
        case 'checkout_time'://反结账
            $sort['checkout_time'] = (int)$desc;
            break;
        case 'pay_time'://反结账
            $sort['pay_time'] = (int)$desc;
            break;
        default:
            $sort = [];
            break;
    }
    $srctype = $_['srctype'] ;

    if(!$shop_id)
    {
        $shop_id    = \Cache\Login::GetShopId();
    }
    //$total      = 0; //总单合计
    if($seat_name){
        $seat       = new \DaoMongodb\Seat;
        $seat_info  = $seat->GetSeatByName($shop_id,$seat_name);
        if(count($seat_info)>0){
            foreach ($seat_info as $s){
                $seat_id    = $s->seat_id;
                $seat_id_list [] =$seat_id;
            }
        }
        $plate  = $seat_name;
    }
    $mgo        = new \DaoMongodb\Order;
    $order_list = $mgo->GetOrderAllList(
        [
            'shop_id'          => $shop_id,
            'order_id'         => $order_id,
            'seat_id_list'     => $seat_id_list,
            'plate'            => $plate,
            'is_invoicing'     => $is_invoicing,
            'dine_way'         => $dine_way,
            'pay_way'          => $pay_way,
            'order_status'     => $order_status,
            'order_begin_time' => $order_begin_time,
            'order_end_time'   => $order_end_time,
            'begin_time'       => $begin_time,
            'end_time'         => $end_time,
            'order_status_list'=> $order_status_list,
            'kitchen_status'   => $kitchen_status,
        ],
        $sort
//        $page_size,
//        $page_no,
        //$total,
        //$pirce_list
    );
    LogDebug($order_list);
//    if ($pirce_list == null) {
//        $pirce_list = [];
//    }
    $all_order = [];

    foreach ($order_list as &$v)
    {
        //自助点餐机的数据来源要根据启用方式来筛选
        if($v->selfhelp_id && $srctype == SrcType::SHOUYINJI){
                if($v->is_ganged != SelfUsingType::GANGED || !in_array($v->order_status,[NewOrderStatus::REFUND,NewOrderStatus::PAY]))
                {
                    continue;
                }
        }
        //自助点餐机的与未支付的不在pc端显示
        if($v->selfhelp_id && $v->order_from == OrderFrom::SELF){
            if($v->order_status == NewOrderStatus::NOPAY)
            {
                continue;
            }
        }
      if($v->order_status == NewOrderStatus::NOPAY && $v->pay_way == PayWay::WEIXIN)
        {
            continue; //所有属于微信支付且未支付成功的都不显示在PAD里
        }
        $customer                      = new \DaoMongodb\Customer;
        $customer_info                 = $customer->QueryById($v->customer_id);
        $v->customer_name              = $customer_info->usernick;
        $v->customer_phone             = $customer_info->phone;
        $employee                      = new \DaoMongodb\Employee;
        $employee_info                 = $employee->GetEmployeeInfo($shop_id, $v->employee_id);
        $v->employee_name              = $employee_info->real_name;
        $employee_info2                = $employee->GetEmployeeInfo($shop_id, $v->status_info->employee_id);
        $v->status_info->employee_name = $employee_info2->real_name;
        $seat                          = new \DaoMongodb\Seat;
        if($v->seat_id)
        {
            $seat_info     = $seat->GetSeatById($v->seat_id);
            $v->seat_name  = $seat_info->seat_name;
        }elseif(!$v->plate && !$v->seat_id){
          $v->seat_name = '--';
        } else {
            $v->seat_name   = $v->plate;
            $v->seat_region = '--';
            $v->seat_type   = '--';
        }
        if($v->order_status == \NewOrderStatus::NOPAY)
        {
            $v->order_payable = $v->order_fee;
        }
        array_push($all_order, $v);
    }

    $all_order_fee    = 0;
    $all_customer_num = 0;
    $paid_price       = 0;
    $order_payable    = 0;
    foreach ($all_order as &$money)
    {
        //订单金额总价
        $all_order_fee+= $money->order_fee;
        //订单总客人数
        $all_customer_num+= $money->customer_num;
        //实收总价
        $paid_price+= $money->paid_price;
        //应付金额
        if($order_status == OrderStatus::FINISH)
        {
            $order_payable+= $money->paid_price;
        }else{
            $order_payable+= $money->order_payable;
        }
    }
    //LogDebug($order_list['customer_name']);
//    //订单金额总价
//    $all_order_fee       = $pirce_list['all_order_fee'];
//    //订单总客人数
//    $all_customer_num    = $pirce_list['all_customer_num'];
//    //实收总价
//    $paid_price          = $pirce_list['all_paid_price'];
//    //应付金额
//    if($order_status == OrderStatus::FINISH)
//    {
//        $order_payable       = $pirce_list['all_paid_price'];
//    }else{
//        $order_payable       = $pirce_list['all_order_payable'];
//    }
    $total = count($all_order);
    $list  = array_slice($all_order, ($page_no-1)*$page_size, $page_size);//<<<<<<<因为有内容筛选所以这里分页
    //订单平均价
    $order_average_price = $all_order_fee / $total;
    //客单价格
    $order_people_price  = $all_order_fee / $all_customer_num;

//    foreach ($order_list as $key => &$value)
//    {
//        $value->seat = \Cache\Seat::Get($value->seat_id);
//        if (!$value->seat)
//        {
//            $value->seat = [];
//        }
//        else
//        {
//            $value->seat->seat_price = Util::FenToYuan($value->seat->seat_price);
//        }
//    }
    //LogDebug($list);
    $page_all = ceil($total/$page_size);//总共页数
    $resp = (object)[
       'order_list'          => $list,
       'total'               => $total,                                  //总单数
       'all_order_fee'       => round($all_order_fee,2),        //订单金额总价
       'get_real_order'      => round($paid_price,2),           //实收总价
       'get_cope_order'      => round($order_payable,2),        //应付总价
       'order_average_price' => round($order_average_price,2),  //订单平均价
       'order_people_price'  => round($order_people_price,2),   //客单价
       'order_nopay_price'   => round($all_order_fee,2),        //未支付总金额
       'order_status'        => $order_status,
       'page_all'            => $page_all,
       'page_no'             => $page_no
    ];

    LogInfo("--ok--");
    return 0;
}
// 订单统计
function GetOrderStat(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $is_byday   = $_["byday"];
    $is_bymon   = $_["bymon"];
    $is_byyear  = $_["byyear"];
    $begin_time = $_["begin_time"];
    $end_time   = $_["end_time"];

    if("" == $begin_time)
    {
        $begin_time = date("Y-m-d");
    }
    if("" == $end_time)
    {
        $end_time = $begin_time;
    }
    LogDebug("begin_time:[$begin_time], end_time:[$end_time]");
    $begin_time_sec = strtotime($begin_time);
    $end_time_sec = strtotime($end_time) + 3600*24 - 1; // 一天的最后一秒

    $shop_id = \Cache\Login::GetShopId();
    LogDebug("shop_id:[$shop_id]");

    $mgo = new \DaoMongodb\Order;
    $order_list = $mgo->GetOrderList(
        [
            'shop_id'    => $shop_id,
            'begin_time' => $begin_time_sec,
            'end_time'   => $end_time_sec
        ],
        [
            "order_time"     => 1,
            "customer_num"   => 1,
            "food_price_all" => 1,
            "food_num_all"   => 1,
        ],
        ["lastmodtime" => 1]
    );
    //LogDebug($order_list);

    // 按天统计
    $byday = [];
    if($is_byday)
    {
        foreach($order_list as $key => &$value)
        {
            $order_day = date("Y-m-d", $value->order_time);
            $data = &$byday[$order_day];
            $data['order_num']++;
            $data['food_num'] += $value->food_num_all;
            $data['order_fee'] += $value->food_price_all;
            $data['persion_num'] += $value->customer_num;
        }
        foreach($byday as $k => &$v)
        {
            $v['order_day'] = $k;
        }
        usort($byday);
    }

    // 按月统计
    $bymon = [];
    if($is_bymon)
    {
        foreach($order_list as $key => &$value)
        {
            $order_mon = date("Y-m", $value->order_time);
            $data = &$bymon[$order_mon];
            $data['order_num']++;
            $data['food_num'] += $value->food_num_all;
            $data['order_fee'] += $value->food_price_all;
            $data['persion_num'] += $value->customer_num;
        }
        foreach($bymon as $k => &$v)
        {
            $v['order_mon'] = $k;
        }
        usort($bymon);
    }

    // 按年统计
    $byyear = [];
    if($is_byyear)
    {
        foreach($order_list as $key => &$value)
        {
            $order_year = date("Y", $value->order_time);
            $data = &$byyear[$order_year];
            $data['order_num']++;
            $data['food_num'] += $value->food_num_all;
            $data['order_fee'] += $value->food_price_all;
            $data['persion_num'] += $value->customer_num;
        }
        foreach($byyear as $k => &$v)
        {
            $v['order_year'] = $k;
        }
        usort($byyear);
    }

    $resp = (object)array(
        'byday' => $byday,
        'bymon' => $bymon,
        'byyear' => $byyear,
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//pad端待处理订单（新订单,退款,出单未支付）
function GetOrderPending(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $order_status_list  = [1,2,8];
    $shop_id            = $_['shop_id'];
    $sort['order_time'] = -1;
    $shop_info = new \DaoMongodb\Shop;
    if (!$shop_id) {
        $shop_id = \Cache\Login::GetShopId();
    }
    $shop_s = $shop_info->GetShopById($shop_id);
    if(!$shop_s->shop_id)
    {
        return errcode::SHOP_NOT_WEIXIN;
    }
    LogDebug("shop_id:[$shop_id]");
    $mgo        = new \DaoMongodb\Order;
    $infos      = $mgo->GetOrderList(
        [
            'shop_id'           => $shop_id,
            'order_status_list' => $order_status_list,
        ],
        [],
        $sort
    );
    $order_info = [];
    foreach ($infos as $v)
    {
        if($v->order_status == NewOrderStatus::NOPAY && ($v->pay_way == PayWay::WEIXIN || $v->pay_way == PayWay::APAY))
        {
            continue; //所有属于微信支付且未支付成功的都不显示在PAD里
        }
        if($v->order_status == NewOrderStatus::NOPAY && $v->selfhelp_id)
        {
            continue; //自助点餐机未支付的不显示
        }
        if(($v->order_status == NewOrderStatus::PAY && $v->is_confirm == 0)|| ($v->order_status == NewOrderStatus::NOPAY && $v->is_confirm == 0))
        {
            $type = 0; //新订单
        }
        elseif($v->order_status == NewOrderStatus::REFUNDING)
        {
            $type = 1; //退款订单
        }
        elseif($v->order_status == NewOrderStatus::NOPAY && $v->pay_status == 1)
        {
            $type = 2; //下单未支付
        } else{
            continue;
        }
        $info['order_id']      = $v->order_id;
        $info['serial_number'] = $v->order_water_num;
        $info['type']          = $type;
        $info['serial_time']   = date('Y-m-d H:i:s',$v->order_time);
        $info['price']         = $v->order_fee;
//        if($v->order_from == 4)
//        {
//            $info['device_type'] = 0;
//
//        }elseif(!$v->order_from){
//
//            $info['device_type'] = 1;
//        }else{
//            $info['device_type'] = $v->order_from;
//        }
        $info['device_type'] = $v->order_from;
        if ($v->order_status == NewOrderStatus::NOPAY)
        {
            $info['is_pay'] = false;
        }
        if ($v->order_status == NewOrderStatus::PAY || $v->order_status == NewOrderStatus::REFUNDING)
        {
            $info['is_pay'] = true;
        }
        array_push($order_info, $info);
    }
    $resp = (object)[
        'pendding' => $order_info,
    ];

    LogInfo("--ok--");
    return 0;
}
// 手机端的订单统计
function GetPhoneOrderStat(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $by_day     = $_["by_day"];   //今日
    $by_week    = $_["by_week"];  //本周
    $by_month   = $_["by_month"]; //当月
    $shop_id    = $_['shop_id'];
    //$is_byyear  = $_["byyear"];
    $begin_time = $_["begin_time"]; //自定义开始时间
    $end_time   = $_["end_time"];   //自定义结束时间
    if(!$shop_id)
    {
        $shop_id = \Cache\Login::GetShopId();
        LogDebug("shop_id:[$shop_id]");
    }
    if($by_day)
    {
        $begin_time = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $end_time   = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
    }
    if($by_week)
    {
        $begin_time = strtotime(date('Y-m-d', strtotime("this week Monday", time())));
        $end_time   = strtotime(date('Y-m-d', strtotime("this week Sunday", time()))) + 24 * 3600 - 1;
    }
    if($by_month)
    {
        $begin_time = mktime(0,0,0,date('m'),1,date('Y'));
        $end_time   = mktime(23,59,59,date('m'),date('t'),date('Y'));
    }
    if (!$begin_time && $end_time)
    {
        $begin_time = -28800; //默认时间戳开始时间
    }
    if (!$end_time && $begin_time)
    {
        $end_time = 1922354460; //默认后面很长时 间
    }
    $mgo = new \DaoMongodb\Order;
    $order_status = [1,2,4,5,6,7,8];
    $order_list = $mgo->GetOrderStat(
        [
            'shop_id'     => $shop_id,
            'begin_time'  => $begin_time,
            'end_time'    => $end_time,
            'order_status'=> $order_status
        ]
    );
    //var_dump($order_list);die;
    //取出统计的数据
    $data  = [];
    $money = [];
    $food  = [];
    $money['cash']     = 0;
    $money['weixin']   = 0;
    $money['apay']     = 0;
    $money['bank_pay'] = 0;
    $money['guaz']     = 0;
    $order_fee  = 0;
    $paid_price = 0;
    foreach($order_list as $key => &$value)
    {

        $order_fee  += $value->order_fee;
        $paid_price += $value->paid_price;
        $data['order_num']++;
        $data['order_fee']  = sprintf("%.2f",$order_fee);
        $data['paid_price'] = sprintf("%.2f",$paid_price);
        $data['food_all_num'] += $value->food_num_all;
        $data['customer_num'] += $value->customer_num;
        if($value->pay_way == PayWay::CASH)
        {
            $money['cash'] += $value->paid_price;
        }
        if($value->pay_way == PayWay::WEIXIN)
        {
            $money['weixin'] += $value->paid_price;
        }
        if($value->pay_way == PayWay::APAY)
        {
            $money['apay'] += $value->paid_price;
        }
        if($value->pay_way == PayWay::BANK)
        {
            $money['bank_pay'] += $value->paid_price;
        }
        if($value->pay_way == PayWay::GUAZ)
        {
            $money['guaz'] += $value->paid_price;
        }
        $money['paid_price'] += $value->paid_price;
        foreach ($value->food_list as $f)
        {
            $food[$f->food_id]['food_name']   = $f->food_name;
            $food[$f->food_id]['food_price'] += $f->food_price_sum;
            $food[$f->food_id]['food_num']   += $f->food_num;
        }
    }
    usort($food, function($a, $b){
                return ($a['food_num'] - $b['food_num']);
            });

    $four = array_slice($food,0,5);   //销售最差的5个

    usort($food, function($a, $b){
        return ($b['food_num'] - $a['food_num']);
    });
    $three = array_slice($food,0,10);   //销售最好的10个



    $resp = (object)array(
        'one'   => $data,
        'two'   => $money,
        'three' => $three,
        'four'  => $four
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["order_info"]))
{
    $ret = GetOrderInfo($resp);
}
elseif(isset($_["orderlist"]))
{
    $ret = GetOrderList($resp);
}
elseif(isset($_["get_order_list"]))
{
    $ret = GetOrderAllList($resp);
}
elseif(isset($_["orderstat"]))
{
    $ret = GetOrderStat($resp);
}
elseif(isset($_["get_order_pendding"]))
{
//    if($_['srctype'] == 3)
//    {
//    Permission::PadUserPermissionCheck(Position::NEW_ORDER);
//    }
    $ret = GetOrderPending($resp);
}elseif(isset($_["get_order_stat"]))
{
    $ret = GetPhoneOrderStat($resp);
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
    'data' => $resp
);

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

