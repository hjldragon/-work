<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取餐品信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_category.php");
require_once("mgo_menu.php");
require_once("mgo_category.php");

function GetFoodList(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id   = $_['shop_id'];
    $food_name = $_['food_name'];

    $mgo       = new \DaoMongodb\MenuInfo;
    $list      = $mgo->GetFoodAllList($shop_id,['food_name'=>$food_name]);
    $food_list = [];
    foreach ($list as $v)
    {
        $food['food_id']       = $v->food_id;
        $food['food_name']     = $v->food_name;
        $food['over_set_time'] = date('Y-m-d',$v->over_set_time);
        $food['overtime']      = $v->overtime;
        array_push($food_list,$food);
    }

    $resp = (object)array(
        'food_list' => $food_list
    );
    LogInfo("--ok--");
    return 0;
}
//获取档口添加的菜品数据列表
function GetAddMenuList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id     = (string)$_['shop_id'];

    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::SHOP_ID_NOT;
    }
    //LogDebug("shop_id:[$shop_id]");
    //获取店铺信息
    $shop_info                     = \Cache\Shop::Get($shop_id);
    //判断是否在营业时间
    $shop_time    = $shop_info->open_time;
    $now_time     = date('H:i:s',time());
    if($shop_time[0]<$now_time && $shop_time[1]>$now_time)
    {
        $shopinfo['is_time']   = 1;//在
    }elseif($shop_time[0] == null && $shop_time[1] == null){
        $shopinfo['is_time']   = 1;//不在
    }else{
        $shopinfo['is_time']   = 0;//不在
    }
    $food         = new \DaoMongodb\MenuInfo;
    //获取该店铺的营业时间点
    $open_times = $shop_info->opening_time;
    //来获取营业时间中的type值
    $num = '';
    $domain         = Cfg::instance()->GetMainDomain();
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
    //按分类输出
    //获取该店铺的所有的分类数据
    $category_info = \Cache\Category::GetCategoryList($shop_id);
    $category_list = [];
    $cate = getTree($category_info,"0");
    foreach ($cate as $cateone_value)
    {
        foreach ($cateone_value->list as $cate_two)
        {
            //如果是配件直接放
            if($cate_two->type == 2)
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
    $menuinfo = [];
    //营业时间的type和
    $lang     = count($num);
    //获取在经营时间类的分类
    foreach ($category_list as $i => &$category)
    {
        //分类的时间段
        $cate_time = $category->opening_time;
        //type不是2的判断时间
        if($category->type != CateType::TYPETWO)
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

        //获取所有时间内所有分类下的餐品数据
        $food_id_list  = $food->GetFoodinfoByCate($shop_id, $category->category_id);
        $food_list_all = [];
        if(!$food_id_list)
        {
            continue;
        }
        $food_list = [];
        foreach ($food_id_list as $food_value)
        {
            //判断菜品是否设置了出售时间
            //找出所有分类下餐品的出售时间
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
            $food_list['food_id']       = $food_value->food_id;
            $food_list['food_name']     = $food_value->food_name;
            $food_list['food_img']      = "http://kitchen.$domain/php/img_get.php?img=1&imgname=".$food_value->food_img_list[0];
            array_push($food_list_all,$food_list);
        }
        if(!$food_list_all)
        {
            LogDebug("food list empty, category_id:[{$category->category_id}]");
            continue;
        }
        array_push($menuinfo, [
            'category_name' => $category->category_name,
            'food_list'     => $food_list_all
        ]);
    }

    $resp = (object)array(
        'list'   => $menuinfo,
    );
    LogInfo("--ok--");
    return 0;
}

function getTree($data, $pId)
{
    $tree = '';
    foreach($data as $k => $v)
    {
        if($v->parent_id == $pId)
        {        //父亲找到儿子
            $v->list = getTree($data, $v->category_id);
            if(!$v->list){
                $v->list = array();
            }
            $tree[] = $v;
        }
    }
    return $tree;
}
function time2string($second){
//    $day = floor($second/(3600*24));
//    $second = $second%(3600*24);
//    $hour = floor($second/3600);
    $second = $second%3600;
    $minute = floor($second/60);
    $second = $second%60;
    // 不用管怎么实现的，能用就ok
    return /*$day.'天'.$hour.'小时'.*/$minute.':'.$second;
}
$ret = -1;
$resp = (object)array();
if(isset($_["get_over_food_list"]))
{
    $ret = GetFoodList($resp);
}elseif(isset($_["get_add_food_list"]))
{
    $ret =  GetAddMenuList($resp);
} else
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

