<?php
/*
 * [Rocky 2017-06-22 02:36:53]
 * 平台统计操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class StatPlatformEntry extends BaseInfo
{
    public $day                     = null;  // 日期(如: int(20170622) )
    public $platform_id             = null;  // 平台ID
    public $region_consume_amount   = null;  // 区域消费额
    public $industry_consume_amount = null;  // 行业消费额
    public $customer_num            = null;  // 点餐消费者人数
    public $qr_order_num            = null;  // 扫码点餐次数
    public $order_num               = null;  // 订单数
    public $active_cus_num          = null;  // 活跃者数
    public $new_cus_num             = null;  // 新增消费者数
    public $industry_agent_num      = null;  // 新增认证成功行业代理商数
    public $region_agent_num        = null;  // 新增认证成功区域代理商数
    public $no_industry_agent_num   = null;  // 新增未认证成功行业代理商数
    public $no_region_agent_num     = null;  // 新增未认证成功区域代理商数
    public $industry_shop_num       = null;  // 新增认证成功行业店铺数
    public $region_shop_num         = null;  // 新增认证成功区域店铺数
    public $no_industry_shop_num    = null;  // 新增未认证成功行业店铺数
    public $no_region_shop_num      = null;  // 新增未认证成功区域店铺数
    public $region_app_order_num    = null;  // 区域App订单数
    public $region_wx_order_num     = null;  // 区域扫码点餐订单数
    public $region_pad_order_num    = null;  // 区域PAD订单数
    public $region_cash_order_num   = null;  // 区域点餐手机订单数
    public $region_self_order_num   = null;  // 区域自助点餐订单数
    public $region_mini_order_num   = null;  // 区域小程序订单数
    public $industry_app_order_num  = null;  // 行业App订单数
    public $industry_wx_order_num   = null;  // 行业扫码点餐订单数
    public $industry_pad_order_num  = null;  // 行业PAD订单数
    public $industry_cash_order_num = null;  // 行业点餐手机订单数
    public $industry_self_order_num = null;  // 行业自助点餐订单数
    public $industry_mini_order_num = null;  // 行业小程序订单数
    public $industry_goods_num      = null;  // 行业商城单数
    public $region_goods_num        = null;  // 区域商城单数
    public $industry_goods_amount   = null;  // 行业商城实付金额
    public $region_goods_amount     = null;  // 区域商城实付金额
    public $lastmodtime             = null;  // 数据最后修改时间
    public $delete                  = null;  // 0:正常, 1:已删除

    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    public function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->day                     = $cursor['day'];
        $this->platform_id             = $cursor['platform_id'];
        $this->region_consume_amount   = $cursor['region_consume_amount'];
        $this->industry_consume_amount = $cursor['industry_consume_amount'];
        $this->order_num               = $cursor['order_num'];
        $this->customer_num            = $cursor['customer_num'];
        $this->qr_order_num            = $cursor['qr_order_num'];
        $this->new_shop_num            = $cursor['new_shop_num'];
        $this->new_cus_num             = $cursor['new_cus_num'];
        $this->active_cus_num          = $cursor['active_cus_num'];
        $this->industry_agent_num      = $cursor['industry_agent_num'];
        $this->region_agent_num        = $cursor['region_agent_num'];
        $this->no_industry_agent_num   = $cursor['no_industry_agent_num'];
        $this->no_region_agent_num     = $cursor['no_region_agent_num'];
        $this->industry_shop_num       = $cursor['industry_shop_num'];
        $this->region_shop_num         = $cursor['region_shop_num'];
        $this->no_industry_shop_num    = $cursor['no_industry_shop_num'];
        $this->no_region_shop_num      = $cursor['no_region_shop_num'];
        $this->region_app_order_num    = $cursor['region_app_order_num'];
        $this->region_wx_order_num     = $cursor['region_wx_order_num'];
        $this->region_pad_order_num    = $cursor['region_pad_order_num'];
        $this->region_cash_order_num   = $cursor['region_cash_order_num'];
        $this->region_self_order_num   = $cursor['region_self_order_num'];
        $this->region_mini_order_num   = $cursor['region_mini_order_num'];
        $this->industry_app_order_num  = $cursor['industry_app_order_num'];
        $this->industry_wx_order_num   = $cursor['industry_wx_order_num'];
        $this->industry_pad_order_num  = $cursor['industry_pad_order_num'];
        $this->industry_cash_order_num = $cursor['industry_cash_order_num'];
        $this->industry_self_order_num = $cursor['industry_self_order_num'];
        $this->industry_mini_order_num = $cursor['industry_mini_order_num'];
        $this->industry_goods_num      = $cursor['industry_goods_num'];
        $this->region_goods_num        = $cursor['region_goods_num'];
        $this->industry_goods_amount   = $cursor['industry_goods_amount'];
        $this->region_goods_amount     = $cursor['region_goods_amount'];
        $this->lastmodtime             = $cursor['lastmodtime'];
        $this->delete                  = $cursor['delete'];

    }

}

class StatPlatform extends MgoBase
{
    private function Tablename()
    {
        return 'stat_platform_byday';
    }

    public function SellNumAdd($platform_id, $day, $num=[])
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'platform_id' => (string)$platform_id,
            'day'         => (int)$day,
        );
        $value = array(
            '$set' => array(
                'lastmodtime' => time(),
                'delete'      => 0
            ),
            '$inc' => array(
                'region_consume_amount'   => (float)$num['region_consume_amount'],
                'industry_consume_amount' => (float)$num['industry_consume_amount'],
                'customer_num'            => (int)$num['customer_num'],
                'new_shop_num'            => (int)$num['new_shop_num'],
                'qr_order_num'            => (int)$num['qr_order_num'],
                'active_cus_num'          => (int)$num['active_cus_num'],
                'industry_shop_num'       => (int)$num['industry_shop_num'],
                'region_shop_num'         => (int)$num['region_shop_num'],
                'industry_agent_num'      => (int)$num['industry_agent_num'],
                'region_agent_num'        => (int)$num['region_agent_num'],
                'no_industry_shop_num'    => (int)$num['no_industry_shop_num'],
                'no_region_shop_num'      => (int)$num['no_region_shop_num'],
                'no_industry_agent_num'   => (int)$num['no_industry_agent_num'],
                'no_region_agent_num'     => (int)$num['no_region_agent_num'],
                'region_app_order_num'    => (int)$num['region_app_order_num'],
                'region_wx_order_num'     => (int)$num['region_wx_order_num'],
                'region_pad_order_num'    => (int)$num['region_pad_order_num'],
                'region_cash_order_num'   => (int)$num['region_cash_order_num'],
                'region_self_order_num'   => (int)$num['region_self_order_num'],
                'region_mini_order_num'   => (int)$num['region_mini_order_num'],
                'industry_app_order_num'  => (int)$num['industry_app_order_num'],
                'industry_wx_order_num'   => (int)$num['industry_wx_order_num'],
                'industry_pad_order_num'  => (int)$num['industry_pad_order_num'],
                'industry_cash_order_num' => (int)$num['industry_cash_order_num'],
                'industry_self_order_num' => (int)$num['industry_self_order_num'],
                'industry_mini_order_num' => (int)$num['industry_mini_order_num'],
                'industry_goods_num'      => (int)$num['industry_goods_num'],
                'region_goods_num'        => (int)$num['region_goods_num'],
                'industry_goods_amount'   => (int)$num['industry_goods_amount'],
                'region_goods_amount'     => (int)$num['region_goods_amount'],
            ),
        );
      LogDebug($cond);
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true]);
            LogDebug("ret:" . $ret["ok"]);
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return $ret;
    }

    public function GetFoodStatByDay($platform_id, $day)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'platform_id' => (string)$platform_id,
            'day'         => (int)$day,
        ];
        $cursor = $table->findOne($cond);
        return new StatPlatformEntry($cursor);
    }

    public function GetFoodStatByTime($platform_id, $start, $day)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'platform_id' => (string)$platform_id,
            'day' => [
                '$gte' => (int)$start,
                '$lte' => (int)$day
            ]
        ];
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'               => null,
                    'all_sold_num'  => ['$sum' => '$sold_num']
                ],
            ],
        ];
        //LogDebug($pipe);
        $all_list = $table->aggregate($pipe);
        if ($all_list['ok'] == 1) {
            return $all_list['result'][0];
        } else {
            return null;
        }
    }

    public function GetStatList($filter)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'delete'  => ['$ne'=>1]
        ];
        $shop_id = $filter['shop_id'];
        if($shop_id )
        {
            $cond['shop_id'] = (string)$shop_id;
        }

        $platform_id = $filter['platform_id'];

        if($platform_id)
        {
            $cond['platform_id'] = (string)$platform_id;
        }

        $platform_id_list = $filter['platform_id_list'];
        if($platform_id_list)
        {
            foreach($platform_id_list as $i => &$item)
            {
                $item = (string)$item;
            }
            $cond['platform_id'] = ['$in' => $platform_id_list];
        }

        $begin_day = $filter['begin_day'];
        $end_day   = $filter['end_day'];
        if($begin_day && $end_day)
        {
            $cond['day'] = [
                '$gte' => (int)$begin_day,
                '$lte' => (int)$end_day
            ];
        }
        $cursor = $table->find($cond, ["_id"=>0]);
        return StatPlatformEntry::ToList($cursor);
    }

    public function QueryByDayAll($filter=null ,$platform_id, &$num_all=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'platform_id' => (string)$platform_id,
            'delete'      => array('$ne'=>1)
        );

        if (null != $filter) {
            $day = $filter['day'];
            if (null !== $day) {
                $cond['day'] = (int)$day;
            }
            $begin_day = $filter['begin_day'];
            $end_day   = $filter['end_day'];
            if (!empty($begin_day)) {
                $cond['day'] = [
                    '$gte' => (int)$begin_day,
                    '$lte' => (int)$end_day,
                ];
            }
        }
        //聚合条件算出
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'                    => null,
                    'region_consume_amount'  => ['$sum' => '$region_consume_amount'],
                    'industry_consume_amount'=> ['$sum' => '$industry_consume_amount'],
                    'customer_num'           => ['$sum' => '$customer_num'],
                    'industry_shop_num'      => ['$sum' => '$industry_shop_num'],
                    'region_shop_num'        => ['$sum' => '$region_shop_num'],
                    'industry_agent_num'     => ['$sum' => '$industry_agent_num'],
                    'region_agent_num'       => ['$sum' => '$region_agent_num'],
                    'no_industry_shop_num'   => ['$sum' => '$no_industry_shop_num'],
                    'no_region_shop_num'     => ['$sum' => '$no_region_shop_num'],
                    'no_industry_agent_num'  => ['$sum' => '$no_industry_agent_num'],
                    'no_region_agent_num'    => ['$sum' => '$no_region_agent_num'],
                    'region_app_order_num'   => ['$sum' => '$region_app_order_num'],
                    'region_wx_order_num'    => ['$sum' => '$region_wx_order_num'],
                    'region_pad_order_num'   => ['$sum' => '$region_pad_order_num'],
                    'region_cash_order_num'  => ['$sum' => '$region_cash_order_num'],
                    'region_self_order_num'  => ['$sum' => '$region_self_order_num'],
                    'region_mini_order_num'  => ['$sum' => '$region_mini_order_num'],
                    'industry_app_order_num' => ['$sum' => '$industry_app_order_num'],
                    'industry_wx_order_num'  => ['$sum' => '$industry_wx_order_num'],
                    'industry_pad_order_num' => ['$sum' => '$industry_pad_order_num'],
                    'industry_cash_order_num'=> ['$sum' => '$industry_cash_order_num'],
                    'industry_self_order_num'=> ['$sum' => '$industry_self_order_num'],
                    'industry_mini_order_num'=> ['$sum' => '$industry_mini_order_num'],
                    'industry_goods_num'     => ['$sum' => '$industry_goods_num'],
                    'region_goods_num'       => ['$sum' => '$region_goods_num'],
                    'region_goods_amount'    => ['$sum' => '$region_goods_amount'],
                    'industry_goods_amount'  => ['$sum' => '$industry_goods_amount']
                ],
            ],
        ];
        $all_list = $table->aggregate($pipe);
        if ($all_list['ok'] == 1) {
            $num_all = $all_list['result'][0];
        } else {
            $num_all = null;
        }
        $ret = $table->find($cond);
        return StatPlatformEntry::ToList($ret);
    }
}


?>
