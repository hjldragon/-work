<?php

require_once("current_dir_env.php");
require_once("typedef.php");
header('Content-Type:text/html;charset=utf-8');
function Input()
{
    $_['get_news_content']   = true;
    $GLOBALS['need_json_obj'] = true;
    require("news_get.php");

}
function Output(&$obj)
{
    $a = $obj->data->content;
    $a = '<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><style>img{width:100%;height:auto}</style></head><body>'.$a.'</body></html>';
    echo $a;
}
Input()
?>