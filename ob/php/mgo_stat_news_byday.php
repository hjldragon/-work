<?php
/*
 * [Rocky 2017-06-22 02:36:53]
 * 餐品统计操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class StatNewsEntry
{
    public $day         = null;     // 日期(如: int(20170622) )
    public $shop_id     = null;     // 店铺ID
    public $send_num    = null;     // 发送数
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
        $this->send_num    = $cursor['send_num'];
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

class StatNews
{
    private function Tablename()
    {
        return 'stat_news_byday';
    }

    public function SellNumAdd($shop_id, $day, $num)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'shop_id' => (string)$shop_id,
            'day' => (int)$day,
        );
        $value = array(
            '$set' => array(
                'lastmodtime' => time(),
            ),
            '$inc' => array(
                'send_num' => (int)$num
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
        return 0;
    }

    public function GetNewsStatByDay($shop_id, $day)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id' => (string)$shop_id,
            'day' => (int)$day,
        ];
        $cursor = $table->findOne($cond);
        return new StatNewsEntry($cursor);
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
        if($shop_id)
        {
            $cond['shop_id'] = (string)$shop_id;
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
        return StatNewsEntry::ToList($cursor);
    }
}


?>
