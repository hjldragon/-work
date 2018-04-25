<?php
/*

 * 评价信息读取类
 */
require_once("current_dir_env.php");
require_once("mgo_evaluation.php");
require_once("cache.php");
require_once("mgo_praise.php");
require_once("redis_id.php");


function GetEvaluationInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $customer_id = $_['customer_id'];
    $mgo = new \DaoMongodb\Evaluation;
    $praisemgo = new \DaoMongodb\Praise;
    $info = $mgo->GetEvaByCustomerList($customer_id);
    foreach ($info as $key => &$value)
    {
        if($value->food_id)
        {
            $food = \Cache\Food::Get($value->food_id);
            $value->food_name = $food->food_name;
            $value->food_img_list = $food->food_img_list; 
        }
        if($value->shop_id)
        {
            $shop = \Cache\Shop::Get($value->shop_id);
            $value->shop_name = $food->shop_name;
            $value->shop_logo = $shop_logo; 
        }
        $data = $mgo->GetEvaluationByToId($value->id);
        if($data->id)
        {
            $value->to_content = $data->content;
            $value->to_time    = $data->ctime;
        }
        $praise = $praisemgo->GetPraiseByCustomer($customer_id, $value->food_id, $value->shop_id, PraiseType::PRAISE);
        if($praise->customer_id)
        {
            $value->is_praise = $praise->is_praise;
        }
        else
        {
            $value->is_praise = 0;
        }

    }
    $resp = (object)array(
        'list' => $info
    );
    return 0;
}


$ret  = -1;
$resp = (object)array();
if (isset($_['evaluation_get']))
{
    $ret = GetEvaluationInfo($resp);
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