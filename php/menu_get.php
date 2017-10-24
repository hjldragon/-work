<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取餐品信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_menu.php");
require_once("mgo_category.php");
require_once("mgo_spec.php");
require_once("mgo_stat_food_byday.php");



// 取餐品已售数
function GetTodayFoodSoldNum($food_id)
{
    $mgo_stat = new \DaoMongodb\StatFood;
    $today = date("Ymd");
    $info = $mgo_stat->GetFoodStatByDay($food_id, $today);
    //LogDebug($info);
    return $info->sold_num?:0;
}

function GetFoodInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $food_id = (string)$_['food_id'];

    $mgo = new \DaoMongodb\MenuInfo;
    $info = $mgo->GetFoodinfoById($food_id);
    if($info->food_id){
        $cateinfo = \Cache\Category::Get($info->category_id);
        $info->category = array();
        $data = array();
        array_push($data, $cateinfo);
        GetCategory($data,$cateinfo->parent_id);
        array_push($info->category, $data);
        $using = $info->food_price->using;
        $price = array();
        if($using & PriceType::ORIGINAL){
            array_push($price, PriceType::ORIGINAL);
        }
        if($using & PriceType::DISCOUNT){
            array_push($price, PriceType::DISCOUNT);
        }
        if($using & PriceType::VIP){
            array_push($price, PriceType::VIP);
        }
        if($using & PriceType::FESTIVAL){
            array_push($price, PriceType::FESTIVAL);
        }
        $info->food_price->using = $price;
    }else{
        $info = array();
    }
    

    //$info->food_sold_num_day = GetTodayFoodSoldNum($food_id);<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<销量暂没做

    $resp = (object)array(
        'info' => $info
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//递归查找父级品类
function GetCategory(&$data,$parent_id){
    $info = \Cache\Category::Get($parent_id);
    array_unshift($data, $info);
    if($info->parent_id){
        GetCategory($data,$info->parent_id);
    }
}
//递归查找子级品类id
function getTree(&$cate_list,$parent_id)
{   
    $mgo = new \DaoMongodb\Category;
    $info = $mgo->GetByParentList($parent_id);
    if(!$info){
      array_push($cate_list,$parent_id);
      return;
    }
    foreach ($info as $key => $value) {
        getTree($cate_list,$value->category_id);
    }

}

function GetFoodList(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $category_id = $_['category_id'];
    $food_name   = $_['food_name'];
    $sale_off    = $_['sale_off'];
    $is_draft    = $_['is_draft'];
    $type        = $_['type'];
    $page_size   = $_['page_size'];
    $page_no     = $_['page_no'];
    if(!$page_size)
    {

        $page_size = 10;//如果没有传默认10条
    }
    if(!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    $shop_id = \Cache\Login::GetShopId();
    
    LogDebug("shop_id:[$shop_id]");
    if($category_id){
        $cate_list = array();
        $cateinfo = \Cache\Category::Get($category_id);
        getTree($cate_list,$cateinfo->category_id);
        //数组去重
        $cate_id_list = array_unique($cate_list);
        if(!$cate_id_list){
            $cate_id_list = array('0');
        }
    }
    $cond = [
        'food_name'    => $food_name,
        'cate_id_list' => $cate_id_list,
        'sale_off'     => $sale_off,
        'is_draft'     => $is_draft,
        'type'         => $type
    ];

    $mgo = new \DaoMongodb\MenuInfo;
    $total = 0;
    $list = $mgo->GetFoodList($shop_id, $cond, $page_size, $page_no,[],$total);

    LogDebug($list);
    foreach($list as $i => &$item)
    {
        $item->category_name = \Cache\Category::Get($item->category_id)->category_name;
        $item->food_sold_num_day = GetTodayFoodSoldNum($item->food_id);
    }
    
    $resp = (object)array(
        'list' => $list,
        'total' => $total
    );
     LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["foodinfo"]))
{
    $ret = GetFoodInfo($resp);
}
elseif(isset($_["foodlist"]))
{
    $ret = GetFoodList($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
//LogDebug($resp);
$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
//LogDebug($html);
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
