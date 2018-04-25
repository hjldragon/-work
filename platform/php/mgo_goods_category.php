<?php
/*
 * [Rocky 2017-04-25 19:29:49]
 * 餐品类别表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class GoodsCategoryEntry
{
    public $category_id   = null;      // 类别id
    public $category_name = null;      // 类别名
    public $lastmodtime   = null;      // 最后修改的时间
    public $delete        = null;      // 是否删除(1删除,0未删除)
    public $type          = null;      // 类别的类型
    public $sortval       = null;      // 商品排序
    public $parent_id     = null;      // 父级id
    public $entry_time    = null;      // 创建时间

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
        $this->category_id   = $cursor['category_id'];
        $this->category_name = $cursor['category_name'];
        $this->lastmodtime   = $cursor['lastmodtime'];
        $this->type          = $cursor['type'];
        $this->sortval       = $cursor['sortval'];
        $this->parent_id     = $cursor['parent_id'];
        $this->delete        = $cursor['delete'];
        $this->entry_time    = $cursor['entry_time'];
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

class GoodsCategory
{
    private function Tablename()
    {
        return 'goods_category';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'category_id' => (string)$info->category_id,
        ];

        $set = [
            "category_id" => (string)$info->category_id,
            "lastmodtime" => time(),
        ];

        if (null !== $info->category_name) {
            $set["category_name"] = (string)$info->category_name;
        }
        if (null !== $info->type) {
            $set["type"] = (int)$info->type;
        }
        if (null !== $info->sortval) {
            $set["sortval"] = (array)$info->sortval;
        }
        if (null !== $info->parent_id) {
            $set["parent_id"] = (string)$info->parent_id;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
        }
        if (null !== $info->entry_time) {
            $set["entry_time"] = (int)$info->entry_time;
        }
        $value = [
            '$set' => $set,
        ];
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

    public function GetByParentList($parent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'parent_id' => (string)$parent_id,
        ];
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(["lastmodtime"=>-1]);
        return CategoryEntry::ToList($cursor);
    }

    public function GetList()
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
        ];
        $cursor = $table->find($cond, ["_id"=>0])->sort(["entry_time"=>1]);
        return GoodsCategoryEntry::ToList($cursor);
    }

    public function GetCategoryById($category_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'category_id' => (string)$category_id
        );
        $cursor = $table->findOne($cond);
        return new GoodsCategoryEntry($cursor);
    }
    //批量删除
    public function BatchDeleteById($category_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($category_id_list as $i => &$id)
        {
            $id = (string)$id;
        }

        $cond = array(
            'category_id' => array('$in' => $category_id_list)
        );

        $value = array(
            '$set'=>array(
                'delete'      => 1,
                'lastmodtime' => time(),
            )
        );
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple' => true]);
            LogDebug("ret:" . json_encode($ret));
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
       
        return 0;
    }

    public function QueryByName($category_name)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'category_name' => $category_name,
            'delete'        => array('$ne' => 1),
        );

        $ret = $table->findOne($cond);

        return new GoodsCategoryEntry($ret);
    }

}


?>
