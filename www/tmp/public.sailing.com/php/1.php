<?php
echo "<pre>";
echo time() . "<br>";
echo "__FILE__: ", __FILE__, "\n";
echo "getcwd(): ", getcwd(), "\n";
echo "DOCUMENT_ROOT: ", $_SERVER["DOCUMENT_ROOT"], "\n";
var_dump($_ENV);
// var_dump($_SERVER['REQUEST_FILENAME']);
// // echo "<hr>";
phpinfo();

