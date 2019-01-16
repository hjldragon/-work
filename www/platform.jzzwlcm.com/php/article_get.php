<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取消息类别信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("mgo_article.php");
require_once("mgo_platformer.php");


Permission::PageLoginCheck();

function GetArticleInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $article_id = (string)$_['article_id'];
    if(!$article_id)
    {
        LogErr(" no article_id");
        return errcode::PARAM_ERR;
    }
    $mgo = new \DaoMongodb\Article;
    $info = $mgo->GetArticleById($article_id);
    $resp = (object)array(
        'info' => $info
    );
    LogInfo("--ok--");
    return 0;
}

function GetArticleList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $page_size     = $_['page_size'];
    $page_no       = $_['page_no'];
    $article_type  = $_['article_type'];
    $article_state = $_['article_state'];
    $platformer_id = $_['platformer_id'];
    $title         = $_['title'];
    $is_send       = $_['is_send'];
    $end_time      = $_['end_time'];
    $begin_time    = $_['begin_time'];
    $sort_name     = $_['sort_name'];
    $desc          = $_['desc'];
    if (!$begin_time && $end_time)
    {
        $begin_time = -28800; //默认时间戳开始时间
    }
    if (!$end_time && $begin_time)
    {
        $end_time = 1922354460; //默认后面很长时 间
    }
    if(!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if(!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    switch ($sort_name) {
        case 'send_time':
            $sort['send_time'] = (int)$desc;
            break;
        default:
            break;
    }
    $total = 0;
    $mgo     = new \DaoMongodb\Article;
    $entry   = new \DaoMongodb\ArticleEntry;
    $list    = $mgo->GetArticleList(
        [
            'is_send'       => $is_send,
            'platformer_id' => $platformer_id,
            'article_type'  => $article_type,
            'article_state' => $article_state,
            'title'         => $title,
            'end_time'      => $end_time,
            'begin_time'    => $begin_time
        ],
        $sort,
        $page_size,
        $page_no,
        $total
    );
    $platformer  = new \DaoMongodb\Platformer;
    foreach ($list as &$item) {
        $platformer_info       = $platformer->QueryById($item->platformer_id);
         $item->platfomer_name = $platformer_info->pl_name;

            if (0 == $item->is_send && $item->send_time <= time()) {
                $item->is_send        = 1;
                //如果发布时间到了保存发送状态
                $entry->article_id    = $item->article_id;
                $entry->is_send       = 1;
                $entry->article_state = 0;
                $ret            = $mgo->Save($entry);
                if(0 != $ret)
                {
                    LogErr("Save err");
                    return errcode::SYS_ERR;
                }
            }

    }
    $resp = (object)array(
        'list'      => $list,
        'page_size' => $page_size,
        'page_no'   => $page_no,
        'total'     => $total,
    );
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_["get_article_info"]))
{
    $ret = GetArticleInfo($resp);
}
elseif(isset($_["get_article_list"]))
{
    $ret = GetArticleList($resp);
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
//LogDebug($html);
?><?php /******************************以下为html代码******************************/?>

