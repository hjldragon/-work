<?php
/*
 * [Rocky 2017-05-05 15:23:40]
 * 保存图片文件
 */
require_once("/www/public.sailing.com/php/img_save.php");
// require_once("current_dir_env.php");
// require_once("page_util.php");
// require_once("const.php");


// //Permission::PageCheck();
// $_=$_REQUEST;


// // 调整数片大小
// function ImgFixed($imgfrom, $imgto)
// {
//     if(!is_uploaded_file($imgfrom))
//     {
//         return;
//     }
//     static $MAX_WIDTH = 800;
//     list($width, $height)=getimagesize($imgfrom);
//     if($width <= $MAX_WIDTH)
//     {
//         //echo "width:$width, MAX_WIDTH:$MAX_WIDTH\n";
//         LogDebug("move_uploaded_file...");
//         return move_uploaded_file($imgfrom, $imgto);
//     }
//     $per = $MAX_WIDTH / $width;
//     $n_w = $width * $per;
//     $n_h = $height * $per;
//     $new = imagecreatetruecolor($n_w, $n_h);
//     $img = imagecreatefromjpeg($imgfrom);
//     //copy部分图像并调整
//     imagecopyresized($new, $img, 0, 0, 0, 0, $n_w, $n_h, $width, $height);
//     //图像输出新图片、另存为
//     imagejpeg($new, $imgto);
//     imagedestroy($new);
//     imagedestroy($img);
//     LogDebug("move end");
//     return true;
// }

// // 保存广告文件
// //    $_FILES["file"]["name"] - 被上传文件的名称
// //    $_FILES["file"]["type"] - 被上传文件的类型
// //    $_FILES["file"]["size"] - 被上传文件的大小，以字节计
// //    $_FILES["file"]["tmp_name"] - 存储在服务器的文件的临时副本的名称
// //    $_FILES["file"]["error"] - 由文件上传导致的错误代码
// //    move_uploaded_file(src, dest)
// //    http://www.w3school.com.cn/php/php_file_upload.asp
// function SaveImgFileHtml5(&$resp=NULL)
// {
//     $_ = $GLOBALS["_"];
//     if(!$_)
//     {
//         LogErr("param err");
//         $msg = "err";
//         return errcode::PARAM_ERR;
//     }

//     $token = $_["token"];
//     $file = $_FILES["imgfile"];

//     //LogDebug($file);
//     if($file["error"] > 0)
//     {
//         LogErr("file upload err: " . json_encode($file));
//         $msg = "err";
//         return errcode::FILE_UPLOAD_ERR;
//     }

//     $md5 = md5_file($file['tmp_name']);

//     $oriname = $file['name']; //basename("");
//     $filesize = filesize($file['tmp_name']); // 注：当文件过大时，$file['size']返回负数
//     $ext = strtolower(pathinfo($oriname, PATHINFO_EXTENSION));
//     // $filetype = PageUtil::FileExtToType($ext);
//     $filename = "$md5.$ext"; // 不包含路径
//     $filemd5 = $md5;
//     $destfile = PageUtil::GetImgFullname($filename);
//     if("" == $destfile)
//     {
//         LogErr("get dest file err: [$destfile]");
//         return -1;
//     }

//     // $ret = move_uploaded_file($file['tmp_name'], $destfile);
//     // $ret = ImgFixed($file['tmp_name'], $destfile);
//     $ret = Util::ImgCopy($file['tmp_name'], $destfile, 480);
//     if(!$ret)
//     {
//         LogErr("move_uploaded_file err:[$destfile]");
//         $msg = "err";
//         return -1;
//     }

//     LogDebug("end, destfile=[$destfile]" .
//              ", ret=[$ret], " .
//              ", filename=[$filename]" .
//              ", oriname=[$oriname]" .
//              ", filetype=[$filetype]" .
//              ", filesize=[$filesize]" .
//              ", filemd5=[$filemd5]");

//     $resp = (object)array(
//         'filename' => $filename,
//         'filesize' => $filesize,
//         'filemd5'  => $filemd5,
//     );
//     LogDebug($resp);
//     LogInfo("--ok--");
//     return 0;
// }


// $ret = -1;
// $resp = (object)array();
// if(isset($_["upload"]))
// {
//     $ret =  SaveImgFileHtml5($resp);
// }

// $html = json_encode((object)array(
//     'ret' => $ret,
//     'data' => $resp
// ));
?>
