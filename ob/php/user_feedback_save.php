<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 新建代理商生成的数据文件
 */
require_once("current_dir_env.php");
require_once("mgo_user_feedback.php");
require_once("redis_id.php");
require_once("redis_login.php");

//Permission::PageCheck();
function SaveUserFeedback(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $feedback_phone = $_['feedback_phone'];
    $feedback_type  = $_['feedback_type'];
    $content        = $_['content'];
    if(!$feedback_phone)
    {
        $userid         = \Cache\Login::GetUserid();
        $user           = new \DaoMongodb\User;
        $user_info      = $user->QueryById($userid);
        $feedback_phone = $user_info->phone;
    }
    if(!$feedback_phone || !$content || $feedback_type == null)
    {
        LogErr("feedback_phone,type,content is empty");
        return errcode::PARAM_ERR;
    }

    $feedback_id = \DaoRedis\Id::GenFeedbackId();
    $entry       = new \DaoMongodb\UserFeedbackEntry;
    $mgo         = new \DaoMongodb\UserFeedback;

    $entry->feedback_id      = $feedback_id;
    $entry->feedback_type    = $feedback_type;
    $entry->feedback_phone   = $feedback_phone;
    $entry->content          = $content;
    $entry->feedback_time    = time();
    $entry->is_ready         = 0;
    $entry->delete           = 0;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

$ret  = -1;
$resp = (object)array();
if(isset($_['user_feedback_save']))
{
    $ret = SaveUserFeedback($resp);
}

else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
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

