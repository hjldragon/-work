<?php

require_once("current_dir_env.php");
require_once("typedef.php");
header('Content-Type:text/html;charset=utf-8');


    $content = '新吃货带领你装逼带你飞,不用看了,赶紧同意吧。带领你走上饮食界的新时代。666666。';
    $content = '<html><head><meta charset="utf-8"><meta name="viewport" content="
                width=device-width,initial-scale=1.0"><style>img{width:100%;height:auto}
                </style></head><body>'.$content.'</body></html>';
    echo $content;

?>