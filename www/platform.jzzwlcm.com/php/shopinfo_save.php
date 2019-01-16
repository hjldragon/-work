<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 店铺信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_shop.php");
require_once("permission.php");
require_once("redis_id.php");
Permission::PageLoginCheck();
//编辑店铺设置
function SaveShopInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

//    // 是否有管理员权限
//    $ret = Permission::Check(Permission::CHK_LOGIN|Permission::CHK_ADMIN);
//    if(0 != $ret)
//    {
//        LogErr("permission err, username:" . \Cache\Login::GetUsername());
//        return $ret;
//    }
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
    LogDebug($shop_id);
    $shop_name           = $_['shop_name'];
    $shop_logo           = $_['shop_logo'];
    $shop_area           = $_['shop_area'];
    $contact             = $_['contact'];
    $address_num         = $_['address_num'];
    $address             = $_['address'];
    $suspend             = $_['suspend'];
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
    $shop_model          = $_['shop_model'];
    $telephone           = $_['telephone'];
    $mgo                 = new \DaoMongodb\Shop;
    $entry               = new \DaoMongodb\ShopEntry;
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


    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
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
    LogDebug($_);
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
//    // 是否有管理员权限
//    $ret = Permission::Check(Permission::CHK_LOGIN|Permission::CHK_ADMIN);
//    if(0 != $ret)
//    {
//        LogErr("permission err, username:" . \Cache\Login::GetUsername());
//        return $ret;
//    }
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
    $company_name                          = $_['company_name'];
    if (!$company_name) {
        LogErr("company_name  is empty");
        return errcode::PARAM_ERR;
    }
    $legal_person                          = $_['legal_person'];
    if (!$legal_person) {
        LogErr("legal_person  is empty");
        return errcode::PARAM_ERR;
    }
    $legal_card                            = $_['legal_card'];
    if (!$legal_card) {
        LogErr("legal_card  is empty");
        return errcode::PARAM_ERR;
    }
    $legal_card_photo                      = json_decode($_['legal_card_photo']);
    if (!$legal_card_photo) {
        LogErr("legal_card_photo  is empty");
        return errcode::PARAM_ERR;
    }
    $business_num                          = $_['business_num'];
    if (!$business_num) {
        LogErr("business_num  is empty");
        return errcode::PARAM_ERR;
    }
    $business_date                         = json_decode($_['business_date']);
    //$business_date                         = explode(',',$_['business_date']);
    if (!$business_date) {
        LogErr("business_date is empty");
        return errcode::PARAM_ERR;
    }
    $business_photo                        = $_['business_photo'];
    if (!$business_photo) {
        LogErr("business_photo  is empty");
        return errcode::PARAM_ERR;
    }
    $repast_permit_identity                = $_['repast_permit_identity'];
    if (!$repast_permit_identity) {
        LogErr("repast_permit_identity  is empty");
        return errcode::PARAM_ERR;
    }
    $repast_permit_year                    = $_['repast_permit_year'];
    if (!$repast_permit_year) {
        LogErr("repast_permit_year  is empty");
        return errcode::PARAM_ERR;
    }
    $repast_permit_num                     = $_['repast_permit_num'];
    if (!$repast_permit_num) {
        LogErr("repast_permit_num  is empty");
        return errcode::PARAM_ERR;
    }
    $repast_permit_photo                   = $_['repast_permit_photo'];
    if (!$repast_permit_photo) {
        LogErr("repast_permit_photo  is empty");
        return errcode::PARAM_ERR;
    }
    $confirmation                          = $_['confirmation'];
    if (!$confirmation) {
        LogErr("confirmation  is empty");
        return errcode::PARAM_ERR;
    }
    $business_scope                        = $_['business_scope'];
    if (!$business_scope) {
        LogErr("business_scope  is empty");
        return errcode::PARAM_ERR;
    }

    $shop_business->company_name           = $company_name;
    $shop_business->legal_person           = $legal_person;
    $shop_business->legal_card             = $legal_card;
    $shop_business->legal_card_photo       = $legal_card_photo;
    $shop_business->business_date          = $business_date;
    $shop_business->business_num           = $business_num;
    $shop_business->business_photo         = $business_photo;
    $shop_business->repast_permit_identity = $repast_permit_identity;
    $shop_business->repast_permit_year     = $repast_permit_year;
    $shop_business->repast_permit_num      = $repast_permit_num;
    $shop_business->repast_permit_photo    = $repast_permit_photo;
    $shop_business->confirmation           = $confirmation;
    $shop_business->business_scope         = $business_scope;
    $shop_bs_status->bs_code = 1;
    $shop_bs_status->id_code = 1;
    $shop_bs_status->rs_code = 1;
    $mgo                                   = new \DaoMongodb\Shop;
    $entry                                 = new \DaoMongodb\ShopEntry;
    $entry->shop_id                        = $shop_id;
    $entry->shop_business                  = $shop_business;
    $entry->shop_bs_status                 = $shop_bs_status;

    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array();
    LogInfo("save ok");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_['shopinfo_save']))
{
    $ret = SaveShopInfo($resp);
}
elseif(isset($_['save_shop_business']))
{
    $ret = SaveShopBusiness($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
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

