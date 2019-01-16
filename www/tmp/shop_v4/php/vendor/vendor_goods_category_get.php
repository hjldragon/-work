<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取商品类别信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods_category.inc");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

function GetVGCategoryInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $category_id = (string)$_['category_id'];
    if(!$category_id)
    {
        LogErr("no category_id");
        return errcode::PARAM_ERR;
    }
    $mgo  = new VendorMgo\VendorGoodsCategory;
    $info =$mgo->GetCategoryById($category_id);

    $resp = (object)array(
        'info' => $info
    );
   // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetVGCategoryList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = $_['shop_id'];
    if(!$shop_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo  = new VendorMgo\VendorGoodsCategory;
    $list = $mgo->GetListByShop($shop_id);

    $data = getTree($list,"0");

    $resp = (object)array(
        'list' => $data
    );
    LogInfo("--ok--");
    return 0;
}
//树形排序
function getTree($data, $pId)
{
    $tree = [];
    foreach($data as $k => $v)
    {
      if($v->parent_id == $pId)
      {        //父亲找到儿子
       $v->list = getTree($data, $v->category_id);
       if(!$v->list){
        $v->list = array();
       }
       $tree[] = $v;
      }
    }
    return $tree;
}


$ret = -1;
$resp = (object)array();
if(isset($_["vg_category_info"]))
{
    $ret = GetVGCategoryInfo($resp);
}
elseif(isset($_["vg_category_list"]))
{
    $ret = GetVGCategoryList($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

\Pub\PageUtil::HtmlOut($ret, $resp);