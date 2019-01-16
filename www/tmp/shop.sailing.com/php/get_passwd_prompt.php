<?php
require_once("current_dir_env.php");
require_once("cache.php");

$_ = PageUtil::DecSubmitData(); //$_REQUEST; //
LogDebug($_);

// 取用户密码提示
function GetUserPassPrompt(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $username = $_['username'];
    if("" == $username)
    {
        LogErr("username empty");
        return errcode::USER_NAME_EMPTY;
    }

    $resp = (object)array(
        'passwd_prompt' => \Cache\UsernameInfo::GetPasswdPrompt($username)
    );
    LogDebug($resp);
    return 0;
}

$ret = -1;
$resp = (object)array();
if($_['type'] == 'user')
{
    $ret = GetUserPassPrompt($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
