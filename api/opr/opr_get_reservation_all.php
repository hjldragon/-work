<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");

function Input()
{
    $_                        = &$GLOBALS["_"];
    $_['reservation_time']    = $_['reserve_time'];
    $_['srctype']             = 3;
    if ($_['start_time'])
    {
        $_['begin_time']      = strtotime($_['start_time']);
    }
    if ($_['end_time'])
    {
        $_['end_time']        = strtotime($_['end_time']) + 86399;
    }
    $_['reservation_status']  = Reserve::$status[$_['state']];
    $_['customer_name']       = $_['keyword'];
    $_['customer_phone']      = $_['keyword'];
    $_['get_reservation_all'] = true;
    require("reservation_get.php");
    //LogDebug($_);
}
function Output(&$obj)
{

    $info = $obj->data->reservation_list;
    $list = [];
    foreach ($info as $v)
    {
        $reserve = [];
        $reserve['id']                  = $v->reservation_id;
        $reserve['state']               = Reserve::$reservation_status[$v->reservation_status];
        $reserve['source']              = Reserve::$source[$v->source];
        $reserve['create_user']         = $v->employee_name;
        $reserve['create_time']         = date('Y-m-d H:i:s',$v->ctime);
        $reserve['reserve_time']        = date('Y-m-d H:i:s',$v->reservation_time);
        if($v->seat_name)
        {
            $reserve['table']           = $v->seat_name;
        }else{
            $reserve['table']           = "--";
        }
        if($v->seat_type)
        {
            $reserve['table_type']      = $v->seat_type;
        }else{
            $reserve['table_type']      = "--";
        }
        $reserve['name']                = $v->customer_name;
        $reserve['total_people']        = $v->customer_num;
        $reserve['phone']               = $v->customer_phone;
        $reserve['gender']              = Reserve::$reservation_sex[$v->customer_sex];
        if($v->note)
        {
            $reserve['remark']          = $v->note;
        }else{
            $reserve['remark']          = " ";
        }
        if($v->reservation_status == 2){
            $reserve['edit_user']           = $v->reserve_info->employee_name;
            $reserve['arrive_time']         = date('Y-m-d H:i:s',$v->reserve_info->made_time);
            $reserve['arrive_total_people'] = $v->reserve_info->reserve_num;
        }
        if ($v->reservation_status == 3)
        {
            $reserve['edit_user']           = $v->reserve_info->employee_name;
            $reserve['cancel_time']         = date('Y-m-d H:i:s',$v->reserve_info->made_time);
            $reserve['cancel_remark']       = $v->reserve_info->reson;
        }

        array_push($list,$reserve);
    }

    $obj->data = $list;

    echo json_encode($obj);
}
Input();
?>