<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 菜单规格保存类
 */
require_once("current_dir_env.php");
require_once("const.php");
require_once("../../public.sailing.com/php/mgo_selfhelp.php");
use \Pub\Mongodb as Mgo;
function SaveSelfHelpInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $userid  = \Cache\Login::GetUserid();
    if(!$userid)
    {
        LogErr("no userid  the why is not login");
        return errcode::USER_NOLOGIN;
    }
    $shop_id         = $_['shop_id'];
    $selfhelp_id     = $_['selfhelp_id'];
    $selfhelp_name   = $_['selfhelp_name'];
    $is_using        = $_['is_using'];
    $using_type      = $_['using_type'];
    $is_print        = $_['is_print'];
    $is_wx_send      = $_['is_wx_send'];
    $remark          = $_['remark'];

    $mgo            = new Mgo\Selfhelp;
    $entry          = new Mgo\SelfhelpEntry;

    $entry->selfhelp_id    = $selfhelp_id;
    $entry->selfhelp_name  = $selfhelp_name;
    $entry->userid         = $userid;
    $entry->shop_id        = $shop_id;
    $entry->is_using       = $is_using;
    $entry->using_type     = $using_type;
    $entry->is_print       = $is_print;
    $entry->is_wx_send     = $is_wx_send;
    $entry->remark         = $remark;
    $entry->delete         = 0;
    $ret = $mgo->Save($entry);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[];
    LogInfo("save ok");
    return 0;
}

function UnBindingSelfhelp(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $selfhelp_id = $_['selfhelp_id'];
    if (!$selfhelp_id){
        LogErr('no selfhelp_id');
        return errcode::PARAM_ERR;
    }

    $mongodb = new Mgo\Selfhelp;
    $ret     = $mongodb->SetUnBinding($selfhelp_id);

    if (0 != $ret) {
        LogErr("unbinding err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    LogInfo("unbinding ok");
    return 0;

}

function UnBindingSelfhelpShopId(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $selfhelp_id = $_['selfhelp_id'];
    if (!$selfhelp_id){
        LogErr('no selfhelp_id');
        return errcode::PARAM_ERR;
    }


    $mongodb = new Mgo\Selfhelp;
    $ret     = $mongodb->UnBindingShopId($selfhelp_id);

    if (0 != $ret) {
        LogErr("unbinding err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    LogInfo("unbinding ok");
    return 0;

}
$ret = -1;
$resp = (object)array();
if(isset($_['selfhelp_save']))
{
    $ret = SaveSelfHelpInfo($resp);
}elseif (isset($_['selfhelp_unbinding']))
{
    $ret = UnBindingSelfhelp($resp);
}elseif (isset($_['shopid_unbinding']))
{
    $ret = UnBindingSelfhelpShopId($resp);
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

