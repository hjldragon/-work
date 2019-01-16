<?php
/*
自动售货机的订单管理
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class VendorGoodsList
{

    public $vendor_goods_id   = null;     // 商品id
    public $goods_name        = null;     // 商品名
    public $goods_price       = null;     // 商品单价(单位元)
    public $goods_num         = null;     // 商品数量
    public $aisle_id          = null;     // 货道id


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
        $this->vendor_goods_id   = $cursor['vendor_goods_id'];
        $this->goods_name        = $cursor['goods_name'];
        $this->goods_price       = $cursor['goods_price'];
        $this->goods_num         = $cursor['goods_num'];
        $this->aisle_id          = $cursor['aisle_id'];

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

class VendorOrderEntry extends BaseInfo
{
    public $vendor_order_id    = null;           // 订单id
    public $customer_id        = null;           // 顾客用户id
    public $vendor_id          = null;           // 设备id
    public $goods_list         = null;           // 商品列表
    public $shop_id            = null;           // 店铺id
    public $order_time         = null;           // 下单时间及创建时间(时间戳)
    public $pay_time           = null;           // 支付时间(时间戳)
    public $order_fee          = null;           // 订单金额（按定价算出来的当前消费额）
    public $paid_price         = null;           // 实收金额
    public $order_status       = null;           // 订单状态(1:未支付(待付款),2:已支付(交易完成),3:退款成功,)
    public $pay_way            = null;           // 支付方式(1:微信支付, 2:支付宝支付)
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
        $this->vendor_order_id = $cursor['vendor_order_id'];
        $this->customer_id     = $cursor['customer_id'];
        $this->vendor_id       = $cursor['vendor_id'];
        $this->goods_list      = VendorGoodsList::ToList($cursor['goods_list']);
        $this->shop_id         = $cursor['shop_id'];
        $this->order_time      = $cursor['order_time'];
        $this->pay_time        = $cursor['pay_time'];
        $this->order_fee       = $cursor['order_fee'];
        $this->paid_price      = $cursor['paid_price'];
        $this->order_status    = $cursor['order_status'];
        $this->pay_way         = $cursor['pay_way'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];

    }

}

class VendorOrder extends MgoBase
{
    private function Tablename()
    {
        return 'vendor_order';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'vendor_order_id'   => (string)$info->vendor_order_id,
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->customer_id)
        {
            $set["customer_id"] = (string)$info->customer_id;
        }
        if(null !== $info->vendor_id)
        {
            $set["vendor_id"] = (string)$info->vendor_id;
        }
        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->goods_list)
        {
            $goods_list = [];
            foreach ($info->goods_list as $v) {
                $p                    = new VendorGoodsList();
                $p->vendor_goods_id   = (string)$v->vendor_goods_id;
                $p->goods_name        = (string)$v->goods_name;
                $p->goods_price       = (float)$v->goods_price;
                $p->goods_num         = (int)$v->goods_num;
                $p->aisle_id          = (string)$v->aisle_id;
                $goods_list[]   = $p;
            }
            $set["goods_list"] = $goods_list;
        }
        if(null !== $info->order_time)
        {
            $set["order_time"] = (int)$info->order_time;
        }
        if(null !== $info->pay_time)
        {
            $set["pay_time"] = (int)$info->pay_time;
        }
        if(null !== $info->order_fee)
        {
            $set["order_fee"] = (float)$info->order_fee;
        }
        if(null !== $info->paid_price)
        {
            $set["paid_price"] = (float)$info->paid_price;
        }
           if(null !== $info->order_status)
        {
            $set["order_status"] = (int)$info->order_status;
        }
        if(null !== $info->pay_way)
        {
            $set["pay_way"] = (int)$info->pay_way;
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

    public function QueryById($vendor_order_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'vendor_order_id' => (string)$vendor_order_id,
            'delete'          => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        return new VendorOrderEntry($ret);
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

            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $pay_way = $filter['pay_way'];
            if(!empty($pay_way))
            {
                $cond['pay_way'] = (int)$pay_way;
            }
            $vendor_id = $filter['vendor_id'];
            if(!empty($vendor_id))
            {
                $cond['vendor_id'] = (string)$vendor_id;
            }
            $order_status_list = $filter['order_status_list'];
            if (null !== $order_status_list)
            {
                foreach ($order_status_list as $i => &$item) {
                    $item = (int)$item;
                }
                $cond['order_status'] = ['$in' => $order_status_list];
            }
            $vendor_order_id = $filter['vendor_order_id'];
            if(!empty($vendor_order_id))
            {
                $cond['vendor_order_id'] = new \MongoRegex("/$vendor_order_id/");
            }
            $begin_time = $filter['begin_time'];
            $end_time   = $filter['end_time'];
            if(!empty($begin_time))
            {
                $cond['order_time'] = [
                    '$gte' => (int)$begin_time,
                    '$lte' => (int)$end_time
                ];
            }
            $order_status = $filter['order_status'];
            if(!empty($order_status))
            {
                $cond['order_status'] = (int)$order_status;
            }
        }
        if(empty($sortby)){
            $sortby['ctime'] = -1;
        }
        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return VendorOrderEntry::ToList($cursor);
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
            $order_status = $filter['order_status'];
            if(!empty($order_status))
            {
                $cond['order_status'] = (int)$order_status;
            }
            $order_status_list = $filter['order_status_list'];
            if (null !== $order_status_list)
            {
                foreach ($order_status_list as $i => &$item) {
                    $item = (int)$item;
                }
                $cond['order_status'] = ['$in' => $order_status_list];
            }
            $order_name = $filter['order_name'];
            if (!empty($order_name)) {
                $cond['$or'] = [
                    ["vendor_order_id"       => new \MongoRegex("/$order_name/")],
                    //["goods_list.goods_name" => new \MongoRegex("/$order_name/")]
                ];
            }

        }


        $cursor = $table->find($cond, ["_id"=>0]);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return VendorOrderEntry::ToList($cursor);
    }
}
?>