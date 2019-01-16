<?php
/*
 * [Rocky 2017-05-13 17:18:17]
 * 取订单信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_order.php");
require_once("mgo_seat.php");
require_once("mgo_menu.php");
require_once("mgo_employee.php");
require_once("/www/public.sailing.com/php/mgo_stall.php");
use \Pub\Mongodb as Mgo;

function GetOrderDownList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id          = $_['shop_id'];
    $made_status      = $_['made_status'];
    $food_name        = $_['food_name'];
    $page_no          = (int)$_['page_no'];
    $page_size        = (int)$_['page_size'];
    if(!$page_size)
    {
        $page_size = 8;//如果没有传默认10条
    }
    if(!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    if(!$made_status)
    {
        $made_status = MadeStatus::WAIT;
    }
    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::SHOP_ID_NOT;
    }

    $order_status_list = [NewOrderStatus::GUAZ,NewOrderStatus::NOPAY,NewOrderStatus::PAY];
    $mgo        = new \DaoMongodb\Order;
    $menu       = new \DaoMongodb\MenuInfo;
    $seat       = new \DaoMongodb\Seat;
    $shop       = new \DaoMongodb\Shop;
    $stall      = new Mgo\Stall;
    $employee   = new \DaoMongodb\Employee;
    $userid     = \Cache\Login::GetUserid();
    if(!$userid)
    {
        LogDebug('no userid');
        return errcode::USER_NOLOGIN;
    }
    $shop_info     = $shop->GetShopById($shop_id);
    $employee_info = $employee->QueryByShopId($userid, $shop_id);
    $stall_info    = $stall->GetInfoByStart($shop_id, $employee_info->employee_id);

    if($stall_info->stall_id)
    {
        $menu_list     = $menu->GetFoodAllList($shop_id, ['food_name'=>$food_name,'food_id_list'=>$stall_info->food_id_list]);
    }else{
        $menu_list     = $menu->GetFoodAllList($shop_id, ['food_name'=>$food_name]);
    }

    $seat_list  = $seat->GetList($shop_id);
    $seat_name_list = [];
    foreach ($seat_list as $s)
    {
        $seat_name_list[$s->seat_id] = $s->seat_name;
    }

    if(count($menu_list)>0)
    {
        $overtime_list = [];
        foreach ($menu_list as $m)
        {
            $overtime_list[$m->food_id] = $m->overtime;
        }
    }else{

        $seat_info  = $seat->GetSeatByName($shop_id,$food_name);
        if(count($seat_info)>0){
            foreach ($seat_info as $s){
                $seat_id    = $s->seat_id;
                $seat_id_list [] =$seat_id;
            }
        }else{
            $plate  = $food_name;
        }
    }

    $order_clear_time = $shop_info->order_clear_time;//店铺设置的清除时间
    $order_time       = time()-$order_clear_time;
    $order_list = $mgo->GetOrderAllList(
        [
            'shop_id'           => $shop_id,
            'order_status_list' => $order_status_list,
            'seat_id_list'      => $seat_id_list,
            'plate'             => $plate,
            'order_time'        => $order_time
        ]
    );
    $food_list   = [];
    $all         = [];
    foreach ($order_list as &$v)
    {
        $get_time = time() - $v->order_time;
       foreach ($v->food_list as $food)
       {
           if ($food->made_status == $made_status) {

               if ($v->is_urge == 1) {
                   $status = 1;//催单的状态(只要订单数据里面含有催单就优先级)
               } elseif ($get_time > $overtime_list[$food->food_id]) {
                   $status = 2;//超时
               } else {
                   $status = 0;//空白及不超时也不催单
               }
                   $all[$food->food_id]['status']      = $status;
                   $all[$food->food_id]['all_num']     += $food->food_num;
                   $all[$food->food_id]['made_status'] = $food->made_status;
           }
       }
    }
    $all_num  = 0;

    foreach ($menu_list as $f)
    {
        $w  = (object)[];
        if($all[$f->food_id])
        {
            $w->food_id     = $f->food_id;
            $w->food_name   = $f->food_name;
            $w->status      = $all[$f->food_id]['status'];
            $w->made_status = $all[$f->food_id]['made_status'];
            $w->num         = $all[$f->food_id]['all_num'];
            $all_num += $all[$f->food_id]['all_num'];
            array_push($food_list,$w);
        }
    }

    $start    = ($page_no-1)*$page_size; #计算每次分页的开始位置
    $total    = ceil(count($food_list)/$page_size);
    $page_all = array_slice($food_list,$start,$page_size);

    $resp = (object)[
       'food_list'    =>$page_all,
       'all_num'      =>$all_num,
       'total'        =>$total,
       'page_size'    =>$page_size,
       'page_no'      =>$page_no

    ];

    LogInfo("--ok--");
    return 0;
}

function GetOrderFoodList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id          = $_['shop_id'];
    $made_status      = (int)$_['made_status'];
    $seek_status      = (int)$_['seek_status'];
    $food_id          = $_['food_id'];
    $page_no          = (int)$_['page_no'];
    $page_size        = (int)$_['page_size'];
    if(!$page_size)
    {
        $page_size = 8;//如果没有传默认10条
    }
    if(!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    if(!$made_status)
    {
        $made_status = MadeStatus::WAIT;
    }
    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::SHOP_ID_NOT;
    }

    $order_status_list = [NewOrderStatus::GUAZ,NewOrderStatus::NOPAY,NewOrderStatus::PAY];
    $mgo        = new \DaoMongodb\Order;
    $menu       = new \DaoMongodb\MenuInfo;
    $seat       = new \DaoMongodb\Seat;
    $shop       = new \DaoMongodb\Shop;
    $stall      = new Mgo\Stall;
    $employee   = new \DaoMongodb\Employee;
    $userid     = \Cache\Login::GetUserid();
    if(!$userid){
        LogDebug('no userid');
        return errcode::USER_NOLOGIN;
    }
    $shop_info     = $shop->GetShopById($shop_id);
    $employee_info = $employee->QueryByShopId($userid, $shop_id);
    $stall_info    = $stall->GetInfoByStart($shop_id, $employee_info->employee_id);
    $seat_list     = $seat->GetList($shop_id);


    $seat_name_list = [];
    foreach ($seat_list as $s)
    {
        $seat_name_list[$s->seat_id] = $s->seat_name;
    }

    $menu_list     = $menu->GetFoodAllList($shop_id);
    $overtime_list = [];
    foreach ($menu_list as $m)
    {
        $overtime_list[$m->food_id] = $m->overtime;
    }
    $order_clear_time = $shop_info->order_clear_time;//店铺设置的清除时间
    $order_time       = time()-$order_clear_time;

    $order_list = $mgo->GetOrderAllList(
        [
            'shop_id'           => $shop_id,
            'order_status_list' => $order_status_list,
            'order_time'        => $order_time
        ]
    );


    $food_list   = [];
    $now         = time();
    $cs_num      = 0;
    $urge_num    = 0;
    foreach ($order_list as &$v)
    {
        foreach ($v->food_list as $key=>$food)
        {

            $get_time                      = $now - $v->order_time; //已过去时间
            if($v->is_urge == 1)
            {
                $status    = 1;//催单的状态(只要订单数据里面含有催单就优先级)
                $urge_num += $food->food_num;
            }
            elseif ($get_time > $overtime_list[$food->food_id])
            {
                    $status   = 2;//超时
                    $cs_num   += $food->food_num;

            }else{
                $status   = 0;//空白及不超时也不催单
            }
            if($made_status == $food->made_status)
            {
                if(!$food_id)
                {
                    $food_page_list = [];
                    $food_page_list['order_id']    = $v->order_id;
                    $food_page_list['id']          = $food->id;
                    $food_page_list['seat_name']   = $seat_name_list[$v->seat_id];
                    if(!$food_page_list['seat_name']){
                        $food_page_list['seat_name'] = $v->plate;
                    }
                    $food_page_list['food_name']   = $food->food_name;
                    $food_page_list['food_num']    = $food->food_num;
                    $food_page_list['order_remark']= $v->order_remark;
                    $food_page_list['status']      = $status;
                    $food_page_list['order_time']  = $v->order_time;
                    $food_page_list['info_list']   = $v->food_list;
                    $food_page_list['food_id']     = $food->food_id;
                    array_push($food_list,$food_page_list);
                }else{
                    if($food_id == $food->food_id)
                    {
                        $food_page_list = [];
                        $food_page_list['order_id']    = $v->order_id;
                        $food_page_list['id']          = $food->id;
                        $food_page_list['seat_name']   = $seat_name_list[$v->seat_id];
                        if(!$food_page_list['seat_name'])
                        {
                            $food_page_list['seat_name'] = $v->plate;
                        }
                        $food_page_list['food_name']   = $food->food_name;
                        $food_page_list['food_num']    = $food->food_num;
                        $food_page_list['order_remark']= $v->order_remark;
                        $food_page_list['status']      = $status;
                        $food_page_list['order_time']  = $v->order_time;
                        $food_page_list['info_list']   = $v->food_list;
                        $food_page_list['food_id']     = $food->food_id;
                        array_push($food_list,$food_page_list);
                    }
                }
            }


        }
    }

        foreach($food_list as $k=>&$v1){

            if($seek_status)//筛选不同的状态
            {
              if($v1['status'] != $seek_status)
                {
                    unset($food_list[$k]);
                }
            }
            if($stall_info->is_stall == StallStart::START)//剔除订单列表中菜品中不是筛选出来的菜品列表
            {
                if(!in_array($v1['food_id'],$stall_info->food_id_list))
                {
                    unset($food_list[$k]);
                }
            }
            foreach ($v1['info_list'] as $k2=>$v2)
            {
//                if($seek_status)
//                {
                    if($v2->made_status != $made_status)
                    {
                        unset($v1['info_list'][$k2]);
                    }
                // }
                if($stall_info->is_stall == StallStart::START)
                {
                    if(!in_array($v2->food_id,$stall_info->food_id_list))
                    {
                        unset($v1['info_list'][$k2]);
                    }
                }
            }
             $v1['info_list'] = array_values($v1['info_list']);
    }
//        foreach ($food_list as $i)
//        {
//            if($i['status'] == 1)
//            {
//                $urge_num += $v1['food_num'];
//            }
//            if($i['status'] == 2)
//            {
//                $cs_num += $v1['food_num'];
//            }
//        }

    //分页
    $start    = ($page_no-1)*$page_size; #计算每次分页的开始位置
    $total    = ceil(count($food_list)/$page_size);
    $page_all = array_slice($food_list,$start,$page_size);
    //LogDebug($page_all);
    $resp = (object)[
        'urge_num'   => $urge_num,
        'cs_num'     => $cs_num,
        'list'       => $page_all,
        'total'      => $total,
        'page_size'  => $page_size,
        'page_no'    => $page_no
    ];

    LogInfo("--ok--");
    return 0;
}


function GetSaleListAll(&$resp)
{

    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id     = $_['shop_id'];
    $choose_way  = $_['choose_way'];
    if (!$shop_id) {
        LogErr("shop_id err or maybe not login");
        return errcode::SEAT_NOT_EXIST;
    }
    $mgo       = new \DaoMongodb\Shop;
    $shop_info = $mgo->GetShopById($shop_id);
    $seatlist  = $shop_info->shop_seat_region;
    $list = array_unique($seatlist);
    array_push($list,'餐牌');
    $sale_way_list = [];
    array_push($sale_way_list,'外卖','打包','堂食');
    if($choose_way == 1)
    {
        $new_list = array_values($list);
    }else{
        $new_list = $sale_way_list;
    }
    $resp = (object)[
        'list' => $new_list,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//获取所有的传菜销售方式的下拉列表
function GetPassThrough(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id          = $_['shop_id'];
    $sale_name        = $_['sale_name'];
    $made_status      = $_['made_status'];
    $page_no          = (int)$_['page_no'];
    $page_size        = (int)$_['page_size'];
    if(!$page_size)
    {
        $page_size = 8;//如果没有传默认10条
    }
    if(!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    if(!$made_status)
    {
        $made_status = MadeStatus::MAKE_OK;
    }
    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::SHOP_ID_NOT;
    }

    $order_status_list = [NewOrderStatus::GUAZ,NewOrderStatus::NOPAY,NewOrderStatus::PAY];
    $mgo        = new \DaoMongodb\Order;
    $seat       = new \DaoMongodb\Seat;
    $shop       = new \DaoMongodb\Shop;
    $stall      = new Mgo\Stall;
    $employee   = new \DaoMongodb\Employee;
    $userid     = \Cache\Login::GetUserid();
    if(!$userid){
        LogDebug('no userid');
        return errcode::USER_NOLOGIN;
    }
    $shop_info     = $shop->GetShopById($shop_id);
    $employee_info = $employee->QueryByShopId($userid, $shop_id);
    $stall_info    = $stall->GetInfoByStart($shop_id, $employee_info->employee_id);
    $seat_list     = $seat->GetList($shop_id);

    $seat_name_list = [];
    foreach ($seat_list as $s)
    {
        $seat_name_list[$s->seat_id] = $s->seat_name;
    }

   if($sale_name)
   {
       if($sale_name == '堂食')
       {
           $dine_way  = SALEWAY::EAT;
       }elseif($sale_name == '外卖'){
           $dine_way  = SALEWAY::TAKEOUT;
       }elseif($sale_name == '打包'){
           $dine_way  = SALEWAY::PACK;

       }elseif($sale_name == '餐牌'){
           $plate_all  = 1;
       }else{
           $seat_info  = $seat->GetSeatByRegion($shop_id, $sale_name);
           if(count($seat_info)>0){
               foreach ($seat_info as $s){
                   $seat_id    = $s->seat_id;
                   $seat_id_list [] =$seat_id;
               }
           }
       }
   }
    $order_clear_time = $shop_info->order_clear_time;//店铺设置的清除时间
    $order_time       = time()-$order_clear_time;
    $order_list       = $mgo->GetOrderAllList(
        [
            'shop_id'           => $shop_id,
            'order_status_list' => $order_status_list,
            'seat_id_list'      => $seat_id_list,
            'dine_way'          => $dine_way,
            'plate_all'         => $plate_all,
            'order_time'        => $order_time
        ]
    );
    $food_list   = [];
    foreach ($order_list as &$v)
    {
        foreach ($v->food_list as $food)
        {

            if($food->made_status == $made_status) {
                if($dine_way == SALEWAY::EAT && $food->is_pack && $v->dine_way == SALEWAY::EAT)
                {
                    continue;//<<<<<产品定义搜索销售方式是堂食但是菜品是打包属于打包搜索
                }
                $order       = [];
                $order['order_id']    = $v->order_id;
                $order['id']          = $food->id;
                $order['seat_name']   = $seat_name_list[$v->seat_id];
                if(!$order['seat_name']){
                    $order['seat_name'] = $v->plate;
                }
                $order['food_name']   = $food->food_name;

                if($food->is_pack)
                {
                    $order['sale_way']     = SALEWAY::PACK;
                }else{
                    $order['sale_way']     = SALEWAY::EAT;
                }
                $order['food_num']  = $food->food_num;
                if($shop_info->is_show_wait_time == 1)//如果设置了显示等待时间就显示
                {
                    $order['order_time']   = $v->order_time;
                }
                $order['info_list']    = $v->food_list;
                $order['food_id']      = $food->food_id;
                array_push($food_list,$order);
            }
        }
    }


    //筛选不同状态的数据
    foreach($food_list as $k=>&$v1){

        if($stall_info->is_stall == StallStart::START)//剔除订单列表中菜品中不是档口出来的菜品列表
        {
            if(!in_array($v1['food_id'],$stall_info->food_id_list))
            {
                unset($food_list[$k]);
            }
        }
        foreach ($v1['info_list'] as $k2=>&$v2)
        {
            if($v2->made_status != $made_status)
            {
                unset($v1['info_list'][$k2]);
            }

            if($stall_info->is_stall == StallStart::START)
            {
                if(!in_array($v2->food_id,$stall_info->food_id_list))
                {
                    unset($v1['info_list'][$k2]);
                }
            }
        }
        $v1['info_list'] = array_values($v1['info_list']);
    }

    $start    = ($page_no-1)*$page_size; #计算每次分页的开始位置
    $total    = ceil(count($food_list)/$page_size);
    $page_all = array_slice($food_list,$start,$page_size);

    $resp = (object)[
        'order_list'   =>$page_all,
        'total'        =>$total,
        'page_size'    =>$page_size,
        'page_no'      =>$page_no

    ];

    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();

if(isset($_["get_order_down_list"]))
{
    $ret = GetOrderDownList($resp);
}elseif(isset($_["get_order_food_list"]))
{
    $ret = GetOrderFoodList($resp);
}elseif(isset($_["get_sale_list"]))
{
    $ret = GetSaleListAll($resp);
}elseif(isset($_["get_pass_through"]))
{
    $ret = GetPassThrough($resp);
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

