<?php
require_once("current_dir_env.inc");
// require_once("mgo_login.php");
require_once("util.php");
require_once("wx_util.inc");
require_once("mgo_user.php");
require_once("mgo_weixin.php");
require_once("mgo_alipay.php");
require_once("redis_id.php");
require_once("cache.php");
require_once("redis_login.php");
require_once("mgo_customer.php");
use \Typedef as T;

if(PageUtil::IsWeixin())
{
    $userid = GetWeixinUser($openid);
}
elseif(PageUtil::IsAlipay())
{
    //$userid = GetAlipayUser($alipay_id);
}
if(!$userid)
{
	LogErr("Userid err");
	 \Pub\PageUtil::TemplateOut("../template/error.tpl", [
            "系统忙..."// 模板文件中的$param参数
        ]);
    exit(0);
}
Index($userid, $openid, $alipay_id);

function Index($userid, $openid=null, $alipay_id=null)
{
    $vendor_id  = $_REQUEST["vendor"];
    $msg = "";
    if(!$vendor_id)
    {
        LogErr("param err, vendor_id empty");
        \Pub\PageUtil::TemplateOut("../template/error.tpl", [
            "系统忙..."// 模板文件中的$param参数
        ]);
        exit(0);
    }
    //餐桌信息
    $vendor = \Cache\Vendor::Get($vendor_id);
    //用户信息
    $user = \Cache\UsernInfo::Get($userid);
    //客户信息
    $customer = \Cache\Customer::GetInfoByUseridShopid($userid,$vendor->shop_id);
    $day            = date('Ymd',time());
    $platform_id    = 1;
    if(!$customer->customer_id)
    {
        $info->userid        = $userid;
        $info->shop_id       = $vendor->shop_id;
        $info->phone         = $user->phone;
        $info->usernick      = $user->usernick;
        $info->sex           = $user->sex;

        $customerinfo = CustomerSave($info);
        if(0 != $customerinfo)
        {
            LogErr("CustomerSave err");
            \Pub\PageUtil::TemplateOut("../template/error.tpl", [
                "系统忙..."// 模板文件中的$param参数
            ]);
            exit(0);
        }
        $customer_id = $info->customer_id;
    }
    else
    {
        $customer_id = $customer->customer_id;
    }
    $token = $_COOKIE["token"];

    if(!$token)
    {
    	$token = 'T3' . Util::GetRandString(14);
    	setcookie("token", $token);
    }
    $redis = new \DaoRedis\Login();
    $redisinfo  = new \DaoRedis\LoginEntry();

    // 注：$info->key字段在终端初次提交时设置
	$redisinfo->token     = $token;
	$redisinfo->login     = 1;
	$redisinfo->userid    = $userid;
	$redisinfo->openid    = $openid;
	$redisinfo->alipay_id = $alipay_id;
    LogDebug($redisinfo);
    $ret = $redis->Save($redisinfo);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
        'vendor_id'   => $vendor_id,
        'shop_id'     => $vendor->shop_id,
        'customer_id' => $customer_id,
        'userid'      => $userid,
        'token'       => $token
    );

    $url = "index.html?".http_build_query($resp);

    header("Location: $url");
    exit();
}

function GetWeixinUser(&$openid=null)
{
	$req = \Wx\Util::GetAccessToken();
    LogDebug($req);
	$openid = $req->openid;
	if(!$openid)
    {
	    LogErr("Openid err");
	    \Pub\PageUtil::TemplateOut("../template/error.tpl", [
            "系统忙..."// 模板文件中的$param参数
        ]);
	    exit(0);
	}
	$weixin = \Cache\Weixin::Get($openid,1);
	if(!$weixin->userid)
    {
        $user = \Pub\Wx\Util::GetUserInfo($req->access_token,$openid);
        if($openid != $user->openid)
        {
            LogErr("User err");
            \Pub\PageUtil::TemplateOut("../template/error.tpl", [
                "系统忙..."// 模板文件中的$param参数
            ]);
		    exit(0);
        }
        if($user->headimgurl)
        {
            $filemd5 = md5($user->headimgurl);
            $destfile = Cfg::GetUserImgFullname($filemd5);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch,CURLOPT_URL,$user->headimgurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $file_content = curl_exec($ch);
            curl_close($ch);
            $downloaded_file = fopen($destfile, 'w');
            fwrite($downloaded_file, $file_content);
            fclose($downloaded_file);
        }
        if($filemd5)
        {
            $info['user_avater'] = $filemd5;
        }
        $info['usernick'] = $user->nickname;
        $info['sex'] = $user->sex;
        $userinfo = UserSave($info, $id);
        if(0 != $userinfo->ret){
            LogErr("UserSave err");
            \Pub\PageUtil::TemplateOut("../template/error.tpl", [
                "系统忙..."// 模板文件中的$param参数
            ]);
            exit(0);
        }
        $user->id     = $weixin->id;
        $user->userid = $id;
        $user->src    = 1;
        $ret = WeixinSave($user);
        //LogDebug($ret);
        if(0 != $ret){
            LogErr("WeixinSave err");
            \Pub\PageUtil::TemplateOut("../template/error.tpl", [
                "系统忙..."// 模板文件中的$param参数
            ]);
            exit(0);
        }
        $userid = $user->userid;
    }
    else
    {
        $userid = $weixin->userid;
    }
    return $userid;
}

function GetAlipayUser(&$alipay_id=null)
{
	$req     = GetToken();//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<方法未实现
	$alipay_id = $req->user_id;
	if(!$alipay_id)
    {
	    LogErr("alipay_id err");
	   \Pub\PageUtil::TemplateOut("../template/error.tpl", [
            "系统忙..."// 模板文件中的$param参数
        ]);
	    exit(0);
	}
	$access_token = $req->access_token;
	$mgo = new \DaoMongodb\Alipay;
	$alipay_user = $mgo->QueryByAlipayid($alipay_id, 1);
	//LogDebug($alipay_user);
	if(!$alipay_user->userid)
    {
	    $user = GetUserInfo($access_token);
	    //LogDebug($user);
	    if($alipay_user->alipay_id && $alipay_user->alipay_id != $user->user_id){
	        LogErr("User err");
	       \Pub\PageUtil::TemplateOut("../template/error.tpl", [
                "系统忙..."// 模板文件中的$param参数
            ]);
		    exit(0);
	    }
	    if($user->avatar)
        {
	        $filemd5 = md5($user->avatar);
	        $destfile = Cfg::GetUserImgFullname($filemd5);
	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_POST, 0);
	        curl_setopt($ch,CURLOPT_URL,$user->avatar);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        $file_content = curl_exec($ch);
	        curl_close($ch);
	        $downloaded_file = fopen($destfile, 'w');
	        fwrite($downloaded_file, $file_content);
	        fclose($downloaded_file);
	    }
	    if($filemd5)
        {
	        $info['user_avater'] = $filemd5;
	    }
	    $info['usernick'] = $user->nick_name;
	    $sex = SEX::$sex[$user->gender];
	    $info['sex'] = $sex;
	    $userinfo = UserSave($info);
	    if(0 != $userinfo->ret){
	    	LogErr("UserSave err");
	        \Pub\PageUtil::TemplateOut("../template/error.tpl", [
                "系统忙..."// 模板文件中的$param参数
            ]);
		    exit(0);
	    }
	    $user->id     = $alipay_user->id;
		$user->userid = $userinfo->data->userid;
	    $user->src    = 1;
	    $user->sex    = $sex;
		$ret = AlipaySave($user);
	    LogDebug($ret);
		if(0 != $ret){
	    	LogErr("AlipaySave err");
	       \Pub\PageUtil::TemplateOut("../template/error.tpl", [
                "系统忙..."// 模板文件中的$param参数
            ]);
		    exit(0);
	    }
	    $userid = $user->userid;
	 }
     else
     {
	 	$userid = $alipay_user->userid;
	 }
	 return $userid;
}


function SaveUserinfo($info, &$userid=null)
{
    if(!$info)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid        = $info['userid'];
    $usernick      = $info['usernick'];
    $user_avater   = $info['user_avater'];
    $sex           = $info['sex'];

    $mgo = new \DaoMongodb\User;
    $entry = new \DaoMongodb\UserEntry;

    if(!$userid){
        $userid = \DaoRedis\Id::GenUserId();
    }

    $entry->userid      = $userid;
    $entry->usernick    = $usernick;
    $entry->user_avater = $user_avater;
    $entry->sex         = $sex;
    $entry->ctime       = time();
    $entry->lastmodtime = time();
    $entry->delete      = 0;

    $ret = $mgo->Save($entry);

    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogInfo("save ok");
    return 0;
}


function WeixinSave($info)
{
    if(!$info)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $id          = $info->id;
    $userid      = $info->userid;
    $headimgurl  = $info->headimgurl;
    $nickname    = $info->nickname;
    $sex         = $info->sex;
    $openid      = $info->openid;
    $city        = $info->city;
    $country     = $info->country;
    $province    = $info->province;
    $src         = $info->src;
    $srctype     = $info->srctype;


    if(!$id)
    {
        $id = \DaoRedis\Id::GenWeixinId();
    }
    $entry = new \DaoMongodb\WeixinEntry;
    $mongodb = new \DaoMongodb\Weixin;

    $entry->id          = $id;
    $entry->userid      = $userid;
    $entry->headimgurl  = $headimgurl;
    $entry->nickname    = $nickname;
    $entry->sex         = $sex;
    $entry->openid      = $openid;
    $entry->city        = $city;
    $entry->province    = $province;
    $entry->country     = $country;
    $entry->src         = $src;
    $entry->delete      = 0;
    $entry->srctype     = $srctype;
    $entry->lastmodtime = time();
    $ret = $mongodb->Save($entry);
    LogDebug($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogInfo("save ok");
    return 0;
}

function AlipaySave($info)
{
    if(!$info)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $id          = $info->id;
    $userid      = $info->userid;
    $avatar      = $info->avatar;
    $nickname    = $info->nick_name;
    $sex         = $info->sex;
    $alipay_id   = $info->user_id;
    $city        = $info->city;
    $province    = $info->province;
    $src         = $info->src;


    if(!$id)
    {
        $id = \DaoRedis\Id::GenAlipayId();
    }
    LogDebug($id);
    $entry = new \DaoMongodb\AlipayEntry;

    $mongodb = new \DaoMongodb\Alipay;

    $entry->id          = $id;
    $entry->userid      = $userid;
    $entry->avatar      = $avatar;
    $entry->nickname    = $nickname;
    $entry->sex         = $sex;
    $entry->alipay_id   = $alipay_id;
    $entry->city        = $city;
    $entry->province    = $province;
    $entry->src         = $src;
    $entry->delete      = 0;
    $entry->lastmodtime = time();
    $ret = $mongodb->Save($entry);
    //LogDebug($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogInfo("save ok");
    return 0;
}

function CustomerSave(&$info)
{
    if (!$info)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $customer_id     = $info->customer_id;
    $phone           = $info->phone;
    $usernick        = $info->usernick;
    $userid          = $info->userid;
    $shop_id         = $info->shop_id;
    $sex             = $info->sex;
    if(!$customer_id){
        $customer_id = \DaoRedis\Id::GenCustomerId();
    }
    //链接mongodb数据库
    $mongodb               = new \DaoMongodb\Customer;
    $entry                 = new \DaoMongodb\CustomerEntry;
    $entry->customer_id    = $customer_id;
    $entry->phone          = $phone;
    $entry->is_vip         = 0;
    $entry->shop_id        = $shop_id;
    $entry->sex            = $sex;
    $entry->usernick       = $usernick;
    $entry->ctime          = time();
    $entry->delete         = 0;
    $entry->userid         = $userid;
    $ret                   = $mongodb->Save($entry);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $info->customer_id = $customer_id;
    LogInfo("save ok");

    return 0;
}