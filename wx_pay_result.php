<?php
/*
 * [Rocky 2017-06-05 18:12:03]
 * 处理微信支付结果
 */
require_once("current_dir_env.php");
require_once("mgo_order.php");
require_once("redis_id.php");
require_once("const.php");

// 支付结果, 1成功, 2出错
$success = ($_REQUEST['payret'] == 1);

$order_id = $_REQUEST['order_id'];