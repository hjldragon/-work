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

    $token   = $_["token"];
    $userid  = $_["userid"];

    $user = new \DaoMongodb\User();
    
    $userinfo = \Cache\Login::Get($userid);
    LogDebug($userinfo);
   
    if(null == $userinfo)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $employee = (object)array();
    $shopinfo = array();
    // 再查员工表
    $employee_mgo = new \DaoMongodb\Employee;
    $employee = $employee_mgo->GetEmployeeById($userid);
    
    foreach ($employee as $key => $value) {
        if($value->shop_id){
           $shop =  \Cache\Shop::Get($value->shop_id);
           array_push($shopinfo, $shop);
        }
    }

    $mongodb = new \DaoMongodb\Login();
    $entry   = new \DaoMongodb\LoginEntry();

    $now = time();
    $entry->id          = \DaoRedis\Id::GenLoginId();
    $entry->userid      = $userid;
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
    $info->userid   = $userid;
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




$_=$_REQUEST;
$ret = -1;
$resp = (object)array();
if(isset($_['userid']))
{
    $ret = Login($resp);
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
