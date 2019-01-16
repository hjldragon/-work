<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 餐品信息发生改变通知
 */
require_once("current_dir_env.php");
require_once("page_util.php");
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
        if($food_value->sale_off_way == SaleFoodSetTime::SETTIME || $food_value->sale_off_way == SaleFoodSetTime::SETWEEK) {
            $time_range_stamp = isset($food_value->food_sale_time) ? $food_value->food_sale_time : '';
            $time_range_week  = isset($food_value->food_sale_week) ? $food_value->food_sale_week : '';
            //菜品时间戳判断
            if ($time_range_stamp != '' || $time_range_week != '') {
                if ($food_value->sale_off == 1) {
                    //获取菜品id
                    $id = $food_value->food_id;
                    if ($time_range_stamp != '') {
                        foreach ($time_range_stamp as $food_time) {
                            $start_time = $food_time->start_time;
                            $end_time   = $food_time->end_time;


                            if ($start_time < time() && time() < $end_time) {

                                //到了指定上下假时间的时候自动变为上架状态
                                $ret = $food->SetFoodSaleOn($id);
                                if (0 != $ret) {
                                    LogErr("when time change food sale Save err");
                                    return errcode::SYS_ERR;
                                }
                                $send_news = 1;
                            }

                        }
                    }

                //菜品时间周判断
                if ($time_range_week != '') {
                    if (in_array(date('w'), $time_range_week))//国际判断周是用0-6,0表示周日
                    {
                        $ret = $food->SetFoodSaleOn($id);
                        if (0 != $ret) {
                            LogErr("when week change food sale Save err");
                            return errcode::SYS_ERR;
                        }
                        $send_news = 2;
                    }
                }
            }
        }
            }

    }
    LogDebug($send_news);
    if($send_news)
    {
        LogDebug($shop_id);
        LogDebug($id);
        $ret_json =  PageUtil::NotifyFoodChange($shop_id, $id);
        $ret_json_obj = json_decode($ret_json);
        LogDebug($ret_json_obj->ret);
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

