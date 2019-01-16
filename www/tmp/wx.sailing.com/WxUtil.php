<?php
require_once("/www/public.sailing.com/php/weixin/WxUtil.php");
/*
 * [Rocky 2017-05-26 02:13:18]
 * 微信操作
 */
// declare(encoding='UTF-8');
// namespace Wx;
// require_once "WxCfg.php";

// // 注：Cfg单独放到WxCfg.php中
// // class Cfg
// // {
// // }

// class Util
// {
//     // 发送post
//     public static function HttpPost($url, $content, $timeout=60)
//     {
//         // $content = http_build_query($array, '', '&');
//         $context = [
//             'http' => [
//                 'timeout' => $timeout,
//                 'method'  => 'POST',
//                 'header'  => 'Content-type:application/x-www-form-urlencoded',
//                 'header'  => 'Content-type: application/x-www-form-urlencoded \r\n'
//                            . 'Content-Length: ' . strlen($content) . '\r\n',
//                 'content' => $content,
//             ]
//         ];
//         return file_get_contents($url, false, stream_context_create($context));
//     }

//     // 设置签名，详见签名生成算法
//     private static function JoinParam($param)
//     {
//         //签名步骤一：按字典序排序参数
//         ksort($param);

//         //
//         $str = "";
//         foreach($param as $k => $v)
//         {
//             if($k != "sign" && "" != $v){
//                 $str .= "$k=$v&";
//             }
//         }
//         return trim($str, "&");
//     }

//     // 设置签名，详见签名生成算法
//     public static function GetSign($param)
//     {
//         $str = self::JoinParam($param);

//         //签名步骤二：在string后加入KEY
//         $str = "$str&key=" . Cfg::KEY;

//         //签名步骤三：MD5加密
//         $sign = strtoupper(md5($str));

//         return $sign;
//     }

//     // 设置签名，详见签名生成算法
//     public static function GetSha1Sign($param)
//     {
//         $str = self::JoinParam($param);

//         //签名步骤三：sha1加密
//         $sign = strtolower(sha1($str));
//         return $sign;
//     }


//      // 获取code(注: 因获取code需要在微信客户端中发起，所以这里使用两次请求)
//     public static function GetCode($scope = 'snsapi_base')
//     {
//         $redirect_uri = urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
//         $url = Cfg::WX_URL_AUTHORIZE
//              . "?appid=" . Cfg::APPID
//              . "&redirect_uri=$redirect_uri"
//              . "&response_type=code&scope=$scope&state=1236&connect_redirect=1#wechat_redirect";
//         $code = $_REQUEST['code'];
//         if($code)
//         {
//             return $code;
//         }
//         //LogDebug($url);
//         header("Location: $url");
//         exit();
//     }
//     // 获取openid和access_token
//     public static function GetOpenid()
//     {
//         $scope = 'snsapi_userinfo';
//         $code = self::GetCode($scope);
//         //LogDebug($code);
//         $url = Cfg::WX_URL_ACCESS_TOKEN
//              . "?appid=" . Cfg::APPID
//              . "&secret=" . Cfg::SECRET
//              . "&code=$code"
//              . "&grant_type=authorization_code";
//         $json = json_decode(file_get_contents($url));
//         return $json;
//     }

//     // 获取openid和access_token
//     public static function GetAppOpenid($appid, $secret, $code)
//     {
//         $url = Cfg::WX_URL_ACCESS_TOKEN
//             . "?appid=$appid"
//             . "&secret=$secret"
//             . "&code=$code"
//             . "&grant_type=authorization_code";
//         $json = json_decode(file_get_contents($url));
//         return $json;
//     }

//     // 获取access_token
//     public static function GetToken()
//     {
//         $url = Cfg::WX_URL_TOKEN
//              . "?grant_type=client_credential"
//              . "&appid=" . Cfg::APPID
//              . "&secret=" . Cfg::SECRET;
//         $json = json_decode(file_get_contents($url));
//         return $json;
//     }

//     // 获取access_token
//     public static function GetTokenByShop($appid, $secret)
//     {
//         $url = Cfg::WX_URL_TOKEN
//              . "?grant_type=client_credential"
//              . "&appid=" . $appid
//              . "&secret=" . $secret;
//         $json = json_decode(file_get_contents($url));
//         return $json;
//     }
//     // 获取微信用户信息
//     public static function GetUserInfo($access_token,$openid)
//     {
//         $url = Cfg::WX_URL_USERINFO
//              . "?access_token=$access_token"
//              . "&openid=$openid"
//              . "&lang=zh_CN";
//         $json = json_decode(file_get_contents($url));
//         return $json;
//     }

//     // 获取jsapi_ticket
//     public static function GetTicket()
//     {
//         $info = self::GetToken();
//         $access_token = $info->access_token;
//         $url = Cfg::WX_URL_TICKET
//              . "?access_token=$access_token"
//              . "&type=jsapi";
//         $json = json_decode(file_get_contents($url));
//         return $json;
//     }

//     public static function ToXml($param)
//     {
//         if(!is_array($param) || count($param) == 0)
//         {
//             return false;
//         }

//         $xml = "<xml>";
//         foreach ($param as $key=>$val)
//         {
//             $xml .= "<$key><![CDATA[$val]]></$key>";
//         }
//         $xml.="</xml>";
//         return $xml;
//     }

//     // 将xml转为array
//     public static function FromXml($xml)
//     {
//         if(!$xml)
//         {
//             return null;
//         }
//         //将XML转为array
//         //禁止引用外部xml实体
//         libxml_disable_entity_loader(true);
//         return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
//     }

//     //将XML转为array
//     public function XmlToJson($xml)
//     {
//         if(!$xml)
//         {
//             return null;
//         }
//         //禁止引用外部xml实体
//         libxml_disable_entity_loader(true);
//         // return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
//         return json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA));
//     }
// }


?>
