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
    static private $module = "alipay"; // 支付宝支付端
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
            "webserver_url" => Util::EmptyToDefault($cfg['orderingsrv.webserver_url'], "http://srv.xinchihuo.com.cn:13010/webserver")
        ];

        $this->alipay = (object)[
            "appid" => Util::EmptyToDefault($cfg['alipay.appid'], "2018080960970578"),
            "publickey" => Util::EmptyToDefault($cfg['alipay.publickey'], "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmiW7jEZngJ5rKkVdXXf0W7tGzXv/XjiniebHL/tJFbz6fpIBWErCvxrhNcgoTeYzzc0Ug7NXuiJaZCjjDdx33dBHBje2JZU3DK56nSVa9p2JXPOtjH3jZr91Na/dXPQ84NTF+ygN7d6KKSfFeW+lYQid0Dpz8LtdSHbpu7jDosA1EVegNwB9rFLhj0JpLDbptGGwdf0U+7M2c1TLQQqAdjicYwBgvXzPc1ZjoYLuqhp8veqYoSBXM6KpDEZ0VQwwrVGCMAnmF/pfzr/ZNf8e9lgmtN5Me9MnffnGWZCccl4UHH7WBelypHROOm2nCnmwojmL2dy9STc9sKt2yEdvawIDAQAB"),
            "privatekey" => Util::EmptyToDefault($cfg['alipay.privatekey'], "MIIEpAIBAAKCAQEA0l9mv12b5+t+DllJqN4M/hsEXTphHwxI+4LzoAm+KdROH7iC2CU4NBsABIH41eRDdfj8Ksxfh58L4H/hby8ncHYBwD4KtXKvLUpoqS1v50OhfifFj9ZHfmW7c4kvDcCsHVs7ls5a/4HNjbrboC2v1fQWn1AC+mG72oo6T8E1k8rl3ZOY5PKLoDjLgSgccRGL09QdkgaH/SVVCNBjV6xVvn6QFFxYTn86V3q/DQThuVkALwO8No15Bb+oGkHywx/3emxYPdOj9hTKl5PnVjQzLM2S0ftbwzLJq614NfI2t0zypeSHsTog3LbK39SNOZQbgONpYC78tu/rHJPdKxkmXQIDAQABAoIBAQCID8HxlUanuky+VQtqF6vJkYnCDmcQYK63VRvi70o53aFlrL2btH7kXG4nfDSHimoDXDUkLSjAG6Lf4XXZpmLYvsolHztn3bPlFBIbEVMl/TzuziReAlYdLwkNEbYILAxltEMXJ4H9bWOX/jncIJBwTb1v7pzuedW449wRB0JywgSrkkbh6EiY5oFse9K3xpwG4CdAY6dXjckxtreWbHAQbcpEPXRg9Qiv8osZmZe0UHsa3MnnKusdjF8uxVBvzkEC+AL/ySQP4mBLJ0J39q5acEgK7howd2Dms4m84ZDCFHlKLb0oVICClrxZY1P2jBmtOIHE+WH8QK1rPK/nwFWhAoGBAOwYLegSnSiGXG+qfGMMSeZY2L8+4+fDNFiq/+Pwh1bw95z/GqYoOsmqvrGvzOUS+BactYj06rxXhX8VKKkyJsnPcOSblrCbWqz04STFa5V3xvxSbzHlNJ4gTYNEAfe78q7yDwG/Uvx0b9DL7zYQJMFh0Wc/oiKpQi8d0fvJ3FD5AoGBAOQcDCnE783ix3QqhFOlrFrN02py7yuTlIPkTGj6ITrOlDPF2xkB2JyKVnXDehBvqKXobj3ctxtatXXFJ5gJtm4SnYlK/UaxzA7tCtHKPk69IhCqbX5pcxUL5pCCnvk1aOVqHuiBKfotkJ+8oDJ43HfJL7FkoKTMbdHDKHZAxf2FAoGAKbZ/7d/UCoPqswQBJBLYv6z05p068QjJJ91WhrRJhS/f38omsDKtaFIVjRlNby+xy3T2kfMLwikIehKH85Tby44uGQXwUtWv6Jz/ZlrHDkpySsRZxwwDBukYKNgMLPP1BbnYsutVwyrjUpWldvzVMaGRuNdCUzNqcR6oqf7ZQSkCgYAfd+MRrBJs87kSRH9GztctG7HrwHlQKKbXXxpPbRpCc5csYUIte/y287qjuljPhafdY2g57oXwJx5bZdxldAmPu8+xzf+MziBZesgAUwcvc5YMeIZsZv2yTJb9HpfxxzV2WKJ07sSst7Z44tuyusunVsDWlww2T+Fii6q8TYlCxQKBgQCXZnMF1vOeTipuQq0ksbLcDMgs+YiASW2VNWATF53xScGFZJHFxEqa1V8ST1NNRQ3ZAYDTuX+OuJKIm5sy0A5zO59AZVSZj4B/NjE0VQZrGyE6CjyxQhyl1Yh/75X9nYFnRP5EsXynUq99Np4+ZtoGTAvqILOfu1BYwT31afyy6Q=="),
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
