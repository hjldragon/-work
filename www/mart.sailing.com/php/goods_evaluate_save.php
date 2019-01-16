<?php
/*

 * 评价信息保存类
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_evaluation.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
require_once("redis_id.php");
use \Pub\Mongodb as Mgo;

function SaveEvaluation($data, $agent_id=null, $shop_id=null, $goods_order_id=null)
{
    if (!$data || (!$agent_id && !$shop_id))
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $id          = $data->id;
    $goods_id     = $data->goods_id;
    //$lable       = $data->lable;
    $content     = $data->content;
    $star_num    = $data->star_num;
    $to_id       = $data->to_id;
    if (!$goods_id && !$to_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    if (!$id)
    {
        $id = \DaoRedis\Id::GenGoodEvaluationId();
    }
    $entry = new Mgo\GoodsEvaluationEntry();
    $entry->id             = $id;
    $entry->agent_id       = $agent_id;
    $entry->shop_id        = $shop_id;
    $entry->goods_id       = $goods_id;
    $entry->goods_order_id = $goods_order_id;
    //$entry->lable        = $lable;
    $entry->content        = $content;
    $entry->star_num       = $star_num;
    $entry->to_id          = $to_id;


    $ret = Mgo\GoodsEvaluation::My()->Save($entry);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($ret);

    LogInfo("Save ok");
    return 0;
}

function SaveEvaluationInfo(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id        = $_['shop_id'];
    $agent_id       = $_['agent_id'];
    $goods_order_id = $_['goods_order_id'];
    $data = json_decode($_['data']);
    $mgo = new Mgo\GoodsOrder;
    $info = $mgo->GetGoodsOrderById($goods_order_id);
    //待评价订单才能评价
    if($info->order_status != GoodsOrderStatus::WAITEVALUATION)
    {
        LogErr("OrderStatus err");
        return errcode::ORDER_STATUS_ERR;
    }
    //订单完成超出7天无法评价
    if($info->lastmodtime + 60*60*24*7 < time())
    {
        LogErr("OrderTime err");
        return errcode::ORDER_ST_TIMEOUT;
    }
    foreach ($data as $key => $value)
    {
        if($value->content || $value->star_num)
        {

            $ret = SaveEvaluation($value, $agent_id, $shop_id, $goods_order_id);
            if(0 != $ret)
            {
                return $ret;
                break;
            }
        }
    }
    //订单变已评价
    $entry = new Mgo\GoodsOrderEntry;
    $entry->goods_order_id = $goods_order_id;
    $entry->order_status   = GoodsOrderStatus::BEEVALUATION;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("GoodsOrderSave err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array();
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
if (isset($_['save_evaluation']))
{
    $ret = SaveEvaluationInfo($resp);
}
else if(isset($_['evaluation_to']))
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