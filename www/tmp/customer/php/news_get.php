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
require_once("mgo_shop.php");
//$_=$_REQUEST;
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
    $time = time();
    $mgo = new \DaoMongodb\News;
    $entry = new \DaoMongodb\NewsEntry;
    $list = $mgo->GetnNewsList($filter);
    $shop_name = \Cache\Shop::Get($shop_id)->shop_name;
    foreach ($list as &$item) {
        if(0 == $item->is_send && $item->send_time <= $time){
            $item->is_send = 1;
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

function GetCustomerList(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $customer_id = $_['customer_id'];
    $shop_id     = $_['shop_id'];
    $mongodb     = new \DaoMongodb\NewsReady;
    $list        = $mongodb->GetNewsByCustomerId($customer_id);
    $mgo         = new \DaoMongodb\News;
    $shop        = new \DaoMongodb\Shop;
    $shop_info   = $shop->GetShopById($shop_id);
    $list_all = [];
    $item = [];
    foreach ($list as $i => $v) {
        $info          = $mgo->GetNewsById($v->news_id);
        $item['title']   = $info->title;
        $item['content'] = $info->content;
        $item['time']    = $v->ctime;
        $item['img']     = $shop_info->shop_logo;
        array_push($list_all,$item);

    }

    $resp = (object)array(
        'messageinfo' => $list_all
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
elseif(isset($_["shop_news_num"]))
{
    $ret = GetShopNewsNum($resp);
}elseif(isset($_["get_customer_news"]))
{
    $ret = GetCustomerList($resp);
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
//LogDebug($html);
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
