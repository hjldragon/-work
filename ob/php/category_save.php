<?php
/*
 * [Rocky 2017-05-04 00:25:54]
 * 餐品类别信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_category.php");
require_once("redis_id.php");
require_once("mgo_menu.php");

Permission::PageCheck($_['srctype']);

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
    $type          = $_['type'];
    $opening_time  = json_decode($_['opening_time']);
    $parent_id     = $_['parent_id'];
    $shop_id       = $_['shop_id'];
    if(!$shop_id)
    {
        $shop_id       = \Cache\Login::GetShopId();
    }
    if(!$category_name || !$opening_time){
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    if(!$parent_id){
        $parent_id = 0;
    }
    $mongodb = new \DaoMongodb\Category;
    $time    = array();
    $cate_info   = $mongodb->QueryByName($category_name, $shop_id);
    if($cate_info->category_name && $category_id != $cate_info->category_id)//在新建的情况下要判断
    {
        LogDebug($category_name,'this category_name have:'.$cate_info->category_name);
        return errcode::NAME_IS_EXIST;
    }
    if(!$category_id)
    {
        $category_id = \DaoRedis\Id::GenCategoryId();
        $entry_time  = time();

    }else{
        $list = $mongodb->GetByParentList($category_id);
        if($list){
            if(!in_array(OpenTime::MORNING, $opening_time)){
                array_push($time, OpenTime::MORNING);
            }
            if(!in_array(OpenTime::NOON, $opening_time)){
                array_push($time, OpenTime::NOON);
            }
            if(!in_array(OpenTime::NIGHT, $opening_time)){
                array_push($time, OpenTime::NIGHT);
            }
            if(!in_array(OpenTime::SUPPER, $opening_time)){
                array_push($time, OpenTime::SUPPER);
            }
            if($time){
                $res = $mongodb->UpdateOpenTime($category_id,$time);
                if(0 != $res){
                    LogErr("opening_time update err");
                    return errcode::DB_OPR_ERR;
                }
            }
        }
    }
    
    //通过登录来获取shop_id
    $shop_id = \Cache\Login::GetShopId();

    $entry   = new \DaoMongodb\CategoryEntry;

    $entry->category_id   = $category_id;
    $entry->category_name = $category_name;
    $entry->shop_id       = $shop_id;
    //$entry->printer_id    = $printer_id;
    $entry->type          = $type;
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
    $menumgo = new \DaoMongodb\MenuInfo;
    $cond = [
        
        'cate_id_list' => $category_id_list
    ];
    $shop_id = \Cache\Login::GetShopId();
    $list = $menumgo->GetFoodList($shop_id, $cond);
    if(count($list)>0){
        LogErr("Delete err");
        return errcode::CATE_NOT_DEL;
    }
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
    $mgo  = new \DaoMongodb\Category;
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
if(isset($_['save']))
{
    $ret = SaveCategory($resp);

}

else if(isset($_['delete']))
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
