<?php
/*
 * 工具函数等
 * [rockyshi 2014-03-26 12:59:47]
 *
 */
require_once("Log.php");
require_once("crypt.php");
class Util{

// 取微秒
static function GetUsec()
{
    $t = explode(' ', microtime());
    return sprintf("%u", ($t[0] + $t[1]) * 1000000);
}

// 取毫秒
static function GetMsec()
{
    $t = explode(' ', microtime());
    return sprintf("%u", intval(($t[0] + $t[1]) * 1000));

}

// 时间格式转换
static function TimeTo($timestamp, $format='%Y-%m-%d %H:%M:%S')
{
    if(!$timestamp)
    {
        $timestamp = time();
    }
    return strftime($format, $timestamp);
}

static function SecFormat($sec, $format=0)
{
    $sec = (int)$sec;
    if($sec >= 3600*24) // 大于一天
    {
        return round((float)$sec / (3600*24), 1) . "<font color='#73008C'>天</font>";
    }
    else if($sec >= 3600) // 大于一小时
    {
        return round((float)$sec / (3600), 1) . "<font color='#107600'>时</font>";
    }
    else if($sec >= 60) // 大于一分钟
    {
        return (int)($sec / (60)) . "<font color='#3D55DF'>分</font>";
    }
    else if(0 == $sec)
    {
        return $sec;
    }
    else
    {
        return $sec . "<font color='#8A750C'>秒</font>";
    }
}

// 分转为元
// Rocky 2017-05-03 18:52:10
static function FenToYuan($fen){
    return round((int)$fen / 100, 2);
}

// 元转为分
// Rocky 2017-05-04 02:33:32
static function YuanToFen($yuan){
    return (int)((float)$yuan * 100);
}

// 时间戳转为周
static function ToWeek($timestamp, $type=0){
    $week = date("w", $timestamp);
    $txt = array(
        0 => array("星期日","星期一","星期二","星期三","星期四","星期五","星期六"),
        1 => array("周日","周一","周二","周三","周四","周五","周六"),
        2 => array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"),
        3 => array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"),
    );
    return $txt[$type][$week];
}

// 取len长的随机字符串
// [rockyshi 2014-04-02 20:53:34]
static function GetRandString($len, $range="")
{
    $range = $range ? $range : 'abcdefghijklmnopqrstuvwxyz';
    $range_len = strlen($range);
    $ret = '';
    for($i=0; $i<$len; $i++)
    {
        $ret .= $range{mt_rand(0, $range_len - 1)};
    }
    return $ret;
}

// rocky 2014-04-27
// 解析url形式的字符串为key/value数组
// a=1&b=xxx&c=%E4%B8%AD%E5%9B%BD&XdeF3hpirHY1PHUiCFQFl2kZOY319tKt=1    --->  {"a":"1","b":"xxx","c":"中国","XdeF3hpirHY1PHUiCFQFl2kZOY319tKt":"1"}
function ParseUrlParam($str, $k1="&", $k2="=")
{
    $result = array();
    $v1 = explode($k1, $str);
    foreach ($v1 as $v1_key => $v1_value) {
        $v2 = explode($k2, $v1_value);
        $result[ $v2[0] ] = urldecode($v2[1]);
    }
    return $result;
}

// 解密返回经过rsa等加密后的数据
// return: {"a":"1","b":"xxx","c":"中国","Xy9T8KEPBPPmvXCcBmIW7xM3JRJ1zF9I":"1"}
function GetSubmitData()
{
    static $param = null;
    if(null === $param)
    {
        // // 当有参数rsapasswd、magic、data时认为是加密数据；
        // if(!empty($_REQUEST['rsapasswd'])
        //    && !empty($_REQUEST['magic'])
        //    && isset($_REQUEST['data'])
        //   )
        // {
        //     $rsapasswd = Util::RsaDec($_REQUEST['rsapasswd']);
        //     $magic = $_REQUEST['magic'];
        //     $data = Crypt::decode($rsapasswd, $_REQUEST['data']);
        //     $param = Util::ParseUrlParam($data);

        //     if(1 != $param[$magic])
        //     {
        //         LogErr("data err, \$magic=[$magic]");
        //         $param = array();
        //     }
        // }

        // 验证签名
        if(md5($_REQUEST['data'] + Cfg::instance()->rsa->privatekey) === $_REQUEST['sign'])  // 引用Cfg [XXX]
        {
            $param = Util::ParseUrlParam($data);
        }
        else
        {
            $param = [];
        }
    }
    return $param;
}

static function EmptyToDefault($value, $default)
{
    return (!isset($value) || $value === '') ? $default : $value;
}

// 删除目录及目录下的所有文件
// 2014-05-22
static function DeleteDir($dirname)
{
    $handle = opendir("$dirname");
    if(!$handle)
    {
        return -1;
    }
    while(false !== ($item = readdir($handle)))
    {
        if($item != "." && $item != ".." )
        {
            if(is_dir("$dirname/$item"))
            {
                DeleteDir("$dirname/$item");
            }
            else
            {
                @unlink("$dirname/$item");
            }
        }
    }
    closedir($handle);
    rmdir($dirname);
    return 0;
}

// 取文件、目录列表
// [rockyshi 2014-09-30 13:05:35]
static function GetFileList($dirname)
{
    $handle = opendir("$dirname");
    if(!$handle)
    {
        LogErr("open dir err, handle=[$handle], dirname=[$dirname]");
        return -1;
    }

    $cmp = function($a, $b){
        return strcasecmp($a->filename, $b->filename);
    };
    $dir  = array();
    $file = array();
    while(false !== ($item = readdir($handle)))
    {
        // if($item != "." && $item != ".." )
        {
            if(is_dir("$dirname/$item"))
            {
                $dir[] = Util::GetFileInfo("$dirname/$item");
            }
            else
            {
                $file[] = Util::GetFileInfo("$dirname/$item");
            }
        }
    }
    closedir($handle);
    usort($dir, $cmp);
    usort($file, $cmp);
    $list = (object)array(
        dir  => $dir,
        file => $file
    );
    return $list;
}

// 取文件、目录信息
// [rockyshi 2014-09-30 13:07:26]
// $ret = {
//   filename : filename,
//   size : xxx,    // 参见下面
// }
static function GetFileInfo($filename)
{
    /*
     * 0	dev	设备名
     * 1	ino	号码
     * 2	mode	inode 保护模式
     * 3	nlink	被连接数目
     * 4	uid	所有者的用户 id
     * 5	gid	所有者的组 id
     * 6	rdev	设备类型，如果是 inode 设备的话
     * 7	size	文件大小的字节数
     * 8	atime	上次访问时间（Unix 时间戳）
     * 9	mtime	上次修改时间（Unix 时间戳）
     * 10	ctime	上次改变时间（Unix 时间戳）
     * 11	blksize	文件系统 IO 的块大小
     * 12	blocks	所占据块的数目
     */
    $info = (object)(lstat($filename));
    $info->filename = basename($filename);
    $info->filetype = is_dir($filename) ? "dir" : "file";
    $info->path     = $filename;                   // 包括目录
    return (object)$info;
}

// 2014-06-17
// 字节加单位
static function ByteFormat($byte)
{
    $byte = (int)$byte;
    if($byte < 1024)
    {
        return $byte . "B";
    }
    elseif($byte < 1048576) // == 1024*1024
    {
        return sprintf("%0.2fK", $byte / 1024);
    }
    elseif($byte < 1073741824) // == 1024*1024*1024
    {
        return sprintf("%0.2fM", $byte / 1048576);
    }
    else
    {
        return sprintf("%0.2fG", $byte / 1073741824);
    }
}

function ToByte($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // 自 PHP 5.1.0 起可以使用修饰符 'G'
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

static function FileCrc32($filename)
{
    return sprintf("%u", crc32(file_get_contents($filename)));
}

// 发送本地文件到前端（文件下载）
static function SendFileToClient($localname, $remotefile)
{
    $fp = fopen($localname, "rb");
    if(!$fp)
    {
        LogErr("file not exist: localname=[$localname]");
        return errcode::FILE_NOT_EXIST;
    }
    $file_size = filesize($localname);
    Header("Content-type: application/octet-stream");
    Header("Connection: Keep-Alive");
    Header("Accept-Ranges: bytes");
    Header("Accept-Length: $file_size");
    Header("Content-Disposition: attachment; filename=$remotefile");    //弹出客户端对话框，对应的文件名
    //防止服务器瞬时压力增大，分段读取
    while(!feof($fp))
    {
        $file_data = fread($fp, 1024);
        echo "$file_data";
    }
    //关闭文件
    fclose($fp);
    LogDebug("send file ok: localname=[$localname], remotefile=[$remotefile]");
    return 0;
}

// 发送数据到前端（文件下载）
static function SendDataToClient($data, $remotefile)
{
    $size = strlen($data);
    Header("Content-type: application/octet-stream");
    Header("Connection: Keep-Alive");
    Header("Accept-Ranges: bytes");
    Header("Accept-Length: $size");
    Header("Content-Disposition: attachment; filename=$remotefile");    //弹出客户端对话框，对应的文件名
    echo "$data";
    LogDebug("send data ok: remotefile=[$remotefile]");
    return 0;
}

// 两个数组合并有一个新数组
static function ArrayJoin(/*$ary1, $ary2, ...*/)
{
    $ret = array();
    $numargs = func_num_args();
    $arg_list = func_get_args();
    for($i=0; $i<$numargs; $i++)
    {
        $item = $arg_list[$i];
        for($j=0; $j<count($item); $j++)
        {
            $ret[] = $item[$j];
        }
    }
    return $ret;
}

// 处理路径（去掉../和./，如：“a/b/../c/” ---> “a/c/”）  [rockyshi 2013-05-08 11:07:15]
// $path = PathNormalize($path)
function PathNormalize($path)
{
    // 处理两部分：./ 和 ../
    $part = explode("/", $path);
    $prev = array();
    foreach($part as $i => &$item)
    {
        if($i > 0)
        {
            if($item === "." )
            {
                // echo "del: $i \n";
                unset($part[$i]);
                continue;
            }
            else if($item === ".." )
            {
                $n = array_pop($prev);
                // echo "del: $i $n\n";
                unset($part[$i]);
                unset($part[$n]);
                continue;
            }
            else if($item === "")
            {
                // echo "del: $i\n";
                unset($part[$i]);
                continue;
            }
        }
        array_push($prev, $i);
    }
    // print_r($part);
    return join("/", $part);
}

//
static function GetHostIp()
{
    $ip = $_SERVER["SERVER_ADDR"];
    if(!$ip)
    {
        $ip = GetHostByName($_SERVER['SERVER_NAME']);
    }
    return $ip;
}

// 格式化json串
// (网上整理来)
static function JsonPretty($json)
{
     $result = '' ;
     $pos = 0 ;
     $strLen = strlen ($json) ;
     $indentStr = '    ' ;
     $newLine = "\n" ;
     $prevChar = '' ;
     $outOfQuotes = true ;
     for ( $i = 0 ; $i <= $strLen ; $i ++ ) {
         // Grab the next character in the string.
         $char = substr ( $json , $i , 1 ) ;
         // Are we inside a quoted string?
         if ( $char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
            // If this character is the end of an element,
            // output a new line and indent the next line.
        } else if (($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;

            for ($j = 0; $j < $pos; $j++) {

                $result .= $indentStr;

            }
        }

        // Add the character to the result string.
        $result .= $char;
        // If the last character was the beginning of an element,
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {

            $result .= $newLine;
            if ($char == '{' || $char == '[' ) {
                 $pos ++ ;
             }
             for ( $j = 0 ; $j < $pos ; $j ++ ) {
                 $result .= $indentStr ;
             }
         }
         $prevChar = $char ;
     }
     return $result ;
}//end of static function JsonIdent(...

// 对象转为json串
static function ToJson($obj, $pretty=0)
{
    $json = json_encode($obj);
    if($pretty)
    {
        $json = JsonPretty($json);
    }
    return $json;
}

// json串转为对象
static function FromJson($json)
{
    return json_decode($json);
}

// 字符串$str是否有$prefix前缀
static function HasPrefix($str, $prefix)
{
    return strpos($str, $prefix) === 0;
}

//
static function GenId()
{
    $id = new MongoId();
    return (string)$id;
}

// 调整数片大小
static function ImgAdjust($imgfrom, $imgto)
{
    // $filename="16.jpg";
    static $MAX_WIDTH = 800;
    print_r(getimagesize($imgfrom));
    list($width, $height)=getimagesize($imgfrom);
    if($width <= $MAX_WIDTH)
    {
        //echo "width:$width, MAX_WIDTH:$MAX_WIDTH\n";
        return;
    }
    $per = 800/$width;
    $n_w = $width*$per;
    $n_h = $height*$per;
    echo "width:$width, height:$height, n_w:$n_w, n_h:$n_h \n";
    $new = imagecreatetruecolor($n_w, $n_h);
    $img = imagecreatefromjpeg($imgfrom);
    //copy部分图像并调整
    imagecopyresized($new, $img, 0, 0, 0, 0, $n_w, $n_h, $width, $height);
    //图像输出新图片、另存为
    imagejpeg($new, $imgto);
    imagedestroy($new);
    imagedestroy($img);
}
// 发短信 [Rocky 2018-04-27 11:55:03]
//      调用 SmsSend(xxx, "大家好，我是赛领老王。")
//      接收到的短信如：【赛领欣吃货】大家好，我是赛领老王。
static function SmsSend($phone, $msg)
    {
        if(!$phone || !$msg)
        {
            LogErr("参数出错");
            return -1;
        }
        $data = (object)[
            "account"  => "N0661361",
            "password" => "CV4kTfgWKoQ1EShE",
            "msg"      => "$msg",
            "phone"    => "$phone",
            //"sendtime" => strftime(time(), '%Y%m%d%H%M%S'),
            "report"   => "true",
            "extend"   => "000",
            "uid"      => getenv("USER"),
        ];
        $content = json_encode($data);
        $context = [
            'http' => [
                'timeout' => 3000,
                'method'  => 'POST',
                'header'  => 'Content-Type: application/json; charset=utf-8',
                'content' => $content,
            ]
        ];

        $url = "http://smssh1.253.com/msg/send/json";
        // {"time":"20180427113449","msgId":"","errorMsg":"定时发送时间格式不正确","code":"127"}
        // {"time":"20180427113517","msgId":"18042711351721212","errorMsg":"","code":"0"}
        $ret_str = file_get_contents($url, false, stream_context_create($context));
        $ret = json_decode($ret_str);
        if("0" != $ret->code)
        {
            LogErr($ret_str);
            return errcode::SMS_SEND_ERR;
        }
        return 0;
    }


}//end of class Util{...
?>
