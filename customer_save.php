<?php
/*
 * 客户信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_customer.php");

//Permission::PageCheck();

//保存用户信息
function SaveCustomer(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $openid = $_['openid'];
    $customer_id = $_['customer_id'];
    $phone = $_['phone'];
    $is_vip = $_['is_vip'];
    $customer_name = $_['customer_name'];
    $weixin_account = $_['weixin_account'];
    $vip_level = $_['vip_level'];
    $userid    = $_['userid'];
    //获取店铺id<<<<<<<<现在在创建数据
    $shop_id = 4; //\Cache\Login::GetShopId();
    //链接mongodb数据库
    $mongodb = new \DaoMongodb\Customer;
    $entry = new \DaoMongodb\CustomerEntry;
    $entry->customer_id = $customer_id;
    $entry->phone = $phone;
    $entry->is_vip = $is_vip;
    $entry->shop_id = $shop_id;
    $entry->customer_name = $customer_name;
    $entry->openid = $openid;
    $entry->ctime = time();
    $entry->delete = 0;
    $entry->weixin_account = $weixin_account;
    $entry->vip_level = $vip_level;
    $entry->userid = $userid;
    $ret = $mongodb->Save($entry);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array();
    LogInfo("save ok");
    LogDebug($resp);
    return 0;
}

$ret = -1;
$resp = (object)array();
if (isset($_['save']))
{
    $ret = SaveCustomer($resp);
}


$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>
