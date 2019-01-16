<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取餐桌位置信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/mgo_city.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;
PlPermissionCheck::PageCheck(PlPermissionCode::AGENT_AREA_SET);
//获取所有列表
function GetCityList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $city_level   = $_['city_level'];
    $page_size    = $_['page_size'];
    $page_no      = $_['page_no'];
    $sort_name    = $_['sort_name'];
    $desc         = $_['desc'];
    //排序字段
    if (!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if (!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    switch ($sort_name) {
        case 'city_name':
            $sort['_id']   = (int)$desc;
            break;
        default:
            break;
    }
    $total        = 0;
    $mgo          = new Mgo\City;
    $list         = $mgo->GetList(['city_level'=>$city_level], $page_size, $page_no,$sort,
    $total);
    $page_all = ceil($total/$page_size);//总共页数
    $resp = (object)array(
        'city_list' => $list,
        'total'     => $total,
        'page_all'  => $page_all,
        'page_no'   => $page_no
    );

    LogInfo("--ok--");
    return 0;
}

function GetCityInfo(&$resp)
{
    $_ = $GLOBALS["_"];

    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $city_name = $_['city_name'];
    if(!$city_name)
    {
        LogErr("city_name is empty");
        return errcode::PARAM_ERR;
    }
    $mgo          = new Mgo\City;
    $info         = $mgo->GetCityByName($city_name);


    $resp = (object)array(
        'city_info' => $info,
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["get_city_list"]))
{
    $ret = GetCityList($resp);
}elseif(isset($_["get_city_info"]))
{
    $ret = GetCityInfo($resp);
}

$result = (object)array(
    'ret' => $ret,
    'data' => $resp
);

if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}
?><?php /******************************以下为html代码******************************/?>

