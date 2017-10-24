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
    $username     = $_["username"];
    $password_md5 = md5($_["password_md5"]);
    $page_code    = strtolower($_['page_code']);
    if(empty($username))
    {
        LogErr("param err, username empty");
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

    // 按条件可能会查出多个用户
    // $userinfo = $user->QueryUser($username);
    $userinfo = $user->QueryUser($username, $username, $username, $username, $password_md5);
    LogDebug($userinfo);
   
    if(null == $userinfo)
    {
        LogErr("user err:[$username]");
        return errcode::USER_PASSWD_ERR;
    }

    $shopinfo = (object)array();
    $employee = (object)array();
    if($userinfo->IsShopAdmin())
    {
        // 再查员工表，看这个用户是不是某个店的员工
        $employee = \Cache\Employee::Get($userinfo->userid);

        if(null != $employee && $employee->shop_id)
        {
            // 再检查店是否正常
            $shopinfo = \Cache\Shop::Get($employee->shop_id);
            if(!$shopinfo)
            {
                LogErr("shop not exist");
                return errcode::SHOP_NOT_EXIST;
            }
        }
    }
    // 是否为系统管事员
    // todo <<<<<<<<<<<<<<<
    //

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
    $info->shop_id  = $shopinfo->shop_id?:"";
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
        'logininfo'    => $logininfo,
        'userinfo'     => $userinfo,
        'shopinfo'     => $shopinfo,
        'employeeinfo' => $employee,
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
