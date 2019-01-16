<?php

$f = fopen("/www/wx.jzzwlcm.com/notify/req.txt", "a");                                                                                                                            
$msg = print_r($_REQUEST, 1);
fwrite($f, $msg);
fclose($f);

//echo $_REQUEST['echostr'];
echo $msg;
?>

