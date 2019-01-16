<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 *售货机看板信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_stat_vendor_byday.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_stat_vendor_goods_byday.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_stat_vendor_order_byday.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_goods.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_record.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_return_record.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_fault_deal.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor_order.inc");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;
//登录店铺的首页信息
function GetHomeData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id     = $_['shop_id'];
    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::PARAM_ERR;
    }
    $vendor_mgo =  new VendorMgo\Vendor;
    $goods_mgo  =  new VendorMgo\VendorGoods;
    $return_mgo =  new VendorMgo\ReturnRecord;
    $record_mgo =  new VendorMgo\VendorRecord;
    $fault_mgo  =  new VendorMgo\Fault;
    $order_mgo  =  new VendorMgo\VendorOrder;
    $stock_num  = 0;
    $out_num    = 0;

    $return_num = 0;
    $return_mgo->GetListTotal(['shop_id'=>$shop_id],$return_num);

    $record_num  = 0;
    $record_mgo->GetListTotal(['shop_id'=>$shop_id],$record_num);

    $stock_deal = 0;
    $fault_mgo->GetAllList(['shop_id'=>$shop_id],[],$stock_deal);
    $scene_one = 0;
    $fault_mgo->GetAllList(['shop_id'=>$shop_id,'deal_status'=>DealStatus::NODEAL],[],$scene_one);
    $scene_two = 0;
    $fault_mgo->GetAllList(['shop_id'=>$shop_id,'deal_status'=>DealStatus::DEAL],[],$scene_two);
    $order_num = 0;
    $order_status_list = [VendorOrderStatus::PAY,VendorOrderStatus::REFUND];
    $order_mgo->GetListTotal(['shop_id'=>$shop_id,'order_status_list' => $order_status_list],$order_num);
    $return_num = 0;
    $return_mgo->GetListTotal(['shop_id'=>$shop_id],$return_num);

    $data['record_num']     = $record_num;//补货
    $data['scene_num']      = $scene_one+$scene_two;//现场处理
    $data['stock_deal']     = $stock_deal;//故障处理数
    $data['order_num']      = $order_num;//订单数
    $data['data_num']       = 12;//报表数？？？？
    $data['my_record_num']  = $record_num+$return_num;//记录数
    $resp = (object)array(
        'data' => $data
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

//登录店铺的补货数据信息
function GetReplData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id     = $_['shop_id'];
    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::PARAM_ERR;
    }
    $vendor_mgo =  new VendorMgo\Vendor;
    $goods_mgo  =  new VendorMgo\VendorGoods;
    $return_mgo =  new VendorMgo\ReturnRecord;
    $record_mgo =  new VendorMgo\VendorRecord;
    $stock_num  = 0;
    $out_num    = 0;
    $vendor_mgo->GetListTotal(
        [
        'vendor_status'=> VendorStatus::STOCKOUT,
            'shop_id'  => $shop_id
    ],
        $stock_num);
    $vendor_mgo->GetListTotal(
        [
            'vendor_status'=> VendorStatus::OUT,
            'shop_id'      => $shop_id
        ],
        $out_num);

    $good_all = $goods_mgo->GetListTotal(['shop_id'=>$shop_id]);

    $goods_num   = 0;
    foreach ($good_all as &$v)
    {
        $goods_num  += $v->goods_stock;
    }
    $return_num = 0;
    $return_mgo->GetListTotal(['shop_id'=>$shop_id],$return_num);

    $record_num  = 0;
    $record_mgo->GetListTotal(['shop_id'=>$shop_id],$record_num);

    $data['stock_num']      = $stock_num;
    $data['out_num']        = $out_num;
    $data['goods_num']      = $goods_num;
    $data['return_num']     = $return_num;
    $data['record_all_num'] = $record_num+$return_num;
    $resp = (object)array(
       'data' => $data
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

//运营报表看板图及顶部数据（按周月自定义）
function GetBoarData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id     = $_['shop_id'];
    $time_type   = $_['time_type'];
    $begin_day   = $_['begin_day'];
    $end_day     = $_['end_day'];

    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::PARAM_ERR;
    }

    $stat_order = new VendorMgo\StatVendorOrder;
    $data       = [];

    if($time_type == 1)
    {
        $begin_day  = date('Ymd',time()-6*24*60*60);
        $end_day    = date('Ymd',time());
        //本周数据
        $stat_order->QueryByDayAll(
            [
                'shop_id'   => $shop_id,
                'begin_day' => $begin_day,
                'end_day'   => $end_day,
            ],
            $to_all
        );
        //上周数据
        $stat_order->QueryByDayAll(
            [
                'shop_id'   => $shop_id,
                'begin_day' => $begin_day-6*24*60*60,
                'end_day'   => $begin_day,
            ],
            $yes_all
        );
        $data['money']          = $to_all['all_money'];//今日金额
        $data['num']            = $to_all['order_num'];//今日单数
        $data['compare_money']  = ($to_all['all_money']/$yes_all['all_money'])*100;//较昨日金额比
        $data['pre_money']      = $to_all['all_money']/$to_all['order_num'];//人均
    }elseif ($time_type == 2)
    {
        $begin_day  = date('Ymd',time()-30*24*60*60);
        $end_day    = date('Ymd',time());
        $stat_order->QueryByDayAll(
            [
                'shop_id'   => $shop_id,
                'begin_day' => $begin_day,
                'end_day'   => $end_day,
            ],
            $to_all
        );
        $stat_order->QueryByDayAll(
            [
                'shop_id'   => $shop_id,
                'begin_day' => $begin_day-30*24*60*60,
                'end_day'   => $begin_day,
            ],
            $yes_all
        );
        $data['money']          = $to_all['all_money'];
        $data['num']            = $to_all['order_num'];
        $data['compare_money']  = ($to_all['all_money']/$yes_all['all_money'])*100;
        $data['pre_money']      = $to_all['all_money']/$to_all['order_num'];

    }else{
        //自定义数据
        $stat_order->QueryByDayAll(
            [
                'shop_id'   => $shop_id,
                'begin_day' => $begin_day,
                'end_day'   => $end_day,
            ],
            $today_all
        );
        $data['money']          = $today_all['all_money'];//今日金额
        $data['num']            = $today_all['order_num'];//今日单数
        $data['compare_money']  = '自定义无法定义算比例,呵呵';//较昨日金额比<<<<<无法计算？？？？？
        $data['pre_money']      = $today_all['all_money']/$today_all['order_num'];//人均
    }

   $list = $stat_order->QueryByDayAll(
        [
            'shop_id'   => $shop_id,
            'begin_day' => $begin_day,
            'end_day'   => $end_day,
        ]
    );
   $sort = GetSortData($shop_id, [
       'begin_day' => $begin_day,
       'end_day'   => $end_day,
   ]);
    $resp = (object)array(
        'data'          => $data,
        'list'          => $list,
        'sort'          => $sort,
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//运营报表看板图及顶部数据（按今日）
function GetTodayData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id     = $_['shop_id'];
    $time_cut    = (int)$_['time_cut'];//以几个小时为一个时间段目前只能接受整数
    if(!$shop_id)
    {
        LogDebug('no shop id');
        return errcode::PARAM_ERR;
    }

    //今日划分的时间段
    $cut_num    = 24/$time_cut;//分成了多少个时间段
    $date       = date('Y-m-d ');//今天的日期
    for ($i = 0; $i < $cut_num; $i++)
    {
        $date_one   = $date.($i*$time_cut).':00:00';//第一个时间戳00:00:00
        $date_other = $date.(($i+1)*$time_cut).':00:00';
        $time_one   = strtotime($date_one);
        $time_other = strtotime($date_other);
        $time_arr[] = [$time_one,$time_other];
    }

    //获取今日订单数据
    $order_status_list = [VendorOrderStatus::REFUND,VendorOrderStatus::PAY];
    $t                 = time();
    $start_time        = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));//当天凌晨(时间戳)
    $end_time          = mktime(24,00,00,date("m",$t),date("d",$t),date("Y",$t));//当天结束(时间戳)

    $order_mgo   = new VendorMgo\VendorOrder;
    $order_list  = $order_mgo->GetHoursData(
        [
            'shop_id'            => $shop_id,
            'begin_day'          => $start_time,
            'end_day'            => $end_time,
            'order_status_list'  => $order_status_list,
        ]
    );

    $all = [];
    foreach ($time_arr as &$t)
    {
        $info = (object)[];
      foreach ($order_list as &$v)
      {

          if ($t[0] < $v->pay_time && $v->pay_time <= $t[1])
          {

              $info->all_money += $v->paid_price;
              $info->order_num ++;
              foreach ($v->goods_list as &$g)
              {
                  $info->goods_num += $g->goods_num;
              }
          }
      }
        array_push($all,[
            'time' => date('H:i',$t[1]),
            'list' => $info
        ]);

    }

    $ys_order_list  = $order_mgo->GetHoursData(
        [
            'shop_id'            => $shop_id,
            'begin_day'          => $start_time-24*60*60,
            'end_day'            => $end_time-24*60*60,
            'order_status_list'  => $order_status_list,
        ]
    );
    $ye   = [];
    $data = [];
    foreach ($ys_order_list as &$y)
    {
        $ye['money'] += $y->paid_price;
    }

    foreach ($order_list as &$o)
    {
        $data['money'] += $o->paid_price;
        $data['num']   ++;
        $data['pre_money']   = $data['money']/$data['num'];
    }

       $data['compare_money']  = ($data['money']/$ye['money'])*100;
    $sort = GetSortData($shop_id, [
        'day' => date('Ymd',time()),
    ]);
    $resp = (object)array(
        'data'      => $data,
        'time_list' => $all,
        'sort'      => $sort

    );
    LogInfo("--ok--");
    return 0;
}

//运营报表排行榜
function GetSortData($shop_id, $filter=null)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $begin_day = $filter['begin_day'];
    $end_day   = $filter['end_day'];
    $day       = $filter['day'];

    $stat_vendor = new VendorMgo\StatVendor;
    $stat_goods  = new VendorMgo\StatVendorGoods;
    $vendor_mgo  = new VendorMgo\Vendor;
    $goods_mgo   = new VendorMgo\VendorGoods;
    //商品数量
    $goods_list  = $stat_goods->GetList([
        'shop_id'   => $shop_id,
        'begin_day' => $begin_day,
        'end_day'   => $end_day,
        'day'       => $day
    ]);
    $goods_all      = [];
    $new_goods_list = [];
    foreach ($goods_list as &$g)
    {
        if(isset($goods_all[$g->vendor_goods_id]))
        {
            $goods_all[$g->vendor_goods_id]->all_money += $g->all_money;
            $goods_all[$g->vendor_goods_id]->all_num   += $g->all_num;
        }
        else
        {
            $goods_all[$g->vendor_goods_id] = $g;
        }
    }

    foreach (array_values($goods_all) as &$ga)
    {
        $goods_info                = $goods_mgo->GetVendorGoodsById($ga->vendor_goods_id);
        $goods['goods_name']       = $goods_info->vendor_goods_name;
        $goods['vendor_goods_id']  = $ga->vendor_goods_id;
        $goods['all_money']        = $ga->all_money;
        $goods['all_num']          = $ga->all_num;
        array_push($new_goods_list,$goods);
    }


    //设备销售金额
    $vendor_list = $stat_vendor->GetList([
        'shop_id'   => $shop_id,
        'begin_day' => $begin_day,
        'end_day'   => $end_day,
        'day'       => $day,
    ]);
    $vendor_all      = [];
    $new_vendor_list = [];
    foreach ($vendor_list as $key=>&$v)
    {
        if(isset($vendor_all[$v->vendor_id]))
        {
            $vendor_all[$v->vendor_id]->all_money += $v->all_money;
        }
        else
        {
            $vendor_all[$v->vendor_id] = $v;
        }
    }

    foreach (array_values($vendor_all) as &$va)
    {
        $vendor_info         = $vendor_mgo->QueryById($va->vendor_id);
        $new['vendor_name']  = $vendor_info->vendor_name;
        $new['vendor_num']   = $vendor_info->vendor_num;
        $new['vendor_id']    = $va->vendor_id;
        $new['all_money']    = $va->all_money;
        array_push($new_vendor_list,$new);
    }

    $vendor_date = array_column($new_vendor_list, 'all_money');
    array_multisort($vendor_date,SORT_DESC,$new_vendor_list);
    $vendor_top = array_slice($new_vendor_list,0,5);   //取金额最多的前5

    $goods_date = array_column($new_goods_list, 'all_money');
    array_multisort($goods_date,SORT_DESC,$new_goods_list);
    $goods_top = array_slice($new_goods_list,0,5);   //取数量最多的前5
    $sort = [];
    $sort['vendor_list'] = array_values($vendor_top);
    $sort['goods_list']  = array_values($goods_top);

    return $sort;
}
$ret = -1;
$resp = (object)array();
if(isset($_["get_date_board"]))
{
    $ret = GetBoarData($resp);
}
elseif(isset($_["get_home_data"]))
{
    $ret = GetHomeData($resp);
}
elseif(isset($_["get_repl_data"]))
{
    $ret = GetReplData($resp);
}
elseif(isset($_["get_today_data"]))
{
    $ret = GetTodayData($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);

