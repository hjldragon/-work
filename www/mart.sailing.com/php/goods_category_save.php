<?php
/*
 * [Rocky 2017-05-04 00:25:54]
 * 餐品类别信息保存类
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_category.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods.php");
use \Pub\Mongodb as Mgo;


function SaveGoodsCategory(&$resp)
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
    $cate_type     = $_['cate_type'];
    if(!$category_name){
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo = new Mgo\GoodsCategory;
    $info = $mgo->GetCateByName($category_name);
    if($info->category_id && $info->category_id != $category_id)
    {
        LogErr("category_name:[$category_name] exist");
        return errcode::NAME_IS_EXIST;
    }
    if(!$category_id)
    {
        $category_id = \DaoRedis\Id::GenGoodsCategoryId();
        $ctime = time();
    }

    $entry   = new Mgo\GoodsCategoryEntry;

    $entry->category_id   = $category_id;
    $entry->category_name = $category_name;
    $entry->parent_id     = $parent_id;
    $entry->cate_type     = $cate_type;
    $entry->ctime         = $ctime;

    $ret = Mgo\GoodsCategory::My()->Save($entry);
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
    $mongodb = new Mgo\GoodsCategory;
    $category_id_list = array();
    getTree($category_id_list,$category_id);

    $goods = new Mgo\Goods;
    $cond = [
        'cate_id_list' => $category_id_list
    ];
    $list = $goods->GetGoodsList($cond);
    if(count($list)>0){
        LogErr("Delete err");
        return errcode::CATE_NOT_DEL;
    }
    $ret = $mongodb->BatchDelete($category_id_list);
    //LogDebug($ret);

    if(0 != $ret){
        LogErr("delete err");
        return errcode::SYS_ERR;
    }
    $resp=(object)array(
    );
    LogInfo("delete ok");
    return 0;

}

//递归查找子级品类中foods_id_list
function getTree(&$category_id_list,$parent_id)
{
    array_push($category_id_list, $parent_id);
    $mgo  = new Mgo\GoodsCategory;
    $info = $mgo->GetByParentList($parent_id);
    if(!$info)
    {
      return;
    }
    foreach ($info as $key => $value) {
        getTree($category_id_list,$value->category_id);
    }
}



$ret = -1;
$resp = (object)array();
if(isset($_['goods_cate_save']))
{
    $ret = SaveGoodsCategory($resp);

}
else if(isset($_['goods_cate_delete']))
{
    $ret = DeleteCategory($resp);

}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
LogDebug($ret);

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
LogDebug($html);
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
