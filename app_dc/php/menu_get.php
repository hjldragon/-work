<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取餐品信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_menu.php");
require_once("mgo_category.php");
require_once("mgo_praise.php");
require_once("mgo_evaluation.php");
require_once("mgo_stat_food_byday.php");

//Permission::PageCheck($_['srctype']);
// 取餐品日销量
function GetTodayFoodSoldNum($food_id)
{
    $mgo_stat = new \DaoMongodb\StatFood;
    $today = date("Ymd");
    $info = $mgo_stat->GetFoodStatByDay($food_id, $today);
    //LogDebug($info);
    return $info->sold_num?:0;
}
// 取餐品周销量
function GetWeekFoodSoldNum($food_id)
{
    $mgo_stat = new \DaoMongodb\StatFood;
    $today = date("Ymd");
    $start = date("Ymd",strtotime("$today -1 Sunday"));
    $info = $mgo_stat->GetFoodStatByTime($food_id, $start, $today);
    return $info['all_sold_num']?:0;
}
// 取餐品月销量
function GetMonFoodSoldNum($food_id)
{
    $mgo_stat = new \DaoMongodb\StatFood;
    $today = date("Ymd",time());
    $start = date("Ym01",strtotime($today));
    $info = $mgo_stat->GetFoodStatByTime($food_id, $start, $today);
    return $info['all_sold_num']?:0;
}

function GetFoodInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $food_id = (string)$_['food_id'];

    $mgo = new \DaoMongodb\MenuInfo;
    $info = $mgo->GetFoodinfoById($food_id);
    if($info->food_id){
        $cateinfo = \Cache\Category::Get($info->category_id);
        $data = array();
        array_push($data, $cateinfo);
        GetCategory($data,$cateinfo->parent_id);
        $info->category = $data;
        $using = $info->food_price->using;
        $price = array();
        if($using & PriceType::ORIGINAL){
            array_push($price, PriceType::ORIGINAL);
        }
        if($using & PriceType::DISCOUNT){
            array_push($price, PriceType::DISCOUNT);
        }
        if($using & PriceType::VIP){
            array_push($price, PriceType::VIP);
        }
        if($using & PriceType::FESTIVAL){
            array_push($price, PriceType::FESTIVAL);
        }
        //LogDebug($price);
        $info->food_price->using = $price;
    }else{
        $info = array();
    }
    //$info->food_sold_num_day = GetTodayFoodSoldNum($food_id);<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<销量暂没做
    $resp = (object)array(
        'info' => $info
    );
   // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//递归查找父级品类
function GetCategory(&$data,$parent_id){
    $info = \Cache\Category::Get($parent_id);
    if($info){
        array_unshift($data, $info);
    }
    if($info->parent_id){
        GetCategory($data,$info->parent_id);
    }
}
//递归查找子级品类id
function getTree(&$cate_list,$parent_id)
{
    $mgo = new \DaoMongodb\Category;
    $info = $mgo->GetByParentList($parent_id);
    if(!$info){
      array_push($cate_list,$parent_id);
      return;
    }
    foreach ($info as $key => $value) {
        getTree($cate_list,$value->category_id);
    }

}

//获取app端所有点餐菜品
function GetAppFoodList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id   = (string)$_['shop_id'];
    $food_name = $_['food_name'];
    if(!$shop_id)
    {
      LogErr('no shop[{'.$shop_id.'}]');
      return errcode::SHOP_NOT_WEIXIN;
    }
    $shop_info = \Cache\Shop::Get($shop_id);
    $food      = new \DaoMongodb\MenuInfo;
    //获取店铺所有的菜品
    $food_list = $food->GetAppFoodList($shop_id,['food_name'=>$food_name]);
    // 判断店铺是否支持打包
    $shop_pack = 0;
    if(in_array(SALEWAY::PACK, $shop_info->sale_way))
    {
        $shop_pack = 1;
    }
    $food_all_list = [];
    foreach ($food_list as &$food_value)
    {
        //判断菜品是否设置了出售时间
        //找出餐品的出售时间
        if($food_value->sale_off_way == SaleFoodSetTime::SETTIME || $food_value->sale_off_way == SaleFoodSetTime::SETWEEK)
        {
            $b                = 0;
            $time_range_stamp = isset($food_value->food_sale_time) ? $food_value->food_sale_time : '';
            $time_range_week  = isset($food_value->food_sale_week) ? $food_value->food_sale_week : '';
            //菜品时间戳判断
            if($time_range_stamp != '' || $time_range_week != '')
                {
                    if($time_range_stamp != '')
                    {
                        foreach ($time_range_stamp as $food_time)
                        {
                            $start_time = $food_time->start_time;
                            $end_time   = $food_time->end_time;

                            if($start_time < time() && time() < $end_time)
                            {
                                $b++;
                            }
                        }
                    }
                    //菜品时间周判断
                    if($time_range_week != ''){
                        if(in_array(date('w'), $time_range_week))//国际判断周是用0-6,0表示周日
                        {
                            $b++;
                        }
                    }
                    if($b == 0)
                    {
                        continue;
                    }
                }
            }
            $accessory = \Cache\Food::Get($food_value->accessory);
            $accessory_price = (float)($accessory->food_price*$food_value->accessory_num);

            $category = \Cache\Category::Get($food_value->category_id);
            $food_value->food_num_mon    = GetMonFoodSoldNum($food_value->food_id);
            $food_value->category_name   = $category->category_name;
            $food_all_list[]             = $food_value;
            $food_value->accessory_price = $accessory_price;
            $food_value->day_sell_num    = GetTodayFoodSoldNum($food_value->food_id);
            if($food_value->stock_num_day == 0)
            {
                $food_value->stock_num_day = 99999;
            }

    }

    if($shop_info->menu_sort == 1)
    {
        usort($food_all_list, function($a, $b){
            return ($b->food_num_mon - $a->food_num_mon);
        });
    }else{
        usort($food_all_list, function($a, $b){
            return ($b->lastmodtime - $a->lastmodtime);
        });
    }
    $resp = (object)array(
        'list'      => $food_all_list,
        'shop_pack' => $shop_pack
    );
    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_["foodinfo"]))
{
    $ret = GetFoodInfo($resp);
} elseif(isset($_["app_food_list"]))
{
    $ret = GetAppFoodList($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret' => $ret,
    'data' => $resp
);

if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}
?><?php /******************************以下为html代码******************************/?>

