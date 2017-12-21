<?php
require_once ("current_dir_env.php");
require_once ("mgo_communication.php");

//var_dump($_REQUEST);
//echo 1;
//exit();
//设置保存留言信息的方法

function SaveContent(&$resp){
    LogDebug($resp);//-1
    $_=$GLOBALS["_"];
   // echo 1;;
    //var_dump($_);die;

    LogDebug($_);//所有要的数据都保存出来了
    if(!$_){
        LogDebug("param err");
        return errcode::PARAM_ERR;
    }

    $content=$_['content'];
    $content_id=$_['content_id'];
    $title=$_['title'];
    $delete = 1;
    $c_name='yy1';
    LogDebug($c_name);//这里获取了登录用户的名字

    //连接留言信息的数据库
    $mongodb=new DaoMongodb\Communication;
    $entry = new DaoMongodb\CommunicationEntry;
    $entry->content_id =  $content_id;
    $entry->content    =  $content;
    $entry->title      =  $title;
    $entry->c_time     =  date("Y-m-d H:i:s",time());
    $entry->c_name     =  $c_name;
    $entry->delete     =  $delete;

    $ret = $mongodb->Save($entry);
    LogDebug($ret);

    $resp=(object)array(
        'communication_list'=>$entry,
    );

    LogInfo("save ok");


    return 0;
}

$ret = -1;
$resp=(object)array(

);
if(isset($_['save'])){
    $ret=SaveContent($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data'=> $resp
));
LogDebug($html);

?><?php /******************************以下为html代码******************************/?>
<?=$html?>