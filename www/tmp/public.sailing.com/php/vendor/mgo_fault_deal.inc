<?php
/*
自动售货机的设备管理
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class FaultEntry extends BaseInfo
{
    public $fault_id           = null;           // 故障id
    public $vendor_id          = null;           // 设备id
    public $fault_time         = null;           // 故障时间
    public $deal_time          = null;           // 处理时间
    public $send_time          = null;           // 派单时间
    public $is_deal            = null;           // 是否处理(1.处理,0.未处理)
    public $lastmodtime        = null;           // 最后修改的时间
    public $delete             = null;           // 是否删除(0:未删除; 1:已删除)


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

        $this->fault_id        = $cursor['fault_id'];
        $this->vendor_id       = $cursor['vendor_id'];
        $this->fault_time      = $cursor['fault_time'];
        $this->deal_time       = $cursor['deal_time'];
        $this->is_deal         = $cursor['is_deal'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];

    }

}

class Vendor extends MgoBase
{
    private function Tablename()
    {
        return 'fault_deal';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'vendor_id'   => (string)$info->vendor_id,
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->vendor_num)
        {
            $set["vendor_num"] = (string)$info->vendor_num;
        }
        if(null !== $info->vendor_model)
        {
            $set["vendor_model"] = (string)$info->vendor_model;
        }
        if(null !== $info->aisle_num)
        {
            $set["aisle_num"] = (int)$info->aisle_num;
        }
        if(null !== $info->sell_goods_type)
        {
            $set["sell_goods_type"] = (string)$info->sell_goods_type;
        }
        if(null !== $info->ownership)
        {
            $set["ownership"] = (string)$info->ownership;
        }
        if(null !== $info->vendor_name)
        {
            $set["vendor_name"] = (string)$info->vendor_name;
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
           if(null !== $info->address)
        {
            $set["address"] = (string)$info->address;
        }
        if(null !== $info->creat_time)
        {
            $set["creat_time"] = (string)$info->creat_time;
        }
        if(null !== $info->vendor_person)
        {
            $set["vendor_person"] = (string)$info->vendor_person;
        }
        if(null !== $info->person_phone)
        {
            $set["person_phone"] = (string)$info->person_phone;
        }
        if(null !== $info->vendor_qrcode)
        {
            $set["vendor_qrcode"] = (string)$info->vendor_qrcode;
        }
        if(null !== $info->is_stockout)
        {
            $set["is_stockout"] = (int)$info->is_stockout;
        }
        if(null !== $info->is_fault)
        {
            $set["is_fault"] = (int)$info->is_fault;
        }
        if(null !== $info->vendor_img)
        {
            $set["vendor_img"] = $info->vendor_img;
        }
        if(null !== $info->vendor_status)
        {
            $set["vendor_status"] = (int)$info->vendor_status;
        }
        if(null !== $info->sell_status)
        {
            $set["sell_status"] = (int)$info->sell_status;
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

    public function QueryById($vendor_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'vendor_id' => (string)$vendor_id,
            'delete'    => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        return new VendorEntry($ret);
    }

    public function BatchDeleteById($vendor_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'vendor_id'  => $vendor_id
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
        return VendorEntry::ToList($cursor);
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
            $ownership = $filter['ownership'];
            if(!empty($ownership))
            {
                $cond['ownership'] = new \MongoRegex("/$ownership/");;
            }
            $vendor_status = $filter['vendor_status'];
            if(!empty($vendor_status))
            {
                $cond['vendor_status'] = (int)$vendor_status;
            }
            $vendor_name = $filter['vendor_name'];
            if(!empty($vendor_name))
            {
                $cond['$or'] = [
                    ['vendor_name' => new \MongoRegex("/$vendor_name/")],
                    ['vendor_num'   => new \MongoRegex("/$vendor_name/")]
                ];
            }
        }
        if(empty($sortby)){
            $sortby['ctime'] = -1;
        }
        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return VendorEntry::ToList($cursor);
    }

    //改变状态
    public function VendorStatus($vendor_id_list, $filter=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($vendor_id_list as $i => &$vendor_id)
        {
            $vendor_id = (string)$vendor_id;
        }

        $cond = array(
            'vendor_id'     => array('$in' => $vendor_id_list),
        );

        if(null != $filter)
        {
            $delete = $filter['delete'];
            if(null != $delete)
            {
                $value = array(
                    '$set'=>array(
                        'delete'      => (int)$delete,
                        'lastmodtime' => time(),
                    )
                );
            }
            $sell_status = $filter['sell_status'];
            if(null != $sell_status)
            {
                $value = array(
                    '$set'=>array(
                        'sell_status'  => (int)$sell_status,
                        'lastmodtime'  => time(),
                    )
                );
            }
        }

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


?>