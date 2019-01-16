<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 取终端类别信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cfg.php");
require_once("mgo_term_binding.php");
require_once("mgo_shop.php");
require_once("mgo_employee.php");
use \Pub\Mongodb as Mgo;

function GetTermList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_name = $_['shop_name'];
    $term_type = $_['term_type'];
    $phone     = $_['phone'];
    $is_login  = $_['is_login'];
    $page_size = $_['page_size'];
    $page_no   = $_['page_no'];
    $shop_mgo = new \DaoMongodb\Shop;
    $em_mgo = new \DaoMongodb\Employee;
    if($shop_name)
    {
        $shoplist = $shop_mgo->GetShopList(['shop_name'=>$shop_name], 99999, 1);
        $shop_id_list = [];
        foreach ($shoplist as  $value)
        {
            array_push($shop_id_list, $value->shop_id);
        }
        if(count($shop_id_list)==0)
        {
            $shop_id_list[] =  '0';
        }
    }
    if($phone)
    {
        $employee_id_list = [];
        $emlist = $em_mgo->GetEmployeeListByPhone($phone);
        foreach ($emlist as  $item)
        {
            array_push($employee_id_list, $item->employee_id);
        }
        if(count($employee_id_list)==0)
        {
            $employee_id_list[] =  'EM0';
        }
    }
    $total = 0;
    $mgo = new Mgo\TermBinding;
    $list = $mgo->GetTermBindList(
        [
            'shop_id_list'     => $shop_id_list,
            'employee_id_list' => $employee_id_list,
            'term_type'        => $term_type,
            'is_login'         => $is_login
        ],
        $page_size,
        $page_no,
        $total
    );
    foreach ($list as &$v)
    {
        $shop_info    = $shop_mgo->GetShopById($v->shop_id);
        $v->shop_name = $shop_info->shop_name;
        $em_info      = $em_mgo->GetEmployeeInfo($v->shop_id,$v->employee_id);
        $v->phone     = $em_info->phone;
    }
    $resp = (object)array(
        'list' => $list,
        'total'=> $total
    );
    LogInfo("--ok--");
    return 0;
}




$ret = -1;
$resp = (object)array();
if(isset($_["term_list"]))
{
    $ret = GetTermList($resp);
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

