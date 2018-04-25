<?php
/*
 *
 */
require_once("current_dir_env.php");
require_once("typedef.php");
function Input()
{
    $_ = &$GLOBALS["_"];
    $_['version_get']       = true;
    $_['srctype']           = $_['src_type'];
    $_['platform']          = 2;
    $_['version_code']      = $_['cur_version_name'];
    require("version_get.php");
}
function Output(&$obj)
{
        $info = $obj->data->info;
        $version = [];
        if($info->need_update)
        {
            $version['need_update'] = true;
        }else{
            $version['need_update'] = false;
        }
        if($info->force_update)
        {
            $version['force_update'] = true;
        }else{
            $version['force_update'] = false;
        }
        $version['last_version_code'] = $info->version_code;
        $version['last_version_name'] = $info->version_name;
        $version['last_version_desc'] = $info->version_desc;
        $version['last_version_url']  = $info->version_url;

        $obj->data = $version;
        $obj->msg = errcode::toString($obj->ret);
        echo json_encode($obj);

}
Input();

?>