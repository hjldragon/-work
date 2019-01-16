<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取餐品类别信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_category.php");
use \Pub\Mongodb as Mgo;

function GetGoodsCategoryInfo(&$resp)
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
    $mgo  = new Mgo\GoodsCategory;
    $info =$mgo->GetCategoryById($category_id);

    $resp = (object)array(
        'info' => $info
    );
   // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetGoodsCategoryList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo  = new Mgo\GoodsCategory;
    $list = $mgo->GetList();
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
if(isset($_["goods_category_info"]))
{
    $ret = GetGoodsCategoryInfo($resp);
}
elseif(isset($_["goods_category_list"]))
{
    $ret = GetGoodsCategoryList($resp);
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
LogDebug($html);
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
