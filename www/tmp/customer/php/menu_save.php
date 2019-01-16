<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 菜单信息保存类
 */
require_once("current_dir_env.php");
require_once("mgo_menu.php");

function SaveFoodinfo(&$resp)
{

    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
//这里是检查用户是否登录
//    if(!PageUtil::LoginCheck())
//    {
//        LogDebug("not login, token:{$_['token']}");
//        return errcode::USER_NOLOGIN;
//    }

    $food_id        = (string)$_['food_id'];
    $food_name      = $_['food_name'];
    $food_category  = (string)$_['food_category'];
    $food_price     = json_decode($_['food_price']);
    $composition    = json_decode($_['composition']);
    $feature        = json_decode($_['feature']);
    $food_img_list  = json_decode($_['food_img_list']);
    $food_intro     = $_['food_intro'];
    $accessory      = $_['accessory'];
    $food_num_mon   = $_['food_num_mon'];
    $praise_num     = $_['praise_num'];
    $entry_time     = $_['entry_time'];
    $shop_id        = $_['shop_id'];
    $food_sale_time = json_decode($_['food_sale_time']);
    //<<<<<<<<<<<<<后面要用到检查图片是否上传过多
//    if(count($food_img_list) > MAX_FOODIMG_NUM)
//    {
//        LogErr("img too many");
//        return errcode::FOOD_IMG_TOOMANY;
//    }

    //$shop_id = \Cache\Login::GetShopId();//<<<<<<<<<<<<<<<<扫码获取的
    if (!$entry_time) {
        $entry_time = time();
    }
    
    $mongodb = new \DaoMongodb\MenuInfo;
    $entry   = new \DaoMongodb\MenuInfoEntry;

    $now                   = time();
    $entry->food_id        = $food_id;
    $entry->shop_id        = $shop_id;
    $entry->food_name      = $food_name;
    $entry->food_category  = $food_category;
    $entry->food_price     = $food_price;
    $entry->composition    = $composition;
    $entry->feature        = $feature;
    $entry->food_img_list  = $food_img_list;
    $entry->food_intro     = $food_intro;
    $entry->food_num_mon   = $food_num_mon;
    $entry->praise_num     = $praise_num;
    $entry->accessory      = $accessory;
    $entry->entry_time     = $entry_time;
    $entry->food_sale_time = $food_sale_time;

    $ret = $mongodb->Save($entry);

    if (0 != $ret) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    LogDebug($entry);

    $resp = (object)array();
    
    LogInfo("save ok");
    return 0;
}

$ret = -1;
$resp = (object)array();
if (isset($_['save']))
{
    $ret = SaveFoodinfo($resp);
}

$html = json_encode((object)array(
    'ret'  => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/ ?>
<?= $html ?>
