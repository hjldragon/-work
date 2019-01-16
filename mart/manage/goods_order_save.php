<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 商品信息保存类
 */
require_once("current_dir_env.php");
require_once("const.php");
require_once("redis_id.php");
require_once("redis_login.php");
require_once("/www/public.sailing.com/php/mart/mgo_freight.php");
require_once("/www/public.sailing.com/php/mgo_platform.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods.php");
require_once("/www/public.sailing.com/php/mart/mgo_address.php");
require_once("/www/public.sailing.com/php/mart/mgo_invoice.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_spec.php");
require_once("/www/public.sailing.com/php/mart/mgo_stat_goods_byday.php");
require_once("/www/public.sailing.com/php/mart/mgo_acc_err_num.php");
require_once("/www/public.sailing.com/php/mgo_agent_cfg.php");
require_once("/www/public.sailing.com/php/mgo_city.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
require_once("/www/public.sailing.com/php/mgo_stat_platform_byday.php");
require_once("/www/public.sailing.com/php/mart/mgo_express_company.php");
require_once("/www/public.sailing.com/php/mart/mgo_address.php");
require_once("/www/public.sailing.com/php/page_util.php");
require_once("mgo_agent.php");
use \Pub\Mongodb as Mgo;

//保存商品订单信息
function SaveGoodsOrderInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $goods_order_id = $_["goods_order_id"];
    $address_id     = $_["address_id"];
    $invoice_id     = $_["invoice_id"];
    $goods_list     = json_decode($_["goods_list"]);
    $pay_way        = $_["pay_way"];
    $total_price    = $_["total_price"];
    $goods_freight  = $_["goods_freight"];
    $agent_id       = $_["agent_id"];
    $shop_id        = $_["shop_id"];
    $is_invoice     = $_["is_invoice"];

    if($address_id)
    {
        $dddress_mgo = new Mgo\Address;
        $address = $dddress_mgo->GetAddressById($address_id);
    }
    if($address_id)
    {
        $invoice_mgo  = new Mgo\Invoice;
        $invoice = $invoice_mgo->GetInvoiceById($invoice_id);
    }
    if($agent_id)
    {
        $rebates = PageUtil::GetAgentRebates($agent_id);
        $agent_level = $rebates['agent_level'];
    }
    $time = time();
    $goods_mgo = new Mgo\Goods;
    $spec_mgo  = new Mgo\GoodsSpec;
    if($goods_list)
    {
        $order_goods_list = [];
        $goods_num_all     = 0;// 商品总数量
        $goods_price_all   = 0;// 商品总价格
        $rebates_price_all = 0;// 商品折扣总价格
        foreach ($goods_list as $item)
        {
            $goods = (object)array();
            $goods_num_all += $item->goods_num;
            $goodsinfo = $goods_mgo->GetGoodsById($item->goods_id);
            $spec = $spec_mgo->GetSpecById($item->spec_id);
            $goods->goods_id    = $item->goods_id;
            $goods->goods_name  = $goodsinfo->goods_name;
            $goods->goods_num   = $item->goods_num;
            $goods->spec_id     = $item->spec_id;
            $goods->goods_price = $spec->price;//商品原价
            $goods->spec_name   = $spec->spec_name;
            $goods->package     = $spec->package;
            $sale_time = $spec->sale_time;//商品促销时间
            if($sale_time)
            {
                if($sale_time[0] <= $time && $time <= $sale_time[1])
                {
                    $goods->goods_price = $spec->sale_price;//商品促销价
                }
            }
            $goods->goods_price_sum = (float)$goods->goods_price*$goods->goods_num;//商品总价
            $goods_price_all += $goods->goods_price_sum;
            if(count($rebates) > 0)
            {
                switch ($goodsinfo->cate_type) {
                    case GoodsCateType::HARDWARE:
                        $goods_rebates = (float)$rebates[$agent_level]['hardware']/10;
                        break;
                    case GoodsCateType::CONSUMABLES:
                        $goods_rebates  = (float)$rebates[$agent_level]['supplies']/10;
                        break;
                    case GoodsCateType::SOFTWARE:
                        $goods_rebates  = (float)$rebates[$agent_level]['software']/10;
                        break;
                    default:
                        break;
                }

                $goods->rebates_price = (float)$goods->goods_price*$goods_rebates;//商品折扣价
                $goods->rebates_price_sum = (float)$goods->rebates_price*$goods->goods_num;//商品折扣总价
                $rebates_price_all += $goods->rebates_price_sum;
            }
            array_push($order_goods_list, $goods);
        }
    }
    if($agent_id && $pay_way == GoodsOrderPayWay::BALANCE)
    {
        $order_fee = $rebates_price_all + $goods_freight;
    }
    else
    {
        $order_fee = $goods_price_all + $goods_freight;
    }
    LogDebug($order_fee);
    if($total_price != $order_fee)
    {
        LogErr("price_all error");
        return errcode::ORDER_ST_ERR;
    }
    if(!$goods_order_id)
    {
        $goods_order_id = \DaoRedis\Id::GenGoodsOrderId();
        $entry_time = $time;
    }

    $entry = new Mgo\GoodsOrderEntry;
    $entry->goods_order_id    = $goods_order_id;
    $entry->shop_id           = $shop_id;
    $entry->agent_id          = $agent_id;
    $entry->pay_way           = $pay_way;
    $entry->goods_list        = $order_goods_list;
    $entry->order_time        = $entry_time;
    $entry->order_status      = 1;
    $entry->goods_num_all     = $goods_num_all;
    $entry->goods_price_all   = $goods_price_all;
    $entry->rebates_price_all = $rebates_price_all;
    $entry->freight_price     = $goods_freight;
    $entry->deliver_address   = $address;
    $entry->order_fee         = $order_fee;
    $entry->invoice           = $invoice;
    $entry->invoice_status    = $is_invoice;
    $entry->rebates           = $goods->rebates_price;

    $ret = Mgo\GoodsOrder::My()->Save($entry);

    if (0 != $ret) {
        LogErr("goods_order_id:[$goods_order_id] Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
        'goods_order_id'   => $entry->goods_order_id,
        'order_time'       => $entry->order_time,
        'order_status'     => $entry->order_status,
        'order_fee'        => $entry->order_fee
    );
    LogInfo("save ok");
    return 0;
}


function BalancePay(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $goods_order_id = $_["goods_order_id"];
    $agent_id       = $_['agent_id'];
    $pay_password   = $_["pay_password"];
    $total_price    = $_['total_price'];
    $phone_code     = $_['phone_code'];
    $token          = $_['token'];

    if(!$goods_order_id || !$agent_id || !$pay_password)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $mgo  = new Mgo\GoodsOrder;
    $info = $mgo->GetGoodsOrderById($goods_order_id);

    // 检查商品库存够不够
    $goods = \Pub\PageUtil::CheckGoodsStockNum($info->goods_list);
    foreach ($goods as $v) {
        $goods_id   = $v->goods_id;
        $goods_name = $v->goods_name;
        $spec_id    = $v->spec_id;
    }
    if(null != $goods){
        $resp = (object)array(
            'goods_id'   => $goods_id,
            'goods_name' => $goods_name,
            'spec_id'    => $spec_id,
        );
        LogErr("not enough, goods_id:[{$goods_id}]");
        return errcode::GOODS_NOT_ENOUGH;
    }

    if($info->agent_id != $agent_id)
    {
        LogErr("agent_id err");
        return errcode::DATA_OWNER_ERR;
    }
    $agent_mgo   = new \DaoMongodb\Agent;
    $agentinfo = $agent_mgo->QueryById($agent_id);
    // $result     = PageUtil::VerifyPhoneCode($token, $agentinfo->pay_phone, $phone_code);
    // if ($result != 0)
    // {
    //     LogDebug($result);
    //     return $result;
    // }
    if($info->rebates_price_all)
    {
        $info->order_fee = $info->rebates_price_all + $info->freight_price;
    }
    if($total_price != $info->order_fee)
    {
        LogErr("price_all error");
        return errcode::ORDER_ST_ERR;
    }
    if($agentinfo->money < $info->order_fee)
    {
        LogErr("Agent Balance error");
        return errcode::MONEY_NOT_ENOUGH;
    }
    $time = time();
    $err_mgo = new Mgo\AccErrNum;
    $err_num = $err_mgo->GetErrNumByAgent($agent_id);
    if($err_num->err_num >= 3 && ($err_num->err_time + 3*60*60 >= $time) && (date('Ymd', $err_num->err_time) == date('Ymd', $time)))
    {
        LogErr("pay_password err num much");
        return errcode::PASSWORD_ERR_TOOMANY;
    }
    if($agentinfo->pay_password != $pay_password)
    {
        // 错误次数加1
        $ret = $err_mgo->SellNumAdd($agent_id);
        LogErr("pay_password err");
        return errcode::PAY_PASSWD_ERR;
    }
    else
    {
        // 清空错误次数
        $ret = $err_mgo->SellNumEmpty($agent_id);
    }

    $entry = new Mgo\GoodsOrderEntry;
    $entry->goods_order_id = $goods_order_id;
    $entry->agent_id       = $agent_id;
    $entry->pay_way        = GoodsOrderPayWay::BALANCE;
    $entry->paid_price     = $total_price;
    $entry->order_fee      = $total_price;
    $entry->pay_time       = $time;
    $entry->order_status   = GoodsOrderStatus::WAITDELIVER;
    $ret = Mgo\GoodsOrder::My()->Save($entry);

    if (0 != $ret) {
        LogErr("goods_order_id:[$goods_order_id] Save err");
        return errcode::SYS_ERR;
    }

    $agent_entry = new \DaoMongodb\AgentEntry;

    $agent_entry->agent_id = $agent_id;
    $agent_entry->money    = $agentinfo->money - $total_price;
    $ret = $agent_mgo->Save($agent_entry);
    if(0 != $ret)
    {
        LogErr("Agent Save err");
        return errcode::SYS_ERR;
    }
    //统计订单金额及数量
    $pl_mgo     = new Mgo\StatPlatform;
    $agent_info = $agent_mgo->QueryById($agent_id);
    if($agent_info->agent_type == AgentType::GUILDAGENT)
    {
        $pl_mgo->SellNumAdd(PlatformID::ID, date('Ymd',time()), $num=['industry_goods_num'=>1,'industry_goods_amount'=>$total_price]);
    }else{
        $pl_mgo->SellNumAdd(PlatformID::ID, date('Ymd',time()), $num=['region_goods_num'=>1,'region_goods_amount'=>$total_price]);
    }
    // 增加商品销量及减去对应库存
    \Pub\PageUtil::UpdateGoodsDauSoldNum($info->goods_list);
    $resp = (object)array(
        'goods_order_id'   => $goods_order_id
    );
    LogInfo("save ok");
    return 0;
}

//改变订单状态（收货,关闭）
function ChangeOrderStatus(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $goods_order_id = $_["goods_order_id"];
    $order_status   = $_["order_status"];

    $entry = new Mgo\GoodsOrderEntry;
    $entry->goods_order_id    = $goods_order_id;
    $entry->order_status      = $order_status;
    $ret = Mgo\GoodsOrder::My()->Save($entry);

    if (0 != $ret) {
        LogErr("goods_order_id:[$goods_order_id] Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

//用户订单删除
function DeleteOrder(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $id_list = json_decode($_['id_list']);

    $mongodb = new Mgo\GoodsOrder;
    $ret = $mongodb->BatchUserDeleteById($id_list);
    if(0 != $ret)
    {
        LogErr("Delete err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("delete ok");
    return 0;
}

function OrderUrge(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $goods_order_id = $_["goods_order_id"];

    $mgo  = new Mgo\GoodsOrder;
    $info = $mgo->GetGoodsOrderById($goods_order_id);
    if(!empty($info->is_urge))
    {
        LogErr("Urge err");
        return errcode::ORDER_IS_URGE;
    }
    $entry = new Mgo\GoodsOrderEntry;
    $entry->goods_order_id = $goods_order_id;
    $entry->is_urge        = 1;
    $ret = Mgo\GoodsOrder::My()->Save($entry);

    if (0 != $ret) {
        LogErr("goods_order_id:[$goods_order_id] Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

function DeliverGoods(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $goods_order_id     = $_["goods_order_id"];
    $express_company_id = $_["express_company_id"];
    $express_num        = $_["express_num"];
    $no_deliver         = (int)$_["no_deliver"];
    $deliver_remark     = $_["deliver_remark"];
    if(!$goods_order_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo  = new Mgo\GoodsOrder;
    $info = $mgo->GetGoodsOrderById($goods_order_id);
    if($info->order_status != GoodsOrderStatus::WAITDELIVER)
    {
        LogErr("order status error");
        return errcode::ORDER_ST_ERR;
    }
    if($no_deliver != 1)
    {
        if( !$express_company_id || !$express_num)
        {
            LogErr("param err");
            return errcode::PARAM_ERR;
        }
        $exp_mgo  = new Mgo\ExpressCompany;
        $exp_info = $exp_mgo->GetExpressCompanyById($express_company_id);
        if(!$exp_info->express_company_id)
        {
            LogErr("express_company_id err");
            return errcode::EXPRESS_COMPANY_ERR;
        }
        $mongodb = new Mgo\Address;
        $addinfo = $mongodb->GetDefaultAddress(PlatformID::ID, AddressType::PLATFORM);
        if(!$addinfo->address_id)
        {
            LogErr("address error");
            return errcode::ADDRESS_NOT;
        }
    }

    $entry = new Mgo\GoodsOrderEntry;
    $entry->goods_order_id     = $goods_order_id;
    $entry->order_status       = GoodsOrderStatus::WAITCOLLECT;
    $entry->deliver_time       = time();
    $entry->express_company_id = $express_company_id;
    $entry->express_num        = $express_num;
    $entry->order_address      = $addinfo;
    $entry->no_deliver         = $no_deliver;
    $entry->deliver_remark     = $deliver_remark;
    LogDebug($entry);
    $ret = Mgo\GoodsOrder::My()->Save($entry);

    if (0 != $ret) {
        LogErr("goods_order_id:[$goods_order_id] Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['order_save']))
{
    $ret = SaveGoodsOrderInfo($resp);
}
elseif(isset($_['balance_pay']))
{
    $ret = BalancePay($resp);
}
elseif(isset($_['change_status']))
{
    $ret = ChangeOrderStatus($resp);
}
elseif(isset($_['order_del']))
{
    $ret = DeleteOrder($resp);
}
elseif(isset($_['order_urge']))
{
    $ret = OrderUrge($resp);
}
elseif(isset($_['deliver_goods']))
{
    $ret = DeliverGoods($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
    'data' => $resp
);

if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}
?><?php /******************************以下为html代码******************************/?>

