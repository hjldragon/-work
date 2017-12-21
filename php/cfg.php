<?php
/*
 * 配置管理
 * [rockyshi 2014-08-13 19:05:37]
 *
 */
require_once("const.php");
require_once("dbcfg.php");
require_once("mgo_cfg.php");
require_once("util.php");


class Cfg
{
    static private $instance = null;
    static private $prefix = "shop"; // 店铺端
    public $db   = null;
    public $log  = null;
    public $rsa  = null;
    public $img  = null;
    public $orderingsrv = null;
    public $data_basedir = null;

    private function __construct()
    {
    }
    public function __destruct()
    {
    }

    public static function instance()
    {
        if(self::$instance == null)
        {
            self::$instance = new Cfg;
            $ret = self::$instance->Init();
            if($ret < 0)
            {
                LogErr("self::\$instance->Init err, ret=[$ret]");
                // return -1;
            }
        }
        return self::$instance;
    }

    private function Init()
    {
        $this->db = (object)array();
        $this->db->mongodb = $GLOBALS["db_mongodb"];
        $this->db->redis = $GLOBALS["db_redis"];

        $cfg = array();
        $dao = new \DaoMongodb\Cfg();
        $ret = $dao->QueryByKeyPrefix($prefix);
        foreach($ret as $item)
        {
            $cfg[$item->key] = $item->value;
        }
        //LogDebug($cfg);
         //error_log(json_encode($cfg));

        // 日志配置
        $path = Util::EmptyToDefault($cfg["$prefix.log.path"], "/log/shop.jzzwlcm.com/log.txt");
        if(file_exists($path))
        {
            $path = realpath($path);    // 注：若文件不存在时，realpath()调用返回空
        }
        $this->log = (object)array(
            'path'  => $path,
            'level' => Util::EmptyToDefault($cfg["$prefix.log.level"], "3")
        );
        $log_path = dirname($this->log->path);
        Util::DirCheck($log_path);
        Log::instance()->SetFile($this->log->path);
        Log::instance()->SetLevel($this->log->level);


        // 数据根目录
        $this->data_basedir = "/data/ordering";  // <<<<<<<<<<<<<<<<<<<
        Util::DirCheck($this->data_basedir);

        // 临时目录
        Util::DirCheck("{$this->data_basedir}/tmp");

        // img.filepath
        $this->img = (object)array(
            'filepath' => Util::EmptyToDefault($cfg["$prefix.img.filepath"], "/data/ordering/imgfile")
        );
        Util::DirCheck($this->img->filepath);

        // 订餐后台服
        $this->orderingsrv = (object)[
            "webserver_url" => Util::EmptyToDefault($cfg["$prefix.orderingsrv.webserver_url"], "http://120.24.40.134:13010/webserver")
        ];

        static $publickey = <<<eof
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQClnAfSpNh3EMKoMGN10MWlCmV+
8lcYU92GnvgHVlFn9rS9aZEig9Dy+9Wos13Zfszp3qfPo7NlnXP59CUKlC07zw/Z
8VPJsHQrsah2HX6nQXKlgyFcqB6q6GoRI4Vp36Vdu8XoNSiWsz7KpBY7MHgMy4uA
xsH7vYPq9U30Q0sBlwIDAQAB
-----END PUBLIC KEY-----
eof;
        static $privatekey = <<<eof
-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQClnAfSpNh3EMKoMGN10MWlCmV+8lcYU92GnvgHVlFn9rS9aZEi
g9Dy+9Wos13Zfszp3qfPo7NlnXP59CUKlC07zw/Z8VPJsHQrsah2HX6nQXKlgyFc
qB6q6GoRI4Vp36Vdu8XoNSiWsz7KpBY7MHgMy4uAxsH7vYPq9U30Q0sBlwIDAQAB
AoGABNjCKdoFM8bby4xO/N21SxU4zzRMdDvQGeaBH8XY8A/6TijOxJHTBal1IVDO
iFT7rkSD6MHDuE+ZW1FX3C1l/XQOdBKzUMJhwJwgjBW9IpHNzUlN7kSObF3eYtae
PjCQivAFMOUUSemsSiLfo80IzCIvpV0WkoNxZavUx0fq/dECQQDPq7V/ux2rIG8x
48ru3kGafqYQF0g2GHS4kGAY/D/79ZIZS9DiMxgmxxP03vFOtNfEO0oD3udT1mUg
qFkh9ULTAkEAzCZ1+Y2ISmXBlk3Zl6PTghCurzTZHmDZhKNoTFOqt0avh8Ppo/PC
n+12Mgx8x0FQ7S6jk6V3+yy06nnyD64jrQJBALirMpzBIeLY1siAjibX0XK3CKjq
azZfjPvKtwnA1o0RlLeV6cwcL2/cO+zWi7K3sd838dt7Ti4JSqg9y/Ucii0CQGDb
5qFuSzmxKbYcXZ6ateFB9P9fvZuyK8HIndWI5LhsKx/pDdMh9jdWvPtl/VW0YacG
t8l3eoOLZJLTJMvXvUkCQQCjx4yA/60HI78YIMbA3d1esuJ7nzhBv8MHXcUuvBb/
zBu0YwR8A+Zl/zTy1CZBiBAwXU2+JXyY85TuDSzALrio
-----END RSA PRIVATE KEY-----

eof;
        $this->rsa = (object)array(
            'publickey'  => $publickey,
            'privatekey' => $privatekey
        );

        // 点餐页面地址
        // $this->ordering_url = "http://of.jzzwlcm.com/menu.php";
        // $this->menu_url     = "http://192.168.5.117/www.of.com/index.php";
        // $this->food_url     = 'http://www.ob.com:8080';
        // $this->login_url    = 'http://wx.jzzwlcm.com/wx_login.php';
        // $this->jump_url     = 'http://wx.jzzwlcm.com/jumpto.php';
        // $this->binding_url  = 'http://wx.jzzwlcm.com/wx_binding.php';
        $this->ordering_url = Util::EmptyToDefault($cfg["$prefix.ordering_url"], "http://of.jzzwlcm.com/menu.php");
        $this->menu_url     = Util::EmptyToDefault($cfg["$prefix.menu_url"], "http://customer.jzzwlcm.com/index.php");
        $this->food_url     = Util::EmptyToDefault($cfg["$prefix.food_url"], "http://shop.jzzwlcm.com");
        $this->login_url    = Util::EmptyToDefault($cfg["$prefix.login_url"], "http://wx.jzzwlcm.com/wx_login.php");
        $this->jump_url     = Util::EmptyToDefault($cfg["$prefix.jump_url"], "http://wx.jzzwlcm.com/jumpto.php");
        $this->binding_url  = Util::EmptyToDefault($cfg["$prefix.binding_url"], "http://wx.jzzwlcm.com/wx_binding.php");
        // LogInfo("cfg:" . json_encode($this));
        return 0;
    }

    public function Save()
    {
        $db = new \DaoMongodb\Cfg;

        // log
        $db->Set("$prefix.log.path", $this->log->path);
        $db->Set("$prefix.log.level", $this->log->level);

        // rsa
        $db->Set("$prefix.rsa.publickey", $this->rsa->publickey);
        $db->Set("$prefix.rsa.privatekey", $this->rsa->privatekey);

        // img
        $db->Set("$prefix.img.filepath", $this->img->filepath);

        // 订餐后台服务
        $db->Set("$prefix.orderingsrv.webserver_url", $this->orderingsrv->webserver_url);

        // 数据跟目录
        $db->Set("$prefix.data_basedir", $this->data_basedir);

        // 点餐页面地址
        $db->Set("$prefix.ordering_url", $this->ordering_url);

        //
    }

    // 是管理员返回true
    public function IsAdmin($username)
    {
        return $username == "admin"; // 暂时只简单处理
    }

    // 取店铺餐位二维码图片路径
    public function GetSeatQrcodePath($shop_id, $seat_id)
    {
        $path = "{$this->data_basedir}/shoper/$shop_id/img/seat_qrcode_img/{$shop_id}_{$seat_id}.png";
        Util::DirCheck(dirname($path));
        return $path;
        // /data/ordering/shoper/3/img/seat_qrcode_img/
    }

    // 取店铺餐位二维码内容
    public function GetSeatQrcodeContect($seat_id)
    {
        //'http://www.of.com:8080/index.php?seat=199'
        //return "{$this->ordering_url}?shop={$shop_id}&seat={$seat_id}";
        return "{$this->menu_url}?seat={$seat_id}";
    }

    // 取餐品二维码图片路径
    public function GetFoodQrcodePath($shop_id, $food_id)
    {
        $path = "{$this->data_basedir}/shoper/$shop_id/img/food_qrcode_img/{$shop_id}_{$food_id}.png";
        Util::DirCheck(dirname($path));
        return $path;
        // /data/ordering/shoper/3/img/seat_qrcode_img/
    }

    // 取餐品二维码内容
    public function GetFoodQrcodeContect($food_id)
    {
        //'http://www.of.com:8080/index.php?seat=199'
        //return "{$this->ordering_url}?food={$food_id}";
        return "{$this->food_url}?food={$food_id}";
    }

    // 取登录二维码内容
    public function GetLoginQrcodeContect($token)
    {
        return "{$this->login_url}?token={$token}";

    }

    // 二维码内容
    public function GetUrlQrcodeContect($url)
    {
        return "{$this->jump_url}?url={$url}";
    }

    // 绑定微信二维码内容
    public function GetBindingQrcodeContect($userid, $token, $type)
    {
        return "{$this->binding_url}?userid={$userid}&token={$token}&type={$type}";
    }

    public function GetTmpPath($filename)
    {
        if("" == $filename)
        {
            $filename = Util::GetRandString(16);
        }
        $path = "{$this->data_basedir}/tmp/$filename";
        Util::DirCheck(dirname($path));
        return $path;
    }

    // 取字体文件
    public function GetFontFile()
    {
        $path = "{$this->data_basedir}/font/Motley2.TTF";
        Util::DirCheck(dirname($path));
        return $path;
    }

     public function GetCodeImgFullname($filename)
    {
        return self::GetImgFullname($filename, "code");
    }
    public function GetUserImgFullname($filename)
    {
        return self::GetImgFullname($filename, "user");
    }
    public function GetImgFullname($filename, $type)
    {
        $crc = crc32($filename);
        $dir = sprintf("%s/img/$type/%d", Cfg::instance()->data_basedir, $crc%1024);
        // LogDebug("[$filename] [$crc] [$dir]");
        if(!is_dir($dir))
        {
            if(!mkdir($dir, 0777, true) || !chmod($dir, 0777))
            {
                LogErr("mkdir or chmod err:[$dir]");
                return "";
            }
        }
        return "$dir/$filename";
    }
};

Cfg::instance();
?>
