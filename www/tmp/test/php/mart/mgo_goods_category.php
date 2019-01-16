<?php
/*
 * [Rocky 2017-04-25 19:29:49]
 * 餐品类别表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class GoodsCategoryEntry extends BaseInfo
{
    public $category_id   = null;      // 类别id
    public $category_name = null;      // 类别名
    public $parent_id     = null;      // 父级id
    public $cate_type     = null;      // 类别属性(1:硬件,2:耗材,3:软件)
    public $lastmodtime   = null;      // 最后修改时间
    public $ctime         = null;      // 创建时间
    public $delete        = null;      //

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
        $this->parent_id     = $cursor['parent_id'];
        $this->cate_type     = $cursor['cate_type'];
        $this->delete        = $cursor['delete'];
        $this->lastmodtime   = $cursor['lastmodtime'];
        $this->ctime         = $cursor['ctime'];
    }
}


class GoodsCategory extends MgoBase
{
    protected function Tablename()
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

        if (null !== $info->parent_id) {
            $set["parent_id"] = (string)$info->parent_id;
        }
        if (null !== $info->category_name) {
            $set["category_name"] = (string)$info->category_name;
        }
        if (null !== $info->cate_type) {
            $set["cate_type"] = (int)$info->cate_type;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
        }
        if (null !== $info->ctime) {
            $set["ctime"] = (int)$info->ctime;
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

    public function GetCateById($category_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$category_id, "category_id");
        return GoodsCategoryEntry::ToObj($cursor);
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
        return GoodsCategoryEntry::ToList($cursor);
    }

    // public function GetByTypeList($shop_id,$type)
    // {
    //     $db = \DbPool::GetMongoDb();
    //     $table = $db->selectCollection($this->Tablename());

    //     $cond = [
    //         'delete'    => ['$ne' => 1],
    //         'type'      => (int)$type,
    //         'shop_id'   => (string)$shop_id,
    //         'parent_id' => '0',
    //     ];
    //     $cursor = $table->findOne($cond);
    //     return new GoodsCategoryEntry($cursor);
    // }

    public function GetList()
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1]
        ];

        $cursor = $table->find($cond, ["_id"=>0])->sort(["ctime"=>1]);
        // LogDebug(iterator_to_array($cursor));
        return GoodsCategoryEntry::ToList($cursor);
    }

    public function GetCateByName($category_name){
        $db =\DbPool::GetMongoDb();
        $table =$db->selectCollection($this->Tablename());
        $cond = array(
            'delete'  => ['$ne'=>1],
            'category_name'=>(string)$category_name
        );
        $cursor = $table->findOne($cond);
        return new  GoodsCategoryEntry($cursor);
    }

    public function BatchDelete($id_list)
    {
        return parent::DoBatchDelete($id_list, "category_id");
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
}


?>
