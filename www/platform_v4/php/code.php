<?php
ob_start();
require_once("current_dir_env.php");
require_once("db_pool.php");
require_once("validatecode.php");
require_once("redis_login.php");
ob_end_clean();

function main()
{
    $token    = $_REQUEST['token'];
    $width    = $_REQUEST['width'];
    $height   = $_REQUEST['height'];
    $codelen  = $_REQUEST['codelen'];
    $fontsize = $_REQUEST['fontsize'];
    //默认数据
    if(!$width){
    	$width = 130;
    }
    if(!$height){
    	$height = 50;
    }
    if(!$codelen){
    	$codelen = 4;
    }
    if(!$fontsize){
    	$fontsize = 20;
    }
    $_vc = new ValidateCode($width, $height, $codelen, $fontsize);
    $_vc->doimg();
    $code = $_vc->getCode();//验证码
    $redis = new \DaoRedis\Login();
    $info = new \DaoRedis\LoginEntry();
    $info->token       = $token;
    $info->page_code   = $code;
    LogDebug($info);
    try
    {
        $ret = $redis->Save($info);
        LogDebug("ret:" . $ret['ok']);
    }
    catch(MongoCursorException $e)
    {
        LogErr($e->getMessage());
        return errcode::DB_OPR_ERR;
    }
}

main();
?>