 <?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{
    $_ = &$GLOBALS["_"];
    $_['get_shop_info']   = true;
    $_['srctype']         = 3;
    require("shop_get.php");
    //LogDebug($_);
}
function Output(&$obj)
{
    //LogDebug($obj);
    $shop_info                  = $obj->data->shop_info;
    if(!$shop_info)
    {
        $obj->data = (object)array(
        );
    }else {
        $shop_info['erasure']       = Shopinfo::$erasure[$shop_info['erasure']];
        $shop_info['auto_order']    = Shopinfo::$auto_order[$shop_info['auto_order']];
        $shop_info['custom_screen'] = Shopinfo::$custom_screen[$shop_info['custom_screen']];
        if ($shop_info['menu_sort'] == null) {
            $shop_info['menu_sort'] = 0;
        }else{
            $shop_info['menu_sort'] = Shopinfo::$shop_sort[$shop_info['menu_sort']];
        }
        //LogDebug($shop_info['auto_order']);
        foreach ($shop_info['print_info'] as &$p) {
            foreach ($p['type'] as $k => &$v) {
                $v = Shopinfo::$type[$v];
            }
        }
        foreach ($shop_info['pay_type'] as &$b) {
            if (isset($b['bookkeeping'])) {
                $b['bookkeeping'] = Shopinfo::$bookkeeping[$b['bookkeeping']];
            }
        }
        $shop_info['phone']       = "0755-23060180";//<<<<<<<<<<<<<<客户电话
        $obj->data = $shop_info;
    }
    //LogDebug($obj);
    echo json_encode($obj);
}
Input();

?>