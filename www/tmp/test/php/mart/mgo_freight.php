<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 平台运费表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class FreightEntry extends BaseInfo
{
    public $freight_id   = null; // 运费id
    public $platform_id  = null; // 平台id
    public $province     = null; // 省
    public $city         = null; // 市
    public $first_fee    = null; // 首费
    public $add_fee      = null; // 续费
    public $first_weight = null; // 首重
    public $add_weight   = null; // 续重
    public $lastmodtime  = null; // 数据最后修改时间
    public $delete       = null; // 0:正常, 1:已删除
    public $ctime        = null; // 创建时间

    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    protected function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->freight_id   = $cursor["freight_id"];
        $this->platform_id  = $cursor["platform_id"];
        $this->province     = $cursor["province"];
        $this->city         = $cursor["city"];
        $this->first_fee    = $cursor["first_fee"];
        $this->add_fee      = $cursor["add_fee"];
        $this->first_weight = $cursor["first_weight"];
        $this->add_weight   = $cursor["add_weight"];
        $this->lastmodtime  = $cursor["lastmodtime"];
        $this->delete       = $cursor["delete"];
        $this->ctime        = $cursor["ctime"];
    }
}

class Freight extends MgoBase
{
    protected function Tablename()
    {
        return 'freight';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'freight_id' => (string)$info->freight_id
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->platform_id)
        {
            $set["platform_id"] = (string)$info->platform_id;
        }
        if(null !== $info->province)
        {
            $set["province"] = (string)$info->province;
        }
        if(null !== $info->city)
        {
            $set["city"] = (string)$info->city;
        }
        if(null !== $info->first_fee)
        {
            $set["first_fee"] = (float)$info->first_fee;
        }
        if(null !== $info->add_fee)
        {
            $set["add_fee"] = (float)$info->add_fee;
        }
        if(null !== $info->first_weight)
        {
            $set["first_weight"] = (float)$info->first_weight;
        }
        if(null !== $info->add_weight)
        {
            $set["add_weight"] = (float)$info->add_weight;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->ctime)
        {
            $set["ctime"] = (int)$info->ctime;
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
        return parent::DoBatchDelete($id_list, "freight_id");
    }

    public function GetFreightById($freight_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$freight_id, "freight_id");
        return FreightEntry::ToObj($cursor);
    }

    public function GetFreightByCity($platform_id, $city){
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'delete'  => ['$ne'=>1],
            'platform_id'=> (string)$platform_id,
            'city' => (string)$city
        );
        $cursor = $table->findOne($cond);
        return new  FreightEntry($cursor);
    }

    public function GetFreightList($platform_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'platform_id' => $platform_id
        ];

        $cursor = $table->find($cond, ["_id"=>0])->sort(["ctime"=>1]);
        return FreightEntry::ToList($cursor);
    }

}



?>