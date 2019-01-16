<?php
ini_set('date.timezone','Asia/Shanghai');
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/weixin/WxUnifiedorder.php");
require_once("cache.php");
require_once("redis_pay.php");
$_ = $_REQUEST;
function PayRecordQrWxpay(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $record_id   = $_['record_id'];
    $token       = $_['token'];
    $price       = $_['record_money'];
    $agent_level = $_['agent_level'];
    LogDebug($agent_level);
    if(!$record_id)
    {
        LogErr("RecordId err");
        return errcode::PARAM_ERR;
    }
    if($price <= 0)
    {
        LogErr("price err:[$price]");
        return errcode::PLAY_NOT_ZERO;
    }
    //LogDebug($price);
    // 订单信息
    $pay_record_info = \Cache\PayRecord::Get($record_id);

    if(!$pay_record_info)
    {
        LogErr("pay record data is err");
        return errcode::ORDER_NOT_EXIST;
    }
    if(CZPayStatus::PAY == $pay_record_info->pay_status)
    {
        LogErr("Pay record Status err");
        return errcode::ORDER_ST_PAID;
    }

    $unifiedorder = new \Pub\Wx\Unifiedorder();
    $attach       = (object)array("record_id"=>$pay_record_info->record_id,"agent_level"=>$agent_level,"token"=>$token);
    if(!$param->attach)
    {
        $param->attach = json_encode($attach);
    }
    $out_trade_no = time() . "_" . $pay_record_info->record_id;
    //应付价格转单位分
    $total_fee = $price*100;
    $unifiedorder->SetParam('body', (string)'深圳前海赛领科技有限公司');            // 商品描述
    $unifiedorder->SetParam('attach', $param->attach);                         // 附加数据
    $unifiedorder->SetParam('out_trade_no', $out_trade_no);                    // 商户订单号
    $unifiedorder->SetParam('sub_mch_id', \Pub\Wx\Cfg::SUB_MCH_ID);        // 子商户号
    $unifiedorder->SetParam('notify_url', \Pub\Wx\Cfg::WX_URL_RECORD_NOTIFY);      // 通知地址
    $unifiedorder->SetParam('total_fee', (int)$total_fee);                     // 总金额


    $xml              = $unifiedorder->SubmitQRpay();
    $unifiedorder_ret = \Pub\Wx\Util::XmlToJson($xml);
    $unifiedorder_ret = json_decode($unifiedorder_ret);
    if($unifiedorder_ret->result_code != 'SUCCESS')
    {
        LogErr("QRCODE err");
        return errcode::PARAM_ERR;
    }

    $resp = (object)array(
        'url' => $unifiedorder_ret->code_url
    );
    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();


$ret = PayRecordQrWxpay($resp);


$result = (object)array(
    'ret' => $ret,
    'msg'  => errcode::toString($ret),
    'data' => $resp
);
// 允许跨域访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Content-Type: text/html; charset=utf-8');
echo json_encode($result);
?>
