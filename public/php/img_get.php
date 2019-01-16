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
require_once("thumb.php");
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
    $height  = (int)$_["height"];
    $imgname = $_["imgname"];
    $type    = (int)$_["type"];

    if($type == ImgType::USER)
    {
        $imgpath = Cfg::GetUserImgFullname($imgname);
    }
    else
    {
        $imgpath = PageUtil::GetImgFullname($imgname);
    }
    if($width>0) {
        $new_imgpath = "{$imgpath}_{$width}";
        if($height>0) {
            $new_imgpath = "{$imgpath}_{$width}_{$height}";
        }
        if(!file_exists($new_imgpath))
        {
            $ret = Util::ImgCopy($imgpath, $new_imgpath, $width, $height);
            if(!$ret)
            {
                LogErr("move_uploaded_file err:[$new_imgpath]");
                $msg = "err";
                return -1;
            }
        }
        $imgpath = $new_imgpath;
    }

    LogDebug("$imgpath");

    header('Content-type: image/jpg'); //输出图片头
    readfile($imgpath);
    exit(0);

    // if($width > 0 && Thumb::IsSupport())
    // {
    //     $w = is_numeric($width)? intval($width) : 120;
    //     Thumb::maxWidth($imgpath, $w);
    // }
    // else
    // {
    //     header('Content-type: image/jpg'); //输出图片头
    //     readfile($imgpath);
    // }

    //exit(0);
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
    $arr = [];
    foreach($seat_list as $i => $seat_id)
    {
        $seat_info = \Cache\Seat::Get($seat_id);
        $seat_name = $seat_info->seat_name;
        $shop_name = \Cache\Shop::GetShopName($seat_info->shop_id);
        if("" == $seat_name)
        {
            LogErr("seat err, id:[$seat_id]");
            return errcode::SEAT_NOT_EXIST;
        }
        $shop = ["shop_id"=>$seat_info->shop_id,"shop_name"=>$shop_name];
        $seat = ["seat_id"=>$seat_id,"seat_name"=>$seat_name];
        $pic = '';
        $seat_qrcode_img = PageUtil::ComposeQR($shop, $seat, $pic);
        if(!$seat_qrcode_img)
        {
            LogErr("img err");
            echo <<<'eof'
    <script>
    alert("图片出错...");
    </script>
eof;
    exit();
        }
        array_push($arr, $pic);
        // 转码
        $seat_name = iconv("UTF-8", "GB2312//IGNORE", $seat_name);
        $zip->addFile($seat_qrcode_img, "{$seat_name}.png");
    }
    $zip->close();
    $zipname = "qrcode_" . $shop_id . "_" . Util::TimeTo(0, '%Y%m%d%H%M%S') . ".zip";
    LogDebug("zipname:[$zipname]");
    Util::SendFileToClient($tmpfile, $zipname);
    if(count($arr)>0)
    {
        foreach ($arr as $item)
        {
            unlink($item);
        }
    }
    unlink($tmpfile);
    exit(0);
}

function GetVendorQrcode(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $vendor_id = $_["vendor_id"];

    if(!$vendor_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $vendor_qrcode_img = PageUtil::GetVendorQrcodeImg($vendor_id);
    LogDebug("vendor_qrcode_img:[$vendor_qrcode_img]");

    header('Content-type: image/jpg'); //输出图片头
    readfile($vendor_qrcode_img);
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
}else if($_["get_vendor_qrcode"])
{
    $ret =  GetVendorQrcode($resp);
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
