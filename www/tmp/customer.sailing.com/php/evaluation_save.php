<?php
/*

 * 评价信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_evaluation.php");
require_once("mgo_order.php");
require_once("redis_id.php");

function SaveEvaluation($data, $customer_id, $order_id=null)
{
    if (!$data || !$customer_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $id          = $data->id;
    $food_id     = $data->food_id;
    $shop_id     = $data->shop_id;
    $lable       = $data->lable;
    $content     = $data->content;
    $star_num    = $data->star_num;
    $to_id       = $data->to_id;
    if (!$food_id && !$shop_id && !$to_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    if (!$id)
    {
        $id = \DaoRedis\Id::GenEvaluationId();
    }
    $entry = new \DaoMongodb\EvaluationEntry();
    $mgo   = new \DaoMongodb\Evaluation();
    $entry->id          = $id;
    $entry->customer_id = $customer_id;
    $entry->food_id     = $food_id;
    $entry->shop_id     = $shop_id;
    $entry->order_id    = $order_id;
    $entry->lable       = $lable;
    $entry->content     = $content;
    $entry->star_num    = $star_num;
    $entry->to_id       = $to_id;
    $entry->delete      = 0;

    $ret = $mgo->Save($entry);
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
    $customer_id = $_['customer_id'];
    $order_id    = $_['order_id'];
    $data = json_decode($_['data']);
    $mgo = new \DaoMongodb\Order;
    $info = $mgo->GetOrderById($order_id);
    //已完成订单才能评价
    if($info->order_status != 2) //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<缺宏定义
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
        if($value->lable || $value->content || $value->star_num)
        {   
            if($value->food_id && $value->shop_id)
            {
                LogErr("Food_id and Shop_id err");
                return errcode::PARAM_ERR;
            }
            $ret = SaveEvaluation($value, $customer_id, $order_id);
            if(0 != $ret)
            {
                return $ret;
                break;
            }
        }
    }
    //订单变已评价
    $entry = new \DaoMongodb\OrderEntry;
    $entry->order_id    = $order_id;
    $entry->is_appraise = 1;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("OrderSave err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array();
    return 0;
}

function SaveEvaluationTo(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $customer_id = $_['customer_id'];
    $data->content = $_['content'];
    $data->to_id   = $_['to_id'];
    if (!$data->to_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo = new \DaoMongodb\Evaluation;
    $ordermgo = new \DaoMongodb\Order;
    $order_id = $mgo->GetEvaluationById($data->to_id)->order_id;
    $info = $ordermgo->GetOrderById($order_id);
    //订单完成超出7天无法追加评价
    if($info->lastmodtime + 60*60*24*7 < time()){
        LogErr("OrderTime err");
        return errcode::ORDER_ST_TIMEOUT;
    }
    $data->id = \DaoRedis\Id::GenEvaluationId();
    $ret = SaveEvaluation($data, $customer_id);
    if(0 != $ret)
    {
        LogErr("Save err");
        return $ret;
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
if (isset($_['evaluation_save']))
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