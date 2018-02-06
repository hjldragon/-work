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
    public $seat_id     = null; // 餐桌位置id
    public $seat_name   = null; // 餐位名称
    public $seat_size   = null; // 餐桌位置大小（可以坐几个人）
    public $price       = null; // 餐位费
    public $shop_id     = null; // 餐馆id
    public $lastmodtime = null; // 数据最后修改时间
    public $delete      = null; // 0:正常, 1:已删除
    public $seat_region = null; // 餐桌区域
    public $seat_type   = null; // 餐桌类型
    public $seat_shape  = null; // 桌形
    public $price_type  = null; // 餐位费结算方式（0:无餐位费,1:按人数,2:固定数,3:餐费百分比）
    public $consume_min = null; // 最低消费
    public $qr_code     = null; // 餐桌二维码

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
        $this->seat_id     = $cursor['seat_id'];
        $this->seat_name   = $cursor['seat_name'];
        $this->seat_size   = $cursor['seat_size'];
        $this->price       = $cursor['price'];
        $this->shop_id     = $cursor['shop_id'];
        $this->lastmodtime = $cursor['lastmodtime'];
        $this->delete      = $cursor['delete'];
        $this->seat_region = $cursor['seat_region'];
        $this->seat_type   = $cursor['seat_type'];
        $this->seat_shape  = $cursor['seat_shape'];
        $this->price_type  = $cursor['price_type'];
        $this->consume_min = $cursor['consume_min'];
        $this->qr_code     = $cursor['qr_code'];
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
            "seat_id"     => (string)$info->seat_id,
            "lastmodtime" => time(),
            'delete'  => ['$ne'=>1],
        );

        if (null !== $info->shop_id) {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if (null !== $info->seat_name){
            $set["seat_name"] = (string)$info->seat_name;
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
        if (null !== $info->seat_region) {
            $set["seat_region"] = (string)$info->seat_region;
        }
        if (null !== $info->seat_type) {
            $set["seat_type"] = (string)$info->seat_type;
        }
        if (null !== $info->seat_shape) {
            $set["seat_shape"] = (string)$info->seat_shape;
        }
        if (null !== $info->price_type) {
            $set["price_type"] = (int)$info->price_type;
        }
        if (null !== $info->consume_min) {
            $set["consume_min"] = (float)$info->consume_min;
        }
        if (null !== $info->qr_code) {
            $set["qr_code"] = (string)$info->qr_code;
        }
        $value = array(
            '$set' => $set
        );
        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true]);

            LogDebug("ret:" . $ret["ok"]);
        } catch (\MongoCursorException $e) {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
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
        $cond = [
            'seat_id' => ['$in' => $seat_id_list],
        ];

        $value = array(
            '$set' => array(
                'delete'  => 1,
                "lastmodtime" => time()
            )
        );

        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true, 'multiple' => true]);
            LogDebug("ret:" . $ret["ok"]);
        } catch (\MongoCursorException $e) {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
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

    public function GetSeatByName($shop_id,$seat_name)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            //'seat_name' => (string)$seat_name,
            'seat_name' => new \MongoRegex("/$seat_name/"),
            'shop_id'   => (string)$shop_id,
            'delete'    => ['$ne' => 1],
        ];

        $cursor = $table->find($cond, ["_id" => 0])->sort(["seat_id" => 1]);
        return SeatEntry::ToList($cursor);
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

    public function GetSeatID($shop_id, $seat_name)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id'     => (string)$shop_id,
            'seat_name'   => (string)$seat_name,
            'delete'      => ['$ne' => 1],
        ];
        $cursor = $table->findOne($cond);
        return new SeatEntry($cursor);
    }
}





?>
