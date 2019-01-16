<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 店铺信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_shop.php");
require_once("permission.php");
require_once("redis_id.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
require_once("/www/public.sailing.com/php/mgo_business.php");
use \Pub\Mongodb as Mgo;
//编辑店铺设置
function SaveShopInfo(&$resp)
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
    $shop_name           = $_['shop_name'];
    $shop_logo           = $_['shop_logo'];
    $shop_area           = $_['shop_area'];
    $contact             = $_['contact'];
    $address_num         = $_['address_num'];
    $address             = $_['address'];
    $suspend             = $_['suspend'];
    $province            = $_['province'];
    $city                = $_['city'];
    $area                = $_['area'];
    $srctype             = $_['srctype'];
    if($suspend)
    {
        $suspend = 2;
    }

    $mgo                 = new \DaoMongodb\Shop;
    $entry               = new \DaoMongodb\ShopEntry;
    $info = $mgo->GetShopById($shop_id);
    $img  = $info->shop_logo;

//       if($shop_logo){
//           if($shop_logo != $img)
//           {
//               if(($info->logo_img_time+30*24*60*60)>=time())
//               {
//                   LogErr("The logo was changed less than a month.");
//                   return errcode::IMG_NOT_MORE;
//               }
//               $entry->logo_img_time  = time();
//           }
//       }



    $shop_pay_way        = json_decode($_['shop_pay_way']);
    //$shop_pay_way        = explode(',',$_['shop_pay_way']);//<<<<<<<<后端测试用的
    $pay_time            = json_decode($_['pay_time']);
    $sale_way            = json_decode($_['sale_way']);
    $shop_label          = json_decode($_['shop_label']);
    $open_time           = json_decode($_['open_time']);
    $is_invoice_vat      = json_decode($_['is_invoice_vat']);
    $invoice_remark      = $_['invoice_remark'];
    $opening_time        = json_decode($_['opening_time']);
    $img_list            = json_decode($_['img_list']);
    $shop_bs_status      = json_decode($_['shop_bs_status']);
    $shop_model          = json_decode($_['shop_model']);
    $telephone           = $_['telephone'];
    $meal_after          = $_['meal_after'];
     if($sale_way)
     {
         ShopPermissionCheck::PageCheck(ShopPermissionCode::EDIT_SHOP_SET);
     }

    $entry->shop_id        = $shop_id;
    $entry->shop_name      = $shop_name;
    $entry->contact        = $contact;
    $entry->shop_logo      = $shop_logo;
    $entry->address_num    = $address_num;
    $entry->shop_area      = $shop_area;
    $entry->address        = $address;
    $entry->opening_time   = $opening_time;
    $entry->img_list       = $img_list;
    $entry->suspend        = $suspend;
    $entry->shop_pay_way   = $shop_pay_way;
    $entry->pay_time       = $pay_time;
    $entry->sale_way       = $sale_way;
    $entry->shop_label     = $shop_label;
    $entry->open_time      = $open_time;
    $entry->is_invoice_vat = $is_invoice_vat;
    $entry->invoice_remark = $invoice_remark;
    $entry->shop_bs_status = $shop_bs_status;
    $entry->shop_model     = $shop_model;
    $entry->telephone      = $telephone;
    $entry->meal_after     = $meal_after;
    $entry->province       = $province;
    $entry->city           = $city;
    $entry->area           = $area;
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $id = "店铺营业时间改变";
    if($opening_time)
    {
        $ret_json =  PageUtil::NotifyFoodChange($shop_id, $id);
        //LogDebug("[$ret_json]");
        $ret_json_obj = json_decode($ret_json);
        LogDebug($ret_json_obj->ret);
        if(0 != $ret_json_obj->ret)
        {
            LogErr("shop change send err");
          }
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}
//编辑工商管理信息
function SaveShopBusiness(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }


    $shop_id       = $_['shop_id'];

    if(!$shop_id)
    {
        LogErr("no shop_id or agent_id");
        return errcode::PARAM_ERR;
    }
    $corporate_num    = $_['corporate_num'];
    $legal_person     = $_['legal_person'];
    if (!$legal_person) {
        LogErr("legal_person  is empty");
        return errcode::PARAM_ERR;
    }
    $legal_card        = $_['legal_card'];
    if (!$legal_card) {
        LogErr("legal_card  is empty");
        return errcode::PARAM_ERR;
    }
    $legal_card_photo   = json_decode($_['legal_card_photo']);
    if (!$legal_card_photo) {
        LogErr("legal_card_photo  is empty");
        return errcode::PARAM_ERR;
    }
    $business_num        = $_['business_num'];
    if (!$business_num) {
        LogErr("business_num  is empty");
        return errcode::PARAM_ERR;
    }
    $business_date       = json_decode($_['business_date']);
    if (!$business_date) {
        LogErr("business_date is empty");
        return errcode::PARAM_ERR;
    }
    $business_photo      = $_['business_photo'];
    if (!$business_photo) {
        LogErr("business_photo  is empty");
        return errcode::PARAM_ERR;
    }
        $repast_permit_num   = $_['repast_permit_num'];
        if (!$repast_permit_num) {
            LogErr("repast_permit_num  is empty");
            return errcode::PARAM_ERR;
        }
        $repast_permit_photo   = $_['repast_permit_photo'];
        if (!$repast_permit_photo) {
            LogErr("repast_permit_photo  is empty");
            return errcode::PARAM_ERR;
        }


    $taxpayer_num    = $_['taxpayer_num'];
    $taxpayer_photo  = $_['taxpayer_photo'];
    $business_scope  = $_['business_scope'];
    if (!$business_scope) {
        LogErr("business_scope  is empty");
        return errcode::PARAM_ERR;
    }
    $merchant_num  = $_['merchant_num'];

    $entry      = new Mgo\BusinessEntry;
    $mgo        = new Mgo\Business;
    $shop_entry = new DaoMongodb\ShopEntry;
    $shop       = new DaoMongodb\Shop;



    $entry->shop_id              = $shop_id;
    $entry->corporate_num        = $corporate_num;
    $entry->legal_person         = $legal_person;
    $entry->legal_card           = $legal_card;
    $entry->legal_card_photo     = $legal_card_photo;
    $entry->business_num         = $business_num;
    $entry->business_date        = $business_date;
    $entry->business_photo       = $business_photo;
    $entry->repast_permit_num    = $repast_permit_num;
    $entry->repast_permit_photo  = $repast_permit_photo;
    $entry->taxpayer_num         = $taxpayer_num;
    $entry->taxpayer_photo       = $taxpayer_photo;
    $entry->business_scope       = $business_scope;
    $entry->merchant_num         = $merchant_num;
    $entry->delete               = 0;
    $ret                         = $mgo->Save($entry);

    $shop_entry->shop_id         = $shop_id;
    $shop_entry->apply_time      = time();
    $shop_entry->business_status = ShopBusiness::APPLY;
    $shop_info = $shop->GetShopById($shop_id);
    if(!$shop_info->audit_plan)
    {
        $shop_entry->audit_plan   = BusinessPlan::XS;
    }
    $shop_ret = $shop->Save($shop_entry);


    if($shop_id)
    {
        if (0!=$shop_ret) {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
    }
    if (0!= $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array();
    LogInfo("save ok");
    return 0;
}

function SaveShopFoodAttach(&$resp)
{
    Permission::EmployeePermissionCheck(
        Permission::CHK_FOOD_W
    );
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $food_attach_list = json_decode($_['food_attach_list']);
    $shop_id = $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;

    $entry->shop_id          = $shop_id;
    $entry->food_attach_list = $food_attach_list;

    LogDebug($entry);

    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save Attach err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save atache ok");
    return 0;
}

function SaveShopFoodUnit(&$resp)
{
    Permission::EmployeePermissionCheck(
        Permission::CHK_FOOD_W
    );
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $food_unit_list = json_decode($_['food_unit_list']);
    $shop_id = $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;

    $entry->shop_id        = $shop_id;
    $entry->food_unit_list = $food_unit_list;

    LogDebug($entry);

    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save Attach err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save atache ok");
    return 0;
}

function ShopOpr(&$resp)
{
    // Permission::EmployeePermissionCheck(
    //     Permission::CHK_FOOD_W
    // );
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $opr = $_['opr'];
    $shop_id = $_['shop_id'];

    if(ShopIsSuspend::NO != $opr
        && ShopIsSuspend::BY_SYS_ADMIN != $opr
        && ShopIsSuspend::BY_SHOP_ADMIN != $opr)
    {
        LogErr("param err, opr:[$opr]");
        return errcode::PARAM_ERR;
    }

    $mgo = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;

    $entry->shop_id = $shop_id;
    $entry->suspend = $opr;

    LogDebug($entry);

    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save Attach err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save atache ok");
    return 0;
}
//添加删除餐店标签
function SaveShopLabel(&$resp)
{
    $_ = $GLOBALS['_'];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $name = $_['label_name'];
    if (!$name) {
        return errcode::SHOP_LABEL_ERR;
    }
    $all  = json_decode($_[$name]);
    $all  = array_filter($all);

    $login_shop_id = \Cache\Login::GetShopId();
    if (!$login_shop_id)
    {
        LogErr("shop_id err or maybe not login");
        return errcode::USER_NOLOGIN;
    }
    $shop_id       = $_['shop_id'];
    if($login_shop_id != $shop_id)
    {
        LogDebug($shop_id,$login_shop_id);
        return errcode::SHOP_NOT_WEIXIN;
    }
    $mgo            = new \DaoMongodb\Shop;
    $entry          = new \DaoMongodb\ShopEntry;
    $entry->shop_id = $shop_id;
    switch ($name)
    {
        case 'shop_label':
            $entry->shop_label = $all;
            break;
        case 'shop_seat_region':
            $entry->shop_seat_region = $all;
            break;
        case 'shop_seat_type':
            $entry->shop_seat_type = $all;
            break;
        case 'shop_seat_shape':
            $entry->shop_seat_shape = $all;
            break;
        case 'shop_composition':
            $entry->shop_composition = $all;
            break;
        case 'shop_feature':
            $entry->shop_feature = $all;
            break;
        case 'food_attach_list':
            $entry->food_attach_list = $all;
            break;
        case 'food_unit_list':
            $entry->food_unit_list = $all;
            break;
        case 'shop_food_attach':
            $entry->shop_food_attach = $all;
            break;
        default:
            return errcode::PARAM_ERR;
            break;
    }
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)[];
    LogInfo("save ok");
    return 0;
}
//收银设置
function SaveCollectionSet(&$resp)
{
    ShopPermissionCheck::PageCheck(ShopPermissionCode::EDIT_SHOUYIN_SET);
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
    $shop_id       = $_['shop_id'];
    if($login_shop_id != $shop_id)
    {
        LogDebug($shop_id,$login_shop_id);
        return errcode::SHOP_NOT_WEIXIN;
    }
    //$is_debt       = $_['is_debt'];
    $is_mailing    = $_['is_mailing'];
    $mailing_type  = $_['mailing_type'];
    $shop_pay_way  = json_decode($_['shop_pay_way']);
    if (null == $is_mailing) {
        LogErr("some word  is empty");
        return errcode::PARAM_ERR;
    }

    //$collection_set->is_debt      = $is_debt;
    $collection_set->is_mailing   = $is_mailing;
    $collection_set->mailing_type = $mailing_type;
    $mgo                                   = new \DaoMongodb\Shop;
    $entry                                 = new \DaoMongodb\ShopEntry;
    $entry->shop_id                        = $shop_id;
    $entry->shop_pay_way                   = $shop_pay_way;
    $entry->collection_set                 = $collection_set;
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array();
    LogInfo("save ok");
    return 0;
}
//微信支付设置
function SaveWeiXinPaySet(&$resp)
{
    ShopPermissionCheck::PageCheck(ShopPermissionCode::EDIT_WEIXIN_SET);
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
    $shop_id       = $_['shop_id'];
    if($login_shop_id != $shop_id)
    {
        LogDebug($shop_id,$login_shop_id);
        return errcode::SHOP_NOT_WEIXIN;
    }
    $mgo                                   = new \DaoMongodb\Shop;
    $entry                                 = new \DaoMongodb\ShopEntry;
    $pay_way    = (int)$_['pay_way'];
    $code_img   = $_['code_img'];
    $code_show  = $_['code_show'];
    $sub_mch_id = $_['sub_mch_id'];
    $api_key    = $_['api_key'];
    $spc_sub    = $_['spc_sub'];
    $tenpay_img = $_['tenpay_img'];

//    if($pay_way === \SetPayWay::USEOUR)
//    {
//        if(null==$pay_way  ||!$code_img || null==$code_show)
//        {
//            LogErr("some word  is empty");
//            return errcode::PARAM_ERR;
//        }
//    }elseif($pay_way==\SetPayWay::USEOTHER){
//       if (null==$pay_way  || !$sub_mch_id || !$api_key || null==$spc_sub )
//       {
//           LogErr("some word  is empty");
//           return errcode::PARAM_ERR;
//       }
//   }else{
//        return errcode::PARAM_ERR;
//    }

    $weixin_pay_set->code_img   = $code_img;
    $weixin_pay_set->code_show  = $code_show;
    $weixin_pay_set->pay_way    = $pay_way;
    $weixin_pay_set->sub_mch_id = $sub_mch_id;
    $weixin_pay_set->api_key    = $api_key;
    $weixin_pay_set->spc_sub    = $spc_sub;
    $weixin_pay_set->tenpay_img = $tenpay_img;

    $entry->shop_id                        = $shop_id;
    $entry->weixin_pay_set                 = $weixin_pay_set;
    $entry->weixin_seting                  = 1;
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array();
    LogInfo("save ok");
    return 0;
}
//支付宝设置
function SaveAlipaySet(&$resp)
{
    ShopPermissionCheck::PageCheck(ShopPermissionCode::EDIT_ALIPAY_SET);
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
    $shop_id       = $_['shop_id'];
    if($login_shop_id != $shop_id)
    {
        LogDebug($shop_id,$login_shop_id);
        return errcode::SHOP_NOT_WEIXIN;
    }
    $pay_way       = $_['pay_way'];
    $code_img      = $_['code_img'];
    $code_show     = $_['code_show'];
    $alipay_app_id = $_['alipay_app_id'];
    $public_key    = $_['public_key'];
    $private_key   = $_['private_key'];
    $safe_code     = $_['safe_code'];
    $hz_identity   = $_['hz_identity'];
    $alipay_num    = $_['alipay_num'];

//    if($pay_way == \SetPayWay::USEOUR)
//    {
//        if(null==$pay_way  || !$code_img || null==$code_show)
//        {
//            LogErr("some word  is empty");
//            return errcode::PARAM_ERR;
//        }
//    }elseif($pay_way==\SetPayWay::USEOTHER){
//        if (null==$pay_way  || !$alipay_app_id || !$public_key || !$safe_code || !$private_key || !$private_key || !$hz_identity || !$alipay_num)
//        {
//            LogErr("some word  is empty");
//            return errcode::PARAM_ERR;
//        }
//    }else{
//        return errcode::PARAM_ERR;
//    }

    $alipay_set->pay_way       = $pay_way;
    $alipay_set->code_img      = $code_img;
    $alipay_set->code_show     = $code_show;
    $alipay_set->alipay_app_id = $alipay_app_id;
    $alipay_set->public_key    = $public_key;
    $alipay_set->private_key   = $private_key;
    $alipay_set->safe_code     = $safe_code;
    $alipay_set->hz_identity   = $hz_identity;
    $alipay_set->alipay_num    = $alipay_num;

    $mgo                                   = new \DaoMongodb\Shop;
    $entry                                 = new \DaoMongodb\ShopEntry;
    $entry->shop_id                        = $shop_id;
    $entry->alipay_set                     = $alipay_set;
    $entry->alipay_seting                  = 1;
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array();
    LogInfo("save ok");
    return 0;
}
// 设置PAD端基础设置到服务器
function SyncBaseSettings(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id       = $_['shop_id'];
    if(!$shop_id)
    {
        return errcode::SHOP_NOT_WEIXIN;
    }
    $auto_order     = $_['auto_order'];
    $custom_screen  = $_['custom_screen'];
    $menu_sort      = $_['menu_sort'];
    LogDebug($_);
    $mgo   = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;
    $entry->shop_id        = $shop_id;
    $entry->auto_order     = $auto_order;
    $entry->custom_screen  = $custom_screen;
    $entry->menu_sort      = $menu_sort;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['shopinfo_save']))
{
    $ret = SaveShopInfo($resp);
}
else if(isset($_['save_food_attach_list']))
{
    $ret = SaveShopFoodAttach($resp);
}
else if(isset($_['save_food_unit_list']))
{
    $ret = SaveShopFoodUnit($resp);
}
else if(isset($_['opr_shop']))
{
    $ret = ShopOpr($resp);
}else if(isset($_['save_label']))
{
    $ret = SaveShopLabel($resp);
}
elseif(isset($_['save_shop_business']))
{
    $ret = SaveShopBusiness($resp);
}elseif(isset($_['save_collection_set']))
{
    $ret = SaveCollectionSet($resp);
}elseif(isset($_['save_weixin_pay_set']))
{
    $ret = SaveWeiXinPaySet($resp);
}elseif(isset($_['save_alipay_set']))
{
    $ret = SaveAlipaySet($resp);
}elseif(isset($_['sync_base_settings']))
{
//    if($_['srctype'] == 3)
//    {
//        Permission::PadUserPermissionCheck(Position::SETTING);
//    }
    $ret = SyncBaseSettings($resp);
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

