<?php
/*

 * 点赞信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_praise.php");
require_once("redis_id.php");

function SavePraiseInfo(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $food_id     = $_['food_id'];
    $shop_id     = $_['shop_id'];
    $customer_id = $_['customer_id'];
    $is_praise   = $_['is_praise'];
    $type        = $_['type'];
    if (!$food_id && !$shop_id || !$customer_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    // if (!$id)
    // {
    //     $id = \DaoRedis\Id::GenPraiseId();
    // }
    $entry = new \DaoMongodb\PraiseEntry();
    $mgo   = new \DaoMongodb\Praise();
    //$entry->id          = $id;
    $entry->customer_id = $customer_id;
    $entry->food_id     = $food_id;
    $entry->shop_id     = $shop_id;
    $entry->is_praise   = $is_praise;
    $entry->type        = $type;
    $entry->delete      = 0; 

    $ret = $mgo->Save($entry);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($ret);
    $resp = (object)array();
    LogInfo("Save ok");
    return 0;
}




$ret  = -1;
$resp = (object)array();
if (isset($_['praise_save']))
{
    $ret = SavePraiseInfo($resp);
}
else{
    LogErr("param no");
    return errcode::PARAM_ERR;
}

$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>