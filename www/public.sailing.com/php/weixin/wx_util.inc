<?php
/*
 * [Rocky 2018-07-12]
 * 微信操作相关功能
 */
namespace Wx;

const WX_URL_AUTHORIZE = 'https://open.weixin.qq.com/connect/oauth2/authorize';
const WX_URL_ACCESS_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/access_token';
const WX_URL_USERINFO = 'https://api.weixin.qq.com/sns/userinfo';
const WX_URL_UNIFIEDORDER = "https://api.mch.weixin.qq.com/pay/unifiedorder";

// 姓别
// https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140842
const SEX_MAIL = 1;     // 男
const SEX_FEMAIL = 2;   // 女

class Util
{
    private static $init = false; // 是否初始化过

    // 要在外部设置下列值
    public static $appid = null;   // 公从平台->开发,基本配置->开发者ID(AppID)
    public static $secret = null;  // 公从平台->开发,基本配置->开发者密码(AppSecret)
    public static $mch_id = null;
    public static $key = null;     // KEY(API密钥) (微信商户平台(pay.weixin.qq.com)-->账户设置-->API安全-->密钥设置)
    public static $notify_url = null; // 后台接收回调通知

    public static function Init($param)
    {
        $param = (object)$param;

        // 应从配置中读取
        if($param->appid)
        {
            // 'wxaaceede0e7695fcf'
            self::$appid = $param->appid;
        }
        if($param->secret)
        {
            // 'eb03a81d333f75a937cb9bd3d4aa5273'
            self::$secret = $param->secret;
        }
        if($param->mch_id)
        {
            // '1464120802'
            self::$mch_id = $param->mch_id;
        }
        if($param->key)
        {
            // 'dada0cc3266ca659e2c0be29798eb357'
            self::$key = $param->key;
        }
        if($param->notify_url)
        {
            // 'http://shop.rockyshi.cn:8081/php/notify_test.php'
            self::$notify_url = $param->notify_url;
        }

        self::$init = true;
    }

    public static function ToXml($param)
    {
        if(!is_array($param) || count($param) == 0)
        {
            return false;
        }

        $xml = "<xml>";
        foreach ($param as $key=>$val)
        {
            $xml .= "<$key><![CDATA[$val]]></$key>";
        }
        $xml.="</xml>";
        return $xml;
    }

    // 将xml转为对象
    public static function FromXml($xml)
    {
        if(!$xml)
        {
            return null;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return (object)json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    //将XML转为json串
    public function XmlToJson($xml)
    {
        if(!$xml)
        {
            return null;
        }
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        // return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA));
    }

    // 发送post
    public static function HttpPost($url, $content, $timeout=60)
    {
        // $content = http_build_query($array, '', '&');
        $context = [
            'http' => [
                'timeout' => $timeout,
                'method'  => 'POST',
                'header'  => 'Content-type:application/x-www-form-urlencoded',
                'header'  => 'Content-type: application/x-www-form-urlencoded \r\n'
                           . 'Content-Length: ' . strlen($content) . '\r\n',
                'content' => $content,
            ]
        ];
        return file_get_contents($url, false, stream_context_create($context));
    }

    // 设置签名，详见签名生成算法
    private static function JoinParam($param)
    {
        $param = (array)$param;
        //签名步骤一：按字典序排序参数
        ksort($param);

        //
        $str = "";
        foreach($param as $k => $v)
        {
            if($k != "sign" && "" != $v){
                $str .= "$k=$v&";
            }
        }
        return trim($str, "&");
    }

    // 设置签名，详见签名生成算法
    public static function GetSign($param)
    {
        $str = self::JoinParam($param);

        //签名步骤二：在string后加入KEY
        $str = "$str&key=" . self::$key;

        //签名步骤三：MD5加密
        $sign = strtoupper(md5($str));
        return $sign;
    }

    // 设置签名，详见签名生成算法
    public static function GetSha1Sign($param)
    {
        $str = self::JoinParam($param);

        //签名步骤三：sha1加密
        $sign = strtolower(sha1($str));
        return $sign;
    }


    // 获取code(注: 因获取code需要在微信客户端中发起，所以这里使用两次请求)
    // scope: snsapi_base|snsapi_userinfo
    public static function GetCode($scope='snsapi_base')
    {
        if($_REQUEST['code'])
        {
            return $_REQUEST['code'];
        }
        $redirect_uri = urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
        $url = "http://wx.jzzwlcm.com/get_code.php?url=$redirect_uri"; // 暂调用wx.jzzwlcm.com下的调用来实现  <<<<<<<<<<<<<<<<<<<<<<<<< 正式时，应改为下面代码
        // $url = WX_URL_AUTHORIZE
        //      . "?appid=" . self::$appid
        //      . "&redirect_uri=$redirect_uri"
        //      . "&response_type=code&scope=$scope&state=1236&connect_redirect=1#wechat_redirect";
        // url: https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxaaceede0e7695fcf&redirect_uri=http%3A%2F%2Fwx.jzzwlcm.com%2Fbak%2Fopenid_test.php%3Fa%3D1%26b%3D2&response_type=code&scope=snsapi_base&state=1236&connect_redirect=1#wechat_redirect
        header("Location: $url");
        exit();
    }

    // 获取openid和access_token
    public static function GetAccessToken($code=null)
    {
        if(!self::$init)
        {
            self::Init();
        }
        if(null == $code)
        {
            $code = self::GetCode('snsapi_base');
        }
        $url = WX_URL_ACCESS_TOKEN
             . "?appid=" . self::$appid
             . "&secret=" . self::$secret
             . "&code=$code"
             . "&grant_type=authorization_code";
        // url: https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxaaceede0e7695fcf&secret=eb03a81d333f75a937cb9bd3d4aa5273&code=061iEdP304Xf2C17XvM305deP30iEdPw&grant_type=authorization_code
        // json: {
        //    "openid" : "oVQGs1Imf8L2EBcn2N0DyJRKQ8pc",
        //    "refresh_token" : "11_Uw3Pbnm0ypDRtnnLx9oe7nGbiVnp6eLGRvcpT35C34WNqiTgmkcBhoJb0qsoJruClEfnZqd19QuvNG3FuD-_OlXUD7dUCeA5UiU3M-RiKOI",
        //    "access_token" : "11_oPUOT96be9WLDe4fYwdoSef5uviC56Z4-jd_y059LYuV8ziaalXSeEE0aIEnU4BDcaQHe8zMQ_XHCHBeSUgVtnSEK3kLgm-FsdnY6_OQkXU",
        //    "expires_in" : 7200,
        //    "scope" : "snsapi_base"
        // }
        $str = file_get_contents($url);
        if(!$str)
        {
            LogErr("get data err:[$url]");
            return null;
        }
        $json = json_decode($str);
        return $json;
    }

    public static function GetOpenid()
    {
        $p = self::GetAccessToken();
        if(!$p)
        {
            LogErr("GetAccessToken err");
            return null;
        }
        return $p->openid;
    }

    // 获取微信用户信息
    public static function GetUserInfo($access_token, $openid)
    {
        if(!$access_token || !$openid)
        {
            $p = self::GetAccessToken();
            if(!$p)
            {
                LogErr("GetAccessToken err");
                return null;
            }
            $access_token = $p->access_token;
            $openid = $p->openid;
        }
        $url = WX_URL_USERINFO
             . "?access_token=$access_token"
             . "&openid=$openid"
             . "&lang=zh_CN";
        // url: https://api.weixin.qq.com/sns/userinfo?lang=zh_CN&access_token=11_HPCnhfpL-eHQh1sOwMfGac523GtVBba86kiGiy8D-AIdfWdYR4swkOTNKVixF5733lakHUN0ut6aXrc1jBxZmyn_An--B3_lXNnVB66wwHw&openid=oVQGs1Imf8L2EBcn2N0DyJRKQ8pc
        // {
        //    "headimgurl" : "http://thirdwx.qlogo.cn/mmopen/vi_32/EgmT0IX2rm4Rx2QxWzSjKQAB3ago6LaLHQRVSQoibB8iaLD61bYic2dOyz3TrgSCOqlu1oN9puGicqgX6XQ2cAS2aA/132",
        //    "city" : "深圳",
        //    "country" : "中国",
        //    "language" : "zh_CN",
        //    "nickname" : "星月",
        //    "privilege" : [],
        //    "province" : "广东",
        //    "openid" : "oVQGs1Imf8L2EBcn2N0DyJRKQ8pc",
        //    "sex" : 1 // 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
        // }
        $json = json_decode(file_get_contents($url));
        return $json;
    }

    // 创建支付数据（h5中使用来调起微信支付）
    // https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1
    public static function GenPayData($param)
    {
        if(!self::$init)
        {
            self::Init();
        }
        $param = (object)$param;
        $openid     = $param->openid;
        $sub_mch_id = $param->sub_mch_id;
        $total_fee  = $param->total_fee;
        $attach     = (object)$param->attach;
        $order_id   = $attach->order_id;

        if(!$order_id
            || !$openid
            || !$sub_mch_id
            || (int)$total_fee <= 0)
        {
            // LogErr("param err:" . \Util::ToJson($param));
            LogErr("param err: order_id:[$order_id], openid:[$openid], sub_mch_id:[$sub_mch_id], total_fee:[$total_fee]");
            return null;
        }

        $out_trade_no = time() . '_' . $order_id;
        $data = [
            'body'             => (string)'订餐',               // 商品描述
            'attach'           => json_encode($attach),         // 附加数据
            'out_trade_no'     => (string)$out_trade_no,        // 商户系统内部订单号，要求32个字符内，只能是数字、大小写字母_-|* 且在同一个商户号下唯一
            'sub_mch_id'       => (string)$sub_mch_id,   // 子商户号
            'notify_url'       => (string)self::$notify_url,    // 通知地址
            'total_fee'        => (int)$total_fee,                          // 总金额(单位:分)
            'openid'           => (string)$openid,

            'appid'            => (string)self::$appid,         // 公众账号ID
            'mch_id'           => (string)self::$mch_id,        // 商户号
            'device_info'      => "WEB",                // 设备号
            'nonce_str'        => md5(rand()),          // 随机字符串
            'fee_type'         => "CNY",                // 货币类型
            // 'spbill_create_ip' => \Util::GetClientIp(), // 终端IP
            'trade_type'       => "JSAPI",              // 交易类型(另注，用"NATIVE"可生成扫码支付的链接)
            'sign_type'        => "MD5",                // 签名类型
        ];
        $data['sign'] = self::GetSign($data);  // 签名

        $xml = self::ToXml($data);
        // echo htmlspecialchars($xml) . "<hr>";
        $ret = self::HttpPost(WX_URL_UNIFIEDORDER, $xml);
        // p: {
        //    "sign" : "2BA7C0C461D36C42567D361FD6BB895C",
        //    "device_info" : "WEB",
        //    "appid" : "wxaaceede0e7695fcf",
        //    "prepay_id" : "wx2017281778457119b2cc132c0658079402",
        //    "return_code" : "SUCCESS",
        //    "sub_mch_id" : "1467121102",
        //    "trade_type" : "JSAPI",
        //    "return_msg" : "OK",
        //    "nonce_str" : "37pPUs4j0tDBWW53",
        //    "mch_id" : "1464120802",
        //    "result_code" : "SUCCESS"
        // }
        $p = self::FromXml($ret);
        if(!$p || 'SUCCESS' != $p->return_code)
        {
            LogErr("data err:" . \Util::ToJson($p) . ", xml:[$xml]");
            return null;
        }
        // https://pay.weixin.qq.com/wiki/doc/api/jsapi_sl.php?chapter=7_7&index=6
        $pay_param = [
            "appId"     => (string)self::$appid,
            "signType"  => "MD5",
            "package"   => "prepay_id={$p->prepay_id}",
            "timeStamp" => (string)time(),
            "nonceStr"  => md5(rand()),
        ];
        $pay_param["paySign"] = self::GetSign($pay_param);
        return $pay_param;
    }
}
