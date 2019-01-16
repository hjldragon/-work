<?php
/*
 * [Rocky 2017-06-22 02:36:53]
 * 用户评价统计操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class StatEvaluateEntry
{
    public $day         = null;     //日期(如: int(20170622) )
    public $customer_id = null;     //客户id
    public $food_id     = null;     //餐品表
    public $order_id    = null;     //订单id
    public $star_num    = null;     //评价星数
    public $content     = null;     //评论内容
    public $ctime       = null;     //评价的时间（时间戳，秒数，按需要转为可读形式，如2017-09-04 19:03）
    public $delete      = null;     //0:正常, 1:已删除

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
        $this->customer_id = $cursor['customer_id'];
        $this->food_id     = $cursor['food_id'];
        $this->order_id    = $cursor['order_id'];
        $this->star_num    = $cursor['star_num'];
        $this->content     = $cursor['content'];
        $this->ctime       = $cursor['ctime'];
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

class StatEvaluate
{
    private function Tablename()
    {
        return 'stat_evaluate_byday';
    }

    public function SellNumAdd($shop_id, $food_id, $day, $num)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
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
        if($shop_id)
        {
            $cond['shop_id'] = (string)$shop_id;
        }
        //
//        $food_id = $filter['food_id'];
//        if($food_id)
//        {
//            $cond['food_id'] = (string)$food_id;
//        }
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
        $end_day   = $filter['end_day'];
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
