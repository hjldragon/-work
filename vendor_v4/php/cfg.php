<?php
require_once("/www/public.sailing.com/php/cfg.php");
require_once("util.php");
require_once("wx_util.inc");

class Cfg extends \Pub\Cfg
{
    protected function ModuleName()
    {
        return "vendor";
    }
    protected function Init()
    {
        // 先调用公共初始化函数
        parent::Init();

        // 微信支付相关设置
        $wxpay = (object)[
            'appid' => Util::EmptyToDefault($this->cfg["wxpay.appid"], "wxaaceede0e7695fcf"),
            'secret' => Util::EmptyToDefault($this->cfg["wxpay.secret"], "eb03a81d333f75a937cb9bd3d4aa5273"),
            'mch_id' => Util::EmptyToDefault($this->cfg["wxpay.mch_id"], "1464120802"),
            'key' => Util::EmptyToDefault($this->cfg["wxpay.key"], "dada0cc3266ca659e2c0be29798eb357"),
            'notify_url' => Util::EmptyToDefault($this->cfg["wxpay.notify_url"], "http://vendor.jzzwlcm.com:8084/php/wx_notify_pay.php"),
        ];
        // 设置微信支付参数
        \Wx\Util::Init($wxpay);
    }
}
Cfg::instance();
