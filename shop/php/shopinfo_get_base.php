<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取店铺信息
 */
require_once("current_dir_env.php");
require_once("/www/shop.sailing.com/php/page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_shop.php");
require_once("permission.php");

Permission::PageCheck();

function GetShopInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    //通过登录来获取员工所在店铺的id
    $employee = \Cache\Employee::Get($_['userid']);
    $shop_id = $employee->shop_id;

    if (!$shop_id) {
        LogErr("shop_id err or maybe not login");
        return errcode::SEAT_NOT_EXIST;
    }
    //$shop_id = (string)$_['shop_id'];//<<<<<<<<<<<<<<<<<测试用的

    $mgo = new \DaoMongodb\Shop;
    $info = $mgo->GetShopById($shop_id);

            if(($info->logo_img_time+30*24*60*60)>=time())
            {
                $logo_img_time = false;
            }else{
                $logo_img_time = true;
            }



    $shopinfo = [];
    $shopinfo['shop_id']       = $info->shop_id;
    $shopinfo['shop_name']     = $info->shop_name;
    $shopinfo['shop_logo']     = $info->shop_logo;
    $shopinfo['contact']       = $info->contact;
    $shopinfo['shop_area']     = $info->shop_area;
    $shopinfo['address']       = $info->address;
    $shopinfo['address_num']   = $info->address_num;
    $shopinfo['province']      = $info->province;
    $shopinfo['city']          = $info->city;
    $shopinfo['area']          = $info->area;
    $shopinfo['logo_img_time'] = $logo_img_time;

    $resp = (object)array(
        'shopinfo' => $shopinfo
    );
    //die;
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["get_shopinfo_base"]))
{
    $ret = GetShopInfo($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>