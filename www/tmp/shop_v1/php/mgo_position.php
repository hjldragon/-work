<?php
/*
 * [Rocky 2017-05-04 11:35:11]
 * 职位表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class PositionEntry
{
    public $position_id            = null;     // 职位id
    public $position_name          = null;     // 职位名
    public $position_permission    = null;     // 职位权限
    public $position_note          = null;     // 职位备注
    public $shop_id                = null;     // 店铺id
    public $delete                 = null;     // 0:正常, 1:已删除
    public $is_edit                = null;     // 0:不编辑, 1:可编辑
    public $lastmodtime            = null;     // 数据最后修改时间
    public $ctime                  = null;     // 创建时间
    public $entry_type             = null;     // 创建类型(1:系统录入，2:手动录入)
    public $is_start               = null;     // (1:启用，2:停用)


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
        $this->position_id            = $cursor['position_id'];
        $this->position_name          = $cursor['position_name'];
        $this->position_permission    = $cursor['position_permission'];
        $this->position_note          = $cursor['position_note'];
        $this->shop_id                = $cursor['shop_id'];
        $this->delete                 = $cursor['delete'];
        $this->lastmodtime            = $cursor['lastmodtime'];
        $this->ctime                  = $cursor['ctime'];
        $this->entry_type             = $cursor['entry_type'];
        $this->is_edit                = $cursor['is_edit'];
        $this->is_start               = $cursor['is_start'];

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

class Position
{
    private function Tablename()
    {
        return 'position';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'position_id' => (string)$info->position_id
        );

        $set = array(
            "position_id" => (string)$info->position_id,
            "lastmodtime" => time()
        );

        if(null !== $info->position_name)
        {
            $set["position_name"] = (string)$info->position_name;
        }
        if(null !== $info->ctime)
        {
            $set["ctime"] = (int)$info->ctime;
        }
        if(null !== $info->position_permission)
        {
            $set["position_permission"] = $info->position_permission;
        }
        if(null !== $info->position_note)
        {
            $set["position_note"] = (string)$info->position_note;
        }
        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
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
        if(null !== $info->is_start)
        {
            $set["is_start"] = (int)$info->is_start;
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

    public function GetPositionById($shop_id, $position_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'position_id' => (string)$position_id,
            'delete'  => ['$ne'=>1],
            'shop_id' => (string)$shop_id
        ];
        $cursor = $table->findOne($cond);
        return new PositionEntry($cursor);
    }

    public function GetPositionByName($shop_id, $position_name)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'position_name' => (string)$position_name,
            'delete'  => ['$ne'=>1],
            'shop_id' => (string)$shop_id
        ];
        $cursor = $table->findOne($cond);
        return new PositionEntry($cursor);
    }

    public function BatchDelete($shop_id,$position_id)
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
            'position_id' => $position_id,
            'entry_type'  => ['$ne'=>1],
            "shop_id"=>$shop_id,
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

    public function GetStartPosition($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'   => ['$ne'=>1],
            'is_start' => 1,
            'shop_id'  => $shop_id
        ];
        $cursor = $table->find($cond)->sort(['ctime'=>1]);
        // LogDebug(iterator_to_array($cursor));
        return PositionEntry::ToList($cursor);
    }

    public function GetAllPositionByShop($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'   => ['$ne'=>1],
            'shop_id'  => $shop_id
        ];
        $cursor = $table->find($cond)->sort(['ctime'=>1]);
        // LogDebug(iterator_to_array($cursor));
        return PositionEntry::ToList($cursor);
    }

    public function QueryByName($shop_id,$position_name,$position_id=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'position_name' => $position_name,
            'shop_id'       => $shop_id,
            'delete'        => array('$ne' => 1),
        );
         if(null != $position_id)
         {
             $cond['position_id'] =array('$ne' => $position_id);
         }

        $ret = $table->findOne($cond);
        // LogDebug($ret);
        return new PositionEntry($ret);
    }

    public function BatchIsStart($position_id,$shop_id, $is_start)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'position_id' => $position_id,
            "shop_id"     => $shop_id
        ];
        $set = array(
            "is_start"    => (int)$is_start,
            "lastmodtime" => time(),
        );
        $value = array(
            '$set' => $set
        );
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple'=>true]);
            LogDebug("ret:" . $ret["ok"]);
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }
}


?>
