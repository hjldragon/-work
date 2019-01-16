<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{
    $_                           = &$GLOBALS["_"];
    $_['reservation_id']         = $_['id'];
    $_['reson']                  = $_['cancel_remark'];
    $_['reservation_status']     = Reserve::$status[$_['state']];
    $_['save_reservation_state'] = true;
    $_['srctype']                = 3;
    //LogDebug($_);
    require("reservation_save.php");
}
function Output(&$obj)
{
    echo json_encode($obj);
}
Input();
?>