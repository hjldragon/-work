<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 *商品信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_goods.php");


function GetGoodsInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $goods_id = $_['goods_id'];
    if(!$goods_id)
    {
        LogErr("no goods_id");
        return errcode::PARAM_ERR;
    }
    $mgo  = new \DaoMongodb\Goods;
    $info = $mgo->GetgoodsinfoById($goods_id);
    $resp = (object)array(
        'info' => $info
    );
   // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetGoodsList(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $category_id = $_['category_id'];
    $goods_name  = $_['goods_name'];
    $sale_off    = $_['sale_off'];
    $page_size   = $_['page_size'];
    $page_no     = $_['page_no'];
    $sort_name   = $_['sort_name'];
    $desc        = $_['desc'];   //(1:正序,-1:倒序)
    if(!$category_id)
    {
        LogErr("no goods_category");
        return errcode::NO_CATE;
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
    $mgo = new \DaoMongodb\Goods;
    $total = 0;
    $list = $mgo->GetGoodsList(
        [
            'category_id' => $category_id,
            'goods_name'  => $goods_name,
            'sale_off'    => $sale_off,
            ],
        $page_size,
        $page_no,
        $sort,
        $total
    );

    $resp = (object)array(
        'list'      => $list,
        'total'     => $total,
        'page_size' => $page_size,
        'page_no'   =>$page_no
    );
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_["goods_info"]))
{
    $ret = GetGoodsInfo($resp);
}
elseif(isset($_["goods_list"]))
{
    $ret = GetGoodsList($resp);
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
?><?php /******************************以下为html代码******************************/?>

