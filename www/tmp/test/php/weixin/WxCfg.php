<?php
/*
 * [Rocky 2018-04-03 11:48:07]
 * 微信配置（注：因之前的开发中，各处直接引用了配置，没有写到配置库中，所在这里单
 * 独出来）
 */
namespace Pub\Wx;

class Cfg
{
    const WX_URL_AUTHORIZE = "https://open.weixin.qq.com/connect/oauth2/authorize";
    const WX_URL_ACCESS_TOKEN = "https://api.weixin.qq.com/sns/oauth2/access_token";
    const WX_URL_UNIFIEDORDER = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    const WX_URL_MICROPAY = "https://api.mch.weixin.qq.com/pay/micropay";
    const WX_URL_ORDERQUERY = "https://api.mch.weixin.qq.com/pay/orderquery";
    const WX_URL_TOKEN = "https://api.weixin.qq.com/cgi-bin/token";
    const WX_URL_USERINFO = "https://api.weixin.qq.com/sns/userinfo";
    const WX_URL_TICKET = "https://api.weixin.qq.com/cgi-bin/ticket/getticket";
    const WX_URL_SEND = "https://api.weixin.qq.com/cgi-bin/message/custom/send";
    const WX_URL_REFUND = "https://api.mch.weixin.qq.com/secapi/pay/refund";
    const WX_URL_TEMPLATE = "https://api.weixin.qq.com/cgi-bin/message/template/send";

    /*
     * 各个环境中，要单独设置
     */
    // 测试环境 {{
    const APPID                    = "wxaaceede0e7695fcf";                  // 公从平台->开发,基本配置->开发者ID(AppID)
    const SECRET                   = "eb03a81d333f75a937cb9bd3d4aa5273";    // 公从平台->开发,基本配置->开发者密码(AppSecret)
    const MCH_ID                   = "1464120802";                          // 金蜘蛛（服务号）
    const KEY                      = 'dada0cc3266ca659e2c0be29798eb357';    // KEY(API密钥) (微信商户平台(pay.weixin.qq.com)-->账户设置-->API安全-->密钥设置)
    const REDIRECT_URI             = "http%3A%2F%2Fwx.jzzwlcm.com%2F";
    const WX_URL_NOTIFY_URL        = "http://wx.jzzwlcm.com/notify.php";
    const WX_URL_MART_NOTIFY       = "http://wx.jzzwlcm.com/mart_notify.php";
    const WX_URL_REFUND_NOTIFY     = "http://wx.jzzwlcm.com/refund_notify.php";
    const WX_URL_RECORD_NOTIFY     = "http://wx.jzzwlcm.com/record_notify.php";
    const WX_URL_GETCODE           = "http://wx.jzzwlcm.com/get_code.php";
    const SUB_MCH_ID               = "1467121102";//<<<<<<<<<<<<<<<<<<<<<<用于测试运营后台充值系统和商品系统的子商户号
    const TEMPLATE_ID              = "QYui0snuG4ySccvIzBA3dn4ebbYRDaSaNA7A7PRCDFk";//模版消息id
    // }}

    // // 正式环境 {{
    // const APPID = "wxe6ee6bc6898df9df";                     // 公从平台->开发,基本配置->开发者ID(AppID)
    // const SECRET = "b56ed11e0de28a711b3733da458ca814";      // 公从平台->开发,基本配置->开发者密码(AppSecret)
    // const MCH_ID = "1500688511";                            // 深圳前海赛领科技有限公司（服务号）
    // const KEY = "dc292d677e0817d3cb6602f7e1b114c0";         // KEY(API密钥) (微信商户平台(pay.weixin.qq.com)-->账户设置-->API安全-->密钥设置)
    // const REDIRECT_URI = "http%3A%2F%2Fwx.xinchihuo.com.cn%2F";
    // const WX_URL_NOTIFY_URL = "http://wx.xinchihuo.com.cn/notify.php";
    // const WX_URL_GETCODE = "http://wx.xinchihuo.com.cn/get_code.php";
    // const WX_URL_MART_NOTIFY   = "http://wx.xinchihuo.com.cn/mart_notify.php";
    // const WX_URL_REFUND_NOTIFY = "http://wx.xinchihuo.com.cn/refund_notify.php";
    // const WX_URL_RECORD_NOTIFY = "http://wx.xinchihuo.com.cn/record_notify.php";
    // const SUB_MCH_ID = "1501566571";    // 深圳赛领信息技术有限公司
    // const TEMPLATE_ID       = "6w7Sew4GxeZh_vKHQQ-Wvym8yqLZlfvmsC61lyrFGh8";//模版消息id
    // //}}
}

?>