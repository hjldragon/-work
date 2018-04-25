<?php
ob_start();
require_once("current_dir_env.php");
require_once("const.php");
require_once("page_util.php");
require_once("cfg.php");
ob_end_clean();





function downQR($shop_name,$seat_name,&$pic)
{	
	// 合成图片
	$bigImgPath = '../static/img/table_qr.png';
	$qCodePath = PageUtil::GetSeatQrcodeImg($shop_id, $seat_id, 22);
	$bigImg = imagecreatefromstring(file_get_contents($bigImgPath)); 
	$qCodeImg = imagecreatefromstring(file_get_contents($qCodePath));  
	imagesavealpha($bigImg,true);
	list($qCodeWidth, $qCodeHight, $qCodeType) = getimagesize($qCodePath);  

	list($bigWidth, $bigHight, $bigType) = getimagesize($bigImgPath);

	imagecopymerge($bigImg, $qCodeImg, ($bigWidth-$qCodeWidth)/2, 375, 0, 0, $qCodeWidth, $qCodeHight, 100);  

	header("Content-type: image/png");
	$pic = "12.png";
	imagepng($bigImg,$pic);
	//加上文字
	$type = 1;
	$font = Cfg::instance()->GetFontFile($type);
	$con = "号桌";
	//获取图片信息
	//图片信息
	list($pic_w, $pic_h, $pic_type) = getimagesize($pic);
	$image = imagecreatefrompng($pic);
	imagesavealpha($image,true);//这里很重要 意思是不要丢了图像的透明色;
	//指定字体颜色
	$col = imagecolorallocate($image,0,0,0);
	$yecol = imagecolorallocate($image, 255, 111, 7);
	//指定字体内容
	$arr = imagettfbbox(40,0,$font,$shop_name);
	$text_width = $arr[2]-$arr[0];
	//给图片添加文字
	imagefttext($image, 40, 0, ($pic_w-$text_width)/2, 1400, $col, $font, $shop_name);
	$seat_arr = imagettfbbox(40,0,$font,$seat_name);
	$seat_width = $seat_arr[2]-$seat_arr[0];
	$con_arr = imagettfbbox(24,0,$font,$con);
	$con_width = $con_arr[2]-$con_arr[0];
	imagefttext($image, 40, 0, ($pic_w-$seat_width-$con_width)/2, 1200, $yecol, $font, $seat_name);
	imagefttext($image, 24, 0, ($pic_w-$seat_width-$con_width)/2+$seat_width, 1197, $col, $font, $con);
	//指定输入类型
	header('Content-type:image/png');
	//动态的输出图片到浏览器中
	return $image;
}

$pic = '';
$a = downQR("湖南人家","试试we阿斯达所",$pic);
header('Content-type:image/png');
imagepng($a);
unlink($pic);
?>