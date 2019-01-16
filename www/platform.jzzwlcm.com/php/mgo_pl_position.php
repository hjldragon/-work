<?php
/*
 * [Rocky 2017-05-04 11:35:11]
 * 平台职位表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class PLPositionEntry
{
    public $pl_position_id         = null;     // 平台职位id
    public $pl_position_name       = null;     // 平台职位名
    public $pl_position_permission = null;     // 平台职位权限
    public $pl_position_note       = null;     // 平台职位备注
    public $platform_id            = null;     // 平台id
    public $delete                 = null;     // 0:正常, 1:已删除
    public $is_edit                = null;     // 0:不编辑, 1:可编辑
    public $lastmodtime            = null;     // 数据最后修改时间
    public $ctime                  = null;     // 创建时间
    public $entry_type             = null;     // 创建类型(1:系统录入，2:手动录入)


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
        $this->pl_position_id         = $cursor['pl_position_id'];
        $this->pl_position_name       = $cursor['pl_position_name'];
        $this->pl_position_permission = $cursor['pl_position_permission'];
        $this->pl_position_note       = $cursor['pl_position_note'];
        $this->platform_id            = $cursor['platform_id'];
        $this->delete                 = $cursor['delete'];
        $this->lastmodtime            = $cursor['lastmodtime'];
        $this->ctime                  = $cursor['ctime'];
        $this->entry_type             = $cursor['entry_type'];
        $this->is_edit                = $cursor['is_edit'];

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

class PLPosition
{
    private function Tablename()
    {
        return 'pl_position';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'pl_position_id' => (string)$info->pl_position_id
        );

        $set = array(
            "pl_position_id" => (string)$info->pl_position_id,
            "lastmodtime" => time()
        );

        if(null !== $info->pl_position_name)
        {
            $set["pl_position_name"] = (string)$info->pl_position_name;
        }
        if(null !== $info->ctime)
        {
            $set["ctime"] = (int)$info->ctime;
        }
        if(null !== $info->pl_position_permission)
        {
            $set["pl_position_permission"] = (int)$info->pl_position_permission;
        }
        if(null !== $info->pl_position_note)
        {
            $set["pl_position_note"] = (string)$info->pl_position_note;
        }
        if(null !== $info->platform_id)
        {
            $set["platform_id"] = (string)$info->platform_id;
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
            $set["is_edit "] = (int)$info->is_edit;
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

    public function GetPositionById($pl_position_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'pl_position_id' => (string)$pl_position_id,
            'delete'  => ['$ne'=>1]
        ];
        $cursor = $table->findOne($cond);
        return new PLPositionEntry($cursor);
    }

    public function BatchDelete($platform_id,$pl_position_id)
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
            'pl_position_id' => $pl_position_id,
            "platform_id"    => $platform_id,
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

    public function GetPositionByPID($platform_id,$page_size, $page_no, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'      => ['$ne'=>1],
            'platform_id' => ['$in' => [0,(string)$platform_id]]
        ];
        $cursor = $table->find($cond)->sort(['ctime'=>1])->skip(($page_no-1)*$page_size)->limit($page_size);;

        if(null !== $total){
            $total = $table->count($cond);
        }
        return PLPositionEntry::ToList($cursor);
    }

    public function QueryByName($platform_id,$pl_position_name)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'pl_position_name' => $pl_position_name,
            'platform_id'      => $platform_id,
            'delete'           => array('$ne' => 1),
        );
//         if(null != $pl_position_id)
//         {
//             $cond['pl_position_id'] =array('$ne' => $pl_position_id);
//         }
        $ret = $table->findOne($cond);
        // LogDebug($ret);
        return new PLPositionEntry($ret);
    }
}


?>
