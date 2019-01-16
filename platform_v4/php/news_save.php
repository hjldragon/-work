<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 消息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_news.php");
require_once("mgo_customer.php");
require_once("mgo_stat_news_byday.php");
require_once("redis_id.php");
require_once("const.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");

function SaveNewsinfo(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::ADD_NEW);
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $content         = $_['content'];
    $title           = $_['title'];
    $send_type       = (int)$_['send_type'];
    $business_status = $_['business_status'];
    $platfomer_id    = $_['platfomer_id'];

    if(!$content || !$title || !$platfomer_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    if(!$send_type){
        $send_type = SendType::ALL;
    }

    if(null == $business_status)
    {
        $business_status = BusinessType::ALL;
    }

    $news_id    = \DaoRedis\Id::GenNewsId();
    $entry      = new \DaoMongodb\NewsEntry;
    $mongodb    = new \DaoMongodb\News;
    $send_time  = time();
    $entry->news_id         = $news_id;
    $entry->title           = $title;
    $entry->content         = $content;
    $entry->send_time       = $send_time;
    $entry->ctime           = time();
    $entry->is_system       = 1;
    $entry->delete          = 0;
    $entry->send_type       = $send_type;
    $entry->business_status = $business_status;
    $entry->platfomer_id    = $platfomer_id;
    $ret = $mongodb->Save($entry);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    if($business_status == BusinessType::ALL)
    {
        $business_status =  null;
    }
    switch ($send_type)
    {
        case 1:
                SendSystemNewsForShop($news_id, $send_time, $business_status);
                SendSystemNewsForAgent($news_id, $send_time, $business_status);
            break;
        case 2:
                SendSystemNewsForShop($news_id, $send_time, $business_status);
            break;
        case 3:
                SendSystemNewsForAgent($news_id, $send_time, $business_status);
            break;
        default:
            LogErr("Send err");
            return errcode::SYS_ERR;
            break;
    }
    $resp = (object)array(
    );
    
    LogInfo("save ok");
    return 0;
}

function DeleteNews(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::DEL_NEW);
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $news_id_list = json_decode($_['news_id_list']);
    $mongodb = new \DaoMongodb\News;
    $ret = $mongodb->BatchDelete($news_id_list);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $mgo = new \DaoMongodb\NewsReady;
    $ret = $mgo->BatchDeleteByNewsId($news_id_list);
    if(0 != $ret)
    {
        LogErr("del err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("del ok");
    return 0;
}


function NewsSend($news_id, $shop_id, $send_time){
    $mgo     = new \DaoMongodb\Customer;
    $list    = $mgo->QueryByShopid($shop_id);
    $entry   = new \DaoMongodb\NewsReadyEntry;
    $mongodb = new \DaoMongodb\NewsReady;
    $entry->shop_id   = $shop_id;
    $entry->news_id   = $news_id;
    $entry->delete    = 0;
    $entry->is_ready  = 0;
    $entry->ctime     = $send_time;
    $entry->is_system = 0;
    foreach ($list as  $item) {
        $entry->id = \DaoRedis\Id::GenNewsReadyId();
        $entry->customer_id = $item->customer_id;
        $ret = $mongodb->Save($entry);
        if(0 != $ret)
        {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
    }
    return 0;
}

function ReadyNews(&$resp, $type=null)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    if(!PageUtil::LoginCheck())
    {
        LogDebug("not login, token:{$_['token']}");
        return errcode::USER_NOLOGIN;
    }

    $id_list = json_decode($_['id_list']);
    LogDebug($id_list);
    $mongodb = new \DaoMongodb\NewsReady;
    $ret = $mongodb->BatchReady($id_list, $type);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

function SendSystemNewsForShop($news_id, $send_time, $business_status){
    $mgo     = new \DaoMongodb\Shop;
    $entry   = new \DaoMongodb\NewsReadyEntry;
    $mongodb = new \DaoMongodb\NewsReady;
    $list     = $mgo->GetAllShopList(['business_status'=>$business_status]);
    $entry->news_id   = $news_id;
    $entry->delete    = 0;
    $entry->is_ready  = 0;
    $entry->ctime     = $send_time;
    $entry->is_system = 1;
    foreach ($list as  $item) {
        $entry->id        = \DaoRedis\Id::GenNewsReadyId();
        $entry->shop_id   = $item->shop_id;
        $ret = $mongodb->Save($entry);
        if(0 != $ret)
        {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
    }
    return 0;
}

function SendSystemNewsForAgent($news_id, $send_time, $business_status){
    $mgo     = new \DaoMongodb\Agent;
    $entry   = new \DaoMongodb\NewsReadyEntry;
    $mongodb = new \DaoMongodb\NewsReady;
    $list    = $mgo->GetAllAgent(['business_status'=>$business_status]);
    $entry->news_id   = $news_id;
    $entry->delete    = 0;
    $entry->is_ready  = 0;
    $entry->ctime     = $send_time;
    $entry->is_system = 1;

    foreach ($list as  $item) {
        $entry->id         = \DaoRedis\Id::GenNewsReadyId();
        $entry->agent_id   = $item->agent_id;
        $ret = $mongodb->Save($entry);
        if(0 != $ret)
        {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
    }
    return 0;
}

//代理商已读信息/删除
function AgentReadyDelNews(&$resp)
{
    AgPermissionCheck::PageCheck(AgentPermissionCode::DEL_NEW);
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $news_id_list  = json_decode($_['news_id_list']);
    $agent_id      = $_['agent_id'];
    $type          = $_['type'];
    if($type)
    {
        $type = 1;
    }else{
        $type = 0;
    }
    $mongodb = new \DaoMongodb\NewsReady;

    $ret = $mongodb->AgentReadyNews($agent_id, $news_id_list ,$type);
    if(0 != $ret)
    {
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
if(isset($_['news_save']))
{
    $ret = SaveNewsinfo($resp);
}
else if(isset($_['del_news']))
{
    $ret = DeleteNews($resp);
}
else if(isset($_['ready_news']))
{
    $ret = ReadyNews($resp);
}
else if(isset($_['del_sysnews']))
{
    $ret = ReadyNews($resp, 1);
}
else if(isset($_['agent_ready_news']))
{
    $ret = AgentReadyDelNews($resp);
}
else
{
    $ret = -1;
    LogErr("param err");
}



$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>


