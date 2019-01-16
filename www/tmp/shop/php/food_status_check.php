<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 餐品信息发生改变通知
 */
require_once("current_dir_env.php");
require_once("/www/shop.sailing.com/php/page_util.php");
require_once("const.php");
require_once("cache.php");
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
         GetFoodCheck($v->shop_id);
     }

    }else{
        GetFoodCheck($shop_id);
    }
}

function GetFoodCheck($shop_id)
{

    if(!$shop_id)
    {
        LogErr('no shop[{'.$shop_id.'}]');
        return errcode::SHOP_NOT_WEIXIN;
    }

    $food      = new \DaoMongodb\MenuInfo;
    //获取店铺所有的菜品
    $food_list = $food->GetFoodAllList($shop_id);
    $send_news = 0;

    foreach ($food_list as &$food_value)
    {
        //判断菜品是否设置了出售时间
        //找出餐品的出售时间
        $b = 0;
        if($food_value->sale_off_way == SaleFoodSetTime::SETTIME) {
            $time_range_stamp = isset($food_value->food_sale_time) ? $food_value->food_sale_time : '';
            //菜品时间戳判断
                    if ($time_range_stamp != '') {
                        foreach ($time_range_stamp as $food_time) {
                            $start_time = $food_time->start_time;
                            $end_time   = $food_time->end_time;
                            if ($start_time < time() && time() < $end_time) {
                                       $b = 1;
                            }
                        }
                    }
            if($b)
            {
                if($food_value->type != 2 && $food_value->sale_off != SALEOFF::ON)
                {
                    $id = $food_value->food_id;
                    $sale_off = SALEOFF::ON;
                    //到了指定上下假时间的时候自动变为上架状态
                    $ret = $food->SetFoodSaleOn($id, $sale_off);
                    if (0 != $ret) {
                        LogErr("when time come, change food sale status err");
                        return errcode::SYS_ERR;
                    }
                    $send_news = 1;
                }

            }else {
                if($food_value->type != 2 && $food_value->sale_off != SALEOFF::OFF)
                {
                    $id = $food_value->food_id;
                    $sale_off = SALEOFF::OFF;
                    //到了指定上下假时间的时候自动变为下架状态
                    $ret = $food->SetFoodSaleOn($id, $sale_off);
                    if (0 != $ret) {
                        LogErr("when time out, change food sale status err");
                        return errcode::SYS_ERR;
                    }
                    $send_news = 2;
                }

            }
        }

        if($food_value->sale_off_way == SaleFoodSetTime::SETWEEK) {
            $time_range_week  = isset($food_value->food_sale_week) ? $food_value->food_sale_week : '';
                //菜品时间周判断
                if ($time_range_week != '') {
                    if (in_array(date('w'), $time_range_week))//国际判断周是用0-6,0表示周日
                    {
                        $id = $food_value->food_id;
                        $sale_off = SALEOFF::ON;
                        $ret = $food->SetFoodSaleOn($id, $sale_off);
                        if (0 != $ret) {
                            LogErr("when week change food sale Save err");
                            return errcode::SYS_ERR;
                        }
                        $send_news = 3;
                    }
                }
        }
    }
    //LogDebug($send_news);
    if($send_news)
    {
        //LogDebug($shop_id);
        //LogDebug($id);
        $ret_json =  PageUtil::NotifyFoodChange($shop_id, $id);
        $ret_json_obj = json_decode($ret_json);
        //LogDebug($ret_json_obj->ret);
        if(0 != $ret_json_obj->ret)
        {
            LogErr("send err");
            echo '<span style="color:firebrick">'.'店铺id为'.$shop_id.'推送发送失败'.'</span><br/>';
            //return errcode::SYS_BUSY;
        }else{
            echo '<span style="color:red">'.'店铺id为'.$shop_id.'推送已发送'.'</span><br/>';
        }
    }else{
        echo '店铺id为'.$shop_id.'餐品未有改动'.'<br/>';
    }

}
GetShop();
//GetFoodCheck();
?><?php /******************************以下为html代码******************************/?>

