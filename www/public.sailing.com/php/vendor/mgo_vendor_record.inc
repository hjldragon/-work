<?php
/*
补货记录
 */
namespace Pub\Vendor\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class AisleList
{

    public $aisle_id          = null;     // 货道id
    public $vendor_goods_id   = null;     // 商品id
    public $goods_num         = null;     // 商品数量



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
        $this->aisle_id          = $cursor['aisle_id'];
        $this->vendor_goods_id   = $cursor['vendor_goods_id'];
        $this->goods_num         = $cursor['goods_num'];


    }

    public static function ToList($cursor)
    {
        $list = array();
        foreach($cursor as $item)
        {
            $entry = new self($item);
            array_push($list, $entry);
        }
        return $list;
    }
}

class VendorRecordEntry extends \Pub\Mongodb\BaseInfo
{

    public $record_id         = null;     // 记录id
    public $aisle_list        = null;     // 补货记录列表
    public $shop_id           = null;     // 店铺ID
    public $vendor_id         = null;     // 设备id
    public $record_time       = null;     // 记录时间
    public $use_name          = null;     // 操作人
    public $lastmodtime       = null;     // 最后修改的时间
    public $delete            = null;     // 是否删除(0:未删除; 1:已删除)


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
        $this->record_id        = $cursor['record_id'];
        $this->aisle_list       = AisleList::ToList($cursor['aisle_list']);
        $this->shop_id          = $cursor['shop_id'];
        $this->vendor_id        = $cursor['vendor_id'];
        $this->record_time      = $cursor['record_time'];
        $this->use_name         = $cursor['use_name'];
        $this->lastmodtime      = $cursor['lastmodtime'];
        $this->delete           = $cursor['delete'];

    }

}

class VendorRecord extends \Pub\Mongodb\MgoBase
{
    private function Tablename()
    {
        return 'vendor_record';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'record_id'   => (string)$info->record_id,
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->vendor_id)
        {
            $set["vendor_id"] = (string)$info->vendor_id;
        }

        if(null !== $info->aisle_list)
        {
            $aisle_list = [];
            foreach ($info->aisle_list as $v) {
                $p                    = new AisleList();
                $p->vendor_goods_id   = (string)$v->vendor_goods_id;
                $p->goods_num         = (int)$v->goods_num;
                $p->aisle_id          = (string)$v->aisle_id;
                $aisle_list[]   = $p;
            }
            $set["aisle_list"] = $aisle_list;
        }
        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->record_time)
        {
            $set["record_time"] = (int)$info->record_time;
        }
        if(null !== $info->use_name)
        {
            $set["use_name"] = (string)$info->use_name;
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

    public function GetListTotal($filter=null, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
        ];
        if(null != $filter)
        {
            $vendor_id_list = $filter['vendor_id_list'];
            if(!empty($vendor_id_list))
            {
                foreach($vendor_id_list as $i => &$item)
                {
                    $item = (string)$item;
                }
                $cond["vendor_id"] = ['$in' => $vendor_id_list];
            }
            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $vendor_id = $filter['vendor_id'];
            if(!empty($vendor_id))
            {
                $cond['vendor_id'] = (string)$vendor_id;
            }
            $use_name = $filter['use_name'];

            if (null !== $use_name)
            {

              $cond['$or'] = [
                        ['use_name'    => new \MongoRegex("/$use_name/")],
                        ['record_id'   => new \MongoRegex("/$use_name/")]
               ];

            }

        }
        $cursor = $table->find($cond, ["_id"=>0]);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return VendorRecordEntry::ToList($cursor);
    }

    public function GetAllList($filter=null, $page_size, $page_no, $sortby = [], &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' =>  ['$ne'=>1] ,
        ];
        if(null != $filter)
        {

            $vendor_id_list = $filter['vendor_id_list'];
            if(!empty($vendor_id_list))
            {
                foreach($vendor_id_list as $i => &$item)
                {
                    $item = (string)$item;
                }
                $cond["vendor_id"] = ['$in' => $vendor_id_list];
            }
            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $vendor_id = $filter['vendor_id'];
            if(!empty($vendor_id))
            {
                $cond['vendor_id'] = (string)$vendor_id;
            }
            $use_name = $filter['use_name'];

            if (null !== $use_name)
            {

                $cond['$or'] = [
                    ['use_name'    => new \MongoRegex("/$use_name/")],
                    ['record_id'   => new \MongoRegex("/$use_name/")]
                ];

            }
        }

        if(empty($sortby)){
            $sortby['record_time'] = -1;
        }

        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return VendorRecordEntry::ToList($cursor);
    }
}


?>
