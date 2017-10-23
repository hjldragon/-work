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
    //$c_name=Cache\Login::GetUsername();

    //var_dump($c_name);

    LogDebug($c_name);//这里获取了登录用户的名字
    //如果无法获取用户登录名,提示错误信息，请求登录
  /*  if(!$c_name){
        LogDebug("param err please login");
        return errcode::PARAM_ERR;
    }*/
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

   // LogDebug(date("Y-m-d H:i:s",time()));

    $resp=(object)array(
        'communication_list'=>$entry,
    );
    var_dump($resp);
    LogInfo("save ok");
    //LogDebug($resp);//这里是一个空数组
    //var_dump($resp);
    return 0;
}

//var_dump($_);die;
$ret = -1;
$resp=(object)array(

);

if(isset($_['save'])){
    $ret=SaveContent($resp);
}
//var_dump($resp);die;
$html = json_encode((object)array(
    'ret' => $ret,
    'data'=> $resp
));

LogDebug($html);//返回的是这个{"ret":0,"resp":{}}？？？？？

?><?php /******************************以下为html代码******************************/?>
<?=$html?>