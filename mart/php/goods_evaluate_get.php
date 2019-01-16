<?php
/*

 * 评价信息查看类
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_evaluation.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_praise.php");
require_once("redis_id.php");
use \Pub\Mongodb as Mgo;

function GetEvaluationList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $goods_id  = $_['goods_id'];
    $page_size = $_['page_size'];
    $page_no   = $_['page_no'];
    $total = 0;
    $mgo = new Mgo\GoodsEvaluation;
    $praise_mgo = new Mgo\GoodsPraise;
    $info = $mgo->GetGoodsEvaList($goods_id, $page_size, $page_no, $total);
    foreach ($info as $key => &$value)
    {
        if($value->agent_id)
        {
            $agent = \Cache\Agent::Get($value->agent_id);
            $value->agent_name = $agent->agent_name;
            $value->agent_logo = $agent->agent_logo;
        }
        if($value->shop_id)
        {
            $shop = \Cache\Shop::Get($value->shop_id);
            $value->shop_name = $shop->shop_name;
            $value->shop_logo = $shop->shop_logo;
        }
        //是否点赞
        $praise = $praise_mgo->GetPraiseByCustomer($value->agent_id, $value->shop_id, $goods_id, PraiseType::PRAISE);
        if($praise->goods_id){
            $value->is_praise = $praise->is_praise;
        }else{
            $value->is_praise = 0;
        }

        $data = $mgo->GetGoodsEvaluationByToId($value->id);
        if($data->id)
        {
            $value->to_content = $data->content;
            $value->to_time    = $data->ctime;
        }
    }
    $good_rate = GetGoodsRate($goods_id);
    $resp = (object)array(
        'list'      => $info,
        'good_rate' => $good_rate,
        'total'     => $total
    );
    return 0;
}

//好评率
function GetGoodsRate($goods_id)
{
    $mgo     = new Mgo\GoodsEvaluation;
    $all     = $mgo->GetGoodsAllCount($goods_id);
    $is_good = $mgo->GetGoodsAllCount($goods_id, 1);
    if($all > 0){
        $good  = round($is_good / $all, 2);
        return $good;
    }
    return 1;
}

$ret  = -1;
$resp = (object)array();
if (isset($_['get_evaluate_list']))
{
    $ret = GetEvaluationList($resp);
}else{
    LogErr("param no");
    return errcode::PARAM_ERR;
}

$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>