
<?php
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html;charset=utf-8');
require_once("current_dir_env.php");
require_once("AlipayUtil.php");
require_once("AopClient.php");
require_once("cache.php");
require_once("SignData.php");
require_once("AlipayFundTransToaccountTransferRequest.php");
require_once("redis_pay.php");



function TransferAlipay(&$resp)
{
    // $_ = $_REQUEST;
    // $order_id = $_['order_id'];
    // $token    = $_['token'];
    // $price    = (float)$_['price'];
    // if(!$order_id)
    // {
    //     LogErr("OrderId err");
    //     return errcode::PARAM_ERR;
    // }
    // if($price <= 0)
    // {
    //     LogErr("price err:[$price]");
    //     return errcode::PLAY_NOT_ZERO;
    // }
    // // 订单信息
    // $order_info = \Cache\Order::Get($order_id);

    // if(!$order_info)
    // {
    //     LogErr("Order err");
    //     return errcode::PARAM_ERR;
    // }
    // if(NewOrderStatus::PAY == $order_info->pay_status)
    // {
    //     LogErr("Order Status err");
    //     return errcode::ORDER_ST_PAID;
    // }
    // if($order_info->order_payable < $price)
    // {
    //     LogErr("price err");
    //     return errcode::FEE_MONEY_ERR;
    // }
    // // 店铺信息
    // $shop_info = \Cache\Shop::Get($order_info->shop_id);

    // $appid       = $shop_info->alipay_set->alipay_app_id;
    // $public_key  = $shop_info->alipay_set->public_key;
    // $private_key = $shop_info->alipay_set->private_key;

    // if(!$appid || !$public_key || !$private_key)
    // {
    //     LogErr("order err, order_id:[$order_id]");
    //     return errcode::ALIPAYPLAY_NO_SUPPORT;
    // }
//2088231090189625
    $content = (object)array();
    $attach = (object)array("order_id"=>$order_info->order_id,"token"=>$token);
    $out_biz_no = time() . "_TR";
    $content->out_biz_no    = $out_biz_no;                       //商户转账唯一订单号
    $content->payee_type    = "ALIPAY_USERID";                       //收款方账户类型
    $content->payee_account = "2088231090189625";                       //收款方账户
    $content->amount        = 0.2;                              //转账金额，单位：元
    $content->remark        = "测试转账";               //订单标题
    $content = json_encode($content);

    $aop = new AopClient ();
    $aop->appId              = Cfg::instance()->alipay->appid;
    $aop->rsaPrivateKey      = Cfg::instance()->alipay->privatekey;
    $aop->alipayrsaPublicKey = Cfg::instance()->alipay->publickey;
    $aop->apiVersion         = '1.0';
    $aop->signType           = 'RSA2';
    $aop->postCharset        = 'UTF-8';
    $aop->format             = 'json';
    $request = new AlipayFundTransToaccountTransferRequest();
    $request->setBizContent($content);
    $result = $aop->execute($request);
    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
    $resultCode = $result->$responseNode->code;
    var_dump($result);die;
    LogDebug($result);
    if(empty($resultCode)||$resultCode != 10000){
        LogErr("QRCODE err");
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

$ret = TransferAlipay($resp);


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

