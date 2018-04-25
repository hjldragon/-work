<?php
ob_start();
require_once("current_dir_env.php");
require_once("const.php");
require_once("cache.php");
require_once("cfg.php");
require_once("customer_save.php");
require_once("mgo_stat_shop_byday.php");
require_once("mgo_stat_platform_byday.php");
require_once("mgo_stat_customer_byday.php");
ob_end_clean();

if(!$_REQUEST['userid'] && !$_REQUEST['debug'] && PageUtil::IsWeixin())
{
    $url = Cfg::instance()->GetWxUrlAddr() . "/wx_userinfo.php?{$_SERVER['QUERY_STRING']}";
    header("Location: $url");
    exit();
}

function Index(&$resp)
{
    $userid  = $_REQUEST["userid"];
    $seat_id = $_REQUEST["seat"];
    $msg = "";
    if(!$seat_id)
    {
        LogErr("param err, seat_id empty");
        $msg = "系统忙...";
        alt($msg);
    }
    //餐桌信息
    $seat = \Cache\Seat::Get($seat_id);
    //店铺信息
    //$shop = \Cache\Shop::Get($seat->shop_id);
    //用户信息
    $user = \Cache\User::Get($userid);
    //客户信息
    $customer = \Cache\Customer::GetInfoByUseridShopid($userid,$seat->shop_id);
    $shop_stat      = new \DaoMongodb\StatShop;
    $platform_data  = new \DaoMongodb\StatPlatform;
    $customer_data  = new \DaoMongodb\StatCustomer;
    $entry_data     = new \DaoMongodb\StatCustomerEntry;
    $shop           = new \DaoMongodb\Shop;
    $shop_info      = $shop->GetShopById($seat->shop_id);
    $day            = date('Ymd',time());
    $platform_id    = 1;
    if(!$customer->customer_id)
    {
        $info->userid        = $userid;
        $info->shop_id       = $seat->shop_id;
        $info->phone         = $user->phone;
        $info->usernick      = $user->usernick;
        $info->sex           = $user->sex;
        //保存平台消费者数据统计
        $new_cus_num    = 1; //如果是新客户就+1
        $shop_stat->SellShopNumAdd($seat->shop_id, $day, ['new_cus_num'=>$new_cus_num], $shop_info->agent_id);
        $platform_data->SellNumAdd($platform_id, $day,['new_cus_num'=>$new_cus_num]);

        $customerinfo = CustomerSave($info);
        if(0 != $customerinfo)
        {
            LogErr("CustomerSave err");
            $msg = "系统忙...[{$customerinfo}]";
            alt($msg);
        }
        $customer_id = $info->customer_id;
    }
    else
    {
        $customer_id = $customer->customer_id;
        //如果不是新客户,如果登录进来就属于活跃人数
        $entry_data->shop_id     = $seat->shop_id;
        $entry_data->customer_id = $customer_id;
        $entry_data->day         = $day;

        $c_data = $customer_data->Save($entry_data);
        if(0 != $c_data)
        {
            LogErr("Customer active data is Save err");
            $msg = "系统忙...[{$c_data}]";
            alt($msg);
        }
        $total = 0;//当天活跃人数总数
        //根据当天扫码进来的顾客人数,找出所有扫码人数
        $customer_data->GetByDay($day, $total);
        $active_cus_num = $total;
        $platform_data->ActiveCusNum($platform_id,  $day, $active_cus_num);

        $shop_total = 0;
        $customer_data->GetByDayShop($seat->shop_id,$day, $shop_total);
        $shop_active_cus_num = $shop_total;
         LogDebug($shop_active_cus_num);
        $shop_stat->ActiveCusNum($seat->shop_id, $day, $shop_active_cus_num, $shop_info->agent_id);
    }

    $resp = (object)array(
        'seat_id'     => $seat_id,
        'shop_id'     => $seat->shop_id,
        'customer_id' => $customer_id,
        'userid'      => $userid
    );
    $url = "index.html?".http_build_query($resp);

    header("Location: $url");
    exit();
}

function alt($msg){
echo <<<eof
<script>
alert("$msg");
</script>
eof;
exit(0);
}

if(isset($_REQUEST['userid']))
{
    $ret = Index($resp);
}


?>