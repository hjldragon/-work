<?php
/*
 * [Rocky 2017-05-04 00:25:54]
 * 餐品类别信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_goods_category.php");
require_once("redis_id.php");
require_once("mgo_goods.php");

//Permission::PageCheck();

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
    $type          = $_['type'];
    $parent_id     = $_['parent_id'];//<<<<<<用于多级分类,这里只用了一级
    if(!$category_name){
        LogErr("no category_name");
        return errcode::PARAM_ERR;
    }
    if(!$parent_id){
        $parent_id = 0;
    }
    $mongodb   = new \DaoMongodb\GoodsCategory;
    $entry     = new \DaoMongodb\GoodsCategoryEntry;
    $cate_info = $mongodb->QueryByName($category_name);
    if($cate_info->category_name)
    {
        LogDebug($category_name,'this category_name have:'.$cate_info->category_name);
        return errcode::NAME_IS_EXIST;
    }
    if(!$category_id)
    {
        $category_id = \DaoRedis\Id::GenGoodsCategoryId();
    }


    $entry->category_id   = $category_id;
    $entry->category_name = $category_name;
    $entry->type          = $type;
    $entry->parent_id     = $parent_id;
    $entry->delete        = 0;
    $entry->entry_time    = time();
    $ret = $mongodb->Save($entry);
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

function DeleteGoodsCategory(&$resp){

    $_=$GLOBALS["_"];
    if(!$_){
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $category_id = $_['category_id'];
    $mongodb     = new \DaoMongodb\GoodsCategory;

    $category_id_list = array();
    getTree($category_id_list,$category_id);
    $menumgo = new \DaoMongodb\Goods;
    $cond = [
        'cate_id_list' => $category_id_list
    ];

    $total = 0;
    $list  = $menumgo->GetByCateForGoodsList($cond, $total);
    if($total>0){
        LogErr("Delete err");
        return errcode::CATE_NOT_DEL;
    }

    $ret = $mongodb->BatchDeleteById($category_id_list);
    if(0 != $ret){
            LogErr("delete err");
            return errcode::SYS_ERR;
    }
    $resp=(object)array();
    LogInfo("delete ok");
    return 0;

}

//递归查找子级品类中goods_id_list
function getTree(&$category_id_list,$parent_id)
{   
    array_push($category_id_list, $parent_id);
    $mgo  = new \DaoMongodb\Goods;
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
if(isset($_['save_goods_category']))
{
    $ret = SaveGoodsCategory($resp);

}elseif(isset($_['delete_goods_category']))
{
    $ret = DeleteGoodsCategory($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
