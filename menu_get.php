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
require_once("mgo_stat_food_byday.php");
require_once("mgo_user.php");
require_once("mgo_customer.php");
require_once("mgo_spec.php");
require_once("mgo_shop.php");
require_once("mgo_evaluation.php");
//Permission::PageCheck();
//$_ = PageUtil::DecSubmitData();
//获取登陆客户信息
function GetCustomerInfo()
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = $_['shop_id'];
    $openid  = $_['openid'];
    $custominfo = [];
    $customer_info = \Cache\Customer::GetInfoByOpenidShopid($openid,$shop_id);
    //根据user表获取登录用户信息
    $userid = $customer_info->userid;
    $user_info = \Cache\User::Get($userid);
    //获取用户表的信息
    $custominfo['customer_name'] = $user_info->usernick;
    $custominfo['customer_portrait'] = $user_info->user_avater;
    $custominfo['customer_id'] = $userid;
    $custominfo['phone'] = $customer_info->phone;
    $custominfo['is_vip'] = $customer_info->is_vip;
    $custominfo['weixin_account'] = $customer_info->weixin_account;
    $custominfo['vip_level'] = $customer_info->vip_level;
    $custominfo['birthday'] = $user_info->birthday;
    $custominfo['sex'] = $user_info->sex;
    LogInfo("--ok--");
    return $custominfo;
}
// 当日取餐品已售数
function GetTodayFoodSoldNum($food_id)
{
    $mgo_stat = new \DaoMongodb\StatFood;
    $today = date("Ymd");
    $info = $mgo_stat->GetFoodStatByDay($food_id, $today);
    LogDebug($info);
    return $info->sold_num ?: 0;
}
//获取轮播信息
function GetBroadcastList($shop_id)
{
    $mgo = new \DaoMongodb\Broadcast();
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
                    $a = 1;
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
    $mgo = new \DaoMongodb\Evaluation;
    $all = $mgo->GetFoodAllCount($food_id);
    $good_is = $mgo->GetFoodGoodAllCount($food_id);
    $good = round($good_is / $all * 1, 2);
    return $good;
}

function GetCategoryInfo($category_id)
{
    $mgo = new \DaoMongodb\Category;
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
    $food_id = (string)$_['food_id'];

    $mgo = new \DaoMongodb\MenuInfo;
    $info = $mgo->GetFoodinfoById($food_id);
    $info->food_price = Util::FenToYuan($info->food_price);
    $info->food_vip_price = Util::FenToYuan($info->food_vip_price);
    $info->food_sold_num_day = GetTodayFoodSoldNum($food_id);

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
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = (string)$_['shop_id'];
    if (!$shop_id)
    {
        $shop_id = \Cache\Login::GetShopId();
    }
    LogDebug("shop_id:[$shop_id]");

    //获取店铺信息
    $shopinfo = [];
    $content_all = GetBroadcastList($shop_id);
    $shop_info = \Cache\Shop::Get($shop_id);
    $shopinfo['shop_id'] = $shop_info->shop_id;
    $shopinfo['shop_name'] = $shop_info->shop_name;
    $shopinfo['classify_name'] = $shop_info->classify_name;
    $shopinfo['telephone'] = $shop_info->telephone;
    $shopinfo['address'] = $shop_info->address;
    $shopinfo['open_time'] = $shop_info->open_time;
    $shopinfo['img_list'] = $shop_info->img_list;
    $shopinfo['praise_num'] = $shop_info->praise_num;
    $shopinfo['good_rate'] = $shop_info->good_rate;
    $shopinfo['broadcast_content'] = $content_all;

    //获取该店铺的营业时间点数
    $open_times = $shop_info->opening_time;
    //来获取营业时间中的type值
    $num = '';
    foreach ($open_times as $open_time) {

        $type = $open_time->type;
        $froms = $open_time->from;
        $tos = $open_time->to;
        $time = time();
        $from = ' ' . $froms->hh . ':' . $froms->mm . ':' . $froms->ss;
        $to = ' ' . $tos->hh . ':' . $tos->mm . ':' . $tos->ss;
        if ($tos->hh < $froms->hh) {
            $time1 = date('Y-m-d') . $from;
            $time2 = date('Y-m-d', strtotime('+1 day')) . $to;
        } else {
            $time1 = date('Y-m-d') . $from;
            $time2 = date('Y-m-d') . $to;
        }
        $time1 = strtotime($time1);
        $time2 = strtotime($time2);
        if ($time1 < $time && $time < $time2) {
            $num [] = $type;     //获取到所有时间段的type值
            //break;
        }
    }
    //按分类输出
    //获取该店铺的所有的分类数据
    $category_list = \Cache\Category::GetCategoryList($shop_id);
    $menuinfo = [];
    $lang = count($num);
    foreach ($category_list as $i => &$category)
    {
        //分类的时间段
        $cate_time = $category->opening_time;
        //判断是否是在这个时间段，不在就跳出
        if ($lang > 0)
        {
            $a = 0;
            foreach ($num as $v)
            {
                if (in_array($v, $cate_time))
                {
                    $a++;
                }
            }
            if ($a == 0)
            {
                continue;
            }
        }
        //获取所有分类下的餐品id数据
        $food_id_list = $category->food_id_list;
        $food_list_all = [];
        $food_list = [];

        foreach ($food_id_list as $food_id)
        {
            $k = \Cache\Food::Get($food_id);//获取餐品信息
            //找出所有分类下餐品的出售时间
            $food_sale_time = $k->food_sale_time[0];
            $b = 0;
            $time_range_stamp = isset($food_sale_time->time_range_stamp) ? $food_sale_time->time_range_stamp : '';
            $time_range_week = isset($food_sale_time->time_range_week) ? $food_sale_time->time_range_week : '';

            if ($time_range_stamp != '' || $time_range_week != '')
            {
                if ($time_range_stamp != '')
                {
                    foreach ($time_range_stamp as $p1)
                    {
                        $t1 = explode(',', $p1);
                        if ($t1[0] < time() && time() < $t1[1])
                        {
                            $b++;
                        }
                    }
                }
                if ($time_range_week != '')
                {
                    if (in_array(date('w'), $time_range_week))
                    {
                        $b++;
                    }
                }
                if ($b == 0)
                {
                    continue;
                }
            }
            $evaluation = \Cache\Evaluation::GetEvaluationList($food_id); //根据商品id获取评价列表
            $evaluation_list = [];
            $evaluation_list_all = [];
            //列出评价信息的数据
            foreach ($evaluation as $e_list) {
                $customer_list = \Cache\Customer::Get($e_list->customer_id);
                $customer_info = [];
                $customer_info['customer_id'] = $customer_list->customer_id;
                $user_info = \Cache\User::Get($customer_list->userid);
                $customer_info['customer_name'] = $user_info->usernick;
                $customer_info['customer_portrait'] = $user_info->user_avater;

                $evaluation_list['customer_info'] = $customer_info;
                if ($customer_info['customer_id'] == null)//<<<<<因为数据不完整加的临时过滤条件
                {
                    unset($evaluation_list['customer_info']);
                }
                $evaluation_list['content'] = $e_list->content;
                $evaluation_list['ctime'] = $e_list->ctime;
                //好评率
                //$evaluation_list['good_rate'] = GetRate($food_id);<<<<<<<<<放在外面了
                array_push($evaluation_list_all, $evaluation_list);
            }
            //价格显示
            $using = $k->food_price->using;
            $name = [];
            if (($using & PriceType::FESTIVAL) != 0)
            {// 节日价
                $name[] = 'festival_price:';
            }
            if (($using & PriceType::VIP) != 0)
            {// 会员价
                $name[] = 'vip_price';
            }
            if (($using & PriceType::DISCOUNT) != 0)
            {// 折扣价
                $name[] = 'discount_price';
            }
            if (($using & PriceType::ORIGINAL) != 0)
            {// 普通价
                $name[] = 'original_price';
            }

            $specs = Cache\Food::GetSpecInfo($food_id);
            if (!$specs)
            {
                $specs = [];
            }
            //返回规格中价格，通过using来选取
            foreach ($specs as $i => $spec)
            {
                $spec1 = [];
                $s = [];
                foreach ($spec->list as $list)
                {
                    $spec1['id'] = $list->id;
                    $spec1['title'] = $list->title;
                    $spec1['default'] = $list->default;
                    foreach ($name as $value)
                    {
                        $spec1[$value] = $list->$value;
                    }
                    array_push($s, $spec1);
                }
                $specs[$i]->list = $s;
            }
            $price = [];
            //通过using来选取没有规格的价格
            $price['type'] = $k->food_price->type;
            foreach ($name as $value)
            {

                $price[$value] = $k->food_price->$value;
            }
            $food_list['food_id'] = $k->food_id;
            $food_list['food_name'] = $k->food_name;
            $food_list['food_num_mon'] = $k->food_num_mon;
            $food_list['praise_num'] = $k->praise_num;
            $food_list['food_intro'] = $k->food_intro;
            $food_list['food_img_list'] = $k->food_img_list;
            $food_list['food_unit'] = $k->food_unit;
            $food_list['food_price'] = $price;

            //通过id来获取配件价格
            if (null != $k->accessory)
            {
                $ac_price = \Cache\Food::Get($k->accessory);
                $p1 = $ac_price->food_price->original_price;
                $food_list['accessory'] = $p1;
            } else
                {
                $food_list['accessory'] = null;
            }
            $food_list['spec'] = $specs;
            $food_list['composition'] = $k->composition;
            $food_list['feature'] = $k->feature;
            $food_list['good_rate'] = GetRate($food_id);
            //餐品的好评率
            $food_list['evaluation'] = $evaluation_list_all;
            array_push($food_list_all, $food_list);

        }
        if (!$food_list_all)
        {
            LogDebug("food list empty, category_id:[{$category->category_id}]");
            continue;
        }
        array_push($menuinfo, [
            'category_id' => $category->category_id,
            'category_name' => $category->category_name,
            'food_list' => $food_list_all
        ]);
    }
    $resp = (object)array(
        'shopinfo' => $shopinfo,
        'custominfo' => GetCustomerInfo(),
        'seatinfo' => \Cache\Seat::Get($_['seat_id']),
        'menuinfo' => $menuinfo,
    );
    //die;
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if (isset($_["foodinfo"]))
{
    $ret = GetFoodInfo($resp);
} elseif (isset($_["get_home_data"]))
{
    $ret = GetMenuInfo($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp,//<<<<<<<未加密返回数据
    //'crypt' => 1, // 是加密数据标记
    //'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>
