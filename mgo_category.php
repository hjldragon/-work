<?php
/*
 * [Rocky 2017-04-25 19:29:49]
 * 餐品类别表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class CategoryEntry
{
    public $category_id = null;      // 类别id
    public $category_name = null;    // 类别名
    public $shop_id = null;          // 餐馆id
    public $printer_id = null;       // 对应的打印机
    public $lastmodtime = null;      //最后修改的时间
    public $delete = null;           //是否删除(1删除,0未删除)
    public $type = null;             //类别的类型(1:一般类品分类，2:配件)
    public $food_id_list = null;     //菜单分类下面的餐品id列表
    public $opening_time = null;     //菜单显示时间段

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
        $this->category_id = $cursor['category_id'];
        $this->category_name = $cursor['category_name'];
        $this->shop_id = $cursor['shop_id'];
        $this->printer_id = $cursor['printer_id'];
        $this->lastmodtime = $cursor['lastmodtime'];
        $this->type = $cursor['type'];
        $this->food_id_list = $cursor['food_id_list'];
        $this->opening_time = $cursor['opening_time'];
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

class Category
{
    private function Tablename()
    {
        return 'category';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'category_id' => (string)$info->category_id
        );

        $set = array(
            "category_id" => (string)$info->category_id,
            "lastmodtime" => time()
        );

        if (null !== $info->shop_id) {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if (null !== $info->printer_id) {
            $set["printer_id"] = (string)$info->printer_id;
        }
        if (null !== $info->category_name) {
            $set["category_name"] = (string)$info->category_name;
        }
        if (null !== $info->type) {
            $set["type"] = (int)$info->type;
        }
        if (null !== $info->food_id_list) {
            $set["food_id_list"] = (array)$info->food_id_list;
        }
        if (null !== $info->opening_time) {
            $set["opening_time"] = (array)$info->opening_time;
        }
        $value = array(
            '$set' => $set
        );

        $table->update($cond, $value, ['upsert' => true]);
        return 0;
    }

    public function GetList($shop_id)
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' => ['$ne' => 1],
            // 'shop_id' => ['$in' => [0, (string)$shop_id]],
            'shop_id' => (string)$shop_id,
        ];
        $cursor = $table->find($cond, ["_id" => 0])->sort(["_id" => 1]);
        LogDebug(iterator_to_array($cursor));

        return CategoryEntry::ToList($cursor);
    }

    public function GetCategoryById($category_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'category_id' => (string)$category_id
        );
        $cursor = $table->findOne($cond);
        return new CategoryEntry($cursor);
    }
}

?>
