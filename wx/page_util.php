<?php
/*
 * 关于页面类操作工具代码
 * [rockyshi 2014-08-20]
 *
 */
require_once("cfg.php");
require_once("cache.php");
require_once("util.php");
class PageUtil{

static function Header($title)
{
}

static function LoginCheck()
{
    $_ = PageUtil::DecSubmitData();
    if(!$_)
    {
        LogErr("param err");
        return false;
    }

    $token = $_["token"];
    $userid = $_["userid"];

    $logininfo = \Cache\Login::Get($token);
    if(1 == $logininfo->login && $logininfo->userid == $userid)
    {
        return true;
    }
    LogDebug("not login, token:$token, userid:$userid");
    return false;
}

// 取在页面中显示的登录信息
static function HtmlLoginInfo()
{
    //$_ = Util::GetSubmitData();
    $_ = $_COOKIE; //$_REQUEST;        // <<<<<<<<<<<<<<<<<<<<< 应改为Util::GetSubmitData();
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $dao = new DaoUser;
    $entry = new UserEntry;
    $userid = $_['userid'];

    // 是否已注册过
    $entry = $dao->QueryById($userid);
    if(!$entry)
    {
        LogErr("user not exist, ret=[$ret], username=[$username]");
        return errcode::USER_NO_EXIST;
    }
    LogDebug( json_encode($entry) );
    $username = $entry->name;
    $time_msg = "用户ID　：$userid \n"
              . "登录IP　：{$_SERVER['REMOTE_ADDR']} \n"
              . "";
    $user_msg = "<font color=blue title='$time_msg'>$username</font>，迎欢您！"
              . "[<a href='logout.cgi?submit_action_type=logout' target='_self'>退出</a>]";
    LogDebug("username=[$username], user_msg=[$user_msg]");
    return $user_msg;
}

// 解密返回经过加密后的数据
static function DecSubmitData()
{
    static $param = null;
    if(null === $param)
    {
        // LogDebug($_REQUEST);
        // 验证签名
        // (
        //     [data] => login=1&username=&password_md5=d41d8cd98f00b204e9800998ecf8427e
        //     [sign] => 0f59a6a38da98801fdb45a222253a718
        // )
        if(isset($_REQUEST['is_plain']))
        {
            $param['err'] = "is_plain, don't decrypt";
            LogInfo(json_encode($param));
            return $param;
        }
        $token = $_REQUEST['token'];
        if($token)
        {
            $login = \Cache\Login::Get($token);
            LogDebug($login);
            $key = $login->key;
            // 后台中不存在此key，可能是数据过期后自动删除了，直接返回给前端.
            if(!$key)
            {
                $html = json_encode((object)array(
                    'ret'   => errcode::DATA_KEY_NOT_EXIST
                ));
                echo $html;
                LogDebug("key not exist, token:[$token], page exit(0).");
                exit(0);
            }
            $encmode = $_REQUEST['encmode'];
            $data = $_REQUEST['data'];
            $md5 = md5($data . $key);
            if($md5 === $_REQUEST['sign'])  // 引用Cfg [XXX]
            {
                if("encrypt1" == $encmode)
                {
                    $data   = Crypt::decode($key, $data);
                }
                $param = Util::ParseUrlParam($data);
            }
            else
            {
                $param['err'] = "md5 error";
                LogErr(json_encode($param));
            }
            if(empty($param['token']))
            {
                $param['token'] = $token;
            }
            if(empty($param['userid']))
            {
                $param['userid'] = $_REQUEST['userid'];
            }
        }
        else
        {
            $param['err'] = "param err, token lose";
        }
        LogDebug($param);
    }
    return $param;
}

// 使用协商随机密码加密返回到前台的数据
static function EncRespData($data)
{
    return Crypt::encode(\Cache\Login::GetKey(), $data);
}

// 图片名取图片url
static function GetImgUrl($imgname)
{
    if($imgname == "")
    {
        return "";
    }
    // 以后图片多时，考滤分目录
    return Cfg::instance()->dir->img . "/$imgname";
}

// 当前页面名
static function GetCurPage()
{
    return basename($_SERVER['SCRIPT_NAME']);
}

// 当前页面名
static function GetRefererPage()
{
    // $parts = Array
    // (
    //     [scheme] => http
    //     [host] => hostname
    //     [user] => username
    //     [pass] => password
    //     [path] => /path
    //     [query] => arg=value
    //     [fragment] => anchor
    // )
    $parts = parse_url($_SERVER['HTTP_REFERER']);
    return basename($parts["path"]);
}

// 跳转到url
static function PageLocation($url)
{
    //重定向浏览器
    Header("HTTP/1.1 302 See Other");
    header("Location: $url");
    //确保重定向后，后续代码不会被执行
    exit;
}


// 输出错误页
static function HtmlError($args)
{
    if($args.type == "json")
    {
        HtmlJsonError($args);
    }
    else if($args.type == "html")
    {
        HtmlPageError($args);
    }
}
// 输出json格式的错误信息
static function HtmlJsonError($args)
{
    echo json_encode($args);
    exit(0);

}
// 输出html错误页面
static function HtmlPageError($args)
{
    LogErr("jump to template/error.php");
    require("template/error.php");
    // PageUtil::PageLocation("template/error.php?" . urlencode(json_encode($args)));
    exit(0);
}

// 合并地区为串
static function AreaToStr($hotelinfo)
{
    $province = $hotelinfo['addr_province']?:"";
    $city = $hotelinfo['addr_city']?:"";
    $area = $hotelinfo['addr_area']?:"";
    return "$province$city$area";
}

// 取广告文件名的全路径
//   == {DataRootDir}/{filename.crc32}/filename
static function GetImgFullname($filename)
{
    $crc = crc32($filename);
    $dir = sprintf("%s/%d", Cfg::instance()->img->filepath, $crc%1024);
    // LogDebug("[$filename] [$crc] [$dir]");
    if(!is_dir($dir))
    {
        if(!mkdir($dir, 0777, true) || !chmod($dir, 0777))
        {
            LogErr("mkdir or chmod err:[$dir]");
            return "";
        }
    }
    return "$dir/$filename";
}

// 使用签名加密形式发送
// $opt -- 可选设置项
function HttpPostJsonEncData($url, $data, $opt=[])
{
    $data = (object)$data;
    $opt = (object)$opt;
    $timeout = (NULL !== $opt->timeout)?$opt->timeout:10;
    $encmode = (NULL !== $opt->encmode)?$opt->encmode:"";
    // 签名发送
    $token = \Cache\Login::Token();
    $datakey = \Cache\Login::GetKey();
    $paramstr = $data->param;
    $data->token = $token;
    $data->datakey = $datakey;
    $data->encmode = $encmode;
    $data->sign = md5($paramstr . $datakey);
    return PageUtil::HttpPostJsonData($url, $data, $timeout);
}

// 发送微信支付完成的通知
function NotifyWxPay($order_id, $price, $token)
{
    $url = Cfg::instance()->orderingsrv->webserver_url;
    $ret_json = PageUtil::HttpPostJsonEncData(
        $url,
        [ // $data
            'name' => "cmd_publish",
            'param' => json_encode([
                'opr'   => "once",
                'param' => [
                    'topic' => "wxpay_notify@" . $token,
                    'data'=> [
                        'order_id' => $order_id,
                        'price'    => $price
                    ]
                ],
            ])
        ]
    );
    return $ret_json;
}

// 发送post
static function HttpPostJsonData($url, $data, $timeout=10)
{
    $content = "data=" . urlencode(json_encode($data));
    LogDebug($content);
    $context = [
        'http' => [
            'timeout' => $timeout,
            'method'  => 'POST',
            'header'  => 'Content-type:application/x-www-form-urlencoded',
            // 'content' => http_build_query($data, '', '&'),
            'content' => $content,
        ]
    ];
    //print_r($context);
    return file_get_contents($url, false, stream_context_create($context));
}

static function IsWeixin()
{
    $user_agent = $_SERVER["HTTP_USER_AGENT"];
    if(strpos($user_agent, "MicroMessenger/") !== false)
    {
        return true;
    }
    return false;
}

static function GetSeatQrcodeImg($shop_id, $seat_id)
{
    $seat_qrcode_img = Cfg::instance()->GetSeatQrcodePath($shop_id, $seat_id);
    if(!file_exists($seat_qrcode_img))
    {
        $seat_qrcode_contect = Cfg::instance()->GetSeatQrcodeContect($shop_id, $seat_id);
        QRcode::png($seat_qrcode_contect, $seat_qrcode_img,
            'L',        // 容错级别
            20,         // 生成图片大小
            1);         // 边框
    }
    return $seat_qrcode_img;
}

// 查检订单是否可修改
static function OrderCanModify($order_id)
{
    $info = \Cache\Order::Get($order_id);
    if(!$info || !$info->order_id)
    {
        return 0;
    }
    // LogDebug($info->order_status);
    switch($info->order_status){
        case OrderStatus::PENDING:
            return 0; // 待处理的订单可以修改
            break;
        case OrderStatus::CONFIRMED:
            return errcode::ORDER_ST_CONFIRMED;
            break;
        case OrderStatus::PAID:
            return errcode::ORDER_ST_PAID;
            break;
        case OrderStatus::FINISH:
            return errcode::ORDER_ST_FINISH;
            break;
        case OrderStatus::CANCEL:
            return errcode::ORDER_ST_CANCEL;
            break;
        case OrderStatus::TIMEOUT:
            return errcode::ORDER_ST_TIMEOUT;
            break;
        case OrderStatus::PRINTED:
            return errcode::ORDER_ST_PRINTED;
            break;
        default:
            return errcode::ORDER_STATUS_ERR;
            break;
    }
    return 0;
}

// 增加餐品售出数
static function UpdateFoodDauSoldNum($order_id)
{
    $orderinfo = \Cache\Order::Get($order_id);
    if(null == $orderinfo || null == $orderinfo->food_list)
    {
        LogDebug("order not exist, order_id:[$order_id]");
        return;
    }

    // 订单是否处于已确认状态
    if(!OrderStatus::HadConfirmed($orderinfo->order_status))
    {
        LogDebug("order status err:{$orderinfo->order_status}");
        return;
    }

    $mgo_stat = new \DaoMongodb\StatFood;
    $day = date("Ymd");
    LogDebug($orderinfo->food_list);
    foreach($orderinfo->food_list as $i => $food)
    {
        $mgo_stat->SellNumAdd($orderinfo->shop_id, $food->food_id, $day, $food->food_num);
        LogDebug("[{$food->food_id}], [$day], [{$food->food_num}]");
    }
}

// 查询扫码枪支付结果
static function OrderQuery($order_id, &$data = null)
{
    $redis = new \DaoRedis\Pay();
    $order = $redis->Get($order_id);
    if(!$order || !$order->out_trade_no)
    {
        LogErr("OrderInfo or status err");
        return errcode::ORDER_NOT_EXIST;
    }
    if(1 == $order->is_pay)
    {
        $data = $order;
        return 0;
    }
    // 订单信息
    $order_info = \Cache\Order::Get($order_id);
    if(!$order_info)
    {
        LogErr("order err, order_id:[$order_id]");
        return errcode::ORDER_NOT_EXIST;
    }
    // 店铺信息
    $shop_info = \Cache\Shop::Get($order_info->shop_id);
    if(!$order_info)
    {
        LogErr("order err, order_id:[$order_id]");
        return errcode::SHOP_NOT_WEIXIN;
    }

    // 兼容处理
    $sub_mch_id = "";
    if($shop_info->weixin_pay_set && $shop_info->weixin_pay_set->sub_mch_id)
    {
        $sub_mch_id = $shop_info->weixin_pay_set->sub_mch_id;
    }
    if("" == $sub_mch_id && $shop_info->weixin && $shop_info->weixin->sub_mch_id)
    {
        $sub_mch_id = $shop_info->weixin->sub_mch_id;
    }
    if("" == $sub_mch_id)
    {
        LogErr("order err, order_id:[$order_id]");
        return errcode::WXPLAY_NO_SUPPORT;
    }

    $unifiedorder = new \Wx\Unifiedorder();
    // $unifiedorder->SetParam('transaction_id', $order->transaction_id);   // 微信订单号(测试:'4200000021201712186680986033')
    $unifiedorder->SetParam('out_trade_no', $order->out_trade_no);          // 商户订单号
    $unifiedorder->SetParam('sub_mch_id', (string)$sub_mch_id);             // 子商户号(测试:'1467121102')
    $xml = $unifiedorder->SubmitOrder();
    $ret = \Wx\Util::FromXml($xml);
    if($ret['trade_state'] != 'SUCCESS')
    {
        if('USERPAYING' == $ret['trade_state'])
        {
            LogInfo("need passwd, order_id:[$order_id]");
            $data = (object)[
                'msg' => $ret['trade_state_desc']
            ];
            return errcode::PAY_NEED_PASSWD;
        }
        LogErr("play err:" . json_encode($ret));
        return errcode::PAY_ERR;
    }
    
    $info = new \DaoRedis\PayEntry();
    $info->order_id       = $order_id;
    $info->transaction_id = $ret['transaction_id'];
    $info->out_trade_no   = $ret['out_trade_no'];
    $info->is_pay         = 1;
    $info->pay_price      = $ret['total_fee']/100;
    $save = $redis->Save($info);
    if(0 != $save)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $mgo = new \DaoMongodb\Order;
    $entry = new \DaoMongodb\OrderEntry;

    $entry->order_id       = $order_id;
    $entry->order_status   = 2;
    $entry->pay_way        = 2;
    $entry->pay_status     = 2;
    $entry->paid_price     = $ret['total_fee']/100;
    $entry->pay_time       = time();

    $order_ret = $mgo->Save($entry);
    if(0 != $order_ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $data = $info;
    // 更新缓存
    \Cache\Order::Clear($order_id);
    // 更新餐品日销售量
    // 增加餐品售出数
    self::UpdateFoodDauSoldNum($order_id);
    return 0;
}
//pad订单小票推送通知
function NotifyOrderPrint($shop_id, $order_id, $what=[])
{
        $url = Cfg::instance()->orderingsrv->webserver_url;
        $ret_json = PageUtil::HttpPostJsonEncData(
            $url,
            [ // $data
                'name' => "cmd_publish",
                'param' => json_encode([
                    'opr'   => "general",
                    'param' => [
                        'topic' => "order_print@" . $shop_id,
                        'data'=> [
                            'order_id' => $order_id,
                            'what'     => $what,
                        ]
                    ],
                ])
            ]
        );
        return $ret_json;
}

// 发送订单变动通知
function NotifyOrderChange($shop_id, $order_id, $order_status, $lastmodtime)
{
    $url = Cfg::instance()->orderingsrv->webserver_url;
    $ret_json = PageUtil::HttpPostJsonEncData(
        $url,
        [ // $data
            'name' => "cmd_publish",
            'param' => json_encode([
                'opr'   => "general",
                'param' => [
                    'topic' => "order_change@" . $shop_id,
                    'data'=> [
                        'lastmodtime' => $lastmodtime,
                        'order_id' => $order_id,
                        'order_status' => $order_status,
                    ]
                ],
            ])
        ]
    );
    return $ret_json;
}

}// end of class PageUtil{...
?>
