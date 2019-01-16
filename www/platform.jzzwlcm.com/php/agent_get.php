<?php
/*
 * [Rocky 2017-06-20 00:10:18]
 *
 */
require_once("current_dir_env.php");
require_once("mgo_agent.php");
require_once("mgo_shop.php");
require_once("redis_id.php");
require_once("cache.php");
require_once("mgo_department.php");
require_once("mgo_position.php");
require_once("mgo_user.php");
require_once("mgo_ag_employee.php");
require_once("mgo_stat_agent_byday.php");
require_once("mgo_stat_shop_byday.php");
Permission::PageLoginCheck();
function GetAgentInfo(&$resp)
{
    $_ = $GLOBALS["_"];

    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id = $_['agent_id'];
    if(!$agent_id)
    {
        LogErr("agent_id is empty");
        return errcode::PARAM_ERR;
    }
    $info = \Cache\Agent::Get($agent_id);
    if(!$info->agent_id)
    {
        LogErr("agent is empty");
        return errcode::PARAM_ERR;
    }
    $mgo = new \DaoMongodb\AGEmployee;
    $admin = $mgo->GetAdminByAgentId($agent_id);
    $userinfo = \Cache\UsernInfo::Get($admin->userid);
    $info->real_name = $userinfo->real_name;
    $info->phone     = $userinfo->phone;
    
    $resp = (object)array(
       'agent_info' => $info,
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetAgentList(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_type      = $_['agent_type'];
    $agent_name      = $_['agent_name'];
    $begin_time      = $_["begin_time"];
    $end_time        = $_["end_time"];
    $agent_level     = $_['agent_level'];
    $business_status = $_['business_status'];
    $begin_bs_time   = $_["begin_bs_time"];
    $end_bs_time     = $_["end_bs_time"];
    $begin_bsub_time = $_["begin_bsub_time"];
    $end_bsub_time   = $_["end_bsub_time"];
    $agent_province  = $_['agent_province'];
    $agent_city      = $_["agent_city"];
    $agent_area      = $_["agent_area"];
    $page_size       = $_['page_size'];
    $page_no         = $_['page_no'];
    $sort_name       = $_['sort_name'];
    $desc            = $_['desc'];
    if ($sort_name)
    {
        $sort[$sort_name] = (int)$desc;
    }
    //排序字段
    if (!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if (!$page_no)
    {
        $page_no = 1; //第一页开始
    }

    if(!$begin_time && $end_time)
    {
        $begin_time = -28800; //默认时间
    }
    if(!$end_time && $begin_time)
    {
        $end_time = time();
    }
    if(!$begin_bs_time && $end_bs_time)
    {
        $begin_bs_time = -28800; //默认时间
    }
    if(!$end_bs_time && $begin_bs_time)
    {
        $end_bs_time = time();
    }
    if(!$begin_bsub_time && $end_bsub_time)
    {
        $begin_bsub_time = -28800; //默认时间
    }
    if(!$end_bsub_time && $begin_bsub_time)
    {
        $end_bsub_time = time();
    }
    $total = 0;
    $mgo = new \DaoMongodb\Agent;
    $list = $mgo->GetAgentList([
        'agent_type'      => $agent_type,
        'agent_name'      => $agent_name,
        'begin_time'      => $begin_time,
        'end_time'        => $end_time,
        'agent_level'     => $agent_level,
        'business_status' => $business_status,
        'begin_bs_time'   => $begin_bs_time,
        'end_bs_time'     => $end_bs_time,
        'begin_bsub_time' => $begin_bsub_time,
        'end_bsub_time'   => $end_bsub_time,
        'agent_province'  => $agent_province,
        'agent_city'      => $agent_city,
        'agent_area'      => $agent_area
    ],
    $page_size,
    $page_no,
    $sort,
    $total
    );

    $resp = (object)array(
        'list' => $list,
        'total' => $total
    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetAgentAllList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_type = $_['agent_type'];
    $mgo  = new \DaoMongodb\Agent;
    $list = $mgo->GetAgentByType($agent_type);
    $agent = array();
    foreach ($list as $value) {
        $info = (object)array();
        $info->agent_id = $value->agent_id;
        $info->agent_name = $value->agent_name;
        array_push($agent, $info);
    }
    
    $resp = (object)[
        'agent_list' => $agent,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

//代理商来源和销售数据看板
function GetAgentSonData(&$resp)
{
    $_ = $GLOBALS["_"];

    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id = $_['agent_id'];
    if(!$agent_id)
    {
        LogErr("agent_id is empty");
        return errcode::PARAM_ERR;
    }
    $mgo       = new \DaoMongodb\Shop;
    $shop_list = $mgo->GetShopByAgentId($agent_id);
    $agent_from = [];
    $agent_area = [];
    $from_total = 0;
    $area_total = 0;
   foreach ($shop_list as &$v){
       if($v->from || $v->from_salesman){
           $agent_data['from_name']    = $v->from;
           $area_data['from_salesman'] = $v->from_salesman;
           $mgo->GetByFrom($v->from, $v->agent_id, $from_total);
           $mgo->GetByFromSalesman($v->from_salesman, $v->agent_id, $area_total);
           $agent_data['from_total']         = $from_total;
           $area_data['from_salesman_total'] = $area_total;
           array_push($agent_from,$agent_data);
           array_push($agent_area,$area_data);
       }
   }
    $from     = array_unset_tt($agent_from);
    $all_from = array_values($from);
    usort($all_from, function($a, $b){
        return ($b['from_total'] - $a['from_total']);
    });
   $from_data = array_slice($all_from,0,10);   //来源最好的10个

    $area              = array_unset_tt($agent_area);
    $all_from_salesman = array_values($area);
    usort($all_from_salesman, function($a, $b){
        return ($b['from_total'] - $a['from_total']);
    });
    $area_data = array_slice($all_from_salesman,0,10);   //销售最好的10个

    $resp = (object)array(
        'from'          => $from_data,
        'from_salesman' => $area_data,
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//用于二维数组去重
function array_unset_tt($array2D){
    foreach ($array2D as $k=>$v){
        $v=join(',',$v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
        $temp[$k]=$v;
    }
    $temp=array_unique($temp); //去掉重复的字符串,也就是重复的一维数组
    foreach ($temp as $k => $v){
        $array=explode(',',$v); //再将拆开的数组重新组装
        //下面的索引根据自己的情况进行修改即可
        $temp2[$k]['from_name']  = $array[0];
        $temp2[$k]['from_total'] = (int)$array[1];
    }
    return $temp2;
}
//行业代理商商户看板
function GetAgentShopData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id  = $_['agent_id'];
    if(!$agent_id)
    {
        LogErr("agent_id is empty");
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
        $end_day =   date('Ymd',$end_day);
    }

    $mgo              = new \DaoMongodb\StatAgent;
    $shop             = new \DaoMongodb\StatShop;
    //所有总计
    $num_all     = 0;
    $platform_id = 1;//目前只有一个运营平台
    $mgo->GetAgentShopByDay(['platform_id'=>$platform_id], $agent_id, $num_all);
    $all_new_shop_num      = $num_all['new_shop_num'];//签约店铺总数

    //当天合计
    $now_day     = (int)date('Ymd',time());
    $now_all_num = 0;
    $mgo->GetAgentShopByDay(['day'=>$now_day,'platform_id'=>$platform_id], $agent_id, $now_all_num);
    //搜索合计
    $num = 0;
    $list = $shop->AgentQueryByDayAll([
        'begin_day'  => $begin_day,
        'end_day'    => $end_day,
    ], $agent_id, $num);
    foreach ($list as &$v)
    {
        $v->guest_price = round($v->consume_amount/$v->customer_num,2);
    }
    $resp = (object)array(
        'all'        => [
            'all_new_shop_num'  => 0, //$all_new_shop_num,
            'all_sign_shop_num' => 0 //$all_new_shop_num,
        ],
        'now'        => [
            'new_shop_num'  => 0, //$now_all_num['new_shop_num'],
            'sign_shop_num' => 0//$now_all_num['sign_shop_num'],
        ],
        'list' => $list
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//区域代理商商户看板
function GetAgentData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id  = $_['agent_id'];
    if(!$agent_id)
    {
        LogErr("agent_id is empty");
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
       $end_day  =  date('Ymd',$end_day);
    }

    $mgo              = new \DaoMongodb\StatAgent;
    //所有总计
    $num_all     = 0;
    $platform_id = 1;//目前只有一个运营平台
    $mgo->GetAgentShopByDay(['platform_id'=>$platform_id], $agent_id, $num_all);
    $all_new_shop_num      = $num_all['new_shop_num']; //店铺总数
    $all_sign_shop_num     = $num_all['sign_shop_num'];//签约店铺

    //当天合计
    $now_day     = (int)date('Ymd',time());
    $now_all_num = 0;
    $mgo->GetAgentShopByDay(['day'=>$now_day,'platform_id'=>$platform_id], $agent_id, $now_all_num);
    //搜索合计
    $num = 0;
    $list = $mgo ->GetAgentShopByDay([
        'begin_day'  => $begin_day,
        'end_day'    => $end_day,
    ], $agent_id, $num);
    foreach ($list as &$v)
    {
        $v->sign_shop_rate   = $v->sign_shop_num/$v->sign_shop_num*100;
        $v->no_sign_shop_num = $v->new_shop_num-$v->sign_shop_num;
        $v->shop_num         = $v->new_shop_num;
        $v->sign_shop_num    = 0;
    }
    $resp = (object)array(
        'all'        => [
            'all_new_shop_num'     => $all_new_shop_num,
            'all_sign_shop_num'    => $all_sign_shop_num,
            'all_no_sign_shop_num' => $all_new_shop_num-$all_sign_shop_num,
            'sign_shop_rate'       => $all_sign_shop_num/$all_sign_shop_num*100,
            'area_sign_shop_num'   => $all_new_shop_num,
        ],
        'now'        => [
            'new_shop_num'  => $now_all_num['new_shop_num'],
            'sign_shop_num' => $now_all_num['sign_shop_num'],
            'shop_num'      => $now_all_num['new_shop_num'],
        ],
        'list' => $list
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_["agent_info"]))
{
    $ret = GetAgentInfo($resp);
}
elseif(isset($_["agent_list"]))
{
    $ret = GetAgentList($resp);
}elseif(isset($_["get_agent_list"]))
{
    $ret = GetAgentAllList($resp);
}elseif(isset($_["get_agent_son_data"]))
{
    $ret = GetAgentSonData($resp);
}elseif(isset($_["get_agent_shop_data"]))
{
    $ret = GetAgentShopData($resp);
}elseif(isset($_["get_agent_data"]))
{
    $ret = GetAgentData($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}


$html = json_encode((object)array(
    'ret' => $ret,
    'data'  => $resp
));
echo $html;
LogDebug($html);
// $callback = $_['callback'];
// $js =<<< eof
// $callback($html);
// eof;
?>