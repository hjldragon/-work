<?php
require_once("current_dir_env.php");
require_once("mgo_login.php");
require_once("mgo_user.php");
require_once("redis_login.php");
require_once("redis_id.php");
require_once("mgo_ag_employee.php");
require_once("mgo_employee.php");
require_once("mgo_agent.php");
require_once("ChuanglanSmsHelper/ChuanglanSmsApi.php");
require_once("/www/public.sailing.com/php/mart/mgo_acc_err_num.php");
require_once("/www/public.sailing.com/php/send_sms.php");
use \Pub\Mongodb as Mgo;
function Login(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $token        = $_["token"];
    $phone        = $_["phone"];
    $password_md5 = $_["password_md5"];
    $src          = $_["src"];//2店铺3代理商

    //$logininfo = (object)array();
    if(empty($phone))
    {
        LogErr("param err, phone empty");
        return errcode::USER_NAME_EMPTY;
    }

    $user     = new \DaoMongodb\User();
    $userinfo = $user->QueryUser($phone, $phone, $password_md5, $src);
    //LogDebug($userinfo);
    if(null == $userinfo)
    {
        LogErr("user err:[$phone]");
        return errcode::USER_PASSWD_ERR;
    }

    $redis    = new \DaoRedis\Login();
    $info     = new \DaoRedis\LoginEntry();
    if($src == UserSrc::SHOP)
    {
        $employee_mgo    = new \DaoMongodb\Employee;
        $employee = $employee_mgo->GetEmployeeAdminByUserId($userinfo->userid);
        if(!$employee->userid)
        {
            LogErr("user err:[$userinfo->userid]");
            return errcode::USER_NO_EXIST;
        }
        $info->shop_id   = $employee->shop_id;
        $shopinfo        = \Cache\Shop::Get($employee->shop_id);
        //代理商是否被冻结
        if($shopinfo->is_freeze == \EmployeeFreeze::FREEZE)
        {
            LogErr("agent freeze");
            return errcode::SHOP_IS_FREEZE;
        }
    }
    if($src == UserSrc::PLATFORM)
    {
        $agepmgo  = new \DaoMongodb\AGEmployee;
        $aginfo   = $agepmgo->QueryByUserId($userinfo->userid);
        if(!$aginfo->userid || $aginfo->is_admin != 1)
        {
            LogErr("user err:[$userinfo->userid]");
            return errcode::USER_NO_EXIST;
        }
        $info->agent_id   = $aginfo->agent_id;
        $agent_mgo   = new \DaoMongodb\Agent;
        $agentinfo = $agent_mgo->QueryById($aginfo->agent_id);

        //代理商是否被冻结
        if($agentinfo->is_freeze == \EmployeeFreeze::FREEZE)
        {
            LogErr("agent freeze");
            return errcode::AGENT_IS_FREEZE;
        }
        if($agentinfo->pay_password != null)
        {
            $aginfo->is_password = 1;
        }
        else
        {
            $aginfo->is_password = 0;
        }
        unset($agentinfo->pay_password);
    }

    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->userid   = $userinfo->userid;
    $info->username = $userinfo->username;
    $info->login    = 1;
    //LogDebug($info);

    $ret = $redis->Save($info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }
    $mongodb = new \DaoMongodb\Login();
    $entry   = new \DaoMongodb\LoginEntry();

    $now = time();
    $entry->id          = \DaoRedis\Id::GenLoginId();
    $entry->userid      = $userinfo->userid;
    $entry->ip          = $_SERVER['REMOTE_ADDR'];
    $entry->login_time  = $now;
    $entry->logout_time = 0;
    $entry->ctime       = $now;
    $entry->mtime       = $now;
    $entry->delete      = 0;
    //LogDebug($entry);
    $ret = $mongodb->Save($entry);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }

    $userinfo->answer   = null;
    $userinfo->password = null;

    $resp = (object)array(
        'logininfo'    => $entry,
        'userinfo'     => $userinfo,
        'agentinfo'    => $aginfo,
        'shopinfo'     => $shopinfo
    );
    LogInfo("login ok, userid:{$userinfo->userid}");
    return 0;
}

function Logout(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token = $_["token"];

    $redis = new \DaoRedis\Login();
    $info  = new \DaoRedis\LoginEntry();

    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->login    = 0;
    LogDebug($info);

    $ret = $redis->Save($info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("logout ok, token:{$token}");
    return 0;
}
//发送手机验证码
function GetCoke(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token    = $_['token'];
    $phone    = $_['phone'];
    $userid   = $_['userid'];
    $agent_id = $_['agent_id'];// 修改支付密码时传
     if (!preg_match('/^\d{11}$/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }
    if(!$userid)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    if($agent_id)
    {
        $agentmgo   = new \DaoMongodb\Agent;
        $agent = $agentmgo->QueryById($agent_id);
        if(!$agent->agent_id)
        {
            LogErr($agent_id."agent_id err");
            return errcode::PARAM_ERR;
        }
        if($agent->pay_phone && $agent->pay_phone != $phone)
        {
            LogErr("phone err");
            return errcode::NOT_BIND_PHONE;
        }
    }
    else
    {
        $mgo   = new \DaoMongodb\User;
        //查找手机号码
        $user = $mgo->QueryById($userid);
        if($user->phone != $phone)
        {
            LogErr("phone err");
            return errcode::PHONE_ERR;
        }
    }

    //$code    = 654321;//<<<<<<<<<<<<<<<<<<<<<<<<<<<<调试写死数据
    $code   = mt_rand(100000, 999999);
    Sms::GetSms($phone,$code);//发送手机验证码

    $redis            = new \DaoRedis\Login();
    $data             = new \DaoRedis\LoginEntry();
    $data->phone      = $phone;
    $data->token      = $token;
    $data->phone_code = $code;
    $data->code_time  = time() + 5 * 60 * 1000;
    //LogDebug($data);
    $redis->Save($data);
    $resp = (object)[
    ];
    return 0;
}

//设置密码
function NewPayPasswd(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token            = $_['token'];
    $phone            = $_['phone'];
    $agent_id         = $_['agent_id'];
    $phone_code       = $_['phone_code'];//手机验证码
    $new_passwd       = $_['new_passwd'];
    $new_passwd_again = $_['new_passwd_again'];
    $mgo   = new \DaoMongodb\Agent;
    $agent = $mgo->QueryById($agent_id);
    if(!$agent->agent_id)
    {
        LogErr($agent_id."agent_id err");
        return errcode::PARAM_ERR;
    }
    if($agent->pay_phone && $agent->pay_phone != $phone)
    {
        LogErr("phone err");
        return errcode::NOT_BIND_PHONE;
    }
    if ($new_passwd != $new_passwd_again)
    {
        LogDebug($new_passwd, $new_passwd_again, 'is not same so err');
        return errcode::PASSWORD_TWO_SAME;
    }
    $result     = PageUtil::VerifyPhoneCode($token, $phone, $phone_code);
    if ($result != 0)
    {
        LogDebug($result);
        return $result;
    }
    $entry = new \DaoMongodb\AgentEntry;
    $entry->agent_id     = $agent_id;
    $entry->pay_password = md5($new_passwd);
    $entry->pay_phone    = $phone;
    $ret            = $mgo->Save($entry);
    LogDebug($ret);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    // 清空支付密码错误次数
    $err_mgo = new Mgo\AccErrNum;
    $ret = $err_mgo->SellNumEmpty($agent_id);
    $resp = (object)[];
    LogInfo("save ok");
    return 0;
}

//位运算分解
function PermissionSplit($n) {
    $n   |= 0;
    $pad = 0;
    $arr = [];
    while ($n)
    {
        if ($n & 1) array_push($arr, 1 << $pad);
        $pad++;
        $n >>= 1;
    }
    return $arr;
}
$ret = -1;
$resp = (object)array();
if(isset($_['login']))
{
    $ret = Login($resp);
}
else if(isset($_['logout']))
{
    $ret = Logout($resp);
}elseif(isset($_['save_new_paypasswd']))
{
    $ret = NewPayPasswd($resp);
}elseif (isset($_['get_coke'])) {

    $ret = GetCoke($resp);
}
else
{
    $ret = -1;
    LogErr("param err");
}

// $html = json_encode((object)array(
//     'ret' => $ret,
//     'data' => $resp
// ));

$result = (object)array(
    'ret' => $ret,
    'data' => $resp
);

if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}

?><?php /******************************以下为html代码******************************/?>
