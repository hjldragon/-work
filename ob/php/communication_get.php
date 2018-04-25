<?php
//配置文件
require_once ("current_dir_env.php");
//加载留言数据文件
require_once ("mgo_communication.php");
//加载用户权限登录文件
require_once ("cache.php");
//Permission::PageCheck();
//定义个获取留言信息的列表

function GetCommunicationList(&$resp){
    //全局变量
    //LogDebug($resp);

    $_=$GLOBALS["_"];
    LogDebug($_);
    //错误提示错误信息
    if(!$_){
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
//    $content_id = $_['content_id'];
//    $content = $_['content'];
//    $title   = $_['title'];
//    $c_name  = $_['c_name'];
//    $c_time  = $_['c_time'];
    //连接数据库
    $mgo = new DaoMongodb\Communication;
    //获取数据库的信息，保存到list中，然后在将list中的文件分配给$_
    $list = $mgo->GetContentList();
//    $list = $mgo->GetContentList([
//            'conent_id'=>$content_id,
//            'content'=>$content,
//            'title'=>$title,
//            'c_name'=>$c_name,
//            'c_time'=>$c_time,
//        ]
    //);
    LogDebug($list);
    $resp=(object)array(
        'list'=>$list
    );
    LogDebug($resp);
    LogInfo("get --ok--");
    return 0;//成功后返回0，即使ret的数据结果
}
$ret =  -1;
$resp=(object)array();
LogDebug($resp);
if(isset($_["list"])){
    $ret = GetCommunicationList($resp);
}
$html =json_encode((object)array(
    'ret' => $ret,
    'data'=>$resp
));
echo $html;//传送给前台
LogDebug($html);//数据已经发送分配成功
?>