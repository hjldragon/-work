<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 *首页看板信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_platformer.php");
require_once("mgo_shop.php");
require_once("mgo_stat_agent_byday.php");
require_once("mgo_stat_food_byday.php");
require_once("/www/public.sailing.com/php/mgo_stat_platform_byday.php");
require_once("/www/public.sailing.com/php/mgo_pay_record.php");
require_once("/www/public.sailing.com/php/mgo_pay_record_byday.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
require_once("mgo_stat_shop_byday.php");
require_once("mgo_user_feedback.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
require_once("/www/public.sailing.com/php/mgo_pl_role.php");
use \Pub\Mongodb as Mgo;

//运营报表代理商顶部看板
function GetAgentData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent = new \DaoMongodb\Agent;
    $p_mgo = new Mgo\StatPlatform;
    $business_status  = BusinessType::SUCCESSFUL;
    $area_agent_type  =\AgentType::AREAAGENT;
    $guild_agent_type =\AgentType::GUILDAGENT;
    $platform_id = PlatformID::ID;
    $area_total  = 0;
    $guild_total = 0;
    $all_total   = 0;
    $ys_num_all  = 0;
    $vn_num_all  = 0;
    $yesterday   = date('Ymd',time()-60*60*24);
    $vorgestern  = date('Ymd',time()-2*60*60*24);
    $agent->GetAgentTotal(['agent_type'=>$area_agent_type,'business_status'=>$business_status],$area_total);
    $agent->GetAgentTotal(['agent_type'=>$guild_agent_type,'business_status'=>$business_status],$guild_total);
    $agent->GetAgentTotal(['business_status'=>$business_status],$all_total);
    $p_mgo->QueryByDayAll(['day'=>$yesterday], $platform_id, $ys_num_all);
    $p_mgo->QueryByDayAll(['day'=>$vorgestern], $platform_id, $vn_num_all);
    $yesterday_num  = $ys_num_all['no_industry_agent_num']+$ys_num_all['no_region_agent_num']+ $ys_num_all['industry_agent_num']+$ys_num_all['region_agent_num'];
    $vorgestern_num =$vn_num_all['no_industry_agent_num']+ $vn_num_all['no_region_agent_num']+ $vn_num_all['industry_agent_num']+$vn_num_all['region_agent_num'];
    $growth_rate =round((($yesterday_num-$vorgestern_num)/$vorgestern_num*100),1).'%';
    $resp = (object)array(
        'agent_all'    => $all_total,
        'area_total'   => $area_total,
        'guild_total'  => $guild_total,
        'ys_guild'     => $ys_num_all['no_industry_agent_num']+$ys_num_all['industry_agent_num'],//<<<<<<8.15认证和未认证都要统计buglist里面提的
        'ys_area'      => $ys_num_all['no_region_agent_num']+$ys_num_all['region_agent_num'],
        'growth_rate'  => $growth_rate
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//运营报表代理商分布图看板
function GetAgentMapData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_type  = $_['agent_type'];
    $agent_board = $_['agent_board'];

    if($agent_board)
    {
        PlPermissionCheck::PageCheck(PlPermissionCode::PL_AGENT_BOARD);
    }else{
        PlPermissionCheck::PageCheck(PlPermissionCode::PL_FROM);
        PlPermissionCheck::PageCheck(PlPermissionCode::AGENT_DATE);
    }
    $business_status = BusinessType::SUCCESSFUL;
    $agent_mgo       = new \DaoMongodb\Agent;
    $shop_mgo        = new \DaoMongodb\Shop;
    $city_list       = [];
    $city_list_one   = [];
    $list = $agent_mgo->GetAgentTotal(
        [
             'agent_type'      => $agent_type,
             'business_status' => $business_status
            ],
        $city_num);
   $num      = 0;
   $shop_num = 0;
   foreach ($list as &$v)
   {
//       if($v->agent_city)
//       {
       if($agent_type == AgentType::GUILDAGENT)//<<<<<<<<<<产品要求行业显示的商户数排行
       {
               $shop_mgo->GetByAgent($v->agent_id,$shop_num);
               $city_one['city_name'] = $v->agent_name;
               $city_one['num']       = $shop_num;
               array_push($city_list_one,$city_one);
       }else{
               if($v->agent_province == '澳门特别行政区' || $v->agent_province == '香港特别行政区')
               {
                   if($v->agent_province == '澳门特别行政区'){
                       $city['city_name'] = '澳门特别行政区';
                   }elseif ($v->agent_province == '香港特别行政区')
                   {
                       $city['city_name'] = '香港特别行政区';
                   }
                   $agent_mgo->GetAgentTotal(
                       [
                           'agent_province'  => $v->agent_province,
                           'business_status' => $business_status,
                           'agent_type'      => $agent_type
                       ],
                       $num);
                   $city['num'] = $num;
               }else{
                   $city['city_name'] = $v->agent_city;
                   $agent_mgo->GetAgentTotal(
                       [
                           'agent_type'      => $agent_type,
                           'business_status' => $business_status,
                           'agent_city'      => $v->agent_city
                       ],
                       $num);
                   $city['num'] = $num;
               }
               array_push($city_list,$city);
           }
       //}

   }
   if($agent_type == AgentType::GUILDAGENT){
       $city_all       = array_values(array_unset_tt($city_list_one));
       usort($city_all, function($a, $b){
           return ($b['num'] - $a['num']);
       });
       $city_sort = array_slice($city_all,0,10);   //拥有最多店铺的10个代理商
   }else{
       $city_all       = array_values(array_unset_tt($city_list));
       usort($city_all, function($a, $b){
           return ($b['num'] - $a['num']);
       });
       $city_sort = array_slice($city_all,0,10);   //区域最多的代理商
   }


    $resp = (object)array(
        'list'  => $city_all,
        'sort'  => $city_sort
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//运营报表新增代理商数据
function GetNewAddAgent(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $use_way   = (int)$_['use_way'];
    $now       = date('Ymd',time());
    $pl_stat   = new Mgo\StatPlatform;
    $all       = [];
    $platform_id = PlatformID::ID;
    if($use_way == UseDataWay::WEEK)
    {
        $beginThisweek  = date('Ymd',time()-6*24*60*60);
        $all  = $pl_stat->GetStatList(['begin_day'=>$beginThisweek,'end_day'=>$now,'platform_id'=>$platform_id]);
        $list = [];
        foreach ($all as &$v){
            $data['day']      = $v->day;
            $data['region']   = $v->region_agent_num + $v->no_region_agent_num;
            $data['industry'] = $v->industry_agent_num  + $v->no_industry_agent_num;
            array_push($list,$data);
        }
    }elseif($use_way == UseDataWay::MONTH)
    {
        $beginThismonth = date('Ymd',time()-30*24*60*60);
        $all = $all  = $pl_stat->GetStatList(['begin_day'=>$beginThismonth,'end_day'=>$now,'platform_id'=>$platform_id]);
        $list = [];
        foreach ($all as $v) {
            $data['day']      = $v->day;
            $data['region']   = $v->region_agent_num+ $v->no_region_agent_num;
            $data['industry'] = $v->industry_agent_num  + $v->no_industry_agent_num;
            array_push($list,$data);
        }
    }elseif($use_way == UseDataWay::YEAR)
    {
        //最后一月
        $last_month = date('Ymd',time()-30*24*60*60);
        $pl_stat->QueryByDayAll(['begin_day'=>$last_month,'end_day'=>$now], $platform_id, $last_num);
        $last['mouth']    = $now;
        $last['industry'] = $last_num['industry_agent_num']+$last_num['no_industry_agent_num'];
        $last['region']   = $last_num['region_agent_num']+$last_num['no_region_agent_num'];
        array_push($all,$last);
        //十一月
        $eleven_month    = date('Ymd',time()-2*30*24*60*60);
        $pl_stat->QueryByDayAll(['begin_day'=>$eleven_month, 'end_day'=>$last_month],$platform_id,$eleven_num);
        $eleven['mouth']    = $last_month;
        $eleven['industry'] = $eleven_num['industry_agent_num']+$eleven_num['no_industry_agent_num'];
        $eleven['region']   = $eleven_num['region_agent_num']+$eleven_num['no_region_agent_num'];
        array_push($all,$eleven);
        //十月
        $ten_month = date('Ymd',time()-3*30*24*60*60);
        $pl_stat->QueryByDayAll(['begin_day'=>$ten_month, 'end_day'=>$eleven_month],$platform_id,$ten_num);
        $ten['mouth']    = $eleven_month;
        $ten['industry'] = $ten_num['industry_agent_num']+$ten_num['no_industry_agent_num'];
        $ten['region']   = $ten_num['region_agent_num']+$ten_num['no_region_agent_num'];
        array_push($all,$ten);
        //九月
        $nine_month = date('Ymd',time()-4*30*24*60*60);
        $pl_stat->QueryByDayAll(['begin_day'=>$nine_month, 'end_day'=>$ten_month],$platform_id,$nine_num);
        $nine['mouth']    = $ten_month;
        $nine['industry'] = $nine_num['industry_agent_num']+$nine_num['no_industry_agent_num'];
        $nine['region']   = $nine_num['region_agent_num']+$nine_num['no_region_agent_num'];
        array_push($all,$nine);
        //八月
        $eight_month = date('Ymd',time()-5*30*24*60*60);
        $pl_stat->QueryByDayAll(['begin_day'=>$eight_month, 'end_day'=>$nine_month],$platform_id,$eight_num);
        $eight['mouth']    = $nine_month;
        $eight['industry'] = $eight_num['industry_agent_num']+$eight_num['no_industry_agent_num'];
        $eight['region']   = $eight_num['region_agent_num']+$eight_num['no_region_agent_num'];
        array_push($all,$eight);
        //七月
        $seven_month = date('Ymd',time()-6*30*24*60*60);
        $pl_stat->QueryByDayAll(['begin_day'=>$seven_month, 'end_day'=>$eight_month],$platform_id,$seven_num);
        $seven['mouth']    = $eight_month;
        $seven['industry'] = $seven_num['industry_agent_num']+$seven_num['no_industry_agent_num'];
        $seven['region']   = $seven_num['region_agent_num']+$seven_num['no_region_agent_num'];
        array_push($all,$seven);
        //六月
        $six_month = date('Ymd',time()-7*30*24*60*60);
        $pl_stat->QueryByDayAll(['begin_day'=>$six_month, 'end_day'=>$seven_month],$platform_id,$six_num);
        $six['mouth']    = $seven_month;
        $six['industry'] = $six_num['industry_agent_num']+$six_num['no_industry_agent_num'];
        $six['region']   = $six_num['region_agent_num']+$six_num['no_region_agent_num'];
        array_push($all,$six);
        //五月
        $five_month = date('Ymd',time()-8*30*24*60*60);
        $pl_stat->QueryByDayAll(['begin_day'=>$five_month, 'end_day'=>$six_month],$platform_id,$five_num);
        $five['mouth']    = $six_month;
        $five['industry'] = $five_num['industry_agent_num']+$five_num['no_industry_agent_num'];
        $five['region']   = $five_num['region_agent_num']+$five_num['no_region_agent_num'];
        array_push($all,$five);
        //四月
        $four_month = date('Ymd',time()-9*30*24*60*60);
        $pl_stat->QueryByDayAll(['begin_day'=>$four_month, 'end_day'=>$five_month],$platform_id,$four_num);
        $four['mouth']    = $five_month;
        $four['industry'] = $four_num['industry_agent_num']+$four_num['no_industry_agent_num'];
        $four['region']   = $four_num['region_agent_num']+$four_num['no_region_agent_num'];
        array_push($all,$four);
        //三月
        $there_month = date('Ymd',time()-10*30*24*60*60);
        $pl_stat->QueryByDayAll(['begin_day'=>$there_month, 'end_day'=>$four_month],$platform_id,$there_num);
        $there['mouth']    = $four_month;
        $there['industry'] = $there_num['industry_agent_num']+$there_num['no_industry_agent_num'];
        $there['region']   = $there_num['region_agent_num']+$there_num['no_region_agent_num'];
        array_push($all,$there);
        //二月
        $two_month = date('Ymd',time()-11*30*24*60*60);
        $pl_stat->QueryByDayAll(['begin_day'=>$two_month, 'end_day'=>$there_month],$platform_id,$two_num);
        $two['mouth']    = $there_month;
        $two['industry'] = $two_num['industry_agent_num']+$two_num['no_industry_agent_num'];
        $two['region']   = $two_num['region_agent_num']+$two_num['no_region_agent_num'];
        array_push($all,$two);
        //一月
        $one_month = date('Ymd',time()-12*30*24*60*60);
        $pl_stat->QueryByDayAll(['begin_day'=>$one_month, 'end_day'=>$two_month],$platform_id,$one_num);
        $one['mouth']    = $two_month;
        $one['industry'] = $one_num['industry_agent_num']+$one_num['no_industry_agent_num'];
        $one['region']   = $one_num['region_agent_num']+$one_num['no_region_agent_num'];
        array_push($all,$one);

        $list = $all;
    }else{
        LogErr('no use way');
        return errcode::PARAM_ERR;
    }

    $resp = (object)array(
        'list'=>$list
    );
    LogInfo("--ok--");
    return 0;
}
//运营报表商户模块顶部看板
function GetShopData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $p_mgo   = new Mgo\StatPlatform;
    $platform_id = PlatformID::ID;
    $all_total   = 0;
    $ys_num_all  = 0;
    $vn_num_all  = 0;
    $yesterday   = date('Ymd',time()-60*60*24);//昨日
    $vorgestern  = date('Ymd',time()-2*60*60*24);//前日
    $p_mgo->QueryByDayAll([],$platform_id,$all_total);
    $all_num  = $all_total['industry_shop_num']+$all_total['region_shop_num']+$all_total['no_industry_shop_num']+$all_total['no_region_shop_num'];
    $p_mgo->QueryByDayAll(['day'=>$yesterday], $platform_id, $ys_num_all);
    $p_mgo->QueryByDayAll(['day'=>$vorgestern], $platform_id, $vn_num_all);
    $yesterday_num  = $ys_num_all['no_industry_shop_num']+$ys_num_all['no_region_shop_num']+$ys_num_all['ndustry_shop_num']+$ys_num_all['region_shop_num'];
    $vorgestern_num = $vn_num_all['no_industry_shop_num']+$vn_num_all['no_region_shop_num']+$vn_num_all['ndustry_shop_num']+$vn_num_all['region_shop_num'];
    $growth_rate =round((($yesterday_num-$vorgestern_num)/$vorgestern_num*100),1).'%';

    $resp = (object)array(
        'agent_all'    => $all_num,
        'all_money'    => $all_total['industry_consume_amount']+$all_total['region_consume_amount'],
        'guild_total'  => $all_total['industry_consume_amount']+$all_total['region_consume_amount']/$all_num,
        'ys_guild'     => $ys_num_all['no_industry_shop_num']+$ys_num_all['industry_shop_num'],
        'ys_area'      => $ys_num_all['no_region_shop_num']+$ys_num_all['region_shop_num'],
        'growth_rate'  => $growth_rate
    );
    LogInfo("--ok--");
    return 0;
}
//运营报表商户分布图看板
function GetShopMapData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_type      = $_['agent_type'];
    $business_status = BusinessType::SUCCESSFUL;
    $shop_mgo        = new \DaoMongodb\Shop;
    $city_list       = [];
    $list = $shop_mgo->GetShopTotal(
        [
            'agent_type'      => $agent_type,
            'business_status' => $business_status
        ],
        $city_num);

    $num = 0;
    foreach ($list as &$v)
    {
        if($v->province){
            $city['city_name'] = $v->province;
            $shop_mgo->GetShopTotal(
                [
                    'agent_type'      => $agent_type,
                    'business_status' => $business_status,
                    'province'        => $v->province
                ],
                $num);
            $city['num'] = $num;
            array_push($city_list,$city);
        }
    }
    $city_all       = array_values(array_unset_tt($city_list));
    usort($city_all, function($a, $b){
        return ($b['num'] - $a['num']);
    });
    $city_sort = array_slice($city_all,0,10);   //销售最好的10个

    $resp = (object)array(
        'list'  => $city_all,
        'sort'  => $city_sort
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//运营报表新增商户数据
function GetShopHomeData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $use_way   = (int)$_['use_way'];
    $now       = date('Ymd',time());
    $pl_stat   = new Mgo\StatPlatform;
    $shop_mgo  = new \DaoMongodb\Shop;
    $shop_stat = new \DaoMongodb\StatShop;
    $platform_id = PlatformID::ID;
    if($use_way == UseDataWay::WEEK)
    {
        $beginThisweek  = date('Ymd',time()-6*24*60*60);
        $all            = $pl_stat->GetStatList(['begin_day'=>$beginThisweek,'end_day'=>$now,'platform_id'=>$platform_id]);
        $list = [];
        foreach ($all as &$v){
            $data['day']      = $v->day;
            $data['region']   = $v->region_shop_num  + $v->no_region_shop_num;
            $data['industry'] = $v->industry_shop_num+ $v->no_industry_shop_num;
            array_push($list,$data);
        }
    }elseif($use_way == UseDataWay::MONTH)
    {
        $beginThismonth = date('Ymd',time()-29*24*60*60);
        $all  = $pl_stat->GetStatList(['begin_day'=>$beginThismonth,'end_day'=>$now,'platform_id'=>$platform_id]);
        $list = [];
        foreach ($all as $v) {
            $data['day']      = $v->day;
            $data['region']   = $v->region_shop_num  + $v->no_region_shop_num;
            $data['industry'] = $v->industry_shop_num+ $v->no_industry_shop_num;
            array_push($list,$data);
        }
    }else{
        LogErr('no use way');
        return errcode::PARAM_ERR;
    }
    $two_all_shop  = $shop_stat->GetStatList([]);
    $two_all       = ArrayUnsetT($two_all_shop, 'shop_id');
    $two_all_num   = [];
    $all_model     = [];
    foreach ($two_all as &$value)
    {
        $shop_num  = [];
        $shop_info = $shop_mgo->GetShopById($value->shop_id);
        $shop_stat->MoneyQueryByShopId([],$value->shop_id,$shop_num);
        $two_data['shop_name']        = $shop_info->shop_name;
        $two_data['consume_amount']   = $shop_num['consume_amount'];
        array_push($two_all_num,$two_data);

    }
    usort($two_all_num, function($a, $b){
        return ($b['consume_amount'] - $a['consume_amount']);
    });
    $two_all_data = array_slice($two_all_num,0,10);   //销售最好的10个
    $shop_list    = $shop_mgo->GetAllShopList();
    foreach ($shop_list as $sl)
    {
        array_push($all_model,$sl->shop_model);
    }
    $new_model    = ArrayAdd($all_model);
    $all_num      = count($new_model);
    $other_num    = array_count_values($new_model);
    $there = [];
    foreach ($other_num as $k=>$n)
    {
        $there[$k] = (round($n/$all_num,2)*100).'%';
    }

    $resp = (object)array(
        'one'  => $list,
        'two'  => $two_all_data,
        'three'=> $there
    );
    LogInfo("--ok--");
    return 0;
}
//运营报表用户模块顶部看板
function GetCustomerOrderData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $p_mgo      = new Mgo\StatPlatform;
    $platform_id = PlatformID::ID;
    $all_total   = 0;
    $ys_num_all  = 0;
    $vn_num_all  = 0;
    $yesterday   = date('Ymd',time()-60*60*24);//昨日
    $vorgestern  = date('Ymd',time()-2*60*60*24);//前日
    $p_mgo->QueryByDayAll([],$platform_id,$all_total);
    $all_num    = $all_total['customer_num'];
    $all_money  = $all_total['region_consume_amount']+$all_total['industry_consume_amount'];
    $p_mgo->QueryByDayAll(['day'=>$yesterday], $platform_id, $ys_num_all);
    $p_mgo->QueryByDayAll(['day'=>$vorgestern], $platform_id, $vn_num_all);
    $region_yesterday_num    = $ys_num_all['region_app_order_num']+$ys_num_all['region_wx_order_num']+$ys_num_all['region_pad_order_num']
                               +$ys_num_all['region_cash_order_num']+$ys_num_all['region_self_order_num']+$ys_num_all['region_mini_order_num'];

    $industry_yesterday_num  = $ys_num_all['industry_app_order_num']+$ys_num_all['industry_wx_order_num']+$ys_num_all['industry_pad_order_num']
                               +$ys_num_all['industry_cash_order_num']+$ys_num_all['industry_self_order_num']+$ys_num_all['industry_mini_order_num'];

    $region_vorgestern_num   = $vn_num_all['region_app_order_num']+$vn_num_all['region_wx_order_num']+$vn_num_all['region_pad_order_num']
                              +$vn_num_all['region_cash_order_num']+$vn_num_all['region_self_order_num']+$vn_num_all['region_mini_order_num'];

    $industry_vorgestern_num = $vn_num_all['industry_app_order_num']+$vn_num_all['industry_wx_order_num']+$vn_num_all['industry_pad_order_num']
                               +$vn_num_all['industry_cash_order_num']+$vn_num_all['industry_self_order_num']+$vn_num_all['industry_mini_order_num'];

    $growth_rate =round(($region_yesterday_num+$industry_yesterday_num-$region_vorgestern_num-$industry_vorgestern_num)/
                  ($industry_vorgestern_num+$region_vorgestern_num)*100,1).'%';

    $resp = (object)array(
        'all_num'      => $all_num,
        'all_money'    => $all_money,
        'ys_order_num' => $region_yesterday_num+$industry_yesterday_num,
        'new_order_num'=> $region_yesterday_num+$industry_yesterday_num-$region_vorgestern_num-$industry_vorgestern_num,
        'growth_rate'  => $growth_rate
    );
    LogInfo("--ok--");
    return 0;
}
//运营报表用户模块订单三个模板看板数据
function GetOrderFromData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $use_way     = $_['use_way'];
    $order_from  = $_['order_from'];
    $p_mgo       = new Mgo\StatPlatform;
    $platform_id = PlatformID::ID;
    $now         = date('Ymd',time());//当前时间
    if($use_way == UseDataWay::WEEK)
    {
        $week_list = [];
        $beginThisweek  = date('Ymd',time()-7*24*60*60);
        $list = $p_mgo->GetStatList(['begin_day'=>$beginThisweek,'end_day'=>$now,'platform_id'=>$platform_id]);
         foreach ($list as &$v)
         {
           $week['day']                = $v->day;
             if($order_from  == DataOrderFrom::APP)
             {
                 $week['region_app']   = $v->region_app_order_num;
                 $week['industry_app'] = $v->industry_app_order_num;
             }elseif($order_from == DataOrderFrom::WECHAT)
             {
                 $week['region_wx']   = $v->region_wx_order_num;
                 $week['industry_wx'] = $v->industry_wx_order_num;
             }elseif($order_from == DataOrderFrom::PAD)
             {
                 $week['region_pad']   = $v->region_pad_order_num;
                 $week['industry_pad'] = $v->industry_pad_order_num;
             }elseif ($order_from == DataOrderFrom::CASH)
             {
                 $week['region_cash']   = $v->region_cash_order_num;
                 $week['industry_cash'] = $v->industry_cash_order_num;
             }elseif ($order_from == DataOrderFrom::SELFHELP)
             {
                 $week['region_self']   = $v->region_self_order_num;
                 $week['industry_self'] = $v->industry_self_order_num;
             }elseif ($order_from == DataOrderFrom::MINI)
             {
                 $week['region_mini']   = $v->region_mini_order_num;
                 $week['industry_mini'] = $v->industry_mini_order_num;
             }else{
                 $week['region_order_num']   = $v->region_app_order_num+$v->region_wx_order_num+$v->region_pad_order_num
                     +$v->region_cash_order_num+$v->region_self_order_num+$v->region_mini_order_num;
                 $week['industry_order_num'] = $v->industry_app_order_num+$v->industry_wx_order_num+$v->industry_pad_order_num
                     +$v->industry_cash_order_num+$v->industry_self_order_num+$v->industry_mini_order_num;
             }
           $week['region_money']       = $v->region_consume_amount;
           $week['industry_money']     = $v->industry_consume_amount;
           array_push($week_list,$week);
         }
        $list = $week_list;
    }elseif($use_way == UseDataWay::MONTH)
    {
        $month_list = [];
        $beginThismonth = date('Ymd',time()-30*24*60*60);
        $list = $p_mgo->GetStatList(['begin_day'=>$beginThismonth,'end_day'=>$now,'platform_id'=>$platform_id]);
        foreach ($list as &$v)
        {
            $month['day']                = $v->day;
            $month['region_order_num']   = $v->region_app_order_num+$v->region_wx_order_num+$v->region_pad_order_num
                +$v->region_cash_order_num+$v->region_self_order_num+$v->region_mini_order_num;
            $month['industry_order_num'] = $v->industry_app_order_num+$v->industry_wx_order_num+$v->industry_pad_order_num
                +$v->industry_cash_order_num+$v->industry_self_order_num+$v->industry_mini_order_num;
            $month['region_money']       = $v->region_consume_amount;
            $month['industry_money']     = $v->industry_consume_amount;
            if($order_from  == DataOrderFrom::APP)
            {
                $month['region_app']   = $v->region_app_order_num;
                $month['industry_app'] = $v->industry_app_order_num;
            }elseif($order_from == DataOrderFrom::WECHAT)
            {
                $month['region_wx']   = $v->region_wx_order_num;
                $month['industry_wx'] = $v->industry_wx_order_num;
            }elseif($order_from == DataOrderFrom::PAD)
            {
                $month['region_pad']   = $v->region_pad_order_num;
                $month['industry_pad'] = $v->industry_pad_order_num;
            }elseif ($order_from == DataOrderFrom::CASH)
            {
                $month['region_cash']   = $v->region_cash_order_num;
                $month['industry_cash'] = $v->industry_cash_order_num;
            }elseif ($order_from == DataOrderFrom::SELFHELP)
            {
                $month['region_self']   = $v->region_self_order_num;
                $month['industry_self'] = $v->industry_self_order_num;
            }elseif ($order_from == DataOrderFrom::MINI)
            {
                $month['region_mini']   = $v->region_mini_order_num;
                $month['industry_mini'] = $v->industry_mini_order_num;
            }
            array_push($month_list,$month);
        }
        $list = $month_list;

    }elseif($use_way == UseDataWay::YEAR)
    {
        $all = [];
        $year_list = [];
        //最后一月
        $last_month  = date('Ymd',time()-30*24*60*60);
        $last_info = $p_mgo->QueryByDayAll(['begin_day'=>$last_month, 'end_day'=>$now],$platform_id,$last_num);
        $last_num['mouth'] = $last_month;
        array_push($all,$last_num);
        //十一月
        $eleven_month    = date('Ymd',time()-2*30*24*60*60);
        $eleven_info = $p_mgo->QueryByDayAll(['begin_day'=>$eleven_month, 'end_day'=>$last_month],$platform_id,$eleven_num);
        $eleven_num['mouth'] = $eleven_month;
        array_push($all,$eleven_num);
        //十月
        $ten_month    = date('Ymd',time()-3*30*24*60*60);
        $ten_info = $p_mgo->QueryByDayAll(['begin_day'=>$ten_month, 'end_day'=>$eleven_month],$platform_id,$ten_num);
        $ten_num['mouth'] = $ten_month;
        array_push($all,$ten_num);
        //九月
        $nine_month    = date('Ymd',time()-4*30*24*60*60);
        $nine_info = $p_mgo->QueryByDayAll(['begin_day'=>$nine_month, 'end_day'=>$ten_month],$platform_id,$nine_num);
        $nine_num['mouth'] = $nine_month;
        array_push($all,$nine_num);
        //八月
        $eight_month    = date('Ymd',time()-5*30*24*60*60);
        $eight_info = $p_mgo->QueryByDayAll(['begin_day'=>$eight_month, 'end_day'=>$nine_month],$platform_id,$eight_num);
        $eight_num['mouth'] = $eight_month;
        array_push($all,$eight_num);
        //七月
        $seven_month    = date('Ymd',time()-6*30*24*60*60);
        $seven_info = $p_mgo->QueryByDayAll(['begin_day'=>$seven_month, 'end_day'=>$eight_month],$platform_id,$seven_num);
        $seven_num['mouth'] = $seven_month;
        array_push($all,$seven_num);
        //六月
        $six_month    = date('Ymd',time()-7*30*24*60*60);
        $six_info = $p_mgo->QueryByDayAll(['begin_day'=>$six_month, 'end_day'=>$seven_month],$platform_id,$six_num);
        $six_num['mouth'] = $six_month;
        array_push($all,$six_num);
        //五月
        $five_month    = date('Ymd',time()-8*30*24*60*60);
        $five_info = $p_mgo->QueryByDayAll(['begin_day'=>$five_month, 'end_day'=>$six_month],$platform_id,$five_num);
        $five_num['mouth'] = $five_month;
        array_push($all,$five_num);
        //四月
        $four_month    = date('Ymd',time()-9*30*24*60*60);
        $four_info = $p_mgo->QueryByDayAll(['begin_day'=>$four_month, 'end_day'=>$five_month],$platform_id,$four_num);
        $four_num['mouth'] = $four_month;
        array_push($all,$four_num);
        //三月
        $there_month    = date('Ymd',time()-10*30*24*60*60);
        $there_info = $p_mgo->QueryByDayAll(['begin_day'=>$there_month, 'end_day'=>$four_month],$platform_id,$there_num);
        $there_num['mouth'] = $there_month;
        array_push($all,$there_num);
        //二月
        $two_month    = date('Ymd',time()-11*30*24*60*60);
        $two_info = $p_mgo->QueryByDayAll(['begin_day'=>$two_month, 'end_day'=>$there_month],$platform_id,$two_num);
        $two_num['mouth'] = $two_month;
        array_push($all,$two_num);
        //一月
        $one_month    = date('Ymd',time()-12*30*24*60*60);
        $one_info = $p_mgo->QueryByDayAll(['begin_day'=>$one_month, 'end_day'=>$two_month],$platform_id,$one_num);
        $one_num['mouth'] = $one_month;
        array_push($all,$one_num);
        foreach ($all as &$v)
        {
            $year['mouth']                = $v['mouth'];
            if($order_from  == DataOrderFrom::APP)
            {
                $year['region_app']   = $v['region_app_order_num'];
                $year['industry_app'] = $v['industry_app_order_num'];
            }elseif($order_from == DataOrderFrom::WECHAT)
            {
                $year['region_wx']   = $v['region_wx_order_num'];
                $year['industry_wx'] = $v['industry_wx_order_num'];
            }elseif($order_from == DataOrderFrom::PAD)
            {
                $year['region_pad']   = $v['region_pad_order_num'];
                $year['industry_pad'] = $v['industry_pad_order_num'];
            }elseif ($order_from == DataOrderFrom::CASH)
            {
                $year['region_cash']   = $v['region_cash_order_num'];
                $year['industry_cash'] = $v['industry_cash_order_num'];
            }elseif ($order_from == DataOrderFrom::SELFHELP)
            {
                $year['region_self']   = $v['region_self_order_num'];
                $year['industry_self'] = $v['industry_self_order_num'];
            }elseif ($order_from == DataOrderFrom::MINI)
            {
                $year['region_mini']   = $v['region_mini_order_num'];
                $year['industry_mini'] = $v['industry_mini_order_num'];
            }
            array_push($year_list,$year);
        }
        $list = $year_list;
    }else{
        LogErr('no use way');
        return errcode::PARAM_ERR;
    }

    $resp = (object)array(
        'list' => $list,
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//运营平台首页待处理
function GetPendingData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop      = new \DaoMongodb\Shop;
    $agent     = new \DaoMongodb\Agent;
    $user_feed = new \DaoMongodb\UserFeedback;

    $shop_status_num   = 0;
    $agent_status_num  = 0;
    $user_feedback_num = 0;
    $business_status   = 1;//待认证状态
    $is_ready          = 0;//未读状态

    $agent->GetByBusinessStatus($business_status,$agent_status_num);
    $shop->GetByBusinessStatus($business_status, $shop_status_num);
    $user_feed->GetFeedbackReadyTotal($is_ready , $user_feedback_num);



    $resp = (object)array(
        'shop_status_num'    => $shop_status_num,
        'agent_status_num'   => $agent_status_num,
        'user_feedback_num'  =>  $user_feedback_num,
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//运营平台代理商管理运营报表顶部看板
function GetAgentAllInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent       = new \DaoMongodb\Agent;
    $pl_mgo      = new Mgo\StatPlatform;
    $pay_mgo     = new Mgo\PayRecord;

    $business_status  = BusinessType::SUCCESSFUL;
    $area_agent_type  =\AgentType::AREAAGENT;
    $guild_agent_type =\AgentType::GUILDAGENT;
    $platform_id      = PlatformID::ID;

    $area_total  = 0;
    $guild_total = 0;
    $all_total   = 0;
    $ys_num_all  = 0;
    $vn_num_all  = 0;
    $pay_money   = 0;
    $order_all   = 0;
    $agent->GetAgentTotal(['business_status'=>$business_status],$all_total);
    $agent->GetAgentTotal(['agent_type'=>$area_agent_type,'business_status'=>$business_status],$area_total);
    $agent->GetAgentTotal(['agent_type'=>$guild_agent_type,'business_status'=>$business_status],$guild_total);
    $pay_mgo->QueryByDayAll(['pay_status'=>RecordPayStatus::PAY], $pay_money);
    $yesterday   = date('Ymd',time()-60*60*24);
    $pl_mgo->QueryByDayAll(['day'=>$yesterday], $platform_id, $ys_num_all);
    $pl_mgo->QueryByDayAll(['day'=>$yesterday], $platform_id, $vn_num_all);
    $yesterday_num  = $ys_num_all['no_industry_agent_num']+$ys_num_all['no_region_agent_num']+ $ys_num_all['industry_agent_num']+$ys_num_all['region_agent_num'];
    $vorgestern_num = $vn_num_all['no_industry_agent_num']+ $vn_num_all['no_region_agent_num']+ $vn_num_all['industry_agent_num']+$vn_num_all['region_agent_num'];
     $pl_mgo->QueryByDayAll([], $platform_id, $order_all);

    $resp = (object)array(
        'agent_all'    => $all_total,
        'area_total'   => $area_total,
        'guild_total'  => $guild_total,
        'pay_money'    => $pay_money['record_money'],
        'order_all'    => $order_all['industry_goods_num']+$order_all['region_goods_num'],
        'ys_agent'     => $yesterday_num

    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//运营平台代理商管理运营报表代理商充值榜
function GetAgentPayTop(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_type  = $_['agent_type'];
    $agent_mgo   = new \DaoMongodb\Agent;
    $pay_mgo     = new Mgo\PayRecord;
    $agent_list   = [];
    $list = $agent_mgo->GetAgentTotal(
        [
            'agent_type'      => $agent_type,
        ],
        $city_num);
    $money = 0;
    foreach ($list as &$v)
    {
            $pay_mgo->QueryByDayAll(['agent_id'=>$v->agent_id,'pay_status'=>RecordPayStatus::PAY],$money);
            $city['city_name'] = $v->agent_name;
            $city['num']        = (float)$money['record_money'];
            array_push($agent_list,$city);
    }
    $date = array_column($agent_list, 'num');
    array_multisort($date,SORT_DESC,$agent_list);
//    $agent_all       = array_values(array_unset_tt($agent_list));
//    usort($agent_all, function($a, $b){
//        return ($b['num'] - $a['num']);
//    });
//
    $top = array_slice($agent_list,0,10);   //取充值最多的前10
    $resp = (object)array(
        'top'  => $top
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//运营平台代理商管理运营报表代理商充值数据
function GetAgentPayData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $use_way     = $_['use_way'];
    $agent_type  = $_['agent_type'];
    $pay_mgo     = new Mgo\StatPayRecord;
    $now         = date('Ymd',time());//当前时间
    $platform_id = PlatformID::ID;
    if($use_way == UseDataWay::WEEK)
    {
        $week_list      = [];
        $week_money     = 0;
        $beginThisweek  = date('Ymd',time()-6*24*60*60);
        $list = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$beginThisweek,'end_day'=>$now],$platform_id, $week_money);
        foreach ($list as &$v)
        {
            $week['day']         = $v->day;
            $week['money']       = $v->money;
            array_push($week_list,$week);
        }
        $list = $week_list;
    }elseif($use_way == UseDataWay::MONTH)
    {
        $month_list = [];
        $month_money= 0;
        $beginThismonth = date('Ymd',time()-29*24*60*60);
        $list = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$beginThismonth,'end_day'=>$now],$platform_id,$month_money);
        foreach ($list as &$v)
        {
                $month['day']   = $v->day;
                $month['money'] = $v->money;
            array_push($month_list,$month);
        }
        $list = $month_list;
    }elseif($use_way == UseDataWay::YEAR)
    {
        $all = [];
        $year_list = [];
        //最后一月
        $last_month= date('Ymd',time()-30*24*60*60);
        $last_info = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$last_month, 'end_day'=>$now],$platform_id,$last_num);
        $last_num['mouth'] = $now;
        array_push($all,$last_num);
        //十一月
        $eleven_month= date('Ymd',time()-2*30*24*60*60);
        $eleven_info = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$eleven_month, 'end_day'=>$last_month],$platform_id,$last_num);
        $eleven_num['mouth'] = $last_month;
        array_push($all,$eleven_num);
        //十月
        $ten_month= date('Ymd',time()-3*30*24*60*60);
        $ten_info = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$ten_month, 'end_day'=>$eleven_month],$platform_id,$last_num);
        $ten_num['mouth'] = $eleven_month;
        array_push($all,$ten_num);
        //九月
        $nine_month= date('Ymd',time()-4*30*24*60*60);
        $nine_info = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$nine_month, 'end_day'=>$ten_month],$platform_id,$last_num);
        $nine_num['mouth'] = $ten_month;
        array_push($all,$nine_num);
        //八月
        $eight_month= date('Ymd',time()-5*30*24*60*60);
        $eight_info = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$eight_month, 'end_day'=>$nine_month],$platform_id,$last_num);
        $eight_num['mouth'] = $nine_month;
        array_push($all,$eight_num);
        //七月
        $seven_month= date('Ymd',time()-6*30*24*60*60);
        $seven_info = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$seven_month, 'end_day'=>$eight_month],$platform_id,$last_num);
        $seven_num['mouth'] = $eight_month;
        array_push($all,$seven_num);
        //六月
        $six_month= date('Ymd',time()-7*30*24*60*60);
        $six_info = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$six_month, 'end_day'=>$seven_month],$platform_id,$last_num);
        $six_num['mouth'] = $seven_month;
        array_push($all,$six_num);
        //五月
        $five_month= date('Ymd',time()-8*30*24*60*60);
        $five_info = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$five_month, 'end_day'=>$six_month],$platform_id,$last_num);
        $five_num['mouth'] = $six_month;
        array_push($all,$five_num);
        //四月
        $four_month= date('Ymd',time()-9*30*24*60*60);
        $four_info = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$four_month, 'end_day'=>$five_month],$platform_id,$last_num);
        $four_num['mouth'] = $five_month;
        array_push($all,$four_num);
        //三月
        $there_month= date('Ymd',time()-10*30*24*60*60);
        $there_info = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$there_month, 'end_day'=>$four_month],$platform_id,$last_num);
        $there_num['mouth'] = $four_month;
        array_push($all,$there_num);
        //二月
        $two_month= date('Ymd',time()-11*30*24*60*60);
        $two_info = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$two_month, 'end_day'=>$there_month],$platform_id,$last_num);
        $two_num['mouth'] = $there_month;
        array_push($all,$two_num);
        //一月
        $one_month = date('Ymd',time()-12*30*24*60*60);
        $one_info  = $pay_mgo->QueryByDayAll(['agent_type'=>$agent_type,'begin_day'=>$one_month, 'end_day'=>$two_month],$platform_id,$last_num);
        $one_num['mouth'] = $two_month;
        array_push($all,$one_num);
        foreach ($all as &$v)
        {
            $year['mouth']  = $v['mouth'];
            $year['money']  = $v['money'];

            array_push($year_list,$year);
        }
        $list = $year_list;
    }else{
        LogErr('no use way');
        return errcode::PARAM_ERR;
    }

    $resp = (object)array(
        'list' => $list,
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//运营平台代理商管理运营报表代理商商户排行榜
function GetAgentShopNumTop(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_mgo   = new \DaoMongodb\Agent;
    $shop_mgo    = new \DaoMongodb\Shop;
    $area_type  = AgentType::AREAAGENT;
    $guild_type = AgentType::GUILDAGENT;

    $list_a   = [];
    $area_list = $agent_mgo->GetAgentTotal(
        [
            'agent_type'      => $area_type,
        ],
        $city_num);
    $shop_num = 0;
    $parm     = 'agent_name';
    foreach ($area_list as &$v)
    {
        $shop_mgo->GetShopTotal(['agent_id'=>$v->agent_id],$shop_num);
        $area['agent_name'] = $v->agent_name;
        $area['num']        = $shop_num;
        array_push($list_a,$area);
    }

    $area_all       = array_values(array_unset_tt_all($list_a, $parm));
    usort($area_all, function($a, $b){
        return ($b['num'] - $a['num']);
    });
    $area_top = array_slice($area_all,0,10);   //取最多的前10


    $list_b   = [];
    $guild_list = $agent_mgo->GetAgentTotal(
        [
            'agent_type'      => $guild_type,
        ],
        $city_num);
    foreach ($guild_list as &$v)
    {
        $shop_mgo->GetShopTotal(['agent_id'=>$v->agent_id],$shop_num);
        $guild['agent_name'] = $v->agent_name;
        $guild['num']        = $shop_num;
        array_push($list_b,$guild);
    }


    $guild__all       = array_values(array_unset_tt_all($list_b, $parm));
    usort($guild__all, function($a, $b){
        return ($b['num'] - $a['num']);
    });
    $guild_top = array_slice($guild__all ,0,10);   //取最多的前10
    $resp = (object)array(
        'area_top'  => $area_top,
        'guild_top' => $guild_top
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//运营平台代理商管理运营报表代理商商户数签约的销售人员排行
function GetAgentFromEmployeeTop(&$resp)
{
    $_             = $GLOBALS["_"];
    $positon_name  = $_['position_name'];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_mgo     = new \DaoMongodb\Agent;
    $pl_mgo        = new \DaoMongodb\Platformer;
    $pl_position   = new \DaoMongodb\PLPosition;
    $role          = new Mgo\PlRole;
    $platform_id   = PlatformID::ID;
    if(!$positon_name)
    {
        $positon_name  = '销售人员'; //只要销售人员，产品需求说明了的
    }
    $position_info  = $pl_position->QueryByName($platform_id,$positon_name);

    $role_id_list = [];
    $role_list    = $role->GetListByPositionId($position_info->pl_position_id);
    foreach ($role_list as $vs)
    {
        $ids = $vs->pl_role_id;
        array_push($role_id_list,$ids);
    }
    $pl_list       = $pl_mgo->GetByPsIdList($role_id_list);
    $area_type  = AgentType::AREAAGENT;
    $guild_type = AgentType::GUILDAGENT;
    $list_a   = [];
    $list_b   = [];
    $parm     = 'pl_name';
    $area_num = 0;
    $guild_num= 0;
    foreach ($pl_list as &$v)
    {
        //区域个数
      $list =  $agent_mgo->GetAgentTotal(
            [
                'agent_type'      => $area_type,
                'from_employee'   => $v->platformer_id
            ],
            $area_num);

            $area['pl_name'] = $v->pl_name;
            $area['num']     = $area_num;
        array_push($list_a,$area);
        //行业个数
        $agent_mgo->GetAgentTotal(
            [
                'agent_type'      => $guild_type,
                'from_employee'   => $v->platformer_id
            ],
            $guild_num);
        $guild['pl_name'] = $v->pl_name;
        $guild['num']     = $guild_num;
        array_push($list_b,$guild);
    }
    $area_all       = array_values(array_unset_tt_all($list_a, $parm));
    usort($area_all, function($a, $b){
        return ($b['num'] - $a[
             'num']);
    });
    $area_top = array_slice($area_all,0,10);   //取最多的前10

    $guild__all       = array_values(array_unset_tt_all($list_b, $parm));
    usort($guild__all, function($a, $b){
        return ($b['num'] - $a['num']);
    });
    $guild_top = array_slice($guild__all ,0,10);   //取最多的前10
    $resp = (object)array(
        'area_top'  => $area_top,
        'guild_top' => $guild_top
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//运营平台代理商管理运营报表代理商订单统计（商城）
function GetGoodsOrder(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $use_way     = (int)$_['use_way'];
    $now         = date('Ymd',time());
    $platform_id = PlatformID::ID;
    $pl_stat     = new Mgo\StatPlatform;
    if($use_way == UseDataWay::WEEK)
    {
        $beginThisweek  = date('Ymd',time()-6*24*60*60);
        $all            = $pl_stat->GetStatList(['begin_day'=>$beginThisweek,'end_day'=>$now,'platform_id'=>$platform_id]);
        $list = [];
        foreach ($all as &$v){
            $data['day']      = $v->day;
            $data['region']   = $v->region_goods_num;
            $data['industry'] = $v->industry_goods_num;
            array_push($list,$data);
        }
    }elseif($use_way == UseDataWay::MONTH)
    {
        $beginThismonth = date('Ymd',time()-29*24*60*60);
        $all  = $pl_stat->GetStatList(['begin_day'=>$beginThismonth,'end_day'=>$now,'platform_id'=>$platform_id]);
        $list = [];
        foreach ($all as $v) {
            $data['day']      = $v->day;
            $data['region']   = $v->region_goods_num;
            $data['industry'] = $v->industry_goods_num;
            array_push($list,$data);
        }
    }else{
        LogErr('no use way');
        return errcode::PARAM_ERR;
    }

    $resp = (object)array(
        'list'  => $list,
    );
    LogInfo("--ok--");
    return 0;
}
//用于二维数转一维
function array_unset_tt($array2D){
    foreach ($array2D as $k=>$v){
        $v=join(',',$v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
        $temp[$k]=$v;
    }
    $temp=array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
    foreach ($temp as $k => $v){
        $array=explode(',',$v); //再将拆开的数组重新组装
        //下面的索引根据自己的情况进行修改即可
        $temp2[$k]['city_name']  = $array[0];
        $temp2[$k]['num']        = (float)$array[1];
    }
    return $temp2;
}
function array_unset_tt_all($array2D, &$parm){
    foreach ($array2D as $k=>$v){
        $v=join(',',$v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
        $temp[$k]=$v;
    }
    $temp=array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
    foreach ($temp as $k => $v){
        $array=explode(',',$v); //再将拆开的数组重新组装
        //下面的索引根据自己的情况进行修改即可
        $temp2[$k][$parm] = $array[0];
        $temp2[$k]['num'] = (int)$array[1];
    }
    return $temp2;
}
//二维数组含对象去重
function ArrayUnsetT($arr, $key)
{
    //建立一个目标数组
    $res = array();
    foreach ($arr as $value) {
        //查看有没有重复项
        if (isset($res[$value->$key])) {
            //有：销毁
            unset($value->$key);
        } else {
            $res[$value->$key] = $value;
        }
    }
    return $res;
}
function ArrayAdd($arr)
{
    $arr2 = array();
    foreach ($arr as $k => $v) {
        foreach ($v as $m => $n) {
            $arr2[] = $n;
        }
    }
    return $arr2;
}

$ret = -1;
$resp = (object)array();
if(isset($_["get_agent_data"]))
{
    PlPermissionCheck::PageCheck(PlPermissionCode::PL_FROM);
    PlPermissionCheck::PageCheck(PlPermissionCode::AGENT_DATE);
    $ret = GetAgentData($resp);
}elseif(isset($_["get_agent_map"]))
{
    $ret = GetAgentMapData($resp);
}elseif(isset($_["get_agent_new_add"]))
{
    PlPermissionCheck::PageCheck(PlPermissionCode::PL_FROM);
    PlPermissionCheck::PageCheck(PlPermissionCode::AGENT_DATE);
    $ret = GetNewAddAgent($resp);
} elseif(isset($_["get_shop_data"]))
{
    PlPermissionCheck::PageCheck(PlPermissionCode::PL_FROM);
    PlPermissionCheck::PageCheck(PlPermissionCode::SHOP_DATE);
    $ret = GetShopData($resp);
}elseif(isset($_["get_shop_map"]))
{
    PlPermissionCheck::PageCheck(PlPermissionCode::PL_FROM);
    PlPermissionCheck::PageCheck(PlPermissionCode::SHOP_DATE);
    $ret = GetShopMapData($resp);
}elseif(isset($_["get_order_from"]))
{
    PlPermissionCheck::PageCheck(PlPermissionCode::PL_FROM);
    PlPermissionCheck::PageCheck(PlPermissionCode::CUSTOMER_DATE);
    $ret = GetOrderFromData($resp);
}elseif(isset($_["get_shop_home_data"]))
{
    PlPermissionCheck::PageCheck(PlPermissionCode::PL_FROM);
    PlPermissionCheck::PageCheck(PlPermissionCode::SHOP_DATE);
    $ret = GetShopHomeData($resp);
}elseif(isset($_["get_order_data"]))
{
    PlPermissionCheck::PageCheck(PlPermissionCode::PL_FROM);
    PlPermissionCheck::PageCheck(PlPermissionCode::CUSTOMER_DATE);
    $ret = GetCustomerOrderData($resp);
}elseif(isset($_["get_agent_board"]))
{
    PlPermissionCheck::PageCheck(PlPermissionCode::PL_AGENT_BOARD);
    $ret = GetAgentAllInfo($resp);
}elseif(isset($_["get_agent_pay"]))
{
    PlPermissionCheck::PageCheck(PlPermissionCode::PL_AGENT_BOARD);
    $ret = GetAgentPayTop($resp);
}elseif(isset($_["agent_pay_date"]))
{
    PlPermissionCheck::PageCheck(PlPermissionCode::PL_AGENT_BOARD);
    $ret = GetAgentPayData($resp);
}elseif(isset($_["agent_shop_num_top"]))
{
    PlPermissionCheck::PageCheck(PlPermissionCode::PL_AGENT_BOARD);
    $ret = GetAgentShopNumTop($resp);
}elseif(isset($_["agent_from_top"]))
{
    PlPermissionCheck::PageCheck(PlPermissionCode::PL_AGENT_BOARD);
    $ret = GetAgentFromEmployeeTop($resp);
}elseif(isset($_["goods_order_stat"]))
{
    $ret = GetGoodsOrder($resp);
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
