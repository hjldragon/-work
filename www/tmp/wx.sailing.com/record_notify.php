<?php
require_once("current_dir_env.php");
require_once("const.php");
require_once("mgo_agent.php");
require_once "WxUtil.php";
require_once("/www/public.sailing.com/php/mgo_pay_record.php");
require_once("/www/public.sailing.com/php/mgo_pay_record_byday.php");

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
//     //
//        [appid] => wxaaceede0e7695fcf
//        [attach] => {"record_id":"PR32","token":"T1HIPw4i1fyHRhbF"}
//        [bank_type] => CFT
//        [cash_fee] => 1
//        [device_info] => WEB
//        [fee_type] => CNY
//        [is_subscribe] => N
//        [mch_id] => 1464120802
//        [nonce_str] => 85f6d10b617da067aac8a4bd123359b5
//        [openid] => oVQGs1JYNZBHgHPKGwJfz-HdFdHo
//        [out_trade_no] => 1527562960_PR32
//        [result_code] => SUCCESS
//        [return_code] => SUCCESS
//        [sign] => 2743845936F7E4C6B331ABE35112C65E
//        [sub_mch_id] => 1467121102
//        [time_end] => 20180529110333
//        [total_fee] => 1
//        [trade_type] => NATIVE
//        [transaction_id] => 4200000124201805297143351798

//     // )

    $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
    $ary = \Pub\Wx\Util::FromXml($xml);
    LogDebug($ary);
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


    $pay_info = json_decode($ary['attach']);
    if(!$pay_info || !$pay_info->record_id)
    {
        LogErr("param err");
        return;
    }
   LogDebug($pay_info->agent_level);
    $mgo         = new Mgo\PayRecord;
    $entry       = new Mgo\PayRecordEntry;
    $agent_mgo   = new \DaoMongodb\Agent;
    $agent_entry = new \DaoMongodb\AgentEntry;
    $pay_mgo     = new Mgo\StatPayRecord;
    $record_info = $mgo->GetInfoByRecordId($pay_info->record_id);
    $info        = $agent_mgo->QueryById($record_info->agent_id);
    $paid_price = $ary['total_fee']/100;
    $old_money  = $info->money;

    $new_money  = $old_money+$paid_price;

    $entry->record_id        = $pay_info->record_id;
    $entry->agent_id         = $info->agent_id;
    $entry->pay_way          = CZPayWay::WX;
    $entry->pay_status       = CZPayStatus::PAY;
    $entry->pay_money        = $paid_price;
    $entry->pay_time         = time();

    $ret      = $mgo->Save($entry);

    $agent_entry->agent_id    = $info->agent_id;
    $agent_entry->money       = $new_money;
    $agent_entry->agent_level = $pay_info->agent_level;
    $ret_agent = $agent_mgo->Save($agent_entry);

    if(0 != $ret || 0 != $ret_agent)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    //统计充值成功的订单数据
    $day           = date('Ymd',time());
    $platform_id   = PlatformID::ID;
    $pay_mgo->SellNumAdd($platform_id, $day, $info->agent_type, ['money'=>$paid_price]);

    $token = $pay_info->token;
    if($token)
    {
        // 因微信直接发来的消息是没有经过加解密解析过程,所
        // 在cache中没有记录token相关数据，这里手动加载
        \Cache\Login::Get($token);
        $ret_json     = PageUtil::NotifyWxPay($pay_info->record_id, $paid_price, $token);
        $ret_json_obj = json_decode($ret_json);
        if(0 != $ret_json_obj->ret)
        {
            LogErr("weixin pay send err");
        }
    }
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
