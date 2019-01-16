<?php
/*
 * [Rocky 2017-06-17 20:46:34]
 * 代理商部门表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");

class AGDepartmentEntry
{
    public $ag_department_id   = null;     // 代理商部门id
    public $ag_department_name = null;     // 代理商部门名称
    public $agent_id           = null;     // 所属代理商id
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

        $this->ag_department_id   = $cursor['ag_department_id'];
        $this->ag_department_name = $cursor['ag_department_name'];
        $this->agent_id           = $cursor['agent_id'];
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

class AGDepartment
{
    private function Tablename()
    {
        return 'ag_department';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'ag_department_id' => (string)$info->ag_department_id
        );

        $set = array(
            'ag_department_id' => (string)$info->ag_department_id,
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->agent_id)
        {
            $set["agent_id"] = (string)$info->agent_id;
        }
        if(null !== $info->ag_department_name)
        {
            $set["ag_department_name"] = (string)$info->ag_department_name;
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

    public function GetDepartmentList($agent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'agent_id' => (string)$agent_id
        ];

        $cursor = $table->find($cond, ["_id"=>0]);
        return AGDepartmentEntry::ToList($cursor);
    }

    public function  QueryByDepartmentName($agent_id,$ag_department_name)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'agent_id'           => (string)$agent_id,
            'ag_department_name' =>(string)$ag_department_name,
            'delete'             => ['$ne' => 1]
        );
        $ret = $table->findOne($cond);

        return new AGDepartmentEntry($ret);
    }
    public function  QueryByDepartmentId($ag_department_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'ag_department_id'=>(string)$ag_department_id,
            'delete'          => ['$ne' => 1]
        );
        $ret = $table->findOne($cond);

        return new AGDepartmentEntry($ret);
    }
}
?>