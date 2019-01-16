<?php
/*
 * [Rocky 2017-05-04 00:25:54]
 * 餐品类别信息保存类
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods_category.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;


function SaveVGCategory(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $category_id   = $_['category_id'];
    $category_name = $_['category_name'];
    $parent_id     = $_['parent_id'];
    $shop_id       = $_['shop_id'];
    if(!$category_name){
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo = new VendorMgo\VendorGoodsCategory;
    $info = $mgo->GetCateByName($category_name, $shop_id);
    if($info->category_id && $info->category_id != $category_id)
    {
        LogErr("category_name:[$category_name] exist");
        return errcode::NAME_IS_EXIST;
    }
    if(!$category_id)
    {
        $category_id = \DaoRedis\Id::GenVGCategoryId();
        $ctime = time();
    }

    $entry   = new VendorMgo\VendorGoodsCategoryEntry;

    $entry->category_id   = $category_id;
    $entry->category_name = $category_name;
    $entry->parent_id     = $parent_id;
    $entry->shop_id       = $shop_id;
    $entry->ctime         = $ctime;

    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

function DeleteCategory(&$resp){

    $_=$GLOBALS["_"];
    LogDebug($_);
    if(!$_){
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $category_id = $_['category_id'];
    $mongodb = new VendorMgo\VendorGoodsCategory;

    $goods = new VendorMgo\VendorGoods;
    $cond = [
        'category_id' => $category_id
    ];
    $list = $goods->GetVendorGoodsList($cond);

    if(count($list)>0){
        LogErr("Delete err");
        return errcode::CATE_NOT_DEL;
    }
    $category_id_list = [];
    array_push($category_id_list, $category_id);
    $ret = $mongodb->BatchDelete($category_id_list);


    if(0 != $ret){
        LogErr("delete err");
        return errcode::SYS_ERR;
    }
    $resp=(object)array(
    );
    LogInfo("delete ok");
    return 0;

}




$ret = -1;
$resp = (object)array();
if(isset($_['vg_cate_save']))
{
    $ret = SaveVGCategory($resp);
}
else if(isset($_['vg_cate_delete']))
{
    $ret = DeleteCategory($resp);

}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
LogDebug($ret);
\Pub\PageUtil::HtmlOut($ret, $resp);



