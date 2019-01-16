<?php
/*
 * [Rocky 2017-04-25 19:29:49]
 * 商品类别表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class VendorGoodsCategoryEntry extends BaseInfo
{
    public $vg_category_id   = null;      // 类别id
    public $vg_category_name = null;      // 类别名
    public $parent_id        = null;      // 父级id
    public $shop_id          = null;      // 所属店铺id
    public $lastmodtime      = null;      // 最后修改时间
    public $ctime            = null;      // 创建时间
    public $delete           = null;      //

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
        $this->vg_category_id   = $cursor['vg_category_id'];
        $this->vg_category_name = $cursor['vg_category_name'];
        $this->parent_id        = $cursor['parent_id'];
        $this->shop_id          = $cursor['shop_id'];
        $this->delete           = $cursor['delete'];
        $this->lastmodtime      = $cursor['lastmodtime'];
        $this->ctime            = $cursor['ctime'];
    }
}


class VendorGoodsCategory extends MgoBase
{
    protected function Tablename()
    {
        return 'vendor_goods_category';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'vg_category_id' => (string)$info->vg_category_id,
        ];

        $set = [
            "vg_category_id" => (string)$info->vg_category_id,
            "lastmodtime" => time(),
        ];

        if (null !== $info->parent_id) {
            $set["parent_id"] = (string)$info->parent_id;
        }
        if (null !== $info->vg_category_name) {
            $set["vg_category_name"] = (string)$info->vg_category_name;
        }
        if (null !== $info->shop_id) {
            $set["shop_id"] = (string)$info->shop_id;
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

    public function GetCateById($vg_category_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$vg_category_id, "vg_category_id");
        return VendorGoodsCategoryEntry::ToObj($cursor);
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
        return VendorGoodsCategoryEntry::ToList($cursor);
    }

    public function GetList()
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1]
        ];

        $cursor = $table->find($cond, ["_id"=>0])->sort(["ctime"=>1]);
        // LogDebug(iterator_to_array($cursor));
        return VendorGoodsCategoryEntry::ToList($cursor);
    }

    public function GetCateByName($vg_category_name){
        $db =\DbPool::GetMongoDb();
        $table =$db->selectCollection($this->Tablename());
        $cond = array(
            'delete'  => ['$ne'=>1],
            'vg_category_name'=>(string)$vg_category_name
        );
        $cursor = $table->findOne($cond);
        return new  VendorGoodsCategoryEntry($cursor);
    }

    public function BatchDelete($id_list)
    {
        return parent::DoBatchDelete($id_list, "vg_category_id");
    }

    public function DeleteById($vg_category_id){
        $db =\DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'vg_category_id'=>(int)$vg_category_id
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
