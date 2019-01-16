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
    $shop_id          = $_['shop_id'];
    $resources_id     = $_['resources_id'];
    $resources_type   = $_['resources_type'];
    $login_begin_time = $_['login_begin_time'];
    $login_end_time   = $_['login_end_time'];
    $last_time        = $_['last_time'];  //1:12小时，2:1天，3:1周，4:1月，5:永久,6:过期
    $page_size        = $_['page_size'];
    $page_no          = $_['page_no'];
    if(!$shop_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    if (!$login_begin_time && $login_end_time)
    {
        $login_begin_time = -28800;
    }
    if (!$login_end_time && $login_begin_time)
    {
        $login_end_time = 1922354460;
    }
    $times = time();
    switch ($last_time)
    {
        case 1:
            $end_time = $times + 12*60*60;
            break;
        case 2:
            $end_time = $times + 24*60*60;
            break;
        case 3:
            $end_time = $times + 7*24*60*60;
            break;
        case 4:
            $end_time = $times + 30*24*60*60;
            break;
        case 5:
            $begin_time = 1;
            break;
        case 6:
            $overdue = 1;
            break;
        default:
            # code...
            break;
    }
    $shop_mgo = new \DaoMongodb\Shop;
    $em_mgo = new \DaoMongodb\Employee;

    $total = 0;
    $mgo = new Mgo\Resources;
    $list = $mgo->GetResourcesList(
        [
            'shop_id'          => $shop_id,
            'resources_id'     => $resources_id,
            'resources_type'   => $resources_type,
            'login_begin_time' => $login_begin_time,
            'login_end_time'   => $login_end_time,
            'end_time'         => $end_time,
            'begin_time'       => $begin_time,
            'overdue'          => $overdue
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
            $time = $v->valid_end_time - $times;
            if($time <= 0)
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

