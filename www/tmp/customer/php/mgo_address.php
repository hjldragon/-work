<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 地址位置表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class AddressEntry
{
    public $address_id     = null; // 地址位置id
    public $userid         = null; // 用户id
    public $name           = null; // 联系人
    public $sex            = null; // (1男，2女)
    public $phone          = null; // 联系电话
    public $lastmodtime    = null; // 数据最后修改时间
    public $delete         = null; // 0:正常, 1:已删除
    public $address_region = null; // 小区/大厦/学校
    public $address_num    = null; // 楼号/门牌号
    public $province       = null; // 省
    public $city           = null; // 市
    public $area           = null; // 县/区
    public $ctime          = null; // 创建时间

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
        $this->address_id     = $cursor['address_id'];
        $this->userid         = $cursor['userid'];
        $this->name           = $cursor['name'];
        $this->sex            = $cursor['sex'];
        $this->phone          = $cursor['phone'];
        $this->lastmodtime    = $cursor['lastmodtime'];
        $this->delete         = $cursor['delete'];
        $this->address_region = $cursor['address_region'];
        $this->address_num    = $cursor['address_num'];
        $this->province       = $cursor['province'];
        $this->city           = $cursor['city'];
        $this->area           = $cursor['area'];
        $this->ctime          = $cursor['ctime'];
    }

    public static function ToList($cursor)
    {
        $list = [];
        foreach ($cursor as $item) {
            $entry = new self($item);
            array_push($list, $entry);
        }
        return $list;
    }

}

class Address
{
    private function Tablename()
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
            "address_id"  => (string)$info->address_id,
            "lastmodtime" => time()
        );

        if (null !== $info->userid) {
            $set["userid"] = (int)$info->userid;
        }
        if (null !== $info->name){
            $set["name"] = (string)$info->name;
        }
        if (null !== $info->sex) {
            $set["sex"] = (int)$info->sex;
        }
        if (null !== $info->phone) {
            $set["phone"] = (string)$info->phone;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
        }
        if (null !== $info->address_region) {
            $set["address_region"] = (string)$info->address_region;
        }
        if (null !== $info->address_num) {
            $set["address_num"] = (string)$info->address_num;
        }
        if (null !== $info->province) {
            $set["province"] = (string)$info->province;
        }
         if (null !== $info->city) {
            $set["city"] = (string)$info->city;
        }
         if (null !== $info->area) {
            $set["area"] = (string)$info->area;
        }
        if (null !== $info->ctime) {
            $set["ctime"] = (int)$info->ctime;
        }
        $value = array(
            '$set' => $set
        );
        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true]);

            LogDebug("ret:" . $ret["ok"]);
        } catch (\MongoCursorException $e) {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function BatchDelete($address_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach ($address_id_list as $i => &$item) {
            $item = (string)$item;
        }
        $cond = [
            'address_id' => ['$in' => $address_id_list],
        ];

        $value = array(
            '$set' => array(
                'delete'  => 1,
                "lastmodtime" => time()
            )
        );

        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true, 'multiple' => true]);
            LogDebug("ret:" . $ret["ok"]);
        } catch (\MongoCursorException $e) {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function Delete($address_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'address_id' => (string)$address_id
        );

        $value = array(
            '$set' => array(
                'delete'      => 1,
                'lastmodtime' => time()
            )
        );

        try
        {
           $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true]);
            LogDebug("ret:" . $ret['ok']);
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }


    public function GetAddressById($address_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'address_id' => (string)$address_id,
            'delete' => ['$ne'=>1]
        ];

        $cursor = $table->findOne($cond);

        return new AddressEntry($cursor);
    }

    public function GetAddress($filter)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'userid' => (int)$userid,
            'address_region' => (string)$address_region,
            'area' => (string)$area,
            'delete' => ['$ne'=>1]
        ];

        $cursor = $table->findOne($cond);
        return new AddressEntry($cursor);
    }


    public function GetListByUser($userid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' => ['$ne' => 1],
            'userid' => (int)$userid
        ];
        $cursor = $table->find($cond, ["_id" => 0])->sort(["ctime" => 1]);
        return AddressEntry::ToList($cursor);
    }
}


?>

