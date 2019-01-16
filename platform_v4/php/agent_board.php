<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 *代理商看板信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods.php");
require_once("mgo_platformer.php");
require_once("mgo_shop.php");
require_once("mgo_stat_agent_byday.php");
require_once("mgo_stat_food_byday.php");
require_once("mgo_stat_shop_byday.php");
require_once("mgo_user_feedback.php");
require_once("/www/public.sailing.com/php/mgo_from.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;
AgPermissionCheck::PageCheck(AgentPermissionCode::DATA_ALL_SEE);

//用来获取代理报表总览
function GetAgentDataAllShop(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id   = $_['agent_id'];
    $stat_agent = new \DaoMongodb\StatAgent;
    $all_num = 0;
    $now     = date('Ymd',time());//当前时间;
    $stat_agent->QueryById($agent_id,$all_num);
    $endYesterday = date('Ymd',time()-1*24*60*60);
    $yesterday_num  = 0;
    $stat_agent->GetAgentShopByDay(['day'=>$endYesterday],$agent_id,$yesterday_num);

    $week_num = 0;
    $beginThisweek = date('Ymd',time()-7*24*60*60);
    $stat_agent->GetAgentShopByDay(['begin_day'=>$beginThisweek, 'end_day'=>$now],$agent_id,$week_num);

    $month_num = 0;
    $beginThismonth = date('Ymd',time()-30*24*60*60);
    $stat_agent->GetAgentShopByDay(['begin_day'=>$beginThismonth, 'end_day'=>$now],$agent_id,$month_num);

    $resp = (object)array(
        'all_shop'      => $all_num,
        'yesterday_num' => $yesterday_num,
        'week_num'      => $week_num,
        'month_num'     => $month_num,
        'end_date'      => date('Y-m-d',time())
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//用来获取代理报商户新增数据
function GetAgentDataNewShop(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id       = $_['agent_id'];
    $use_way        = (int)$_['use_way'];
    $now            = date('Ymd',time());//当前时间
    $stat_agent     = new \DaoMongodb\StatAgent;
    $all = [];
    if($use_way == UseDataWay::WEEK)
    {
        $beginThisweek  = date('Ymd',time()-7*24*60*60);
        $list = $stat_agent->GetStatList(['begin_day'=>$beginThisweek,'end_day'=>$now,'agent_id'=>$agent_id]);
    }elseif($use_way == UseDataWay::MONTH)
    {
        $beginThismonth = date('Ymd',time()-30*24*60*60);
        $list = $stat_agent->GetStatList(['begin_day'=>$beginThismonth,'end_day'=>$now,'agent_id'=>$agent_id]);
    }elseif($use_way == UseDataWay::YEAR)
    {

        //最后一月
        $last_month  = date('Ymd',time()-30*24*60*60);
        $last_info = $stat_agent->GetAgentShopByDay(['begin_day'=>$last_month, 'end_day'=>$now],$agent_id,$last_num);
        $last_num['mouth'] = $now;
        array_push($all,$last_num);
        //十一月
        $eleven_month    = date('Ymd',time()-2*30*24*60*60);
        $eleven_info = $stat_agent->GetAgentShopByDay(['begin_day'=>$eleven_month, 'end_day'=>$last_month],$agent_id,$eleven_num);
        $eleven_num['mouth'] = $last_month;
        array_push($all,$eleven_num);
        //十月
        $ten_month    = date('Ymd',time()-3*30*24*60*60);
        $ten_info = $stat_agent->GetAgentShopByDay(['begin_day'=>$ten_month, 'end_day'=>$eleven_month],$agent_id,$ten_num);
        $ten_num['mouth'] = $eleven_month;
        array_push($all,$ten_num);
        //九月
        $nine_month    = date('Ymd',time()-4*30*24*60*60);
        $nine_info = $stat_agent->GetAgentShopByDay(['begin_day'=>$nine_month, 'end_day'=>$ten_month],$agent_id,$nine_num);
        $nine_num['mouth'] = $ten_month;
        array_push($all,$nine_num);
        //八月
        $eight_month    = date('Ymd',time()-5*30*24*60*60);
        $eight_info = $stat_agent->GetAgentShopByDay(['begin_day'=>$eight_month, 'end_day'=>$nine_month],$agent_id,$eight_num);
        $eight_num['mouth'] = $nine_month;
        array_push($all,$eight_num);
        //七月
        $seven_month    = date('Ymd',time()-6*30*24*60*60);
        $seven_info = $stat_agent->GetAgentShopByDay(['begin_day'=>$seven_month, 'end_day'=>$eight_month],$agent_id,$seven_num);
        $seven_num['mouth'] = $eight_month;
        array_push($all,$seven_num);
        //六月
        $six_month    = date('Ymd',time()-7*30*24*60*60);
        $six_info = $stat_agent->GetAgentShopByDay(['begin_day'=>$six_month, 'end_day'=>$seven_month],$agent_id,$six_num);
        $six_num['mouth'] = $seven_month;
        array_push($all,$six_num);
        //五月
        $five_month    = date('Ymd',time()-8*30*24*60*60);
        $five_info = $stat_agent->GetAgentShopByDay(['begin_day'=>$five_month, 'end_day'=>$six_month],$agent_id,$five_num);
        $five_num['mouth'] = $six_month;
        array_push($all,$five_num);
        //四月
        $four_month    = date('Ymd',time()-9*30*24*60*60);
        $four_info = $stat_agent->GetAgentShopByDay(['begin_day'=>$four_month, 'end_day'=>$five_month],$agent_id,$four_num);
        $four_num['mouth'] = $five_month;
        array_push($all,$four_num);
        //三月
        $there_month    = date('Ymd',time()-10*30*24*60*60);
        $there_info = $stat_agent->GetAgentShopByDay(['begin_day'=>$there_month, 'end_day'=>$four_month],$agent_id,$there_num);
        $there_num['mouth'] = $four_month;
        array_push($all,$there_num);
        //二月
        $two_month    = date('Ymd',time()-11*30*24*60*60);
        $two_info = $stat_agent->GetAgentShopByDay(['begin_day'=>$two_month, 'end_day'=>$there_month],$agent_id,$two_num);
        $two_num['mouth'] = $there_month;
        array_push($all,$two_num);
        //一月
        $one_month    = date('Ymd',time()-12*30*24*60*60);
        $one_info = $stat_agent->GetAgentShopByDay(['begin_day'=>$one_month, 'end_day'=>$two_month],$agent_id,$one_num);
        $one_num['mouth'] = $two_month;
        array_push($all,$one_num);
        $list = $all;
    }else{
        LogErr('no use way');
        return errcode::PARAM_ERR;
    }

    $resp = (object)array(
      'list'     => $list,
      'end_date' => date('Y-m-d',time())
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//代理商看板模营业额度排行榜
function GetShopMoneyBestTen(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id  = $_['agent_id'];
    $shop_stat = new \DaoMongodb\StatShop;
    $shop_mgo  = new \DaoMongodb\Shop;
    $all_shop  = $shop_stat->GetStatList(['agent_id'=>$agent_id]);
    $all       = ArrayUnsetT($all_shop, 'shop_id');
    $all_num   = [];
    foreach ($all as &$value)
    {
        $shop_num  = [];
        $shop_info = $shop_mgo->GetShopById($value->shop_id);
        $shop_stat->MoneyQueryByShopId([],$value->shop_id,$shop_num);
        $data['shop_name']        = $shop_info->shop_name;
        $data['consume_amount']   = $shop_num['consume_amount'];
        array_push($all_num,$data);
    }

    usort($all_num, function($a, $b){
        return ($b['consume_amount'] - $a['consume_amount']);
    });
    $all_data = array_slice($all_num,0,10);   //销售最好的10个
    $resp = (object)array(
        'list'=>$all_data
    );
    LogInfo("--ok--");
    return 0;
}
//代理商看板商户订单数据
function GetShopOrderData(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id  = $_['agent_id'];
    $use_way   = (int)$_['use_way'];

    $now       = date('Ymd',time());
    $shop_stat = new \DaoMongodb\StatShop;
    $all       = [];
    if($use_way == UseDataWay::WEEK)
    {
        $beginThisweek  = date('Ymd',time()-7*24*60*60);
        $all = $shop_stat->AgentQueryByDayAll(['begin_day'=>$beginThisweek,'end_day'=>$now], $agent_id, $num_all);
        $all_consume_amount = [];
        $list = [];
        foreach ($all as $v) {
            $all_consume_amount[$v->day] += $v->consume_amount;
        }
        foreach ($all_consume_amount as $k=>$as){
            $data['day']            = $k;
            $data['consume_amount'] = $as;
            array_push($list,$data);
        }
    }elseif($use_way == UseDataWay::MONTH)
    {
        $beginThismonth = date('Ymd',time()-30*24*60*60);
        $all = $shop_stat->AgentQueryByDayAll(['begin_day'=>$beginThismonth,'end_day'=>$now], $agent_id, $num_all);
             $all_consume_amount = [];
             $list = [];
             foreach ($all as $v) {
                 $all_consume_amount[$v->day] += $v->consume_amount;
             }
            foreach ($all_consume_amount as $k=>$as){
                $data['day']            = $k;
                $data['consume_amount'] = $as;
                array_push($list,$data);
            }

    }elseif($use_way == UseDataWay::YEAR)
    {
        //最后一月
        $last_month = date('Ymd',time()-30*24*60*60);
        $last_info  = $shop_stat->AgentQueryByDayAll(['begin_day'=>$last_month,'end_day'=>$now], $agent_id, $last_num);
        $last_num['mouth'] = $now;
        array_push($all,$last_num);
        //十一月
        $eleven_month    = date('Ymd',time()-2*30*24*60*60);
        $eleven_info = $shop_stat->AgentQueryByDayAll(['begin_day'=>$eleven_month, 'end_day'=>$last_month],$agent_id,$eleven_num);
        $eleven_num['mouth'] = $last_month;
        array_push($all,$eleven_num);
        //十月
        $ten_month = date('Ymd',time()-3*30*24*60*60);
        $ten_info  = $shop_stat->AgentQueryByDayAll(['begin_day'=>$ten_month, 'end_day'=>$eleven_month],$agent_id,$ten_num);
        $ten_num['mouth'] = $eleven_month;
        array_push($all,$ten_num);
        //九月
        $nine_month = date('Ymd',time()-4*30*24*60*60);
        $nine_info  = $shop_stat->AgentQueryByDayAll(['begin_day'=>$nine_month, 'end_day'=>$ten_month],$agent_id,$nine_num);
        $nine_num['mouth'] = $ten_month;
        array_push($all,$nine_num);
        //八月
        $eight_month = date('Ymd',time()-5*30*24*60*60);
        $eight_info  = $shop_stat->AgentQueryByDayAll(['begin_day'=>$eight_month, 'end_day'=>$nine_month],$agent_id,$eight_num);
        $eight_num['mouth'] = $nine_month;
        array_push($all,$eight_num);
        //七月
        $seven_month = date('Ymd',time()-6*30*24*60*60);
        $seven_info  = $shop_stat->AgentQueryByDayAll(['begin_day'=>$seven_month, 'end_day'=>$eight_month],$agent_id,$seven_num);
        $seven_num['mouth'] = $eight_month;
        array_push($all,$seven_num);
        //六月
        $six_month = date('Ymd',time()-7*30*24*60*60);
        $six_info  = $shop_stat->AgentQueryByDayAll(['begin_day'=>$six_month, 'end_day'=>$seven_month],$agent_id,$six_num);
        $six_num['mouth'] = $seven_month;
        array_push($all,$six_num);
        //五月
        $five_month = date('Ymd',time()-8*30*24*60*60);
        $five_info  = $shop_stat->AgentQueryByDayAll(['begin_day'=>$five_month, 'end_day'=>$six_month],$agent_id,$five_num);
        $five_num['mouth'] = $six_month;
        array_push($all,$five_num);
        //四月
        $four_month = date('Ymd',time()-9*30*24*60*60);
        $four_info  = $shop_stat->AgentQueryByDayAll(['begin_day'=>$four_month, 'end_day'=>$five_month],$agent_id,$four_num);
        $four_num['mouth'] = $five_month;
        array_push($all,$four_num);
        //三月
        $there_month = date('Ymd',time()-10*30*24*60*60);
        $there_info  = $shop_stat->AgentQueryByDayAll(['begin_day'=>$there_month, 'end_day'=>$four_month],$agent_id,$there_num);
        $there_num['mouth'] = $four_month;
        array_push($all,$there_num);
        //二月
        $two_month = date('Ymd',time()-11*30*24*60*60);
        $two_info  = $shop_stat->AgentQueryByDayAll(['begin_day'=>$two_month, 'end_day'=>$there_month],$agent_id,$two_num);
        $two_num['mouth'] = $there_month ;
        array_push($all,$two_num);
        //一月
        $one_month = date('Ymd',time()-12*30*24*60*60);
        $one_info  = $shop_stat->AgentQueryByDayAll(['begin_day'=>$one_month, 'end_day'=>$two_month],$agent_id,$one_num);
        $one_num['mouth'] = $two_month;
        array_push($all,$one_num);
        $list = $all;
    }

    $resp = (object)array(
        'list'=>$list
    );
    LogInfo("--ok--");
    return 0;
}
//代理商商户数据分析
function GetShopDataAnalyze(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id  = $_['agent_id'];

    $shop_mgo    = new \DaoMongodb\Shop;
    $ag_employee = new \DaoMongodb\AGEmployee;
    $from_mgo    = new  Mgo\From;

    $total        = 0;
    $shop_list    = $shop_mgo ->GetShopByAgentId($agent_id,$total);
    $employee_all = [];
    $agent_from   = [];
    $all_model    = [];
    $from_total   = 0;
    $ag_total     = 0;
    foreach ($shop_list as &$v){
        if($v->from)
        {
            $from_info = $from_mgo->GetByFromId($v->from);
            $data_from['from']    = $from_info->from;
            $shop_mgo->GetByFrom($v->from, $v->agent_id, $from_total);
            $data_from['from_total']   = round($from_total/$total,2)*100;
            array_push($agent_from,$data_from);
        }
        if($v->from_employee)
        {
            $ag_info = $ag_employee->QueryById($v->from_employee);
            $ag_from['from']    = $ag_info->real_name;
            $shop_mgo->GetByFromEmployee($v->from_employee, $v->agent_id, $ag_total);
            $ag_from['from_total']   = round($ag_total/$total,2)*100;
            array_push($employee_all,$ag_from);
        }
            array_push($all_model,$v->shop_model);
    }

    $there       = array_values(array_unset_tt($agent_from));
    $from_ag     = array_values(array_unset_tt($employee_all));

    $new_model = ArrayAdd($all_model);
    $all_num   = count($new_model);
    $other_num = array_count_values($new_model);
    $one = [];
    foreach ($other_num as $k=>$n)
    {
        $one[$k] = round($n/$all_num,2)*100;
    }


    usort($from_ag, function($a, $b){
        return ($b['from_total'] - $a['from_total']);
    });
    $two = array_slice($from_ag,0,10);   //销售最好的10个

    $resp = (object)array(
        'one'   => $one,
        'two'   => $two,
        'there' => $there
    );
    LogInfo("--ok--");
    return 0;
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
        $temp2[$k]['from']       = $array[0];
        $temp2[$k]['percent'] = (int)$array[1];
    }
    return $temp2;
}
//二维数组合成一维数字
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
if(isset($_["get_agent_all_shop"]))
{
    $ret = GetAgentDataAllShop($resp);
}
elseif(isset($_["get_agent_new_shop"]))
{
    $ret = GetAgentDataNewShop($resp);
}
elseif(isset($_["get_agent_shop_sort"]))
{
    $ret = GetShopMoneyBestTen($resp);
}
elseif(isset($_["order_allmoney_data"]))
{
    $ret = GetShopOrderData($resp);
}
elseif(isset($_["shop_data_analyze"]))
{
    $ret = GetShopDataAnalyze($resp);
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