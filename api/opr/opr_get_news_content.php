<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
header('Content-Type:text/html;charset=utf-8');
function Input()
{
    $_ = &$_REQUEST;
    $_['get_news_content']    = true;
    $_['srctype']             = 3;
    require("news_get.php");
    // LogDebug($_);
}
function Output(&$obj)
{
    $a = $obj->data->content;
    //LogDebug($obj);
    $b = '<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head><body>'.$a.'</body></html>';
    echo $b;
}
Input();

?>