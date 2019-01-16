<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 *售货机看板信息
 */
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/vendor/mgo_vendor.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_fault_deal.inc");
require_once("/www/public.sailing.com/php/vendor/mgo_stat_vendor_order_byday.inc");
require_once("/www/public.sailing.com/php/page_util.php");
use Pub\Vendor\Mongodb as VendorMgo;

//售货机顶部看板
function GetTopData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = $_['shop_id'];
    $vendor  = new VendorMgo\Vendor;
    $fault   = new VendorMgo\Fault;

    $total          = 0;//设备总数
    $normal_total   = 0;//正常总数<<<<产品没有说明
    $stockout_total = 0;//缺货总数
    $deal_total     = 0;//待处理总数

    $vendor->GetListTotal(['shop_id'=>$shop_id], $total);
    $vendor->GetListTotal(['shop_id'=>$shop_id,'vendor_status'=>VendorStatus::NORMAL], $normal_total);
    $vendor->GetListTotal(['shop_id'=>$shop_id,'vendor_status'=>VendorStatus::STOCKOUT], $stockout_total);
    $fault->GetAllList(['is_deal'=> 0],[],$deal_total);

    $resp = (object)array(
        'total'           => $total,
        'normal_total'    => $normal_total,
        'stockout_total'  => $stockout_total,
        'deal_total'      => $deal_total
    );

    LogInfo("--ok--");
    return 0;
}
//售货机运营报表看板图
function GetBoarData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id     = $_['shop_id'];
    $day         = $_['day'];
    $begin_day   = $_['begin_day'];
    $end_day     = $_['end_day'];

    $stat_order = new VendorMgo\StatVendorOrder;

    if(!$begin_day)
    {
        $begin_day  = date('Ymd',time()-6*24*60*60);
    }
    if(!$end_day)
    {
        $end_day   = date('Ymd',time());
    }

   $list = $stat_order->QueryByDayAll(
        [
            'shop_id'   => $shop_id,
            'day'       => $day,
            'begin_day' => $begin_day,
            'end_day'   => $end_day,
        ]
    );


    $stat_order->QueryByDayAll(
        [
            'shop_id'   => $shop_id,
            'day'       => date('Ymd',time()),
        ],
        $today_all
    );
    $stat_order->QueryByDayAll(
        [
            'shop_id'   => $shop_id,
            'day'       => date('Ymd',time()-24*60*60),
        ],
        $yesday_all
    );
    $today_money          = $today_all['all_money'];//今日金额
    $today_num            = $today_all['order_num'];//今日单数
    $yesday_compare_money = ($today_money/$yesday_all['all_money'])*100;//较昨日金额比
    $yesday_compare_num    = ($today_num/$yesday_all['order_num'])*100;//较昨日单数比

    $stat_order->QueryByDayAll(
        [
            'shop_id'   => $shop_id,
            'begin_day' => date('Ymd',time()-6*24*60*60),
            'end_day'   => date('Ymd',time()),
        ],
        $toweek_all
    );
    $stat_order->QueryByDayAll(
        [
            'shop_id'   => $shop_id,
            'begin_day' => date('Ymd',time()-13*24*60*60),
            'end_day'   => date('Ymd',time()-6*24*60*60),
        ],
        $yesweek_all
    );
    $toweek_money          = $toweek_all['all_money'];//今日金额
    $toweek_num            = $toweek_all['order_num'];//今日单数
    $yesweek_compare_money = ($toweek_money/$yesweek_all['all_money'])*100;//较上周金额比
    $yesweek_compare_num   = ($toweek_num/$yesweek_all['order_num'])*100;//较上周单数比

    $stat_order->QueryByDayAll(
        [
            'shop_id'   => $shop_id,
            'begin_day' => date('Ymd',time()-30*24*60*60),
            'end_day'   => date('Ymd',time()),
        ],
        $tomoth_all
    );
    $stat_order->QueryByDayAll(
        [
            'shop_id'   => $shop_id,
            'begin_day' => date('Ymd',time()-60*24*60*60),
            'end_day'   => date('Ymd',time()-30*24*60*60),
        ],
        $yesmoth_all
    );
    $tomoth_money          = $tomoth_all['all_money'];//今日金额
    $tomoth_num            = $tomoth_all['order_num'];//今日单数
    $yesmoth_compare_money = ($tomoth_money/$yesmoth_all['all_money'])*100;//较上周金额比
    $yesmoth_compare_num   = ($tomoth_num/$yesmoth_all['order_num'])*100;//较上周单数比

    $data = [];
    $data['today_money']          = $today_money;
    $data['today_num']            = $today_num;
    $data['yesday_compare_money'] = $yesday_compare_money;
    $data['yesday_compare_num']   = $yesday_compare_num;

    $data['toweek_money']          = $toweek_money;
    $data['toweek_num']            = $toweek_num;
    $data['yesweek_compare_money'] = $yesweek_compare_money;
    $data['yesweek_compare_num']   = $yesweek_compare_num;

    $data['tomoth_money']          = $tomoth_money;
    $data['tomoth_num']            = $tomoth_num;
    $data['yesmoth_compare_money'] = $yesmoth_compare_money;
    $data['yesmoth_compare_num']   = $yesmoth_compare_num;
    $resp = (object)array(
        'list'  => $list,
        'data'  => $data
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
//售货机运营报表分布图
function GetMapData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $province = $_['province'];
    $shop_id  = $_['shop_id'];
    $vendor   = new VendorMgo\Vendor;
    $list     = $vendor->GetListTotal(['shop_id'=>$shop_id,'province'=>$province], $total);

    $all = [];
    foreach ($list as &$v)
    {
        $num = 0;
        if($v->province)
        {
            $num = 1;
        }
        $info['province'] = $v->province;
        $info['num']      = $num;
        array_push($all,$info);
    }

    $new = [];
    foreach ($all as $a)
    {
        if(isset($new[$a['province']]))
        {
           $new[$a['province']]['num'] += $a['num'];
        }
        else
        {
            $new[$a['province']] = $a;
        }
    }

    $resp = (object)array(
        'list'     =>  array_values($new),
    );

    LogInfo("--ok--");
    return 0;
}
//获取选择店铺的首页数据<<<<<<公众号
function GetShopHomeData(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $shop_id  = $_['shop_id'];
    $province = $_['province'];
    $vendor   = new VendorMgo\Vendor;
    $list     = $vendor->GetListTotal(['shop_id'=>$shop_id,'province'=>$province], $total);

    $all = [];
    foreach ($list as &$v)
    {
        $num = 0;
        if($v->province)
        {
            $num = 1;
        }
        $info['province'] = $v->province;
        $info['num']      = $num;
        array_push($all,$info);
    }

    $new = [];
    foreach ($all as $a)
    {
        if(isset($new[$a['province']]))
        {
            $new[$a['province']]['num'] += $a['num'];
        }
        else
        {
            $new[$a['province']] = $a;
        }
    }

    $resp = (object)array(
        'list'     =>  array_values($new),
    );

    LogInfo("--ok--");
    return 0;
}
//二维数组含对象去重
function ArrayUnsetT($arr, $key)
{
    //建立一个目标数组
    $res = array();
    foreach ($arr as $value) {
        //查看有没有重复项
        if (isset($res[$value->$key])) {
            //有：销毁
            unset($value->$key);
        } else {
            $res[$value->$key] = $value;
        }
    }
    return $res;
}
$ret = -1;
$resp = (object)array();
if(isset($_["get_top_data"]))
{
    $ret = GetTopData($resp);
}elseif(isset($_["get_date_board"]))
{
    $ret = GetBoarData($resp);
}elseif(isset($_["get_date_map"]))
{
    $ret = GetMapData($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}
\Pub\PageUtil::HtmlOut($ret, $resp);
