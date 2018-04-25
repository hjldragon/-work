<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{
    $_ = &$GLOBALS["_"];
    $_['get_seat_list']       = true;
    $_['srctype']             = 3;
    require("seat_get.php");
    //LogDebug($_);
}
function Output(&$obj)
{
    $seat_list = $obj->data->seatlist;
    $tables    = [];
    foreach ($seat_list as $seat)
    {
        $table['table_name'] = $seat['seat_name'];
        $table['area']       = $seat['seat_region'];
        $table['type']       = $seat['seat_type'];
        $table['seat']       = $seat['seat_size'];
        array_push($tables, $table);
    }
    $a         = (object)['tables' => $tables];
    $obj->data = $a;
    echo json_encode($obj);
}
Input();

?>