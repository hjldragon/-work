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
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");


function GetCategoryInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $category_id = (string)$_['category_id'];
    $info = \Cache\Category::Get($category_id);

    $resp = (object)array(
        'info' => $info
    );
   // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetCategoryList(&$resp)
{
    ShopPermissionCheck::PageCheck(ShopPermissionCode::SEE_CATEGORY);
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $type = $_['type'];

    $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Category;
    $list = $mgo->GetList($shop_id, $type);

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
    $tree = '';
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

function GetCategoryTypeList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $type = $_['type'];

    $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Category;
    $cate = $mgo->GetByTypeList($shop_id,$type);
    $data->category_id = $cate->category_id;
    $data->category_name = $cate->category_name;
    $data->list = array();

    getCategoryId($data,$cate->category_id);
    $resp = (object)array(
        'list' => $data
    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

//递归查找子级品类
function getCategoryId(&$data,$parent_id)
{
    $mgo = new \DaoMongodb\Category;
    $info = $mgo->GetByParentList($parent_id);
    if($info){
        $data->list = $info;
    }else{
        $data->list = array();
    }
    foreach ($info as $key => &$value) {
        getCategoryId($value,$value->category_id);
    }
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
elseif(isset($_["type_list"]))
{
    $ret = GetCategoryTypeList($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
