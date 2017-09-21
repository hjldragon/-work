<?php
/*
 * [Rocky 2017-05-04 00:25:54]
 * 餐品类别信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_category.php");


function SaveCategory(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<待合并
//    if(!PageUtil::LoginCheck())
//    {
//        LogDebug("not login, token:{$_['token']}");
//        return errcode::USER_NOLOGIN;
//    }

    $category_id = $_['category_id'];
    $category_name = $_['category_name'];
    $printer_id = $_['printer_id'];
    $shop_id = $_['shop_id'];
    $type = $_['type'];
    $food_id_list = explode(',', $_['food_id_list']);
    $opening_time = explode(',', $_['opening_time']);

    //$shop_id = \Cache\Login::GetShopId();//<<<<<<<通过登录来获取shop_id

    $mongodb = new \DaoMongodb\Category;
    $entry = new \DaoMongodb\CategoryEntry;

    $entry->category_id = $category_id;
    $entry->category_name = $category_name;
    $entry->shop_id = $shop_id;
    $entry->printer_id = $printer_id;
    $entry->type = $type;
    $entry->food_id_list = $food_id_list;
    $entry->opening_time = $opening_time;

    $ret = $mongodb->Save($entry);
    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array();
    LogInfo("save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if (isset($_['save']))
{
    $ret = SaveCategory($resp);
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>
