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
require_once("mgo_stat_shop_byday.php");
require_once("/www/public.sailing.com/php/mgo_stat_platform_byday.php");
use \Pub\Mongodb as Mgo;
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
    //$userid = $_["userid"];
    $userid = \Cache\Login::GetUserid();
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

static function GetSeatQrcodeImg($shop_id, $seat_id, $size=null)
{
    if(null == $size)
    {
        $size = 20;
    }
    $fix = "_tmp";
    $seat_img = Cfg::instance()->GetSeatQrcodePath($shop_id, $seat_id, $fix);
    // if(!file_exists($seat_qrcode_img))
    // {
        $seat_qrcode_contect = Cfg::instance()->GetSeatQrcodeContect($seat_id);
        QRcode::png($seat_qrcode_contect, $seat_img,
            'L',        // 容错级别
            $size,      // 生成图片大小
            1);         // 边框
    //}
    $seat_qrcode_img = Cfg::instance()->GetSeatQrcodePath($shop_id, $seat_id);
    PageUtil::ImgCopy($seat_img, $seat_qrcode_img, 295, 295);
    unlink($seat_img);
    return $seat_qrcode_img;
}

static function GetFoodQrcodeImg($shop_id, $food_id)
{
    $food_qrcode_img = Cfg::instance()->GetFoodQrcodePath($shop_id, $food_id);

    // if(!file_exists($food_qrcode_img))
    // {
        $food_qrcode_contect = Cfg::instance()->GetFoodQrcodeContect($food_id);
        QRcode::png($food_qrcode_contect, $food_qrcode_img,
            'L',        // 容错级别
            20,         // 生成图片大小
            1);         // 边框
    //}
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
        PageUtil::SendFoodStock($orderinfo->shop_id, $food->food_id);
    }
}

// 发送餐品库存改变通知
static function SendFoodStock($shop_id, $food_id)
{
    $food_info = \Cache\Food::Get($food_id);
    $mgo_stat  = new \DaoMongodb\StatFood;
    $today     = date("Ymd");
    $food_sale = $mgo_stat->GetFoodStatByDay($food_id, $today);

    if($food_info->stock_num_day <= 0)
    {
        $food_num = 99999;
    }else{
        $food_num = $food_info->stock_num_day-$food_sale->sold_num;
    }
    $ret_json     = PageUtil::NotifyFoodStock($shop_id, $food_id, $food_num);
    $ret_json_obj = json_decode($ret_json);
    LogDebug($ret_json_obj);
    if(0 != $ret_json_obj->ret)
    {
        LogErr("Food Stock Send err");
    }
}
// 发送餐品变动通知
function NotifyFoodStock($shop_id, $food_id, $stock_num)
{
        $url = Cfg::instance()->orderingsrv->webserver_url;
        $ret_json = PageUtil::HttpPostJsonEncData(
            $url,
            [ // $data
                'name' => "cmd_publish",
                'param' => json_encode([
                    'opr'   => "general",
                    'param' => [
                        'topic' => "food_stock@" . $shop_id,
                        'data'=> [
                            'food_id' => $food_id,      //
                            'stock_num' => $stock_num,  // 餐品当前库存
                        ]
                    ],
                ])
            ]
        );
        return $ret_json;
}
//加菜的库存改变
static function UpdateAddFoodDauSoldNum($order_id)
{
        $orderinfo = \Cache\Order::Get($order_id);
        if(null == $orderinfo || null == $orderinfo->add_food_list)
        {
            LogDebug("order not exist, order_id:[$order_id]");
            return;
        }
        $mgo_stat = new \DaoMongodb\StatFood;
        $day = date("Ymd");
        foreach($orderinfo->add_food_list as $i => $food)
        {
            $mgo_stat->SellNumAdd($orderinfo->shop_id, $food->food_id, $day, $food->food_num);
            LogDebug("$food->food_id, $day, $food->food_num");
        }
}

// 检查餐品库存够不够（有不够的餐品时，返回其餐品信息，满足要求时返回null）
static function CheckFoodStockNum($shop_id, $need_food_list)
{
    // 取出id列表
//    $food_id_list = array_map(function($v) {
//        return $v->food_id;
//    }, $need_food_list);
    $food_id_list = [];
    foreach ($need_food_list as $id)
    {
        $food_id_list[] = $id->food_id;
    }
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
                  //当餐品库存不足的时候给PAD端发送消息
                    $ret_json =  PageUtil::NotifyFoodChange($shop_id, $food->food_id);
                    LogDebug("[$ret_json]");
                    $ret_json_obj = json_decode($ret_json);
                    if(0 != $ret_json_obj->ret)
                    {
                        LogErr("shop change send err");
                        return errcode::SYS_BUSY;
                    }
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
                if($v->food_id == $food->food_id)
                {
                    $foodinfo[] = $v;
                }
            }
            LogDebug("not enough");
            return $foodinfo;
        }
    }
    return null;
}
//检查餐品库存够不够,不够返回所有的不足的餐品列表
static function CheckFoodListStockNum($shop_id, $need_food_list)
{
        $food_id_list = [];
        foreach ($need_food_list as $id)
        {
            $food_id_list[] = $id->food_id;
        }
        // 读出当前餐品每天备货量

        $mgo_food = new \DaoMongodb\MenuInfo;
        $list = $mgo_food->GetOrderFoodList(
            $shop_id,
            [
                'food_id_list' => $food_id_list,
            ]
        );
        $id2stock_num_day = [];
        foreach($list as $i => $v)
        {
            if($v->stock_num_day == 0)
            {
                $v->stock_num_day = 99999;
            }
            $id2stock_num_day[$v->food_id] = (int)$v->stock_num_day;
        }
        // 读出当前已售出量
        $mgo_stat = new \DaoMongodb\StatFood;
        $today = date("Ymd");
        $list_two = $mgo_stat->GetStatList([
            'food_id_list' => $food_id_list,
            'shop_id'      => $shop_id,
            'begin_day'    => $today,
            'end_day'      => $today,
        ]);
        // food_id --> 已售出量
        $id2food_sold_num = [];
        foreach($list_two as $i => $v)
        {
            $id2food_sold_num[$v->food_id] = $v->sold_num;
        }
        // 查看餐品存量
        $all_food = [];
        $food_one = [];
        foreach($need_food_list as $food)
        {

            //每日限售量
            $stock_num_day = (int)$id2stock_num_day[$food->food_id];
            //当日出售量
            $food_sold_num = (int)$id2food_sold_num[$food->food_id];
            // 库存够吗？
            if($food->food_num > $stock_num_day - $food_sold_num)
            {

                $food_one['food_name'] = $food->food_name;
                $food_one['food_id']   = $food->food_id;
                $food_one['stock_num_day']   = $stock_num_day - $food_sold_num;
                array_push($all_food,$food_one);
            }
        }
        return $all_food;
}
//恢复减菜的库存数量
static function RecoverFoodListStockNum($shop_id, $need_food_list)
    {
        $food_id_list = [];
        foreach ($need_food_list as $id)
        {
            $food_id_list[] = $id->food_id;
        }
        // 读出当前餐品每天备货量

        $mgo_food = new \DaoMongodb\MenuInfo;
        $list = $mgo_food->GetOrderFoodList(
            $shop_id,
            [
                'food_id_list' => $food_id_list,
            ]
        );
        $id2stock_num_day = [];
        foreach($list as $i => $v)
        {
            if($v->stock_num_day == 0)
            {
                $v->stock_num_day = 99999;
            }
            $id2stock_num_day[$v->food_id] = (int)$v->stock_num_day;
        }
        // 读出当前已售出量
        $mgo_stat = new \DaoMongodb\StatFood;
        $today = date("Ymd");
        $list_two = $mgo_stat->GetStatList([
            'food_id_list' => $food_id_list,
            'shop_id'      => $shop_id,
            'begin_day'    => $today,
            'end_day'      => $today,
        ]);
        // food_id --> 已售出量
        $id2food_sold_num = [];
        foreach($list_two as $i => $v)
        {
            $id2food_sold_num[$v->food_id] = $v->sold_num;
        }
        // 查看餐品存量
        $all_food = [];
        $food_one = [];
        foreach($need_food_list as $food)
        {

            //每日限售量
            $stock_num_day = (int)$id2stock_num_day[$food->food_id];
            //当日出售量
            $food_sold_num = (int)$id2food_sold_num[$food->food_id];
            // 库存够吗？
            if($food->food_num > $stock_num_day - $food_sold_num)
            {

                $food_one['food_name'] = $food->food_name;
                $food_one['food_id']   = $food->food_id;
                $food_one['stock_num_day']   = $stock_num_day - $food_sold_num;
                array_push($all_food,$food_one);
            }
        }
        return $all_food;
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
    $data->code_time  = time() + 5 * 60;
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
//判断菜品上架状态
static function GetFoodSaleOff($food_info)
{

    //菜品状态为下架
    if($food_info->sale_off == 1)
    {
        return 1;
    }
    //判断菜品是否处于上架时间
    if($food_info->sale_off_way == SaleFoodSetTime::SETTIME || $food_info->sale_off_way == SaleFoodSetTime::SETWEEK)
    {
        $b                = 0;
        $time_range_stamp = isset($food_info->food_sale_time) ? $food_info->food_sale_time : '';
        $time_range_week  = isset($food_info->food_sale_week) ? $food_info->food_sale_week : '';
       //菜品时间戳判断
        if($time_range_stamp != '' || $time_range_week != '')
        {
            if($time_range_stamp != '')
            {
                foreach ($time_range_stamp as $food_time)
                {
                    $start_time = $food_time->start_time;
                    $end_time   = $food_time->end_time;
                    if($start_time < time() && time() < $end_time)
                    {
                        $b++;
                    }
                }
            }
            //菜品时间周判断
            if($time_range_week != ''){
                if(in_array(date('w'), $time_range_week))//国际判断周是用0-6,0表示周日
                {
                    $b++;
                }
            }
            if($b == 0)
            {
                return 1;
            }
        }
    }
    //目前PAD端并不使用店铺餐时功能来做
    //判断菜品所属分类是否处于经营时间
//    $shop_info = \Cache\Shop::Get($food_info->shop_id);
//    $open_times = $shop_info->opening_time;
//    //来获取营业时间中的type值
//    $num = '';
//    foreach ($open_times as $open_time)
//    {
//
//        $type  = $open_time->type;
//        $froms = $open_time->from;
//        $tos   = $open_time->to;
//        $time  = time();
//        $from  = ' ' . $froms->hh . ':' . $froms->mm . ':' . $froms->ss;
//        $to    = ' ' . $tos->hh . ':' . $tos->mm . ':' . $tos->ss;
//        if($tos->hh < $froms->hh)
//        {
//            $time1 = date('Y-m-d') . $from;
//            $time2 = date('Y-m-d', strtotime('+1 day')) . $to;
//        } else {
//            $time1 = date('Y-m-d') . $from;
//            $time2 = date('Y-m-d') . $to;
//        }
//        $time1 = strtotime($time1);
//        $time2 = strtotime($time2);
//        if($time1 < $time && $time < $time2){
//            $num[] = $type;     //获取到所有时间段的type值
//            //break;
//        }
//    }
//    $lang     = count($num);
//    $category = \Cache\Category::Get($food_info->category_id);
//    //分类的时间段
//    $cate_time = $category->opening_time;
//    //type不是2的判断时间
//    if($category->type != CateType::TYPETWO)
//    {
//        //判断是否是在这个时间段
//        if($lang > 0)
//        {
//            $a = 0;
//            foreach ($num as $v)
//            {
//                if(in_array($v, $cate_time))
//                {
//                    $a++;
//                }
//
//            }
//            if($a == 0){
//                return 1;
//            }
//        }
//    }
    return 0;
}
// PAD端发送餐品变动通知
static  function NotifyFoodChange($shop_id, $food_id)
{
        $url = Cfg::instance()->orderingsrv->webserver_url;
        $ret_json = PageUtil::HttpPostJsonEncData(
            $url,
            [ // $data
                'name' => "cmd_publish",
                'param' => json_encode([
                    'opr'   => "general",
                    'param' => [
                        'topic' => "food_change@" . $shop_id,
                        'data'=> [
                            'food_id' => $food_id,
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
                            'order_id'     => $order_id,
                            'order_status' => $order_status,
                        ]
                    ],
                ])
            ]
        );
        return $ret_json;
}
// 发送订单催菜通知
function NotifyOrderRemind($order_info)
{
        $shop_id = $order_info->shop_id;
        $order_id = $order_info->order_id;
        $order_water_num = $order_info->order_water_num;
        LogDebug($order_info->order_water_num);
        $url = Cfg::instance()->orderingsrv->webserver_url;
        $ret_json = PageUtil::HttpPostJsonEncData(
            $url,
            [ // $data
                'name' => "cmd_publish",
                'param' => json_encode([
                    'opr'   => "general",
                    'param' => [
                        'topic' => "order_remind@" . $shop_id,
                        'data'=> [
                            'order_id' => $order_id,
                            'serial_number' => $order_water_num,
                        ]
                    ],
                ])
            ]
        );
        return $ret_json;
}
//根据餐桌合成二维码
function ComposeQR($shop, $seat, &$pic, $qrsize=22)
{
    // 合成图片
    $bigImgPath = '../static/img/table_qr.4e28885.png';
    $qCodePath  = PageUtil::GetSeatQrcodeImg($shop["shop_id"], $seat["seat_id"], $qrsize);
    $bigImg     = imagecreatefromstring(file_get_contents($bigImgPath));
    $qCodeImg   = imagecreatefromstring(file_get_contents($qCodePath));

    if(!$bigImg)
    {
        LogErr("img err");
        return 0;
    }

    imagesavealpha($bigImg,true);
    list($qCodeWidth, $qCodeHight, $qCodeType) = getimagesize($qCodePath);
    list($bigWidth, $bigHight, $bigType) = getimagesize($bigImgPath);

    imagecopymerge($bigImg, $qCodeImg, ($bigWidth-$qCodeWidth)/2, 355, 0, 0, $qCodeWidth, $qCodeHight, 100);

    header("Content-type: image/png");
    $pic = time().rand(0,999).".png";
    imagepng($bigImg,$pic);

    //加上文字
    $type = 1;
    $font = Cfg::instance()->GetFontFile($type);//"data/ording/font/msyhbd.ttf";字体文件
    //获取图片信息
    //图片信息
    list($pic_w, $pic_h, $pic_type) = getimagesize($pic);
    $image = imagecreatefrompng($pic);
    imagesavealpha($image,true);//这里很重要 意思是不要丢了图像的透明色;

    //指定字体颜色
    $col = imagecolorallocate($image,254,254,254);
    $yecol = imagecolorallocate($image, 238, 70, 64);
    //指定字体内容
    $arr = imagettfbbox(22,0,$font,$shop['shop_name']);
    $text_width = $arr[2]-$arr[0];
    //给图片添加文字
    imagefttext($image, 22, 0, ($pic_w-$text_width)/2, 317, $yecol, $font, $shop['shop_name']);
    $seat_arr = imagettfbbox(32,0,$font,$seat["seat_name"]);
    $seat_width = $seat_arr[2]-$seat_arr[0];
    imagefttext($image, 32, 0, ($pic_w-$seat_width)/2, 240, $col, $font, $seat["seat_name"]);
    imagepng($image,$pic);
    return $pic;
}

// 调整图片大小
static function ImgCopy($imgfrom, $imgto, $new_width, $new_height=null)
{
    list($width, $height, $type) = getimagesize($imgfrom);
    if(!isset($new_height))
    {
        $new_height = $height * ($new_width / $width);
    }
    if($width == $new_width && $height == $new_height)
    {
        return copy($imgfrom, $imgto);
    }
    $new = imagecreatetruecolor($new_width, $new_height);
    switch($type)
    {
        case 2:
            $img = imagecreatefromjpeg($imgfrom);
            //copy部分图像并调整
            imagecopyresized($new, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            //图像输出新图片、另存为
            imagejpeg($new, $imgto);
            break;
        case 3:
            $img = imagecreatefrompng($imgfrom);
            imagecopyresized($new, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagepng($new, $imgto);
            break;
    }
    imagedestroy($new);
    imagedestroy($img);
    return true;
}
//下单支付成功后完成后发送短信
static function PayOrderGetMessage($order, $shopinfo)
    {
        if($order->dine_way ==SALEWAY::EAT)
        {
            $dine_way = '在店吃';
        }elseif ($order->dine_way == SALEWAY::TAKEOUT)
        {
            $dine_way = '外卖';
        }else{
            $dine_way = '其他';
        }
        if($order->pay_way == PayWay::WEIXIN)
        {
            $pay_way = '微信';
        }elseif ($order->pay_way == PayWay::APAY)
        {
            $pay_way = '支付宝';
        }else{
            $pay_way = '其他';
        }
        $str = '';
        foreach ($order->food_list as $v)
        {
            $str .= $v->food_name.'  '.$v->food_num.'份'."\r\n";
        }

        //LogDebug($order->customer_phone);
        if($order->customer_phone /*&& $shopinfo->auto_order == AutoOrder::Yes*/)
        {
            //执行成功后发送短信
            $msg = '欢迎光临'.$shopinfo->shop_name.'店铺，您的订单正在快速的制作中，请耐心等待！
            消费方式：'.$dine_way.'
            订单号：'.$order->order_id.'
            时间：'.date("Y-m-d H:i:s",$order->order_time).'
            订单详情：
              '.$str.'
            菜品合计：'.$order->food_price_all.'
            消费合计：'.$order->paid_price.'
            支付方式：'.$pay_way.'
            多谢惠顾！
            本店外卖电话：'.$shopinfo->telephone.'';
            //LogDebug($order->customer_phone);
            //LogDebug($msg);
            $msg_ret = Util::SmsSend($order->customer_phone, $msg);
            LogDebug($msg_ret);
            if(0 != $msg_ret)
            {
                LogErr("phone send err".$msg_ret);
            }else{
                LogDebug("phone send successful");
            }
        }
    }
//下单支付成功后的订单统计
static function PayOrderBoard($order, $shop_info, $agent_info, $consume_amount)
{
        //保存统计数据
        $platformer     = new Mgo\StatPlatform;
        $shop_stat      = new \DaoMongodb\StatShop;
        $day            = date('Ymd',time());
        $platform_id    = PlatformID::ID;//现在只有一个运营平台id
        LogDebug($order->order_id);
        LogDebug($order->order_status);
        if($order->order_status == NewOrderStatus::REFUND || ($order->order_status == NewOrderStatus::CLOSER && $order->pay_status == PayStatus::PAY) || $order->order_status == NewOrderStatus::KNOT){
            $consume_amount = 0-(float)$consume_amount;
            $customer_num   = 0-$order->customer_num;
            $num            = -1;
        }elseif ($order->order_status == NewOrderStatus::CLOSER && $order->pay_status == PayStatus::NOPAY)
        {
            $consume_amount = 0;
            $customer_num   = 0;
            $num            = 0;
        }
        else{
            $consume_amount = (float)$consume_amount;
            $customer_num   = $order->customer_num;
            $num            = 1;
        }
        LogDebug($consume_amount);
        LogDebug($customer_num);
        LogDebug($num);
        LogDebug($agent_info->agent_type);
        LogDebug($order->order_from);
        if($agent_info->agent_type == AgentType::AREAAGENT)
        {
            if($order->order_from == OrderFrom::SHOUYIN)
            {
                $platformer->SellNumAdd($platform_id, $day, ['region_consume_amount'=>$consume_amount,'customer_num'=>$customer_num,
                                                             'region_cash_order_num' =>$num]);
            }elseif ($order->order_from == OrderFrom::SELF)
            {
                $platformer->SellNumAdd($platform_id, $day, ['region_consume_amount'=>$consume_amount,'customer_num'=>$customer_num,
                                                             'region_self_order_num' =>$num]);
            }elseif ($order->order_from == OrderFrom::WECHAT)
            {
                $platformer->SellNumAdd($platform_id, $day, ['region_consume_amount'=>$consume_amount,'customer_num'=>$customer_num,
                                                             'region_wx_order_num' =>$num]);
            }elseif ($order->order_from == OrderFrom::PAD)
            {
                $platformer->SellNumAdd($platform_id, $day, ['region_consume_amount'=>$consume_amount,'customer_num'=>$customer_num,
                                                             'region_pad_order_num' =>$num]);
            }elseif ($order->order_from == OrderFrom::APP)
            {
                $platformer->SellNumAdd($platform_id, $day, ['region_consume_amount'=>$consume_amount,'customer_num'=>$customer_num,
                                                             'region_app_order_num' =>$num]);
            }elseif ($order->order_from == OrderFrom::MINI)
            {
                $platformer->SellNumAdd($platform_id, $day, ['region_consume_amount'=>$consume_amount,'customer_num'=>$customer_num,
                                                             'region_mini_order_num' =>$num]);
            }
        }elseif($agent_info->agent_type == AgentType::GUILDAGENT){
            if($order->order_from == OrderFrom::SHOUYIN)
            {
                $platformer->SellNumAdd($platform_id, $day, ['industry_consume_amount'=>$consume_amount,'customer_num'=>$customer_num,
                                                             'industry_cash_order_num' =>$num]);
            }elseif ($order->order_from == OrderFrom::SELF)
            {
                $platformer->SellNumAdd($platform_id, $day, ['industry_consume_amount'=>$consume_amount,'customer_num'=>$customer_num,
                                                             'industry_self_order_num'=>$num]);
            }elseif ($order->order_from == OrderFrom::WECHAT)
            {
                $platformer->SellNumAdd($platform_id, $day, ['industry_consume_amount'=>$consume_amount,'customer_num'=>$customer_num,
                                                             'industry_wx_order_num'=>$num]);
            }elseif ($order->order_from == OrderFrom::PAD)
            {
                $platformer->SellNumAdd($platform_id, $day, ['industry_consume_amount'=>$consume_amount,'customer_num'=>$customer_num,
                                                             'industry_pad_order_num' =>$num]);
            }elseif ($order->order_from == OrderFrom::APP)
            {
                $platformer->SellNumAdd($platform_id, $day, ['industry_consume_amount'=>$consume_amount,'customer_num'=>$customer_num,
                                                             'industry_app_order_num' =>$num]);
            }elseif ($order->order_from == OrderFrom::MINI)
            {
                $platformer->SellNumAdd($platform_id, $day, ['industry_consume_amount'=>$consume_amount,'customer_num'=>$customer_num,
                                                             'industry_mini_order_num'=>$num]);
            }
        }
        LogDebug($consume_amount);
        LogDebug($customer_num);
        LogDebug($shop_info->agent_id);
        $shop_stat->SellShopNumAdd($shop_info->shop_id, $day, ['consume_amount'=>$consume_amount,'customer_num'=>$customer_num], $shop_info->agent_id);
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
}// end of class PageUtil{...
?>
