<?php
/*
 * [Rocky 2017-05-04 11:35:11]
 * 代理商职位表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class AGPositionEntry
{
    public $ag_position_id         = null;     // 代理商职位id
    public $ag_position_name       = null;     // 代理商职位名
    public $ag_position_permission = null;     // 代理商职位权限
    public $ag_position_note       = null;     // 代理商职位备注
    public $agent_id               = null;     // 代理商id
    public $delete                 = null;     // 0:正常, 1:已删除
    public $is_edit                = null;     // 0:不编辑, 1:可编辑
    public $lastmodtime            = null;     // 数据最后修改时间
    public $ctime                  = null;     // 创建时间
    public $entry_type             = null;     // 创建类型(1:系统录入，2:手动录入)
    public $audit_person           = null;     // 审核人独立职位区分(0.未设置(自己创建的),
                                               //1.销售人员,2.销售经理,3,运营人员,4.运营经理,5.财务人员,6.财务经理)


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
        $this->ag_position_id         = $cursor['ag_position_id'];
        $this->ag_position_name       = $cursor['ag_position_name'];
        $this->ag_position_permission = $cursor['ag_position_permission'];
        $this->ag_position_note       = $cursor['ag_position_note'];
        $this->agent_id               = $cursor['agent_id'];
        $this->delete                 = $cursor['delete'];
        $this->lastmodtime            = $cursor['lastmodtime'];
        $this->ctime                  = $cursor['ctime'];
        $this->entry_type             = $cursor['entry_type'];
        $this->is_edit                = $cursor['is_edit'];
        $this->audit_person           = $cursor['audit_person'];

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

class AGPosition
{
    private function Tablename()
    {
        return 'ag_position';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'ag_position_id' => (string)$info->ag_position_id
        );

        $set = array(
            "ag_position_id" => (string)$info->ag_position_id,
            "lastmodtime" => time()
        );

        if(null !== $info->ag_position_name)
        {
            $set["ag_position_name"] = (string)$info->ag_position_name;
        }
        if(null !== $info->ctime)
        {
            $set["ctime"] = (int)$info->ctime;
        }
        if(null !== $info->ag_position_permission)
        {
            foreach($info->ag_position_permission as $i => $item)
            {
                    $set['ag_position_permission.'.$i] = $item;
            }
        }
        if(null !== $info->ag_position_note)
        {
            $set["ag_position_note"] = (string)$info->ag_position_note;
        }
        if(null !== $info->agent_id)
        {
            $set["agent_id"] = (string)$info->agent_id;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->entry_type)
        {
            $set["entry_type"] = (int)$info->entry_type;
        }
        if(null !== $info->is_edit )
        {
            $set["is_edit"] = (int)$info->is_edit;
        }
        if(null !== $info->audit_person)
        {
            $set["audit_person"] = (int)$info->audit_person;
        }

        $value = array(
            '$set' => $set
        );
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true]);
            LogDebug("ret:" . json_encode($ret));
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function GetPositionById($ag_position_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'ag_position_id' => (string)$ag_position_id,
            'delete'         => ['$ne'=>1]
        ];
        $cursor = $table->findOne($cond);
        return new AGPositionEntry($cursor);
    }

    public function BatchDelete($agent_id,$ag_position_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $set = array(
            "delete" => 1,
            "lastmodtime" => time()
        );
        $value = array(
            '$set' => $set
        );
        $cond = [
            'ag_position_id' => $ag_position_id,
            'entry_type'     => ['$ne'=>1],
            "agent_id"       =>$agent_id,
        ];
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple'=>true]);
            LogDebug("ret:" . json_encode($ret));
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function GetPositionByAgentID($agent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'agent_id' => ['$in' => [0,(string)$agent_id]]
        ];
        $cursor = $table->find($cond)->sort(['ctime'=>1]);
        return AGPositionEntry::ToList($cursor);
    }

    public function QueryByName($agent_id,$ag_position_name,$ag_position_id=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'ag_position_name' => $ag_position_name,
            'agent_id'         => $agent_id,
            'delete'           => array('$ne' => 1),
        );
         if(null != $ag_position_id)
         {
             $cond['ag_position_id'] =array('$ne' => $ag_position_id);
         }

        $ret = $table->findOne($cond);
        return new AGPositionEntry($ret);
    }

    public function GetPositionByAudit($agent_id, $audit_person_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        foreach($audit_person_list as $i => &$item)
        {
            $item = (int)$item;
        }
        $cond = [
            'agent_id'     => (string)$agent_id,
            'audit_person' => ['$in' => $audit_person_list],
            'delete'       => ['$ne'=>1]
        ];
        $cursor = $table->find($cond)->sort(['ctime'=>1]);
        return AGPositionEntry::ToList($cursor);
    }
}


?>
