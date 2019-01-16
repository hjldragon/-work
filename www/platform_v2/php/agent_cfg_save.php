<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 平台保存相应需求的配置信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("/www/public.sailing.com/php/mgo_agent_cfg.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;

//保持代理商配置信息
function SaveAgentCfg(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::ALL_AGENT_SET);
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $id                 = $_['id'];
    $agent_type         = $_['agent_type'];
    $agent_level        = $_['agent_level'];
    $software_rebates   = $_['software_rebates'];
    $hardware_rebates   = $_['hardware_rebates'];
    $supplies_rebates   = $_['supplies_rebates'];
    $uplevel_money      = $_['uplevel_money'];
    $banner             = $_['banner'];
    $url                = $_['url'];

    if(!$agent_type && !$agent_level)
    {
        LogErr("agent_type or agent_level is empty");
        return errcode::PARAM_ERR;
    }
    $entry = new Mgo\AgentCfgEntry;
    $mgo   = new Mgo\AgentCfg;
    $a     = 0;
    if(!$id){
        $id = \DaoRedis\Id::GenAgentSedId();
        $a  = 1;
    }else{
       $a_cfg_info = $mgo->GetInfoById($id);
       if($a_cfg_info->agent_level != $agent_level || $a_cfg_info->agent_type != $agent_type)
       {
            $a = 1;
       }
    }

    if($a == 1)
    {
        $info = $mgo->GetInfoByLevel($agent_type, $agent_level);
        if($info->id)
        {
            LogErr('this set is have');
            return errcode::SET_ERR;
        }
    }

    $entry->id                = $id;
    $entry->agent_type        = $agent_type;
    $entry->agent_level       = $agent_level;
    $entry->software_rebates  = $software_rebates;
    $entry->hardware_rebates  = $hardware_rebates;
    $entry->supplies_rebates  = $supplies_rebates;
    $entry->uplevel_money     = $uplevel_money;
    $entry->banner            = $banner;
    $entry->url               = $url;
    $entry->delete            = 0;
    $ret = $mgo->Save($entry);

    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

//删除Bnner
function DeleteBanner(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $agent_type         = $_['agent_type'];
    $agent_level        = $_['agent_level'];
    $mgo     = new Mgo\AgentCfg;
    $ret     = $mgo->DeleteByBanner($agent_type,$agent_level);

    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['save_agent_cfg'])) {
    $ret = SaveAgentCfg($resp);
}
//elseif(isset($_['delete_banner']))
//{
//    $ret = DeleteBanner($resp);
//}
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
?><?php /******************************以下为html代码******************************/?>

