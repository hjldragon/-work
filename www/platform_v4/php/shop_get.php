<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取店铺信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_shop.php");
require_once("mgo_user.php");
require_once("permission.php");
require_once("mgo_seat.php");
require_once("mgo_printer.php");
require_once("mgo_employee.php");
require_once("mgo_ag_employee.php");
require_once("mgo_authorize.php");
require_once("mgo_resources.php");
require_once("/www/public.sailing.com/php/mgo_business.php");
require_once("/www/public.sailing.com/php/mgo_from.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;

function GetShopInfo(&$resp)
{


    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id       = $_['shop_id'];
    $platform      = $_['platform'];
    $shop_bs_info  = $_['shop_bs_info'];
    if(!$shop_bs_info)
    {
        if(!$platform)
        {
            AgPermissionCheck::PageCheck(AgentPermissionCode::SHOP_INFO_SEE);
        }else{
            PlPermissionCheck::PageCheck(PlPermissionCode::SHOP_SEE);
        }
    }

    $mgo      = new \DaoMongodb\Shop;
    $user_mgo = new \DaoMongodb\User;
    $ag_em_mgo= new \DaoMongodb\AGEmployee;
    //$auth     = new \DaoMongodb\Authorize;
    $em_mgo   = new \DaoMongodb\Employee;
    $from_mgo = new Mgo\From;
    $res_mgo  = new Mgo\Resources;

    $em_info   = $em_mgo->GetAdminByShopId($shop_id);
    if(!$em_info->userid)
    {
        LogErr("this shop not employee");
        return errcode::USER_NOT_ZC;
    }
    $user_info  = $user_mgo->QueryById($em_info->userid);
    $info       = $mgo->GetShopById($shop_id);
    $ag_em_info = $ag_em_mgo->QueryById($info->from_employee);
    $from_info  =  $from_mgo->GetByFromId($info->from);
    $authorize  = (object)array();
    $authorize->pad_num     = $res_mgo->GetResourcesCount($shop_id, NewSrcType::PAD);      //平板智能点餐机资源数
    $authorize->cashier_num = $res_mgo->GetResourcesCount($shop_id, NewSrcType::SHOUYINJI);//智能收银机资源数
    $authorize->app_num     = $res_mgo->GetResourcesCount($shop_id, NewSrcType::APP);      //掌柜通资源数
    $authorize->machine_num = $res_mgo->GetResourcesCount($shop_id, NewSrcType::SELFHELP); //自助点餐机资源数

    $info->authorize         = $authorize;
    $info->employee_name     = $ag_em_info->real_name;
    $info->userid            = $user_info->userid;
    $info->real_name         = $user_info->real_name;
    $info->phone             = $user_info->phone;
    $info->password          = $user_info->password;
    $info->email             = $user_info->email;
    $info->from              = $from_info->from;

    $resp = (object)array(
        'shopinfo' => $info
    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetShopList(&$resp)
{
    AgPermissionCheck::PageCheck(AgentPermissionCode::SHOP_LIST_SEE);
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_name       = $_['shop_name'];
    $agent_id        = $_['agent_id'];
    $shop_id         = $_['shop_id'];
    $province        = $_['province'];
    $city            = $_['city'];
    $area            = $_['area'];
    $shop_model      = $_['shop_model'];
    $begin_time      = $_["begin_time"];
    $agent_type      = $_['agent_type'];
    $end_time        = $_["end_time"];
    $business_status = $_['business_status'];
    $begin_bs_time   = $_['begin_bs_time'];
    $employee_name   = $_['employee_name'];
    $end_bs_time     = $_['end_bs_time'];
    $from            = $_['from'];
    $page_size       = $_['page_size'];
    $page_no         = $_['page_no'];
    $sort_name       = $_['sort_name'];
    $desc            = $_['desc'];


    switch ($sort_name) {
        case 'shop_id':
            $sort['_id']   = (int)$desc;
            break;
        case 'ctime':
            $sort['ctime'] = (int)$desc;
            break;
        default:
            break;
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
    if(!$begin_bs_time && $end_bs_time)
    {
        $begin_bs_time = -28800; //默认时间
    }
    if(!$end_bs_time && $begin_bs_time)
    {
        $end_bs_time = time();
    }
    $total            = 0;
    $mgo              =  new \DaoMongodb\Shop;
    $from_mgo         = new Mgo\From;
    $ag_em_mgo        = new \DaoMongodb\AGEmployee;
    $ag_employee_info = $ag_em_mgo->GetEmployeeByName($agent_id,$employee_name);
    $from_employee    = $ag_employee_info->ag_employee_id;
    $f_info           =  $from_mgo->GetByFromName($from);
    $list = $mgo->GetShopList([
        'shop_name'       => $shop_name,
        'agent_id'        => $agent_id,
        'province'        => $province,
        'shop_id'         => $shop_id,
        'city'            => $city,
        'area'            => $area,
        'from'            => $f_info->from_id,
        'agent_type'      => $agent_type,
        'from_employee'   => $from_employee,
        'shop_model'      => $shop_model,
        'business_status' => $business_status,
        'begin_time'      => $begin_time,
        'end_time'        => $end_time,
        'begin_bs_time'   => $begin_bs_time,
        'end_bs_time'     => $end_bs_time,
    ],
    $page_size,
    $page_no,
    $sort,
    $total
    );
    $emmgo = new \DaoMongodb\Employee;
    foreach ($list as &$item)
    {
        if($item->agent_id)
        {
            $item->agent_name = \Cache\Agent::GetAgentName($item->agent_id);
        }
        else
        {
            $item->agent_name = '无';
        }
        $eminfo               = $emmgo->GetAdminByShopId($item->shop_id);
        $from_info            =  $from_mgo->GetByFromId($item->from);
        $ag_info              = $ag_em_mgo->QueryById($item->from_employee);
        $item->phone          = $eminfo->phone;
        $item->from           = $from_info->from;
        $item->from_salesman  = $ag_info->real_name;

    }
    $resp = (object)array(
        'list'  => $list,
        'total' => $total
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//获取商户列表
function GetShopBusinessList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_name       = $_['shop_name'];
    $agent_id        = $_['agent_id'];
    $agent_type      = $_['agent_type'];
    $agent_name      = $_['agent_name'];
    $shop_id         = $_['shop_id'];
    $business_status = $_['business_status'];
    $from            = $_['from'];
    $province        = $_['province'];
    $city            = $_['city'];
    $area            = $_['area'];
    $employee_name   = $_['employee_name'];
    $begin_time      = $_['begin_time'];
    $end_time        = $_['end_time'];
    $page_size       = $_['page_size'];
    $page_no         = $_['page_no'];
    $sort_name       = $_['sort_name'];
    $desc            = $_['desc'];
    $platform        = $_['platform'];
    $shop_bind       = $_['shop_bind'];
    if($platform)
    {
        if($business_status == BusinessType::SUCCESSFUL)
        {
            if($shop_bind)
            {
                PlPermissionCheck::PageCheck(PlPermissionCode::SHOP_BIND_SEE);
                if($shop_name || $shop_id || $from || $begin_time || $end_time)
                {
                    PlPermissionCheck::PageCheck(PlPermissionCode::SHOP_BIND_SEEK);
                }
            }else{
//                PlPermissionCheck::PageCheck(PlPermissionCode::SHOP_SEE);
//                if($shop_name || $employee_name || $from || $begin_time || $end_time || $agent_type || $agent_name)
//                {
                    PlPermissionCheck::PageCheck(PlPermissionCode::SEEK_SHOP);

            }
        }else
        {
            PlPermissionCheck::PageCheck(PlPermissionCode::SHOP_AUDIT_LIST);
        }
    }else{

        AgPermissionCheck::PageCheck(AgentPermissionCode::SHOP_BUSINESS_LIST);
    }


    switch ($sort_name) {
        case 'shop_id':
            $sort['_id'] = (int)$desc;
            break;
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
    if(!$begin_time && $end_time)
    {
        $begin_time = -28800; //默认时间
    }
    if(!$end_time && $begin_time)
    {
        $end_time = time();
    }
    if(null == $business_status)
    {
        $business_status_list = [ShopBusiness::NOAPPLY,ShopBusiness::APPLY,ShopBusiness::FAIL]; //找没有认证成功的状态数据
    }
    $mgo              = new \DaoMongodb\Shop;
    $ag_employee      = new \DaoMongodb\AGEmployee;
    $pl_employee      = new \DaoMongodb\Platformer;
    $mgo_bs           = new Mgo\Business;
    $ag_mgo           = new \DaoMongodb\Agent;
    $from_mgo         = new Mgo\From;
    //<<<<<<<<<<<<<<<<<7.30没想好和产品定义销售人员只搜索赛领888的销售人员
    //<<<<<<<<<<<<<<<<<8.31测试要运营平台显示所有
    //$id  = PlAgentId::ID;
    if($platform)
    {
        $pl_info       = $pl_employee->QueryByPlName($employee_name);
        $from_employee = $pl_info->platform_id;
        $agent_id      = null;
    }else{
        $ag_employee_info = $ag_employee->GetEmployeeByName($agent_id,$employee_name);
        $from_employee    = $ag_employee_info->ag_employee_id;
    }

    if($agent_type && !$agent_name)
    {
        $ag_list          = $ag_mgo->GetAllAgent(['agent_type'=>$agent_type]);
        foreach ($ag_list as $s){
            $agent_id_list[] = $s->agent_id;
        }
    }elseif($agent_name && !$agent_type){
        $ag_list          = $ag_mgo->GetAllAgent(['agent_name'=>$agent_name]);
        foreach ($ag_list as $s){
            $agent_id_list[] = $s->agent_id;
        }
    }elseif($agent_type && $agent_name){

        $ag_list          = $ag_mgo->GetAllAgent(['agent_name'=>$agent_name,'agent_type'=>$agent_type]);
        foreach ($ag_list as $s){
            $agent_id_list[] = $s->agent_id;
        }
    }
    if($agent_type || $agent_name)
    {
        if(!$agent_id_list)
        {
            $business_status = BusinessType::ALL; //<<<<<<没该状态,因为列表是只显示认证成功了的数据.
        }
    }

    $f_info       =  $from_mgo->GetByFromName($from);
    $total = 0;
    $list = $mgo->GetShopList([
        'shop_name'            => $shop_name,
        'agent_id'             => $agent_id,
        'agent_id_list'        => $agent_id_list,
        'shop_id'              => $shop_id,
        'from'                 => $f_info->from_id,
        'province'             => $province,
        'city'                 => $city,
        'area'                 => $area,
        'from_employee'        => $from_employee,
        'business_status'      => $business_status,
        'business_status_list' => $business_status_list,
        'begin_time'           => $begin_time,
        'end_time'             => $end_time,
    ],
        $page_size,
        $page_no,
        $sort,
        $total
    );

    $all = [];
    foreach ($list as &$v)
    {

     $platformer_info        = $ag_employee->QueryById($v->from_employee);
     $bs_info                = $mgo_bs->GetByShopId($v->shop_id);
     $ag_info                = $ag_mgo->QueryById($v->agent_id);
     $from_info              =  $from_mgo->GetByFromId($v->from);
     $new['ctime']           = $v->ctime;
     if($ag_info->agent_type == AgentType::AREAAGENT)
     {
         $ag_type = '区域';
     }else{
         $ag_type = '行业';
     }
     $new['agent_type']      = $ag_type;
     $new['agent_name']      = $ag_info ->agent_name;
     $new['id']              = $v->shop_id;
     $new['shop_name']       = $v->shop_name;
     $new['address']         = $v->address;
     $new['from']            = $from_info->from;
     $new['employee_name']   = $platformer_info->real_name;
     $new['business_status'] = $v->business_status;
     $new['audit_plan']      = $v->audit_plan;
     $new['province']        = $v->province;
     $new['city']            = $v->city;
     $new['area']            = $v->area;
     $new['is_freeze']       = $v->is_freeze;
     $new['corporate_num']   = $bs_info->corporate_num;
     $new['merchant_num']    = $bs_info->merchant_num;
     $new['weixin_set']      = $v->weixin_pay_set;
     $new['alipay_set']      = $v->alipay_set;
     $new['agent_id']        = $v->agent_id;
     array_push($all,$new);
    }

    $page_all = ceil($total/$page_size);//总共页数
    $resp = (object)array(
        'list'     => $all,
        'total'    => $total,
        'page_all' => $page_all,
        'page_no'  => $page_no
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//平台获取商户所有信息详情
function GetShopAllInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = $_['shop_id'];
    $mgo_shop     = new \DaoMongodb\Shop;
    $auth_mgo     = new \DaoMongodb\Authorize;
    $em_mgo       = new \DaoMongodb\Employee;
    $bus_mgo      = new Mgo\Business;
    $em_info      = $em_mgo->GetAdminByShopId($shop_id);
    if(!$em_info->userid)
    {
        LogErr("this shop not employee");
        return errcode::USER_NOT_ZC;
    }
    $info      = $mgo_shop->GetShopById($shop_id);
    $authorize = $auth_mgo->GetAuthorizeByShop($shop_id);
    $bus_info  = $bus_mgo->GetByShopId($shop_id);
    $one->ctime      = $info->ctime;
    $one->phone      = $em_info->phone;
    $one->shop_name  = $info->shop_name;
    $one->telephone  = $info->telephone;
    $one->shop_area  = $info->shop_area;
    $one->address    = $info->address;
    $one->shop_model = $info->shop_model;


    $resp = (object)array(
        'one'   => $one,
        'two'   => $bus_info,
        'three' => $authorize,
    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_["get_shop_info"]))
{
    $ret = GetShopInfo($resp);
}
elseif(isset($_["get_shop_list"]))
{
    $ret = GetShopList($resp);
}elseif(isset($_["shop_business_list"]))
{
    $ret = GetShopBusinessList($resp);
}elseif(isset($_["shop_all_info"]))
{
    $ret = GetShopAllInfo($resp);
}

$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
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

