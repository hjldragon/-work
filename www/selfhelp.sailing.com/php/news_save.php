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

Permission::PageCheck();

function SaveNewsinfo(&$resp)
{
    
    $_ = $GLOBALS["_"];
    LogDebug($_);
    
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

    $news_id   = $_['news_id'];
    $content   = $_['content'];
    $title     = $_['title'];
    $is_draft  = $_['is_draft'];
    $send_time = $_['send_time'];
    $time      = time();
    if(!$content || !$title)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = \Cache\Login::GetShopId();
    $mgo = new \DaoMongodb\StatNews;
    $type = 0;
    if(!$is_draft){
        $day = date('Ymd',$time);
        $send_num = $mgo->GetNewsStatByDay($shop_id, $day)->send_num;
        $type = 1;
    }
    
    if((int)$send_num >= ShopNewsDay::NUM){
        LogErr("send num err");
        return errcode::NEWS_NUM_MAX;
    }
    if(!$content || !$title)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    if(!$news_id)
    {
        $news_id = \DaoRedis\Id::GenNewsId();
        $ctime   = $time;
    }

    $entry = new \DaoMongodb\NewsEntry;
    $mongodb = new \DaoMongodb\News;
    
    if($send_time > $time || $is_draft){
        $is_send = 0; 
    }else{
        $is_send = 1; 
        $send_time = $time;
    }
    $entry->news_id   = $news_id;
    $entry->shop_id   = $shop_id;
    $entry->title     = $title;
    $entry->content   = $content;
    $entry->is_draft  = $is_draft;
    $entry->send_time = $send_time;
    $entry->is_send   = $is_send;
    $entry->ctime     = $ctime;
    $entry->is_system = 0;
    $entry->delete    = 0;
    $ret = $mongodb->Save($entry);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($entry);
    if(1 == $type){
        $send = NewsSend($news_id, $shop_id, $send_time);
        if(0 != $send){
            LogErr("Send err");
            return errcode::SYS_ERR;
        }
        // 今日发送数加1
        $ret = $mgo->SellNumAdd($shop_id, $day, 1);
        if(0 != $ret)
        {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
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

    if(!PageUtil::LoginCheck())
    {
        LogDebug("not login, token:{$_['token']}");
        return errcode::USER_NOLOGIN;
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
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}


function NewsSend($news_id, $shop_id, $send_time){
    $mgo = new \DaoMongodb\Customer;
    $list = $mgo->QueryByShopid($shop_id);
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


