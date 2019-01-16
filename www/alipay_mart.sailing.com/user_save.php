<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 用户信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_user.php");
require_once("redis_id.php");


function SaveUserinfo($info,&$resp)
{
    if(!$info)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid        = $info['userid'];
    $usernick      = $info['usernick'];
    $user_avater   = $info['user_avater'];
    $phone         = $info['phone'];
    $sex           = $info['sex'];

    $mgo = new \DaoMongodb\User;
    $entry = new \DaoMongodb\UserEntry;

    if(!$userid){
        $userid = \DaoRedis\Id::GenUserId();
    }

    $entry->userid      = $userid;
    $entry->usernick    = $usernick;
    $entry->user_avater = $user_avater;
    $entry->phone       = $phone;
    $entry->sex         = $sex;
    $entry->ctime       = time();
    $entry->lastmodtime = time();
    $entry->delete      = 0;
    
    $ret = $mgo->Save($entry);
    
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
        'userid' => $userid
    );
    LogInfo("save ok");
    return 0;
}


$ret = -1;
$resp = (object)array();

function UserSave($info){
    $ret = SaveUserinfo($info,$resp);
    return (object)array(
        'ret' => $ret,
        'data' => $resp
    );
}



?>