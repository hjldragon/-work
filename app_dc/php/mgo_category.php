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
    public $category_id   = null;      // 类别id
    public $category_name = null;      // 类别名
    public $shop_id       = null;      // 餐馆id
    public $printer_id    = null;      // 对应的打印机
    public $lastmodtime   = null;      // 最后修改的时间
    public $delete        = null;      // 是否删除(1删除,0未删除)
    public $type          = null;      // 类别的类型(1:一般类品分类，2:配件,3:酒水)
    //public $food_id_list  = null;      // 菜单分类下面的餐品id列表
    public $opening_time  = null;      // 菜单显示时间段
    public $sortval       = null;      // 菜品排序
    public $parent_id     = null;      // 父级id
    public $entry_type    = null;      // 创建类型(1:系统录入，2:手动录入)
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
        $this->shop_id       = $cursor['shop_id'];
        $this->printer_id    = $cursor['printer_id'];
        $this->lastmodtime   = $cursor['lastmodtime'];
        $this->type          = $cursor['type'];
        //$this->food_id_list  = $cursor['food_id_list'];
        $this->opening_time  = $cursor['opening_time'];
        $this->sortval       = $cursor['sortval'];
        $this->parent_id     = $cursor['parent_id'];
        $this->delete        = $cursor['delete'];
        $this->entry_type    = $cursor['entry_type'];
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

        $cond = [
            'category_id' => (string)$info->category_id,
        ];

        $set = [
            "category_id" => (string)$info->category_id,
            "lastmodtime" => time(),
        ];

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
        // if (null !== $info->food_id_list) {
        //     $set["food_id_list"] = (array)$info->food_id_list;
        // }
        if (null !== $info->opening_time) {
            foreach ($info->opening_time as &$v) {
                $v = (int)$v;
            }
            $set["opening_time"] = $info->opening_time;
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
        if (null !== $info->entry_type) {
            $set["entry_type"] = (int)$info->entry_type;
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

    // public function GetByFoodList($food_id)
    // {
    //     $db = \DbPool::GetMongoDb();
    //     $table = $db->selectCollection($this->Tablename());

    //     $cond = [
    //         'delete'  => ['$ne'=>1],
    //         'food_id_list' => ['$in' => [(string)$food_id]],
    //     ];
    //     $cursor = $table->find($cond, ["_id"=>0])->sort(["lastmodtime"=>-1]);
    //     // LogDebug(iterator_to_array($cursor));
    //     return CategoryEntry::ToList($cursor);
    // }

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

    public function GetByTypeList($shop_id,$type)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'    => ['$ne' => 1],
            'type'      => (int)$type,
            'shop_id'   => (string)$shop_id,
            'parent_id' => '0',
        ];
        $cursor = $table->findOne($cond);
        return new CategoryEntry($cursor);
    }

    public function GetList($shop_id, $type = null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            // 'shop_id' => ['$in' => [0, (string)$shop_id]],
            'shop_id' => (string)$shop_id,
        ];
        if($type){
            $cond['type'] = ['$ne'=>2];
        }
        $cursor = $table->find($cond, ["_id"=>0])->sort(["entry_time"=>1]);
        // LogDebug(iterator_to_array($cursor));
        return CategoryEntry::ToList($cursor);
    }

    public function GetNameById($category_name,$shop_id){
        $db =\DbPool::GetMongoDb();
        $table =$db->selectCollection($this->Tablename());
        $cond = array(
            'delete'  => ['$ne'=>1],
            'category_name'=>(string)$category_name,
            'shop_id'=>(string)$shop_id
        );
        $cursor = $table->findOne($cond);
        return new  CategoryEntry($cursor);
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

    public function UpdateOpenTime($parent_id,$time){
        $db =\DbPool::GetMongoDb();
        $table =$db->selectCollection($this->Tablename());
        $cond = array(
            'delete'  => ['$ne'=>1],
            'parent_id'=>(string)$parent_id
        );
        $value = [
            '$pullAll' => array(
                "opening_time" => $time
            ) 
        ];
         try {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>false, 'multiple' => true]);
            LogDebug("ret:" . $ret['ok']);
        } catch (\MongoCursorException $e) {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
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
    LogDebug($cond);
    LogDebug($value);
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
    public function DeleteById($category_id){
        $db =\DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'category_id'=>(int)$category_id
        );
        LogDebug($cond);
        $set = array(
            "lastmodtime"=>time(),
            "delete"=>1
        );
        $value = array(
            '$set'=>$set
        );
        LogDebug($value);
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple' => true]);
            LogDebug("ret:" . $ret['ok']);
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function QueryByName($category_name, $shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'category_name' => $category_name,
            'shop_id'       => $shop_id,
            'delete'        => array('$ne' => 1),
        );

        $ret = $table->findOne($cond);

        return new CategoryEntry($ret);
    }
}


?>
