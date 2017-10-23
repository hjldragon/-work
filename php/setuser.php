<?php
require_once("current_dir_env.php");
require_once("crypt.php");
require_once("util.php");
require_once("page_util.php");
require_once("mgo_user.php");

//LogDebug($_REQUEST);


function Logout(&$resp)
{
}

// function Regiser(&$resp)
// {
//     $_ = PageUtil::DecSubmitData();
//     if(!$_)
//     {
//         LogErr("param err");
//         return errcode::PARAM_ERR;
//     }

//     // 暂不开放注册
//     LogErr("permission err");
//     return errcode::USER_PERMISSION_ERR;

//     $username = $_["username"];
//     $passwd   = $_["passwd"];
//     $prompt   = $_["prompt"];

//     $dao = new DaoUser;

//     // 是否已注册过(不需要详细信息)
//     $ret = $dao->QueryByName($username);
//     if($ret != 0)
//     {
//         if($ret > 0)
//         {
//             LogErr("user exist, ret=[$ret], username=[$username]");
//             return errcode::USER_HAD_REG;
//         }
//         LogErr("dao->Exist err, ret=[$ret], username=[$username]");
//         return errcode::SYS_ERR;
//     }
//     //LogDebug( json_encode($entry) );


//     $mod = new UserModEntry;

//     $mod->SetField('name', $username);
//     $mod->SetField('passwd', $passwd);
//     $mod->SetField('passwd_prompt', $prompt);

//     if(Cfg::instance()->IsAdmin($username))
//     {
//         $mod->SetField('property', UserEntry::PROP_IS_ADMIN);
//     }


//     $ret = $dao->Insert($mod->data);
//     if($ret < 0)
//     {
//         LogErr("Insert err, ret=[$ret]");
//         return $ret;
//     }
//     $userid = $dao->GetId();

//     $resp = (object)array(
//         userid      => $userid
//     );

//     LogInfo("register ok, username:[{$username}], userid=[{$userid}], ip:[{$_SERVER['REMOTE_ADDR']}]");
//     return 0;
// }

function Modify(&$resp)
{
    $_ = PageUtil::DecSubmitData();
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $userid         = $_["userid"];
    $username       = $_["username"];
    $passwd_old     = $_["passwd_old"];
    $passwd_new     = $_["passwd_new"];
    $passwd_prompt  = $_["prompt"];

    $dao = new DaoUser;
    $entry = new UserEntry;

    // 是否已注册
    $ret = $dao->QueryById($userid, $entry);
    if($ret <= 0)
    {
        if($ret == 0)
        {
            LogErr("user not exist, ret=[$ret], userid=[$userid], username=[$username]");
            return errcode::USER_NO_EXIST;
        }
        LogErr("dao->Exist err, ret=[$ret], userid=[$userid], username=[$username]");
        return errcode::SYS_ERR;
    }
    //LogDebug( json_encode($entry) );

    if($passwd_old !== $entry->passwd)
    {
        LogErr("passwd_old err, passwd_old=[$passwd_old]");
        return errcode::USER_OLD_PASSWD_ERR;
    }

    $mod = new UserModEntry;

    $mod->SetField("passwd", $passwd_new);
    $mod->SetField("passwd_prompt", $passwd_prompt);
    $mod->SetEqCond("userid", $userid);

    LogDebug( json_encode($mod) );

    $ret = $dao->Update($mod);
    if($ret < 0)
    {
        LogErr("Update err, ret=[$ret]");
        return $ret;
    }
    LogInfo("ok, userid=[{$userid}]");
    return 0;
}

$resp = (object)array();
if($_REQUEST["opr"] === "login")
{
    $ret = Login($resp);
    LogDebug("ret=[$ret]");
    $resp = array(
        ret => $ret,
        data => $resp
    );
}
else
{
    $_ = PageUtil::DecSubmitData();
    if($_["opr"] === "register")
    {
        $ret = Regiser($resp);
        LogDebug("ret=[$ret]");
        $resp = array(
            ret => $ret,
            data => $resp
        );
    }
    elseif($_["opr"] === "modify")
    {
        $ret = Modify($resp);
        LogDebug("ret=[$ret]");
        $resp = array(
            ret => $ret,
            data => $resp
        );
    }
}

$html = json_encode($resp);;
print $html;
LogDebug($html);
LogInfo("--end--");
?>
