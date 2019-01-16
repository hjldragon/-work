<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取餐桌位置信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
require_once("/www/public.sailing.com/php/mgo_pl_role.php");
use \Pub\Mongodb as Mgo;


function GetPlRoleList(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::ROLE_LIST_SEE);
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $only_name       = $_['only_name'];
    $page_size       =(int)$_['page_size'];
    $page_no         =(int) $_['page_no'];
    $sort_name       = $_['sort_name'];
    $desc            = $_['desc'];

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
    $mgo       = new Mgo\PlRole;
    $total     = 0;
    $list      = $mgo->GetAllList([],$page_size, $page_no, $sort, $total);
    $all_name  = [];
    $list_name = $mgo->GetList();
    foreach ($list_name as &$v) {
        $name['pl_role_id']   = $v->pl_role_id;
        $name['role_name']    = $v->role_name;
        array_push($all_name,$name);
    }

    $page_all = ceil($total/$page_size);//总共页数
    if($only_name)
    {
        $resp = (object)array(
            'list' => $all_name
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
function GetPlRoleInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    PlPermission::PageCheck("010101");
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $pl_role_id       = $_['pl_role_id'];

    $mgo       = new Mgo\PlRole;
    $info      = $mgo->QueryById($pl_role_id);

    $resp = (object)array(
        'info'     => $info,
        );


    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_["get_pl_role_list"]))
{
    $ret = GetPlRoleList($resp);
}elseif(isset($_['pl_role_info']))
{
    $ret = GetPlRoleInfo($resp);
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

