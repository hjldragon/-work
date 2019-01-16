<?php
/*
 * [Rocky 2017-05-04 11:48:01]
 * 取打印机信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_category.php");
require_once("mgo_order.php");
require_once("mgo_seat.php");
require_once("mgo_employee.php");
require_once("mgo_menu.php");
require_once("mgo_order_status.php");
require_once("/www/public.sailing.com/php/mgo_stall.php");
use \Pub\Mongodb as Mgo;
//获取后厨上面需要打印的餐品类别并树桩列出
function GetPrintFoodCategoryList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = $_['shop_id'];
    if(!$shop_id)
    {
        $shop_id = \Cache\Login::GetShopId();
    }

    $list    = \Cache\Category::GetCategoryList($shop_id);
    $cate  =[];
    foreach ($list as $v)
    {
        if(!$v->parent_id)
        {
            $data = getTree($list,$v->category_id);
            $v->subType = $data;
            array_push($cate,$v);
        }
    }

    $all = [];
    foreach ($cate as $c)
    {
        $a['id']            = $c->category_id;
        $a['name'] = $c->category_name;

        $b = [];
        foreach ($c->subType as $s)
        {
            $d['id']            = $s->category_id;
            $d['name'] = $s->category_name;
            array_push($b,$d);
        }
        $a['subType']       = $b;
        array_push($all,$a);
    }
    $resp = (object)array(
        'list' => $all
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

//获取打印机订单数据
function GetPrinterOrderInfo(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $order_id = $_['order_id'];

    if(!$order_id)
    {
        LogErr("no order_id");
        return errcode::PARAM_ERR;
    }
    $shop_id  = $_['shop_id'];
    if(!$shop_id)
    {
        $shop_id = \Cache\Login::GetShopId();
    }
    $userid     = \Cache\Login::GetUserid();
    if(!$userid){
        LogDebug('no userid');
        return errcode::USER_NOLOGIN;
    }
    $mgo          = new \DaoMongodb\Order;
    $seat         = new \DaoMongodb\Seat;
    $employee     = new \DaoMongodb\Employee;
    $category     = new \DaoMongodb\MenuInfo;
    $order_status = new \DaoMongodb\OrderStatus;
    $stall        = new Mgo\Stall;

    $employee_info = $employee->QueryByShopId($userid, $shop_id);
    $stall_info    = $stall->GetInfoByStart($shop_id, $employee_info->employee_id);
    $info          = $mgo->GetOrderById($order_id);
    $status        = $order_status->GetOrderByIdAndStatus($order_id, $info->order_status);
    $user_info     = $employee->GetEmployeeInfo($shop_id, $status->employee_id);//操作员
    $water_info    = $employee->GetEmployeeInfo($shop_id, $info->employee_id); //服务员

    $seat_info     = $seat->GetSeatById($info->seat_id);
    if(!$seat_info->seat_id)
    {
        $table_name   = $info->plate;

    }else{
        $table_name   = $seat_info->seat_name;
    }
    if(!$info->order_remark)
    {
        $order_remark = '--';
    }else{
        $order_remark = $info->order_remark;
    }
   //操作信息
    $orderInfo['orderNum']    = $info->order_id;
    $orderInfo['tableNum']    = $table_name;
    $orderInfo['customerNum'] = $info->customer_num;
    $orderInfo['seqNo']       = $info->order_water_num;
    if(!$water_info->real_name)
    {
        $water_name = '--';
    }else{
        $water_name = $water_info->real_name;
    }
    if(!$user_info->real_name) //如果还未进行操作,要求打印的单据上都是服务员姓名
    {
        $orderInfo['operUser']    = $water_name;
    }else{
        $orderInfo['operUser']    = $user_info->real_name;
    }

    $orderInfo['waiter']      = $water_name;      //产品目前说明的服务员就是该登录账号的员工
    $orderInfo['startTime']   = date('Y-m-d H:i:s',$info->order_time);
    $orderInfo['printTime']   = date('Y-m-d H:i:s',time());
    //消费信息
    $total_count = 0;//总价格
    $total_price = 0;//总数量
    $food_list = [];

    foreach ($info->food_list as $k=>&$v)
    {
        if($stall_info->stall_id)
        {
            if(!in_array($v->food_id,$stall_info->food_id_list))//不符合档口的跳过
            {
                continue;
            }
        }
        $cate_food = $category->GetFoodinfoById($v->food_id);
        $cateinfo  = \Cache\Category::Get($cate_food->category_id);
        $data      = array();
        array_push($data, $cateinfo);
        GetCategoryId($data,$cateinfo->parent_id);
        foreach ($data as $cate)
        {
            if($cate->parent_id == "0")
            {
                $one_id  = $cate->category_id;
            }
            if($cate->parent_id == $one_id)
            {
                $two_id = $cate->category_id;
            }
        }
        foreach ($v->food_attach_list as $k=>$fl)
        {
            if($fl->title == '份量'){
                unset($v->food_attach_list[$k]);
            }
            if($fl->spec_value == "")
            {
                unset($v->food_attach_list[$k]);
            }
        }
        //将规格和大小分拼接
        $s_all = [];
        foreach ($v->food_attach_list as $fl)
        {
            $s_one = $fl->spec_value;
            array_push($s_all,$s_one);
        }
        $new_name = implode('', $s_all);
        if($new_name)
        {
            $spec_name = '('.$new_name.')';
        }else{
            $spec_name = '';
        }
        if($v->weight == 1)
        {
            $weight = '(大份)';
        }elseif ($v->weight == 2)
        {
            $weight = '(中份)';
        }elseif ($v->weight == 3)
        {
            $weight = '(小份)';
        }else{
            $weight = '';
        }
        if($v->is_add == 1)
        {
            $is_add = '+';
        }else{
            $is_add = '';
        }

        $food['id']             = $v->food_id;
        if($v->is_pack)
        {
            $food['name']           = $v->food_name.'(打包)'.$weight.$spec_name.$is_add;
        }elseif ($v->is_send)
        {
            $food['name']           = $v->food_name.'(赠送)'.$weight.$spec_name.$is_add;
        }else{
            $food['name']           = $v->food_name.$weight.$spec_name.$is_add;
        }

        $food['unit_price']     = $v->food_price;
        $food['count']          = $v->food_num;
        $food['price']          = $v->food_price_sum;
        $food['level_one_id']   = $one_id; //一级分类id
        $food['level_two_id']   = $two_id; //二级分类id
        $total_count  += $v->food_num;
        $total_price  += $v->food_price_sum;
        array_push($food_list,$food);
    }
    $productInfo['totalCount']   = $total_count;
    $productInfo['totalPrice']   = $total_price+$info->seat_price;
    $productInfo['sq_code_url']  = '扫码自助开发票功能即将上线，敬请期待…';//<<<<<<<写死的数据
    $productInfo['mark']         = $order_remark;
    $productInfo['productList']  = $food_list;
    $resp = (object)array(
        'orderInfo'    => $orderInfo,
        'productInfo'  => $productInfo,
    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

//树形排序只要一级分类
function getTree($data, $pId)
{
    $tree = '';
    foreach($data as $k => $v)
    {
        if($v->parent_id == $pId)
        {
            $tree[] = $v;
        }
    }
    return $tree;
}
//递归查找父级品类
function GetCategoryId(&$data,$parent_id){
    $info = \Cache\Category::Get($parent_id);
    if($info){
        array_unshift($data, $info);
    }
    if($info->parent_id){
        GetCategory($data,$info->parent_id);
    }
}
//递归查找父级品类
function GetCategory(&$data, $parent_id){
    $info = \Cache\Category::Get($parent_id);
    if($info){
        array_unshift($data, $info);
    }
    if($info->parent_id){
        GetCategory($data,$info->parent_id);
    }
}
$ret = -1;
$resp = (object)array();
if(isset($_["get_print_info"]))
{
    $ret = GetPrinterOrderInfo($resp);
}elseif(isset($_["get_food_type_list"]))
{
    $ret = GetPrintFoodCategoryList($resp);
}else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
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

