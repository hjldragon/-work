<?php
require_once("current_dir_env.php");
require_once("const.php");
require_once("mgo_agent.php");
require_once("/www/public.sailing.com/php/mgo_pay_record.php");
require_once("/www/public.sailing.com/php/mgo_pay_record_byday.php");
use \Pub\Mongodb as Mgo;


function pay()
{


//[gmt_create] => 2018-05-29 14:19:44
//[charset] => UTF-8
//[seller_email] => liuxiao@xinchihuo.com.cn
//[subject] => 深圳前海赛领科技有限公司
//[sign] => QUXmzak4rbkzX1VeIpMLcGLjBlS9HpNPwiT8JxBxeH2lPjHHnrMlRfGFVwEFlUi2aoqrD2ei9Stfg8zYhKZctTsj8ILcJJL2tHPkiE0Fb9nSfxY/snPzlpy948/KGI2WzXkQJpL0lbI2yprqX08bY32Hyodny3KGqHrGTnFP0mVOw1eFSNGYeEWmHZZ533x4/xH3Ksl/XcMpUCj5wwDrAhDY8wH01imH2CPEEp3c5TRCpNCV20MzAW/F6hxANXWeDKW60p1IxpME51aSU6J1TClwfjHsVTLTf+wQY8XPmio4Rbz3D8DZyjTmW4pVb3REx/jbGHGD61fXWfBETKE7VA==
//[body] => {"record_id":"PR70","token":"T1HIPw4i1fyHRhbF"}
//[buyer_id] => 2088802173919095
//[invoice_amount] => 0.01
//[notify_id] => c00121b004453698d597921b43560b7gp5
//[fund_bill_list] => [{"amount":"0.01","fundChannel":"ALIPAYACCOUNT"}]
//[notify_type] => trade_status_sync
//[trade_status] => TRADE_SUCCESS
//[receipt_amount] => 0.01
//[buyer_pay_amount] => 0.01
//[app_id] => 2018031302365379
//[sign_type] => RSA2
//[seller_id] => 2088031549791447
//[gmt_payment] => 2018-05-29 14:20:03
//[notify_time] => 2018-05-29 14:20:03
//[version] => 1.0
//[out_trade_no] => 1527574769_
//[total_amount] => 0.01
//[trade_no] => 2018052921001004090512331165
//[auth_app_id] => 2018031302365379
//[buyer_logon_id] => 182****6916
//[point_amount] => 0.00

//     // )

    $data = $_POST;
    LogDebug($data);
    if("TRADE_SUCCESS" != $data['trade_status'])
    {
        LogErr("trade_status:{$data['trade_status']}");
        return errcode::SYS_ERR;
    }

    $pay_info = json_decode($data['body']);
    if(!$pay_info || !$pay_info->record_id)
    {
        LogErr("param err");
        return;
    }

    $mgo         = new Mgo\PayRecord;
    $entry       = new Mgo\PayRecordEntry;
    $agent_mgo   = new \DaoMongodb\Agent;
    $agent_entry = new \DaoMongodb\AgentEntry;
    $pay_mgo     = new Mgo\StatPayRecord;
    $record_info = $mgo->GetInfoByRecordId($pay_info->record_id);
    $info        = $agent_mgo->QueryById($record_info->agent_id);
    $paid_price = $data['total_amount'];
    $old_money  = $info->money;

    $new_money  = $old_money+$paid_price;

    $entry->record_id        = $pay_info->record_id;
    $entry->agent_id         = $info->agent_id;
    $entry->pay_way          = CZPayWay::ALIPAY;
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
        $ret_json     = PageUtil::NotifyAlipayPay($pay_info->record_id, $paid_price, $token);
        $ret_json_obj = json_decode($ret_json);
        if(0 != $ret_json_obj->ret)
        {
            LogErr("alipay send err");
        }
    }
    return 0;
}

function main()
{
    LogDebug("begin...");
    $ret = pay();
    if(0 == $ret)
    {
        $ret_xml = "success";
        echo $ret_xml;
    }
}

main();
?>
