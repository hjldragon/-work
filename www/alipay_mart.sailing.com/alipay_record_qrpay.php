
<?php
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once("current_dir_env.php");
require_once("AlipayUtil.php");
require_once("AopClient.php");
require_once("cache.php");
require_once("SignData.php");
require_once("AlipayTradePrecreateRequest.php");
require_once("redis_pay.php");

function PayRecordQrAlipay(&$resp)
{
    $_ = $_REQUEST;
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $record_id   = $_['record_id'];
    $token       = $_['token'];
    $price       = $_['record_money'];
    $agent_level = $_['agent_level'];
    if(!$record_id)
    {
        LogErr("RecordId is empty");
        return errcode::PARAM_ERR;
    }
    if($price <= 0)
    {
        LogErr("price err:[$price]");
        return errcode::PLAY_NOT_ZERO;
    }
    // 订单信息
    $pay_record_info = \Cache\PayRecord::Get($record_id);

    if(!$pay_record_info)
    {
        LogErr("pay record data is err");
        return errcode::PARAM_ERR;
    }
    if(CZPayStatus::PAY == $pay_record_info->pay_status)
    {
        LogErr("Pay record Status err");
        return errcode::PARAM_ERR;
    }

    $content      = (object)array();
    $attach = (object)array("record_id"=>$pay_record_info->record_id,"token"=>$token,"agent_level"=>$agent_level,);
    $out_trade_no = time() . "_" . $pay_record_info->order_id;
    $content->out_trade_no = $out_trade_no;                       //商户订单号
    $content->total_amount = $price;                              //订单总金额
    $content->subject      = '深圳前海赛领科技有限公司';               //订单标题
    $content->body         = json_encode($attach);                //对交易或商品的描述

    $content = json_encode($content);

    $aop = new AopClient ();
    $aop->appId              = Cfg::instance()->alipay->appid;
    $aop->rsaPrivateKey      = Cfg::instance()->alipay->privatekey;
    $aop->alipayrsaPublicKey = Cfg::instance()->alipay->publickey;
    $aop->apiVersion         = '1.0';
    $aop->signType           = 'RSA2';
    $aop->postCharset        = 'UTF-8';
    $aop->format             = 'json';
    $request = new AlipayTradePrecreateRequest();
    $request->setBizContent($content);
    $request->setNotifyUrl(Cfg::instance()->alipay->alipay_record_notify);
    $result = $aop->execute($request);
    //LogDebug($result);
    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
    $resultCode = $result->$responseNode->code;
    if(empty($resultCode)||$resultCode != 10000){
        LogErr("QRCODE err:" . json_encode($result));
        return errcode::PARAM_ERR;
    }

    $resp = (object)array(
        'url' => $result->$responseNode->qr_code
    );
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if($_['type'])
{
    $ret = PayRecordQrAlipay($resp);
}

$info = (object)array(
    'ret' => $ret,
    'msg'  => errcode::toString($ret),
    'data' => $resp
);

//echo json_encode($info);
// 允许跨域访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Content-Type: text/html; charset=utf-8');
echo json_encode($info);
?>

