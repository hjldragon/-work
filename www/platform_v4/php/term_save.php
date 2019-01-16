<?php
/*
 * [Rocky 2017-05-03 19:42:11]
 * 解绑终端类别信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cfg.php");
require_once("mgo_term_binding.php");
use \Pub\Mongodb as Mgo;

function TermReBinding(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $term_id     = $_['term_id'];
    $employee_id = $_['employee_id'];
    if(!$term_id || !$employee_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $term  = new Mgo\TermBinding;
    $term_info = $term->GetTermById($term_id);
    if($term_info->employee_id != $employee_id)
    {
        LogErr("employee id err");
        return errcode::DATA_CHANGE;
    }
    $entry = new Mgo\TermBindingEntry;
    $entry->term_id     = $term_id;
    $entry->login_time  = 0;
    $entry->employee_id = "0";
    $entry->is_login    = 0;
    $ret = $term->Save($entry);
    if(0 != $ret)
    {
        LogErr("term_binding save err");
        return errcode::SYS_ERR;
    }
    LogInfo("--ok--");
    return 0;
}




$ret = -1;
$resp = (object)array();
if(isset($_["term_rebinding"]))
{
    $ret = TermReBinding($resp);
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
//var_dump($GLOBALS['need_json_obj']);
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

