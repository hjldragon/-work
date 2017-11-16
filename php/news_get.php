<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取消息类别信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("mgo_news.php");

Permission::PageCheck();

function GetNewsInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $news_id = (string)$_['news_id'];
    $mgo = new \DaoMongodb\News;
    $shop_id  = \Cache\Login::GetShopId();
    $info = $mgo->GetNewsById($news_id);
    if($info->is_system){
        $info->send_username = '新吃货官方';
        $mongodb = $mgo = new \DaoMongodb\NewsReady;
        $ready = $mongodb->ReadyNews($shop_id, $news_id);
    }else{
        $info->send_username = \Cache\Shop::Get($info->shop_id)->shop_name;
    }
    $resp = (object)array(
        'info' => $info
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetNewsList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $is_send   = $_['is_send'];
    $is_draft  = $_['is_draft'];
    $shop_id   = \Cache\Login::GetShopId();
    $filter['shop_id']   = $shop_id;
    $filter['is_send']   = $is_send;
    $filter['is_draft']  = $is_draft;
    $mgo = new \DaoMongodb\News;
    $list = $mgo->GetnNewsList($filter);
    $mongodb = new \DaoMongodb\NewsReady;
    $shop_name = \Cache\Shop::Get($shop_id)->shop_name;
    foreach ($list as $i => &$item) {
        $item->send_username = $shop_name;
    }
    $resp = (object)array(
        'list' => $list
    );
    LogInfo("--ok--");
    return 0;
}

function GetSysNewsList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id   = \Cache\Login::GetShopId();
    $mongodb = new \DaoMongodb\NewsReady;
    $list = $mongodb->GetNewsReadyByShopId($shop_id);
    $mgo = new \DaoMongodb\News;
    foreach ($list as $i => &$item) {
        $info = $mgo->GetNewsById($item->news_id);
        $item->send_username = '新吃货官方';
        $item->title         = $info->title;
        $item->content       = $info->content;
    }
    $resp = (object)array(
        'list' => $list
    );
    LogInfo("--ok--");
    return 0;
}




$ret = -1;
$resp = (object)array();
if(isset($_["news_info"]))
{
    $ret = GetNewsInfo($resp);
}
elseif(isset($_["news_list"]))
{
    $ret = GetNewsList($resp);
}
elseif(isset($_["sysnews_list"]))
{
    $ret = GetSysNewsList($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
LogDebug($html);
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
