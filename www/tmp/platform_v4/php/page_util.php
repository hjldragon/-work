<?php
/*
 * 关于页面类操作工具代码
 * [rockyshi 2014-08-20]
 *
 */
require_once("cfg.php");
require_once("cache.php");
require_once("util.php");
include_once("3rd/phpqrcode.php");
require_once ("class.phpmailer.php");
require_once ("class.smtp.php");
//require_once ("alidayu/TopSdk.php");
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

    $token  = $_["token"];
    $userid = $_["userid"];
    LogDebug($userid);
    $logininfo = \Cache\Login::Get($token);
    LogDebug($logininfo);
    if(1 == $logininfo->login && $logininfo->userid == $userid)
    {
        return true;

    }
    LogDebug("not login, token:$token, userid:$userid");
    return false;
}

// 当前登录员工权限
static function CurLoginEmployeePermission()
{
    $logininfo = \Cache\Login::Get(null);

    if(!$logininfo)
    {
        LogErr("not login");
        return null;
    }

    // 取用户信息
    $employeeinfo = \Cache\Employee::Get($logininfo->userid);
    if(!$employeeinfo || !$employeeinfo->permission)
    {
        LogErr("no employee, id:[{$logininfo->userid}]");
        return null;
    }
    return $employeeinfo->permission;
}
// 取当前登录员工是否是超级管理员不需要权限
static function IsLoginEmployeePermission($shop_id)
{
        $userid = \Cache\Login::GetUserid();
        if(!$userid)
        {
            LogErr("not login");
            return null;
        }

        // 取用户信息
        $employeeinfo = \Cache\Employee::GetInfo($userid,$shop_id);

        if(!$employeeinfo)
        {
            LogErr("permisson is not enough, id:[{$userid}]");
            return null;
        }
        return $employeeinfo->is_admin;
}
// 取在页面中显示的登录信息
static function HtmlLoginInfo()
{
    $_ = Util::GetSubmitData();
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
        if(isset($_REQUEST['is_plain']) || '1' == $_REQUEST['get_login_qrcode'])
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
            LogDebug($_REQUEST);

            LogDebug($_COOKIE);
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
                if("aes" == $encmode)
                {
                    $data   = Aes::Decrypt($key, $data);
                }
                $param = Util::ParseUrlParam($data);
            }
            else
            {
                $param['key'] = $key;
                $param['data'] = $data;
                $param['err'] = "md5 error, md5:[$md5]";
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
static function HttpPostJsonEncData($url, $data, $opt=[])
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
        $seat_qrcode_contect = Cfg::instance()->GetSeatQrcodeContect($seat_id);
        QRcode::png($seat_qrcode_contect, $seat_qrcode_img,
            'L',        // 容错级别
            20,         // 生成图片大小
            1);         // 边框
    }
    return $seat_qrcode_img;
}

static function GetFoodQrcodeImg($shop_id, $food_id)
{
    $food_qrcode_img = Cfg::instance()->GetFoodQrcodePath($shop_id, $food_id);
    if(!file_exists($food_qrcode_img))
    {
        $food_qrcode_contect = Cfg::instance()->GetFoodQrcodeContect($food_id);
        QRcode::png($food_qrcode_contect, $food_qrcode_img,
            'L',        // 容错级别
            20,         // 生成图片大小
            1);         // 边框
    }
    return $food_qrcode_img;
}

static function GetLoginQrcodeImg($token)
{
    $login_qrcode_contect = Cfg::instance()->GetLoginQrcodeContect($token);
    QRcode::png($login_qrcode_contect, $login_qrcode_img,
        'L',        // 容错级别
        20,         // 生成图片大小
        1);         // 边框

    return $login_qrcode_img;
}

static function GetUrlQrcodeImg($url)
{
    $url_qrcode_contect = Cfg::instance()->GetUrlQrcodeContect($url);
    QRcode::png($url_qrcode_contect, $url_qrcode_img,
        'L',        // 容错级别
        20,         // 生成图片大小
        1);         // 边框

    return $url_qrcode_img;
}

static function GetBindingQrcodeImg($userid, $token, $type)
{
    $binding_qrcode_contect = Cfg::instance()->GetBindingQrcodeContect($userid, $token, $type);
    QRcode::png($binding_qrcode_contect, $binding_qrcode_img,
        'L',        // 容错级别
        20,         // 生成图片大小
        1);         // 边框

    return $binding_qrcode_img;
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
        case OrderStatus::POSTPONED:
            // return errcode::ORDER_ST_CONFIRMED;
            return 0;   // 在管理端，确定的订单也可修改，如客人加菜等
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
            // return errcode::ORDER_ST_PRINTED;    // Rocky 2017-07-20 00:17:27
            return 0;   // 在管理端，确定的订单也可修改，如客人加菜等
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

    // 订单是否处于已下单确认状态
//    if(!OrderSureStatus::HadConfirmed($orderinfo->order_status))
//    {
//        LogDebug("order status err:[{$orderinfo->order_status}]");
//        return;
//    }

    $mgo_stat = new \DaoMongodb\StatFood;
    $day = date("Ymd");
    foreach($orderinfo->food_list as $i => $food)
    {
        $mgo_stat->SellNumAdd($orderinfo->shop_id, $food->food_id, $day, $food->food_num);
        LogDebug("$food->food_id, $day, $food->food_num");
    }
}

// 检查餐品库存够不够（有不够的餐品时，返回其餐品信息，满足要求时返回null）
static function CheckFoodStockNum($shop_id, $need_food_list)
{
    // 取出id列表
    $food_id_list = array_map(function($v) {
        return $v->food_id;
    }, $need_food_list);
    // LogDebug($food_id_list);
    // 读出当前餐品每天备货量
    $mgo_food = new \DaoMongodb\MenuInfo;
    $list = $mgo_food->GetOrderFoodList(
        $shop_id,
        [
            'food_id_list' => $food_id_list,
        ]
    );
    // LogDebug($list);
    // food_id --> 备货量
    $id2stock_num_day = [];
    foreach($list as $i => $v)
    {
        $id2stock_num_day[$v->food_id] = (int)$v->stock_num_day;
    }
    // LogDebug($id2stock_num_day);
    // 读出当前已售出量
    $mgo_stat = new \DaoMongodb\StatFood;
    $today = date("Ymd");
    $list_two = $mgo_stat->GetStatList([
        'food_id_list' => $food_id_list,
        'shop_id'      => $shop_id,
        'begin_day'    => $today,
        'end_day'      => $today,
    ]);
    // LogDebug($list);
    // food_id --> 已售出量
    $id2food_sold_num = [];
    foreach($list_two as $i => $v)
    {
        $id2food_sold_num[$v->food_id] = $v->sold_num;
    }
    // LogDebug($id2food_sold_num);

    // 查看餐品存量
    foreach($need_food_list as $i => $food)
    {
         //每日限售量
        $stock_num_day = (int)$id2stock_num_day[$food->food_id];
        if($stock_num_day <= 0)
        {
            continue;
        }
        //日出售量
        $food_sold_num = (int)$id2food_sold_num[$food->food_id];
        LogDebug("food_id:[{$food->food_id}], food_num:[{$food->food_num}], stock_num_day:[{$stock_num_day}], food_sold_num:[{$food_sold_num}]");
        // 库存够吗？
        if($food->food_num > $stock_num_day - $food_sold_num)
        {
            //如果库存不足计算出菜品中限量的剩余的库存
            foreach($list as  &$v)
            {
                $v->stock_num_day = $stock_num_day - $food_sold_num;
            }
            LogDebug("not enough");
            return $list;
        }
    }
    return null;
}

//邮箱发送配置
static function GetMail($email,$url,$zi)
{
    try {
        $mail = new PHPMailer(); //建立邮件发送类
        /*服务器相关信息*/
        $mail->IsSMTP();
        $mail->SMTPAuth  = true;//开启认证
        $mail->CharSet   = "UTF-8";//设置信息的编码类型
        $mail->Host      = "smtp.163.com"; //邮箱服务器
        $mail->Username  = "18280156916@163.com"; //服务器邮箱账号
        $mail->Password  = "sailing123"; // 163邮箱设置密钥
        $mail->SMTPDebug = 1;
        $mail->Port      = 25;//邮箱服务器端口号
        /*内容信息*/
        $mail->IsHTML(true);
        $mail->AddReplyTo("18280156916@163.com", "mckee");//回复地址
        $mail->From     = "18280156916@163.com"; //发件人的完整邮箱
        $mail->FromName = "赛领新吃货"; //发送邮箱
        $mail->Subject  = "新吃货邮箱绑定";//标题
        $mail->MsgHTML("这是您登录帐户时所需的$zi.邮箱连接!<a href='$url'>请点击$zi</a>");//邮件消息体
        $mail->AddAddress($email);
        $mail->WordWrap = 80; // 设置每行字符串的长度
        $mail->Send();
        return 0;
    } catch (phpmailerException $e) {
        echo "邮件发送失败：" . $e->errorMessage();
        echo $mail->ErrorInfo;
        return -1;
    }
}
//阿里大于手机验证码发送配置
static function SendCheckCode($code, $phone)
{
$c            = new TopClient;
$c->appkey    = "24493589";//这里是我的应用key
$c->secretKey = "71f080699a57dab32d3d2a037b13c2ba";//密匙
$req          = new AlibabaAliqinFcSmsNumSendRequest;
/*
     公共回传参数，在“消息返回”中会透传回该参数；
     举例：用户可以传入自己下级的会员ID，在消息返回时，
*/
$req->setExtend("123456");
/*
    短信类型，传入值请填写normal
*/
$req->setSmsType("normal");
/*
   短信签名，传入的短信签名必须是在阿里大于“管理中心-短信签名管理”中的可用签名。
*/
$req->setSmsFreeSignName("赛领新吃货");   //这里根据自己的做调整， 不调整会报错
/*
   短信模板变量，传参规则{"key":"value"}，
*/
$req->setSmsParam("{\"code\":\"$code\",\"product\":\"赛领科技\"}"); //一样， 可以调整。 这里不调整不会报错
/*
    短信接收号码。支持单个或多个手机号码，传入号码为11位手机号码，
*/
$req->setRecNum("$phone");
$req->setSmsTemplateCode("SMS_105000102");
$resp = $c->execute($req);
if ($resp->result->success) {
    return 0;
} else {
    return -1;
}
}
//发送手机验证码
static function GetCoke($token,$phone,$page_code){
    $db         = new \DaoRedis\Login;
    $redis      = $db->Get($token);
    $radis_code = $redis->page_code;
//验证验证码
    if ($radis_code != $page_code) {
        LogErr("coke err");
        return errcode::COKE_ERR;
    }

    if (!preg_match('/^\d{11}$/', $phone)) {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }
    $mgo  = new \DaoMongodb\User;
    $info = $mgo->QueryByPhone($phone);
    if ($info->phone) {
        LogErr("phone is exist");
        return errcode::PHONE_IS_EXIST;
    }
    $code = 654321;//<<<<<<<<<<<<<<<<<<<<<<<<<<<<调试写死数据
//$code = mt_rand(100000, 999999);
//    $clapi  = new ChuanglanSmsApi();
//    $msg    = '【赛领新吃货】尊敬的用户，您本次的验证码为' . $code . '有效期5分钟。打死不要将内容告诉其他人！';
//    $result = $clapi->sendSMS($phone, $msg);
//    LogDebug($result);
//    if (!is_null(json_decode($result)))
//    {
//        $output = json_decode($result, true);
//        if (isset($output['code']) && $output['code'] == '0')
//        {
//            LogDebug('短信发送成功！');
//        } else {
//            return $output['errorMsg'] . errcode::PHONE_SEND_FAIL;
//        }
//    }
    LogDebug($code);
    $redis            = new \DaoRedis\Login();
    $data             = new \DaoRedis\LoginEntry();
    $data->phone      = $phone;
    $data->token      = $token;
    $data->phone_code = $code;
    $data->code_time  = time() + 5 * 60 * 1000;
    LogDebug($data);
    $redis->Save($data);

    return 0;
}
//验证手机验证码
static function VerifyPhoneCode($token,$phone,$phone_code){
    if (!preg_match('/^\d{11}$/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }

    $redis = new \DaoRedis\Login();
    $data  = $redis->Get($token);//获取手机号上面的验证码
    if ($phone_code != $data->phone_code)
    {
        LogErr("phone_code is err");
        return errcode::PHONE_COKE_ERR;
    }
    if (time() > $data->code_time)
    {
        LogErr("phone_code is lapse");
        return errcode::PHONE_CODE_LAPSE;
    }
    if ($phone != $data->phone)
    {
        LogErr("phone is not true");
        return errcode::PHONE_TWO_NOT;
    }
    return 0;
}
//验证邮箱
static function GetEmail($email)
{
        if (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i', $email))
        {
            LogErr("email err");
            return null;
        }else{
            return $email;
        }
}
//验证手机号码
static function GetPhone($phone)
{
        if (!preg_match('/^\d{11}$/', $phone))
        {
            LogErr("phone err");
            return null;
        }else{
            return $phone;
        }
}

static function GetApkName($apkname)
{
        $crc = crc32($apkname);
        $dir = sprintf("%s/apk/", Cfg::instance()->apk->fileapk, $crc%1024);
        // LogDebug("[$filename] [$crc] [$dir]");
        if(!is_dir($dir))
        {
            if(!mkdir($dir, 0777, true) || !chmod($dir, 0777))
            {
                LogErr("mkdir or chmod err:[$dir]");
                return "";
            }
        }
        return "$dir/$apkname";
}

static function GetAgentRebates($agent_id)
{
    $agent_mgo     = new DaoMongodb\Agent;
    $agent_cfg_mgo = new \Pub\Mongodb\AgentCfg;

    $agent_info    = $agent_mgo->QueryById($agent_id);
    $info          = $agent_cfg_mgo->GetInfoByLevel($agent_info->agent_type, $agent_info->agent_level);
    $rebates       = [];
    $rebates['hardware'] = $info->hardware_rebates;
    $rebates['software'] = $info->software_rebates;
    $rebates['supplies'] = $info->supplies_rebates;
    return $rebates;
}
//自动售货机的二维码
static function GetVendorQrcodeImg($vendor_id)
{
        $vendor_qrcode_img     = Cfg::instance()->GetVendorQrcodePath($vendor_id);
        $vendor_qrcode_contect = Cfg::instance()->GetVendorQrcodeContect($vendor_id);
        QRcode::png($vendor_qrcode_contect, $vendor_qrcode_img,
            'L',        // 容错级别
            20,         // 生成图片大小
            1);         // 边框

        return $vendor_qrcode_img;
}
}// end of class PageUtil{...
?>
