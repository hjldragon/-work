<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取资源类别信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cfg.php");
require_once("mgo_resources.php");
require_once("mgo_shop.php");
require_once("mgo_employee.php");
use \Pub\Mongodb as Mgo;

function GetResourcesList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_name        = $_['shop_name'];
    $resources_id     = $_['resources_id'];
    $agent_id         = $_['agent_id'];
    $resources_type   = $_['resources_type'];
    $login_begin_time = $_['login_begin_time'];
    $login_end_time   = $_['login_end_time'];
    $page_size        = $_['page_size'];
    $page_no          = $_['page_no'];
    $shop_mgo = new \DaoMongodb\Shop;
    $em_mgo = new \DaoMongodb\Employee;

    if($agent_id)
    {
        $shop_list = $shop_mgo->GetShopTotal(['agent_id'=>$agent_id]);
        $shop_id_list = [];
        foreach ($shop_list as  $value)
        {
            array_push($shop_id_list, $value->shop_id);
        }
        if(count($shop_id_list)==0)
        {
            $shop_id_list[] =  '0';
        }
        //LogDebug($shop_id_list);
    }
    if($shop_name)
    {
        $shoplist = $shop_mgo->GetAllShopList(['shop_name'=>$shop_name]);
        $id_list = [];
        foreach ($shoplist as  $value)
        {
            array_push($id_list, $value->shop_id);
        }
        if(count($id_list)==0)
        {
            $id_list[] =  '0';
        }
        //LogDebug($id_list);
        if($shop_id_list)
        {
            $shop_id_list = array_values(array_intersect($shop_id_list,$id_list));
        }
        else
        {
            $shop_id_list = $id_list;
        }

        if(count($shop_id_list)==0)
        {
            $shop_id_list[] =  '0';
        }
    }
    if (!$login_begin_time && $login_end_time)
    {
        $login_begin_time = -28800;
    }
    if (!$login_end_time && $login_begin_time)
    {
        $login_end_time = 1922354460;
    }

    $total = 0;
    $mgo = new Mgo\Resources;
    $list = $mgo->GetResourcesList(
        [
            'shop_id_list'     => $shop_id_list,
            'resources_id'     => $resources_id,
            'resources_type'   => $resources_type,
            'login_begin_time' => $login_begin_time,
            'login_end_time'   => $login_end_time
        ],
        $page_size,
        $page_no,
        $total
    );
    foreach ($list as &$v)
    {
        $shop_info    = $shop_mgo->GetShopById($v->shop_id);
        $v->shop_name = $shop_info->shop_name;
        if($v->valid_begin_time == 0)
        {
            $v->over_time = '永久';
        }
        else
        {
            $time = $v->valid_end_time - time();
            if($time <=0)
            {
                $v->over_time = '过期';
            }
            else
            {
                $v->over_time = time2string($time);
            }

        }
    }
    $resp = (object)array(
        'list' => $list,
        'total'=> $total
    );
    LogInfo("--ok--");
    return 0;
}

function time2string($second){
    $day = floor($second/(3600*24));
    $second = $second%(3600*24);
    $hour = floor($second/3600);
    $second = $second%3600;
    $minute = floor($second/60);
    $second = $second%60;
        // 不用管怎么实现的，能用就ok
    return $day.'天'.$hour.'小时'.$minute.'分';//.$second.'秒'
}



$ret = -1;
$resp = (object)array();
if(isset($_["resources_list"]))
{
    $ret = GetResourcesList($resp);
}

else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
    'data' => $resp
);
//var_dump($GLOBALS['need_json_obj']);
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

