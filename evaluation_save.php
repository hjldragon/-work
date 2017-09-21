<?php
/*

 * 评价信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_evaluation.php");
require_once("redis_id.php");

function SaveEvaluationInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $id = (string)$_['id'];
    $customer_id = (string)$_['customer_id'];
    $food_id = (string)$_['food_id'];
    $order_id = (string)$_['order_id'];
    $content = (string)$_['content'];
    $ctime = (string)time();
    $is_good = (string)$_['is_good'];
    if (!$id)
    {
        $id = \DaoRedis\Id::GenEvaluationId();
    }
    $entry = new \DaoMongodb\EvaluationEntry();
    $mgo = new \DaoMongodb\Evaluation();
    $entry->id = $id;
    $entry->customer_id = $customer_id;
    $entry->food_id = $food_id;
    $entry->order_id = $order_id;
    $entry->content = $content;
    $entry->ctime = $ctime;
    $entry->is_good = $is_good;

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

$ret = -1;
$resp = (object)array();
if (isset($_['save']))
{
    $ret = SaveEvaluationInfo($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>