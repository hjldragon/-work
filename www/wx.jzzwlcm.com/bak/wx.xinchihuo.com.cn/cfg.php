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
    static private $module = "weixin"; // 微信支付端
    public $db   = null;
    public $log  = null;
    public $rsa  = null;
    public $img  = null;
    public $data_root = null;
    public $orderingsrv = null;
    public $order_timeout_sec = null; // 订单超时时间

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
        $ret = $dao->QueryByModule(self::$module);
        foreach($ret as $item)
        {
            $cfg[$item->key] = $item->value;
        }
        // error_log(json_encode($cfg));
        //

        $this->data_root = "/data/ordering";

        // 日志配置
        $path = Util::EmptyToDefault($cfg["log.path"], "/log/wx.jzzwlcm.com/log.txt");
        if(file_exists($path))
        {
            $path = realpath($path);    // 注：若文件不存在时，realpath()调用返回空
        }
        $this->log = (object)array(
            'path'  => $path,
            'level' => Util::EmptyToDefault($cfg["log.level"], "3")
        );
        $log_path = dirname($this->log->path);
        if(!is_dir($log_path))
        {
            if(!mkdir($log_path, 0777, true))
            {
                chmod($log_path, 0777);
                $msg = "cfg err, log_path=[$log_path]";
                LogDebug($msg);
                error_log($msg);
            }
        }
        Log::instance()->SetFile($this->log->path);
        Log::instance()->SetLevel($this->log->level);

        // img.filepath
        $this->img = (object)array(
            'filepath' => Util::EmptyToDefault($cfg["img.filepath"], "/data/ordering/imgfile")
        );
        if(!is_dir($this->img->filepath))
        {
            if(!mkdir($this->img->filepath, 0777, true))
            {
                chmod($this->img->filepath, 0777);
                $msg = "cfg err, this->img->filepath=[{$this->img->filepath}]";
                LogDebug($msg);
                error_log($msg);
            }
        }

        // 订餐服务
        $this->orderingsrv = (object)[
            "webserver_url" => Util::EmptyToDefault($cfg['orderingsrv.webserver_url'], "http://srv.jzzwlcm.com:13010/webserver")
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

        // 订单超时时间
        $this->order_timeout_sec = 3600*12;

        // 主域名（即【xxx.jzzwlcm.com】中的【zzwlcm.com】
        $this->main_domain = Util::EmptyToDefault($cfg["main_domain"], "xinchihuo.com.cn");

        // LogDebug("cfg:" . json_encode($this));
        return 0;
    }

    public function Save()
    {
        $db = new \DaoMongodb\Cfg;

        // log
        $db->Set(self::$module, "log.path", $this->log->path);
        $db->Set(self::$module, "log.level", $this->log->level);

        // rsa
        $db->Set(self::$module, "rsa.publickey", $this->rsa->publickey);
        $db->Set(self::$module, "rsa.privatekey", $this->rsa->privatekey);

        // img
        $db->Set(self::$module, "img.filepath", $this->img->filepath);

        // 订餐后台服务
        $db->Set(self::$module, "orderingsrv.webserver_url", $this->orderingsrv->webserver_url);

        // 订单超时时间
        $db->Set(self::$module, "order_timeout_sec", $this->order_timeout_sec);
    }

    // 是管理员返回true
    public function IsAdmin($username)
    {
        return $username == "admin"; // 暂时只简单处理
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
        $dir = sprintf("%s/img/$type/%d", Cfg::instance()->data_root, $crc%1024);
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

    public function GetMainDomain()
    {
        return $this->main_domain;
    }
};

Cfg::instance();
?>
