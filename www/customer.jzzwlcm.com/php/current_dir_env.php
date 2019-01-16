<?php
// error_reporting(E_ERROR | E_WARNING | E_PARSE); // http://www.jb51.net/article/38952.htm
// error_reporting(E_ERROR | E_WARNING);
// error_reporting(E_ERROR | E_PARSE);
// error_reporting(E_ERROR);
// error_reporting(E_ALL);
// error_reporting(E_ALL & ~E_NOTICE);
set_include_path(dirname(__FILE__) . "/:/www/public.sailing.com/php/");
date_default_timezone_set('Asia/Shanghai');   // 2015-01-28 20:49:45
require_once("global.php");
ob_end_clean();
?>
