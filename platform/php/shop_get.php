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
require_once("mgo_employee.php");
require_once("mgo_authorize.php");
//Permission::PageCheck();
function GetShopInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = $_['shop_id'];

    $mgo = new \DaoMongodb\Shop;
    $info = $mgo->GetShopById($shop_id);
    $emmgo = new \DaoMongodb\Employee;
    $eminfo = $emmgo->GetAdminByShopId($shop_id);
    //$user = \Cache\UsernInfo::Get($eminfo->userid);
    $auth = new \DaoMongodb\Authorize;
    $info->authorize = $auth->GetAuthorizeByShop($shop_id);
    $info->phone     = $eminfo->phone;
    $info->userid    = $eminfo->userid;
    //$info->password  = $user->password;
    if($info->agent_id)
    {
        $info->agent_name = \Cache\Agent::GetAgentName($info->agent_id);
    }
    else
    {
        $info->agent_name = '无';
    }
    $resp = (object)array(
        'shopinfo' => $info
    );
    //LogDebug($resp);
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
    $shop_name       = $_['shop_name'];
    $agent_id        = $_['agent_id'];
    $agent_type      = $_['agent_type'];
    $province        = $_['province'];
    $city            = $_['city'];
    $area            = $_['area'];
    $shop_model      = $_['shop_model'];
    $begin_time      = $_["begin_time"];
    $end_time        = $_["end_time"];
    $business_status = $_['business_status'];
    $begin_bs_time   = $_['begin_bs_time'];
    $end_bs_time     = $_['end_bs_time'];
    $page_size       = $_['page_size'];
    $page_no         = $_['page_no'];
    $sort_name       = $_['sort_name'];
    $desc            = $_['desc'];


    if ($sort_name)
    {
        $sort[$sort_name] = (int)$desc;
    }
    //排序字段
    if (!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if (!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    if(!$begin_time && $end_time)
    {
        $begin_time = -28800; //默认时间
    }
    if(!$end_time && $begin_time)
    {
        $end_time = time();
    }
    if(!$begin_bs_time && $end_bs_time)
    {
        $begin_bs_time = -28800; //默认时间
    }
    if(!$end_bs_time && $begin_bs_time)
    {
        $end_bs_time = time();
    }
    $total = 0;
    $mgo = new \DaoMongodb\Shop;
    $list = $mgo->GetShopList([
        'shop_name'       => $shop_name,
        'agent_id'        => $agent_id,
        'province'        => $province,
        'city'            => $city,
        'area'            => $area,
        'agent_type'      => $agent_type,
        'shop_model'      => $shop_model,
        'business_status' => $business_status,
        'begin_time'      => $begin_time,
        'end_time'        => $end_time,
        'begin_bs_time'   => $begin_bs_time,
        'end_bs_time'     => $end_bs_time,
    ],
    $page_size,
    $page_no,
    $sort,
    $total
    );
    $emmgo = new \DaoMongodb\Employee;
    foreach ($list as &$item)
    {
        if($item->agent_id)
        {
            $item->agent_name = \Cache\Agent::GetAgentName($item->agent_id);
        }
        else
        {
            $item->agent_name = '无';
        }
        $eminfo = $emmgo->GetAdminByShopId($item->shop_id);
        $item->phone = $eminfo->phone;
    }
    $resp = (object)array(
        'list'  => $list,
        'total' => $total
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//pad端端店铺信息
function GetPadShopInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = (string)$_['shop_id'];
    $mgo        = new \DaoMongodb\Shop;
    $is_shop = $mgo->GetShopById($shop_id);
    if(!$shop_id || !$is_shop->shop_id)
    {
        return errcode::SHOP_NOT_WEIXIN;
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
        $ss['name'] = $s->seat_name;
        $ss['area'] = $s->seat_region;
        $ss['type'] = $s->seat_type;
        $ss['seat'] = $s->seat_size;
        array_push($seat_info, $ss);
    }
    $pay = [];
    if ($info->weixin_seting == 1)
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
                $weixin['url'] = null;
            }

        }
        array_push($pay, $weixin);
    }

    if ($info->alipay_seting == 1)
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

  if($info->collection_set->is_debt == 1)
  {
      $collpay['type']        = "guazhang";
      $collpay['bookkeeping'] = $info->collection_set->is_debt;
      array_push($pay, $collpay);
  }

    $shop_info['erasure']       = $info->collection_set->mailing_type;
    $shop_info['auto_order']    = $info->auto_order;
    $shop_info['custom_screen'] = $info->custom_screen;
    $shop_info['menu_sort']     = $info->menu_sort;
    $shop_info['phone']         = $info->telephone;
    $shop_info['tables']        = $seat_info;
    $shop_info['print_info']    = $print_info;
    $shop_info['pay_type']      = $pay;
    $resp = (object)[
        'shop_info' => $shop_info,
        'token'     => $_['token'],
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

$ret = -1;
$resp = (object)array();
if(isset($_["get_shop_info"]))
{
    $ret = GetShopInfo($resp);
}
elseif(isset($_["get_shop_list"]))
{
    $ret = GetShopList($resp);
}elseif(isset($_['get_shop_info']))
{
    $ret = GetPadShopInfo($resp);
}elseif(isset($_['get_shop_qrcode']))
{
    $ret = GetPadShopCode($resp);
}
$result = (object)array(
    'ret' => $ret,
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

