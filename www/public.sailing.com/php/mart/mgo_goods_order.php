<?php
/*
 * [Rocky 2017-05-12 19:39:55]
 * 商品订单表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class OrderGoodsList
{
    //public $id                = null;     // 当前数据id（主键）
    public $goods_id          = null;     // 商品id
    public $goods_name        = null;     // 商品名
    public $goods_price       = null;     // 商品单价(单位元)
    public $rebates_price     = null;     // 商品折扣单价(单位元)
    public $goods_num         = null;     // 商品数量
    public $goods_price_sum   = null;     // 商品总费用(=goods_price*goods_num)
    public $rebates_price_sum = null;     // 商品折扣总费用(=goods_price*goods_num)
    public $spec_id           = null;     // 规格id
    public $spec_name         = null;     // 规格名称
    public $package           = null;     // 套餐名
    public $time              = null;     // 规格时长
    public $time_unit         = null;     // 规格时长单位(1.日,2.月,3.季,4.年)
    public $terminal          = null;     // 规格授权端（1:智能收银机,2:自助点餐机,4:平板智能点餐机,5:掌柜通）
    public $invoice           = null;      // 1：不提供发票  2: 提供发票
    public $invoice_price     = null;      // 发票金额

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
        //$this->id              = $cursor['id'];
        $this->goods_id          = $cursor['goods_id'];
        $this->goods_name        = $cursor['goods_name'];
        $this->goods_price       = $cursor['goods_price'];
        $this->rebates_price     = $cursor['rebates_price'];
        $this->goods_num         = $cursor['goods_num'];
        $this->goods_category    = $cursor['goods_category'];
        $this->goods_price_sum   = $cursor['goods_price_sum'];
        $this->rebates_price_sum = $cursor['rebates_price_sum'];
        $this->spec_id           = $cursor['spec_id'];
        $this->spec_name         = $cursor['spec_name'];
        $this->package           = $cursor['package'];
        $this->time              = $cursor['time'];
        $this->time_unit         = $cursor['time_unit'];
        $this->terminal          = $cursor['terminal'];
        $this->invoice           = $cursor["invoice"];
        $this->invoice_price     = $cursor["invoice_price"];
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

class OrderInvoiceEntry extends BaseInfo
{
    public $invoice_type        = null;   // 1普通发票,2专用发票
    public $title_type          = null;   // 1个人,2单位
    public $invoice_title       = null;   // 发票抬头名称
    public $duty_paragraph      = null;   // 税号
    public $phone               = null;   // 收票人电话号码
    public $email               = null;   // 邮箱
    public $unit_phone          = null;   // 单位号码
    public $unit_address        = null;   // 单位地址
    public $bank_name           = null;   // 单位的开户行名称
    public $bank_account        = null;   // 单位的银行账号

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
        $this->invoice_type    = $cursor['invoice_type'];
        $this->title_type      = $cursor['title_type'];
        $this->invoice_title   = $cursor['invoice_title'];
        $this->duty_paragraph  = $cursor['duty_paragraph'];
        $this->phone           = $cursor['phone'];
        $this->email           = $cursor['email'];
        $this->unit_phone      = $cursor['unit_phone'];
        $this->unit_address    = $cursor['unit_address'];
        $this->bank_name       = $cursor['bank_name'];
        $this->bank_account    = $cursor['bank_account'];
    }
}

class OrderAddressEntry extends BaseInfo
{

    public $address      = null; // 地址
    public $province     = null; // 省
    public $city         = null; // 市
    public $area         = null; // 区
    public $phone        = null; // 联系电话
    public $name         = null; // 联系人

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
        $this->address      = $cursor["address"];
        $this->province     = $cursor["province"];
        $this->city         = $cursor["city"];
        $this->area         = $cursor["area"];
        $this->phone        = $cursor["phone"];
        $this->name         = $cursor["name"];
    }
}

class GoodsOrderEntry extends BaseInfo
{
    public $goods_order_id     = null;    // 订单id
    public $shop_id            = null;    // 店铺id（店铺购买时）
    public $agent_id           = null;    // 代理商id（代理商购买时）
    //public $order_water_num  = null;    // 订单流水号
    public $pay_way            = null;    // 支付方式(0:未确定,1:余额支付, 2:微信支付, 3:支付宝支付)
    public $order_status       = null;    // 订单状态(1:待付款,2:待发货,3:待收货,4:待评价,5:已评价,6:退款中,7:退款失败,8:已退款,9:已关闭)
    public $goods_list         = null;    // 商品列表
    public $order_time         = null;    // 下单时间及创建时间(时间戳)
    public $pay_time           = null;    // 支付时间(时间戳)
    public $refunds_time       = null;    // 退款时间(时间戳)
    public $refunds_fail_time  = null;    // 退款失败时间(时间戳)
    public $close_time         = null;    // 关闭时间(时间戳)
    public $lastmodtime        = null;    // 最后修改时间(时间戳)
    public $delete             = null;    // 0:未删除; 1:已删除
    public $uesr_delete        = null;    // (用户删除)0:未删除; 1:已删除
    public $goods_num_all      = null;    // 商品总数
    public $goods_price_all    = null;    // 商品总价
    public $rebates_price_all  = null;    // 商品折扣总价
    public $paid_price         = null;    // 实收金额（收银员收款金额）
    public $freight_price      = null;    // 运费
    public $deliver_address    = null;    // 送货地址
    public $order_address      = null;    // 发货地址
    //public $deliver_status     = null;    // 发货状态(0待发货1待收货2已收货)
    public $deliver_time       = null;    // 发货时间
    public $express_company_id = null;    // 快递公司id
    public $express_num        = null;    // 快递单号
    public $order_fee          = null;    // 订单金额（按定价算出来的当前消费额）
    public $order_remark       = null;    // 订单备注
    public $invoice            = null;    // 发票信息
    public $invoice_status     = null;    // 开票状态（0:不开票,1:需要开票,2:已开票)
    public $rebates            = null;    // 折扣
    public $is_urge            = null;    // 是否催单（1:已催单,0:未催单)
    public $no_deliver         = null;    // 是否不用发货（1:不用,0:要)
    public $deliver_remark     = null;    // 发货备注


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
        $this->goods_order_id     = $cursor['goods_order_id'];
        $this->shop_id            = $cursor['shop_id'];
        $this->agent_id           = $cursor['agent_id'];
        //$this->order_water_num  = $cursor['order_water_num'];
        $this->pay_way            = $cursor['pay_way'];
        $this->order_status       = $cursor['order_status'];
        $this->goods_list         = OrderGoodsList::ToList($cursor['goods_list']);
        $this->order_time         = $cursor['order_time'];
        $this->pay_time           = $cursor['pay_time'];
        $this->refunds_time       = $cursor['refunds_time'];
        $this->refunds_fail_time  = $cursor['refunds_fail_time'];
        $this->close_time         = $cursor['close_time'];
        $this->lastmodtime        = $cursor['lastmodtime'];
        $this->delete             = $cursor['delete'];
        $this->uesr_delete        = $cursor['uesr_delete'];
        $this->goods_num_all      = $cursor['goods_num_all'];
        $this->goods_price_all    = $cursor['goods_price_all'];
        $this->rebates_price_all  = $cursor['rebates_price_all'];
        $this->paid_price         = $cursor['paid_price'];
        $this->freight_price      = $cursor['freight_price'];
        $this->deliver_address    = new OrderAddressEntry($cursor['deliver_address']);
        $this->order_address      = new OrderAddressEntry($cursor['order_address']);
        $this->express_company_id = $cursor['express_company_id'];
        $this->express_num        = $cursor['express_num'];
        //$this->deliver_status     = $cursor['deliver_status'];
        $this->deliver_time       = $cursor['deliver_time'];
        $this->order_fee          = $cursor['order_fee'];
        $this->order_remark       = $cursor['order_remark'];
        $this->invoice            = new OrderInvoiceEntry($cursor['invoice']);
        $this->invoice_status     = $cursor['invoice_status'];
        $this->rebates            = $cursor['rebates'];
        $this->is_urge            = $cursor['is_urge'];
        $this->no_deliver         = $cursor['no_deliver'];
        $this->deliver_remark     = $cursor['deliver_remark'];
    }
}

class GoodsOrder extends MgoBase
{
    protected function Tablename()
    {
        return 'goods_order';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'goods_order_id' => (string)$info->goods_order_id
        );

        $set = array(
            "goods_order_id"    => (string)$info->goods_order_id,
            "lastmodtime" => (null === $info->lastmodtime) ? time() : (int)$info->lastmodtime,
        );


        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->agent_id)
        {
            $set["agent_id"] = (string)$info->agent_id;
        }
        if(null !== $info->dine_way)
        {
            $set["dine_way"] = (int)$info->dine_way;
        }
        if(null !== $info->pay_way)
        {
            $set["pay_way"] = (int)$info->pay_way;
        }
        if(null !== $info->goods_list)
        {
            $goods_list = [];
            foreach ($info->goods_list as $v) {
                $p                  = new OrderGoodsList();
                //$p->id              = (string)$v->id;
                $p->goods_id          = (string)$v->goods_id;
                $p->goods_name        = (string)$v->goods_name;
                $p->goods_price       = (float)$v->goods_price;
                $p->rebates_price     = (float)$v->rebates_price;
                $p->goods_num         = (int)$v->goods_num;
                $p->goods_price_sum   = (float)$v->goods_price_sum;
                $p->rebates_price_sum = (float)$v->rebates_price_sum;
                $p->spec_id           = (string)$v->spec_id;
                $p->spec_name         = (string)$v->spec_name;
                $p->package           = (string)$v->package;
                $p->time              = (int)$v->time;
                $p->time_unit         = (int)$v->time_unit;
                $p->terminal          = (int)$v->terminal;
                $p->invoice           = (int)$v->invoice;
                $p->invoice_price     = (float)$v->invoice_price;
                $goods_list[]   = $p;
            }
            $set["goods_list"] = $goods_list;
        }
        if(null !== $info->order_status)
        {
            $set["order_status"] = (int)$info->order_status;
        }
        if(null !== $info->order_time)
        {
            $set["order_time"] = (int)$info->order_time;
        }
        if(null !== $info->pay_time)
        {
            $set["pay_time"] = (int)$info->pay_time;
        }
        // if(null !== $info->order_water_num)
        // {
        //     $set["order_water_num"] = (string)$info->order_water_num;
        // }
        if(null !== $info->refunds_time)
        {
            $set["refunds_time"] = (int)$info->refunds_time;
        }
        if(null !== $info->refunds_fail_time)
        {
            $set["refunds_fail_time"] = (int)$info->refunds_fail_time;
        }
        if(null !== $info->close_time)
        {
            $set["close_time"] = (int)$info->close_time;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->uesr_delete)
        {
            $set["uesr_delete"] = (int)$info->uesr_delete;
        }
        if(null !== $info->goods_num_all)
        {
            $set["goods_num_all"] = (int)$info->goods_num_all;
        }
        if(null !== $info->goods_price_all)
        {
            $set["goods_price_all"] = (float)$info->goods_price_all;
        }
        if(null !== $info->rebates_price_all)
        {
            $set["rebates_price_all"] = (float)$info->rebates_price_all;
        }
        if(null !== $info->paid_price)
        {
            $set["paid_price"] = (float)$info->paid_price;
        }
        if(null !== $info->freight_price)
        {
            $set["freight_price"] = (float)$info->freight_price;
        }
        if(null !== $info->deliver_address)
        {
            $address = new OrderAddressEntry();
            $address->address      = $info->deliver_address->address;
            $address->province     = $info->deliver_address->province;
            $address->city         = $info->deliver_address->city;
            $address->area         = $info->deliver_address->area;
            $address->phone        = $info->deliver_address->phone;
            $address->name         = $info->deliver_address->name;
            $set["deliver_address"] = $address;
        }
        if(null !== $info->order_address)
        {
            $address = new OrderAddressEntry();
            $address->address      = $info->order_address->address;
            $address->province     = $info->order_address->province;
            $address->city         = $info->order_address->city;
            $address->area         = $info->order_address->area;
            $address->phone        = $info->order_address->phone;
            $address->name         = $info->order_address->name;
            $set["order_address"] = $address;
        }
        if(null !== $info->express_company_id)
        {
            $set["express_company_id"] = (string)$info->express_company_id;
        }
        if(null !== $info->express_num)
        {
            $set["express_num"] = (string)$info->express_num;
        }
        // if(null !== $info->deliver_status)
        // {
        //     $set["deliver_status"] = (int)$info->deliver_status;
        // }
        if(null !== $info->deliver_time)
        {
            $set["deliver_time"] = (int)$info->deliver_time;
        }
        if(null !== $info->order_remark)
        {
            $set["order_remark"] = (string)$info->order_remark;
        }

        if(null !== $info->order_fee)
        {
            $set["order_fee"] = (float)$info->order_fee;
        }

        if(null !== $info->invoice)
        {
            $invoice = new OrderInvoiceEntry();
            $invoice->invoice_type    = $info->invoice->invoice_type;
            $invoice->title_type      = $info->invoice->title_type;
            $invoice->invoice_title   = $info->invoice->invoice_title;
            $invoice->duty_paragraph  = $info->invoice->duty_paragraph;
            $invoice->phone           = $info->invoice->phone;
            $invoice->email           = $info->invoice->email;
            $invoice->unit_phone      = $info->invoice->unit_phone;
            $invoice->unit_address    = $info->invoice->unit_address;
            $invoice->bank_name       = $info->invoice->bank_name;
            $invoice->bank_account    = $info->invoice->bank_account;
            $set["invoice"] = $invoice;
        }
        if(null !== $info->invoice_status)
        {
            $set["invoice_status"] = (int)$info->invoice_status;
        }
        if(null !== $info->rebates)
        {
            $set["rebates"] = (float)$info->rebates;
        }
        if(null !== $info->is_urge)
        {
            $set["is_urge"] = (int)$info->is_urge;
        }
        if(null !== $info->no_deliver)
        {
            $set["no_deliver"] = (int)$info->no_deliver;
        }
        if(null !== $info->deliver_remark)
        {
            $set["deliver_remark"] = (string)$info->deliver_remark;
        }

        $value = array(
            '$set' => $set
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

    public function BatchDelete($id_list)
    {
        return parent::DoBatchDelete($id_list, "goods_order_id");
    }

    public function GetGoodsOrderById($goods_order_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$goods_order_id, "goods_order_id");
        return GoodsOrderEntry::ToObj($cursor);
    }

    public function GetList($filter=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = ['delete'  => ['$ne'=>1]];
        if(null != $filter)
        {
            $pay_time = $filter['pay_time'];
            if(!empty($pay_time))
            {
                $cond['pay_time'] = ['$lte'=> $pay_time];
            }
            $order_status = $filter['order_status'];
            if(!empty($order_status))
            {
                $cond['order_status'] = (int)$order_status;
            }
        }
        $sortby['_id'] = -1;
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort($sortby);
        return GoodsOrderEntry::ToList($cursor);
    }

    public function GetGoodsOrderList($filter=null, $sortby=[], $page_size=5, $page_no=1, &$total=null, &$price_list=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = ['delete'  => ['$ne'=>1]];
        if(null != $filter)
        {
            $goods_order_id = $filter['goods_order_id'];
            if(!empty($goods_order_id))
            {
                $cond['goods_order_id'] = new \MongoRegex("/$goods_order_id/");
            }
            $uesr_delete = $filter['uesr_delete'];
            if(!empty($uesr_delete))
            {
                $cond['uesr_delete'] = ['$ne'=>1];
            }
            $order_status = $filter['order_status'];
            if(!empty($order_status))
            {
                $cond['order_status'] = (int)$order_status;
            }
            $invoice_status = $filter['invoice_status'];
            if(null != $invoice_status)
            {
                $cond['invoice_status'] = (int)$invoice_status;
            }
            $agent_id = $filter['agent_id'];
            if(!empty($agent_id))
            {
                $cond['agent_id'] = (string)$agent_id;
            }
            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $express_company_id = $filter['express_company_id'];
            if(!empty($express_company_id))
            {
                $cond['express_company_id'] = (string)$express_company_id;
            }
            $begin_time = $filter['begin_time'];
            $end_time = $filter['end_time'];
            if(!empty($begin_time))
            {
                $cond['order_time'] = [
                    '$gte' => (int)$begin_time,
                    '$lte' => (int)$end_time
                ];
            }
            $deliver_begin_time = $filter['deliver_begin_time'];
            $deliver_end_time   = $filter['deliver_end_time'];
            if(!empty($deliver_begin_time))
            {
                $cond['deliver_time'] = [
                    '$gte' => (int)$deliver_begin_time,
                    '$lte' => (int)$deliver_end_time
                ];
            }
            $order_status_list = $filter['order_status_list'];

            if(!empty($order_status_list))
            {
                foreach($order_status_list as $i => &$item)
                {
                    $item = (int)$item;
                }
                $cond["order_status"] = ['$in' => $order_status_list];
            }
            // 订单来源（1代理商2商户）
            $goods_order_from = $filter['goods_order_from'];
            if(!empty($goods_order_from))
            {
                if(1 == $goods_order_from)
                {
                    $cond['agent_id'] = ['$ne'=>null];
                }
                if(2 == $goods_order_from)
                {
                    $cond['shop_id'] = ['$ne'=>null];
                }
            }
            //统计取支付成功的数据字段
            $pay_status = $filter['pay_status'];
            if(!empty($pay_status))
            {
                $cond['pay_time'] = ['$gt' => 0 ];
            }
        }

        if(empty($sortby))
        {
            $sortby['_id'] = -1;
        }

        LogDebug($sortby);
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort($sortby)->skip(((int)$page_no - 1) * (int)$page_size)->limit((int)$page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        //聚合条件算出:订单总价
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'               => null,
                    'all_order_fee'     => ['$sum' => '$order_fee']
                ],
            ],
        ];
        //LogDebug($pipe);
        $all_list = $table->aggregate($pipe);
        if ($all_list['ok'] == 1) {
            $price_list = $all_list['result'][0];
        } else {
            $price_list = null;
        }
        return GoodsOrderEntry::ToList($cursor);
    }
    //用户删除
    public function BatchUserDeleteById($id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($id_list as $i => &$id)
        {
            $id = (string)$id;
        }
        $cond = array(
            'goods_order_id' => array('$in' => $id_list)
        );
        $set = array(
            "lastmodtime" => time(),
            "uesr_delete" => 1
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

    //15天自动收货
    public function AutomaticCollectGoods($id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($id_list as $i => &$id)
        {
            $id = (string)$id;
        }
        $cond = array(
            'goods_order_id' => array('$in' => $id_list)
        );
        $set = array(
            "lastmodtime" => time(),
            "order_status" => 4
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
}


?>