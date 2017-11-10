<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");

// 跨域访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');

LogDebug($_REQUEST);
LogDebug($_);

// echo json_encode($_REQUEST);
LogDebug("token:" . $_REQUEST['token']);
LogDebug(Util::ParseUrlParam($_REQUEST['data']));

echo json_encode((object)array(
    'ret'   => 19,
    'data'  => $_REQUEST
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?>