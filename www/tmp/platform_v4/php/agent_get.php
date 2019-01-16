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
require_once("mgo_pl_department.php");
require_once("mgo_pl_position.php");
require_once("mgo_platformer.php");
require_once("mgo_user.php");
require_once("mgo_ag_employee.php");
require_once("mgo_stat_agent_byday.php");
require_once("mgo_stat_shop_byday.php");
require_once("/www/public.sailing.com/php/mgo_city.php");
require_once("/www/public.sailing.com/php/mgo_from.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;
function GetAgentInfo(&$resp)
{


    $_ = $GLOBALS["_"];

    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id = $_['agent_id'];
    $platform = $_['platform'];

    if($platform)
    {
        PlPermissionCheck::PageCheck(PlPermissionCode::PL_AGENT_INFO);
    }
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

    $pl_mgo          = new \DaoMongodb\Platformer;
    $ag_mgo          = new \DaoMongodb\AGEmployee;
    $user_mgo        = new \DaoMongodb\User;
    $from_mgo        = new Mgo\From;
    $pl_info         = $pl_mgo->QueryById($info->from_employee);
    $ag_info         = $ag_mgo->GetAdminByAgentId($agent_id);
    $user_info       = $user_mgo->QueryById($ag_info->userid);
    $from_info       =  $from_mgo->GetByFromId($info->from);
    $info->real_name     = $pl_info->pl_name;
    $info->phone         = $user_info->phone;
    $info->password      = $user_info->password;
    $info->relation_name = $user_info->real_name;
    $info->email         = $user_info->email;
    $info->from_id       = $info->from;
    $info->from          = $from_info->from;

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
    //LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_type      = $_['agent_type'];
    $agent_name      = $_['agent_name'];
    $begin_time      = $_["begin_time"];
    $end_time        = $_["end_time"];
    $from            = $_['from'];
    $agent_level     = $_['agent_level'];
    $business_status = $_['business_status'];
    $agent_province  = $_['agent_province'];
    $agent_city      = $_["agent_city"];
    $agent_area      = $_["agent_area"];
    $agent_id        = $_['agent_id'];
    $real_name       = $_['real_name'];
    $page_size       = $_['page_size'];
    $page_no         = $_['page_no'];
    $sort_name       = $_['sort_name'];
    $desc            = $_['desc'];
    if($business_status == BusinessType::SUCCESSFUL)
    {
        PlPermissionCheck::PageCheck(PlPermissionCode::AGENT_LIST_SEE);
    }else{
        PlPermissionCheck::PageCheck(PlPermissionCode::AGENT_AUDIT_SEE);
    }

    if($agent_level || $agent_id || $agent_type || $agent_name || $begin_time || $end_time || $from || $real_name)
    {
        PlPermissionCheck::PageCheck(PlPermissionCode::AGENT_EDIT);
    }
    switch ($sort_name) {
        case 'agent_id':
            $sort['_id']   = (int)$desc;
            break;
        case 'ctime':
            $sort['ctime'] = (int)$desc;
            break;
        default:
            break;
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
    if(null == $business_status)
    {
        $business_status_list = [0,1,3]; //找没有认证成功的状态数据
    }
    $total = 0;
    $mgo             = new \DaoMongodb\Agent;
    $pl_mgo          = new \DaoMongodb\Platformer;
    $shop_mgo        = new \DaoMongodb\Shop;
    $from_mgo        = new Mgo\From;
    $pl_info         = $pl_mgo->QueryByPlName($real_name);
    $from_employee   = $pl_info->platformer_id;
    $from_info       =  $from_mgo->GetByFromName($from);

    $list = $mgo->GetAgentList([
        'agent_type'           => $agent_type,
        'from'                 => $from_info->from_id,
        'agent_name'           => $agent_name,
        'begin_time'           => $begin_time,
        'end_time'             => $end_time,
        'agent_level'          => $agent_level,
        'business_status'      => $business_status,
        'business_status_list' => $business_status_list,
        'agent_id'             => $agent_id,
        'from_employee'        => $from_employee,
        'agent_province'       => $agent_province,
        'agent_city'           => $agent_city,
        'agent_area'           => $agent_area
    ],
    $page_size,
    $page_no,
    $sort,
    $total
    );

    $page_all = ceil($total/$page_size);//总共页数
    foreach ($list as &$v)
    {
        $count        = 0;
        $pl_info      = $pl_mgo->QueryById($v->from_employee);
        $from_info    =  $from_mgo->GetByFromId($v->from);
        $shop_mgo->GetShopTotal(['agent_id'=>$v->agent_id,'business_status_list'=>[2,3]],
            $count);
        $v->from      = $from_info->from;
        $v->real_name = $pl_info->pl_name;
        $v->shop_num  = $count;
    }
    $resp = (object)array(
        'list'     => $list,
        'total'    => $total,
        'page_all' => $page_all,
        'page_no'  => $page_no
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
    $list = $mgo->GetAgentTotal(['agent_type'=>$agent_type]);
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
//获取代理商的优惠折扣
function GetAgentRebates(&$resp)
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
    $info      = \Cache\Agent::Get($agent_id);
    $city      = $info->agent_city;
    $city_mgo  = new Mgo\City;
    $city_info = $city_mgo->GetCityByName($city);

    $resp = (object)array(
    );
    LogInfo("--ok--");
    return 0;
}
//获取代理商下面的商户列表数据
function GetShopListByAgent(&$resp)
{

    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id             = $_['agent_id'];
    $sort_name            = $_['sort_name'];
    $desc                 = $_['desc'];
    $page_size            = $_['page_size'];
    $page_no              = $_['page_no'];
    $business_status_list = json_decode($_['business_status_list']);

    $platform = $_['platform'];
    if($platform)
    {
        PlPermissionCheck::PageCheck(PlPermissionCode::PL_AGENT_INFO);
    }
    $shop_mgo = new \DaoMongodb\Shop;
    $em_mgo   = new \DaoMongodb\Employee;
    $from_mgo = new Mgo\From;

    switch ($sort_name) {
        case 'record_id':
            $sort['_id']   = (int)$desc;
            break;
        case 'ctime':
            $sort['ctime'] = (int)$desc;
            break;
        case 'pay_way':
            $sort['pay_way'] = (int)$desc;
            break;
        default:
            break;
    }

    if (!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if (!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    $total    = 0;
    $list     = $shop_mgo->GetShopList([
        'agent_id'=>$agent_id,
        'business_status_list'=>$business_status_list],
        $page_size, $page_no, $sort, $total);
    $all = [];
    foreach ($list as &$v)
    {
        $admin                   = $em_mgo->GetAdminByShopId($v->shop_id);
        $from_info               =  $from_mgo->GetByFromId($v->from);
        $one['shop_id']          = $v->shop_id;
        $one['ctime']            = $v->ctime;
        $one['shop_name']        = $v->shop_name;
        $one['phone']            = $admin->phone;
        $one['business_status']  = $v->business_status;
        $one['telephone']        = $v->telephone;
        $one['from']             = $from_info->from;
        $one['is_freeze']        = $v->is_freeze;
        array_push($all,$one);
    }

    $resp = (object)[
       'shop_list' => $all,
       'total'     => $total,
       'page_all'  => ceil($total/$page_size),//总共页数,
       'page_no'   => $page_no
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//代理商平台获取代理商商城订单记录
function GetGoodsOrderList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id             = $_['agent_id'];
    $sort_name            = $_['sort_name'];
    $desc                 = $_['desc'];
    $page_size            = $_['page_size'];
    $page_no              = $_['page_no'];
    $platform             = $_['platform'];
    if($platform)
    {
        PlPermissionCheck::PageCheck(PlPermissionCode::PL_AGENT_INFO);
    }
    $goods_order          = new Mgo\GoodsOrder;
    $goods_mgo            = new Mgo\Goods;

    switch ($sort_name) {
        case 'goods_order_id':
            $sort['_id']   = (int)$desc;
            break;
        default:
            break;
    }

    $total    = 0;
    $list     = $goods_order->GetGoodsOrderList(['agent_id'=>$agent_id,'pay_status'=>1],$sort, $page_size, $page_no,$total);
    $all      = [];

    foreach ($list as &$v)
    {
        $img_all  = [];
        $one['order_time']       = date('Ymd',$v->order_time);
        $one['goods_order_id']   = $v->goods_order_id;
        foreach ($v->goods_list as $ls)
        {
          $goods_info = $goods_mgo->GetGoodsById($ls->goods_id);
          array_push($img_all,$goods_info->goods_img_list[0]);

        }
        if($v->pay_way != GoodsOrderPayWay::BALANCE)
        {
            $v->rebates = 0;
        }
        $one['goods_img_list']   = $img_all;
        $one['goods_num_all']    = $v->goods_num_all;
        $one['pay_way']          = $v->pay_way;
        $one['rebates']          = $v->rebates;
        $one['goods_price_all']  = $v->goods_price_all;
        $one['paid_price']       = round($v->paid_price,2);
        array_push($all,$one);
    }

    $resp = (object)[
        'list'      => $all,
        'total'     => $total,
        'page_all'  => ceil($total/$page_size),//总共页数,
        'page_no'   => $page_no
    ];
    //LogDebug($resp);
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
}elseif(isset($_["get_shop_by_agent"]))
{
    $ret = GetShopListByAgent($resp);
}elseif(isset($_["get_good_order_list"]))
{
    $ret = GetGoodsOrderList($resp);
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