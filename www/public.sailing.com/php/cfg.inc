<?php
/*
 * 配置管理
 * [rockyshi 2014-08-13 19:05:37]
 *
 */
namespace Pub;
require_once("dbcfg.php");
require_once("mgo_cfg.php");
require_once("util.php");


class Cfg
{
    static private $instance = null;
    protected $cfg = []; // 各配置项集合
    public $db   = null;
    public $log  = null;
    public $rsa  = null;
    public $cors = null; // 跨域设置
    public $orderingsrv = null;
    public $data_basedir = null;
    public $running_env = null; // 运行环境: 'dev',test','beta','product'

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
            self::$instance = new static;
            $ret = self::$instance->Init();
            if($ret < 0)
            {
                LogErr("self::instance->Init err, ret=[$ret]");
                error_log("self::instance->Init err, ret=[$ret]");
                exit(0);
            }
        }
        return self::$instance;
    }

    // 各模块配置名
    // 请在子类是重载ModuleName()接口，指定模块名
    protected function ModuleName()
    {
        // 请在子类中重载ModuleName()接口，指定模块名
        error_log(__FILE__ . ':' . __LINE__. ", err, ModuleName not Implementing");
        exit(0);
        return "";
    }

    protected function Init()
    {
        $this->db = (object)array();
        $this->db->mongodb = $GLOBALS["db_mongodb"];
        $this->db->redis = $GLOBALS["db_redis"];

        $dao = new \DaoMongodb\Cfg();

        // 先读入公共配置
        $ret = $dao->QueryByModule("public");
        foreach($ret as $item)
        {
            $this->cfg[$item->key] = $item->value;
        }

        // 再读入当前配置，覆盖公共设置。(注：模块配置名在各子类中指定)
        $name = $this->ModuleName();
        if(!$name)
        {
            // 请在子类中重载ModuleName()接口，指定模块名
            error_log(__FILE__ . ':' . __LINE__. ", err, ModuleName not Implementing");
            exit(0);
        }
        $ret = $dao->QueryByModule($name);
        foreach($ret as $item)
        {
            $this->cfg[$item->key] = $item->value;
        }
        //error_log(json_encode($cfg));

        // 日志配置
        $module_dirname = basename($_SERVER["DOCUMENT_ROOT"]);
        $path = \Util::EmptyToDefault($this->cfg["log.path"], "/log/$module_dirname/log.txt");
        if(file_exists($path))
        {
            $path = realpath($path);    // 注：若文件不存在时，realpath()调用返回空
        }
        $this->log = (object)array(
            'path'  => $path,
            'level' => \Util::EmptyToDefault($this->cfg["log.level"], "3")
        );
        $log_path = dirname($this->log->path);
        \Util::DirCheck($log_path);
        \Log::instance()->SetFile($this->log->path);
        \Log::instance()->SetLevel($this->log->level);

        $cors = null; // 跨域设置
        $this->cors = (object)[
            'allow' => \Util::EmptyToDefault($this->cfg["cors.allow"], 1), // 1:允许跨域设置, 0:禁用跨域设置
        ];
        if($this->cors->allow)
        {
            // 指定允许其他域名访问
            header('Access-Control-Allow-Origin: *');
            // 响应类型
            header('Access-Control-Allow-Methods: POST');
            // 响应头设置
            header('Access-Control-Allow-Headers: x-requested-with,content-type');
        }

        // 数据根目录
        $this->data_basedir = "/data/ordering";
        \Util::DirCheck($this->data_basedir);

        // 临时目录
        \Util::DirCheck("{$this->data_basedir}/tmp");

        // img.filepath
        $this->img = (object)array(
            'filepath' => \Util::EmptyToDefault($this->cfg["img.filepath"], "/data/ordering/imgfile")
        );
        \Util::DirCheck($this->img->filepath);

        // 订餐后台服
        $this->orderingsrv = (object)[
            'webserver_url' => \Util::EmptyToDefault($this->cfg["orderingsrv.webserver_url"], "http://srv.xinchihuo.com.cn:13010/webserver"),
            'websocket_url' => \Util::EmptyToDefault($this->cfg["orderingsrv.websocket_url"], "ws://srv.xinchihuo.com:13010/websocket"),
        ];

        // 版本升级
        $this->apk = (object)[
            "fileapk" => \Util::EmptyToDefault($this->cfg['apk.fileapk'], "/www/dl.sailing.com")
        ];

        $this->invoice = (object)[
            "appid"        => \Util::EmptyToDefault($this->cfg['invoice.appid'], "wxe6ee6bc6898df9df"),
            "appkey"       => \Util::EmptyToDefault($this->cfg['invoice.appkey'], "ofVDMK1MOKWUEum5Z6sCAWeUtQ9ynR8m"),
            "appsecret"    => \Util::EmptyToDefault($this->cfg['invoice.appsecret'], "GPL83NTjsRCU79FmsSEwhftRWltCZN40Q5kuCrDYBkZU1ave"),
            "taxpayer_num" => \Util::EmptyToDefault($this->cfg['invoice.taxpayer_num'], "91440300MA5ENTGUOH"),
            "callback_url" => \Util::EmptyToDefault($this->cfg['invoice.callback_url'], "http://mart.xinchihuo.com/php/invoice_notify.php"),
            "card_id"      => \Util::EmptyToDefault($this->cfg['invoice.card_id'], "pF_Yo0SzdbFoqSBFDZ4BlvugB488"),
            "tax_rate"     => \Util::EmptyToDefault($this->cfg['invoice.tax_rate'], "0.03")
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

        // 主域名
        // $this->main_domain    = \Util::EmptyToDefault($this->cfg["main_domain"], "xinchihuo.com.cn"); // 用下面primary_domain这个字段
        $this->primary_domain    = \Util::EmptyToDefault($this->cfg["primary_domain"], "xinchihuo.com.cn");

        // 运行环境
        $this->running_env  = \Util::EmptyToDefault($this->cfg["running_env"], "product");

        // LogInfo("cfg:" . json_encode($this, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        return 0;
    }

    static public function GetTmpPath($filename)
    {
        if("" == $filename)
        {
            $filename = Util::GetRandString(16);
        }
        $path = self::instance()->data_basedir . "/tmp/$filename";
        \Util::DirCheck(dirname($path));
        return $path;
    }

    // 取字体文件
    static public function GetFontFile($type=null)
    {
        if(null == $type)
        {
            $path = self::instance()->data_basedir . "/font/Motley2.TTF";
        }
        else
        {
            $path = self::instance()->data_basedir . "/font/msyhbd.ttf";
        }
        \Util::DirCheck(dirname($path));
        return $path;
    }

    static public function GetPrimaryDomain()
    {
        return self::instance()->primary_domain;
    }

    static public function GetRunningEnv()
    {
        return self::instance()->running_env;
    }

    static public function GetWebSocketUrl()
    {
        return self::instance()->orderingsrv->websocket_url;
    }

    static public function GetWebServerUrl()
    {
        return self::instance()->orderingsrv->webserver_url;
    }
};
