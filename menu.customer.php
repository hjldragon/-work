<?php
/*
 * [rockyshi 2014-10-04]
 * 用户信息表
 *
 */
require_once("current_dir_env.php");

if(!$_REQUEST['openid'] && PageUtil::IsWeixin())   // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
{
    $url = "http://wx.jzzwlcm.com/wx_openid.php";
    header("Location: $url");
    exit();
}
require_once("mgo_customer.php");
require_once("redis_id.php");
require_once("cache.php");

Log::instance()->SetLevel(4);
Log::instance()->SetFile("/home/log/ordering/log2.txt");

// Permission::PageCheck();
$_=$_REQUEST;
LogDebug($_);


function Register($openid)
{
    $entry = new \DaoMongodb\CustomerEntry();
    $entry->customer_id = \DaoRedis\Id::GenCustomerId();
    $entry->phone       = "";
    $entry->is_vip      = 0;
    $entry->openid      = $openid;
    $entry->property    = 0;
    $entry->ctime       = time();
    $entry->mtime       = time();
    $entry->delete      = 0;
    $mgo = new \DaoMongodb\Customer;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return null;
    }
    return $entry;
}

function GetCustomer(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $customer_id = $_['customer_id'];
    $browser = "unknown";
    $openid = "";
    $customer = null;

    LogDebug("PageUtil::IsWeixin():" . PageUtil::IsWeixin());
    if(PageUtil::IsWeixin())
    {
        $browser = "weixin";
        $openid = $_['openid'];
    }
    LogDebug("openid:[$openid], customer_id:[$customer_id]");

    if($openid)
    {
        $customer = \Cache\Customer::GetInfoByOpenid($openid);
    }
    else if($customer_id)
    {
        $customer = \Cache\Customer::Get($customer_id);
    }
    else
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    if(!$customer)
    {
        if(!$openid)
        {
            LogErr("param err: openid");
            return errcode::PARAM_ERR;
        }
        $ret = Register($openid);
        if(null == $ret)
        {
            LogErr("Register err");
            return errcode::SYS_ERR;
        }
        $customer = $ret;
    }

    $resp = (object)array(
        'customer' => $customer,
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}


$resp = null;
$ret =  GetCustomer($resp);

$html = json_encode((object)array(
    'ret' => $ret,
    'data'  => $resp
));
// echo $html;
// $callback = $_['callback'];
// $js =<<< eof
// $callback($html);
// eof;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/main.css?1494609357" type="text/css">
<title>xxx</title>
<script type="text/javascript" src="./js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="./js/jquery.cookie.js"></script>
<script type="text/javascript" src="./js/jquery.md5.js"></script>
<script type="text/javascript" src="./js/cfg.js"></script>
<script type="text/javascript" src="./js/jquery.query.js"></script>
<script type="text/javascript" src="./js/util.js?1454301113"></script>
<script type="text/javascript" src="./js/public.js?1461295407"></script>
<script type="text/javascript" src="./js/encrypt.js?1412787720"></script>
<script type="text/javascript" src="./js/PageStore.js?1418317466"></script>
<script type="text/javascript" src="./js/toast.js?1494520361"></script>
<script type="text/javascript" src="./js/global.js?1418273744"></script>


<!-- --------------------用户信息（客人）-------------------- -->
<script type="text/javascript">
$(function() {
    var UserInfo = new function(){
        var THIS = this;
        var $doing = $("#id_user_1494696280 .ctrl .doing");
        var box = new $.FloatBox($("#id_user_1494696280"));

        function Open()
        {
            box.Open();
            ReLoad();
        }
        THIS.Open = Open;

        function ReLoad()
        {
            console.log("begin");
            LoadUser();
        }

        function LoadUser()
        {
            // $doing.html("正在加载用户数据...").show().SetPromptStyle();
            // // Util.EncSubmit("customer_get.php",
            // $.getJSON("customer_get.php?" + Util.GetRandString(3),
            //     {
            //         // 'openid' : Util.GetQueryString("openid"),
            //         // 'customer_id' : Util.GetQueryString("customer_id")
            //     },
            //     function(resp){
            //         if(resp.ret < 0)
            //         {
            //             $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
            //             return;
            //         }
            //         var customer = resp.data.customer||{};
            //         $("#id_user_1494696280 .customer_id .value").Value(customer.customer_id);
            //         $("#id_user_1494696280 .phone .value").Value(customer.phone);
            //         $doing.html("");
            //         // LoadShop();  // <<<<<<<<<<<<<<<<<<<<<<<<<<
            //     }
            // );
            var resp = $.Json.toObj("<?=$html?>");
            var customer = resp.data.customer||{};
            $("#id_user_1494696280 .customer_id .value").Value(customer.customer_id);
            $("#id_user_1494696280 .phone .value").Value(customer.phone);
            $doing.html("");
        }

        function LoadShop()
        {
            $doing.html("正在加载店铺数据...").show().SetPromptStyle();
            Util.EncSubmit("shop_get.php",
                {
                    shopinfo : true,
                    shop_id  : Util.GetQueryString("shop_id"),
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        return;
                    }
                    var shop = resp.data.info||{};
                    $("#id_user_1494696280 .shop_name .value").Value(shop.shop_name);
                    $doing.html("");
                }
            );
        }

        function Init()
        {
            ReLoad();
        }

        $("#id_user_1494696280 .ctrl .logout").click(function(){
            ReLoad();
        })
        $("#id_user_1494696280 .ctrl .close").click(function(){
            box.Close();
        })

        Init();
    }//end of var UserInfo = new function(...
    window.UserInfo = UserInfo;
});
</script>
<style type="text/css">
#id_user_1494696280 {
    /*display: none;*/
    background-color: white;
    padding: 0.3rem;
    width: 80%
}
#id_user_1494696280 .line {
    margin-top: 0.8rem;
    white-space: nowrap;
}
#id_user_1494696280 .title {
    float: left;
    width: 25%;
    text-align: right;
    margin-right: 1rem;
}
#id_user_1494696280 .value {
    width: 70%;
    height: 2rem;
    font-size: 1.8rem;
    border: 1px solid #DEDEDE;
    padding: 0.3rem;
}
#id_user_1494696280 .ctrl {
    text-align: right;
    margin: 1rem 1rem 0.3rem 0;
}
</style>
<div id="id_user_1494696280">
<fieldset>
<legend>我的信息</legend>
    <div class="shop_name line">
        <div class="title">店铺名称 :</div>
        <input type="text" class="value noempty" readonly="readonly" />
    </div>
    <div class="customer_id line">
        <div class="title">用户ID :</div>
        <input type="text" class="value noempty" readonly="readonly" />
    </div>
    <div class="phone line">
        <div class="title">手机 :</div>
        <input type="text" class="value noempty" readonly="readonly" />
    </div>

    <div class="ctrl">
        <span class="doing align_left">&nbsp;</span>
        <input type="button" class="logout hide" value="注销" />
        <input type="button" class="close" value="关闭" />
    </div>
</fieldset>
</div><!-- id="id_user_1494696280" -->
