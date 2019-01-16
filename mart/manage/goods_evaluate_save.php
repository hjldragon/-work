<?php
/*

 * 评价信息保存类
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_evaluation.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
require_once("redis_id.php");
use \Pub\Mongodb as Mgo;

function SaveEvaluationTo(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    //$id      = $_['id'];
    $content = $_['content'];
    $to_id   = $_['to_id'];
    if (!$to_id || !$content)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo = new Mgo\GoodsEvaluation;
    $info = $mgo->GetGoodsEvaluationByToId($to_id);
    if($info->id)
    {
        LogErr("evaluation is exist");
        return errcode::EVA_IS_EXIST;
    }
    if (!$id)
    {
        $id = \DaoRedis\Id::GenGoodEvaluationId();
    }
    $entry = new Mgo\GoodsEvaluationEntry();
    $entry->id             = $id;
    $entry->content        = $content;
    $entry->to_id          = $to_id;

    $ret = $mgo->Save($entry);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    return 0;
}

function EvaluationDel(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $id  = $_['id'];
    $mgo = new \DaoMongodb\Evaluation();
    $ret = $mgo->Delete($id);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($ret);

    LogInfo("Save ok");
    return 0;
}
$ret  = -1;
$resp = (object)array();
if(isset($_['evaluation_to']))
{
    $ret = SaveEvaluationTo($resp);
}
else if(isset($_['evaluation_del']))
{
    $ret = EvaluationDel($resp);
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