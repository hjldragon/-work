<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取餐桌位置信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("mgo_platformer.php");
require_once("cache.php");
require_once("/www/public.sailing.com/php/mgo_from.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;

function GetFromList(&$resp)
{


    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $only_from       = $_['only_from'];
    $page_size       =(int)$_['page_size'];
    $page_no         =(int) $_['page_no'];
    $sort_name       = $_['sort_name'];
    $desc            = $_['desc'];
    $agent_cfg_set   = $_['agent_cfg_set'];
    if($agent_cfg_set)
    {
        PlPermissionCheck::PageCheck(PlPermissionCode::PL_FROM_SET);
    }
    switch ($sort_name) {
        case 'ctime':
            $sort['ctime'] = (int)$desc;
            break;
        default:
            break;
    }
    //排序字段
    if (!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if (!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    $mgo       = new Mgo\from;
    $pl_mgo    = new \DaoMongodb\Platformer();
    $total     = 0;
    $list      = $mgo->GetList([],$page_size, $page_no, $sort, $total);
    $all_from  = [];
    $list_name = $mgo->GetFromList();
    foreach ($list_name as &$v) {
        $pl_info      = $pl_mgo->QueryById($v->platformer_id);
        $v->real_name = $pl_info->pl_name;
        $all_from[]   = $v->from;
    }

    $page_all = ceil($total/$page_size);//总共页数
    if($only_from)
    {
        $resp = (object)array(
            'list' => $all_from
        );
    }else{
        $resp = (object)array(
            'list'     => $list,
            'total'    => $total,
            'page_all' => $page_all,
            'page_no'  => $page_no
        );
    }

    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["get_from_list"]))
{
    $ret = GetFromList($resp);
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

