<?php
/*
 * [Rocky 2017-06-17 20:46:34]
 * 部门表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");

class DepartmentEntry
{
    public $department_id        = null;     // 部门id
    public $department_name      = null;     // 部门名称
    public $shop_id              = null;     // 店铺id
    public $lastmodtime          = null;     // 数据最后修改时间
    public $delete               = null;     // 0:正常, 1:已删除

    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    public function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }

        $this->department_id      = $cursor['department_id'];
        $this->department_name    = $cursor['department_name'];
        $this->shop_id            = $cursor['shop_id'];
        $this->lastmodtime        = $cursor['lastmodtime'];
        $this->delete             = $cursor['delete'];

    }

    public static function ToList($cursor)
    {
        $list = [];
        foreach($cursor as $item)
        {
            $entry = new self($item);
            array_push($list, $entry);
        }
        return $list;
    }
}

class Department
{
    private function Tablename()
    {
        return 'department';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'department_id' => (string)$info->department_id
        );

        $set = array(
            'department_id' => (string)$info->department_id,
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->department_name)
        {
            $set["department_name"] = (string)$info->department_name;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        $value = array(
            '$set' => $set
        );

        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true]);
            LogDebug("ret:" . $ret["ok"]);
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function GetDepartmentList($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'shop_id' => (string)$shop_id
        ];

        $cursor = $table->find($cond, ["_id"=>0]);
        return DepartmentEntry::ToList($cursor);
    }

    public function  QueryByDepartmentName($shop_id,$department_name)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'shop_id' => (string)$shop_id,
            'department_name'=>(string)$department_name,
            'delete' => ['$ne' => 1]
        );
        $ret = $table->findOne($cond);

        return new DepartmentEntry($ret);
    }
    public function  QueryByDepartmentId($shop_id, $department_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'shop_id'       => (string)$shop_id,
            'department_id' => (string)$department_id,
            'delete'        => ['$ne' => 1]
        );
        $ret = $table->findOne($cond);

        return new DepartmentEntry($ret);
    }
}
?>