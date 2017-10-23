<?php
require_once("Log.php");

/*
 * 功能：打乱及还原字符串
 * 编写：Rocky 2010-05-14 10:42:45
 */
class Swap
{
    // 把str顺序打乱
    public static function doit($seed, $str)
    {
        $len = strlen($str);
        $seed += $len; // 再加串的长度做相关性

        for($i=0; $i < $len; $i++)
        {
            $range = $len - $i - 1;
            $m = self::Rand($seed, $i, 0, $range);

            $tmp = $str[ $range ];
            $str[ $range ] = $str[ $m ];
            $str[ $m ] = $tmp;
        }
        return $str;
    }

    // 对应于Swap0()，即还原str串；
    public static function undo($seed, $str)
    {
        $len = strlen($str);
        $seed += $len; // 再加串的长度做相关性

        for($i=$len-1; $i>=0; $i--)
        {
            $range = $len - $i - 1;
            $m = self::Rand($seed, $i, 0, $range);

            $tmp = $str[ $m ];
            $str[ $m ] = $str[ $range ];
            $str[ $range ] = $tmp;
        }
        return $str;
    }

    // 取随机数（伪）
    private static function Rand($seed, $n, $min, $max)
    {
        $m = ( ~($seed * 262147 * $n) ) & 0x0FFFFFFF;
        $range = $max + 1 - $min;
        if($range <= 0)
        {
            $range = 1;
        }
        $ret = ($min + $m) % $range;
        return $ret;
    }
}

class Crypt
{
    // 计算种子（简单相加
    private static function CalSeed($str)
    {
        $seed = 0;
        $len = strlen($str);
        for($i=0; $i < $len; $i++)
        {
            $seed = ($seed + ord($str[$i])) & 0x7FFFFFFF;
        }
        return $seed & 0x7FFFFFFF;
    }

    public static function encode($password, $data)
    {
        $pasd_b64 = rawurlencode($password);    // urlencode
        $pasd_len = strlen($pasd_b64);
        $plaintext_b64 = rawurlencode($data);   // urlencode
        $plaintext_len = strlen($plaintext_b64);
        $ciphertext = '';
        $pasd_i = 0;

        for($data_i=0; $data_i < $plaintext_len; $data_i++)
        {
            //ciphertext += String.fromCharCode(plaintext_b64.charCodeAt(data_i) ^ pasd_b64.charCodeAt(pasd_i));
            $num = ord($plaintext_b64[$data_i]) ^ ord($pasd_b64[$pasd_i]);
            $ciphertext .= sprintf("%02x", $num);
            $pasd_i++;
            if($pasd_i == $pasd_len)
            {
                $pasd_i = 0;
            }
        }
        $seed = self::CalSeed($pasd_b64);
        return Swap::doit($seed, $ciphertext);
    }

    public static function decode($password, $data)
    {
        $pasd_b64 = urlencode($password);
        $pasd_len = strlen($pasd_b64);
        $seed = self::CalSeed($pasd_b64);
        $ciphertext = Swap::undo($seed, $data);
        $ciphertext_len = strlen($ciphertext);
        $plaintext_b64 = '';
        $pasd_i = 0;

        for($i=0; $i < $ciphertext_len; $i+=2)
        {
            //plaintext_b64 += String.fromCharCode(ciphertext.charCodeAt(i) ^ pasd_b64.charCodeAt(pasd_i));
            $hex1 = $ciphertext[$i];
            $hex2 = $ciphertext[$i + 1];
            $ascii = hexdec("$hex1$hex2") ^ ord($pasd_b64[$pasd_i]);
            $plaintext_b64 .= chr($ascii);
            $pasd_i++;
            if($pasd_i == $pasd_len)
            {
                $pasd_i = 0;
            }
        }
        return urldecode($plaintext_b64);
    }
}


//$data = '现22在';
////
////$s1 = Swap::doit(120, $data);
////echo "$s1 \n";
////
////$s2 = Swap::undo(120, $s1);
////echo "$s2 \n";
////
////echo "-----------------------------------------------------------------------\n";
////
//$s1 = Crypt::encode('12345678', $data);
//LogDebug($s1);
//
////$s1 = '17770112100170007102732703011d11201c474a';
//$s2 = Crypt::decode('12345678', $s1);
//LogDebug($s2);

?>
