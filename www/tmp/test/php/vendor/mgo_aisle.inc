<?php
/*
自动售货机的货道管理
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class AisleEntry extends BaseInfo
{

    public $aisle_id          = null;     // 当前数据id
    public $vendor_id         = null;     // 设备id
    public $shop_id           = null;     // 所属店铺
    public $aisle_name        = null;     // 货道编号(名称)
    public $aisle_capacity    = null;     // 货道容量
    public $vendor_goods_id   = null;     // 商品id
    public $goods_num         = null;     // 商品数量
    public $aisle_status      = null;     // 货道状态(1.正常,2.异常)
    public $is_inform         = null;     // 是否通知(1.通知,0.未通知)
    public $stockout_time     = null;     // 缺货时间
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
        $this->aisle_id         = $cursor['aisle_id'];
        $this->vendor_id        = $cursor['vendor_id'];
        $this->shop_id          = $cursor['shop_id'];
        $this->aisle_name       = $cursor['aisle_name'];
        $this->aisle_capacity   = $cursor['aisle_capacity'];
        $this->vendor_goods_id  = $cursor['vendor_goods_id'];
        $this->goods_num        = $cursor['goods_num'];
        $this->aisle_status     = $cursor['aisle_status'];
        $this->is_inform        = $cursor['is_inform'];
        $this->stockout_time    = $cursor['stockout_time'];
        $this->lastmodtime      = $cursor['lastmodtime'];
        $this->delete           = $cursor['delete'];

    }

}

class Aisle extends MgoBase
{
    private function Tablename()
    {
        return 'aisle';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'aisle_id'   => (string)$info->aisle_id,
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->aisle_name)
        {
            $set["aisle_name"] = (string)$info->aisle_name;
        }
        if(null !== $info->vendor_id)
        {
            $set["vendor_id"] = (string)$info->vendor_id;
        }
        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->aisle_capacity)
        {
            $set["aisle_capacity"] = (int)$info->aisle_capacity;
        }
        if(null !== $info->vendor_goods_id)
        {
            $set["vendor_goods_id"] = (string)$info->vendor_goods_id;
        }
        if(null !== $info->goods_num)
        {
            $set["goods_num"] = (int)$info->goods_num;
        }

        if(null !== $info->aisle_status)
        {
            $set["aisle_status"] = (int)$info->aisle_status;
        }
        if(null !== $info->is_inform)
        {
            $set["is_inform"] = (int)$info->is_inform;
        }
        if(null !== $info->stockout_time)
        {
            $set["stockout_time"] = (int)$info->stockout_time;
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

    public function QueryById($aisle_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'aisle_id'  => (string)$aisle_id,
            'delete'    => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        return new AisleEntry($ret);
    }
    public function ListByVendorId($vendor_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'vendor_id'  => (string)$vendor_id,
            'delete'     => array('$ne'=>1)
        );

        $ret = $table->find($cond);
        return AisleEntry::ToList($ret);
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
            $vendor_id_list = $filter['vendor_id_list'];
            if(!empty($vendor_id_list))
            {
                foreach($vendor_id_list as $i => &$item)
                {
                    $item = (string)$item;
                }
                $cond["vendor_id"] = ['$in' => $vendor_id_list];
            }
            $vendor_id = $filter['vendor_id'];
            if(!empty($vendor_id))
            {
                $cond['vendor_id'] = (string)$vendor_id;
            }
            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $goods_num = $filter['goods_num'];
            if(null !== $goods_num)
            {
                $cond['goods_num'] = (int)$goods_num;
            }
        }

        if(empty($sortby)){
            $sortby['ctime'] = -1;
        }
        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return AisleEntry::ToList($cursor);
    }

    public function ListByGoodsId($vendor_id, $vendor_goods_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'vendor_id'        => (string)$vendor_id,
            'vendor_goods_id'  => (string)$vendor_goods_id,
            'delete'           => array('$ne'=>1)
        );

        $ret = $table->find($cond);
        return AisleEntry::ToList($ret);
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
            $goods_num = $filter['goods_num'];
            if(null !== $goods_num)
            {
                $cond['goods_num'] = (int)$goods_num;
            }
        }
        $cursor = $table->find($cond, ["_id"=>0]);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return AisleEntry::ToList($cursor);
    }

}


?>
