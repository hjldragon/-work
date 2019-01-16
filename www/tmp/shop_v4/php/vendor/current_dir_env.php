<?php
ob_start();
set_include_path(dirname(dirname(__FILE__)) . "/:/www/public.sailing.com/php/");
date_default_timezone_set('Asia/Shanghai');
require_once("global.php");
ob_end_clean();
?>
