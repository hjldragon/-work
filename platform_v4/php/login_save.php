<?php
require_once("current_dir_env.php");
require_once("mgo_login.php");
require_once("mgo_user.php");
require_once("redis_login.php");
require_once("redis_id.php");
require_once("mgo_ag_position.php");
require_once("mgo_platformer.php");
require_once("mgo_ag_employee.php");
require_once("mgo_pl_position.php");
require_once("ChuanglanSmsHelper/ChuanglanSmsApi.php");
require_once("/www/public.sailing.com/php/mgo_pl_role.php");
require_once("/www/public.sailing.com/permission/permission.php");
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
    $page_code    = strtolower($_['page_code']);

    if(empty($phone))
    {
        LogErr("param err, phone empty");
        return errcode::USER_NAME_EMPTY;
    }
    $db    = new \DaoRedis\Login();
    $radis = $db->Get($token);
    $radis_code = $radis->page_code;
    if($radis_code != $page_code){
        LogErr("coke err");
        return errcode::COKE_ERR;
    }
    $user     = new \DaoMongodb\User();
    $userinfo = $user->QueryUser($phone, $phone, $password_md5, UserSrc::PLATFORM);

    if(null == $userinfo)
    {
        LogErr("user err:[$phone]");
        return errcode::USER_PASSWD_ERR;
    }

    $plmgo       = new \DaoMongodb\Platformer;
    $agepmgo     = new \DaoMongodb\AGEmployee;
    $redis       = new \DaoRedis\Login();
    $info        = new \DaoRedis\LoginEntry();
    $position    = new \DaoMongodb\PLPosition;
    $role        = new Mgo\PlRole;
    $ag_role     = new Mgo\AgRole;
    $ag_position = new \DaoMongodb\AGPosition;

    $plinfo  = $plmgo->QueryByUserId($userinfo->userid, PlatformID::ID);
    $aginfo  = $agepmgo->QueryByUserId($userinfo->userid);

    if((!$plinfo->userid && !$aginfo->userid) || ($plinfo->userid && $aginfo->userid))
    {
        LogErr("user err:[$userinfo->userid]");
        return errcode::USER_NO_EXIST;
    }




    $agentinfo = (object)array();
    $type      = 0;
    if($plinfo->userid)
    {
        //员工是否被冻结
        if($plinfo->is_freeze == \EmployeeFreeze::FREEZE && $plinfo->is_admin != 1)
        {
            LogErr("employee freeze");
            return errcode::EMPLOYEE_IS_FREEZE;
        }
        //拥有权限
        $r_info         = $role->QueryById($plinfo->pl_role_id);
        $ps_info        = $position->GetPositionById($r_info->pl_position_id);
        $plinfo->permission     = $ps_info->pl_position_permission;
        $plinfo->pl_position_id = $ps_info->pl_position_id;
        $plinfo->agent_id       = PlAgentId::ID;
        $userinfo->is_admin     = $aginfo->is_admin;
        $userinfo ->audit_person= $ps_info->audit_person;///<<<<<<<<<<<<<<<<审核级别
        $info->platform_id      = PlatformID::ID;
        $platforminfo           = $plinfo;
        $type                   = 1;
    }

    if($aginfo->userid)
    {
        $info->agent_id   = $aginfo->agent_id;
        $agentinfo        = \Cache\Agent::Get($aginfo->agent_id);
        //代理商是否被冻结
        if($agentinfo->is_freeze == \EmployeeFreeze::FREEZE)
        {
            LogErr("agent freeze");
            return errcode::AGENT_IS_FREEZE;
        }
        //员工是否被冻结
        if($aginfo->is_freeze == \EmployeeFreeze::FREEZE)
        {
            LogErr("employee freeze");
            return errcode::EMPLOYEE_IS_FREEZE;
        }
        $ag_r_info      = $ag_role->QueryById($aginfo->ag_role_id);
        $ag_info        = $ag_position->GetPositionById($ag_r_info->ag_position_id);

        $agentinfo->permission     = $ag_info->ag_position_permission;
        $agentinfo->ag_position_id = $ag_info->ag_position_id;
        $agentinfo->ag_employee_id = $aginfo->ag_employee_id;
        $agentinfo->real_name      = $aginfo->real_name;
        $agentinfo->is_freeze      = $aginfo->is_freeze;
        $userinfo->is_admin        = $aginfo->is_admin;
        $userinfo ->audit_person   = $ag_info->audit_person;//<<<<<<<<<<<<<<<<审核级别
        if($agentinfo->agent_type == 1)
        {
            $type = 2;
        }
        else
        {
            $type = 3;
        }
    }
    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->userid   = $userinfo->userid;
    $info->username = $userinfo->username;
    $info->login    = 1;

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
        'agentinfo'    => $agentinfo,
        'platforminfo' => $platforminfo,
        'type'         => $type
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
//发送图形手机验证码
function GetCoke(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token = $_['token'];
    $phone = $_['phone'];
    $name  = $_['name'];
    $mgo   = new \DaoMongodb\User;
    // 查找手机号码
    $ret = $mgo->QueryByPhone($phone,UserSrc::PLATFORM);
    //LogDebug($ret);
    switch ($name)
    {
        case 'forgetPwd':
            if(!$ret->phone){
                return errcode::USER_NOT_ZC;
            }
            break;
        case 'registrate':
            if ($ret->phone) {
                return errcode::PHONE_IS_EXIST;
            }
            break;
        default:
            return errcode::PARAM_ERR;
            break;
    }
    $page_code  = strtolower($_['page_code']);
    $db         = new \DaoRedis\Login;
    $redis      = $db->Get($token);
    $radis_code = $redis->page_code;
    //验证验证码
    if ($radis_code != $page_code)
    {
        LogErr("coke err");
        return errcode::COKE_ERR;
    }

    if (!preg_match('/^\d{11}$/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }

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
//无图形验证码发送手机验证码
function GetPhoneCode(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token      = $_['token'];
    $phone      = $_['phone'];
    $user       = new \DaoMongodb\User;
    $userinfo   = $user->QueryByPhone($phone);
    if(!$userinfo->phone)
    {
        return errcode::USER_NOT_ZC;
    }
    if (!preg_match('/^\d{11}$/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }

    //$code    = 654321;//<<<<<<<<<<<<<<<<<<<<<<<<<<<<调试写死数据
    $code = mt_rand(100000, 999999);
    $clapi  = new ChuanglanSmsApi();
    $msg    = '尊敬的用户，您本次的验证码为' . $code . '有效期5分钟。打死不要将内容告诉其他人！';
    $result = $clapi->sendSMS($phone, $msg);
    LogDebug($result);
    if (!is_null(json_decode($result)))
    {
        $output = json_decode($result, true);
        if (isset($output['code']) && $output['code'] == '0')
        {
            LogDebug('短信发送成功！');
        } else {
            return $output['errorMsg'] . errcode::PHONE_SEND_FAIL;
        }
    }

    $redis            = new \DaoRedis\Login();
    $data             = new \DaoRedis\LoginEntry();
    $data->phone      = $phone;
    $data->token      = $token;
    $data->phone_code = $code;
    $data->code_time  = time() + 5 * 60 * 1000;
    LogDebug($data);
    $redis->Save($data);
    $resp = (object)[
        'phone_code' => $code,
    ];
    return 0;
}
//忘记密码
function UserNewPasswd(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token      = $_['token'];
    $phone      = $_['phone'];
    $mgo        = new \DaoMongodb\User;
    $user       = $mgo->QueryByPhone($phone,UserSrc::PLATFORM);
    LogDebug($_);
    if(!$user->userid)
    {
        LogDebug($phone);
        return errcode::PHONE_ERR;
    }
    $phone_code = $_['phone_code'];//手机验证码
    $result     = PageUtil::VerifyPhoneCode($token, $phone, $phone_code);
    LogDebug($result);
    if ($result != 0)
    {
        LogDebug($result);
        return errcode::PARAM_ERR;
    }
    $userid = $user->userid;
    if (!$userid)
    {
        LogDebug('userinfo ii not');
        return errcode::PARAM_ERR;
    }
    $new_passwd       = $_['new_passwd'];
    $new_passwd_again = $_['new_passwd_again'];

    $user             = new \DaoMongodb\UserEntry;
    if ($new_passwd != $new_passwd_again)
    {
        LogDebug($new_passwd, $new_passwd_again, 'is not same so err');
        return errcode::PASSWORD_TWO_SAME;
    }
    $user->userid   = $userid;
    $user->password = $new_passwd;
    $ret            = $mgo->Save($user);
    LogDebug($ret);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[];
    LogInfo("save ok");
    return 0;
}
//注册账号
function UserEmployeeSetting(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $real_name = $_['real_name'];
    if (!$real_name)
    {
        LogErr("name is empty,have to");
        return errcode::PARAM_ERR;
    }
    $sex        = $_['sex'];
    if(!$sex)
    {
        LogErr("sex is no,have to");
        return errcode::PARAM_ERR;
    }
    $token      = $_['token'];
    $phone      = $_['phone'];
    $phone_code = $_['phone_code'];//手机验证码
    $result     = PageUtil::VerifyPhoneCode($token, $phone, $phone_code);
    //验证手机结果
    if ($result != 0)
    {
        return $result;
    }
    $mgo    = new \DaoMongodb\User;
    $userid = \DaoRedis\Id::GenUserId();
    // 是否已注册过
    $ret = $mgo->IsExist([
        'userid'    => $userid,
        'real_name' => $real_name,
        'phone'     => $phone,
    ]);
    LogDebug($ret);
    if (null != $ret) {
        if ($ret->phone) {
            LogErr("phone exist:[{$ret->phone}");
            return errcode::PHONE_IS_EXIST;
        }
    }

    $passwd = $_['passwd'];
    if (!$passwd) {
        LogErr("passwd is empty,have to");
        return errcode::PARAM_ERR;
    }
    $passwd_again = $_['passwd_again'];
    if ($passwd != $passwd_again) {
        LogDebug($passwd, $passwd_again);
        return errcode::PASSWORD_TWO_SAME;
    }


    //创建生成用户表
    $user   = new \DaoMongodb\UserEntry;

    $user->userid             = $userid;
    $user->sex                = $sex;
    $user->real_name          = $real_name;
    $user->delete             = 0;
    $user->password           = $passwd;
    $user->phone              = $phone;
    $user->ctime              = time();
    $ret                      = $mgo->Save($user);
    if ($ret != 0) {
        LogErr("Save err, ret=[$ret]");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
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
else if(isset($_['login_shop']))
{
    $ret = LoginShop($resp);
}
else if(isset($_['logout']))
{
    $ret = Logout($resp);
}elseif(isset($_['save_new_passwd']))
{
    $ret = UserNewPasswd($resp);
}elseif(isset($_['setting_user']))
{
    $ret = UserEmployeeSetting($resp);
}elseif (isset($_['get_coke'])) {

    $ret = GetCoke($resp);
}elseif (isset($_['get_phone_code'])) {

    $ret = GetPhoneCode($resp);
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
