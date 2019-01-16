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
require_once("../../public.sailing.com/php/page_util.php");

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
//获取所有菜品
function GetSelfHelpFoodList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id       = (string)$_['shop_id'];
    //$category_id   = $_['category_id'];
    $sale_way_list = json_decode($_['sale_way_list']);
    if(!$shop_id)
    {
      LogErr('no shop[{'.$shop_id.'}]');
      return errcode::SHOP_NOT_WEIXIN;
    }
    if(!$sale_way_list)
    {
        $sale_way_list = [];
    }
    $food      = new \DaoMongodb\MenuInfo;
    $praise    = new \DaoMongodb\Praise;
    $shop_info = \Cache\Shop::Get($shop_id);
    //$all       = [];

    //获取该店铺的营业时间点
    $open_times = $shop_info->opening_time;
    //来获取营业时间中的type值
    $num = '';
    foreach ($open_times as $open_time)
    {
        $type  = $open_time->type;
        $froms = $open_time->from;
        $tos   = $open_time->to;
        $time  = time();
        $from  = ' ' . $froms->hh . ':' . $froms->mm . ':' . $froms->ss;
        $to    = ' ' . $tos->hh . ':' . $tos->mm . ':' . $tos->ss;
        if($tos->hh < $froms->hh)
        {
            $time1 = date('Y-m-d') . $from;
            $time2 = date('Y-m-d', strtotime('+1 day')) . $to;
        } else {
            $time1 = date('Y-m-d') . $from;
            $time2 = date('Y-m-d') . $to;
        }
        $time1 = strtotime($time1);
        $time2 = strtotime($time2);
        if($time1 < $time && $time < $time2){
            $num [] = $type;     //获取到所有时间段的type值
            //break;
        }
    }
    //获取三级分来并排序出来
    $food_all_list = [];
    $category_list = [];
    $category_info = \Cache\Category::GetCategoryList($shop_id);
    $cate          = getCateTree($category_info,"0");
    foreach ($cate as $cateone_value)
    {
        foreach ($cateone_value->list as $cate_two)
        {
            //如果是配件直接放
            if($cate_two->type == CateType::TYPETWO)
            {
                array_push($category_list,$cate_two);
            }else {
                foreach ($cate_two->list as $cate_three)
                {
                    array_push($category_list,$cate_three);
                }
            }
        }
    }
    $lang     = count($num);
    foreach ($category_list as $c)
     {
         //分类的时间段
         $cate_time = $c->opening_time;
         //type不是2的判断时间
         if($c->type != CateType::TYPETWO)
         {
             //判断是否是在这个时间段，不在就跳出
             if($lang > 0)
             {
                 $a = 0;
                 foreach ($num as $v)
                 {
                     if(in_array($v, $cate_time))
                     {
                         $a++;
                     }
                 }
                 if($a == 0){
                     continue;
                 }
             }
         }
         //var_dump($c->category_name);
         //var_dump($c->category_id);<<<<<<<<<<<<<如果要显示餐盒这类的配件把type条件加进去就行了。
         //获取店铺类所有分类下面菜品
         $food_list          = $food->GetSelfhelpFoodList($shop_id,[
             'sale_way_list' => $sale_way_list,
             'category_id'   => $c->category_id]);
         //var_dump($food_list);
         if(!$food_list)
         {
             continue;
         }
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

             //$category = \Cache\Category::Get($food_value->category_id);
             $food_value->food_num_mon  = GetMonFoodSoldNum($food_value->food_id);
             $food_value->category_name = $c->category_name;
             $food_value->day_sell_num  = GetTodayFoodSoldNum($food_value->food_id);
             $food_value->praise_num    = $praise->GetFoodAllCount($food_value->food_id);//点赞数
             $domain                    = Cfg::instance()->GetMainDomain();

             foreach ($food_value->food_img_list as &$f_img)
             {
                 $f_img  = "http://selfhelp.$domain/php/img_get.php?img=1&imgname=".$f_img;
             }
             $food_value->food_img      = $food_value->food_img_list[0];
             if($food_value->stock_num_day == 0)
             {
                 $food_value->inventory = 99999;
             }else{
                 $inventory = $food_value->stock_num_day-$food_value->day_sell_num;
                 if($inventory<0)
                 {
                     $food_value->inventory = 0;
                 }else{
                     $food_value->inventory = $inventory;
                 }
             }
             if(null != $food_value->accessory)
             {
                 $ac_price                    = \Cache\Food::Get($food_value->accessory);
                 $ay_price                    = $ac_price->food_price;
                 $food_value->accessory_price = $ay_price ;
             } else {
                 $food_value->accessory_price = 0;
             }
             if(null == $food_value->accessory_num)
             {
                 $food_value->accessory_num = 0;
             }
             $food_all_list[]         = $food_value;
             if($food_value->food_price->using == 5)
             {
                 $food_value->food_price->using = 1;
             }

         }

     }
//die;
    $resp = (object)array(
        'food_list' => $food_all_list,
    );
    LogInfo("--ok--");
    return 0;
}
//树形排序
function getCateTree($data, $pId)
{
    $tree = '';
    foreach($data as $k => $v)
    {
        if($v->parent_id == $pId)
        {        //父亲找到儿子
            $v->list = getCateTree($data, $v->category_id);
            if(!$v->list){
                $v->list = array();
            }
            $tree[] = $v;
        }
    }
    return $tree;
}
$ret = -1;
$resp = (object)array();
if(isset($_["foodinfo"]))
{
    $ret = GetFoodInfo($resp);
}
elseif(isset($_["accessory"]))
{
    $ret = GetAccessoryList($resp);
}elseif(isset($_["selfhelp_food_list"]))
{
    $ret = GetSelfHelpFoodList($resp);
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
//\Pub\PageUtil::HtmlOut($ret, $resp);
?><?php /******************************以下为html代码******************************/?>

