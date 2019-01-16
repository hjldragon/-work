<?php
set_include_path(__DIR__."/:/www/public.sailing.com/php/:/www/public.sailing.com/php/weixin/");
date_default_timezone_set('Asia/Shanghai');   // 2015-01-28 20:49:45

ob_start();
require_once("global.php");
ob_end_clean();
