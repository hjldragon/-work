<?php
/*
 * [Rocky 2017-06-22 02:36:53]
 * 平台统计操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class StatPlatformEntry
{
    public $day                     = null;  // 日期(如: int(20170622) )
    public $platform_id             = null;  // 平台ID
    public $industry_agent_num      = null;  // 新增行业代理商数
    public $region_agent_num        = null;  // 新增区域代理商数
    public $consume_amount          = null;  // 消费额
    public $customer_num            = null;  // 点餐消费者人数
    public $qr_order_num            = null;  // 扫码点餐次数
    public $active_cus_num          = null;  // 活跃者数
    public $new_shop_num            = null;  // 新增店铺数
    public $industry_shop_num       = null;  // 新增未签约行业店铺数
    public $region_shop_num         = null;  // 新增未签约区域店铺数
    public $sign_industry_shop_num  = null;  // 新增签约行业店铺数
    public $sign_region_shop_num    = null;  // 新增签约区域店铺数
    public $new_cus_num             = null;  // 新增消费者数
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
        $this->day                = $cursor['day'];
        $this->platform_id        = $cursor['platform_id'];
        $this->industry_agent_num = $cursor['industry_agent_num'];
        $this->region_agent_num   = $cursor['region_agent_num'];
        $this->consume_amount     = $cursor['consume_amount'];
        $this->customer_num       = $cursor['customer_num'];
        $this->qr_order_num       = $cursor['qr_order_num'];
        $this->new_shop_num       = $cursor['new_shop_num'];
        $this->new_cus_num        = $cursor['new_cus_num'];
        $this->industry_shop_num  = $cursor['industry_shop_num'];
        $this->region_shop_num    = $cursor['region_shop_num'];
        $this->active_cus_num     = $cursor['active_cus_num'];
        $this->lastmodtime        = $cursor['lastmodtime'];
        $this->delete             = $cursor['delete'];
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

class StatPlatform
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
                'industry_agent_num' => (int)$num['industry_agent_num'],
                'region_agent_num'   => (int)$num['region_agent_num'],
                'consume_amount'     => (float)$num['consume_amount'],
                'customer_num'       => (int)$num['customer_num'],
                'new_shop_num'       => (int)$num['new_shop_num'],
                'qr_order_num'       => (int)$num['qr_order_num'],
                'industry_shop_num'  => (int)$num['industry_shop_num'],
                'region_shop_num'    => (int)$num['region_shop_num'],
                'active_cus_num'     => (int)$num['active_cus_num']
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
        //
        $shop_id = $filter['shop_id'];
        if($shop_id )
        {
            $cond['shop_id'] = (string)$shop_id;
        }
        //
        $platform_id = $filter['platform_id'];
        if($platform_id)
        {
            $cond['platform_id'] = (string)$platform_id;
        }
        //
        $platform_id_list = $filter['platform_id_list'];
        if($platform_id_list)
        {
            foreach($platform_id_list as $i => &$item)
            {
                $item = (string)$item;
            }
            $cond['platform_id'] = ['$in' => $platform_id_list];
        }
        //
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
        //聚合条件算出:每个代理商的商户总数
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'                    => null,
                    'new_shop_num'           => ['$sum' => '$new_shop_num'],
                    'consume_amount'         => ['$sum' => '$consume_amount'],
                    'customer_num'           => ['$sum' => '$customer_num'],
                    'new_cus_num'            => ['$sum' => '$new_cus_num'],
                    'active_cus_num'         => ['$sum' => '$active_cus_num'],
                    'industry_shop_num'      => ['$sum' => '$industry_shop_num'],
                    'region_shop_num'        => ['$sum' => '$region_shop_num'],
                    'sign_industry_shop_num' => ['$sum' => '$sign_industry_shop_num'],
                    'sign_region_shop_num'   => ['$sum' => '$sign_region_shop_num'],
                    'qr_order_num'           => ['$sum' => '$qr_order_num'],
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
        return StatPlatformEntry::ToList($ret);
    }
}


?>
