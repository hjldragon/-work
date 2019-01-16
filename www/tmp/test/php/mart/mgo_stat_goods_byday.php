<?php
/*
 * [Rocky 2017-06-22 02:36:53]
 * 商品统计操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class StatGoodsEntry extends BaseInfo
{
    public $day         = null;     // 日期(如: int(20170622) )
    public $goods_id    = null;     // 商品ID
    public $sold_num    = null;     // 售出数
    public $lastmodtime = null;     // 数据最后修改时间
    public $delete      = null;     // 0:正常, 1:已删除

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
        $this->day         = $cursor['day'];
        $this->goods_id    = $cursor['goods_id'];
        $this->sold_num    = $cursor['sold_num'];
        $this->lastmodtime = $cursor['lastmodtime'];
        $this->delete      = $cursor['delete'];
    }
}

class StatGoods extends MgoBase
{
    private function Tablename()
    {
        return 'stat_goods_byday';
    }

    public function SellNumAdd($goods_id, $day, $num)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'goods_id' => (string)$goods_id,
            'day'      => (int)$day,
            'delete'   => ['$ne'=>1]
        );
        $value = array(
            '$set' => array(
                'lastmodtime' => time(),
            ),
            '$inc' => array(
                'sold_num' => (int)$num
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

    public function GetGoodsStatByDay($goods_id, $day)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'goods_id' => (string)$goods_id,
            'day'      => (int)$day,
            'delete'   => ['$ne'=>1]
        ];
        $cursor = $table->findOne($cond);
        return new StatGoodsEntry($cursor);
    }

    public function GetGoodsStatByTime($goods_id, $start, $day)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'   => ['$ne'=>1],
            'goods_id' => (string)$goods_id,
            'day' => [
                '$gte' => (int)$start,
                '$lte' => (int)$day
            ]
        ];
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'           => null,
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
        $goods_id = $filter['goods_id'];
        if($goods_id)
        {
            $cond['goods_id'] = (string)$goods_id;
        }
        //
        $goods_id_list = $filter['goods_id_list'];
        if($goods_id_list)
        {
            foreach($goods_id_list as $i => &$item)
            {
                $item = (string)$item;
            }
            $cond['goods_id'] = ['$in' => $goods_id_list];
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
        return StatGoodsEntry::ToList($cursor);
    }
}


?>
