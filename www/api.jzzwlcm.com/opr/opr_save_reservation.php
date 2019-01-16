<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");

function Input()
{
    $_                       = &$GLOBALS["_"];
    $_['reservation_id']     = $_['id'];
    $_['customer_name']      = $_['name'];
    $_['customer_sex']       = Reserve::$sex[$_['gender']];
    $_['reservation_time']   = strtotime($_['reserve_time']);
    $_['customer_phone']     = $_['phone'];
    $_['customer_num']       = $_['total_people'];
    $_['seat_name']          = $_['table'];
    $_['reson']              = $_['cancel_remark'];
    $_['save_reservation']   = true;
    $_['srctype']            = 3;
    require("reservation_save.php");
    //LogDebug($_);
}
function Output(&$obj)
{
    echo json_encode($obj);
}
Input();
?>