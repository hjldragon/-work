<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取餐品信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_stat_food_byday.php");
require_once("mgo_order.php");
require_once("mgo_user.php");
require_once("mgo_customer.php");
require_once("mgo_evaluation.php");
require_once("mgo_praise.php");

//Permission::PageCheck();
//$_=$_REQUEST;
//获取登陆客户信息
function GetCustomerInfo()
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id       = $_['shop_id'];
    $userid        = $_['userid'];
    if(!$userid)
    {
        LogErr("userid empty");
        return errcode::USER_NOT_ZC;
    }
    $custominfo    = [];
    $customer_info = \Cache\Customer::GetInfoByUseridShopid($userid, $shop_id);
    //根据user表获取登录用户信息
    $user_info = \Cache\User::Get($userid);
    //获取用户表的信息
    $custominfo['customer_name']     = $user_info->usernick;
    $custominfo['customer_portrait'] = $user_info->user_avater;
    $custominfo['customer_id']       = $customer_info->customer_id;
    $custominfo['phone']             = $user_info->phone;
    $custominfo['is_vip']            = $customer_info->is_vip;
    $custominfo['weixin_account']    = $customer_info->weixin_account;
    $custominfo['vip_level']         = $customer_info->vip_level;
    $custominfo['birthday']          = $user_info->birthday;
    $custominfo['sex']               = $user_info->sex;
    LogInfo("--ok--");
    return $custominfo;
}
// 取餐品日销量
function GetTodayFoodSoldNum($food_id)
{
    $mgo_stat = new \DaoMongodb\StatFood;
    $today = date("Ymd");
    $info = $mgo_stat->GetFoodStatByDay($food_id, $today);
    //LogDebug($info);
    return $info->sold_num?:0;
}
// 取餐品月销量
function GetMonFoodSoldNum($food_id)
{
    $mgo_stat = new \DaoMongodb\StatFood;
    $today = date("Ymd");
    $start = date("Ym01",strtotime($today));
    $info = $mgo_stat->GetFoodStatByTime($food_id, $start, $today);
    return $info['all_sold_num']?:0;
}
//获取轮播信息
function GetBroadcastList($shop_id)
{
    $mgo            = new \DaoMongodb\Broadcast();
    $broadcast_list = $mgo->GetBroadcastByShopId($shop_id);

    foreach ($broadcast_list as $broadcast)
    {
        $time_range_1 = $broadcast->time_range_1;
        $time_range_2 = $broadcast->time_range_2;
        $a = 0;//用于2个条件都符合是，不重复数据
        if ($time_range_1 != '')
        {
            foreach ($time_range_1 as $p1)
            {
                $t1 = explode(',', $p1);
                if ($t1[0] < time() && time() < $t1[1])
                {
                    $content[] = $broadcast->content;
                    $a         = 1;
                }
            }
        }
        if ($time_range_2 != '' && $a == 0)
        {
            if (in_array(date('w'), $time_range_2))
            {
                $content[] = $broadcast->content;
            }
        }
    }

    return $content;
}
//好评率
function GetRate($food_id)
{
    $mgo     = new \DaoMongodb\Evaluation;
    $all     = $mgo->GetFoodAllCount($food_id);
    $good_is = $mgo->GetFoodAllCount($food_id, 1);
    if($all > 0){
        $good  = round($good_is / $all, 2);
        return $good;
    }
    return 0;

}

function CategorySort(&$list){
    $cate1 = [];
    $cate2 = [];
    $cate3 = [];

    foreach ($list as $v)
    {
        if($v->type ==1)
        {
           $cate1[] = $v;
        }
        if($v->type ==3)
        {
            $cate2[] = $v;
        }
        if($v->type ==2)
        {
            $cate3[] = $v;
        }
    }
   $list = array_merge($cate1,$cate2,$cate3);

}
//树形排序
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
function GetCategoryInfo($category_id)
{
    $mgo  = new \DaoMongodb\Category;
    $info = $mgo->GetCategoryById($category_id);
    LogDebug($info);
    return $info;
}

function GetFoodInfo(&$resp)
{
    LogDebug($resp);
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $food_id     = (string)$_['food_id'];
    $customer_id = $_['customer_id'];
    $page_size   = $_['page_size'];
    $page_no     = $_['page_no'];

    $mgo  = new \DaoMongodb\MenuInfo;
    $info = $mgo->GetFoodinfoById($food_id);

    if($info->type != CateType::TYPETWO)
    {   
        $info->price_type = $info->food_price->type;
        $using = $info->food_price->using;
        $name  = [];
        if(($using & PriceType::FESTIVAL) != 0){// 节日价
            $name[] = 'festival_price';
        }
        if(($using & PriceType::VIP) != 0){     // 会员价
            $name[] = 'vip_price';
        }
        if(($using & PriceType::DISCOUNT) != 0){// 折扣价
            $name[] = 'discount_price';
        }
        if(($using & PriceType::ORIGINAL) != 0){// 普通价
            $name[] = 'original_price';
        }
        $price = [];
        foreach ($info->food_price->price as $value)
        {
            $p = [];
            if(0 == $value->spec_type || $value->is_use == 1)
            {
                $p['spec_type'] = $value->spec_type;
                foreach ($name as $v)
                {
                    $p[$v] = $value->$v;
                }
                array_push($price, $p);
            }
            
        }
        $info->food_price = $price;
    }

    // 所有评价
    $total = 0;
    $eva_mgo      = new \DaoMongodb\Evaluation;
    $customer_mgo = new \DaoMongodb\Customer;
    $user_mgo     = new \DaoMongodb\User;
    $praise_mgo   = new \DaoMongodb\Praise;
    $data = $eva_mgo->GetFoodIdByList($food_id, $page_size, $page_no, $total);
    foreach ($data as &$item) {
        $customer = $customer_mgo->QueryById($item->customer_id);
        $user = $user_mgo->QueryById($customer->userid);
        $item->customer_name = $user->usernick;
        $item->customer_portrait = $user->user_avater;
        $to_eva = $eva_mgo->GetEvaluationByToId($item->id);
        if($to_eva->id)
        {
            $item->to_content = $to_eva->content;
            $item->to_ctime   = $to_eva->ctime;
        }
        //是否点赞该餐品
        $pra = $praise_mgo->GetPraiseByCustomer($item->customer_id, $food_id, '', PraiseType::PRAISE);
        if($pra->customer_id){
            $item->is_praise = $pra->is_praise;
        }else{
            $item->is_praise = 0;
        }
    }
    $info->total      = $total;
    $info->evaluation = $data;
    // 月售
    $info->food_sold_num_mon = GetMonFoodSoldNum($food_id);
    // 好评率
    $info->good_rate = GetRate($food_id);
    if($customer_id)
    {
        //是否点赞
        $praise = $praise_mgo->GetPraiseByCustomer($customer_id, $food_id, '', PraiseType::PRAISE);
        if($praise->customer_id){
            $info->is_praise = $praise->is_praise;
        }else{
            $info->is_praise = 0;
        }
        //是否收藏
        $collect = $praise_mgo->GetPraiseByCustomer($customer_id, $food_id, '', PraiseType::COLLECT);
        if($collect->customer_id){
            $info->is_collect = $collect->is_praise;
        }else{
            $info->is_collect = 0;
        }
    }
    
    //餐品被点赞数
    $info->praise_num = $praise_mgo->GetFoodAllCount($food_id);
    $resp = (object)array(
        'info' => $info
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetMenuInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id     = (string)$_['shop_id'];
    $customer_id = $_['customer_id'];
    $custominfo  = GetCustomerInfo();
    if(!$custominfo['customer_id'])
    {   
        LogErr("[$customer_id] err");
        return errcode::USER_NOT_ZC;
    }
    if(!$shop_id)
    {
        $shop_id = \Cache\Login::GetShopId();
    }
    //LogDebug("shop_id:[$shop_id]");
    //获取店铺信息
    $shopinfo                      = [];
    $content_all                   = GetBroadcastList($shop_id);
    $shop_info                     = \Cache\Shop::Get($shop_id);
    $shopinfo['shop_id']           = $shop_info->shop_id;
    $shopinfo['shop_name']         = $shop_info->shop_name;
    $shopinfo['classify_name']     = $shop_info->classify_name;
    $shopinfo['telephone']         = $shop_info->telephone;
    $shopinfo['address']           = $shop_info->address;
    $shopinfo['open_time']         = $shop_info->open_time;
    $shopinfo['img_list']          = $shop_info->img_list;
    //$shopinfo['praise_num']        = $shop_info->praise_num;
    $shopinfo['good_rate']         = $shop_info->good_rate;
    $shopinfo['is_invoice_vat']    = $shop_info->is_invoice_vat;
    $shopinfo['broadcast_content'] = $content_all;
    $shopinfo['meal_after']        = $shop_info->meal_after; // 是否支持餐前餐后付款
    $shopinfo['suspend']           = $shop_info->suspend;    // 店铺是否被停用
    //判断是否在营业时间
    $shop_time    = $shop_info->open_time;
    $now_time     = date('H:i:s',time());
    if($shop_time[0]<$now_time && $shop_time[1]>$now_time)
    {
        $shopinfo['is_time']   = 1;//在
    }else{
        $shopinfo['is_time']   = 0;//不在
    }
    $food         = new \DaoMongodb\MenuInfo;
    $mgo_el       = new \DaoMongodb\Evaluation;
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
    //按分类输出
    //获取该店铺的所有的分类数据
    $category_info = \Cache\Category::GetCategoryList($shop_id);
    //$mgo           = new \DaoMongodb\Category;
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
//        $info = $mgo->GetByParentList($cateone_value->category_id);
//
//        if(!$info)
//        {
//            array_push($category_list, $cateone_value);
//        }
    }
    //排序
    //CategorySort($category_list);
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
        $praise_mgo = new \DaoMongodb\Praise;
        $food_list = [];
        // 判断店铺是否支持打包
        $shop_pake = 0;
        if(in_array(SALEWAY::PACK, $shop_info->sale_way))
        {
            $shop_pake = 1;
        }
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
            
            // 判断菜品是否能打包
            $is_pake = 0;
            if(in_array(SALEWAY::PACK, $food_value->sale_way) && $shop_pake == 1)
            {
                $is_pake = 1;
            }
            $page_sie    = (int)$_['page_size']; //条数
            $page_no     = (int)$_['page_no'];   //页数
            $evaluation  = $mgo_el->GetFoodIdByList($food_value->food_id, $page_sie, $page_no);
            //$evaluation          = \Cache\Evaluation::GetEvaluationList($food_value->food_id, $page_sie, $page_no); //根据商品id获取评价列表
            $evaluation_list     = [];
            $evaluation_list_all = [];
            //列出评价信息的数据
            foreach ($evaluation as $e_list)
            {
                $customer_list                      = \Cache\Customer::Get($e_list->customer_id);
                $customer_info                      = [];
                $customer_info['customer_id']       = $customer_list->customer_id;
                $user_info                          = \Cache\User::Get($customer_list->userid);
                $customer_info['customer_name']     = $user_info->usernick;
                $customer_info['customer_portrait'] = $user_info->user_avater;

                $evaluation_list['customer_info'] = $customer_info;
                if($customer_info['customer_id'] == null)//<<<<<因为数据不完整加的临时过滤条件
                {
                    unset($evaluation_list['customer_info']);
                }
                $evaluation_list['content'] = $e_list->content;
                $evaluation_list['ctime']   = $e_list->ctime;
                //好评率
                //$evaluation_list['good_rate'] = GetRate($food_id);<<<<<<<<<放在外面了
                array_push($evaluation_list_all, $evaluation_list);
            }
            //用于判断使用的是哪种价格显示
            $using = $food_value->food_price->using;
            $name  = [];
            if(($using & PriceType::FESTIVAL) != 0){// 节日价
                $name[] = 'festival_price';
            }
            if(($using & PriceType::VIP) != 0){     // 会员价
                $name[] = 'vip_price';
            }
            if(($using & PriceType::DISCOUNT) != 0){// 折扣价
                $name[] = 'discount_price';
            }
            if(($using & PriceType::ORIGINAL) != 0){// 普通价
                $name[] = 'original_price';
            }
            //菜品规格 用于含有规格的菜品使用的口味及价格显示
            $specs = [];
            if($food_value->food_attach_list)
            {
                $spec_one = [];
                foreach ($food_value->food_attach_list as $spec_value)
                {
                    $spec_one['type']  = 1;
                    $spec_one['title'] = $spec_value->title;
                    $spec_one['list']  = $spec_value->spc_value;
                    array_push($specs, $spec_one);
                }

            }
            //判断店铺的价格使用type值是否是规格的价格，type=2
            if($food_value->food_price->type == FoodPriceType::SPEC)
            {
                $spec_two['type']  = $food_value->food_price->type;
                $spec_two['title'] = "份量";
                $s              = [];
                $spec_four      = [];
                foreach ($food_value->food_price->price as $price_value)
                {
                    if($price_value->is_use == IsPecs::YES)
                    {
                        $spec_four['spec_type'] = $price_value->spec_type;
                        foreach ($name as $value)
                        {
                            $spec_four[$value] = $price_value->$value;
                        }
                        array_push($s, $spec_four);
                    }
                    $spec_two['list'] = $s;
                }
                array_push($specs, $spec_two);
            }
            //用于没有规格使用的菜品价格
            $price = [];
            if($food_value->food_price->type == FoodPriceType::THIS)
            {
                $price['type'] = $food_value->food_price->type;
                foreach ($food_value->food_price->price as $price_dif)
                {
                    foreach ($name as $value)
                    {
                        $price[$value] = $price_dif->$value;
                    }
                }

            }else {
                $price['type'] = $food_value->food_price->type;
            }
            //如果类型是配件就直接取价格
            if($food_value->type == CateType::TYPETWO)
            {
                $price = $food_value->food_price;
                $is_pake = 0;   // 配件不能打包
            }
            if($food_value->stock_num_day > 0)
            {
                $food_num_day           = GetTodayFoodSoldNum($food_value->food_id);
                $food_list['stock_num'] = (int)$food_value->stock_num_day - (int)$food_num_day;
            }else{
                unset($food_list['stock_num']);       //不限量
            }
            $food_list['food_id']       = $food_value->food_id;
            $food_list['food_name']     = $food_value->food_name;
            $food_list['food_num_mon']  = GetMonFoodSoldNum($food_value->food_id);
            $food_list['praise_num']    = $praise_mgo->GetFoodAllCount($food_value->food_id);
            $food_list['food_intro']    = $food_value->food_intro;
            $food_list['food_img_list'] = $food_value->food_img_list;
            $food_list['food_unit']     = $food_value->food_unit;
            $food_list['food_price']    = $price;
            $food_list['spec']          = $specs;
            $food_list['is_pake']       = $is_pake;
            $food_list['sale_num']      = $food_value->sale_num;
            //通过id来获取配件价格
            if(null != $food_value->accessory)
            {
                $ac_price               = \Cache\Food::Get($food_value->accessory);
                $ay_price               = $ac_price->food_price;

                $food_list['accessory'] = $ay_price ;
            } else {
                $food_list['accessory'] = null;
            }
            $food_list['accessory_num'] = $food_value->accessory_num;
            $food_list['composition']   = $food_value->composition;
            $food_list['feature']       = $food_value->feature;
            $food_list['good_rate']     = GetRate($food_value->food_id);
            //餐品的好评率
            $food_list['evaluation'] = $evaluation_list_all;
            array_push($food_list_all, $food_list);
        }
        if(!$food_list_all)
        {
            LogDebug("food list empty, category_id:[{$category->category_id}]");
            continue;
        }
        array_push($menuinfo, [
            'category_id'   => $category->category_id,
            'category_name' => $category->category_name,
            'type'          => $category->type,
            'food_list'     => $food_list_all
        ]);
    }
    $mgo_order = new \DaoMongodb\Order;
    $orderinfo = $mgo_order->GetLastOrder($customer_id);
    $bought = [];
    if($orderinfo)
    {
        $bought['order_id'] = $orderinfo->order_id;
        $bought['ctime']    = $orderinfo->pay_time;
        $bought['price']    = $orderinfo->order_fee;
        $food_name = [];
        foreach ($orderinfo->food_list as  $item) {
            $name = $item->food_name;
            $attach = [];
            if($item->food_attach_list)
            {
                foreach ($item->food_attach_list as $spec)
                {
                    array_push($attach, $spec->spec_value);
                }
            }
            if($attach)
            {   
                $attach = array_filter($attach);
                $attach = implode(',', $attach);
                $name = $name.'('.$attach.')';
            }
            array_push($food_name, $name);
        }
        $bought['food_name'] = $food_name;
    }
    $seat_info = \Cache\Seat::Get($_['seat_id']);
    if(!$seat_info->seat_id)
    {
        LogErr("[seat_id:".$seat_info['seat_id']."], err");
        return errcode::SEAT_NOT_EXIST;
    }
    $resp = (object)array(
        'shopinfo'   => $shopinfo,
        'custominfo' => $custominfo,
        'seatinfo'   => $seat_info,
        'menuinfo'   => $menuinfo,
        'bought'     => $bought
    );
    LogInfo("--ok--");
    return 0;
}

function GetPraiseMenu(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $customer_id = $_['customer_id'];
    if(!$customer_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    LogDebug("customer_id:[$customer_id]");
    //获取店铺信息
    $customer  = \Cache\Customer::Get($customer_id);
    $shop_id   = $customer->shop_id;
    $shop_info = \Cache\Shop::Get($shop_id);
    //取出所收藏的菜品id
    $praise_mgo = new \DaoMongodb\Praise;
    $praise_food = $praise_mgo->GetFoodByCustomerId($customer_id);
    $praise_food_id = [];
    foreach ($praise_food as $value)
    {
        if($value->food_id)
        {
            array_push($praise_food_id, $value->food_id);
        }
    }
    //无收藏时
    if(count($praise_food_id) <= 0)
    {
        $resp = (object)array(
            'menuinfo'   => []
        );
        LogInfo("--ok--");
        return 0;
    }
    $food      = new \DaoMongodb\MenuInfo;
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
    //按分类输出
    //获取该店铺的所有的分类数据
    $category_info = \Cache\Category::GetCategoryList($shop_id);
    $mgo           = new \DaoMongodb\Category;
    $category_list = [];
    foreach ($category_info as $cateone_value)
    {
        $info = $mgo->GetByParentList($cateone_value->category_id);
        if(!$info)
        {
            array_push($category_list, $cateone_value);
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
        // 判断店铺是否支持打包
        $shop_pake = 0;
        if(in_array(SALEWAY::PACK, $shop_info->sale_way))
        {
            $shop_pake = 1;
        }

        $food_list = [];
        foreach ($food_id_list as $food_value)
        {
            if(!in_array($food_value->food_id, $praise_food_id))
            {
                continue;
            }
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
            // 判断菜品是否能打包
            $is_pake = 0;
            if(in_array(SALEWAY::PACK, $food_value->sale_way) && $shop_pake == 1)
            {
                $is_pake = 1;
            }
            //用于判断使用的是哪种价格显示
            $using = $food_value->food_price->using;
            $name  = [];
            if(($using & PriceType::FESTIVAL) != 0){// 节日价
                $name[] = 'festival_price';
            }
            if(($using & PriceType::VIP) != 0){     // 会员价
                $name[] = 'vip_price';
            }
            if(($using & PriceType::DISCOUNT) != 0){// 折扣价
                $name[] = 'discount_price';
            }
            if(($using & PriceType::ORIGINAL) != 0){// 普通价
                $name[] = 'original_price';
            }
            //菜品规格 用于含有规格的菜品使用的口味及价格显示
            $specs = [];
            if($food_value->food_attach_list)
            {
                $spec_one = [];
                foreach ($food_value->food_attach_list as $spec_value)
                {
                    $spec_one['type']  = 1;
                    $spec_one['title'] = $spec_value->title;
                    $spec_one['list']  = $spec_value->list;
                    array_push($specs, $spec_one);
                }

            }
            //判断店铺的价格使用type值是否是规格的价格，type=2
            if($food_value->food_price->type == FoodPriceType::SPEC)
            {
                $spec_two['type']  = $food_value->food_price->type;
                $spec_two['title'] = "份量";
                $s              = [];
                $spec_four      = [];
                foreach ($food_value->food_price->price as $price_value)
                {
                    if($price_value->is_use == IsPecs::YES)
                    {
                        $spec_four['spec_type'] = $price_value->spec_type;
                        foreach ($name as $value)
                        {
                            $spec_four[$value] = $price_value->$value;
                        }
                        array_push($s, $spec_four);
                    }
                    $spec_two['list'] = $s;
                }
                array_push($specs, $spec_two);
            }
            //用于没有规格使用的菜品价格
            $price = [];
            if($food_value->food_price->type == FoodPriceType::THIS)
            {
                $price['type'] = $food_value->food_price->type;
                foreach ($food_value->food_price->price as $price_dif)
                {
                    foreach ($name as $value)
                    {
                        $price[$value] = $price_dif->$value;
                    }
                }

            }else {
                $price['type'] = $food_value->food_price->type;
            }
            //如果类型是配件就直接取价格
            if($food_value->type == CateType::TYPETWO)
            {
                $price = $food_value->food_price;
                $is_pake = 0;
            }
            if($food_value->stock_num_day > 0)
            {
                $food_num_day = GetTodayFoodSoldNum($food_value->food_id);
                $food_list['stock_num'] = (int)$food_value->stock_num_day - (int)$food_num_day;
            }
            $food_list['food_id']       = $food_value->food_id;
            $food_list['food_name']     = $food_value->food_name;
            $food_list['food_num_mon']  = GetMonFoodSoldNum($food_value->food_id);
            $food_list['praise_num']    = $praise_mgo->GetFoodAllCount($food_value->food_id);
            $food_list['food_intro']    = $food_value->food_intro;
            $food_list['food_img_list'] = $food_value->food_img_list;
            $food_list['food_unit']     = $food_value->food_unit;
            $food_list['food_price']    = $price;
            $food_list['spec']          = $specs;
            $food_list['is_pake']       = $is_pake;
            //通过id来获取配件价格
            if(null != $food_value->accessory)
            {
                $ac_price               = \Cache\Food::Get($food_value->accessory);
                $ay_price               = $ac_price->food_price;

                $food_list['accessory'] = $ay_price ;
            } else {
                $food_list['accessory'] = null;
            }
            $food_list['accessory_num'] = $food_value->accessory_num;
            $food_list['composition']   = $food_value->composition;
            $food_list['feature']       = $food_value->feature;
            $food_list['good_rate']     = GetRate($food_value->food_id);
            array_push($food_list_all, $food_list);
        }
        if(!$food_list_all)
        {
            LogDebug("food list empty, category_id:[{$category->category_id}]");
            continue;
        }
        array_push($menuinfo, [
            'category_id'   => $category->category_id,
            'category_name' => $category->category_name,
            'type'          => $category->type,
            'food_list'     => $food_list_all
        ]);
    }

    $resp = (object)array(
        'menuinfo'   => $menuinfo
    );
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if (isset($_["foodinfo"]))
{
    $ret = GetFoodInfo($resp);
}
elseif (isset($_["get_home_data"]))
{
    $ret = GetMenuInfo($resp);
}
elseif (isset($_["get_praise_menu"]))
{
    $ret = GetPraiseMenu($resp);
}elseif (isset($_["customer_info"]))
{
    $ret = GetCustomerInfo();
}

$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp,//<<<<<<<未加密返回数据
    //'crypt' => 1, // 是加密数据标记
    //'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>
