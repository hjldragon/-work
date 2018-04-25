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

//Permission::PageCheck();
Permission::PageCheck($_['srctype']);
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
    $shop_id  = $_['shop_id'];
    if(!$shop_id)
    {
        $shop_id = \Cache\Login::GetShopId();
    }

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
    $shopinfo['phone']       = $entry->phone;
//    $shopinfo['is_weixin']   = $entry->is_weixin;
//    $shopinfo['telephone']   = $info->telephone;
//    $shopinfo['email']       = $info->email;
//    $shopinfo['contact']     = $info->contact;
//    $shopinfo['shop_id']     = $info->shop_id;
    $shopinfo['shop_name']   = $info->shop_name;
    $shopinfo['shop_logo']   = $info->shop_logo;
    $shopinfo['shop_area']   = $info->shop_area;
    $shopinfo['address']     = $info->address;
    $shopinfo['address_num'] = $info->address_num;
    $shopinfo['shop_model']  = $info->shop_model;
    $shopinfo['telephone']   = $info->telephone;
    $shopinfo['admin']       = $entry->phone;

    $resp = (object)[
        'shopinfo' => $shopinfo,
    ];
    //LogDebug($resp);
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
    $shop_id = $_['shop_id'];
    if(!$shop_id)
    {
        $shop_id = \Cache\Login::GetShopId();
    }
    if (!$shop_id) {
        LogErr("shop_id err or maybe not login");
        return errcode::SEAT_NOT_EXIST;
    }

    //$shop_id = (string)$_['shop_id'];//<<<<<<<<<<<<<<<<<测试用的
    $mgo  = new \DaoMongodb\Shop;
    $info = $mgo->GetShopById($shop_id);
    LogDebug($info);
    //LogDebug($info->opening_time);
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
    if($info->suspend != 0)
    {
        $shopinfo['suspend']    = 1;
    }else{
        $shopinfo['suspend']    = $info->suspend;
    }
    $shopinfo['invoice_remark'] = $info->invoice_remark;
    $shopinfo['meal_after']     = $info->meal_after;


    $resp = (object)array(
        'shopinfo' => $shopinfo
    );

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
        'shopsbusiness'   => $shopinfo,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//获取工商状态信息
function GetShopBusinessStatus(&$resp)
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
    $shopinfo                  = $info->shop_bs_status;

    $resp = (object)[
        'shop_bs_status'  => $shopinfo,
        'business_status' => $info->business_status,
    ];
    //LogDebug($resp);
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
//获取店铺收银设置信息
function GetShopPaySet(&$resp)
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
        return errcode::SHOP_NOT_EXIST;
    }
    $mgo         = new \DaoMongodb\Shop;
    $info        = $mgo->GetShopById($shop_id);
    $shopinfo    = [];
    $shopinfo['shop_pay_way']  = $info->shop_pay_way;
    $shopinfo['is_mailing']    = $info->collection_set->is_mailing;
    $shopinfo['mailing_type']  = $info->collection_set->mailing_type;

    $resp = (object)[
        'collection_set' => $shopinfo,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//支付宝使用个人码或者支付宝端设置
function SaveAlipaySelectSet(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $login_shop_id = \Cache\Login::GetShopId();
    if (!$login_shop_id)
    {
        LogErr("shop_id err or maybe not login");
        return errcode::USER_NOLOGIN;
    }
    $shop_id = $_['shop_id'];
    if ($login_shop_id != $shop_id)
    {
        LogDebug($shop_id, $login_shop_id);
        return errcode::SHOP_NOT_WEIXIN;
    }
    $pay_way             = $_['pay_way'];
    $alipay_set->pay_way = $pay_way;
    $mgo                 = new \DaoMongodb\Shop;
    $entry               = new \DaoMongodb\ShopEntry;
    $entry->shop_id      = $shop_id;
    $entry->alipay_set   = $alipay_set;
    $ret                 = $mgo->Save($entry);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogInfo("save ok");
    $info        = $mgo->GetShopById($shop_id);
    $alipay_info = [];

    if ($info->alipay_seting == 1)
    {
        if ($info->alipay_set->pay_way == PaySetingWay::PAYONE)
        {
            $alipay['pay_way']   = $info->alipay_set->pay_way;
            $alipay['code_img']  = $info->alipay_set->code_img;
            $alipay['code_show'] = $info->alipay_set->code_show;
            array_push($alipay_info, $alipay);
        } elseif ($info->alipay_set->pay_way == PaySetingWay::PAYTWO)
        {
            $alipay['pay_way']       = $info->alipay_set->pay_way;
            $alipay['alipay_app_id'] = $info->alipay_set->alipay_app_id;
            $alipay['public_key']    = $info->alipay_set->public_key;
            $alipay['private_key']   = $info->alipay_set->private_key;
            $alipay['safe_code']     = $info->alipay_set->safe_code;
            $alipay['hz_identity']   = $info->alipay_set->hz_identity;
            $alipay['alipay_num']    = $info->alipay_set->alipay_num;
            array_push($alipay_info, $alipay);
        } else {
            $alipay_info = [];
        }
    } else {
        $alipay_info = [];
    }
    $resp = (object)[
        'alipay_seting' => $info->alipay_seting,
        'alipay'        => $alipay_info,
    ];
    return 0;
}
//微信使用个人码或者微信端设置
function SaveWeixinSelectSet(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $login_shop_id = \Cache\Login::GetShopId();
    if (!$login_shop_id) {
        LogErr("shop_id err or maybe not login");
        return errcode::USER_NOLOGIN;
    }
    $shop_id = $_['shop_id'];
    if ($login_shop_id != $shop_id) {
        LogDebug($shop_id, $login_shop_id);
        return errcode::SHOP_NOT_WEIXIN;
    }
    $pay_way                 = $_['pay_way'];
    $weixin_pay_set->pay_way = $pay_way;
    $mgo                     = new \DaoMongodb\Shop;
    $entry                   = new \DaoMongodb\ShopEntry;
    $entry->shop_id          = $shop_id;
    $entry->weixin_pay_set   = $weixin_pay_set;
    $ret                     = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogInfo("save ok");
    $info        = $mgo->GetShopById($shop_id);
    $weixin_info = [];
    if ($info->weixin_seting == 1) {
        if ($info->weixin_pay_set->pay_way == PaySetingWay::PAYONE ) {
            $weixin['pay_way']   = $info->weixin_pay_set->pay_way;
            $weixin['code_img']  = $info->weixin_pay_set->code_img;
            $weixin['code_show'] = $info->weixin_pay_set->code_show;
            array_push($weixin_info, $weixin);
        } elseif ($info->weixin_pay_set->pay_way == PaySetingWay::PAYTWO) {
            $weixin['pay_way']    = $info->weixin_pay_set->pay_way;
            $weixin['spc_sub']    = $info->weixin_pay_set->spc_sub;
            $weixin['sub_mch_id'] = $info->weixin_pay_set->sub_mch_id;
            $weixin['api_key']    = $info->weixin_pay_set->api_key;
            $weixin['tenpay_img'] = $info->weixin_pay_set->tenpay_img;
            array_push($weixin_info, $weixin);
        } else {
            $weixin_info = [];
        }
    } else {
        $weixin_info = [];
    }
    $resp = (object)[
        'weixin_seting'  => $info->weixin_seting,
        'weixin_pay_set' => $weixin_info,
    ];

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
}elseif (isset($_['get_shop_bs_status']))
{
    $ret = GetShopBusinessStatus($resp);
}elseif (isset($_['get_shop_pay_set']))
{
    $ret = GetShopPaySet($resp);
}elseif(isset($_['select_alipay_set']))
{
    $ret = SaveAlipaySelectSet($resp);
}elseif(isset($_['select_weixin_set']))
{
    $ret = SaveWeixinSelectSet($resp);
}elseif(isset($_['shop_info']))
{
    $ret = GetPadShopInfo($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
