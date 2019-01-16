<?php
/*
自动售货机的设备管理
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class VendorEntry extends BaseInfo
{
    public $vendor_id          = null;           // 设备id
    public $vendor_num         = null;           // 设备编号
    public $vendor_model       = null;           // 设备型号
    public $vendor_type        = null;           // 设备类型(1.升降,2.称重式,3.弹簧)
    public $aisle_num          = null;           // 货道数
    public $sell_goods_type    = null;           // 售货类型
    public $shop_id            = null;           // 设备所有权(即可以是店铺的也可以是运营平台的)店铺Id
    public $vendor_name        = null;           // 设备名称
    public $province           = null;           // 省
    public $city               = null;           // 市
    public $area               = null;           // 区
    public $address            = null;           // 地址
    public $creat_time         = null;           // 添加時間
    public $vendor_person      = null;           // 设备负责人
    public $person_phone       = null;           // 负责人手机号
    public $is_stockout        = null;           // 是否开启缺货实时通知(1.开,0.关)
    public $is_fault           = null;           // 是否开启故障实时通知(1.开,0.关)
    public $vendor_img         = null;           // 设备图片
    public $vendor_status      = null;           // 设备状态(1.正常,2.故障,3.缺货,4.断货)
    public $sell_status        = null;           // 设备状态(1.正在售货,0.停止售货)
    public $max_weight         = null;           // 最大重量(电子秤vendor_type=2)
    public $error_weight       = null;           // 误差重量(电子秤vendor_type=2)
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
        $this->vendor_id       = $cursor['vendor_id'];
        $this->vendor_num      = $cursor['vendor_num'];
        $this->vendor_model    = $cursor['vendor_model'];
        $this->vendor_type     = $cursor['vendor_type'];
        $this->aisle_num       = $cursor['aisle_num'];
        $this->sell_goods_type = $cursor['sell_goods_type'];
        $this->shop_id         = $cursor['shop_id'];
        $this->vendor_name     = $cursor['vendor_name'];
        $this->province        = $cursor['province'];
        $this->city            = $cursor['city'];
        $this->area            = $cursor['area'];
        $this->address         = $cursor['address'];
        $this->creat_time      = $cursor['creat_time'];
        $this->vendor_person   = $cursor['vendor_person'];
        $this->person_phone    = $cursor['person_phone'];
        $this->is_stockout     = $cursor['is_stockout'];
        $this->is_fault        = $cursor['is_fault'];
        $this->vendor_img      = $cursor['vendor_img'];
        $this->vendor_status   = $cursor['vendor_status'];
        $this->sell_status     = $cursor['sell_status'];
        $this->max_weight      = $cursor['max_weight'];
        $this->error_weight    = $cursor['error_weight'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];

    }

}

class Vendor extends MgoBase
{
    private function Tablename()
    {
        return 'vendor';
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
        if(null !== $info->vendor_type)
        {
            $set["vendor_type"] = (int)$info->vendor_type;
        }
        if(null !== $info->aisle_num)
        {
            $set["aisle_num"] = (int)$info->aisle_num;
        }
        if(null !== $info->sell_goods_type)
        {
            $set["sell_goods_type"] = (string)$info->sell_goods_type;
        }
        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
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
        if(null !== $info->max_weight)
        {
            $set["max_weight"] = (int)$info->max_weight;
        }
        if(null !== $info->error_weight)
        {
            $set["error_weight"] = (int)$info->error_weight;
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

    public function GetListTotal($filter=null, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
        ];
        if(null != $filter)
        {
            $vendor_name = $filter['vendor_name'];
            if(!empty($vendor_name))
            {
                $cond['$or'] = [
                    ['vendor_name' => new \MongoRegex("/$vendor_name/")],
                    ['vendor_id'   => new \MongoRegex("/$vendor_name/")]
                ];
            }
            $shop_id_list = $filter['shop_id_list'];
            if(!empty($shop_id_list))
            {
                foreach($shop_id_list as $i => &$item)
                {
                    $item = (string)$item;
                }
                $cond["shop_id"] = ['$in' => $shop_id_list];
            }
            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $vendor_status = $filter['vendor_status'];
            if(!empty($vendor_status))
            {
                $cond['vendor_status'] = (int)$vendor_status;
            }
            $province = $filter['province'];
            if(!empty($province))
            {
                $cond['province'] = (string)$province;
            }
        }
        $cursor = $table->find($cond, ["_id"=>0]);
        if(null !== $total){
            $total = $table->count($cond);
        }

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

            $shop_id_list = $filter['shop_id_list'];
            if(!empty($shop_id_list))
            {
                foreach($shop_id_list as $i => &$item)
                {
                    $item = (string)$item;
                }
                $cond["shop_id"] = ['$in' => $shop_id_list];
            }
            $vendor_status_list = $filter['vendor_status_list'];
            if(!empty($vendor_status_list))
            {
                foreach($vendor_status_list as $i => &$item)
                {
                    $item = (int)$item;
                }
                $cond["vendor_status"] = ['$in' => $vendor_status_list];
            }
            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
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
                    ['vendor_id'   => new \MongoRegex("/$vendor_name/")]
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
