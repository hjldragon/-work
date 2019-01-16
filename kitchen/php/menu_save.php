<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 菜单信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_menu.php");
require_once("redis_id.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");


function SaveFoodinfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $food_list        = json_decode($_['food_list']);
    if(count($food_list)<0)
    {
        LogDebug('no any food');
       return errcode::FOOD_ERR;
    }
    $mgo   = new \DaoMongodb\MenuInfo;
    foreach ($food_list as $v)
    {
        $ret = $mgo->SetOverTime($v->food_id, $v->overtime);
        if(0 != $ret)
        {
            LogErr("Save err");
            return errcode::SYS_ERR;
        }
    }

    LogInfo("save ok");
    $resp = (object)array(
    );
    return 0;
}



$ret = -1;
$resp = (object)array();
if(isset($_['save_food']))
{
    $ret = SaveFoodinfo($resp);
}else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
