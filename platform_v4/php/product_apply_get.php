<?php
/*
 * [Rocky 2017-06-20 00:10:18]
 * 员工信息表
 *
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("cache.php");
require_once("mgo_product_apply.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
function GetProductApplyList(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::SHOP_BUSINESS_LIST);
    $_ = $GLOBALS["_"];
    //LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $begin_time      = $_["begin_time"];
    $end_time        = $_["end_time"];
    $apply_status    = $_['apply_status'];
    $telephone       = $_['telephone'];
    $province        = $_["province"];
    $city            = $_["city"];
    $area            = $_['area'];
    $page_size       = $_['page_size'];
    $page_no         = $_['page_no'];
    $sort_name       = $_['sort_name'];
    $desc            = $_['desc'];
    if($telephone || $apply_status)
    {
        PlPermissionCheck::PageCheck(PlPermissionCode::SEEK_SHOP_BS);
    }
    if (!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if (!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    if(!$begin_time && $end_time)
    {
        $begin_time = -28800; //默认时间
    }
    if(!$end_time && $begin_time)
    {
        $end_time = time();
    }
    switch ($sort_name) {
        case 'apply_time':
            $sort['_id'] = (int)$desc;
            break;
        default:
            break;
    }
    $total = 0;
    $mgo = new \DaoMongodb\ProductApply;
    $list = $mgo->GetProductApplyList([
        'apply_status' => $apply_status,
        'telephone'    => $telephone,
        'begin_time'   => $begin_time,
        'end_time'     => $end_time,
        'province'     => $province,
        'city'         => $city,
        'area'         => $area
    ],
    $page_size,
    $page_no,
    $sort,
    $total
    );

    $resp = (object)array(
        'apply_list'      => $list,
        'total'     => $total,
        'page_size' => $page_size,
        'page_no'   => $page_no,

    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetProductApplyInfo(&$resp)
{
    PlPermissionCheck::PageCheck(PlPermissionCode::SHOP_INFO_BS_SEE);
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $apply_id = $_['apply_id'];
    if(!$apply_id)
    {
        LogErr("apply_id is not");
        return errcode::PARAM_ERR;
    }
    $mgo             = new \DaoMongodb\ProductApply;
    $pl_mgo          = new \DaoMongodb\Platformer;
    $info            = $mgo->GetInfoById($apply_id);
    $pl_info         = $pl_mgo->QueryById($info->pl_employee_id);
    $info->real_name = $pl_info->pl_name;
    $resp = (object)[
        'apply_info' => $info,
    ];
    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_["product_apply_list"]))
{
    $ret = GetProductApplyList($resp);
}elseif(isset($_['get_apply_info']))
{
    $ret = GetProductApplyInfo($resp);
} else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}


$html = json_encode((object)array(
    'ret' => $ret,
    'data'  => $resp
));
echo $html;
LogDebug($html);
// $callback = $_['callback'];
// $js =<<< eof
// $callback($html);
// eof;
?>