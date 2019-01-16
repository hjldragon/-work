<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{
    $_ = &$GLOBALS["_"];
    $_['order_id']        = $_['order_num'];
    $_['srctype']         = 3;
    $_['order_info']      = true;
    require("order_get.php");
    //LogDebug($_);
}
function Output(&$obj)
{

    $info = &$obj->data->order_info;

    if(!$info)
    {
        $obj->data = (object)array(
        );
    }else {

        $order_info['order_state']   = Order::$type_name[$info->order_status];
        $order_info['order_num']     = $info->order_id;
        $order_info['order_src']     = Order::$order_from[$info->order_from];
        if(!$info->employee_name)
        {
            $order_info['order_who']     = '--';
        }else{
            $order_info['order_who']     = $info->employee_name;
        }
        $order_info['order_time']    = date('Y-m-d H:i:s', $info->order_time);
        $order_info['serial_number'] = $info->order_water_num;
        $order_info['table_num']     = $info->seat->seat_name;
        $order_info['table_area']    = $info->seat->seat_region;
        $order_info['table_type']    = $info->seat->seat_type;
        $order_info['customer_num']  = $info->customer_num;
        $order_info['sale_type']     = Order::$sale_type[$info->dine_way];
        $order_info['invoice_gave']  = Order::$invoice_gave[$info->is_invoicing];

        $pay_info                    = [];
        $pay_info['pay_state']       = Order::$pay_status[$info->pay_status];
        $pay_info['order_money']     = $info->order_fee;
        if (!$info->order_waiver_fee) {
            $info->order_waiver_fee = 0;
        }
        $pay_info['reduce_money'] = $info->order_waiver_fee;
        $pay_info['need_money']   = $info->order_payable;
        if ($info->maling_price) {
            $pay_info['maling_price'] = round((float)$info->maling_price, 2);
        }

//        if ($info->order_status != 1 && $info->pay_way != 5) {
//            $pay_info['real_money'] = $info->paid_price;
//        }else
        if ($info->order_status == 4 || $info->order_status ==5 )//退款中显示的实收金额
        {
            if($info->order_waiver_fee)
            {
                $pay_info['real_money'] = $info->order_payable-$info->order_waiver_fee;
            }else{
                $pay_info['real_money'] = $info->order_payable;
            }
        }elseif($info->pay_way == 5){ //挂账显示的实际金额
            $pay_info['real_money'] = '--';
        }elseif ($info->order_status == 6 && $info->pay_status == 1){ //关闭并未支付订单不显示
            $pay_info['real_money'] = null;
        }elseif ($info->order_status == 1 ){
            $pay_info['real_money'] = '--';
        }else{
            $pay_info['real_money'] = $info->paid_price;
        }
        $pay_info['take_off_zero_money'] = $info->maling_price;
        $pay_info['cashier']             = $info->employee_name;
        if ($info->pay_time) {
            $pay_info['pay_time'] = date('Y-m-d H:i:s', $info->pay_time);
        }
        $pay_info['pay_type']        = Order::$pay_type[$info->pay_way];
        $product_info                = [];
        $product_info['total_price'] = $info->order_fee;
        $product_info['remark']      = $info->order_remark;
        $product_info['total_count'] = $info->total_count;
        $product_list                = [];
        foreach ($info->food_list as $food) {
            $product['id']           = $food->id;
            $product['food_id']      = $food->food_id;
            $product['product_name'] = $food->food_name;
            $product['unit_price']   = $food->food_price;
            $product['number']       = $food->food_num;
            $product['count_price']  = $food->food_price_sum;
            $product['is_pack']      = Order::$is_ps[$food->is_pack];
            $product['is_gift']      = Order::$is_ps[$food->is_send];
            $product['gift_reason']  = $food->send_remark;
            foreach ($food->food_attach_list as $k=>$fl)
            {
                if($fl->title == '份量'){
                    unset($food->food_attach_list[$k]);
                }
                if($fl->spec_value == "")
                {
                    unset($food->food_attach_list[$k]);
                }
            }
            $product['attribute']    = $food->food_attach_list;
            $product['weight']       = Order::$weight_pad[$food->weight];
            if(!$food->is_add)
            {
                $product['is_add']      = 0;
            }else{
                $product['is_add']      = $food->is_add;
            }

            array_push($product_list, $product);
        }
        //餐位费用
        $product_two['product_name'] = '餐位费';
        $product_two['unit_price']   = round($info->seat->price,2);
        $product_two['number']       = $info->seat->num;
        $product_two['is_seat']      = true;//<<<<<<<<<<<<<<pad端用来判断是否餐位费
        $product_two['is_pack']      = false;
        $product_two['is_gift']      = false;
        $product_two['count_price']  = round($info->seat->price,2);
        array_push($product_list, $product_two);
        $product_info['product_list'] = $product_list;
        if ($info->order_status == 7) {
            foreach ($info->status_info as $s) {
                if ($s['order_status'] == 7) {
                    $on_credit_info                          = [];
                    $on_credit_info['oper_user']             = $s['employee_name'];
                    $on_credit_info['on_credit_time']        = date('Y-m-d H:i:s', $s['made_time']);
                    $on_credit_info['customer_name']         = $s['customer_name'];
                    $on_credit_info['customer_connect_info'] = $s['customer_phone'];
                    $on_credit_info['on_credit_reason']      = $s['made_cz_reson'];
                }
            }
        } else {
            $on_credit_info = null;
        }
        if ($info->order_status == 4 || $info->order_status == 5) {

            foreach ($info->status_info as $s) {
               if($s['order_status'] == 8)
               {
                   $refund_time = $s['apply_time'];
                   $apply_reson = $s['made_reson'];
               }
                $refund_info = [];
                if ($s['order_status'] == 4) {
                    $refund_time = $s['apply_time'];
                    $apply_reson = $s['made_reson'];
                    $refund_info['oper_user']   = $s['employee_name'];
                    $refund_info['apply_time']  = date('Y-m-d H:i:s', $refund_time);
                    $refund_info['refund_time'] = date('Y-m-d H:i:s', $info->refunds_time);

                    if (!in_array($s['order_status'], [1, 2, 3, 4, 5, 6, 7, 8])) {
                        $info['refund_state'] = '其他';
                    } else {
                        $refund_info['refund_state'] = Order::$type_name[$s['order_status']];
                    }
                    if($info->order_waiver_fee)
                    {
                        $refund_info['refund_money'] = $info->order_payable-$info->order_waiver_fee;
                    }else{
                        $refund_info['refund_money'] = $info->order_payable;
                    }
                    //$refund_info['refund_money']       = $info->order_payable;
                    //$refund_info['real_money']         = $info->order_payable;//实收金额
                    $refund_info['refund_reason']      = $s['made_cz_reson'];
                    $refund_info['apply_refund_reason']= $apply_reson;
                    //$refund_info['refund_fail_reason'] = $s['made_reson'];//<<<<<<<<<<<可以有申请原因？
                }
                if ($s['order_status'] == 5) {

                    $refund_info['oper_user']   = $s['employee_name'];
                    $refund_info['apply_time']  = date('Y-m-d H:i:s', $s['made_time']);
                   // $refund_info['refund_time'] = date('Y-m-d H:i:s', $info->refunds_fail_time);
                    if (!in_array($s['order_status'], [1, 2, 3, 4, 5, 6, 7])) {
                        $info['refund_state'] = '其他';
                    } else {
                        $refund_info['refund_state'] = Order::$type_name[$s['order_status']];
                    }
                    $refund_info['refund_money']       = $info->order_payable;
                    //$refund_info['real_money']         = $info->order_payable;//实收金额
                    $refund_info['refund_reason']      = $s['made_reson'];
                    $refund_info['refund_fail_reason'] = $s['made_cz_reson'];

                }
            }

        } else {
            $refund_info = null;
        }
        if ($info->order_status == 6) {
            //($info->order_status == 6 && $info->pay_status == 1 && $info->is_invoicing == 0)
            foreach ($info->status_info as $s) {
                if ($s['order_status'] == 6) {
                    $close_info                 = [];
                    $close_info['oper_user']    = $s['employee_name'];
                    $close_info['oper_time']    = date('Y-m-d H:i:s', $info->close_time);
                    $close_info['close_reason'] = $s['made_cz_reson'];
                }
                if ($s['order_status'] == 7) {
                    $on_credit_info                          = [];
                    $on_credit_info['oper_user']             = $s['employee_name'];
                    $on_credit_info['on_credit_time']        = date('Y-m-d H:i:s', $s['made_time']);
                    $on_credit_info['customer_name']         = $s['customer_name'];
                    $on_credit_info['customer_connect_info'] = $s['customer_phone'];
                    $on_credit_info['on_credit_reason']      = $s['made_cz_reson'];
                }
            }
        } else {
            $close_info = null;
            // $on_credit_info = null;
        }
        if ($info->order_status == 3) {
            foreach ($info->status_info as $s) {
                if ($s['order_status'] == 3) {
                    $back_pay_info                    = [];
                    $back_pay_info['oper_user']       = $s['employee_name'];
                    $back_pay_info['oper_time']       = date('Y-m-d H:i:s', $info->checkout_time);
                    $back_pay_info['back_pay_reason'] = $s['made_cz_reson'];
                }
            }

        } else {
            $back_pay_info = null;
        }
        //退款中的退款信息<<<<<<<<<<<<<<<<pad协议里面没有该操作
        if ($info->order_status == 8) {
            foreach ($info->status_info as $s) {
                $refund_info = [];
                if ($s['order_status'] == 8) {
                    $refund_info['refund_time']        = date('Y-m-d H:i:s', $s['apply_time']);
                    $refund_info['refund_money']       = $info->paid_price;
                    $refund_info['refund_reason']      = $s['made_reson'];
                }
            }
        }
// else {
//            $refund_info = null;
//        }

        //LogDebug($refund_info);
        //根据订单状态和发票状态,支付状态，确认状态来返回按钮状态
        if (/*($info->order_status == 1 && $info->is_invoicing == 0 && $info->is_confirm == 1) ||*/
            ($info->order_status == 1 && $info->order_sure_status == 2 && $info->is_confirm == 1)
        ) {
            $ids       = [0, 5, 11, 13, 12];
            $oper_list = OrderLable($ids);
        }elseif ($info->order_status == 1 && $info->is_confirm == 1) {
            $ids       = [0, 5, 11, 13, 12];
            $oper_list = OrderLable($ids);
        } elseif ($info->order_status == 1 && $info->is_confirm == 0) {
            $ids       = [6, 5, 11, 13, 7];
            $oper_list = OrderLable($ids);
        } elseif ($info->order_status == 2 && $info->is_confirm == 0) {
            $ids       = [6, 5, 13];
            $oper_list = OrderLable($ids);
        }elseif ($info->order_status == 2 && $info->is_confirm == 1) {
            $ids       = [1, 2, 3];
            $oper_list = OrderLable($ids);
        }
//        elseif ($info->order_status == 2 && $info->is_confirm == 1) {
//            $ids       = [2, 5, 3];
//            $oper_list = OrderLable($ids);
//        } elseif ($info->order_status == 1 && $info->order_sure_status == 2) {
//            $ids       = [0, 6, 11, 12, 13];
//            $oper_list = OrderLable($ids);
//        } elseif ($info->order_status == 2 && $info->is_confirm == 0 && $info->pay_status == 2) {
//            $ids       = [6, 9];
//            $oper_list = OrderLable($ids);
//        }
//        elseif ($info->order_status == 4 && $info->is_invoicing == 1 && $info->is_confirm == 1) {
//            $ids       = [4];
//            $oper_list = OrderLable($ids);
//        }
        elseif ($info->order_status == 5 /*&& $info->is_invoicing == 1 */&& $info->is_confirm == 1) {
            $ids       = [2];
            $oper_list = OrderLable($ids);
        }
//        elseif ($info->order_status == 5 && $info->is_invoicing == 0 && $info->is_confirm == 1) {
//            //$ids       = [1, 2];
//            $ids       = [2];
//            $oper_list = OrderLable($ids);
//        }
//        elseif ($info->order_status == 3 && $info->is_invoicing == 1 && $info->is_confirm == 1) {
//            $ids       = [4];
//            $oper_list = OrderLable($ids);
//        }
        elseif ($info->order_status == 7 && $info->is_invoicing == 0 && $info->is_confirm == 1) {
            $ids       = [5,0];
            $oper_list = OrderLable($ids);
        } elseif ($info->order_status == 8 /*&& $info->is_confirm == 0*/ && $info->pay_status == 2) {
            $ids       = [3, 8];
            $oper_list = OrderLable($ids);
        } else {
            $oper_list = [];
        }

        $obj->data = (object)[
            'order_info'     => $order_info,
            'pay_info'       => $pay_info,
            'product_info'   => $product_info,
            'on_credit_info' => $on_credit_info,
            'refund_info'    => $refund_info,
            'close_info'     => $close_info,
            'back_pay_info'  => $back_pay_info,
            'oper_info'      => $oper_list
        ];
    }
    // LogDebug($obj);
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
            $oper['need_dlg_confirm']   = false;
            $oper['need_admin_confirm'] = false;
            array_push($oper_list,$oper);
        }
        if($id == 1)
        {
            $oper['lable'] = "开发票";
            $oper['id']    = 1;
            $oper['need_dlg_confirm']   = false;
            $oper['need_admin_confirm'] = false;
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
            $oper['need_dlg_confirm']   = true;
            $oper['need_admin_confirm'] = true;
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
            $oper['need_dlg_confirm']   = true;
            $oper['need_admin_confirm'] = false;
            array_push($oper_list,$oper);
        }
        if($id == 6)
        {
            $oper['lable'] = "下单";
            $oper['id']    = 6;
            $oper['need_dlg_confirm']   = false;
            $oper['need_admin_confirm'] = false;
            array_push($oper_list,$oper);
        }
        if($id == 7)
        {
            $oper['lable'] = "下单并结账";
            $oper['id']    = 7;
            $oper['need_dlg_confirm']   = false;
            $oper['need_admin_confirm'] = false;
            array_push($oper_list,$oper);
        }
        if($id == 8)
        {
            $oper['lable'] = "拒绝退款";
            $oper['id']    = 8;
            $oper['need_dlg_confirm']   = true;
            $oper['need_admin_confirm'] = false;
            array_push($oper_list,$oper);
        }
        if($id == 9)
        {
            $oper['lable'] = "关闭并退款";
            $oper['id']    = 9;
            $oper['need_dlg_confirm']   = true;
            $oper['need_admin_confirm'] = true;
            array_push($oper_list,$oper);
        }
        if($id == 11)
        {
            $oper['lable'] = "加菜";
            $oper['id']    = 11;
            $oper['need_dlg_confirm']   = false;
            $oper['need_admin_confirm'] = false;
            array_push($oper_list,$oper);
        }
        if($id == 12)
        {
            $oper['lable'] = "催菜";
            $oper['id']    = 12;
            $oper['need_dlg_confirm']   = false;
            $oper['need_admin_confirm'] = false;
            array_push($oper_list,$oper);
        }
        if($id == 13)
        {
            $oper['lable'] = "退菜";
            $oper['id']    = 13;
            $oper['need_dlg_confirm']   = false;
            $oper['need_admin_confirm'] = false;
            array_push($oper_list,$oper);
        }
    }

    return $oper_list;
}
?>