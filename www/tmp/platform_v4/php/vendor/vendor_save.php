<?php
/*
 * [Rocky 2017-05-12 20:16:27]
 * 订单信息保存类
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_aisle.inc");
require_once("/www/public.sailing.com/php/redis_id.php");
require_once("mgo_shop.php");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

//保存编辑设备信息
function SaveVendorInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_id          = $_['vendor_id'];
    $vendor_num         = $_['vendor_num'];
    $vendor_model       = $_['vendor_model'];
    $aisle_num          = $_['aisle_num'];
    $sell_goods_type    = $_['sell_goods_type'];
    $ownership          = $_['ownership'];
    $shop_id            = $_['shop_id'];//<<<<<店铺端使用的
    $vendor_name        = $_['vendor_name'];
    $province           = $_['province'];
    $city               = $_['city'];
    $area               = $_['area'];
    $address            = $_['address'];
    $vendor_person      = $_['vendor_person'];
    $person_phone       = $_['person_phone'];
    $is_stockout        = $_['is_stockout'];
    $is_fault           = $_['is_fault'];
    $vendor_type        = $_['vendor_type'];
    $max_weight         = $_['max_weight'];
    $error_weight       = $_['error_weight'];
    $vendor_img         = json_decode($_['vendor_img']);
    $production_num     = $_['production_num'];
    $aisle_capacity     = $_['aisle_capacity'];
    if(!$vendor_num || !$vendor_name || !$vendor_model || !$aisle_num || !$production_num
         || !$province || !$city || !$area || !$vendor_person || !$person_phone
        || !$vendor_type || null == $aisle_capacity)
    {
        LogErr("no some required fields");
        return errcode::PARAM_ERR;
    }
    if($vendor_type == VendorType::WEIGHT)
    {
        if(!$max_weight || !$error_weight)
        {
            LogErr('no weight or error weight');
            return errcode::PARAM_ERR;
        }

    }


    $entry       = new VendorMgo\VendorEntry;
    $mgo         = new VendorMgo\Vendor;
    $shop        = new \DaoMongodb\Shop;
    $aisle_entry = new VendorMgo\AisleEntry;
    $aisle_mgo   = new VendorMgo\Aisle;

   if($ownership)
   {
       if($ownership != "赛领科技信息有限公司" && $ownership != "赛领科技")
       {
           $shop_info = $shop->GetShopByName($ownership);
           if(!$shop_info->shop_id)
           {
               LogDebug('no shop info');
               return errcode::SHOP_NOT_EXIST;
           }
           $shop_id = $shop_info->shop_id;
       }else{
           $shop_id = PlShopId::ID;
       }
   }



    if(!$vendor_id)
    {
        $vendor_id           = \DaoRedis\Id::VendorId();
        $entry->creat_time   = time();
        //根据货道数创建对应的货道
        for($i=1; $i<=$aisle_num; $i++)
        {
           $aisle_entry->aisle_id        = \DaoRedis\Id::GenAisleId();
           $aisle_entry->vendor_id       = $vendor_id;
           $aisle_entry->shop_id         = $shop_id;
           $aisle_entry->aisle_name      = $i;
           $aisle_entry->is_inform       = IsInform::NO;
           $aisle_entry->aisle_capacity  = $aisle_capacity;//由设备参数生产
           $aisle_entry->aisle_status    = AisleStatus::NORMAL;
           $aisle_entry->delete          = 0;

           $ret = $aisle_mgo->Save($aisle_entry);
            if (0 != $ret) {
                LogErr("aisle Save err");
                return errcode::SYS_ERR;
            }
        }
    }


    $entry->vendor_id             = $vendor_id;
    $entry->vendor_num            = $vendor_num;
    $entry->vendor_model          = $vendor_model;
    $entry->aisle_num             = $aisle_num;
    $entry->sell_goods_type       = $sell_goods_type;
    $entry->shop_id               = $shop_id;
    $entry->vendor_name           = $vendor_name;
    $entry->province              = $province;
    $entry->city                  = $city;
    $entry->area                  = $area;
    $entry->address               = $address;
    $entry->vendor_person         = $vendor_person;
    $entry->person_phone          = $person_phone;
    $entry->is_stockout           = $is_stockout;
    $entry->is_fault              = $is_fault;
    $entry->vendor_img            = $vendor_img;
    $entry->vendor_type           = $vendor_type;
    $entry->max_weight            = $max_weight;
    $entry->error_weight          = $error_weight;
    $entry->vendor_status         = VendorStatus::NORMAL;
    $entry->sell_status           = SellStatus::SELL;
    $entry->production_num        = $production_num;
    $entry->aisle_capacity        = $aisle_capacity;
    $entry->delete                = 0;

    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("vendor Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)[
    ];
    return 0;
}
//批量设备状态状态
function SaveOrderUrge(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_id_list     = json_decode($_['vendor_id_list']);
    $delete             = $_['delete'];
    $sell_status        = $_['sell_status'];
    $mgo                = $mgo   = new VendorMgo\Vendor;

    $ret = $mgo->VendorStatus($vendor_id_list, [
        'delete'      => $delete,
        'sell_status' => $sell_status
    ]);

    if (0 != $ret) {
        LogErr("change err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    return 0;
}
//
function IsShop(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $ownership  = $_['ownership'];
    $shop       = new \DaoMongodb\Shop;

    if($ownership != "赛领科技信息有限公司" && $ownership != "赛领科技")
    {
        $shop_info  = $shop->GetShopByName($ownership);
        if(!$shop_info->shop_id)
        {
            LogDebug('no shop info');
            return errcode::SHOP_NOT_EXIST;
        }
    }
    $resp = (object)[
    ];
    return 0;
}

//发送设备断货/缺货通知<<<<<<<<<公众号
function SendOutMessage(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_id           = $_['vendor_id'];
    $vendor_status       = $_['vendor_status'];
    if(!$vendor_id)
    {
        LogDebug('no vendor_id');
        return errcode::PARAM_ERR;
    }
    $entry  = new VendorMgo\VendorEntry;
    $mgo    = new VendorMgo\Vendor;

    $entry->vendor_id     = $vendor_id;
    $entry->vendor_status = $vendor_status;
    if($vendor_status == VendorStatus::STOCKOUT)
    {
        $entry->stockout_time = time();
    }
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_['save_vendor']))
{
    $ret = SaveVendorInfo($resp);
}elseif (isset($_['change_status']))
{
    $ret = SaveOrderUrge($resp);
}elseif (isset($_['is_shop']))
{
    $ret = IsShop($resp);
}elseif (isset($_['save_vendor_status']))
{
    $ret = SendOutMessage($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

