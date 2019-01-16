<?php
require_once("current_dir_env.php");
require_once("const.php");
require_once("cache.php");
require_once("cfg.php");



echo "<pre>";
echo json_encode(Cfg::instance(), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);


// $p = (object)[
//     "a1" => "我是a1",
//     "a2" => "/data/a",
//     "a3" => "123456",
//     "a4" => 12345678901234567890,
// ];

// $s = json_encode($p);
// echo "-------------------------\n";
// echo $s, "\n\n";


// $s = json_encode($p, JSON_PRETTY_PRINT);
// echo "-------------------------\n";
// echo $s, "\n\n";


// $s = json_encode($p,
//         JSON_PRETTY_PRINT
//         | JSON_UNESCAPED_SLASHES
//         | JSON_UNESCAPED_UNICODE
// );
// echo "-------------------------\n";
// echo $s, "\n\n";

?>