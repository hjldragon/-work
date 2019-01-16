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
    static private $module = "alipay_mart"; // 支付端
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
        $path = Util::EmptyToDefault($cfg["log.path"], "/log/alipay.jzzwlcm.com/log.txt");
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
            "webserver_url" => Util::EmptyToDefault($cfg['orderingsrv.webserver_url'], "http://srv.xinchihuo.com:13010/webserver")
        ];

        $this->alipay = (object)[
            "appid" => Util::EmptyToDefault($cfg['alipay.appid'], "2018051060155264"),
            "publickey" => Util::EmptyToDefault($cfg['alipay.publickey'], "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmiW7jEZngJ5rKkVdXXf0W7tGzXv/XjiniebHL/tJFbz6fpIBWErCvxrhNcgoTeYzzc0Ug7NXuiJaZCjjDdx33dBHBje2JZU3DK56nSVa9p2JXPOtjH3jZr91Na/dXPQ84NTF+ygN7d6KKSfFeW+lYQid0Dpz8LtdSHbpu7jDosA1EVegNwB9rFLhj0JpLDbptGGwdf0U+7M2c1TLQQqAdjicYwBgvXzPc1ZjoYLuqhp8veqYoSBXM6KpDEZ0VQwwrVGCMAnmF/pfzr/ZNf8e9lgmtN5Me9MnffnGWZCccl4UHH7WBelypHROOm2nCnmwojmL2dy9STc9sKt2yEdvawIDAQAB"),
            "privatekey" => Util::EmptyToDefault($cfg['alipay.privatekey'], "MIIEowIBAAKCAQEAoB4p77jbpyFuWRyuIIpleYmvuoJ7S1rf393KyoLCpdl+9hr/2/yhir/etuUNaANREMJrYCM4AQWEhBZVbCNDzps6OMhT3kFHRhNw7PpSVELAhK3Vlm9QQPsTgz3wjKJ7To0PKIns98F+sHpJs8tw31Fs+IgrnGJ8nqrrpsOa0ly8862xWuSYzss/E/Aw2cq0WEbSlSKuqQw02Te2G8JT5/Rq+yvaikqmnggeK/gzwzjBYaPcp9XaeNIcexpLRA/m6ZtnqpKy1P3NUIPTINADZ0JKyTLd07W6k5UiIkYPI9z5pDkkyM0I905VFgDQd+/NU38kJOXyxO0i12DAUc+P/wIDAQABAoIBAF9AcnwsgWcUaTvT5vZuvl0l/oc6ljRG1EWuALSmQzs8ft3TRABPcp5C9C/jZS1KQ9BviEcBzJA3AxU84AZbmDvxdhpPei0OsmLKr/QaGh4MM9D6RMSgZaKyenIpx88x3Z7mJW1dV74vurkrlmrCDNyc6PGUuDTpFe+iCFwFozWeitiL1/yFjn6JBbMIK15M9AG2z+DzPligIAmhCXGf2Q/FIrH0m8CFsD/NkF6JDyqAYQPngWHacYIjwm4ySxVtz3W17QeTaPAwU2AjAg3/iOVFlYRBKbS/l/Y0bH3yu1O3Zzy4/RI/tt99FWOdhEu7E8042j3csqcy7ZU1kiB3BrkCgYEAzEELMnG/6NatXOjXAaUiN1JteASk6fRcqVIEK8bB/m7QHP3r11GybGqzalyzdmH2kU5xGD/y1yoH/3c8el1gklZNHVXlQPyiPMN/Ajw8pfVyc3LBX+F/Sr1pDHlixx7NPRUD75UN2tKnSj/RHbMi9hDDYcM/C2iZR/1Avja3kxUCgYEAyK6mqxzSsamnGQQnkYoVYAqarmJ7AITd7Moml1XkdJmVph8VizHp8JUkwRKpgGAZa72zp7IUl7Vs6gqQ3tEg4cliHZ4a//mj9fvf/155l9BI1vAs6fSlksA7UFcYvFazsnkGgnxfE0C8oGR6fSxNWlJnIu8fhD5ACF5h2/6uK8MCgYB4BWh89dZdDXjFdZSRxOU8ONVWhvQY/ZxNaoJE7wAjcB4eSx8AYWNEzdYrk9looNvwOnW+WCVsRah5dyk0hhYvJ0MdD9Isw8bZic7iE19X7ciZCj+TBcB0vKQzzkg61WComea8K4v+n3Xp7WIF37OVf/gvRDmRjJ81bB1VKHXqmQKBgQC4X5VqsiF07qxBlIqR8S4wPa3dN0dQs8F80kDgWx54bb6KkBZn+yS2TyAkbnPiKOhSD0imcjrvItT8tVZiD/rI1g9ZrK+bq4yS66ThZrgJ3Wd+acyp6YXRLOGRr2NfbbzM5NoxOfjTpG9oYMgUhoZOzFRLSSxIgDSuO6Bm0TbNpwKBgBO/N7VA55eGBmpYBkWCTzHbABNz4TpI/BzbSE1ncG/XJ4E382PH+GYKarewxSqGdxQla0nwxQunSDz9tHYEzxTbcVaRxyjKSZ5sEYmCdkn3Xb9unRgKYF3hPAdrzBeEK3Pvqpg3sczsvyi8rKweop9Ll4KFye4JftgVyGV5chwm"),
            "alipay_notify_url" => Util::EmptyToDefault($cfg['alipay.alipay_notify_url'], "http://alipay.xinchihuo.com.cn/notify.php"),
            "alipay_record_notify" => Util::EmptyToDefault($cfg['alipay.alipay_record_notify'], "http://alipay.xinchihuo.com.cn/record_notify.php")
        ];

        static $publickey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmiW7jEZngJ5rKkVdXXf0W7tGzXv/XjiniebHL/tJFbz6fpIBWErCvxrhNcgoTeYzzc0Ug7NXuiJaZCjjDdx33dBHBje2JZU3DK56nSVa9p2JXPOtjH3jZr91Na/dXPQ84NTF+ygN7d6KKSfFeW+lYQid0Dpz8LtdSHbpu7jDosA1EVegNwB9rFLhj0JpLDbptGGwdf0U+7M2c1TLQQqAdjicYwBgvXzPc1ZjoYLuqhp8veqYoSBXM6KpDEZ0VQwwrVGCMAnmF/pfzr/ZNf8e9lgmtN5Me9MnffnGWZCccl4UHH7WBelypHROOm2nCnmwojmL2dy9STc9sKt2yEdvawIDAQAB";

        static $privatekey = "MIIEpQIBAAKCAQEAshhMn+ZzOSz9gd01Uy8gbehx1FU5muM/JcBAdVMEXNcuOUEdqKMLcxR1EMCzg1rMLYwbjdGWVxqGM03pM9Qgh5+0NjSqfGKM0cGUtQn01SdUZLN9v0HaOhNn+OdE0Yen0gKuxxTdnM9RQYVjag8G/OIaFBd8xn/6GVfgkuAP38VOduY2FhC/aOu3OX4na0YzynsQY5cykUg8O5pF+1IIeCQqbPjxCYG1Zmn6wWWU1lcE99o4++cQZYHcZYJSVzCIQVqR1UR8wrdLzORa3NsUuql1gARI8HSB2t3C5BEQRE4pJOKZIAwsrFk2ILAh55WP1XpvzFdXlIhEAWKeXy9teQIDAQABAoIBAE/WKstnoHGaJI7J3nTK5IvArv6Tsf4oLw+6TmsJ6yO/Zr5N3YDSjnhpTE1VRWP2GR+19kguE2o6vrxTCd6tEmQaCMxf8alO+HezgvlhXDsNpXx7JjzNxYlRV8Ox0Rzo5id0AmvGg84e9fscU8Ogtu44YZ5uJQZLKo+f4FGXViZmal/xkUqsA6trNKbWElyKGyHNo0wsuYdK8uWHcdF/mR6s7DoecTNfm+GYMC5JsHawmJtwH5H2/pHdjt1WIj+3jyzrF0x8/gbSIPP/HgO8Oy/UH6u9lz9GgY4VN8WWuNAXZZwIu1MfblkpnmCCtV3osEyQG6zUGlcKo0cboq834fECgYEA2IBeOxxhnqUkaKCqROVlGi5pkoRTK2P8eUkZcIdXvCdU+g/ZRf91KUcS2R0/TUiK0fcfoyeRpJvUOj0ppTRj/UMqBn5G/nWvb9mZXe3LFINU8EaSKAt4567fPC5UmZ5xa7NwvbSGQuyVFB5DAQQdz8iHhLrEH0A+oU1b2VV0ZP0CgYEA0pYqsrXhTGzlL4p8wTj6DNy+U8KqypcjHn9/LNfirbcQdtKLNbyn0I7gDUXrrpcWAHIUP86T5Mdiq/hcKlgwhmNPU/NPw4G9Ekd78Qg+M5vrIepboTsp3WJeqDc+Y9qjrTJxEdsWObNthcn8orVsj1svi8+bBHAX4KR5JcmecS0CgYEAy75Kb6Bu8eiB0gDZHAsxUSk6awsiZeQrlkxHp5GG7GerXcUKeQhGTvk28KHIj8dj9OJ2oIk9U8beO4yRo4ohSxco9oFh+FfyKPWDklSVWBGcHp6bLxpUxtvX83+6V/M2099DixPXgiW4yf0Mm2kvUPprJmrI5IwGJLBzbp0v81UCgYEAyrRZrWR91f8MLVL0+1lI+JCTdDTyCcipuXWoiRXV0LliU74e2j8amhqteJlRX+F3AD7PVwwWOCXkyPxsWyZAr1twxLJ0mRCNWscMetsgZLU1BJR+VxiIF93Ul791gtC2tMTVKgblwe0BA8OGN2jDcsqnXcEcyozLryUiqEkHBpECgYEAuBSUxVEAsd5zuILo4/DrCoNAqmB/sQcfnwYvEzifb3WDsvzrGK0kGACxSacgHa5XSY82cz970LDCwcomiSOngY4syUbTa/jfFcMb71Df2iYIeQfzUycievkTRXYGOT/P+ZJU8WLPDRzG8IVEEpR146/eOIzd+g4i3iG35m+0qKA=";
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
