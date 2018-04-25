<?php
/*
 * 点赞收藏表
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class PraiseEntry
{
    //public $id          = null;          //点赞id
    public $type        = null;          //1:点赞，2:收藏
    public $customer_id = null;          //客户id
    public $food_id     = null;          //点赞餐品id
    public $shop_id     = null;          //点赞店铺id
    public $is_praise   = null;          //是否点赞(1点赞)
    public $ctime       = null;          //点赞的时间（时间戳，秒数，按需要转为可读形式，如2017-09-04 19:03）
    public $delete      = null;          //0:正常, 1:已删除

    function __construct($cursor = null)
    {

        $this->FromMgo($cursor);
    }

    public function FromMgo($cursor)
    {
        if (!$cursor) {
            return;
        }
        //$this->id          = $cursor['id'];
        $this->type        = $cursor['type'];
        $this->customer_id = $cursor['customer_id'];
        $this->food_id     = $cursor['food_id'];
        $this->shop_id     = $cursor['shop_id'];
        $this->is_praise   = $cursor['is_praise'];
        $this->ctime       = $cursor['ctime'];
        $this->delete      = $cursor['delete'];
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

class Praise
{
    private function Tablename()
    {
        return 'praise';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            "type"        => (int)$info->type,
            "customer_id" => (string)$info->customer_id,
            "food_id"     => (string)$info->food_id,
            "shop_id"     => (string)$info->shop_id
        );
        $set = array(
            "type"        => (int)$info->type,
            "customer_id" => (string)$info->customer_id,
            "food_id"     => (string)$info->food_id,
            "shop_id"     => (string)$info->shop_id,
            'ctime' => time()
        );
        
        if (null !== $info->is_praise) {
            $set["is_praise"] = (int)$info->is_praise;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
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

    public function GetPraiseById($id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'id' => (string)$id,
            'delete' =>['$ne'=>1]
        );
        $cursor = $table->findOne($cond);
        return new PraiseEntry($cursor);
    }

    
    public function GetPraiseByCustomer($customer_id, $food_id, $shop_id, $type)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            "customer_id" => (string)$customer_id,
            "food_id"     => (string)$food_id,
            "shop_id"     => (string)$shop_id,
            "type"        => (int)$type
        ];
        $field["_id"] = 0;
        $cursor = $table->findOne($cond, $field);
        return new PraiseEntry($cursor);
    }

    //通过餐品id找出所有点赞条数或收藏数
    public function GetFoodAllCount($food_id, $type=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'food_id' => (string)$food_id,
            'type'    => 1,
            'is_praise' => 1,
            'delete' =>['$ne'=>1]
        ];
        if($type)
        {
           $cond['type'] = $type;
        }
        $cursor = $table->count($cond);
        return $cursor;
    }

    //通过店铺id找出所有的点赞条数
    public function GetShopAllCount($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'shop_id' => (string)$shop_id,
            'type'    => 1,
            'is_praise' => 1,
            'delete' =>['$ne'=>1]
        ];
        $cursor = $table->count($cond);
        return $cursor;
    }

    //通过客户id找出所有收藏餐品
    public function GetFoodByCustomerId($customer_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'customer_id' =>(string)$customer_id,
            'type'      => 2,
            'is_praise' => 1,
            'shop_id'   => "",
            'delete'    =>['$ne'=>1]
        ];
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(['ctime'=>-1]);
        return PraiseEntry::ToList($cursor);
    }
}

?>