<?php
/*
 * [Rocky 2017-06-22 02:36:53]
 * 餐品统计操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class StatFoodEntry
{
    public $day         = null;     // 日期(如: int(20170622) )
    public $food_id     = null;     // 餐桌ID
    public $sold_num    = null;     // 售出数
    public $shop_id     = null;     // 餐馆id
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
        $this->food_id     = $cursor['food_id'];
        $this->sold_num    = $cursor['sold_num'];
        $this->shop_id     = $cursor['shop_id'];
        $this->lastmodtime = $cursor['lastmodtime'];
        $this->delete      = $cursor['delete'];
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

class StatFood
{
    private function Tablename()
    {
        return 'stat_food_byday';
    }

    public function SellNumAdd($shop_id, $food_id, $day, $num)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'food_id' => (string)$food_id,
            'day' => (int)$day,
        );
        $value = array(
            '$set' => array(
                'shop_id' => (string)$shop_id,
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

    public function GetFoodStatByDay($food_id, $day)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'food_id' => (string)$food_id,
            'day' => (int)$day,
        ];
        $cursor = $table->findOne($cond);
        return new StatFoodEntry($cursor);
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
        $food_id = $filter['food_id'];
        if($food_id)
        {
            $cond['food_id'] = (string)$food_id;
        }
        //
        $food_id_list = $filter['food_id_list'];
        if($food_id_list)
        {
            foreach($food_id_list as $i => &$item)
            {
                $item = (string)$item;
            }
            $cond['food_id'] = ['$in' => $food_id_list];
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
        return StatFoodEntry::ToList($cursor);
    }
}


?>
