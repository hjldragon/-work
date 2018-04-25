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
require_once("mgo_seat.php");
require_once("mgo_printer.php");
require_once("mgo_authorize.php");
require_once("redis_login.php");
require_once("redis_id.php");
//Permission::PageCheck();

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
        'info' => $info
    );
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
    $shop_id   = (string)$_['shop_id'];
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
//pad端端店铺信息
function GetPadShopInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id     = (string)$_['shop_id'];
    $token       = $_['token'];
     if(!$shop_id)
     {
         $shop_id = \Cache\Login::GetShopId();
     }
    //选择店铺的时候需要判断是否具有能操作改店铺的权限功能
    $device_type = $_['device_type'];//用于区分PAD,收银,自主点餐机
    $userid      = \Cache\Login::GetUserid();
    $shopinfo    = \Cache\Shop::Get($shop_id);
    $mgo      = new \DaoMongodb\Employee;
    $permission  = new \DaoMongodb\Position;
    $employee = $mgo->QueryByShopId($userid, $shop_id);
    $permission_info = $permission->GetPositionById($shop_id, $employee->position_id);
    //pad端要的非接口控制权限
    if($employee->is_admin != 1)
    {
        if(($permission_info->position_permission & Position::ORDERING) != 0)
        {
            $author->menu = 0;
        }else{
            $author->menu = -1;
        }
        if(($permission_info->position_permission & Position::USRPREDETET) != 0)
        {
            $author->reserve = 0;
        }else{
            $author->reserve = -1;
        }
        if(($permission_info->position_permission & Position::USRHISTORORDER) != 0)
        {
            $author->order = 0;
        }else{
            $author->order = -1;
        }
        if(($permission_info->position_permission & Position::SETTING) != 0)
        {
            $author->setting = 0;
        }else{
            $author->setting = -1;
        }
    }else{
        $author->menu    = 0;
        $author->reserve = 0;
        $author->order   = 0;
        $author->setting = 0;
    }



    //权限操作
    //$ret = Permission::UserPermissionCheck($employee);
//    if($ret !=0 )
//    {
//        return errcode::USER_PERMISSION_ERR;
//    }
    //店铺是否被冻结
    if($shopinfo->is_freeze == \EmployeeFreeze::FREEZE)
    {
        LogErr("shop freeze");
        return errcode::SHOP_IS_FREEZE;
    }
    //员工是否被冻结
    if($employee->is_freeze == \EmployeeFreeze::FREEZE)
    {
        LogErr("employee freeze");
        return errcode::EMPLOYEE_IS_FREEZE;
    }
    //员工是否具有pc端登录权限(位运算1:pc,2:pad,4:收银,8:app)
    if($device_type) {
        if ($device_type == 0) {
            if (($employee->authorize & 2) == 0 && $employee->is_admin != 1) {
                LogErr("employee authorize:" . $employee->authorize);
                return errcode::EMPLOYEE_NOT_LOGIN;
            }
        }
        if ($device_type == 1) {
            if (($employee->authorize & 4) == 0 && $employee->is_admin != 1) {
                LogErr("employee authorize:" . $employee->authorize);
                return errcode::EMPLOYEE_NOT_LOGIN;
            }
        }
    }
//    }else{
//        LogErr("no device_type");
//        return errcode::PARAM_ERR;
//    }
    $redis         = new \DaoRedis\Login();
    $info          = new \DaoRedis\LoginEntry();
    $info->token   = $token;
    $info->userid  = $userid;
    $info->shop_id = $shop_id;
    $info->login   = 1;
    $redis->Save($info);

    $mgo        = new \DaoMongodb\Shop;
    $is_shop = $mgo->GetShopById($shop_id);

    if(!$shop_id || !$is_shop->shop_id)
    {
        return errcode::SHOP_NOT_WEIXIN;
    }
    $print      = new \DaoMongodb\Printer;
    $seat       = new \DaoMongodb\Seat;
    $info       = $mgo->GetShopById($shop_id);
    $prints     = $print->GetList($shop_id);
    $print_info = [];
    foreach ($prints as $p)
    {
        $ps['name'] = $p->printer_name;
        $ps['type'] = $p->receipt_type;
        //$ps['recv'] =$p->printer_name;
        //$ps['isPrint'] =$p->printer_name;
        array_push($print_info, $ps);
    }
    $seats     = $seat->GetList($shop_id);
    $seat_info = [];
    foreach ($seats as $s)
    {
        $ss['name'] = $s->seat_name;
        $ss['area'] = $s->seat_region;
        $ss['type'] = $s->seat_type;
        $ss['seat'] = $s->seat_size;
        array_push($seat_info, $ss);
    }
    $pay = [];
    if (in_array(ShopSaleWAY::WEIXIN,$info->shop_pay_way))
    {
        $weixin['type'] = "weixin";
        if ($info->weixin_pay_set->pay_way == 2)
        {
            $weixin['scan'] = 1;
        } else {
            $weixin['scan'] = 2;
            if($info->weixin_pay_set->code_img){
                $weixin['url']  = Cfg::instance()->GetShopUrlAddr() . '/php/img_get.php?img=1&imgname='.$info->weixin_pay_set->code_img;
            }else{
                $weixin['url'] = null;
            }

        }
        array_push($pay, $weixin);
    }

    if (in_array(ShopSaleWAY::APAY,$info->shop_pay_way))
    {
        $alipay['type'] = "zhifu";
        if ($info->alipay_set->pay_way == 2)
        {
            $alipay['scan'] = 1;
        } else {
            $alipay['scan'] = 2;
            if($info->alipay_set->code_img){
                $alipay['url']  = Cfg::instance()->GetShopUrlAddr() . '/php/img_get.php?img=1&imgname='.$info->alipay_set->code_img;
            }else{
                $alipay['url'] = null;
            }
        }
        array_push($pay, $alipay);
    }

  if(in_array(ShopSaleWAY::GUAZ,$info->shop_pay_way))
  {
      $collpay['type']        = "guazhang";
      $collpay['bookkeeping'] = $info->collection_set->is_debt;
      array_push($pay, $collpay);
  }
    if($info->collection_set->is_mailing == 0 )
    {
        $shop_info['erasure']       = -1;
    }else{
        $shop_info['erasure']       = $info->collection_set->mailing_type;
    }

    $shop_info['auto_order']    = $info->auto_order;
    $shop_info['custom_screen'] = $info->custom_screen;
    $shop_info['menu_sort']     = $info->menu_sort;
    $shop_info['shop_phone']    = $info->telephone;
    $shop_info['shop_address']  = $info->address;
    $shop_info['tables']        = $seat_info;
    $shop_info['print_info']    = $print_info;
    $shop_info['pay_type']      = $pay;
    $shop_info['author']        = $author;
    $resp = (object)[
        'shop_info' => $shop_info,
        'token'     => $_['token'],
                     ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//pad端店铺支付宝或微信二维码
function GetPadShopCode(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id  = (string)$_['shop_id'];
    $pay_type = (int)$_['pay_type'];
    $mgo        = new \DaoMongodb\Shop;
    $shop_info = $mgo->GetShopById($shop_id);
    if(!$shop_id || !$shop_info->shop_id)
    {
        return errcode::SHOP_NOT_WEIXIN;
    }
    if($pay_type == 0)
    {
        $url = $shop_info->weixin_pay_set->code_img;
    }
    if($pay_type == 1)
    {
        $url = $shop_info->alipay_set->code_img;
    }
    if(!$url)
    {
        return errcode::CODE_NOT_SET;
    }
    $resp  = (object)[
        'url'=> Cfg::instance()->GetShopUrlAddr() . '/php/img_get.php?img=1&imgname='.$url,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetShopAuthorize(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Authorize;
    $info = $mgo->GetAuthorizeByShop($shop_id);
    $resp = (object)array(
        'info' => $info
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["shopinfo"]))
{
    $ret = GetShopInfo($resp);
}
elseif(isset($_["shoplist"]))
{
    $ret = GetShopList($resp);
}elseif(isset($_['get_shop_info']))
{
    $ret = GetPadShopInfo($resp);
}elseif(isset($_['get_shop_qrcode']))
{
    $ret = GetPadShopCode($resp);
}elseif(isset($_['get_shop_authorize']))
{
    $ret = GetShopAuthorize($resp);
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

