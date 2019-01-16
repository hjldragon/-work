<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{
    $_ = &$GLOBALS["_"];
    $_['sysnews_list']        = true;
    $_['srctype']             = 1;
    require("news_get.php");
    //LogDebug($_);
}
function Output(&$obj)
{
    $lists = $obj->data->list;
    $list_data = [];
    foreach ($lists as $list)
    {
        $list_d['id']             = $list->news_id;
        $list_d['title']          = $list->title;
        $list_d['time']           = date('Y-m-d H:i:s', $list->ctime);
        $list_d['send_user']      = $list->send_username;
        $domain = Cfg::instance()->GetMainDomain();
        $list_d['url']            = "http://api.$domain/index.php?opr=get_news_content&news_id={$list->news_id}";
        array_push($list_data, $list_d);
    }
    $obj->data->list = $list_data;
    echo json_encode($obj);
}
Input();

?>