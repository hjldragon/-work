<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{
    $_ = &$GLOBALS["_"];
    $_['auto_order']         = Shopinfo::$save_auto_order[$_['auto_order']];
    $_['custom_screen']      = Shopinfo::$save_custom_screen[$_['custom_screen']];
    $_['menu_sort']          = Shopinfo::$sort[$_['sort']];
    $_['sync_base_settings'] = true;
    $_['srctype']            = 3;
    require("shopinfo_save.php");
    //LogDebug($_);
}
function Output(&$obj)
{
    echo json_encode($obj);
}
Input();

?>