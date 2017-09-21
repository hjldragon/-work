<?php
/*
 * 评论表
 */
declare(encoding='UTF-8');

namespace DaoMongodb;
require_once("db_pool.php");


class EvaluationEntry
{
    public $id = null;           //评论id
    public $customer_id = null;  //客户id
    public $food_id = null;      //餐品表
    public $order_id = null;     //客户头像
    public $content = null;      //评论内容
    public $ctime = null;        // 评价的时间（时间戳，秒数，按需要转为可读形式，如2017-09-04 19:03）
    public $is_good = null;      //当前餐品是否被点赞（1:被点赞, 0或不存存:不被点赞）

    function __construct($cursor = null)
    {

        $this->FromMgo($cursor);
    }

    public function FromMgo($cursor)
    {
        if (!$cursor) {
            return;
        }
        $this->id = $cursor['id'];
        $this->customer_id = $cursor['customer_id'];
        $this->food_id = $cursor['food_id'];
        $this->order_id = $cursor['order_id'];
        $this->content = $cursor['content'];
        $this->ctime = $cursor['ctime'];
        $this->is_good = $cursor['is_good'];
    }

    public static function ToList($cursor)
    {
        $list = array();
        foreach ($cursor as $item) {
            $entry = new self($item);
            array_push($list, $entry);
        }
        return $list;
    }
}

class Evaluation
{
    private function Tablename()
    {
        return 'evaluation';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'id' => (string)$info->id
        );
        $set = array(
            'id' => (string)$info->id,
        );

        if (null !== $info->id) {
            $set["id"] = (string)$info->id;
        }
        if (null !== $info->customer_id) {
            $set["customer_id"] = (string)$info->customer_id;
        }
        if (null !== $info->food_id) {
            $set["food_id"] = (string)$info->food_id;
        }
        if (null !== $info->order_id) {
            $set["order_id"] = (string)$info->order_id;
        }
        if (null !== $info->content) {
            $set["content"] = (string)$info->content;
        }
        if (null !== $info->ctime) {
            $set["ctime"] = (int)$info->ctime;
        }
        if (null !== $info->is_good) {
            $set["is_good"] = (string)$info->is_good;
        }
        $value = array(
            '$set' => $set
        );
        try {
            $ret = $table->update($cond, $value, ['upsert' => true]);
            LogDebug("ret:" . json_encode($ret));
        } catch (\MongoCursorException $e) {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }

        return 0;
    }

    //根据餐品id来获取所有数据
    public function GetFoodIdByList($food_id)
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'food_id' => (string)$food_id,
        ];

        $field["_id"] = 0;
        $cursor = $table->find($cond, $field);

        return EvaluationEntry::ToList($cursor);
    }

    //通过餐品id找出所有的评论总条数
    public function GetFoodAllCount($food_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'food_id' => (string)$food_id,
        ];
        $set = [
            'skip' => 10,
            'limit' => 5,
        ];
        $cursor = $table->find($cond, $set)->count(true);

        return $cursor;
    }

    //找出所有的点赞评论条数
    public function GetFoodGoodAllCount($food_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'food_id' => (string)$food_id,
            'is_good' => (string)1,
        ];

        $set = [
            'skip' => 10,
            'limit' => 5,
        ];
        $cursor = $table->find($cond, $set)->count(true);

        return $cursor;
    }
}

?>