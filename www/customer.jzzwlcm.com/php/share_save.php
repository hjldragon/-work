<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 菜品分享保存类
 */
require_once("current_dir_env.php");
require_once("mgo_share.php");
require_once("redis_id.php");

function ShareSave(&$resp)
{

    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $food_id        = $_['food_id'];
    $shop_id        = $_['shop_id'];
    $customer_id    = $_['customer_id'];
    if((!$food_id && !$shop_id) || ($food_id && $shop_id) || !$customer_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mongodb = new \DaoMongodb\Share;
    $entry   = new \DaoMongodb\ShareEntry;
    $id = \DaoRedis\Id::GenShareId();
    $entry->id          = $id;
    $entry->food_id     = $food_id;
    $entry->shop_id     = $shop_id;
    $entry->customer_id = $customer_id;

    $ret = $mongodb->Save($entry);

    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($entry);

    $resp = (object)array();
    
    LogInfo("save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if (isset($_['share_save']))
{
    $ret = ShareSave($resp);
}

$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>
