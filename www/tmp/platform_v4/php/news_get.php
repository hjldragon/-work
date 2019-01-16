<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取消息类别信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("mgo_news.php");
require_once("mgo_stat_news_byday.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
function GetNewsInfo(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::NEW_INFO_INFO);
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
    $page_size        = $_['page_size'];
    $page_no          = $_['page_no'];
    $send_type        = $_['send_type'];
    $pl_name          = $_['pl_name'];
    $business_status  = $_['business_status'];
    $sort_name        = $_['sort_name'];
    $desc             = $_['desc'];
    if($business_status || $send_type || $pl_name)
    {
        PlPermissionCheck::PageCheck(PlPermissionCode::NEW_SEEK);
    }

    switch ($sort_name) {
        case 'news_id':
            $sort['_id'] = (int)$desc;
            break;
        case 'send_time':
            $sort['send_time'] = (int)$desc;
            break;
        default:
            break;
    }
    if(!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if(!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    if($sort_name)
    {
        $sort[$sort_name] = (int)$desc;
    }
    $total = 0;
    $mgo     = new \DaoMongodb\News;
    $pl_mgo  = new \DaoMongodb\Platformer;
    $pl_info = $pl_mgo->QueryByPlName($pl_name);
    $list    = $mgo->GetnNewsList(
        [
            'platfomer_id'     => $pl_info->platformer_id,
            'send_type'        => $send_type,
            'is_system'        => 1,
            'business_status'  => $business_status,
        ],
        $sort,
        $page_size,
        $page_no,
        $total
    );
    foreach ($list as &$v)
    {
       $name_info    = $pl_mgo->QueryById($v->platfomer_id);
       $v->send_name = $name_info->pl_name;
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

function GetSysNewsInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $news_id = (string)$_['news_id'];
    if(!$news_id)
    {
        LogErr("no news_id");
        return errcode::PARAM_ERR;
    }
    $mgo  = new \DaoMongodb\News;
    $info = $mgo->GetNewsById($news_id);

    $resp = (object)array(
        'info' => $info
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
    $shop_id   = \Cache\Login::GetShopId();
    $day = date('Ymd',time());
    $mgo = new \DaoMongodb\StatNews;
    $send_num = $mgo->GetNewsStatByDay($shop_id, $day)->send_num;
    $max_num = ShopNewsDay::NUM;
    $num = $max_num - (int)$send_num;
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
    LogInfo($_);
    LogInfo("--ok--");
    return 0;
}

//获取代理商系统公告列表
function GetAgentSysNewsList(&$resp)
{
    AgPermissionCheck::PageCheck(AgentPermissionCode::SEE_NEW);
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $page_size  = $_['page_size'];
    $page_no    = $_['page_no'];
    $end_time   = $_['end_time'];
    $begin_time = $_['begin_time'];
    $sort_name  = $_['sort_name'];
    $desc       = $_['desc'];
    $agent_id   = $_['agent_id'];
    if(!$agent_id)
    {
        LogErr("no agent_id");
        return errcode::PARAM_ERR;
    }
    if($begin_time || $end_time)
    {
        AgPermissionCheck::PageCheck(AgentPermissionCode::NEW_SEEK);
    }

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
    if($sort_name)
    {
        $sort[$sort_name] = (int)$desc;
    }

    $total   = 0;
    $mgo     = new \DaoMongodb\NewsReady;
    $list    = $mgo->GetAgentNewsList(
        [
            'agent_id'  => $agent_id,
            'end_time'  => $end_time,
            'begin_time'=> $begin_time
        ],
        $sort,
        $page_size,
        $page_no,
        $total
    );
    $mongodb = new \DaoMongodb\News;
    foreach ($list as &$v)
    {
        $news_info    = $mongodb->GetNewsById($v->news_id);
        $v->send_name = '欣吃货官方';
        $v->title     = $news_info->title;
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
//获取代理商详情内容信息并标记为已读
function GetAgentNewsInfo(&$resp)
{


    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $news_id   = $_['news_id'];
    $agent_id  = $_['agent_id'];
    $read      = $_['read'];
    if($read)
    {
        AgPermissionCheck::PageCheck(AgentPermissionCode::READ_NEW);
    }else{
        AgPermissionCheck::PageCheck(AgentPermissionCode::NEW_INFO);
    }

    $news_ids  = [$news_id];

    $mgo  = new \DaoMongodb\News;
    $info = $mgo->GetNewsById($news_id);
    $info->send_username = '欣吃货官方';

    $mongodb = new \DaoMongodb\NewsReady;
    $ready = $mongodb->AgentReadyNews($agent_id, $news_ids);
    if(0 != $ready)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
        'info' => $info
    );
    LogDebug($resp);
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
}elseif(isset($_["sysnews_info"]))
{
    $ret = GetSysNewsInfo($resp);
}
elseif(isset($_["shop_news_num"]))
{
    $ret = GetShopNewsNum($resp);
}
elseif(isset($_["get_news_content"]))  // 注，当由index.php中require(...)调用过来时，这里的$_是Input()中定义的局部变量
{
    $ret = GetNewsContent($resp);
}elseif(isset($_["get_agent_news_list"]))
{
    $ret = GetAgentSysNewsList($resp);
}
elseif(isset($_["agent_news_info"]))
{
    $ret = GetAgentNewsInfo($resp);
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

