<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 订单中餐品信息发生改变通知
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_order.php");
require_once("mgo_menu.php");
require_once("mgo_shop.php");
header('Content-Type:text/html;charset=utf-8');
function GetShop()
{
    $_       = &$_REQUEST;
    $shop_id = $_['shop_id'];
    $shop    = new \DaoMongodb\Shop;
    if(!$shop_id)
    {
     $shop_list =  $shop->GetShopList();
     foreach ($shop_list as $v)
     {
         if($v->is_change_urge)
         {
             GetOrderCheck($v->shop_id);
         }

     }

    }else{
        $shop_info = $shop->GetShopById($shop_id);
        if($shop_info->is_change_urge)
        {
            GetOrderCheck($shop_id);
        }

    }
}

function GetOrderCheck($shop_id)
{

    if(!$shop_id)
    {
        LogErr('no shop[{'.$shop_id.'}]');
        return errcode::SHOP_NOT_WEIXIN;
    }

    $food      = new \DaoMongodb\MenuInfo;
    $order     = new \DaoMongodb\Order;
    //获取店铺所有的菜品
    $order_status_list = [NewOrderStatus::GUAZ,NewOrderStatus::NOPAY,NewOrderStatus::PAY];
    $order_list = $order->GetOrderAllList(
        [
            'shop_id'           => $shop_id,
            'order_status_list' => $order_status_list,
        ]
    );

    foreach ($order_list as &$o)
    {
       foreach ($o->food_list as $v)
       {
           $food_info = $food->GetFoodinfoById($v->food_id);
           if(time()- $o->order_time >= $food_info->overtime){ //下单时间超过当前时间就发送推送
               $is_urge = 1;
               $ret     = $order->OrderInfoUrge($o->order_id,$is_urge);
               if(0 != $ret)
               {
                   LogErr("Save ok");
                   return errcode::SYS_BUSY;
               }
               $order_info   = $order->GetOrderById($o->order_id);
               $ret_json     = PageUtil::NotifyOrderUrge($order_info);
               $ret_json_obj = json_decode($ret_json);
               if(0 != $ret_json_obj->ret)
               {
                   LogErr("send err");
                   echo '<span style="color:firebrick">'.'店铺id为'.$shop_id.'催单推送发送失败'.'</span><br/>';
                   //return errcode::SYS_BUSY;
               }else{
                   echo '<span style="color:red">'.'店铺id为'.$shop_id.'催单推送已发送'.'</span><br/>';
               }

           }else{
               echo '店铺id为'.$shop_id.'订单未有改动'.'<br/>';
           }
       }
    }

}
GetShop();
//GetFoodCheck();
?><?php /******************************以下为html代码******************************/?>

