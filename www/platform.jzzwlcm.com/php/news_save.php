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
Permission::PageLoginCheck();

function SaveNewsinfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $content   = $_['content'];
    $title     = $_['title'];
    $news_id   = $_['news_id'];
    $send_time = $_['send_time'];
    $send_type = $_['send_type'];
    if(!$content || !$title)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    if(!$news_id)
    {
        $news_id   = \DaoRedis\Id::GenNewsId();
    }
    $entry      = new \DaoMongodb\NewsEntry;
    $mongodb    = new \DaoMongodb\News;
    $news_info  = $mongodb->GetNewsById($news_id);

    if($news_info->is_send == 1)
    {
        Logerr('news is send  can not editor');
        return errcode::NEWS_IS_SEND;
    }
    if($send_time > time()){
        $is_send   = 0;
    }else{
        $is_send   = 1;
        $send_time = time();
    }
    $entry->news_id   = $news_id;
    $entry->title     = $title;
    $entry->content   = $content;
    $entry->send_time = $send_time;
    $entry->is_send   = $is_send;
    $entry->ctime     = time();
    $entry->is_system = 1;
    $entry->delete    = 0;
    $entry->send_type = $send_type;
    $ret = $mongodb->Save($entry);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    switch ($send_type)
    {
        case 1:
            if($send_time == time())
            {
                SendSystemNewsForShop($news_id, $send_time);
            }
            break;
        case 2:
            if($send_time == time())
            {
                SendSystemNewsForAgent($news_id, $send_time);
            }
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
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
//    if(!PageUtil::LoginCheck())
//    {
//        LogDebug("not login, token:{$_['token']}");
//        return errcode::USER_NOLOGIN;
//    }
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

function SendSystemNewsForShop($news_id, $send_time){
    $mgo  = new \DaoMongodb\Shop;
    $list = $mgo->GetAllShopList();
    $entry   = new \DaoMongodb\NewsReadyEntry;
    $mongodb = new \DaoMongodb\NewsReady;
    $entry->news_id   = $news_id;
    $entry->delete    = 0;
    $entry->is_ready  = 0;
    $entry->ctime     = $send_time;
    $entry->is_system = 1;
    foreach ($list as  $item) {
        $entry->id = \DaoRedis\Id::GenNewsReadyId();
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

function SendSystemNewsForAgent($news_id, $send_time){
    $mgo     = new \DaoMongodb\Agent;
    $list    = $mgo->GetAllAgent();
    $entry   = new \DaoMongodb\NewsReadyEntry;
    $mongodb = new \DaoMongodb\NewsReady;
    $entry->news_id   = $news_id;
    $entry->delete    = 0;
    $entry->is_ready  = 0;
    $entry->ctime     = $send_time;
    $entry->is_system = 1;
    foreach ($list as  $item) {
        $entry->id = \DaoRedis\Id::GenNewsReadyId();
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


