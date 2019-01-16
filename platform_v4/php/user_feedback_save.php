<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 新建代理商生成的数据文件
 */
require_once("current_dir_env.php");
require_once("mgo_user_feedback.php");
require_once("redis_id.php");
require_once("redis_login.php");
//批量删除用户反馈信息
function DelUserFeedback(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $feedback_id_list = json_decode($_['feedback_id_list']);

    $mongodb = new \DaoMongodb\UserFeedback;
    $ret     = $mongodb->BatchDelete($feedback_id_list);
    if(0 != $ret)
    {
        LogErr("Change err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("del ok");
    return 0;
}
$ret  = -1;
$resp = (object)array();
if(isset($_['user_feedback_del']))
{
    $ret = DelUserFeedback($resp);

}

else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
