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
require_once("permission.php");

Permission::PageCheck();
//$_=$_REQUEST;
function GetShopInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = (string)$_['shop_id'];

    $mgo = new \DaoMongodb\Shop;
    $info = $mgo->GetShopById($shop_id);

    $resp = (object)array(
        'shopinfo' => $info
    );
    //die;
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetShopList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = (string)$_['shop_id'];
    $shop_name = $_['shop_name'];

    $mgo = new \DaoMongodb\Shop;
    $list = $mgo->GetShopList(['shop_id'=>$shop_id, 'shop_name'=>$shop_name]);
    $resp = (object)array(
        'list' => $list
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//获取店铺基本信息
function GetShopBaseInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = \Cache\Login::GetShopId();
    $userid  = \Cache\Login::GetUserid();
    if (!$shop_id || !$userid)
    {
        LogErr("shop_id err and userid empty, or maybe not login");
        return errcode::USER_NOLOGIN;
    }
    $mgo                     = new \DaoMongodb\Shop;
    $mgo2                    = new \DaoMongodb\User;
    $entry                   = $mgo2->QueryById($userid);
    $info                    = $mgo->GetShopById($shop_id);
    $shopinfo                = [];
    $shopinfo['username']    = $entry->username;
    $shopinfo['is_weixin']   = $entry->is_weixin;
    $shopinfo['telephone']   = $info->telephone;
    $shopinfo['email']       = $info->email;
    $shopinfo['contact']     = $info->contact;
    $shopinfo['shop_id']     = $info->shop_id;
    $shopinfo['shop_name']   = $info->shop_name;
    $shopinfo['shop_logo']   = $info->shop_logo;
    $shopinfo['shop_area']   = $info->shop_area;
    $shopinfo['address']     = $info->address;
    $shopinfo['address_num'] = $info->address_num;



    $resp = (object)[
        'shopinfo' => $shopinfo,
    ];
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//获取店铺编辑信息
function GetShopEditInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = \Cache\Login::GetShopId();
    if (!$shop_id) {
        LogErr("shop_id err or maybe not login");
        return errcode::SEAT_NOT_EXIST;
    }
    //$shop_id = (string)$_['shop_id'];//<<<<<<<<<<<<<<<<<测试用的

    $mgo = new \DaoMongodb\Shop;
    $info = $mgo->GetShopById($shop_id);
    $shopinfo = [];
    $shopinfo['shop_pay_way']   = $info->shop_pay_way;
    $shopinfo['pay_time']       = $info->pay_time;
    $shopinfo['sale_way']       = $info->sale_way;
    $shopinfo['shop_label']     = $info->shop_label;
    $shopinfo['open_time']      = $info->open_time;
    $shopinfo['address']        = $info->address;
    $shopinfo['opening_time']   = $info->opening_time;
    $shopinfo['is_invoice_vat'] = $info->is_invoice_vat;
    $shopinfo['img_list']       = $info->img_list;
    $shopinfo['suspend']        = $info->suspend;
    $shopinfo['invoice_remark'] = $info->invoice_remark;


    $resp = (object)array(
        'shopinfo' => $shopinfo
    );
    //die;
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//获取工商信息
function GetShopBusinessInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = Cache\Login::GetShopId();
    if (!$shop_id)
    {
        LogErr("shop_id err or maybe not login");
        return errcode::SEAT_NOT_EXIST;
    }
    //$shop_id = (string)$_['shop_id'];//<<<<<<<<<<<<<<<<<测试用的

    $mgo                       = new \DaoMongodb\Shop;
    $info                      = $mgo->GetShopById($shop_id);
    $shopinfo = $info->shop_business;

    $resp = (object)[
        'shopsbusiness' => $shopinfo,
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
    //$shop_id = $_['shop_id'];//<<<<<<<<<<<<<<<<<<<<<<<<<<<<测试数据
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
        default:
            return errcode::SHOP_LABEL_ERR;
            break;
    }
    $resp = (object)[
        $name => $shop_info,
    ];

    LogInfo("get    ok");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_["get_shop_info"]))
{
    $ret = GetShopInfo($resp);
}
elseif(isset($_["shoplist"]))
{
    $ret = GetShopList($resp);
}elseif(isset($_["get_shopinfo_base"]))
{
    $ret = GetShopBaseInfo($resp);
}elseif (isset($_['get_shopinfo_edit']))
{
    $ret = GetShopEditInfo($resp);
}elseif (isset($_['get_shop_business']))
{
    $ret = GetShopBusinessInfo($resp);
}elseif (isset($_['get_shop_label']))
{
    $ret = GetShopLabel($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
