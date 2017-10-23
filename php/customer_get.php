<?php
/*
 * [rockyshi 2014-10-04]
 * 用户信息表
 *
 */
require_once("current_dir_env.php");
require_once("mgo_customer.php");
require_once("mgo_weixin.php");
require_once("mgo_user.php");
require_once("redis_id.php");
require_once("cache.php");

// Permission::PageCheck();
//$_=$_REQUEST;
LogDebug($_);
//获取客户信息
function GetCustomerInfo(&$resp){
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    //通过获取用户登陆id来，获取用户信息
    $userid  = $_['userid'];
    $shop_id = $_['shop_id'];
    $openid  = $_['openid'];
    $user_mgo  = new DaoMongodb\User;
    //根据user表获取登录用户信息
    $user_info = $user_mgo->QueryById($userid);
    //空数组
    $custominfo                      = [];
    $custominfo['customer_name']     = $user_info->usernick;
    $custominfo['customer_portrait'] = $user_info->user_avater;
    $customer_mgo                    = new DaoMongodb\Customer;
    $customer_info                   = $customer_mgo->QueryByOpenidShopid($shop_id,$openid);
    $custominfo['customer_id']       = $customer_info->customer_id;
    $custominfo['phone']             = $customer_info->phone;
    $custominfo['is_vip']            = $customer_info->is_vip;
    $custominfo['weixin_account']    = $customer_info->weixin_account;
    $custominfo['vip_level']         = $customer_info->vip_level;
    $resp = (object)[
        'custominfo'=>$custominfo,
    ];
    LogInfo("--ok--");
    return 0;
}

//获取客户列表
function GetCustomerList(&$resp){
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id  = 4; //\Cache\Login::GetShopId();
    //通过获取用户登陆id来，获取用户信息
    $nickname  = $_['nickname'];
    $phone     = $_['phone'];
    $sex       = $_['sex'];
    $page_size = $_['page_size'];
    $page_no   = $_['page_no'];
    if(!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if(!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    $user_mgo = new DaoMongodb\User;
    $userid_list = array();
    if($nickname || $sex){
        $info['nickname'] = $nickname;
        $info['sex']      = $sex;
        $user_info = $user_mgo->GetUserList($info);
        foreach ($wx_info as $key => $value) {
            array_push($userid_list, $value->userid);
        }
    }
    $filter['phone'] = $phone;
    $filter['userid_list'] = $userid_list;
    $mgo = new \DaoMongodb\Customer;
    $custominfo = $mgo->GetCustomerList($shop_id,$filter,$page_size,$page_no);
    foreach ($custominfo as $i => &$item) {
        $user = $user_mgo->QueryById($item->userid);
        $item->nickname    = $user->nickname;
        $item->sex         = $user->sex;
        $item->user_avater = $user->user_avater;
    }
   
    $resp =(object)[
        'custominfo'=>$custominfo,
    ];
    LogInfo("--ok--");
    return 0;
}

function Register($openid, $shop_id)
{
    $entry = new \DaoMongodb\CustomerEntry();
    $entry->customer_id = \DaoRedis\Id::GenCustomerId();

    $entry->shop_id     = $shop_id;
    $entry->phone       = "";
    $entry->is_vip      = 0;
    $entry->openid      = $openid;
    $entry->property    = 0;
    $entry->ctime       = time();
    $entry->mtime       = time();
    $entry->delete      = 0;
    $mgo = new \DaoMongodb\Customer;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return null;
    }
    return $entry;
}

function GetCustomer(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $openid  = $_['openid'];
    $shop_id = $_['shop_id'];
    $seat_id = $_['seat_id'];
    LogDebug("$openid");

    if(empty($openid) || 'null' == $openid)
    {
        LogErr("param err, openid=[$openid]");
        return errcode::BROWSER_NOT_WEIXIN;
    }

    if(empty($shop_id) || 'null' == $shop_id)
    {
        LogErr("param err, shop_id=[$shop_id]");
        return errcode::SHOP_NOT_WEIXIN;
    }

    $customer = \Cache\Customer::GetInfoByOpenidShopid($openid, $shop_id);
    LogDebug($customer);
    if(!$customer)
    {
        $ret = Register($openid, $shop_id);
        if(null == $ret)
        {
            LogErr("Register err");
            return errcode::SYS_ERR;
        }
        $customer = $ret;
    }

    $shop = (object)array();
    if($shop_id)
    {
        $shop = \Cache\Shop::Get($shop_id);
        if(null == $shop)
        {
            LogErr("shop err, shop_id:[$shop_id], shop_id:[$shop_id]");
            return errcode::SHOP_NOT_EXIST;
        }

        // 检查店铺是否正常
        if(ShopIsSuspend::IsSuspend($shop->suspend))
        {
            LogErr("shop suspend, shop_id:[$shop_id]");
            return errcode::SHOP_SUSPEND;
        }
    }

    $seat = (object)array();
    if($seat_id)
    {
        $seat = \Cache\Seat::Get($seat_id);
        if(null == $seat || $seat->shop_id != $shop_id)
        {
            LogErr("seat err: seat_id[$seat_id], shop_id:[$shop_id]");
            return errcode::SEAT_NOT_EXIST;
        }
        LogDebug($seat);
        //$seat->seat_price = Util::FenToYuan($seat->seat_price);

        if($customer)
        {
            $mgo_order  = new \DaoMongodb\Order;
            $order_info = $mgo_order->GetLastOrder($customer->customer_id);
            if(null != $order_info && (time() - (int)$order_info->order_time) < 10)//3600 *4) // 4小时内，不收餐位费  // [XXX]  <<<<<<<<<<<<
            {
                LogDebug("seat_price set 0");
                $seat->seat_price = 0;
            }
        }
    }

    $resp = (object)array(
        'customer' => $customer,
        'shop'     => $shop,
        'seat'     => $seat
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}


$resp = null;
$ret  =  GetCustomer($resp);

$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
));
echo $html;
// $callback = $_['callback'];
// $js =<<< eof
// $callback($html);
// eof;
?>