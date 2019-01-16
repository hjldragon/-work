<?php
/*
 * 配置管理
 * [rockyshi 2014-08-13 19:05:37]
 *
 */
require_once('/www/public.sailing.com/php/cfg.inc');
require_once("const.php");
require_once("dbcfg.php");
require_once("mgo_cfg.php");
require_once("util.php");


class Cfg extends \Pub\Cfg
{
    public $db   = null;
    public $log  = null;
    public $rsa  = null;
    public $img  = null;
    public $orderingsrv = null;
    public $data_basedir = null;

    protected function ModuleName()
    {
        return "shop";
    }

    protected function Init()
    {
        // 先调用公共初始化函数
        $ret = parent::Init();
        $cfg = $this->cfg;

        // 点餐页面地址
        $this->menu_url       = Util::EmptyToDefault($cfg["menu_url"], "http://customer.xinchihuo.com.cn/index.php");
        $this->food_url       = Util::EmptyToDefault($cfg["food_url"], "http://customer.xinchihuo.com.cn/static/share/index.html");
        //$this->food_url     = Util::EmptyToDefault($cfg["food_url"], "http://customer.xinchihuo.com.cn/#/shareFoodDetail");
        $this->login_url      = Util::EmptyToDefault($cfg["login_url"], "http://wx.xinchihuo.com.cn/wx_login.php");
        $this->jump_url       = Util::EmptyToDefault($cfg["jump_url"], "http://wx.xinchihuo.com.cn/jumpto.php");
        $this->binding_url    = Util::EmptyToDefault($cfg["binding_url"], "http://wx.xinchihuo.com.cn/wx_binding.php");
        $this->api_domain     = Util::EmptyToDefault($cfg["api_domain"], "http://api.xinchihuo.com.cn");
        // 店铺地址
        $this->shop_url_addr  = Util::EmptyToDefault($cfg["shop_url_addr"], "http://shop.xinchihuo.com.cn");
        // 主域名
        $this->main_domain  = Util::EmptyToDefault($cfg["main_domain"], "http://shop.xinchihuo.com.cn");
        $this->vendor_url   = Util::EmptyToDefault($cfg["vendor_url"], "http://vendor.xinchihuo.com/php/index.php");
    }

    // 是管理员返回true
    public function IsAdmin($username)
    {
        return $username == "admin"; // 暂时只简单处理
    }

    // 取店铺餐位二维码图片路径
    public function GetSeatQrcodePath($shop_id, $seat_id, $fix=null)
    {
        $path = "{$this->data_basedir}/shoper/$shop_id/img/seat_qrcode_img/{$shop_id}_{$seat_id}{$fix}.png";
        Util::DirCheck(dirname($path));
        return $path;
        // /data/ordering/shoper/3/img/seat_qrcode_img/
    }

    // 取店铺餐位二维码内容
    public function GetSeatQrcodeContect($seat_id)
    {
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
        return "{$this->food_url}?food_id={$food_id}";
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

    // Rocky 2018-03-02 12:10:04
    public function GetApiDomain()
    {
        return $this->api_domain;
    }

    // Rocky 2018-03-30 15:33:58
    public function GetShopUrlAddr()
    {
        return $this->shop_url_addr;
    }

    // 主域名（目前测试环境:jzzwlcm.com，正式环境:xinchihuo.com.cn） --Rocky 2018-04-24 11:07:18
    public function GetMainDomain()
    {
        return $this->main_domain;
    }

    // 取售货机二维码图片路径
    public function GetVendorQrcodePath($vendor_id)
    {
        $path = "{$this->data_basedir}/shoper/$vendor_id/img/vendor_qrcode_img/{$vendor_id}.png";
        LogDebug($path);
        Util::DirCheck(dirname($path));
        return $path;
        // /data/ordering/shoper/3/img/seat_qrcode_img/
    }

    // 取售货机二维码内容
    public function GetVendorQrcodeContect($vendor_id)
    {
        return "{$this->vendor_url}?vendor_id={$vendor_id}";
    }
};

Cfg::instance();
