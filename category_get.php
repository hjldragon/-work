<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取餐品类别信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_category.php");

// $_=$_REQUEST;

function GetCategoryInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $category_id = (string)$_['category_id'];

    $mgo = new \DaoMongodb\Category;
    $info = $mgo->GetCategoryById($category_id);

    $resp = (object)array(
        'info' => $info
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetCategoryList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Category;
    $list = $mgo->GetList($shop_id);
    $resp = (object)array(
        'list' => $list
    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_["info"]))
{
    $ret = GetCategoryInfo($resp);
}
elseif(isset($_["list"]))
{
    $ret = GetCategoryList($resp);
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
