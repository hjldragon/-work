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
require_once ("class.phpmailer.php");
require_once ("class.smtp.php");
require_once ("alidayu/TopSdk.php");

class Cfg
{
    static private $instance = null;
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
        $ret = $dao->QueryByKeyPrefix("");
        foreach($ret as $item)
        {
            $cfg[$item->key] = $item->value;
        }
        //LogDebug($cfg);
         //error_log(json_encode($cfg));

        // 日志配置
        // $path = Util::EmptyToDefault($cfg['log.path'], "/home/log/ordering/log.txt");
        $path = "/log/ordering/www.ob.com/log.txt";
        if(file_exists($path))
        {
            $path = realpath($path);    // 注：若文件不存在时，realpath()调用返回空
        }
        $this->log = (object)array(
        'path'  => $path,
        'level' => Util::EmptyToDefault($cfg['log.level'], "3")
    );
        $log_path = dirname($this->log->path);
        Util::DirCheck($log_path);
        Log::instance()->SetFile($this->log->path);
        Log::instance()->SetLevel($this->log->level);


        // 数据根目录
        $this->data_basedir = "/data/ordering";
        Util::DirCheck($this->data_basedir);

        // 临时目录
        Util::DirCheck("{$this->data_basedir}/tmp");

        // img.filepath
        $this->img = (object)array(
            'filepath' => Util::EmptyToDefault($cfg['img.filepath'], "/data/ordering/imgfile")
        );
        Util::DirCheck($this->img->filepath);

        // 订餐服务
        $this->orderingsrv = (object)[
            "webserver_url" => Util::EmptyToDefault($cfg['orderingsrv.webserver_url'], "http://120.24.40.134:21121/webserver")
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
        $this->ordering_url = "http://of.jzzwlcm.com/menu.php";
        $this->menu_url = "http://www.of.com:8080/index.php";
        $this->food_url = '';
        // LogInfo("cfg:" . json_encode($this));
        return 0;
    }

    public function Save()
    {
        $db = new \DaoMongodb\Cfg;

        // log
        $db->Set("log.path", $this->log->path);
        $db->Set("log.level", $this->log->level);

        // rsa
        $db->Set("rsa.publickey", $this->rsa->publickey);
        $db->Set("rsa.privatekey", $this->rsa->privatekey);

        // img
        $db->Set("img.filepath", $this->img->filepath);

        // 订餐后台服务
        $db->Set("orderingsrv.webserver_url", $this->orderingsrv->webserver_url);

        // 数据跟目录
        $db->Set("data_basedir", $this->data_basedir);

        // 点餐页面地址
        $db->Set("ordering_url", $this->ordering_url);

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
    //邮箱发送配置
    public function GetMail($email,$url,$zi)
    {
        try {
            $mail = new PHPMailer(); //建立邮件发送类
            /*服务器相关信息*/
            $mail->IsSMTP();
            $mail->SMTPAuth  = true;//开启认证
            $mail->CharSet   = "UTF-8";//设置信息的编码类型
            $mail->Host      = "smtp.163.com"; //邮箱服务器
            $mail->Username  = "18280156916@163.com"; //服务器邮箱账号
            $mail->Password  = "sailing123"; // QQ邮箱密码
            $mail->SMTPDebug = 1;
            $mail->Port      = 25;//邮箱服务器端口号
            /*内容信息*/
            $mail->IsHTML(true);
            $mail->AddReplyTo("18280156916@163.com", "mckee");//回复地址
            $mail->From     = "18280156916@163.com"; //发件人的完整邮箱
            $mail->FromName = "赛领新吃货"; //发送邮箱
            $mail->Subject  = "新吃货邮箱绑定";//标题
            $mail->MsgHTML("这是您登录帐户时所需的$zi.邮箱连接!<a href='$url'>请点击$zi</a>");//邮件消息体
            $mail->AddAddress($email);
            $mail->WordWrap = 80; // 设置每行字符串的长度
            $mail->Send();
            return 0;
        } catch (phpmailerException $e) {
            echo "邮件发送失败：" . $e->errorMessage();
            echo $mail->ErrorInfo;
            return -1;
        }
    }
    //手机验证码发送配置
    function SendCheckCode($code,$phone)
    {
        $c = new TopClient;
        $c->appkey = "24493589";//这里是我的应用key
        $c->secretKey = "71f080699a57dab32d3d2a037b13c2ba";//密匙
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        /*
             公共回传参数，在“消息返回”中会透传回该参数；
             举例：用户可以传入自己下级的会员ID，在消息返回时，
        */
        $req->setExtend("123456");
        /*
            短信类型，传入值请填写normal
        */
        $req->setSmsType("normal");
        /*
           短信签名，传入的短信签名必须是在阿里大于“管理中心-短信签名管理”中的可用签名。
        */
        $req->setSmsFreeSignName("赛领新吃货");   //这里根据自己的做调整， 不调整会报错
        /*
           短信模板变量，传参规则{"key":"value"}，
        */
        $req->setSmsParam("{\"code\":\"$code\",\"product\":\"赛领科技\"}"); //一样， 可以调整。 这里不调整不会报错
        /*
            短信接收号码。支持单个或多个手机号码，传入号码为11位手机号码，
        */
        $req->setRecNum("$phone");
        $req->setSmsTemplateCode("SMS_105000102");
        $resp = $c->execute($req);
        if($resp->result->success)
        {
            return 0;
        }
        else
        {
            return -1;
        }
    }
};

?>
