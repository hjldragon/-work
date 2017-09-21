<?php
/*
 * [rockyshi 2014-09-24]
 * 保存图片
 *
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
include_once("thumb.php");



function GetImg(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $width   = $_["width"];
    $height  = $_["height"];
    $imgname = $_["imgname"];

    $imgpath = PageUtil::GetImgFullname($imgname);
    if("" == $imgpath)
    {
        LogErr("get dest file err: [$imgpath]");
        return -1;
    }
    // LogDebug("[width:$width], [height:$height]");

    if(!$height)
    {
        $height = 0;
    }

    $info = pathinfo($imgpath);
    $imgpath_w_h = "{$info['dirname']}/{$info['filename']}_{$width}_{$height}.{$info['extension']}";
    // LogDebug("[imgpath_w_h:$imgpath_w_h]");

    if(is_file($imgpath_w_h))
    {
        $imgpath = $imgpath_w_h;
    }
    else if(!Util::ImgCopy($imgpath, $imgpath_w_h, $width, $height))
    {
        Util::ImgCopy($imgpath, $imgpath_w_h, $width, $height);
        $imgpath = $imgpath_w_h;
    }
    else
    {
        $imgpath = $imgpath_w_h;
    }

    // LogDebug($imgpath);

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

$_ = $_REQUEST;
LogDebug($_);
$ret = -1;
$resp = null;
if($_["img"])
{
    $ret =  GetImg($resp);
}
LogDebug($html);
$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
