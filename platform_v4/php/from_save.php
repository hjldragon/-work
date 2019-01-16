<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 店铺信息保存类
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/mgo_from.php");
require_once("mgo_platformer.php");
require_once("mgo_agent.php");
require_once("mgo_shop.php");
require_once("/www/public.sailing.com/php/redis_id.php");
use \Pub\Mongodb as Mgo;

//来源编辑
function FromSave(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $from        = $_['from'];
    $userid      = $_['userid'];
    $from_id     = $_['from_id'];
    $mgo         = new Mgo\From;
    $entry       = new Mgo\FromEntry;
    $pl_mgo      = new \DaoMongodb\Platformer;

    if(!$userid)
    {
        $userid           = \Cache\Login::GetUserid();
    }
    $info = $mgo->GetByFromName($from);
    if($info->from_id)
    {
        LogErr('from name is have');
        return errcode::NAME_IS_EXIST;
    }
    //获取超级管理员信息
    $platform_id             = PlatformID::ID;
    $pl_info                 =  $pl_mgo->QueryByUserId($userid, $platform_id);
    $platformer_id           = $pl_info->platformer_id;
    if(!$from_id)
    {
        $from_id = \DaoRedis\Id::GenFromId();
    }
    $entry->from_id          = $from_id;
    $entry->ctime            = time();
    $entry->from             = $from;
    $entry->platformer_id    = $platformer_id;
    $entry->delete           = 0;
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
//删除来源
function DeleteSave(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $from_id_list     = json_decode($_['from_id_list']);
    $mgo              = new Mgo\From;
    $agent_mgo        = new \DaoMongodb\Agent;
    $shop_mgo         = new \DaoMongodb\Shop;
    $page_size        = 10;
    $page_no          = 1;
    $total            = 0;
    foreach ($from_id_list as $ids)
    {
        $ag_list = $agent_mgo->GetAgentList(['from'=>$ids],$page_size, $page_no, $sortby = [], $total);
        if(count($ag_list)>0)
        {
            LogDebug("Del err,this from is use by agent");
            return errcode::FROM_ERR;
        }
        $shop_list = $shop_mgo->GetShopList(['from'=>$ids],$page_size, $page_no, $sortby = [], $total);
        if(count($shop_list)>0)
        {
            LogDebug("Del err,this from is use by shop");
            return errcode::FROM_ERR;
        }
    }

    $ret = $mgo->BatchDelete($from_id_list);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("delete ok");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_['save_from']))
{
    $ret = FromSave($resp);
}elseif(isset($_['delete_from']))
{
    $ret = DeleteSave($resp);
}
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

