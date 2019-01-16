<?php
/*
 * 菜品分享表
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("/www/shop.sailing.com/php/db_pool.php");


class ShareEntry
{
    public $id          = null;          //分享id
    public $customer_id = null;          //客户id
    public $food_id     = null;          //餐品id
    public $shop_id     = null;          //店铺id
    public $ctime       = null;          //分享时间（时间戳，秒数，按需要转为可读形式，如2017-09-04 19:03）
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
        $this->id          = $cursor['id'];
        $this->customer_id = $cursor['customer_id'];
        $this->food_id     = $cursor['food_id'];
        $this->shop_id     = $cursor['shop_id'];
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

class Share
{
    private function Tablename()
    {
        return 'share';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            "id"        => (string)$info->id
        );
        $set = array(
            "id"    => (string)$info->id,
            'ctime' => time()
        );
        
        if (null !== $info->customer_id) {
            $set["customer_id"] = (string)$info->customer_id;
        }
        if (null !== $info->food_id) {
            $set["food_id"] = (string)$info->food_id;
        }
        if (null !== $info->shop_id) {
            $set["shop_id"] = (string)$info->shop_id;
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

    //通过餐品id找分享总数
    public function GetFoodShareCount($food_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'food_id' => (string)$food_id,
            'delete' =>['$ne'=>1]
        ];
        $cursor = $table->count($cond);
        return $cursor;
    }

}

?>