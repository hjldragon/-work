<?php
require_once("current_dir_env.php");
require_once("mgo_login.php");
require_once("mgo_user.php");
require_once("redis_login.php");
require_once("redis_id.php");


function Login(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    if(PageUtil::LoginCheck())
    {
        $logininfo = \Cache\Login::Get($token);
        $resp = (object)array(
            'userid' => $logininfo->userid,
            'username' => $logininfo->username
        );
        LogDebug("already login, userid:{$logininfo->userid}");
        return errcode::USER_LOGINED;
    }

    $token        = $_["token"];
    $username     = $_["username"];
    $password_md5 = $_["password_md5"];

    if(empty($username))
    {
        LogErr("param err, username empty");
        return errcode::USER_NAME_EMPTY;
    }

    $user = new \DaoMongodb\User();
    $userinfo = $user->QueryByName($username);
    LogDebug($userinfo);
    if($username !== $userinfo->username)
    {
        LogErr("user err:[$username]");
        return errcode::USER_NO_EXIST;
    }
    if($password_md5 !== md5($userinfo->password))
    {
        LogErr("password err:[$password_md5]");
        return errcode::USER_PASSWD_ERR;
    }

    $mongodb = new \DaoMongodb\Login();
    $entry = new \DaoMongodb\LoginEntry();

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
    $info->shop_id  = $userinfo->shop_id;
    $info->login    = 1;
    LogDebug($info);

    $redis->Save($info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
        'userid'   => $userinfo->userid,
        'username' => $userinfo->username
    );
    LogInfo("login ok, userid:{$userinfo->userid}");
    return 0;
}

function AutoLogin(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token = $_['token'];
    $redis = new \DaoRedis\Login();
    $info = new \DaoRedis\LoginEntry();

    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->login    = 1;
    LogDebug($info);

    $ret = $redis->Save($info);

    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::DB_OPR_ERR;
    }
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['login']))
{
    $ret = Login($resp);
}
else if(isset($_['auto_login']))
{
    $ret = AutoLogin($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
