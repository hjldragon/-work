<?php
/*
 * [rockyshi 2014-09-24]
 * 保存图片
 *
 */
ob_start();
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
include_once("3rd/phpqrcode.php");
ob_end_clean();

function GetImg(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $width   = (int)$_["width"];
    $imgname = $_["imgname"];
    $type    = (int)$_["type"];
    if($type == ImgType::NONE){
        $imgpath = PageUtil::GetImgFullname($imgname);
    }
    if($type == ImgType::USER){
        $imgpath = Cfg::GetUserImgFullname($imgname);
    }
    if("" == $imgpath)
    {
        LogErr("get dest file err: [$imgpath]");
        return -1;
    }

    LogDebug("$imgpath");

    header('Content-type: image/jpg'); //输出图片头
    readfile($imgpath);
    exit(0);

    // if($width > 0 && Thumb::IsSupport())
    // {
    //     $w = is_numeric($width)? intval($width) : 120;
    //     Thumb::maxWidth($img_bytes, $w);
    // }
    // else
    // {
    //     header('Content-type: image/png'); //输出图片头
    //     echo $img_bytes;
    // }

    // exit(0);
    // return 0;
}

function GetSeatQrcode(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id = $_["shop_id"];
    $seat_id = $_["seat_id"];
    if(!$shop_id || !$seat_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $seat_name = \Cache\Seat::GetSeatName($seat_id);
    $seat_qrcode_img = PageUtil::GetSeatQrcodeImg($shop_id, $seat_id);
    
    LogDebug("seat_qrcode_img:[$seat_qrcode_img]");

    header('Content-type: image/jpg'); //输出图片头
    readfile($seat_qrcode_img);
    exit(0);
}

function GetFoodQrcode(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = $_["shop_id"];
    $food_id = $_["food_id"];
    if(!$shop_id || !$food_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $food_qrcode_img = PageUtil::GetFoodQrcodeImg($shop_id, $food_id);
    
    LogDebug("food_qrcode_img:[$food_qrcode_img]");

    header('Content-type: image/jpg'); //输出图片头
    readfile($food_qrcode_img);
    exit(0);
}

function GetLoginQrcode(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    
    $token = $_["token"];
    if(!$token)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $tokendata = \Cache\Login::Get($token);
    $login_qrcode_img = PageUtil::GetLoginQrcodeImg($token);
    
    LogDebug("login_qrcode_img:[$login_qrcode_img]");

    header('Content-type: image/jpg'); //输出图片头
    readfile($login_qrcode_img);
    exit(0);
}

function GetUrlQrcode(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $url = $_["url"];
    $url = urlencode($url);
    $url_qrcode_img = PageUtil::GetUrlQrcodeImg($url);
    
    LogDebug("url_qrcode_img:[$url_qrcode_img]");

    header('Content-type: image/jpg'); //输出图片头
    readfile($url_qrcode_img);
    exit(0);
}

function GetBindingQrcode(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid  = $_["userid"];
    $type    = $_["type"];  // 0:解绑,1:绑定
    $token   = $_["token"];
    $url_qrcode_img = PageUtil::GetBindingQrcodeImg($userid, $token, $type);
    
    LogDebug("url_qrcode_img:[$url_qrcode_img]");

    header('Content-type: image/jpg'); //输出图片头
    readfile($url_qrcode_img);
    exit(0);
}

function ExportSeatQrcode(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id = $_["shop_id"];
    $seat_list = json_decode($_["seat_list"]);

    $tmpfile = Cfg::instance()->GetTmpPath("qrcode_" . Util::GetRandString(16) . ".zip");
    $zip = new ZipArchive;
    if($zip->open($tmpfile, ZipArchive::OVERWRITE) !== TRUE)
    {
        LogErr("can't create zip:[$tmpfile]");
        return errcode::SYS_ERR;
    }
    foreach($seat_list as $i => $seat_id)
    {
        $seat_name = \Cache\Seat::GetSeatName($seat_id);
        if("" == $seat_name)
        {
            LogErr("seat err, id:[$seat_id]");
            return errcode::SEAT_NOT_EXIST;
        }
        $seat_qrcode_img = PageUtil::GetSeatQrcodeImg($shop_id, $seat_id);
        LogDebug("seat_name:[$seat_name], seat_qrcode_img:[$seat_qrcode_img]");
        // 转码
        $seat_name = iconv("UTF-8", "GB2312//IGNORE", $seat_name);
        $zip->addFile($seat_qrcode_img, "{$seat_name}.png");
    }
    $zip->close();

    $zipname = "qrcode_" . $shop_id . "_" . Util::TimeTo(0, '%Y%m%d%H%M%S') . ".zip";
    LogDebug("zipname:[$zipname]");

    Util::SendFileToClient($tmpfile, $zipname);
    exit(0);
}

$_ = $_REQUEST;
LogDebug($_);
$ret = -1;
$resp = null;
if($_["img"])
{
    $ret =  GetImg($resp);
}
else if($_["get_seat_qrcode"])
{
    $ret =  GetSeatQrcode($resp);
}
else if($_["get_food_qrcode"])
{
    $ret =  GetFoodQrcode($resp);
}
else if($_["get_login_qrcode"])
{
    $ret =  GetLoginQrcode($resp);
}
else if($_["get_binding_qrcode"])
{
    $ret =  GetBindingQrcode($resp);
}
else if($_["get_url_qrcode"])
{
    $ret =  GetUrlQrcode($resp);
}
else if($_["batch_export_seat_qrcode"])
{
    $ret =  ExportSeatQrcode($resp);
}
else
{
    LogErr("param err");
    $ret =  errcode::PARAM_ERR;
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
