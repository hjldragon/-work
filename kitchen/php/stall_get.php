<?php
/*
 * [Rocky 2017-05-03 16:47:09]
 * 菜单信息保存类
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_menu.php");
require_once("/www/public.sailing.com/php/mgo_stall.php");
use \Pub\Mongodb as Mgo;

function GetStallList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id      = $_['shop_id'];
    $userid       = \Cache\Login::GetUserid();
    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::SHOP_ID_NOT;
    }
    if(!$userid){
        LogDebug('no userid');
        return errcode::USER_NOLOGIN;
    }
    $mgo       = new Mgo\Stall;
    $menu      = new DaoMongodb\MenuInfo;
    $employee  = new \DaoMongodb\Employee;

    $employee_info  = $employee->QueryByShopId($userid, $shop_id);
    $list           = $mgo->GetListByShop($shop_id, $employee_info->employee_id);
    $domain         = Cfg::instance()->GetMainDomain();
    foreach ($list as &$v)
    {
        $food_list = [];
        foreach ($v->food_id_list as $k=>$l)
        {
            $food  = [];
            $food_info = $menu->GetFoodInfoById($l);
            $food['food_id']   = $food_info->food_id;
            $food['food_name'] = $food_info->food_name;
            $food['food_img']  = "http://kitchen.$domain/php/img_get.php?img=1&imgname=".$food_info->food_img_list[0];
            array_push($food_list,$food);
        }
        $v->food_list = $food_list;
    }

    $resp = (object)array(
        'list' => $list
    );

    return 0;
}

//获取基础设置处的档口下拉列表
function GetStallDownList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id      = $_['shop_id'];
    $userid       = \Cache\Login::GetUserid();
    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::SHOP_ID_NOT;
    }
    if(!$userid){
        LogDebug('no userid');
        return errcode::USER_NOLOGIN;
    }
    $mgo            = new Mgo\Stall;
    $employee       = new \DaoMongodb\Employee;
    $employee_info  = $employee->QueryByShopId($userid, $shop_id);
    $list           = $mgo->GetListByShop($shop_id, $employee_info->employee_id);
    $down_list = [];
    foreach ($list as &$v)
    {
       $down['stall_id']   = $v->stall_id;
       $down['stall_name'] = $v->stall_name;
       array_push($down_list,$down);
    }

    $resp = (object)array(
        'list' => $down_list
    );

    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_['get_stall_list']))
{
    $ret = GetStallList($resp);
}elseif(isset($_['get_down_list'])) {
    $ret = GetStallDownList($resp);
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
