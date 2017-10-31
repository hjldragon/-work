<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取店铺信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_department.php");
require_once("permission.php");
require_once("mgo_employee.php");

Permission::PageCheck();
//$_=$_REQUEST;
function GetAllDepartment(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = \Cache\Login::GetShopId();

    $mgo             = new \DaoMongodb\Department;
    $department_list = $mgo->GetDepartmentList($shop_id);
    LogDebug($department_list);
    $employee = new \DaoMongodb\Employee;
    $list_all = [];
    foreach ($department_list as $id)
    {
        $list                    = [];
        $list['department_id']   = $id->department_id;
        $list['department_name'] = $id->department_name;
        $employee_list           = $employee->GetDepartmentEmployee($shop_id,$id->department_id);
        $all_employee            = [];
        foreach ($employee_list as $all)
        {
            $employee_list_all                  = [];
            $employee_list_all['employee_id']   = $all->employee_id;
            $employee_list_all['employee_name'] = $all->real_name;
            array_push($all_employee, $employee_list_all);
        }
        $list['employee_list'] = $all_employee;
        array_push($list_all, $list);
    }

    $resp = (object)[
        'department_list' => $list_all,
    ];
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

//获取餐店所有标签
function GetShopLabel(&$resp)
{
    $_ = $GLOBALS['_'];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = \Cache\Login::GetShopId();
    if (!$shop_id)
    {
        LogErr("shop_id err or maybe not login");
        return errcode::USER_NOLOGIN;
    }

    $mgo            = new \DaoMongodb\Shop;
    $info           = $mgo->GetShopById($shop_id);
    $name = $_['label_name'];
    switch ($name)
    {
        case 'shop_label':
            $shop_info = $info->shop_label;
            break;
        case 'shop_seat_region':
            $shop_info = $info->shop_seat_region;
            break;
        case 'shop_seat_type':
            $shop_info = $info->shop_seat_type;
            break;
        case 'shop_seat_shape':
            $shop_info = $info->shop_seat_shape;
            break;
        case 'shop_composition':
            $shop_info = $info->shop_composition;
            break;
        case 'shop_feature':
            $shop_info = $info->shop_feature;
            break;
        case 'food_attach_list':
            $shop_info = $info->food_attach_list;
            break;
        case 'food_unit_list':
            $shop_info = $info->food_unit_list;
            break;
        case 'shop_food_attach':
            $shop_info = $info->shop_food_attach;
            break;
        default:
            return errcode::SHOP_LABEL_ERR;
            break;
    }
    $resp = (object)[
        $name => $shop_info,
    ];

    LogInfo("get ok");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_["get_department_list"]))
{
    $ret = GetAllDepartment($resp);
}
elseif(isset($_["shoplist"]))
{
    $ret = GetShopList($resp);
}elseif(isset($_["get_shopinfo_base"]))
{
    $ret = GetShopBaseInfo($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>