<?php
/*
 * 透传数据
 */
ini_set('date.timezone','Asia/Shanghai');
header('Content-Type:text/html; charset=utf-8');
echo htmlspecialchars_decode($_REQUEST['data']);
