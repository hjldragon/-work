<?php

if(isset($_REQUEST['v1']))
{
    setcookie("version", "v1");
}
if(isset($_REQUEST['v2']))
{
    setcookie("version", "v2");
}

echo "<h1><pre>";
// echo "REMOTE_ADDR    :", $_SERVER["REMOTE_ADDR"], "\n";
// echo "HTTP_X_REAL_IP :", $_SERVER["HTTP_X_REAL_IP"], "\n";
echo "cookie.version: ", $_COOKIE["version"], "\n";
echo "当前目录: ", dirname(__FILE__), "\n";
// echo "ok\n";
// phpinfo();


?>