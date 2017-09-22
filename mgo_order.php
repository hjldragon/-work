<?php
/*
 * [Rocky 2017-05-12 19:39:55]
 * 订单表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("redis_id.php");



class OrderFoodInfo
{
    public $id               = null;     // 当前数据id（主键）
    public $food_id          = null;     // 餐品id（每个餐品唯一，但在加菜时可能重复）
    public $food_name        = null;     // 餐品名
    public $food_price       = null;     // 餐品单价(单位元)
    public $food_num         = null;     //
    public $food_category    = null;     // 餐品分类
    public $food_price_sum   = null;     // 餐品费用(=food_price*food_num，及food_unit、unit_num)
    public $food_attach_list = null;     // 口味附加属性list（加辣等）
    public $food_unit        = null;     // 店铺餐品单位（份、碗、斤等）
    public $unit_num         = null;     // 餐品量(即点了多少斤等)
    public $pack_num         = null;     // 打包数量

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
        $this->id               = $cursor['id'];
        $this->food_id          = $cursor['food_id'];
        $this->food_name        = $cursor['food_name'];
        $this->food_price       = $cursor['food_price'];
        $this->food_num         = $cursor['food_num'];
        $this->food_category    = $cursor['food_category'];
        $this->food_price_sum   = $cursor['food_price_sum'];
        $this->food_attach_list = $cursor['food_attach_list'];
        $this->food_unit        = $cursor['food_unit'];
        $this->unit_num         = $cursor['unit_num'];
        $this->pack_num         = $cursor['pack_num'];
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

class InvoiceOrederInfo
{   
    public $type           = null;   // 发票类型(1:普通发票,2:专用发票)
    public $title_type     = null;   // 发票抬头类型(1:单位,2:个人)
    public $invoice_title  = null;   // 发票抬头名称
    public $duty_paragraph = null;   // 税号
    public $phone          = null;   // 电话号码
    public $address        = null;   // 地址
    public $bank_name      = null;   // 单位的开户行名称
    public $bank_account   = null;   // 单位的银行账号
    public $email          = null;   // 电子邮箱
    


    // // 具体业务数据
    // public $shop_id = null;     // 当前用户所属的店 （注，此这段分出到员工表）

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
        $this->type           = $cursor['type'];
        $this->title_type     = $cursor['title_type'];
        $this->invoice_title  = $cursor['invoice_title'];
        $this->duty_paragraph = $cursor['duty_paragraph'];
        $this->phone          = $cursor['phone'];
        $this->address        = $cursor['address'];
        $this->bank_name      = $cursor['bank_name'];
        $this->bank_account   = $cursor['bank_account'];
        $this->email          = $cursor['email'];
    }
    
};

class OrderEntry
{
    public $order_id         = null;    // 订单id
    public $customer_id      = null;    // 顾客用户id
    public $shop_id          = null;    // 餐馆id
    public $dine_way         = null;    // 用餐方式(0:未确定, 1:在店吃, 2:打包)
    public $pay_way          = null;    // 支付方式(0:未确定, 1:现金, 2:微信支付)
    public $customer_num     = null;    // 顾客人数
    public $seat_id          = null;    // 餐桌座位
    public $food_list        = null;    // 餐品列表
    public $order_status     = null;    // 订单状态(0:不确定,1:待付款,2:交易完成,3:退款中,4:退款成功,5:退款失败,6:待评价)
    public $drawback_reason  = null;    //退款原因
    public $order_time       = null;    // 下单时间(时间戳)
    public $lastmodtime      = null;    //最后修改时间
    public $delete           = null;    // 0:未删除; 1:已删除
    public $food_num_all     = null;    // 菜总数
    public $food_price_all   = null;    // 餐品总价
    public $order_waiver_fee = null;    // 减免金额
    public $order_payable    = null;    // 应付金额（客人应该支付的金额：order_fee-order_waiver_fee）
    public $seat_price       = null;    // 餐位费
    public $order_fee        = null;    // 订单金额（按定价算出来的当前消费额）
    public $invoice          = null;    // 发票信息
    public $invoice_type     = null;    // 发票材质类型(0:不开发票,1:纸质,2:电子)
   

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
        $this->order_id         = $cursor['order_id'];
        $this->shop_id          = $cursor['shop_id'];
        $this->customer_id      = $cursor['customer_id'];
        $this->dine_way         = $cursor['dine_way'];
        $this->pay_way          = $cursor['pay_way'];
        $this->customer_num     = $cursor['customer_num'];
        $this->seat_id          = $cursor['seat_id'];
        $this->food_list        = OrderFoodInfo::ToList($cursor['food_list']);
        $this->order_status     = $cursor['order_status'];
        $this->drawback_reason  = $cursor['drawback_reason'];
        $this->order_time       = $cursor['order_time'];
        $this->lastmodtime      = $cursor['lastmodtime'];
        $this->delete           = $cursor['delete'];
        $this->food_num_all     = $cursor['food_num_all'];
        $this->food_price_all   = $cursor['food_price_all'];
        $this->order_waiver_fee = $cursor['order_waiver_fee'];
        $this->order_payable    = $cursor['order_payable'];
        $this->seat_price       = $cursor['seat_price'];
        $this->order_fee        = $cursor['order_fee'];
        $this->invoice          = new InvoiceOrederInfo($cursor['food_list']);
        $this->invoice_type     = $cursor['invoice_type'];
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

class Order
{
    private function Tablename()
    {
        return 'order';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'order_id' => (string)$info->order_id
        );

        $set = array(
            "order_id"    => (string)$info->order_id,
            "lastmodtime" => (null === $info->lastmodtime) ? time() : (int)$info->lastmodtime,
        );

        if(null !== $info->customer_id)
        {
            $set["customer_id"] = (int)$info->customer_id;
        }
        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->dine_way)
        {
            $set["dine_way"] = (int)$info->dine_way;
        }
        if(null !== $info->pay_way)
        {
            $set["pay_way"] = (int)$info->pay_way;
        }
        if(null !== $info->customer_num)
        {
            $set["customer_num"] = (int)$info->customer_num;
        }
        if(null !== $info->seat_id)
        {
            $set["seat_id"] = (string)$info->seat_id;
        }
        if(null !== $info->food_list)
        {
            $food_list = [];
            foreach($info->food_list as $i => $item)
            {
                $id = $item->id;
                if(!$id)
                {
                    $id = \DaoRedis\Id::GenOrderFoodId();
                }
                array_push($food_list, new OrderFoodInfo([
                    'id'               => (string)$id,
                    'food_id'          => (string)$item->food_id,
                    'food_name'        => (string)$item->food_name,
                    'food_price'       => (float)$item->food_price,
                    'food_price_sum'   => (float)$item->food_price_sum,
                    'food_num'         => (int)$item->food_num,
                    'unit_num'         => (float)$item->unit_num,
                    'pack_num'         => (float)$item->pack_num,
                ]));
            }
            $set["food_list"] = $food_list;
        }
        if(null !== $info->order_status)
        {
            $set["order_status"] = (int)$info->order_status;
        }
        if(null !== $info->drawback_reason)
        {
            $set["drawback_reason"] = (string)$info->drawback_reason;
        }
        if(null !== $info->order_time)
        {
            $set["order_time"] = (int)$info->order_time;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->food_num_all)
        {
            $set["food_num_all"] = (int)$info->food_num_all;
        }

        if(null === $info->order_fee
            && null !== $info->food_price_all
            && null !== $info->seat_price
            && null !== $info->customer_num )
        {
            $info->order_fee = (float)$info->food_price_all  // 餐品总费用
                + (float)$info->seat_price * (int)$info->customer_num;  // 餐位费
        }

        if(null !== $info->order_waiver_fee
            && null !== $info->order_fee )
        {
            $info->order_payable = (float)$info->order_fee - (float)$info->order_waiver_fee;  //减免的费用
        }
        //
        if(null !== $info->seat_price)
        {
            $set["seat_price"] = (float)$info->seat_price;
        }
        if(null !== $info->order_fee)
        {
            $set["order_fee"] = (float)$info->order_fee;
        }
        if(null !== $info->food_price_all)
        {
            $set["food_price_all"] = (float)$info->food_price_all;
        }
        if(null !== $info->order_waiver_fee)
        {
            $set["order_waiver_fee"] = (float)$info->order_waiver_fee;
        }
        if(null !== $info->order_payable)
        {
            $set["order_payable"] = (float)$info->order_payable;
        }
        if(null !== $info->invoice_type)
        {
            $set["invoice_type"] = (int)$info->invoice_type;
        }
        if(null !== $info->invoice)
        {
            $invoice = [];
            foreach($info->invoice as $i => $item)
            {
                array_push($invoice, new InvoiceOrederInfo([
                    'type'            => (int)$type,
                    'title_type'      => (int)$item->title_type,
                    'invoice_title'   => (string)$item->invoice_title,
                    'duty_paragraph'  => (string)$item->duty_paragraph,
                    'phone'           => (string)$item->phone,
                    'address'         => (string)$item->address,
                    'bank_name'       => (string)$item->bank_name,
                    'email'           => (string)$item->email,
                    'invoice_type'    => (int)$invoice_type
                ]));
            }
            $set["invoice"] = $invoice;
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

    public function Delete($order_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'order_id' => (string)$order_id
        );

        $value = array(
            '$set' => array(
                'delete'      => 1,
                'lastmodtime' => time()
            )
        );

        try
        {
            $table->update($cond, $value, ['safe'=>true, 'upsert'=>true]);
            LogDebug("ret:" . $ret['ok']);
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function GetOrderById($order_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'order_id' => (string)$order_id
        );
        $cursor = $table->findOne($cond);
        return new OrderEntry($cursor);
    }

    public function GetOrderList($filter, $field=[], $sortby=[])
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = ['delete'  => ['$ne'=>1]];
        if(null != $filter)
        {
            $order_id = $filter['order_id'];
            if(!empty($order_id))
            {
                $cond['order_id'] = (string)$order_id;
            }
            $order_status = $filter['order_status'];
            if(!empty($order_status))
            {
                $cond['order_status'] = (int)$order_status;
            }
            $customer_id = $filter['customer_id'];
            if(!empty($customer_id))
            {
                $cond['customer_id'] = (int)$customer_id;
            }
            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $seat_id = $filter['seat_id'];
            if(!empty($seat_id))
            {
                $cond['seat_id'] = (string)$seat_id;
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
            $order_status_list = $filter['order_status_list'];

            if(!empty($order_status_list))
            {
                foreach($order_status_list as $i => &$item)
                {
                    $item = (int)$item;
                }
                $cond["order_status"] = ['$in' => $order_status_list];
            }
        }

        if(empty($sortby))
        {
            $sortby['_id'] = -1;
        }
        // LogDebug($cond);
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort($sortby);
        return OrderEntry::ToList($cursor);
    }

    // 取客人最近的一条订单
    public function GetLastOrder($customer_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'customer_id' => $customer_id
        ];
        // LogDebug($cond);
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(['order_time'=>-1])->limit(1);
        $list = OrderEntry::ToList($cursor);
        if(count($list) > 0)
        {
            return $list[0];
        }
        return null;
    }
}


?>
