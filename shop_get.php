<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取店铺信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_shop.php");

// $_=$_REQUEST;

function GetShopInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = (string)$_['shop_id'];

    $mgo = new \DaoMongodb\Shop;
    $info = $mgo->GetShopById($shop_id);

    $resp = (object)array(
        'info' => $info
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetShopList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = (string)$_['shop_id'];
    $shop_name = $_['shop_name'];

    $mgo = new \DaoMongodb\Shop;
    $list = $mgo->GetShopList(['shop_id'=>$shop_id, 'shop_name'=>$shop_name]);
    $resp = (object)array(
        'list' => $list
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_["shopinfo"]))
{
    $ret = GetShopInfo($resp);
}
elseif(isset($_["shoplist"]))
{
    $ret = GetShopList($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
