<?php
/*
 * [Rocky 2016-01-28 15:33:02]
 * 取终端机列表
 */

require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");

$_ = $_REQUEST;
LogDebug($_);

if("screencap" == $_['type'])
{
    $termid = $_['termid'];
    $imgfile = Cfg::instance()->GetScreenCapPath($termid, "{$termid}.png");
    LogDebug("imgfile:[$imgfile]");
    $img = file_get_contents($imgfile);
    if(file_exists($imgfile))
    {
        $img = str_replace('data:image/png;base64,', '', $img);
        echo $img;
        exit(0);
    }
}

// 取图片时间
if("captime" == $_['type'])
{
    $termid = $_['termid'];
    $imgfile = Cfg::instance()->GetScreenCapPath($termid, "{$termid}.png");
    LogDebug("imgfile:[$imgfile]");
    if(file_exists($imgfile))
    {
        echo filemtime($imgfile);
        exit(0);
    }
}

// echo "<h1>err</h1><pre>";
// print_r($_);
?>

