<?php
/*
 * [Rocky 2018-06-07]
 * 工商信息表 
 */
///declare(encoding='UTF-8');
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

//后厨系统的档口
class StallEntry extends BaseInfo
{

    public $shop_id                      = null;    //店铺id
    public $stall_id                     = null;    //档口id
    public $stall_name                   = null;    //档口名称
    public $food_id_list                 = null;    //档口拥有的所有菜品id
    public $employee_id                  = null;    //档口编辑人员的id
    public $is_stall                     = null;    //是否启用档口(1.是,0.否)
    public $delete                       = null;    //
    public $lastmodtime                  = null;    //



    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    private function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->shop_id                   = $cursor['shop_id'];
        $this->stall_id                  = $cursor['stall_id'];
        $this->stall_name                = $cursor['stall_name'];
        $this->food_id_list              = $cursor['food_id_list'];
        $this->employee_id               = $cursor['employee_id'];
        $this->is_stall                  = $cursor['is_stall'];
        $this->lastmodtime               = $cursor['lastmodtime'];
        $this->delete                    = $cursor['delete'];
    }
}

class Stall extends MgoBase
{
    protected function Tablename()
    {
        return 'kitchen_stall';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'shop_id'   => (string)$info->shop_id,
            'stall_id' => (string)$info->stall_id,
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->stall_name)
        {
            $set["stall_name"] = (string)$info->stall_name;
        }
        if(null !== $info->food_id_list)
        {
            $set["food_id_list"] = $info->food_id_list;
        }
        if(null !== $info->employee_id)
        {
            $set["employee_id"] = (string)$info->employee_id;
        }
        if(null !== $info->is_stall)
        {
            $set["is_stall"] = (int)$info->is_stall;
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

    public function BatchDelete($id_list)
    {
        return parent::DoBatchDelete($id_list, "id_list");
    }

    public function GetExampleById($example_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$example_id, "example_id");
        return BusinessEntry::ToObj($cursor);
    }
    public function GetInfoByStart($shop_id,$employee_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'employee_id' => (string)$employee_id,
            'shop_id'     => (string)$shop_id,
            'is_stall'    => \StallStart::START,
            'delete'      => 0
        );

        $cursor = $table->findOne($cond);
        return new StallEntry($cursor);
    }

    public function GetListByShop($shop_id, $employee_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id'     => (string)$shop_id,
            'employee_id' => (string)$employee_id,
            'delete'      => ['$ne'=>1],
        ];

        $field["_id"] = 0;
        $cursor = $table->find($cond);
        return StallEntry::ToList($cursor);
    }

    public function BatchDelStall($stall_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($stall_id_list as $i => &$stall_id)
        {
            $stall_id = (string)$stall_id;
        }
        $cond = array(
            'stall_id'     => array('$in' => $stall_id_list),
        );

        $value = array(
            '$set'=>array(
                'delete'      => (int)1,
                'lastmodtime' => time(),
            )
        );

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

}
