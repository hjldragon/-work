<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 餐桌位置表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class SeatEntry
{
    public $seat_id = null;     // 餐桌位置id
    public $name = null;        // 餐位名称
    public $seat_size = null;   // 餐桌位置大小（可以坐几个人）
    public $price = null;       // 每人餐位费
    public $shop_id = null;     // 餐馆id
    public $lastmodtime = null; // 数据最后修改时间
    public $delete = null;      // 0:正常, 1:已删除

    function __construct($cursor = null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    public function FromMgo($cursor)
    {
        if (!$cursor) {
            return;
        }
        $this->seat_id = $cursor['seat_id'];
        $this->name = $cursor['name'];
        $this->seat_size = $cursor['seat_size'];
        $this->price = $cursor['price'];
        $this->shop_id = $cursor['shop_id'];
        $this->lastmodtime = $cursor['lastmodtime'];
        $this->delete = $cursor['delete'];
    }

    public static function ToList($cursor)
    {
        $list = [];
        foreach ($cursor as $item) {
            $entry = new self($item);
            array_push($list, $entry);
        }
        return $list;
    }

}

class Seat
{
    private function Tablename()
    {
        return 'seat';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'seat_id' => (string)$info->seat_id
        );

        $set = array(
            "seat_id" => (string)$info->seat_id,
            "lastmodtime" => time()
        );

        if (null !== $info->shop_id) {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if (null !== $info->seat_id) {
            $set["seat_id"] = (string)$info->seat_id;
        }
        if (null !== $info->name) {
            $set["name"] = (string)$info->name;
        }
        if (null !== $info->seat_size) {
            $set["seat_size"] = (int)$info->seat_size;
        }
        if (null !== $info->price) {
            $set["price"] = (float)$info->price;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
        }
        $value = array(
            '$set' => $set
        );
        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true]);
            var_dump($ret);
            LogDebug("ret:" . $ret["ok"]);
        } catch (MongoCursorException $e) {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function BatchDelete($seat_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach ($seat_id_list as $i => &$item) {
            $item = (string)$item;
        }

        $set = array(
            "delete" => 1,
            "lastmodtime" => time()
        );
        $value = array(
            '$set' => $set
        );
        $cond = [
            'seat_id' => ['$in' => $seat_id_list]
        ];
        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true, 'multiple' => true]);
            LogDebug("ret:" . $ret["ok"]);
        } catch (MongoCursorException $e) {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function GetSeatById($seat_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'seat_id' => (string)$seat_id,
        ];

        $cursor = $table->findOne($cond);

        return new SeatEntry($cursor);
    }

    public function GetList($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' => ['$ne' => 1],
            'shop_id' => (string)$shop_id
        ];
        $cursor = $table->find($cond, ["_id" => 0])->sort(["seat_id" => 1]);
        return SeatEntry::ToList($cursor);
    }
}


?>
