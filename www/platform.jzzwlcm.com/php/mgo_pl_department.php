<?php
/*
 * [Rocky 2017-06-17 20:46:34]
 * 平台部门表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");

class PLDepartmentEntry
{
    public $pl_department_id   = null;     // 平台部门id
    public $pl_department_name = null;     // 平台部门名称
    public $platform_id        = null;     // 所属平台id
    public $lastmodtime        = null;     // 数据最后修改时间
    public $delete             = null;     // 0:正常, 1:已删除

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

        $this->pl_department_id   = $cursor['pl_department_id'];
        $this->pl_department_name = $cursor['pl_department_name'];
        $this->platform_id        = $cursor['platform_id'];
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

class PLDepartment
{
    private function Tablename()
    {
        return 'pl_department';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'pl_department_id' => (string)$info->pl_department_id
        );

        $set = array(
            'pl_department_id' => (string)$info->pl_department_id,
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->platform_id)
        {
            $set["platform_id"] = (string)$info->platform_id;
        }
        if(null !== $info->pl_department_name)
        {
            $set["pl_department_name"] = (string)$info->pl_department_name;
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

    public function GetDepartmentList($platform_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'platform_id' => (string)$platform_id
        ];

        $cursor = $table->find($cond, ["_id"=>0]);
        return PLDepartmentEntry::ToList($cursor);
    }

    public function  QueryByDepartmentName($platform_id,$pl_department_name)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'platform_id'       => (string)$platform_id,
            'pl_department_name'=>(string)$pl_department_name,
            'delete'            => ['$ne' => 1]
        );
        $ret = $table->findOne($cond);
        return new PLDepartmentEntry($ret);
    }
    public function  QueryByDepartmentId($pl_department_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'pl_department_id'=>(string)$pl_department_id,
            'delete' => ['$ne' => 1]
        );
        $ret = $table->findOne($cond);

        return new PLDepartmentEntry($ret);
    }
}
?>