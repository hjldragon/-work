<?php
/*
 * [Rocky 2017-05-04 11:48:01]
 * 取打印机信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_printer.php");
require_once("mgo_category.php");
// $_=$_REQUEST;

function GetPrinterInfo(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $printer_id = $_['printer_id'];

    $mgo = new \DaoMongodb\Printer;
    $info = $mgo->GetPrinterById($printer_id);
    $info->category = array();
    foreach ($info->food_category_list as $key => $value) {
        $cateinfo = \Cache\Category::Get($value);
        $data = array();
        array_push($data, $cateinfo);
        GetCategory($data,$cateinfo->parent_id);
        array_push($info->category, $data);
    }
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
    if($info){
        array_unshift($data, $info);
    }
    if($info->parent_id){
        GetCategory($data,$info->parent_id);
    }
}

function GetPrinterList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Printer;
    $list = $mgo->GetList($shop_id);

    foreach($list as $i => &$item)
    {
        $category_name = [];
        foreach($item->food_category_list as $j => $category_id)
        {   
            if(0 == $category_id){
                $category_name[] = '全部';
            }else{
               $category_name[] = \Cache\Category::Get($category_id)->category_name; 
            }
            
        }
        $item->food_category_name = $category_name;
    }

    $resp = (object)array(
        'list' => $list
    );
    // LogDebug($resp);//所有打印机列表
    LogInfo("--ok--");
    return 0;
}

// 需要在当前选择打印机上可印的餐品类别
function NeedPrintFoodCategory(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $printer_id = $_['printer_id'];

    $exist = [];
    $printer_info = \Cache\Printer::Get($printer_id);
    if(null != $printer_info && null != $printer_info->food_category_list)
    {
        foreach($printer_info->food_category_list as $i => $food_category)
        {
            $exist[$food_category] = true;
        }
    }

    $shop_id = \Cache\Login::GetShopId();

    $list = \Cache\Category::GetCategoryList($shop_id);
    foreach($list as $i => &$v)
    {
        if($exist[$v->category_id])
        {
            $v->selected = true;
        }
    }

    $resp = (object)array(
        'list' => $list
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["printer_info"]))
{
    $ret = GetPrinterInfo($resp);
}
elseif(isset($_["printer_list"]))
{
    $ret = GetPrinterList($resp);
}
elseif(isset($_["need_print_food_category"]))
{
    $ret = NeedPrintFoodCategory($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
