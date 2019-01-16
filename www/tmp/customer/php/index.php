<?php
require_once("current_dir_env.php");
require_once("const.php");
require_once("cache.php");
require_once("cfg.php");
require_once("customer_save.php");


if(!$_REQUEST['userid'] && !$_REQUEST['debug'] && PageUtil::IsWeixin())
{
    $url = Cfg::instance()->GetWxUrlAddr() . "/wx_userinfo_tmp.php?{$_SERVER['QUERY_STRING']}";
    header("Location: $url");
    exit();
}

function Index(&$resp)
{
    $openid  = $_REQUEST["openid"];
    $userid  = $_REQUEST["userid"];
    $seat_id = $_REQUEST["seat_id"];

    if(!$seat_id)
    {
        LogErr("param err, seat_id empty");
        echo "系统忙...";exit();
    }
    //餐桌信息
    $seat = \Cache\Seat::Get($seat_id);
    //店铺信息
    //$shop = \Cache\Shop::Get($seat->shop_id);
    //用户信息
    $user = \Cache\User::Get($userid);
    //客户信息
    $customer = \Cache\Customer::GetInfoByOpenidShopid($openid,$seat->shop_id);
    if(!$customer->customer_id){
        $info->userid        = $userid;
        $info->shop_id       = $seat->shop_id;
        $info->customer_name = $user->usernick;
        $info->openid        = $openid;
        $customerinfo = CustomerSave($info);
        if(0 != $customerinfo->ret){
            LogErr("CustomerSave err");
            echo "系统忙...";exit();
        }
        $customer_id = $customerinfo->data->customer_id;
    }else{
        $customer_id = $customer->customer_id;
    }

    $resp = (object)array(
        'seat_id'     => $seat_id,
        'shop_id'     => $seat->shop_id,
        'customer_id' => $customer_id
    );
    $url = "index.html?".http_build_query($resp);
    header("Location: $url");
    exit();
}


if(isset($_REQUEST['userid']))
{
    $ret = Index($resp);
}


?>