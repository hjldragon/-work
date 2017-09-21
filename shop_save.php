<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 店铺信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_shop.php");
require_once("redis_id.php");


function SaveShopinfo(&$resp)
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

    $shop_id = (string)$_['shop_id'];
    $shop_name = $_['shop_name'];
    $contact = $_['contact'];
    $telephone = $_['telephone'];
    $email = $_['email'];
    $address = $_['address'];
    $opening_time =json_decode($_['opening_time']);
    $img_list=explode(',',$_['img_list']);
    $mgo = new \DaoMongodb\Shop;
    $entry = new \DaoMongodb\ShopEntry;

    $entry->shop_id = $shop_id;
    $entry->shop_name = $shop_name;
    $entry->contact = $contact;
    $entry->telephone = $telephone;
    $entry->email = $email;
    $entry->address = $address;
    $entry->opening_time   = $opening_time;
    $entry->img_list   = $img_list;


    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array();
    LogInfo("save ok");
    return 0;
}

function SaveBroadcastInfo(&$resp)
{

    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $id = (string)$_['id'];
    $shop_id = (string)$_['shop_id'];
    $type = (string)$_['type'];
    $time_range_1 = explode('.', $_['time_range_1']);
    $time_range_2 = explode(',', $_['time_range_2']);
    $content = (string)$_['content'];
    if (!$id) {
        $id = \DaoRedis\Id::GenBroadcastId();
    }
    $entry = new \DaoMongodb\BroadcastEntry();
    $mgo = new \DaoMongodb\Broadcast();
    $entry->id = $id;
    $entry->shop_id = $shop_id;
    $entry->type = $type;
    $entry->time_range_1 = $time_range_1;
    $entry->time_range_2 = $time_range_2;
    $entry->content = $content;
    $ret = $mgo->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($ret);
    $resp = (object)array();
    LogInfo("Save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if (isset($_['save']))
{
    $ret = SaveShopinfo($resp);
}elseif (isset($_['b_save'])) {
    $ret = SaveBroadcastInfo($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>
