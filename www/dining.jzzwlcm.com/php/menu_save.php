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

//Permission::PageCheck();

function SaveFoodinfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $food_id             = $_['food_id'];
    $food_name           = $_['food_name'];
    $category_id         = $_['category_id'];
    $food_price          = $_['food_price'];
    $composition         = json_decode($_['composition']);
    $feature             = json_decode($_['feature']);
    $food_img_list       = json_decode($_['food_img_list']);
    $food_intro          = $_['food_intro'];
    $accessory           = $_['accessory'];
    $accessory_num       = $_['accessory_num'];
    $pack_remark         = $_['pack_remark'];
    $praise_num          = $_['praise_num'];
    $entry_type          = $_['entry_type'];
    $food_sale_time      = json_decode($_['food_sale_time']);
    $food_sale_week      = json_decode($_['food_sale_week']);
    $sale_off            = $_['sale_off'];
    $food_attach_list    = json_decode($_['food_attach_list']);
    $food_unit           = $_['food_unit'];
    $need_waiter_confirm = $_['need_waiter_confirm'];
    $stock_num_day       = $_['stock_num_day'];
    $is_draft            = $_['is_draft'];
    $type                = $_['type'];
    $sale_way            = json_decode($_['sale_way']);
    $sale_num            = $_['sale_num'];
    $sale_off_way        = $_['sale_off_way'];
    //LogDebug($_);
    if(null != $food_price && null == $type){
        LogErr("type err");
        return errcode::PARAM_ERR;
    }
    if(2 != $type){
        $food_price  = json_decode($food_price);
    }
    if(!$food_name){
        LogErr("food_name err");
        return errcode::PARAM_ERR;
    }
    if(!$category_id){
        LogErr("category_id err");
        return errcode::PARAM_ERR;
    }
    if(count($food_img_list) > MAX_FOODIMG_NUM)
    {
        LogErr("img too many");
        return errcode::FOOD_IMG_TOOMANY;
    }
    $shop_id = \Cache\Login::GetShopId();

    $mongodb = new \DaoMongodb\MenuInfo;
    $info = $mongodb->GetFoodinfoByName($food_name);
    if($info->food_name == $food_name
        && $info->shop_id === $shop_id
        && $info->food_id !== $food_id)
    {
        LogErr("food_name exist:[$food_name], info->food_id:[{$info->food_id}]");
        return errcode::FOOD_EXIST;
    }
    if(!$food_id)
    {
        $food_id = \DaoRedis\Id::GenFoodId();
    }
    if($entry_type)
    {
        $entry_time = time();
    }
  
    $entry = new \DaoMongodb\MenuInfoEntry;
    $now = time();
    $entry->food_id             = $food_id;
    $entry->shop_id             = $shop_id;
    $entry->category_id         = $category_id;
    $entry->food_name           = $food_name;
    $entry->stock_num_day       = $stock_num_day;
    $entry->food_price          = $food_price;
    $entry->food_img_list       = $food_img_list;
    $entry->food_intro          = $food_intro;
    $entry->entry_time          = $entry_time;
    $entry->food_attach_list    = $food_attach_list;
    $entry->food_unit           = $food_unit;
    $entry->need_waiter_confirm = $need_waiter_confirm;
    $entry->sale_off            = $sale_off;
    $entry->composition         = $composition;
    $entry->feature             = $feature;
    $entry->accessory           = $accessory;
    $entry->accessory_num       = $accessory_num;
    $entry->pack_remark         = $pack_remark;
    $entry->praise_num          = $praise_num;
    $entry->is_draft            = $is_draft;
    $entry->food_sale_time      = $food_sale_time;
    $entry->food_sale_week      = $food_sale_week;
    $entry->type                = $type;
    $entry->sale_way            = $sale_way;
    $entry->sale_num            = $sale_num;
    $entry->sale_off_way        = $sale_off_way;

    $ret = $mongodb->Save($entry);
    //LogDebug($ret);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}

function DeleteFood(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $food_id_list = json_decode($_['food_id_list']);
    
    $mongodb = new \DaoMongodb\MenuInfo;
    $ret = $mongodb->Delete($food_id_list);

    if(0 != $ret)
    {
        LogErr("Delete err");
        return errcode::SYS_ERR;
    }
       //当餐品状态删除的时候发送消息
        $shop_id = \Cache\Login::GetShopId();
        LogDebug($shop_id);
        foreach ($food_id_list as $id)
        {
            $ret_json =  PageUtil::NotifyFoodChange($shop_id, $id);
            LogDebug("[$ret_json]");
            $ret_json_obj = json_decode($ret_json);
            if(0 != $ret_json_obj->ret)
            {
                LogErr("Order err");
                return errcode::SYS_BUSY;
            }
        }


    $resp = (object)array(
    );
    LogInfo("delete ok");
    return 0;
}
// 批量上、下架操作
function SetSaleOff(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $food_id_list = json_decode($_['food_id_list']);
    $is_sale_off = $_['is_sale_off'];

    $mongodb = new \DaoMongodb\MenuInfo;
    $ret = $mongodb->SetSale($food_id_list,$is_sale_off);
   
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
        $shop_id = \Cache\Login::GetShopId();
        //LogDebug($shop_id);
        foreach ($food_id_list as $id)
        {
            LogDebug($id);
            $ret_json =  PageUtil::NotifyFoodChange($shop_id, $id);
            //LogDebug("[$ret_json]");
            $ret_json_obj = json_decode($ret_json);
            LogDebug($ret_json_obj->ret);
            if(0 != $ret_json_obj->ret)
            {
                LogErr("shop change send err");
                return errcode::SYS_BUSY;
            }
        }

    $resp = (object)array(
    );
    LogInfo("set sale_off:[{$is_sale_off}] ok: ");
    return 0;
}
//$_=$_REQUEST;
$ret = -1;
$resp = (object)array();
if(isset($_['save']))
{
    $ret = SaveFoodinfo($resp);
}
elseif(isset($_['del_food']))
{
    $ret = DeleteFood($resp);
}
elseif(isset($_['sale_off_opr']))
{
    $ret = SetSaleOff($resp);
}
else
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
