<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 消息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_article.php");
require_once("redis_id.php");
require_once("const.php");

function SaveArticleInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $content       = $_['content'];
    $title         = $_['title'];
    $article_id    = $_['article_id'];
    $article_type  = $_['article_type'];
    $send_time     = $_['send_time'];
    $platformer_id = $_['platformer_id'];
    if(!$content || !$title || !$platformer_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    if(!$article_id)
    {
        $article_id   = \DaoRedis\Id::GenArticleId();
        if($send_time > time()){
            $is_send       = 0;
            $article_state = 1;
        }else{
            $is_send       = 1;
            $send_time     = time();
            $article_state = 0;
        }
    }else{
        if($send_time > time()){
            $is_send       = 0;
            $article_state = 1;
        }else{
            $is_send       = 1;
            $send_time     = time();
            $article_state = 0;
        }
    }
    if(!$article_type)
    {
        $article_type   = 1;
    }
    $entry      = new \DaoMongodb\ArticleEntry;
    $mongodb    = new \DaoMongodb\Article;

    $entry->article_id    = $article_id;
    $entry->platformer_id = $platformer_id;
    $entry->title         = $title;
    $entry->content       = $content;
    $entry->send_time     = $send_time;
    $entry->is_send       = $is_send;
    $entry->ctime         = time();
    $entry->delete        = 0;
    $entry->article_type  = $article_type;
    $entry->article_state = $article_state;
    $ret = $mongodb->Save($entry);

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

function DeleteArticle(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $article_id_list = json_decode($_['article_id_list']);

    $mongodb = new \DaoMongodb\Article;
    $ret = $mongodb->BatchDelete($article_id_list);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("del ok");
    return 0;
}

function ArticleOpen(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $article_id   = $_['article_id'];
    $article_state = $_['article_state'];
    if(!$article_id)
    {
        LogErr(" no article_id");
        return errcode::PARAM_ERR;
    }
    $mongodb = new \DaoMongodb\Article;
    $entry   = new \DaoMongodb\ArticleEntry;

    $entry->article_id = $article_id;
    if($article_state == 0)
    {
        $entry->send_time = time();
        $entry->is_send = 1;
    }

    $entry->article_state = $article_state;
    $ret = $mongodb->Save($entry);
    //$ret     = $mongodb->BatchOpen($article_id, $article_state);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("open ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['article_save']))
{
    $ret = SaveArticleInfo($resp);
} elseif(isset($_['article_del']))
{
    $ret = DeleteArticle($resp);
}elseif(isset($_['article_open']))
{
    $ret = ArticleOpen($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));

?><?php /******************************以下为html代码******************************/?>
<?=$html?>


