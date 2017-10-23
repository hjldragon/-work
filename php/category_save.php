<?php
/*
 * [Rocky 2017-05-04 00:25:54]
 * 餐品类别信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_category.php");
require_once("redis_id.php");

function SaveCategory(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    if(!PageUtil::LoginCheck())
    {
        LogDebug("not login, token:{$_['token']}");
        return errcode::USER_NOLOGIN;
    }

    $category_id   = $_['category_id'];
    $category_name = $_['category_name'];
    //$printer_id    = $_['printer_id'];
    $type          = $_['type'];
    //$food_id_list  = json_decode($_['food_id_list']);
    $opening_time  = json_decode($_['opening_time']);
    $parent_id     = $_['parent_id'];
    if(!$category_name){
        LogErr("category_name err");
        return errcode::PARAM_ERR;
    }
    if(!$parent_id){
        $parent_id = 0;
    }
    if(!$category_id)
    {
        $category_id = \DaoRedis\Id::GenCategoryId();
        $entry_time = time();
    }
    
    //通过登录来获取shop_id
    $shop_id = \Cache\Login::GetShopId();

    $mongodb = new \DaoMongodb\Category;
    $entry   = new \DaoMongodb\CategoryEntry;

    $entry->category_id   = $category_id;
    $entry->category_name = $category_name;
    $entry->shop_id       = $shop_id;
    $entry->printer_id    = $printer_id;
    $entry->type          = $type;
    //$entry->food_id_list  = $food_id_list;
    $entry->opening_time  = $opening_time;
    $entry->parent_id     = $parent_id;
    $entry->delete        = 0;
    $entry->entry_type    = 2;
    $entry->entry_time    = $entry_time;

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

function DeleteCategory(&$resp){
    //获取删除数据的全局变量
    //如果没有数据，提示错误信息
    $_=$GLOBALS["_"];
    LogDebug($_);
    if(!$_){
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $category_id = $_['category_id'];
    LogDebug($category_id);
    
    $mongodb = new \DaoMongodb\Category;
    $category_id_list = array();
    getTree($category_id_list,$category_id);

    LogDebug($category_id_list);
    $ret = $mongodb->BatchDeleteById($category_id_list);
    LogDebug($ret);
    
        if(0 != $ret){
            LogErr("delete err");
            return errcode::SYS_ERR;
        }
        $resp=(object)array(
        );
        LogInfo("delete ok");
        return 0;

}

//递归查找子级品类中food_id_list
function getTree(&$category_id_list,$parent_id)
{   
    array_push($category_id_list, $parent_id);
    $mgo = new \DaoMongodb\Category;
    $info = $mgo->GetByParentList($parent_id);
    if(!$info){
      return;
    }
    foreach ($info as $key => $value) {
        getTree($category_id_list,$value->category_id);
    }
}

$ret = -1;
$resp = (object)array();
if(isset($_['save']))
{
    $ret = SaveCategory($resp);

}
//HJL设置测试删除方法
else if(isset($_['delete']))
{
    $ret=DeleteCategory($resp);

}
LogDebug($ret);

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
LogDebug($html);
?><?php /******************************以下为html代码******************************/?>
<?=$html?>