<?php
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_shop.php");
require_once("mgo_user.php");
require_once("mgo_customer.php");
require_once("mgo_evaluation.php");
require_once("mgo_praise.php");
//$_=$_REQUEST;
function GetShopInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = (string)$_['shop_id'];
    $customer_id = $_['customer_id'];
    $page_size   = $_['page_size'];
    $page_no     = $_['page_no'];
    // if (!$shop_id)
    // {
    //     $shop_id = \Cache\Login::GetShopId();
    // }
    // LogDebug("shop_id:[$shop_id]");

    //获取店铺信息
    $shopinfo = [];
    $shop_info = \Cache\Shop::Get($shop_id);

    $shopinfo['shop_id']       = $shop_info->shop_id;
    $shopinfo['shop_name']     = $shop_info->shop_name;
    $shopinfo['shop_label']    = $shop_info->shop_label;
    $shopinfo['telephone']     = $shop_info->telephone;
    $shopinfo['address']       = $shop_info->address;
    $shopinfo['open_time']     = $shop_info->open_time;
    $shopinfo['img_list']      = $shop_info->img_list;
    $mgo          = new \DaoMongodb\Evaluation;
    $customer_mgo = new \DaoMongodb\Customer;
    $user_mgo     = new \DaoMongodb\User;
    $praise_mgo   = new \DaoMongodb\Praise;
    //店铺收到的评价
    $count = 0;
    $list = $mgo->GetEvaByShopList($shop_id, $page_size, $page_no, $count);
    //店铺收到的好评
    $good_num = 0;
    $good_eva = $mgo->GetEvaByShopList($shop_id, $page_size, $page_no, $good_num, 1);
    //好评率
    $good_rate = 0;
    if(count($list) > 0)
    {
        foreach ($list as &$item) 
        {
            $info = $customer_mgo->QueryById($item->customer_id);
            $user = $user_mgo->QueryById($info->userid);
            $item->customer_name = $user->usernick;
            $item->customer_portrait = $user->user_avater;
            $data = $mgo->GetEvaluationByToId($item->id);
            if($data->id)
            {
                $item->to_content = $data->content;
                $item->to_ctime   = $data->ctime;
            }
            //是否点赞该店铺
            $pra = $praise_mgo->GetPraiseByCustomer($item->customer_id, '', $shop_id, PraiseType::PRAISE);
            if($pra->customer_id){
                $item->is_praise = $pra->is_praise;
            }else{
                $item->is_praise = 0;
            }
        }
        $good_rate = round($good_num/$count,2);
    }
    $shopinfo['evaluation']     = $list;
    $shopinfo['evaluation_num'] = $count;
    $shopinfo['good_rate']      = $good_rate;
    if($customer_id)
    {
        //是否点赞
        $praise = $praise_mgo->GetPraiseByCustomer($customer_id, '', $shop_id, PraiseType::PRAISE);
        if($praise->customer_id){
            $shopinfo['is_praise'] = $praise->is_praise;
        }else{
            $shopinfo['is_praise'] = 0;
        }
        //是否收藏
        $collect = $praise_mgo->GetPraiseByCustomer($customer_id, '', $shop_id, PraiseType::COLLECT);
        if($collect->customer_id){
            $shopinfo['is_collect'] = $collect->is_praise;
        }else{
            $shopinfo['is_collect'] = 0;
        }
    }
    
    //店铺被点赞数
    $shopinfo['praise_num'] = $praise_mgo->GetShopAllCount($shop_id);
    $resp = (object)array(
        'shopinfo' => $shopinfo,
    );

    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if (isset($_["get_shop_info"]))
{
    $ret = GetShopInfo($resp);
}

$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp,//<<<<<<<未加密返回数据
    //'crypt' => 1, // 是加密数据标记
    //'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>