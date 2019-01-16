<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取餐桌位置信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("mgo_shop.php");
require_once("mgo_agent.php");
require_once("cache.php");
require_once("/www/public.sailing.com/php/mgo_business.php");
use \Pub\Mongodb as Mgo;
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
function GetShopBusinessInfo(&$resp)
{
    ShopPermissionCheck::PageCheck(ShopPermissionCode::SEE_SHOP_BUSINESS);
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id  =  (string)$_['shop_id'];
    $agent_id =  (string)$_['agent_id'];

    if(!$shop_id && !$agent_id)
    {
        LogErr("no shop_id or agent_id");
        return errcode::PARAM_ERR;
    }

    $shop_mgo  = new \DaoMongodb\Shop;
    $agent_mgo = new \DaoMongodb\Agent;
    $mgo       = new Mgo\Business;
    if($shop_id)
    {
        $shop_info = $shop_mgo->GetShopById($shop_id);
        $info      = $mgo->GetByShopId($shop_id);
        $info->shop_name =  $shop_info->shop_name;
    }elseif($agent_id){
        $agent_info = $agent_mgo->QueryById($agent_id);
        $info       = $mgo->GetByAgentId($agent_id);
        $info->agent_name = $agent_info->agent_name;
    }else{
        LogErr("shop_id or agent_id is empty");
        return errcode::PARAM_ERR;
    }

    $resp = (object)array(
        'business_info' => $info
    );
   // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["business_info"]))
{
    $ret = GetShopBusinessInfo($resp);
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

