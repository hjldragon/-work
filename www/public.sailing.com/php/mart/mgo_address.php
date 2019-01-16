<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 地址管理表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class AddressEntry extends BaseInfo
{
    public $address_id   = null; // 地址id
    public $address_type = null; // 地址类型（1.平台发货,2.代理商收货,3.店铺收货）
    public $uid          = null; // 平台/代理商/店铺id
    public $address      = null; // 地址
    public $province     = null; // 省
    public $city         = null; // 市
    public $area         = null; // 区
    public $phone        = null; // 联系电话
    public $name         = null; // 联系人
    public $is_default   = null; // 是否默认
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
        $this->address_id   = $cursor["address_id"];
        $this->address_type = $cursor["address_type"];
        $this->uid          = $cursor["uid"];
        $this->address      = $cursor["address"];
        $this->province     = $cursor["province"];
        $this->city         = $cursor["city"];
        $this->area         = $cursor["area"];
        $this->phone        = $cursor["phone"];
        $this->name         = $cursor["name"];
        $this->is_default   = $cursor["is_default"];
        $this->lastmodtime  = $cursor["lastmodtime"];
        $this->delete       = $cursor["delete"];
        $this->ctime        = $cursor["ctime"];
    }
}
class Address extends MgoBase
{
    protected function Tablename()
    {
        return 'address';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'address_id' => (string)$info->address_id
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->address_type)
        {
            $set["address_type"] = (int)$info->address_type;
        }

        if(null !== $info->uid)
        {
            $set["uid"] = (string)$info->uid;
        }

        if(null !== $info->address)
        {
            $set["address"] = (string)$info->address;
        }

        if(null !== $info->province)
        {
            $set["province"] = (string)$info->province;
        }

        if(null !== $info->city)
        {
            $set["city"] = (string)$info->city;
        }

        if(null !== $info->area)
        {
            $set["area"] = (string)$info->area;
        }

        if(null !== $info->phone)
        {
            $set["phone"] = (string)$info->phone;
        }

        if(null !== $info->name)
        {
            $set["name"] = (string)$info->name;
        }
         if(null !== $info->is_default)
        {
            $set["is_default"] = (int)$info->is_default;
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
        return parent::DoBatchDelete($id_list, "address_id");
    }

    public function GetAddressById($address_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$address_id, "address_id");
        return AddressEntry::ToObj($cursor);
    }

    public function GetDefaultAddress($uid, $address_type)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'uid'          => (string)$uid,
            'address_type' => (int)$address_type,
            'delete'       => ['$ne' => 1],
            'is_default'   => 1
        ];
        $cursor = $table->findOne($cond);

        return new AddressEntry($cursor);
    }

    public function GetListById($uid, $address_type)
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'uid'          => (string)$uid,
            'address_type' => (int)$address_type,
            'delete'       => ['$ne' => 1]
        ];
        $cursor = $table->find($cond, ["_id"=>0])->sort(["ctime"=>1]);

        return AddressEntry::ToList($cursor);
    }
}



?>