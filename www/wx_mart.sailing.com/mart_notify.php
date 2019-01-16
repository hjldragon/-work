<?php
require_once("current_dir_env.php");
require_once("mgo_agent.php");
require_once("const.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
require_once("/www/public.sailing.com/php/mgo_stat_platform_byday.php");
require_once("/www/public.sailing.com/php/page_util.php");
require_once("redis_pay.php");
require_once("/www/public.sailing.com/php/weixin/WxUtil.php");
use \Pub\Mongodb as Mgo;

function pay()
{

//     // <hr>GLOBALS['HTTP_RAW_POST_DATA']
//     $xml =<<<eof
//     <xml><appid><![CDATA[wxaaceede0e7695fcf]]></appid>
//     <attach><![CDATA[{"order_id":100000000023}]]></attach>
//     <bank_type><![CDATA[CMB_CREDIT]]></bank_type>
//     <cash_fee><![CDATA[1]]></cash_fee>
//     <device_info><![CDATA[WEB]]></device_info>
//     <fee_type><![CDATA[CNY]]></fee_type>
//     <is_subscribe><![CDATA[Y]]></is_subscribe>
//     <mch_id><![CDATA[1464120802]]></mch_id>
//     <nonce_str><![CDATA[324c948af2ec9760745db78fd8ac15c8]]></nonce_str>
//     <openid><![CDATA[oVQGs1Imf8L2EBcn2N0DyJRKQ8pc]]></openid>
//     <out_trade_no><![CDATA[1495991051]]></out_trade_no>
//     <result_code><![CDATA[SUCCESS]]></result_code>
//     <return_code><![CDATA[SUCCESS]]></return_code>
//     <sign><![CDATA[92318D3A2F4ADE9818D383693EA89C64]]></sign>
//     <sub_mch_id><![CDATA[1467121102]]></sub_mch_id>
//     <time_end><![CDATA[20170529010447]]></time_end>
//     <total_fee>1</total_fee>
//     <trade_type><![CDATA[JSAPI]]></trade_type>
//     <transaction_id><![CDATA[4004402001201705293101872675]]></transaction_id>
//     </xml>
// eof;
//     $xml =<<<eof
//     <xml><appid><![CDATA[wxaaceede0e7695fcf]]></appid>
//     <attach><![CDATA[{"order_id":"214"}]]></attach>
//     <bank_type><![CDATA[CFT]]></bank_type>
//     <cash_fee><![CDATA[1]]></cash_fee>
//     <device_info><![CDATA[WEB]]></device_info>
//     <fee_type><![CDATA[CNY]]></fee_type>
//     <is_subscribe><![CDATA[N]]></is_subscribe>
//     <mch_id><![CDATA[1464120802]]></mch_id>
//     <nonce_str><![CDATA[ce2a99c493958f18629008753fe5cd03]]></nonce_str>
//     <openid><![CDATA[oVQGs1CM07N3qgJNTyQMAIkmxEMw]]></openid>
//     <out_trade_no><![CDATA[1512465763_214]]></out_trade_no>
//     <result_code><![CDATA[SUCCESS]]></result_code>
//     <return_code><![CDATA[SUCCESS]]></return_code>
//     <sign><![CDATA[DF73867CE255EC53A05534F2F2645C8A]]></sign>
//     <sub_mch_id><![CDATA[1467121102]]></sub_mch_id>
//     <time_end><![CDATA[20171205172257]]></time_end>
//     <total_fee>1</total_fee>
//     <trade_type><![CDATA[JSAPI]]></trade_type>
//     <transaction_id><![CDATA[4200000026201712059263000413]]></transaction_id>
//     </xml>
// eof;

//     // Array
//     // (
//     //     [appid] => wxaaceede0e7695fcf
//     //     [attach] => {"order_id":100000000023}
//     //     [bank_type] => CMB_CREDIT
//     //     [cash_fee] => 1
//     //     [device_info] => WEB
//     //     [fee_type] => CNY
//     //     [is_subscribe] => Y
//     //     [mch_id] => 1464120802
//     //     [nonce_str] => 324c948af2ec9760745db78fd8ac15c8
//     //     [openid] => oVQGs1Imf8L2EBcn2N0DyJRKQ8pc
//     //     [out_trade_no] => 1495991051
//     //     [result_code] => SUCCESS
//     //     [return_code] => SUCCESS
//     //     [sign] => 92318D3A2F4ADE9818D383693EA89C64
//     //     [sub_mch_id] => 1467121102
//     //     [time_end] => 20170529010447
//     //     [total_fee] => 1
//     //     [trade_type] => JSAPI
//     //     [transaction_id] => 4004402001201705293101872675
//     // )

    $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
    $ary = \Pub\Wx\Util::FromXml($xml);
    //LogDebug($ary);
    if($_REQUEST['debug'])
    {
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        echo "<pre>";
        print_r($ary);
        echo "<hr>";
        print_r(Cfg::instance());
    }


    $sign = \Pub\Wx\Util::GetSign($ary);
    if($sign !== $ary['sign'] || "SUCCESS" != $ary['return_code'])
    {
        LogErr("sign err, sign:[$sign], return_code:{$ary['return_code']}");
        return errcode::SYS_ERR;
    }


    $order_info = json_decode($ary['attach']);
    if(!$order_info || !$order_info->goods_order_id)
    {
        LogErr("param err");
        return;
    }

    $paid_price = $ary['total_fee']/100;
    $time = time();
    $mgo  = new Mgo\GoodsOrder;
    $info = $mgo->GetGoodsOrderById($order_info->goods_order_id);
    if($info->order_status == GoodsOrderStatus::WAITDELIVER)
    {
        LogInfo("--ok--");
        return 0;
    }
    $entry = new Mgo\GoodsOrderEntry;
    $entry->goods_order_id = $order_info->goods_order_id;
    $entry->pay_way        = GoodsOrderPayWay::WX;
    $entry->paid_price     = $paid_price;
    $entry->pay_time       = $time;
    $entry->order_status   = GoodsOrderStatus::WAITDELIVER;
    $ret = $mgo->Save($entry);
    LogDebug($entry);
    if (0 != $ret) {
        LogErr("goods_order_id:[$goods_order_id] Save err");
        return errcode::SYS_ERR;
    }

    //统计订单金额及数量
    if($info->agent_id)
    {
        $pl_mgo     = new Mgo\StatPlatform;
        $agent_mgo   = new \DaoMongodb\Agent;
        $agent_info = $agent_mgo->QueryById($info->agent_id);
        if($agent_info->agent_type == AgentType::GUILDAGENT)
        {
            $pl_mgo->SellNumAdd(PlatformID::ID, date('Ymd',$time), $num=['industry_goods_num'=>1,'industry_goods_amount'=>$paid_price]);
        }else{
            $pl_mgo->SellNumAdd(PlatformID::ID, date('Ymd',$time), $num=['region_goods_num'=>1,'region_goods_amount'=>$paid_price]);
        }
    }
    LogDebug($order_info->goods_order_id);
    // 增加商品销量及减去对应库存
    \Pub\PageUtil::UpdateGoodsDauSoldNum($order_info->goods_order_id);

    //LogDebug($ret);
    return 0;
}

function main()
{
    $ret = pay();
    if(0 == $ret)
    {
        $ret_xml =<<<eof
<xml>
   <return_code><![CDATA[SUCCESS]]></return_code>
   <return_msg><![CDATA[OK]]></return_msg>
</xml>
eof;
        echo $ret_xml;
    }
    else
    {

        $ret_xml =<<<eof
<xml>
   <return_code><![CDATA[FAIL]]></return_code>
   <return_msg><![CDATA[出错]]></return_msg>
</xml>
eof;
        echo $ret_xml;
    }
}

main();

?>
