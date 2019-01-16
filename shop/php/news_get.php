<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取消息类别信息
 */
require_once("current_dir_env.php");
require_once("/www/shop.sailing.com/php/page_util.php");
require_once("const.php");
require_once("cfg.php");
require_once("mgo_news.php");
require_once("mgo_stat_news_byday.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");

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
        $info->send_username = '欣吃货官方';
        $mongodb = new \DaoMongodb\NewsReady;
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
    $send_time = $_['sort_send_time'];
    $ctime     = $_['sort_ctime'];
    if($is_draft)
    {
        ShopPermissionCheck::PageCheck(ShopPermissionCode::DRAFT_NEWS);
    }else{
        ShopPermissionCheck::PageCheck(ShopPermissionCode::SEE_NEWS);
    }
    if($send_time)
    {
        $sortby['send_time'] = (int)$send_time;
    }
    if($ctime)
    {
        $sortby['ctime'] = (int)$ctime;
    }
    $time = time();
    $mgo = new \DaoMongodb\News;
    $entry = new \DaoMongodb\NewsEntry;
    $list = $mgo->GetnNewsList($filter,$sortby);
    $shop_name = \Cache\Shop::Get($shop_id)->shop_name;
    foreach ($list as &$item) {
        if(0 == $item->is_send && $item->send_time <= $time){
            $item->is_send  = 1;
            $entry->news_id = $item->news_id;
            $entry->is_send = 1;
            $ret = $mgo->Save($entry);
        }
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
    $page_num   = $_['page_num'];
    $page_index = $_['page_index'];
    $shop_id    = $_['shop_id'];
    $sort_name  = $_['sort_name'];
    $desc       = $_['desc'];
    $srctype    = $_['srctype'];
    if($srctype == NewSrcType::SHOUYINJI)
    {
        ShopPermissionCheck::PageCheck(ShopPermissionCode::SY_SEE_SYSTEM);
    }else{
        ShopPermissionCheck::PageCheck(ShopPermissionCode::SEE_SYSTEM);
    }

    if(!$page_num)
    {
        $page_num = 10;//如果没有传默认10条
    }
    if(!$page_index)
    {
        $page_index = 1; //第一页开始
    }
    if(!$shop_id)
    {
        $shop_id   = \Cache\Login::GetShopId();
    }
    if(!$shop_id)
    {
        LogErr("no shop id");
        return errcode::SHOP_NOT_WEIXIN;
    }

    if ($sort_name)
    {
        $sort[$sort_name] = (int)$desc;
    }
    $total   = 0;
    $mongodb = new \DaoMongodb\NewsReady;
    $list    = $mongodb->GetNewsReadyByShopId($shop_id, $sort,$page_index,$page_num,$total);
    $mgo     = new \DaoMongodb\News;
    //遍历出的数据都对PC端无影响
    foreach ($list as $i => &$item) {
        $info = $mgo->GetNewsById($item->news_id);
        $item->send_username = '欣吃货官方';
        $item->title         = $info->title;
        $item->content       = strip_tags($info->content);
        $item->url           = Cfg::instance()->GetApiDomain()."?opr=app_new_url&srctype=2&news_id={$item->news_id}";
    }
    //当前页数
    $total_page = ceil($total/$page_num);
    $resp = (object)array(
        'page_index' => $page_index,
        'total_page' => $total_page,
        'total_num'  => $total,
        'list'       => $list,
    );
    LogInfo("--ok--");
    return 0;
}

function GetShopNewsNum(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id  = \Cache\Login::GetShopId();
    $day      = date('Ymd',time());
    $mgo      = new \DaoMongodb\StatNews;
    $send_num = $mgo->GetNewsStatByDay($shop_id, $day)->send_num;
    $max_num  = ShopNewsDay::NUM;
    $num      = $max_num - (int)$send_num;

    $resp = (object)array(
        'num' => $num
    );
    LogInfo("--ok--");
    return 0;
}

//外包网页详情动态内容
function GetNewsContent(&$resp)
{
    $_ = $_REQUEST;
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $news_id   = $_['news_id'];
    if(!$news_id)
    {
        return errcode::NEWS_ID_NOT_EP;
    }
    $mgo       = new \DaoMongodb\News;
    $info      = $mgo->GetNewsById($news_id);
    if(!$info->content)
    {
        return errcode::NEWS_NUM_MAX;
    }
    $resp = (object)array(
        'content' => $info->content,
    );
    //LogInfo($_);
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
elseif(isset($_["shop_news_num"]))
{
    $ret = GetShopNewsNum($resp);
}
elseif(isset($_["get_news_content"]))  // 注，当由index.php中require(...)调用过来时，这里的$_是Input()中定义的局部变量
{
    $ret = GetNewsContent($resp);
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
//var_dump($GLOBALS['need_json_obj']);
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

