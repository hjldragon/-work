<?php

$user = 'http://wx.qlogo.cn/mmopen/vi_32/nbJian2Dg5w9xhORXfaKVyHdbqWx3l5CwT8DAs9WZcApXvmyricwqajQdWl6ezsJwLquog2aU4nIdc735zFU98Eg/0';
// $user = 'http://shop.jzzwlcm.com/php/img_get.php?img=1&imgname=648536f4f0d92a403962b36034272e82.jpg';
$data = file_get_contents($user);

// var_dump($data);
echo $data;
?>