<?php
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_shop.php");
require_once("mgo_evaluation.php");

function GetShopInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = (string)$_['shop_id'];
    if (!$shop_id)
    {
        $shop_id = \Cache\Login::GetShopId();
    }
    LogDebug("shop_id:[$shop_id]");

    //获取店铺信息
    $shopinfo = [];


    $shop_info = \Cache\Shop::Get($shop_id);

    $shopinfo['shop_id'] = $shop_info->shop_id;
    $shopinfo['shop_name'] = $shop_info->shop_name;
    $shopinfo['classify_name'] = $shop_info->classify_name;
    $shopinfo['telephone'] = $shop_info->telephone;
    $shopinfo['address'] = $shop_info->address;
    $shopinfo['open_time'] = $shop_info->open_time;
    $shopinfo['img_list'] = $shop_info->img_list;
    //<<<<<<<<<<<<<<<<<<<<<<<下面现在制作的都是假数据
    $customer_info = [];
    $customer_info['customer_id'] = 1033;
    $customer_info['customer_name'] = "张三";
    $customer_info['customer_portrait'] = "2440db927309767d2307b20317fabfcd.jpeg";

    $evaluation = [];
    $evaluation_list =[];
    $evaluation['customer_info'] = $customer_info;
    $evaluation['content'] = "很好吃";
    $evaluation['ctime'] = 1504523024;
    $evaluation['good_rate'] = 0.821;
    array_push($evaluation_list,$evaluation);
    $shopinfo['evaluation'] = $evaluation_list;


    $resp = (object)array(
        'shopinfo' => $shopinfo,
    );
    //var_dump($resp);
    //die;
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if (isset($_["get_shop_info"]))
{
    $ret = GetShopInfo($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp,//<<<<<<<未加密返回数据
    //'crypt' => 1, // 是加密数据标记
    //'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>