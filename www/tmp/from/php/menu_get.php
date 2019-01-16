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
require_once("mgo_share.php");
require_once("mgo_evaluation.php");
require_once("mgo_stat_food_byday.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
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
// 取餐品总销量
function GetAllFoodSoldNum($food_id)
{
    $mgo_stat = new \DaoMongodb\StatFood;
    $info = $mgo_stat->GetFoodStatByAll($food_id);
    return $info['all_sold_num']?:0;
}

function GetFoodInfo(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $food_id = (string)$_['food_id'];
    $preview = $_['preview'];
    if($preview)
    {
        ShopPermissionCheck::PageCheck(ShopPermissionCode::PREVIEW_MENU);
    }



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

function GetFoodList(&$resp)
{

    $_ = $GLOBALS["_"];
    //LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $category_id = $_['category_id'];
    $food_name   = $_['food_name'];
    $sale_off    = $_['sale_off'];
    $is_draft    = $_['is_draft'];
    $type        = $_['type'];
    $page_size   = $_['page_size'];
    $page_no     = $_['page_no'];
    $sortby      = $_['sortby']; //(排序1:日销量,2:周销量,3:月销量,4:余量,5:分享,6:收藏,7评论)
    $sort        = $_['sort'];   //(1:正序,-1:倒序)
    LogDebug($_);
    if($is_draft)
    {
        ShopPermissionCheck::PageCheck(ShopPermissionCode::DRAFT_LIST);
    }else{
        ShopPermissionCheck::PageCheck(ShopPermissionCode::SEE_MENU);
    }

    if(!$page_size)
    {
       $page_size = 10000;//如果没有传默认10000条
    }
    if(!$page_no)
    {
       $page_no = 1; //第一页开始
    }
    $shop_id = $_['shop_id'];
    if(!$shop_id)
    {
        $shop_id = \Cache\Login::GetShopId();
    }
    $shop      = new \DaoMongodb\Shop;
    $shop_info = $shop->GetShopById($shop_id);
    if(!$shop_info->shop_id)
    {
        LogErr("no shop");
        return errcode::SHOP_NOT_WEIXIN;
    }
    //判断店铺是否可以进行打包
    if(in_array(SALEWAY::SINCE, $shop_info->sale_way))
    {
        $shop_pack = 1; //可以进行打包
    }else{
        $shop_pack = 0;
    }
    $pad_sort  = $shop_info->menu_sort;
    LogDebug("shop_id:[$shop_id]");
    if($category_id)
    {
        $cate_list = array();
        $cateinfo = \Cache\Category::Get($category_id);
        getTree($cate_list,$cateinfo->category_id);
        //数组去重
        $cate_id_list = array_unique($cate_list);
        if(!$cate_id_list){
            $cate_id_list = array('0');
        }
    }
    $cond = [
        'food_name'    => $food_name,
        'cate_id_list' => $cate_id_list,
        'sale_off'     => $sale_off,
        'is_draft'     => $is_draft,
        'type'         => $type
    ];
    //LogDebug($cond);
    $mgo = new \DaoMongodb\MenuInfo;
    $total = 0;
    $menu_sort = [];
    if($pad_sort == 2)
    {
        $menu_sort['entry_time'] = -1;
    }
    $list = $mgo->GetFoodList($shop_id, $cond, $menu_sort, $total);
    //LogDebug($list);
    $eva_mgo = new \DaoMongodb\Evaluation;
    $praise  = new \DaoMongodb\Praise;
    $share   = new \DaoMongodb\Share;
    foreach($list as $i => &$item)
    {   
        $count = 0;
        $eva = $eva_mgo->GetFoodIdByList($item->food_id, 10, 1, $count);
        $item->category_name = \Cache\Category::Get($item->category_id)->category_name;
        $item->food_num_day  = GetTodayFoodSoldNum($item->food_id);
        $item->food_num_week = GetWeekFoodSoldNum($item->food_id);
        $item->food_num_mon  = GetMonFoodSoldNum($item->food_id);
        //$item->praise_num    = $praise->GetFoodAllCount($item->food_id);//点赞数
        $item->collect_num   = $praise->GetFoodAllCount($item->food_id, 2);//收藏数
        $item->comment_num   = $count;//评论数
        $item->share_num     = $share->GetFoodShareCount($item->food_id);//分享数
        if($item->stock_num_day > 0)
        {
            $stock_num_day = (int)$item->stock_num_day - (int)$item->food_num_day; // 余量
            if ($stock_num_day < 0) 
            {
                $item->stock_num_day = 0;
            }else
            {
                $item->stock_num_day = $stock_num_day;
            }
        }
        else
        {
            $item->stock_num_day = 99999; // 库存不限量
        }

        //只取出普通价格
        $p_all = [];
        foreach ($item->food_price->price as $price)
        {
            if($price->is_use == 1 || $item->food_price->type == 1)
            {
                $p= (object)[];
                $p->spec_type      = $price->spec_type;
                $p->original_price = $price->original_price;
                array_push($p_all,$p);
            }
        }
        $item->food_price->price = $p_all;
    }
    
    if(3 == $sortby || $pad_sort == 1)//月销量排序
    {
        //$sortby = "food_num_mon";
        if(1 == $sort)
        {
            usort($list, function($a, $b){
                return ($a->food_num_mon - $b->food_num_mon);
            });
        }
        else
        {
            usort($list, function($a, $b){
                return ($b->food_num_mon - $a->food_num_mon);
            });
        }
    }
    if(1 == $sortby)//日销量排序
    {
        //$sortby = "food_num_day";
        if(1 == $sort)
        {
            usort($list, function($a, $b){
                return ($a->food_num_day - $b->food_num_day);
            });
        }
        else
        {
            usort($list, function($a, $b){
                return ($b->food_num_day - $a->food_num_day);
            });
        }
    }
    if(2 == $sortby)//周销量排序
    {
        //$sortby = "food_num_week";
        if(1 == $sort)
        {
            usort($list, function($a, $b){
                return ($a->food_num_week - $b->food_num_week);
            });
        }
        else
        {
            usort($list, function($a, $b){
                return ($b->food_num_week - $a->food_num_week);
            });
        }
    }
    
    if(4 == $sortby)//余量排序
    {
        //$sortby = "stock_num_day";
        if(1 == $sort)
        {
            usort($list, function($a, $b){
                return ($a->stock_num_day - $b->stock_num_day);
            });
        }
        else
        {
            usort($list, function($a, $b){
                return ($b->stock_num_day - $a->stock_num_day);
            });
        }
    }
    if(5 == $sortby)//分享排序
    {
        //$sortby = "share_num";
        if(1 == $sort)
        {
            usort($list, function($a, $b){
                return ($a->share_num - $b->share_num);
            });
        }
        else
        {
            usort($list, function($a, $b){
                return ($b->share_num - $a->share_num);
            });
        }
    }
    if(6 == $sortby)//收藏排序
    {
        //$sortby = "collect_num";
        if(1 == $sort)
        {
            usort($list, function($a, $b){
                return ($a->collect_num - $b->collect_num);
            });
        }
        else
        {
            usort($list, function($a, $b){
                return ($b->collect_num - $a->collect_num);
            });
        }
    }
    if(7 == $sortby)//评论排序
    {
        //$sortby = "comment_num";
        if(1 == $sort)
        {
            usort($list, function($a, $b){
                return ($a->comment_num - $b->comment_num);
            });
        }
        else
        {
            usort($list, function($a, $b){
                return ($b->comment_num - $a->comment_num);
            });
        }
    }
    // if(isset($list[0]->$sortby))
    // {
    //     usort($list, function($a, $b){
    //         if(1 == $sort)
    //         {  
    //            return ($a->$sortby - $b->$sortby);
    //         }
    //         else
    //         {
    //             return ($b->$sortby - $a->$sortby);
    //         }
    //     });
    // }
    //分页
   
    $list = array_slice($list, ($page_no-1)*$page_size, $page_size);
    
    $resp = (object)array(
        'list'      => $list,
        'total'     => $total,
        'shop_pack' => $shop_pack
    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//查找餐盒品类下的配件
function GetAccessoryList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = \Cache\Login::GetShopId();

    $catemgo = new \DaoMongodb\Category;
    $category_name = "餐盒";
    $cate_info = $catemgo->GetNameById($category_name,$shop_id);
    if(!$cate_info->category_id){
        LogErr("category err");
        return errcode::PARAM_ERR;
    }
    $mgo = new \DaoMongodb\MenuInfo;
    $cond['cate_id_list'] = [$cate_info->category_id];
    $info = $mgo->GetFoodList($shop_id, $cond);
    $resp = (object)array(
        'list' => $info
    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//获取pad端所有菜品
function  GetPadFoodList(&$resp)
{
    ShopPermissionCheck::PageCheck(ShopPermissionCode::MENU_LIST);
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = (string)$_['shop_id'];
    if(!$shop_id)
    {
      LogErr('no shop[{'.$shop_id.'}]');
      return errcode::SHOP_NOT_WEIXIN;
    }
    $shop_info = \Cache\Shop::Get($shop_id);
    $food      = new \DaoMongodb\MenuInfo;
    //获取店铺所有的菜品
    $food_list = $food->GetPadFoodList($shop_id);
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
            $category = \Cache\Category::Get($food_value->category_id);
            $food_value->food_num_mon  = GetAllFoodSoldNum($food_value->food_id);
            $food_value->category_name = $category->category_name;
            $food_all_list[]           = $food_value;
            $food_value->day_sell_num  = GetTodayFoodSoldNum($food_value->food_id);

    }
//    if($shop_info->menu_sort == 1)
//    {
//        usort($food_all_list, function($a, $b){
//            return ($b->food_num_mon - $a->food_num_mon);
//        });
//    }else{
//        usort($food_all_list, function($a, $b){
//            return ($b->lastmodtime - $a->lastmodtime);
//        });
//    }
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
}
elseif(isset($_["foodlist"]))
{
    $ret = GetFoodList($resp);
}
elseif(isset($_["accessory"]))
{
    $ret = GetAccessoryList($resp);
}elseif(isset($_["pad_food_list"]))
{
    $ret = GetPadFoodList($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
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

