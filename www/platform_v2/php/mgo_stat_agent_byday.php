<?php
/*
 * [Rocky 2017-06-22 02:36:53]
 * 代理商统计操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class StatAgentEntry
{
    public $day            = null;     // 日期(如: int(20170622) )
    public $agent_id       = null;     // 代理商ID
    public $platform_id    = null;     // 平台ID
    public $new_shop_num   = null;     // 店铺数(根据agent_id来区分行业或区域)
    public $sign_shop_num  = null;     // 新增签约店铺数
    public $goods_order_num= null;     // 支付成功的商城订单数
    public $all_paid_price = null;     // 实收商城订单总金额
    public $lastmodtime    = null;     // 数据最后修改时间
    public $delete         = null;     // 0:正常, 1:已删除

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
        $this->day             = $cursor['day'];
        $this->agent_id        = $cursor['agent_id'];
        $this->platform_id     = $cursor['platform_id'];
        $this->new_shop_num    = $cursor['new_shop_num'];
        $this->sign_shop_num   = $cursor['sign_shop_num'];
        $this->goods_order_num = $cursor['goods_order_num'];
        $this->all_paid_price  = $cursor['all_paid_price'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];
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

class StatAgent
{
    private function Tablename()
    {
        return 'stat_agent_byday';
    }

    public function SellAgentNumAdd($agent_id, $day, $num=[])
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'agent_id'    => (string)$agent_id,
            'day'         => (int)$day,
            'platform_id' => (string)1,
        );
        $value = array(
            '$set' => array(
                'lastmodtime' => time(),
            ),
            '$inc' => array(
                'new_shop_num'    => (int)$num['new_shop_num'],
                'goods_order_num' => (int)$num['goods_order_num'],
                'all_paid_price'  => (float)$num['all_paid_price'],
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

    public function GetFoodStatByDay($agent_id, $day)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'agent_id' => (string)$agent_id,
            'day' => (int)$day,
        ];
        $cursor = $table->findOne($cond);
        return new StatAgentEntry($cursor);
    }

    public function GetFoodStatByTime($agent_id, $start, $day)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'agent_id' => (string)$agent_id,
            'day' => [
                '$gte' => (int)$start,
                '$lte' => (int)$day
            ]
        ];
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'             => null,
                    'all_sold_num'    => ['$sum' => '$sold_num'],
                    'all_order_num'   => ['$sum' => '$goods_order_num'],
                    'all_paid_price'  => ['$sum' => '$all_paid_price']
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
        $agent_id = $filter['agent_id'];
        if($agent_id)
        {
            $cond['agent_id'] = (string)$agent_id;
        }
        //
        $agent_id_list = $filter['agent_id_list'];
        if($agent_id_list)
        {
            foreach($agent_id_list as $i => &$item)
            {
                $item = (string)$item;
            }
            $cond['agent_id'] = ['$in' => $agent_id_list];
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
        return StatAgentEntry::ToList($cursor);
    }

    public function QueryById($agent_id,&$num_all=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'agent_id' => (string)$agent_id,
            'delete'   => array('$ne'=>1)
        );
        //聚合条件算出:每个代理商的商户总数
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'               => null,
                    'all_shop_num'      => ['$sum' => '$new_shop_num'],
                    'all_sign_shop_num' => ['$sum' => '$sign_shop_num'],
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
        $ret = $table->findOne($cond);
        return new StatAgentEntry($ret);
    }

    public function GetAgentShopByDay($filter=null ,$agent_id, &$num_all=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'agent_id' => (string)$agent_id,
            'delete'   => array('$ne'=>1)
        );

        if (null != $filter) {
            $day = $filter['day'];
            if (null !== $day) {
                $cond['day'] = (int)$day;
            }
            $platform_id = $filter['platform_id'];
            if (null !== $platform_id) {
                $cond['platform_id'] = (string)$platform_id;
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

        //聚合条件算出:新增店铺总数
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'             => null,
                    'new_shop_num'    => ['$sum' => '$new_shop_num'],
                    'sign_shop_num'   => ['$sum' => '$sign_shop_num'],
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
        return StatAgentEntry::ToList($ret);
    }
}


?>
