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
    //LogDebug($param);
    if(null === $param)
    {
        // LogDebug($_REQUEST);
        // 验证签名
        // (
        //     [data] => login=1&username=&password_md5=d41d8cd98f00b204e9800998ecf8427e
        //     [sign] => 0f59a6a38da98801fdb45a222253a718
        // )
        //检测前台发送过来的数据是否是明文
        if(isset($_REQUEST['is_plain']))
        {
            $param['err'] = "is_plain, don't decrypt";
            LogInfo(json_encode($param));
            return $param;
        }
        //这里是加密
        $token = $_REQUEST['token'];
        //LogDebug($token);
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
            //LogDebug($encmode);
            $data = $_REQUEST['data'];
            //LogDebug($data);
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
   // LogDebug($data);
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

    if(OrderStatus::HadConfirmed($orderinfo->order_status))
    {
        LogDebug("order not modify, status:{$orderinfo->order_status}");
        return;
    }

    $mgo_stat = new \DaoMongodb\StatFood;
    $day = date("Ymd");
    foreach($orderinfo->food_list as $i => $food)
    {
        $mgo_stat->SellNumAdd($orderinfo->shop_id, $food->food_id, $day, $food->food_num);
        LogDebug("$food->food_id, $day, $food->food_num");
    }
}

// 检查餐品库存够不够（有不不够的餐品时，返回其餐品信息，满足要求时返回null）
static function CheckFoodStockNum($shop_id, $need_food_list)
{
    // 取出id列表
    $food_id_list = array_map(function($v) {
        return $v->food_id;
    }, $need_food_list);
    // LogDebug($food_id_list);

    // 读出当前餐品每天备货量
    $mgo_food = new \DaoMongodb\MenuInfo;
    $list = $mgo_food->GetFoodList(
        $shop_id,
        [
            'food_id_list' => $food_id_list,
        ],
        [
            'food_id' => 1,
            'food_num_day' => 1,
        ]
    );
    // LogDebug($list);

    // food_id --> 备货量
    $id2food_num_day = [];
    foreach($list as $i => $v)
    {
        $id2food_num_day[$v->food_id] = $v->food_num_day;
    }
    // LogDebug($id2food_num_day);

    // 读出当前已售出量
    $mgo_stat = new \DaoMongodb\StatFood;
    $today = date("Ymd");
    $list = $mgo_stat->GetStatList([
        'food_id_list' => $food_id_list,
        'begin_day' => $today,
        'end_day' => $today,
    ]);
    // LogDebug($list);

    // food_id --> 已售出量
    $id2food_sold_num = [];
    foreach($list as $i => $v)
    {
        $id2food_sold_num[$v->food_id] = $v->sold_num;
    }
    // LogDebug($id2food_sold_num);

    // 查看餐品存量
    foreach($need_food_list as $i => $food)
    {
        $food_num_day = (int)$id2food_num_day[$food->food_id];
        if($food_num_day <= 0)
        {
            continue;
        }
        $food_sold_num = (int)$id2food_sold_num[$food->food_id];

        LogDebug("food_id:[{$food->food_id}], food_num:[{$food->food_num}], food_num_day:[{$food_num_day}], food_sold_num:[{$food_sold_num}]");

        // 库存够吗？
        if($food->food_num > $food_num_day - $food_sold_num)
        {
            LogDebug("not enough");
            return $food;
        }
    }

    return null;
}


}// end of class PageUtil{...
?>
