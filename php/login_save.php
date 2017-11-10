<?php
require_once("current_dir_env.php");
require_once("mgo_login.php");
require_once("mgo_user.php");
require_once("redis_login.php");
require_once("redis_id.php");
require_once("mgo_employee.php");
function Login(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $logininfo = (object)array();
    // if(PageUtil::LoginCheck())
    // {
    //     $logininfo = \Cache\Login::Get($token);
    //     $resp = (object)array(
    //         'userid' => $logininfo->userid,
    //         'username' => $logininfo->username
    //     );
    //     LogDebug("already login, userid:{$logininfo->userid}");
    //     return errcode::USER_LOGINED;
    // }

    $token        = $_["token"];
    $phone        = $_["phone"];
    $password_md5 = md5($_["password_md5"]);
    $page_code    = strtolower($_['page_code']);
    if(empty($phone)) 
    {
        LogErr("param err, phone empty");
        return errcode::USER_NAME_EMPTY;
    }
    $db = new \DaoRedis\Login();
    $radis = $db->Get($token);
    $radis_code = $radis->page_code;
    if($radis_code != $page_code){
        LogErr("coke err");
        return errcode::COKE_ERR;
    }

    $user = new \DaoMongodb\User();
    
    $userinfo = $user->QueryUser($phone, $phone, $password_md5);
    LogDebug($userinfo);
    if(null == $userinfo)
    {
        LogErr("user err:[$username]");
        return errcode::USER_PASSWD_ERR;
    }
    $ret = LoginSave($userinfo, $token, $resp);
    if(0 != $ret)
    {
        LogErr("Login err");
        return errcode::SYS_ERR;
    }
    return 0;
    // $employee = (object)array();
    // $shopinfo = array();
    // // 再查员工表
    // $employee_mgo = new \DaoMongodb\Employee;
    // $employee = $employee_mgo->GetEmployeeById($userinfo->userid);
    
    // foreach ($employee as $key => $value) {
    //     if($value->shop_id){
    //        $shop =  \Cache\Shop::Get($value->shop_id);
    //        array_push($shopinfo, $shop);
    //     }
    // }

    // $mongodb = new \DaoMongodb\Login();
    // $entry   = new \DaoMongodb\LoginEntry();

    // $now = time();
    // $entry->id          = \DaoRedis\Id::GenLoginId();
    // $entry->userid      = $userinfo->userid;
    // $entry->ip          = $_SERVER['REMOTE_ADDR'];
    // $entry->login_time  = $now;
    // $entry->logout_time = 0;
    // $entry->ctime       = $now;
    // $entry->mtime       = $now;
    // $entry->delete      = 0;
    // $ret = $mongodb->Save($entry);
    // if(0 != $ret)
    // {
    //     LogErr("SaveKey err");
    //     return errcode::SYS_ERR;
    // }

    // $redis = new \DaoRedis\Login();
    // $info = new \DaoRedis\LoginEntry();

    // // 注：$info->key字段在终端初次提交时设置
    // $info->token    = $token;
    // $info->userid   = $userinfo->userid;
    // $info->username = $userinfo->username;
    // $info->shop_id  = '';
    // $info->login    = 1;
    // LogDebug($info);
    // $redis->Save($info);
    

    // $userinfo->answer = null;
    // $userinfo->password = null;

    // $resp = (object)array(
    //     'logininfo'    => $entry,
    //     'userinfo'     => $userinfo,
    //     'shopinfo'     => $shopinfo
    // );
    // LogInfo("login ok, userid:{$userinfo->userid}");
    // return 0;
}

function LoginWx(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token = $_["token"];
    $db = new \DaoRedis\Login();
    $radis = $db->Get($token);
    if(1 != $radis->login || !$radis->userid)
    {
        return errcode::USER_NOLOGIN;
    }
    $userinfo = \Cache\Login::Get($radis->userid);
    LogDebug($userinfo);
    $ret = LoginSave($userinfo, $token, $resp);
    if(0 != $ret)
    {
        LogErr("Login err");
        return errcode::SYS_ERR;
    }
    return 0;
    // $employee = (object)array();
    // $shopinfo = array();
    // // 再查员工表
    // $employee_mgo = new \DaoMongodb\Employee;
    // $employee = $employee_mgo->GetEmployeeById($radis->userid);
    
    // foreach ($employee as $key => $value) {
    //     if($value->shop_id){
    //        $shop =  \Cache\Shop::Get($value->shop_id);
    //        array_push($shopinfo, $shop);
    //     }
    // }
    // $mongodb = new \DaoMongodb\Login();
    // $entry   = new \DaoMongodb\LoginEntry();

    // $now = time();
    // $entry->id          = \DaoRedis\Id::GenLoginId();
    // $entry->userid      = $radis->userid;
    // $entry->ip          = $_SERVER['REMOTE_ADDR'];
    // $entry->login_time  = $now;
    // $entry->logout_time = 0;
    // $entry->ctime       = $now;
    // $entry->mtime       = $now;
    // $entry->delete      = 0;
    // $ret = $mongodb->Save($entry);
    // if(0 != $ret)
    // {
    //     LogErr("SaveKey err");
    //     return errcode::SYS_ERR;
    // }

    // $redis = new \DaoRedis\Login();
    // $info = new \DaoRedis\LoginEntry();

    // // 注：$info->key字段在终端初次提交时设置
    // $info->token    = $token;
    // $info->username = $userinfo->username;
    // $info->shop_id  = '';
    // LogDebug($info);

    // $redis->Save($info);
    

    // $userinfo->answer = null;
    // $userinfo->password = null;

    // $resp = (object)array(
    //     'logininfo'    => $entry,
    //     'userinfo'     => $userinfo,
    //     'shopinfo'     => $shopinfo
    // );
    // LogInfo("login ok, userid:{$userinfo->userid}");
    // return 0;
}

function LoginSave($userinfo, $token, &$resp)
{
    $employee = (object)array();
    $shopinfo = array();
    // 再查员工表
    $employee_mgo = new \DaoMongodb\Employee;
    $employee = $employee_mgo->GetEmployeeById($userinfo->userid);
    foreach ($employee as $key => $value) {
        if($value->shop_id){
           $shop =  \Cache\Shop::Get($value->shop_id);
           if($shop){
           	 array_push($shopinfo, $shop);
           }
        }
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
    $ret = $mongodb->Save($entry);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }

    $redis = new \DaoRedis\Login();
    $info = new \DaoRedis\LoginEntry();

    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->userid   = $userinfo->userid;
    $info->username = $userinfo->username;
    $info->shop_id  = '';
    $info->login    = 1;
    LogDebug($info);

    $redis->Save($info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }

    $userinfo->answer = null;
    $userinfo->password = null;

    $resp = (object)array(
        'logininfo'    => $entry,
        'userinfo'     => $userinfo,
        'shopinfo'     => $shopinfo
    );
    LogInfo("login ok, userid:{$userinfo->userid}");
    return 0;
}


function LoginShop(&$resp){
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token   = $_["token"];
    $shop_id = $_['shop_id'];
    $userid  = \Cache\Login::GetUserid();
    $shopinfo = \Cache\Shop::Get($shop_id);
    $mgo = new \DaoMongodb\Employee;
    $employee = $mgo->QueryByShopId($userid, $shop_id);
    $redis = new \DaoRedis\Login();
    $info = new \DaoRedis\LoginEntry();
    $info->token    = $token;
    $info->userid   = $userid;
    $info->shop_id  = $shop_id;
    $redis->Save($info);
    $resp = (object)array(
        'employeeinfo' => $employee,
        'shopinfo' => $shopinfo
    );
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

    $redis->Save($info);
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

$ret = -1;
$resp = (object)array();
if(isset($_['login']))
{
    $ret = Login($resp);
}
else if(isset($_['login_wx']))
{
    $ret = LoginWx($resp);
}
else if(isset($_['login_shop']))
{
    $ret = LoginShop($resp);
}
else if(isset($_['logout']))
{
    $ret = Logout($resp);
}
else
{
    $ret = -1;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
