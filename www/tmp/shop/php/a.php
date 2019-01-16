<?php
phpinfo();
// ob_start();
// require_once("current_dir_env.php");
// require_once("const.php");
// require_once("/www/shop.sailing.com/php/page_util.php");
// require_once("cfg.php");
// ob_end_clean();


// function downQR($shop_name,$seat_name,&$pic)
// {

//     // 合成图片
//     // $bigImgPath = '../static/img/table_qr.63353dc.png';
//     // $qCodePath  = PageUtil::GetSeatQrcodeImg($shop["shop_id"], $seat["seat_id"], $qrsize);
//     // $bigImg     = imagecreatefromstring(file_get_contents($bigImgPath));
//     // $qCodeImg   = imagecreatefromstring(file_get_contents($qCodePath));
//     // if(!$bigImg)
//     // {
//     //     LogErr("img err");
//     //     return 0;
//     // }

//     // imagesavealpha($bigImg,true);
//     // list($qCodeWidth, $qCodeHight, $qCodeType) = getimagesize($qCodePath);
//     // list($bigWidth, $bigHight, $bigType) = getimagesize($bigImgPath);

//     // imagecopymerge($bigImg, $qCodeImg, ($bigWidth-$qCodeWidth)/2, 375, 0, 0, $qCodeWidth, $qCodeHight, 100);

//     // header("Content-type: image/png");
//     // $pic = time().rand(0,999).".png";
//     // imagepng($bigImg,$pic);

//     // //加上文字
//     // $type = 1;
//     // $font = Cfg::instance()->GetFontFile($type);//"data/ording/font/msyhbd.ttf";字体文件
//     // $con = "号桌";//固定文字
//     // //获取图片信息
//     // //图片信息
//     // list($pic_w, $pic_h, $pic_type) = getimagesize($pic);
//     // $image = imagecreatefrompng($pic);
//     // imagesavealpha($image,true);//这里很重要 意思是不要丢了图像的透明色;

//     // //指定字体颜色
//     // $col = imagecolorallocate($image,0,0,0);
//     // $yecol = imagecolorallocate($image, 255, 111, 7);
//     // //指定字体内容
//     // $arr = imagettfbbox(40,0,$font,$shop['shop_name']);
//     // $text_width = $arr[2]-$arr[0];
//     // //给图片添加文字
//     // imagefttext($image, 40, 0, ($pic_w-$text_width)/2, 1400, $col, $font, $shop['shop_name']);
//     // $seat_arr = imagettfbbox(40,0,$font,$seat["seat_name"]);
//     // $seat_width = $seat_arr[2]-$seat_arr[0];
//     // $con_arr = imagettfbbox(24,0,$font,$con);
//     // $con_width = $con_arr[2]-$con_arr[0];
//     // imagefttext($image, 40, 0, ($pic_w-$seat_width-$con_width)/2, 1200, $yecol, $font, $seat["seat_name"]);
//     // imagefttext($image, 24, 0, ($pic_w-$seat_width-$con_width)/2+$seat_width, 1197, $col, $font, $con);
//     // imagepng($image,$pic);
//     // return $pic;

// 	// 合成图片
// 	$bigImgPath = '../static/img/table_qr.4e28885.png';
// 	$qCodePath = PageUtil::GetSeatQrcodeImg("5", "4", 10);
// 	$bigImg = imagecreatefromstring(file_get_contents($bigImgPath));
// 	$qCodeImg = imagecreatefromstring(file_get_contents($qCodePath));

// 	imagesavealpha($bigImg,true);
// 	list($qCodeWidth, $qCodeHight, $qCodeType) = getimagesize($qCodePath);

// 	list($bigWidth, $bigHight, $bigType) = getimagesize($bigImgPath);

// 	imagecopymerge($bigImg, $qCodeImg, ($bigWidth-$qCodeWidth)/2, 355, 0, 0, $qCodeWidth, $qCodeHight, 100);

// 	header("Content-type: image/png");
// 	$pic = "12.png";
// 	imagepng($bigImg,$pic);
// 	//加上文字
// 	$type = 1;
// 	$font = Cfg::instance()->GetFontFile($type);
// 	//$con = "号桌";
// 	//获取图片信息
// 	//图片信息
// 	list($pic_w, $pic_h, $pic_type) = getimagesize($pic);
// 	$image = imagecreatefrompng($pic);
// 	imagesavealpha($image,true);//这里很重要 意思是不要丢了图像的透明色;
// 	//指定字体颜色
// 	$col = imagecolorallocate($image,254,254,254);
// 	$yecol = imagecolorallocate($image, 238, 70, 64);
// 	//指定字体内容
// 	$arr = imagettfbbox(20,0,$font,$shop_name);
// 	$text_width = $arr[2]-$arr[0];
// 	//给图片添加文字
// 	imagefttext($image, 20, 0, ($pic_w-$text_width)/2, 317, $yecol, $font, $shop_name);
// 	$seat_arr = imagettfbbox(50,0,$font,$seat_name);
// 	$seat_width = $seat_arr[2]-$seat_arr[0];
// 	//$con_arr = imagettfbbox(24,0,$font,$con);
// 	//$con_width = $con_arr[2]-$con_arr[0];
// 	imagefttext($image, 50, 0, ($pic_w-$seat_width)/2, 240, $col, $font, $seat_name);
// 	//imagefttext($image, 24, 0, ($pic_w-$seat_width-$con_width)/2+$seat_width, 200, $col, $font, $con);
// 	//指定输入类型
// 	header('Content-type:image/png');
// 	//动态的输出图片到浏览器中
// 	//return $image;
// 	imagepng($image);
// 	die;
// }



// $pic = '';
// downQR("湖南人家","A15",$pic);
// // header('Content-type:image/png');
// // imagepng($a);
// //unlink($pic);
?>