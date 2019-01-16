<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取店铺信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_shop.php");
require_once("permission.php");
require_once("mgo_seat.php");
require_once("mgo_printer.php");
require_once("mgo_authorize.php");
require_once("mgo_employee.php");
require_once("redis_login.php");
require_once("redis_id.php");
require_once("mgo_position.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
require_once("/www/public.sailing.com/php/mgo_resources.php");
require_once("/www/public.sailing.com/php/mgo_term_binding.php");
use \Pub\Mongodb as Mgo;

function GetShopInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = (string)$_['shop_id'];

    $mgo = new \DaoMongodb\Shop;
    $info = $mgo->GetShopById($shop_id);

    $resp = (object)array(
        'info' => $info
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetShopList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id   = (string)$_['shop_id'];
    $shop_name = $_['shop_name'];

    $mgo = new \DaoMongodb\Shop;
    $list = $mgo->GetShopList(['shop_id'=>$shop_id, 'shop_name'=>$shop_name]);
    $resp = (object)array(
        'list' => $list
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//pad端端店铺信息
function GetPadShopInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id     = (string)$_['shop_id'];
    $token       = $_['token'];
     if(!$shop_id)
     {
         $shop_id = \Cache\Login::GetShopId();
     }
    //选择店铺的时候需要判断是否具有能操作改店铺的权限功能
    $device_type = $_['device_type'];//NEWSRCTYPE
    $userid      = \Cache\Login::GetUserid();
    $shopinfo    = \Cache\Shop::Get($shop_id);
    $ep_mgo      = new \DaoMongodb\Employee;
    $position  = new \DaoMongodb\Position;
    $employee    = $ep_mgo->QueryByShopId($userid, $shop_id);
    $permission_info = $position->GetPositionById($shop_id, $employee->position_id);
    //pad端要的非接口控制权限
    if($employee->is_admin != 1)
    {
         $permission  = $permission_info->position_permission;
        if($permission_info->position_permission[ShopPermissionCode::SEE_RESERVATION] == 0){
            $author->reserve = -1;
        }else{
            $author->reserve = 0;
        }
        if($permission_info->position_permission[ShopPermissionCode::SY_ORDER_LIST] == 0){
            $author->order = -1;
        }else{
            $author->order = 0;
        }
        if($permission_info->position_permission[ShopPermissionCode::SY_SEE_SYSTEM] == 0){
            $author->notice = -1;
        }else{
            $author->notice = 0;
        }

            if($permission_info->position_permission[ShopPermissionCode::BASIS_SET] == 0  &&
                $permission_info->position_permission[ShopPermissionCode::PRINT_SET] == 0 &&
                $permission_info->position_permission[ShopPermissionCode::QUESTION_BACK] == 0 &&
                $permission_info->position_permission[ShopPermissionCode::ABOUT] == 0)
            {
                $author->setting = -1;
            }else{
                $author->setting = 0;
            }
        $author->menu    = 0;
    }else{
        $author->menu    = 0;
        $author->reserve = 0;
        $author->order   = 0;
        $author->notice  = 0;
        $author->setting = 0;
        $permission  = (object)[
                "020101" => 1,
                "020102" => 1,
                "020103" => 1,
                "020104" => 1,
                "020105" => 1,
                "020106" => 1,
                "020107" => 1,
                "020108" => 1,
                "020109" => 1,
                "020110" => 1,
                "020111" => 1,
                "020112" => 1,
                "020113" => 1,
                "020114" => 1,
                "020115" => 1,
                "020116" => 1,
                "020117" => 1,
                "020118" => 1,
                "020119" => 1,
                "020120" => 1,
                "020121" => 1,
                "020201" => 1,
                "020202" => 1,
                "020203" => 1,
                "020204" => 1,
                "020205" => 1,
                "020301" => 1,
                "020302" => 1,
                "020303" => 1,
                "020304" => 1,
                "020305" => 1,
                "020306" => 1,
                "020307" => 1,
                "020308" => 1,
                "020401" => 1,
                "020501" => 1,
                "020502" => 1,
                "020503" => 1,
                "020504" => 1
        ]; //超管外包未判断是否是超管,所以后端在拼接一次PAD的全部权限
    }
    //店铺是否被冻结
    if($shopinfo->is_freeze == \EmployeeFreeze::FREEZE)
    {
        LogErr("shop freeze");
        return errcode::SHOP_IS_FREEZE;
    }
    //员工是否被冻结
    if($employee->is_freeze == \EmployeeFreeze::FREEZE)
    {
        LogErr("employee freeze");
        return errcode::EMPLOYEE_IS_FREEZE;
    }
    $time      = time();
    $resources = new Mgo\Resources;
    //判断该店铺是否拥有足够登录授权数及是否绑定终端
    if($employee->is_admin != 1 && $device_type)
    {
        $position_info = $position->GetPositionById($employee->shop_id,$employee->position_id);
        //收银机的所有权限
        if( $position_info->position_permission['020101'] == 0 && $position_info->position_permission['020102'] == 0 &&
            $position_info->position_permission['020103'] == 0 && $position_info->position_permission['020104'] == 0 &&
            $position_info->position_permission['020105'] == 0 && $position_info->position_permission['020106'] == 0 &&
            $position_info->position_permission['020107'] == 0 && $position_info->position_permission['020108'] == 0 &&
            $position_info->position_permission['020109'] == 0 && $position_info->position_permission['020110'] == 0 &&
            $position_info->position_permission['020111'] == 0 && $position_info->position_permission['020112'] == 0 &&
            $position_info->position_permission['020113'] == 0 && $position_info->position_permission['020114'] == 0 &&
            $position_info->position_permission['020115'] == 0 && $position_info->position_permission['020116'] == 0 &&
            $position_info->position_permission['020117'] == 0 && $position_info->position_permission['020118'] == 0 &&
            $position_info->position_permission['020119'] == 0 && $position_info->position_permission['020120'] == 0 &&
            $position_info->position_permission['020121'] == 0 &&
            $position_info->position_permission['020201'] == 0 && $position_info->position_permission['020202'] == 0 &&
            $position_info->position_permission['020203'] == 0 && $position_info->position_permission['020204'] == 0 &&
            $position_info->position_permission['020205'] == 0 &&
            $position_info->position_permission['020301'] == 0 && $position_info->position_permission['020302'] == 0 &&
            $position_info->position_permission['020303'] == 0 && $position_info->position_permission['020304'] == 0 &&
            $position_info->position_permission['020305'] == 0 && $position_info->position_permission['020306'] == 0 &&
            $position_info->position_permission['020307'] == 0 && $position_info->position_permission['020308'] == 0 &&
            $position_info->position_permission['020401'] == 0 &&
            $position_info->position_permission['020501'] == 0 && $position_info->position_permission['020502'] == 0 &&
            $position_info->position_permission['020503'] == 0 && $position_info->position_permission['020504'] == 0
        ){
            LogDebug('no permission');
            return errcode::USER_PERMISSION_ERR;
        }
        $srctype = $device_type;
        //LogDebug($srctype);
        $resources_info = $resources->GetList(
            [
                'shop_id'        => $shop_id,
                'resources_type' => $srctype,
                'login'          => 1 // 登录
            ]
        );
        if(empty($resources_info))
        {
            LogErr("resources not enough");
            return errcode::RESOURCES_NOT_ENOUGH;
        }
        //取出该设备上一次登录的资源
        foreach ($resources_info as $value)
        {
            if($value->term_id == $token)
            {
                $resources_id = $value->resources_id;
                break;
            }
        }
        //取出新的资源
        if(!$resources_id)
        {
            foreach ($resources_info as $v)
            {
                if($v->last_use_time < $time - 90)
                {
                    $resources_id = $v->resources_id;
                    break;
                }
            }
        }
        if(!$resources_id)
        {
            LogErr("resources not enough");
            return errcode::RESOURCES_NOT_ENOUGH;
        }
        $term =  new Mgo\TermBinding;
        $term_info = $term->QueryByEmployeeId($employee->employee_id);
        if($term_info->term_id && $term_info->term_id != $token)
        {
            LogErr($employee->employee_id."not binging term");
            return errcode::NOT_BIND_TERM;
        }
        $terminfo = $term->GetTermById($token);
        if(!empty($terminfo->employee_id) && $terminfo->employee_id != $employee->employee_id)
        {
            LogErr($employee->employee_id."not binging user");
            return errcode::NOT_BIND_USER;
        }
        $res_entry = new Mgo\ResourcesEntry;
        $res_entry->resources_id  = $resources_id;
        $res_entry->last_use_time = $time;
        $res_entry->term_id       = $token;
        LogDebug($res_entry);
        $ret = $resources->Save($res_entry);
        if(0 != $ret)
        {
            LogErr("resources save err");
            return errcode::SYS_ERR;
        }
        $term_entry = new Mgo\TermBindingEntry;
        $term_id = $term_info->term_id;
        if(!$term_id)
        {
            $term_id = $token;
            $term_entry->ctime = $time;
        }
        $term_entry->shop_id         = $shop_id;
        $term_entry->term_id         = $term_id;
        $term_entry->term_type       = $srctype;
        $term_entry->is_login        = 1;
        $term_entry->login_time      = $time;
        $term_entry->employee_id     = $employee->employee_id;
        LogDebug($term_entry);
        $ret = $term->Save($term_entry);
        if(0 != $ret)
        {
            LogErr("term_binding save err");
            return errcode::SYS_ERR;
        }
    }
    $overdue_resources = $resources->GetResourcesList(//过期时间小于1周的资源
            [
                'shop_id'   => $shop_id,
                'end_time'  => $time + 7*24*60*60
            ]);
    if(count($overdue_resources)>0)
    {
        $is_overdue = 1;
    }
    else
    {
        $is_overdue = 0;
    }

    $redis         = new \DaoRedis\Login();
    $info          = new \DaoRedis\LoginEntry();
    $info->token   = $token;
    $info->userid  = $userid;
    $info->shop_id = $shop_id;
    $info->login   = 1;
    $redis->Save($info);

    $mgo        = new \DaoMongodb\Shop;
    $is_shop = $mgo->GetShopById($shop_id);

    if(!$shop_id || !$is_shop->shop_id)
    {
        return errcode::SHOP_NOT_WEIXIN;
    }
    // 是否是萃荟店铺
    if($is_shop->agent_id == CuiHui::AGENTID)
    {
        $is_cuihui = 1;
    }
    $print      = new \DaoMongodb\Printer;
    $seat       = new \DaoMongodb\Seat;
    $info       = $mgo->GetShopById($shop_id);
    $prints     = $print->GetList($shop_id);
    $print_info = [];
    foreach ($prints as $p)
    {
        $ps['name'] = $p->printer_name;
        $ps['type'] = $p->receipt_type;
        //$ps['recv'] =$p->printer_name;
        //$ps['isPrint'] =$p->printer_name;
        array_push($print_info, $ps);
    }
    $seats     = $seat->GetList($shop_id);
    $seat_info = [];
    foreach ($seats as $s)
    {
        $ss['name']       = $s->seat_name;
        $ss['area']       = $s->seat_region;
        $ss['type']       = $s->seat_type;
        $ss['seat']       = $s->seat_size;
        $ss['price_type'] = $s->price_type;
        $ss['price']      = $s->price;
        array_push($seat_info, $ss);
    }
    $pay = [];
    if (in_array(ShopSaleWAY::WEIXIN,$info->shop_pay_way))
    {
        $weixin['type'] = "weixin";
        if ($info->weixin_pay_set->pay_way == 2)
        {
            $weixin['scan'] = 1;
        } else {
            $weixin['scan'] = 2;
            if($info->weixin_pay_set->code_img){
                $weixin['url']  = Cfg::instance()->GetShopUrlAddr() . '/php/img_get.php?img=1&imgname='.$info->weixin_pay_set->code_img;
            }else{
                $weixin['url']  = null;
            }

        }
        array_push($pay, $weixin);
    }

    if (in_array(ShopSaleWAY::APAY,$info->shop_pay_way))
    {
        $alipay['type'] = "zhifu";
        if ($info->alipay_set->pay_way == 2)
        {
            $alipay['scan'] = 1;
        } else {
            $alipay['scan'] = 2;
            if($info->alipay_set->code_img){
                $alipay['url']  = Cfg::instance()->GetShopUrlAddr() . '/php/img_get.php?img=1&imgname='.$info->alipay_set->code_img;
            }else{
                $alipay['url'] = null;
            }
        }
        array_push($pay, $alipay);
    }

  if(in_array(ShopSaleWAY::GUAZ,$info->shop_pay_way))
  {
      $collpay['type']        = "guazhang";
      $collpay['bookkeeping'] = $info->collection_set->is_debt;
      array_push($pay, $collpay);
  }
    if($info->collection_set->is_mailing == 0 )
    {
        $shop_info['erasure']       = -1;
    }else{
        $shop_info['erasure']       = $info->collection_set->mailing_type;
    }

    $shop_info['auto_order']    = $info->auto_order;
    $shop_info['custom_screen'] = $info->custom_screen;
    $shop_info['menu_sort']     = $info->menu_sort;
    $shop_info['shop_phone']    = $info->telephone;
    $shop_info['shop_address']  = $info->address;
    $shop_info['tables']        = $seat_info;
    $shop_info['print_info']    = $print_info;
    $shop_info['pay_type']      = $pay;
    $shop_info['permission']    = $permission;
    $shop_info['author']        = $author;
    $shop_info['is_cuihui']     = $is_cuihui;
    $resp = (object)[
        'shop_info' => $shop_info,
        'token'     => $_['token'],
        'is_overdue'=> $is_overdue
                     ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//pad端店铺支付宝或微信二维码
function GetPadShopCode(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id  = (string)$_['shop_id'];
    $pay_type = (int)$_['pay_type'];
    $mgo        = new \DaoMongodb\Shop;
    $shop_info = $mgo->GetShopById($shop_id);
    if(!$shop_id || !$shop_info->shop_id)
    {
        return errcode::SHOP_NOT_WEIXIN;
    }
    if($pay_type == 0)
    {
        $url = $shop_info->weixin_pay_set->code_img;
    }
    if($pay_type == 1)
    {
        $url = $shop_info->alipay_set->code_img;
    }
    if(!$url)
    {
        return errcode::CODE_NOT_SET;
    }
    $resp  = (object)[
        'url'=> Cfg::instance()->GetShopUrlAddr() . '/php/img_get.php?img=1&imgname='.$url,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetShopAuthorize(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = \Cache\Login::GetShopId();

    $mgo = new \DaoMongodb\Authorize;
    $info = $mgo->GetAuthorizeByShop($shop_id);
    $resp = (object)array(
        'info' => $info
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["shopinfo"]))
{
    $ret = GetShopInfo($resp);
}
elseif(isset($_["shoplist"]))
{
    $ret = GetShopList($resp);
}elseif(isset($_['get_shop_info']))
{
    $ret = GetPadShopInfo($resp);
}elseif(isset($_['get_shop_qrcode']))
{
    $ret = GetPadShopCode($resp);
}elseif(isset($_['get_shop_authorize']))
{
    $ret = GetShopAuthorize($resp);
}
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
    'data' => $resp
);
if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}
?><?php /******************************以下为html代码******************************/?>

