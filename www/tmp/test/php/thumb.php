<?php
class Thumb
{
    static function IsSupport()
    {
        return function_exists("imagecreatetruecolor") && function_exists(imagecreatefromstring);
    }

    /**
     * 以最大宽度缩放图像
     * @param string  $im 图像元数据
     * @param float $w 最大宽度
     */
    static function maxWidth($im,$w){
        if(empty($im) ||  empty($w) || !is_numeric($w)){
            throw new Exception("缺少必须的参数");
        }
        $im = imagecreatefromstring($im); //创建图像
        if(!$im){
            $im  = imagecreatetruecolor(150, 30);
            $bg = imagecolorallocate($im, 255, 255, 255);
            $text_color  = imagecolorallocate($im, 0, 0, 255);
            //填充背景色
            imagefilledrectangle($im, 0, 0, 150, 30, $bg);
            //以图像方式输出错误信息
            imagestring($im, 3, 5, 5, "Error loading image", $text_color);
        }
        list($im_w,$im_h) = self::getsize($im); //获取图像宽高
        if($im_w > $w){
            $new_w = $w;
            $new_h = $w / $im_w * $im_h;
        }else{
            $new_w = $im_w;
            $new_h = $im_h;
        }
        $dst_im = imagecreatetruecolor($new_w,$new_h);
        imagecopyresampled($dst_im,$im,0,0,0,0,$new_w,$new_h,$im_w,$im_h);
        header('Content-type:image/jpeg');
        imagepng($dst_im);
        imagedestroy($dst_im);imagedestroy($im);
    }

    /**
     * 以最大高度缩放图像
     * @param string  $im 图像元数据
     * @param float $w 最大高度
     */
    static function maxHeight($im,$h){
        if(empty($im) ||  empty($h) || !is_numeric($h)){
            throw new Exception("缺少必须的参数");
        }
        $im = imagecreatefromstring($im); //创建图像
        list($im_w,$im_h) = self::getsize($im); //获取图像宽高
        if($im_h > $h){
            $new_w = $h / $im_h * $im_w;
            $new_h = $h;
        }else{
            $new_w = $im_w;
            $new_h = $im_h;
        }
        $dst_im = imagecreatetruecolor($new_w,$new_h);
        imagecopyresampled($dst_im,$im,0,0,0,0,$new_w,$new_h,$im_w,$im_h);
        header('Content-type:image/jpeg');
        imagepng($dst_im);
        imagedestroy($dst_im);imagedestroy($im);
    }

    /**
     * 生成固定大小的图像并按比例缩放
     * @param string  $im 图像元数据
     * @param float $w 最大宽度
     * @param float $h 最大高度
     */
    static function fixed($im,$w,$h){
        if(empty($im) || empty($w) || empty($h) || !is_numeric($w) || !is_numeric($h)){
            throw new Exception("缺少必须的参数");
        }
        $im = imagecreatefromstring($im); //创建图像
        list($im_w,$im_h) = self::getsize($im); //获取图像宽高
        if($im_w > $im_h || $w < $h){
            $new_h = intval(($w / $im_w) * $im_h);
            $new_w = $w;
        }else{
            $new_h = $h;
            $new_w = intval(($h / $im_h) * $im_w);
        }
        //echo "$im_w x $im_h <br/> $new_w x $new_h <br/> $x $y";exit;
        //开始创建缩放后的图像
        $dst_im = imagecreatetruecolor($new_w,$new_h);
        imagecopyresampled($dst_im,$im,0,0,0,0,$new_w,$new_h,$im_w,$im_h);
        header('Content-type:image/jpeg');
        imagepng($dst_im);
        imagedestroy($dst_im);imagedestroy($im);
    }

    /*
     * 获取图像大小
     * @param string  $im 图像元数据
     * @return array
     */
    protected static function getsize($im){
        return array(imagesx($im),imagesy($im));
    }
}

?>
