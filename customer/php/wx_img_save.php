<?php
require_once("current_dir_env.php");
set_include_path("/www/wx.jzzwlcm.com/");
require_once "WxUtil.php";

function WxSaveImg(&$resp)
{   
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $media_id = $_['media_id'];
    if(!$media_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
   
    $info = \Pub\Wx\Util::GetToken();
    $access_token = $info->access_token;

    $str = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$media_id";
    //LogDebug($str);
    $md5 = md5($media_id);
    $filename = "$md5.jpg";
    //获取微信“获取临时素材”接口返回来的内容（即刚上传的图片）  
    $a = file_get_contents($str);
    //__DIR__指向当前执行的PHP脚本所在的目录
    //echo __DIR__;
    //以读写方式打开一个文件，若没有，则自动创建
    $url = PageUtil::GetImgFullname($filename);
    LogDebug($url);
    $resource = fopen($url , 'w+');
    //将图片内容写入上述新建的文件  
    fwrite($resource, $a);
    //关闭资源
    fclose($resource);
    $resp = (object)array(
        'filename' => $filename
    );
    return 0;
}


$ret = -1;
//$_ = $_REQUEST;
$resp = (object)array();
if(isset($_["wx_upload"]))
{
    $ret =  WxSaveImg($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));

?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>