<?php
ini_set('date.timezone','Asia/Shanghai');
require_once "WxUnifiedorder.php";
require_once("cache.php");
require_once("mgo_seat.php");
require_once("cfg.php");
header('Content-Type:text/html;charset=utf-8');


echo "<pre>";

$req   = \Pub\Wx\Util::GetOpenid();
$openid = $req->openid;




$unifiedorder = new \Pub\Wx\Unifiedorder();


/*
    注意：
    1. 应付价格转单位分
    2. 所有字段不要为空
 */
$unifiedorder->SetParam('body', "body");                 // 商品描述
$unifiedorder->SetParam('attach', "this_is_attache");                              // 附加数据
$unifiedorder->SetParam('out_trade_no', "test_" . time());  // 商户订单号
$unifiedorder->SetParam('sub_mch_id', (string)"1467121103");                     // 子商户号
$unifiedorder->SetParam('notify_url', \Pub\Wx\Cfg::WX_URL_NOTIFY_URL);              // 通知地址
$unifiedorder->SetParam('total_fee', (int)1);                          // 总金额
$unifiedorder->SetParam('openid', $openid);




// $xml = $unifiedorder->Submit();
// public function Submit()
// {
$unifiedorder->value['appid']            = \Pub\Wx\Cfg::APPID;  // 公众账号ID
$unifiedorder->value['mch_id']           = \Pub\Wx\Cfg::MCH_ID;  // 商户号
$unifiedorder->value['device_info']      = "WEB";  // 设备号
$unifiedorder->value['nonce_str']        = md5(rand());  // 随机字符串
$unifiedorder->value['fee_type']         = "CNY";  // 货币类型
$unifiedorder->value['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];  // 终端IP
$unifiedorder->value['trade_type']       = "JSAPI";  // 交易类型(另注，用"NATIVE"可生成扫码支付的链接)
$unifiedorder->value['sign_type']        = "MD5";  // 签名类型
$unifiedorder->value['sign']             = \Pub\Wx\Util::GetSign($unifiedorder->value);  // 签名


// echo json_encode($unifiedorder->value,
//         JSON_PRETTY_PRINT
//         | JSON_UNESCAPED_SLASHES
//         | JSON_UNESCAPED_UNICODE
// ) . "<hr>\n";

$xml = \Pub\Wx\Util::ToXml($unifiedorder->value);
//echo htmlspecialchars($xml) . "<hr>";
$xml = \Pub\Wx\Util::HttpPost(\Pub\Wx\Cfg::WX_URL_UNIFIEDORDER, $xml);
// return $ret;
// }

//echo htmlspecialchars($xml) . "<hr>";

//exit(0);

$unifiedorder_ret = \Pub\Wx\Util::XmlToJson($xml);
$unifiedorder_ret = json_decode($unifiedorder_ret);
if("SUCCESS" != $unifiedorder_ret->return_code)
{
    LogErr("mch_id err");
    echo <<<eof
    <script>
    alert("[$unifiedorder_ret->return_msg]");
    </script>
eof;
    exit(0);
}
//var_dump($unifiedorder_ret);
//exit(0);
$tmp = [
    "appId"     => (string)$unifiedorder_ret->appid,
    "signType"  => "MD5",
    "package"   => "prepay_id={$unifiedorder_ret->prepay_id}",
    "timeStamp" => (string)time(),
    "nonceStr"  => md5(rand()),
];
$tmp["paySign"] = \Pub\Wx\Util::GetSign($tmp);
$pay_param = json_encode($tmp);

echo json_encode($pay_param,
        JSON_PRETTY_PRINT
        | JSON_UNESCAPED_SLASHES
        | JSON_UNESCAPED_UNICODE
);
echo "\n $param->referer";
echo "\n $param->ref";
// exit(0);
?>


<script type="text/javascript">
//调用微信JS api 支付
function jsApiCall()
{
    WeixinJSBridge.invoke(
        'getBrandWCPayRequest',
        <?php echo $pay_param; ?>,
        function(res){
            var msg = res.err_msg.split(':');
            alert(res.err_msg);
        }
    );
}

function callpay()
{
    if (typeof WeixinJSBridge == "undefined"){
        if( document.addEventListener ){
            document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
        }else if (document.attachEvent){
            document.attachEvent('WeixinJSBridgeReady', jsApiCall);
            document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
        }
    }else{
        jsApiCall();
    }
}

callpay();

</script>