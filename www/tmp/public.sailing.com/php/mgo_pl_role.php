<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 运营平台角色表
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class PlRoleEntry extends BaseInfo
{
    public $pl_role_id       = null;           // 角色id
    public $role_name        = null;           // 角色名称
    public $pl_position_id   = null;           // 关联平台职位id
    public $lastmodtime      = null;           // 最后修改的时间
    public $delete           = null;           // 是否删除(0:未删除; 1:已删除)


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
        $this->pl_role_id     = $cursor['pl_role_id'];
        $this->role_name      = $cursor['role_name'];
        $this->pl_position_id = $cursor['pl_position_id'];
        $this->lastmodtime    = $cursor['lastmodtime'];
        $this->delete         = $cursor['delete'];

    }

}

class PlRole extends MgoBase
{
        private function Tablename()
    {
        return 'pl_role';
    }

        public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'pl_role_id'   => (string)$info->pl_role_id,
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->role_name)
        {
            $set["role_name"] = (string)$info->role_name;
        }
        if(null !== $info->pl_position_id)
        {
            $set["pl_position_id"] = (string)$info->pl_position_id;
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
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function QueryById($pl_role_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'pl_role_id' => (string)$pl_role_id,
            'delete'  => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        return new PlRoleEntry($ret);
    }

    public function BatchDeleteById($pl_role_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'pl_role_id'  => $pl_role_id
        ];
        $set = array(
            "lastmodtime" => time(),
            "delete"      => 1
        );
        $value = array(
            '$set' => $set
        );
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple' => true]);
            LogDebug("ret:" . $ret['ok']);
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function GetList()
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
        ];

        $cursor = $table->find($cond, ["_id"=>0]);
        return PlRoleEntry::ToList($cursor);
    }

    public function QueryByRoleName($role_name)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'role_name' => (string)$role_name,
            'delete'  => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        return new PlRoleEntry($ret);
    }

    //获取列表数据
    public function GetAllList($filter=null, $page_size, $page_no, $sortby = [], &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' =>  ['$ne'=>1] ,
        ];
        if(null != $filter)
        {
            $role_name = $filter['role_name'];
            if(!empty($role_name))
            {
                $cond['role_name'] = (string)$role_name;
            }
        }
        if(empty($sortby)){
            $sortby['ctime'] = -1;
        }
        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return PlRoleEntry::ToList($cursor);
    }
    public function GetListByPositionId($pl_position_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'pl_position_id' => $pl_position_id,
            'delete'  => ['$ne'=>1],
        ];

        $cursor = $table->find($cond, ["_id"=>0]);
        return PlRoleEntry::ToList($cursor);
    }
    public function GetIdList($pl_position_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($pl_position_id_list as $i => &$item)
        {
            $item = (string)$item;
        }
        $cond = [
            'delete'          => ['$ne'=>1],
            'pl_position_id'  => ['$in' => $pl_position_id_list]
        ];

        $cursor = $table->find($cond, ["_id"=>0]);
        return PlRoleEntry::ToList($cursor);
    }
}


?>
