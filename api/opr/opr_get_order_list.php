<?php
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{
    $_                     = &$GLOBALS["_"];
    $_['srctype']          = 1;
    $_['page_size']        = $_['page_num'];
    $_['page_no']          = $_['page_index'];
    //LogDebug($_['from_time']);
    $_['order_begin_time'] = $_['from_time'];
    $_['order_end_time']   = $_['to_time'];
    $_['pay_way']          = $_['pay_type'];
    $_['pay_way']          = $_['pay_type'];
    $_['order_id']         = $_['order_num'];
    $_['seat_name']        = $_['table_num'];
    $_['sort_name']        = $_['sort_key'];
    $_['desc']             = $_['sort_type'];
    $_['get_order_list']   = true;
    $_['order_status']     = Order::$order_status[$_['type_id']];
    $_['dine_way']         = Order::$dine_way[$_['sale_type']];
    $_['is_invoicing']     = Order::$is_invoicing[$_['invoice_gave']];
    $_['pay_way']          = Order::$pay_way[$_['pay_way']];
    $from_time_pay         = $_['from_time_pay'];
    $to_time_pay           = $_['to_time_pay'];
    if($_['type_id'] == 2)
    {
        $_['begin_time'] = $from_time_pay;
        $_['end_time']   = $to_time_pay;
    }
    $from_time_back_pay = $_['from_time_back_pay'];
    $to_time_back_pay   = $_['to_time_back_pay'];
    if($_['type_id'] == 4)
    {
        $_['begin_time'] = $from_time_back_pay;
        $_['end_time']   = $to_time_back_pay;
    }
    $from_time_refund = $_['from_time_refund'];
    $to_time_refund   = $_['to_time_refund'];
    if($_['type_id'] == 5)
    {
        $_['begin_time'] = $from_time_refund;
        $_['end_time']   = $to_time_refund;
    }
    $from_time_reject_refund = $_['from_time_reject_refund'];
    $to_time_reject_refund   = $_['to_time_reject_refund'];
    if($_['type_id'] == 6)
    {
        $_['begin_time'] = $from_time_reject_refund;
        $_['end_time']   = $to_time_reject_refund;
    }
  //排序字段名装换
    switch ($_['sort_type']) {
        case 'desc':
            $_['desc'] = -1;
            break;
        case 'asc':
            $_['desc'] = 1;
            break;
        default:
            break;
    }
    switch ($_['sort_key']) {
        case 'order_num':
            $_['sort_name'] = 'order_id';
            break;
        case 'create_time':
            $_['sort_name'] = 'order_time';
            break;
        case 'order_money':
            $_['sort_name'] = 'order_payable';
            break;
        case 'real_money':
            $_['sort_name'] = 'paid_price';
            break;
        case 'pay_time':
            $_['sort_name'] = 'pay_time';
            break;
        case 'back_pay_time':
            $_['sort_name'] = 'checkout_time';
            break;
        case 'reject_refund_time':
            $_['sort_name'] = 'refunds_fail_time';
            break;
        case 'close_time':
            $_['sort_name'] = 'close_time';
            break;
        case 'need_money':
            $_['sort_name'] = 'order_payable';
            break;
        default:
            break;
    }
    //LogDebug($_);
    require("order_get.php");
}


function Output(&$obj)
{
    $order_list = $obj->data->order_list;
    $type       = $obj->data->order_status;
    switch ($type) {
        case null:
            $type_id                            = 0;
            $statistics->order_count            = $obj->data->total;
            $statistics->order_money            = $obj->data->all_order_fee;
            $statistics->real_money             = $obj->data->get_real_order;
            $statistics->oder_average_money     = $obj->data->order_average_price;
            $statistics->customer_average_money = $obj->data->order_people_price;
            break;
        case 1:
            $type_id                            = 1;
            $statistics->order_count            = $obj->data->total;
            $statistics->order_money            = $obj->data->all_order_fee;
            $statistics->unpay_money            = $obj->data->order_nopay_price;
            $statistics->oder_average_money     = $obj->data->order_average_price;
            $statistics->customer_average_money = $obj->data->order_people_price;
            break;
        case 2:
            $type_id                            = 2;
            $statistics->order_count            = $obj->data->total;
            $statistics->order_money            = $obj->data->all_order_fee;
            $statistics->pay_money              = $obj->data->get_real_order;
            $statistics->oder_average_money     = $obj->data->order_average_price;
            $statistics->customer_average_money = $obj->data->order_people_price;
            break;
        case 3:
            $type_id                            = 4;
            $statistics->order_count            = $obj->data->total;
            $statistics->order_money            = $obj->data->all_order_fee;
            $statistics->back_pay_money         = $obj->data->get_cope_order;
            $statistics->oder_average_money     = $obj->data->order_average_price;
            $statistics->customer_average_money = $obj->data->order_people_price;
            break;
        case 4:
            $type_id                            = 5;
            $statistics->order_count            = $obj->data->total;
            $statistics->order_money            = $obj->data->all_order_fee;
            $statistics->refund_money           = $obj->data->get_cope_order;
            $statistics->oder_average_money     = $obj->data->order_average_price;
            $statistics->customer_average_money = $obj->data->order_people_price;
            break;
        case 5:
            $type_id                            = 6;
            $statistics->order_count            = $obj->data->total;
            $statistics->order_money            = $obj->data->all_order_fee;
            $statistics->pay_money              = $obj->data->get_real_order;
            $statistics->oder_average_money     = $obj->data->order_average_price;
            $statistics->customer_average_money = $obj->data->order_people_price;
            break;
        case 6:
            $type_id                            = 7;
            $statistics->order_count            = $obj->data->total;
            $statistics->order_money            = $obj->data->all_order_fee;
            $statistics->unpay_money            = $obj->data->get_cope_order;
            $statistics->oder_average_money     = $obj->data->order_average_price;
            $statistics->customer_average_money = $obj->data->order_people_price;
            break;
        case 7:
            $type_id                            = 3;
            $statistics->order_count            = $obj->data->total;
            $statistics->order_money            = $obj->data->all_order_fee;
            $statistics->delay_pay_money        = $obj->data->get_cope_order;
            $statistics->oder_average_money     = $obj->data->order_average_price;
            $statistics->customer_average_money = $obj->data->order_people_price;
            break;
        default:
            # code...
            break;
    }
    $info_list = [];
    foreach ($order_list as $order)
    {
        $info['order_num']             = $order->order_id;
        $info['create_time']           = date('Y-m-d H:i:s',$order->order_time);
        $info['pay_time']              = date('Y-m-d H:i:s',$order->pay_time);
        $info['back_pay_time']         = date('Y-m-d H:i:s',$order->checkout_time);
        $info['reject_refund_time']    = date('Y-m-d H:i:s',$order->refunds_fail_time);
        $info['refund_time']           = date('Y-m-d H:i:s',$order->refunds_time);
        $info['close_time']            = date('Y-m-d H:i:s',$order->close_time);
        $info['table_num']             = $order->seat_name;
        $info['customer_name']         = $order->customer_name;
        $info['customer_contact_info'] = $order->customer_phone;
        if (!in_array($order->dine_way, [1, 2, 3, 4]))
        {
            $info['sale_type'] = '其他';
        } else {
            $info['sale_type'] = Order::$sale_type[$order->dine_way];
        }
        $info['order_money'] = $order->order_fee;
        if($order->order_status == 1 && !$info->paid_price)
        {
            $order->paid_price = 0;
        }
        $info['real_money']  = $order->paid_price;
        $info['need_money']  = $order->order_payable;
        if (!in_array($order->pay_way, [1, 2, 3, 4, 5]))
        {
            $info['pay_type'] = '其他';
        } else {
            $info['pay_type'] = Order::$pay_type[$order->pay_way];
        }
        if (!in_array($order->is_invoicing, [0, 1]))
        {
            $info['invoice_gave'] = '其他';
        } else {
            $info['invoice_gave'] = Order::$invoice_gave[$order->is_invoicing];
        }

        if (!in_array($order->order_status, [1, 2, 3, 4, 5, 6, 7]))
        {
            $info['order_state'] = '其他';
        } else {

            $info['order_state'] = Order::$type_name[$order->order_status];
        }
        //LogDebug($info['order_state']);
        $info['oper']               = "detail";
        array_push($info_list,$info);
    }
    $data = (object)[
        'type_id'             => $type_id,
        'page_index'          => $obj->data->page_no,
        'total_page'          => $obj->data->page_all,
        'statistics'          => $statistics,
        'table_data'          => $info_list,
    ];
   // LogDebug($data);
    $obj->data = $data;
    echo json_encode($obj);
}


Input();
function OrderLable($ids)
{
    $oper_list = [];
    foreach ($ids as $id)
    {
        if($id == 0)
        {
            $oper['lable'] = "结账";
            $oper['id']    = 0;
            $oper['need_dlg_confirm'] = false;
            $oper['need_admin_confirm'] =false;
            array_push($oper_list,$oper);
        }
        if($id == 1)
        {
            $oper['lable'] = "开发票";
            $oper['id']    = 1;
            $oper['need_dlg_confirm'] = false;
            $oper['need_admin_confirm'] =false;
            array_push($oper_list,$oper);
        }
        if($id == 2)
        {
            $oper['lable'] = "反结账";
            $oper['id']    = 2;
            $oper['need_dlg_confirm']   = true;
            $oper['need_admin_confirm'] = true;
            array_push($oper_list,$oper);
        }
        if($id == 3)
        {
            $oper['lable'] = "退款";
            $oper['id']    = 3;
            $oper['need_dlg_confirm'] = true;
            $oper['need_admin_confirm'] =true;
            array_push($oper_list,$oper);
        }
        if($id == 4)
        {
            $oper['lable'] = "红冲";
            $oper['id']    = 4;
            $oper['need_dlg_confirm'] = true;
            $oper['need_admin_confirm'] =true;
            array_push($oper_list,$oper);
        }
        if($id == 5)
        {
            $oper['lable'] = "关闭订单";
            $oper['id']    = 5;
            $oper['need_dlg_confirm'] = true;
            $oper['need_admin_confirm'] =true;
            array_push($oper_list,$oper);
        }
        if($id == 6)
        {
            $oper['lable'] = "下单";
            $oper['id']    = 6;
            $oper['need_dlg_confirm'] = false;
            $oper['need_admin_confirm'] =false;
            array_push($oper_list,$oper);
        }
        if($id == 7)
        {
            $oper['lable'] = "下单并结账";
            $oper['id']    = 7;
            $oper['need_dlg_confirm'] = false;
            $oper['need_admin_confirm'] =false;
            array_push($oper_list,$oper);
        }
    }

    return $oper_list;
}
?>