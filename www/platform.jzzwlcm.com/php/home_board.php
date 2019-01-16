<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 *首页看板信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_goods.php");
require_once("mgo_platformer.php");
require_once("mgo_shop.php");
require_once("mgo_stat_agent_byday.php");
require_once("mgo_stat_food_byday.php");
require_once("mgo_stat_platform_byday.php");
require_once("mgo_stat_shop_byday.php");
require_once("mgo_user_feedback.php");

Permission::PageLoginCheck();
//运营平台首页代理商看板
function GetAgentData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent = new \DaoMongodb\Agent;
    $area_agent_type  =\AgentType::AREAAGENT;
    $guild_agent_type =\AgentType::GUILDAGENT;
    $area_total  = 0;
    $guild_total = 0;
    $area_agent_all   = $agent->GetAgentByType($area_agent_type,$area_total);
    $guild_agent_all  = $agent->GetAgentByType($guild_agent_type,$guild_total);
    $area_agent       = GetAgentDataInfo($area_agent_all);
    $guild_agent      = GetAgentDataInfo($guild_agent_all);

    $resp = (object)array(
        'area'        => $area_agent,
        'guild'       =>$guild_agent,
        'area_total'  =>$area_total,
        'guild_total' =>$guild_total,
        'all_total'   =>$area_total+$guild_total,
        'date'        =>date('Y-m-d',time())
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//运营平台商户看板
function GetShopData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $begin_day = $_['begin_day'];
    $end_day   = $_['end_day'];
    if (!$begin_day)
    {
        $begin_day = date('Ymd',strtotime('-1 week')); //默认当前+1周后
    }else{
        $begin_day = date('Ymd',$begin_day);
    }
    if (!$end_day)
    {
        $end_day = date('Ymd',time()); //默认当前
    }else{
        $end_day = date('Ymd',$end_day);
    }
    $mgo              = new \DaoMongodb\StatPlatform;
    //所有总计
    $num_all     = 0;
    $platform_id = 1;
    $mgo->QueryByDayAll([], $platform_id, $num_all);
    $all_new_shop_num      = $num_all['new_shop_num'];//店铺总数
    $all_industry_shop_num = $num_all['industry_shop_num']+$num_all['sign_industry_shop_num'];//行业总数
    $all_region_shop_num   = $num_all['region_shop_num']+$num_all['sign_region_shop_num'];//区域总数
    $all_consume_amount    = $num_all['consume_amount'];//消费总额
    //当天合计
    $now_day     = (int)date('Ymd',time());
    $now_all_num = 0;
    $mgo->QueryByDayAll(['day'=>$now_day], $platform_id, $now_all_num);
    //搜索合计
    $num = 0;
    $list = $mgo->QueryByDayAll([
        'begin_day'         => $begin_day,
        'end_day'           => $end_day,
    ], $platform_id, $num);
     foreach ($list as &$v)
     {
         $v->average_money = round($v->consume_amount/$all_new_shop_num,2);
         $v->average_qr    = round($v->qr_order_num/$all_new_shop_num,2);
     }
    $resp = (object)array(
        'all'        => [
            'all_consume_amount'        => $all_consume_amount,
            'all_new_shop_num'          => $all_new_shop_num,
            'all_sign_shop'             => 0,//$all_industry_shop_num+$all_region_shop_num,//签约商铺
            'all_region_shop_num'       => $all_region_shop_num, //区域商户总计<<<<<<<<<<<<<<<<<<<<<<现在签约是只有签合同什么的才是签约，平台编辑下面的代理商都是未签约的
            'all_sign_industry_shop_num'=> 0,
            'all_sign_region_shop_num'  => 0,
            'region_percent'            => 0,
            'shop_all'                  => $all_new_shop_num //店铺总计
        ],
        'now'        => [
            'consume_amount'        => $now_all_num['consume_amount'],
            'average_money'         => round($now_all_num['consume_amount']/$all_new_shop_num,2),
            'average_qr'            => round($now_all_num['qr_order_num']/$all_new_shop_num,2),
            'sign_industry_shop_num'=> $now_all_num['sign_industry_shop_num'],
            'sign_region_shop_num'  => $now_all_num['sign_region_shop_num'],
            'new_shop_num'          => $now_all_num['new_shop_num'],
        ],
        'list' => $list
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//运营平台消费者看板
function GetCustomerData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $begin_day = $_['begin_day'];
    $end_day   = $_['end_day'];
    if (!$begin_day)
    {
        $begin_day  = date('Ymd',strtotime('-1 week')); //默认当前+1周后
    }else{
      $begin_day =  date('Ymd',$begin_day);
    }
    if (!$end_day)
    {
        $end_day = date('Ymd',time()); //默认当前
    }else{
        $end_day =  date('Ymd',$end_day);
    }

    $mgo              = new \DaoMongodb\StatPlatform;
    //所有总计
    $num_all     = 0;
    $platform_id = 1;
    $now_day     = (int)date('Ymd',time());
    $mgo->QueryByDayAll([], $platform_id, $num_all);
    $all_qr_order_num    = $num_all['qr_order_num'];//扫码点餐次数
    $all_customer_num    = $num_all['customer_num'];//消费者数
    $all_consume_amount  = $num_all['consume_amount'];//消费总额
    //当天合计
    $now_all_num = 0;
    $mgo->QueryByDayAll(['day'=>$now_day], $platform_id, $now_all_num);
    //搜索合计
    $num = 0;
    $list = $mgo->QueryByDayAll([
        'begin_day'         => $begin_day,
        'end_day'           => $end_day,
    ], $platform_id, $num);
    foreach ($list as &$v)
    {
        $v->guest_price = round($v->consume_amount/$v->customer_num,2);
    }
    $resp = (object)array(
        'all'        => [
            'all_customer_num'     => $all_customer_num,
            'all_qr_order_num'     => $all_qr_order_num,
            'all_consume_amount'   => $all_consume_amount,
        ],
        'now'        => [
            'consume_amount' => $now_all_num['consume_amount'],
            'new_cus_num'    => $now_all_num['new_cus_num'],
            'active_cus_num' => $now_all_num['active_cus_num'],
            'qr_order_num'   => $now_all_num['qr_order_num'],
            'guest_price'    => round($now_all_num['consume_amount']/$now_all_num['customer_num'],2)//客单价
        ],
        'list' => $list
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//用来获取代理商下面店铺的数据总和的方法
function GetAgentDataInfo($agent_all)
{
    $agent_stat = new \DaoMongodb\StatAgent;
    $agent_data = [];
    foreach ($agent_all as &$v)
    {
        $all_num = [];
        $stats = $agent_stat->QueryById($v->agent_id,$all_num);
        $data['agent_id']     = $v->agent_id;
        $data['agent_name']   = $v->agent_name;
        if(!$all_num['all_sign_shop_num']) //<<<<<<<<<<数据来源无法获取
        {
            $num = 0;
        }else{
            $num =$all_num['all_sign_shop_num'];
        }
        $data['all_sign_shop_num'] = $num;
        array_push($agent_data,$data);
    }

    usort($agent_data, function($a, $b){
        return ($b['all_sign_shop_num'] - $a['all_sign_shop_num']);
    });
    $all_data = array_slice($agent_data,0,10);   //销售最好的10个
    return $all_data;
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
$ret = -1;
$resp = (object)array();
if(isset($_["get_agent_data"]))
{
    $ret = GetAgentData($resp);
}
elseif(isset($_["get_shop_data"]))
{
    $ret = GetShopData($resp);
}
elseif(isset($_["get_customer_data"]))
{
    $ret = GetCustomerData($resp);
}
elseif(isset($_["get_pending_data"]))
{
    $ret = GetPendingData($resp);
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
