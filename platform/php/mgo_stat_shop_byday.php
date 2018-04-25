<?php
/*
 * [Rocky 2017-06-22 02:36:53]
 * 店铺统计操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class StatShopEntry
{
    public $day             = null;  // 日期(如: int(20170622) )
    public $shop_id         = null;  // 店铺ID
    public $agent_id        = null;  // 代理商ID
    public $qr_order_num    = null;  // 扫码点餐次数
    public $consume_amount  = null;  // 消费总额
    //public $guest_price     = 0;   // 客单价
    public $customer_num    = null;  // 消费者数
    public $platform_id     = null;  // 平台ID
    public $new_cus_num     = null;  // 新增客户数
    public $active_cus_num  = null;  // 活跃者数
    public $lastmodtime     = null;  // 数据最后修改时间
    public $delete          = null;  // 0:正常, 1:已删除

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
        $this->day            = $cursor['day'];
        $this->shop_id        = $cursor['shop_id'];
        $this->agent_id       = $cursor['agent_id'];
        $this->platform_id    = $cursor['platform_id'];
        $this->new_cus_num    = $cursor['new_cus_num'];
        $this->active_cus_num = $cursor['active_cus_num'];
        $this->consume_amount = $cursor['consume_amount'];
        $this->customer_num   = $cursor['customer_num'];
        $this->qr_order_num   = $cursor['qr_order_num'];
        $this->lastmodtime    = $cursor['lastmodtime'];
        $this->delete         = $cursor['delete'];
    }

    public static function ToList($cursor)
    {
        $list = [];
        foreach($cursor as $item)
        {
            $entry = new self($item);
            array_push($list, $entry);
        }
        return $list;
    }
}

class StatShop
{
    private function Tablename()
    {
        return 'stat_shop_byday';
    }

    public function SellNumAdd($shop_id, $day, $num=[])
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'shop_id'     => (string)$shop_id,
            'day'         => (int)$day,
            'platform_id' => 1,
        );
        $value = array(
            '$set' => array(
                'lastmodtime' => time(),
                'delete'      => 0
            ),
            '$inc' => array(
                'qr_order_num'    => (int)$num['qr_order_num'],
                'active_cus_num'  => (int)$num['active_cus_num'],
                'new_cus_num'     => (int)$num['new_cus_num'],
                'customer_num'    => (int)$num['customer_num'],
                'consume_amount'  => (float)$num['consume_amount'],
            ),
        );
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

    public function GetFoodStatByDay($shop_id, $day)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id' => (string)$shop_id,
            'day' => (int)$day,
        ];
        $cursor = $table->findOne($cond);
        return new StatShopEntry($cursor);
    }

    public function GetFoodStatByTime($shop_id, $start, $day)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id' => (string)$shop_id,
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
        //
        $shop_id = $filter['shop_id'];
        if($shop_id )
        {
            $cond['shop_id'] = (string)$shop_id;
        }
        //
        $shop_id = $filter['shop_id'];
        if($shop_id)
        {
            $cond['shop_id'] = (string)$shop_id;
        }
        //
        $shop_id_list = $filter['shop_id_list'];
        if($shop_id_list)
        {
            foreach($shop_id_list as $i => &$item)
            {
                $item = (string)$item;
            }
            $cond['shop_id'] = ['$in' => $shop_id_list];
        }
        //
        $begin_day = $filter['begin_day'];
        $end_day = $filter['end_day'];
        if($begin_day && $end_day)
        {
            $cond['day'] = [
                '$gte' => (int)$begin_day,
                '$lte' => (int)$end_day
            ];
        }

        $cursor = $table->find($cond, ["_id"=>0]);
        return StatShopEntry::ToList($cursor);
    }

    public function AgentQueryByDayAll($filter=null ,$agent_id, &$num_all=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'agent_id' => (string)$agent_id,
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
        //聚合条件算出:所有商户总数统计
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'            => null,
                    'qr_order_num'   => ['$sum' => '$qr_order_num'],
                    'consume_amount' => ['$sum' => '$consume_amount'],
                    'customer_num'   => ['$sum' => '$customer_num'],
                    'new_cus_num'    => ['$sum' => '$new_cus_num'],
                    'active_cus_num' => ['$sum' => '$active_cus_num'],
                ],
            ],
        ];
        //LogDebug($pipe);
        $all_list = $table->aggregate($pipe);
        if ($all_list['ok'] == 1) {
            $num_all = $all_list['result'][0];
        } else {
            $num_all = null;
        }
        $ret = $table->find($cond);
        return StatShopEntry::ToList($ret);
    }
}


?>
