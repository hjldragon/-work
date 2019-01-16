<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取餐桌位置信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("mgo_agent.php");
require_once("const.php");
require_once("cache.php");
require_once("/www/public.sailing.com/php/mgo_agent_cfg.php");
require_once("/www/public.sailing.com/php/mgo_city.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;
//PlPermissionCheck::PageCheck(PlPermissionCode::ALL_AGENT_SET);
//通过代理商来获取目前所享有的折扣率
function GetAgentRebates(&$resp)
{
    AgPermissionCheck::PageCheck(AgentPermissionCode::MY_CAT_SEE);
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id = (string)$_['agent_id'];
    if (!$agent_id) {
        LogErr("agent_id  is empty");
        return errcode::PARAM_ERR;
    }
    $agent_mgo     = new DaoMongodb\Agent;
    $agent_info    = $agent_mgo->QueryById($agent_id);
    $agent_level   = $agent_info->agent_level;
    $rebates       = PageUtil::GetAgentRebates($agent_id);

    $resp = (object)array(
     'agent_name'      => $agent_info->agent_name,
     'agent_id'        => $agent_info->agent_id,
     'agent_logo'      => $agent_info->agent_logo,
     'money'           => $agent_info->money,
     'agent_level'     => $agent_level,
     'business_status' => $agent_info->business_status,
     'rebates'         => $rebates
    );

    LogInfo("--ok--");
    return 0;
}
//通过代理商充值金额数据
function GetAgentUpMoney(&$resp)
{
    AgPermissionCheck::PageCheck(AgentPermissionCode::USE_PAY);
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id = (string)$_['agent_id'];
    if (!$agent_id) {
        LogErr("agent_id  is empty");
        return errcode::PARAM_ERR;
    }
    $agent_mgo     = new DaoMongodb\Agent;
    $agent_cfg_mgo = new Mgo\AgentCfg;
    $city_mgo      = new Mgo\City;
    $agent_info    = $agent_mgo->QueryById($agent_id);
//    $city_info     = $city_mgo->GetCityByName($agent_info->agent_city);
//    if($agent_info->agent_type == AgentType::GUILDAGENT)
//    {
//        $city_level = CityLevel::HANGYE;
//    }else{
//        $city_level = $city_info->city_level;
//    }
    $agent_type    = $agent_info->agent_type;
    $list          = $agent_cfg_mgo->GetListCityLevel($agent_type);
    $new_list      = [];
    foreach ($list  as &$v)
    {
      $info['agent_level']      = $v->agent_level;
      $info['hardware_rebates'] = $v->hardware_rebates;
      $info['uplevel_money']    = $v->uplevel_money;
      array_push($new_list,$info);
    }
    $resp = (object)array(
        'list'=>$new_list
    );

    LogInfo("--ok--");
    return 0;
}
//获取所有列表
function GetCfgList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_type      = $_['agent_type'];
    $agent_level     = $_['agent_level'];
    $page_size       = $_['page_size'];
    $page_no         = $_['page_no'];
    //排序字段
    if (!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if (!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    $total           = 0;
    $agent_cfg_mgo   = new Mgo\AgentCfg;

    $list            = $agent_cfg_mgo->GetList(
        ['agent_type'=>$agent_type,'agent_level'=>$agent_level],
        $page_size,
        $page_no,
        $sortby = [],
        $total
    );

    $page_all = ceil($total/$page_size);//总共页数

    $resp = (object)array(
        'banner_list' => $list,
        'total'    => $total,
        'page_all' => $page_all,
        'page_no'  => $page_no
    );

    LogInfo("--ok--");
    return 0;
}
//获取每个配置详情
function GetCfgInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_level   = $_['agent_level'];
    $agent_type    = $_['agent_type'];
    $id            = $_['id'];
    $agent_cfg_mgo = new Mgo\AgentCfg;
    if($id)
    {
        $info          = $agent_cfg_mgo->GetInfoById($id);
    }else{
        $info          = $agent_cfg_mgo->GetInfoByLevel($agent_type,$agent_level);
    }

    $resp = (object)array(
        'info' => $info
    );

    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["get_agent_rebates"]))
{
    $ret = GetAgentRebates($resp);
}elseif(isset($_["get_agent_upmoney"]))
{
    $ret = GetAgentUpMoney($resp);
}elseif(isset($_["get_list"]))
{
    $ret = GetCfgList($resp);
}elseif(isset($_["get_cfg_info"]))
{
    $ret = GetCfgInfo($resp);
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

