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
    public $order_id          = null;    // 订单id
    public $customer_id       = null;    // 顾客用户id
    public $employee_id       = null;    // 收银员id
    public $order_from        = null;    // 订单来源(1:智能收银机, 2:自助点餐机, 3:扫码点餐,4:平板智能点餐机,5:掌柜通,6.小程序)
    public $order_water_num   = null;    // 订单流水号
    public $shop_id           = null;    // 餐馆id
    public $dine_way          = null;    // 用餐方式(1:在店吃, 2:自提, 3:打包, 4:外卖)
    public $pay_way           = null;    // 支付方式(0:未确定,1:现金支付, 2:微信支付, 3:支付宝支付, 4:银行卡支付,5:挂账,6:餐后支付（需要pad端确认))
    public $pay_status        = null;    // 支付状态(0:未确定,1:未支付, 2:已支付)
    public $order_sure_status = null;    // 订单确定状态(1:未下单,2:下单,3:下单并支付)
    public $order_status      = null;    // 订单状态(1:未支付(待付款),2:已支付(交易完成),3:已反结,4:退款成功,5:退款失败,6:已关闭,7:挂账,8:退款中)
    public $is_confirm        = null;    // 是否已确认订单（pad端的下单功能） （1:已确认,0:未确认）手机端的订单都属于未确认
    public $customer_num      = null;    // 顾客人数
    public $seat_id           = null;    // 餐桌座位
    public $food_list         = null;    // 餐品列表
    //public $status_info     = null;    // 状态信息
    public $order_time        = null;    // 下单时间及创建时间(时间戳)
    public $pay_time          = null;    // 支付时间(时间戳)//<<<<<<<<<<<<<所有的的状态时间与order_status里面的操作时间made_time重复了,这些字段可以进行删除的。
    public $checkout_time     = null;    // 反结时间(时间戳)
    public $refunds_time      = null;    // 退款时间(时间戳)
    public $refunds_fail_time = null;    // 退款失败时间(时间戳)
    public $close_time        = null;    // 关闭时间(时间戳)
    public $lastmodtime       = null;    // 最后修改时间(时间戳)
    public $delete            = null;    // 0:未删除; 1:已删除
    public $food_num_all      = null;    // 菜总数
    public $food_price_all    = null;    // 餐品总价
    public $order_waiver_fee  = null;    // 减免金额
    public $order_payable     = null;    // 应付金额（客人应该支付的金额：order_fee-order_waiver_fee）
    public $paid_price        = null;    // 实收金额（收银员收款金额）
    public $maling_price      = null;    // 抹零金额
    public $seat_price        = null;    // 餐位费
    public $order_fee         = null;    // 订单金额（按定价算出来的当前消费额）
    public $order_remark      = null;    // 订单备注
    public $invoice           = null;    // 发票信息
    public $is_appraise       = null;    // 是否评价（1:已评价,0:待评价)
    public $is_urge           = null;    // 是否催单（1:已催单,0:未催单)
    public $is_invoicing      = null;    // 是否开票（1:已普通开票,0:未普通开票)
    public $red_dashed        = null;    // 是否红冲（1:红冲,0:2未红冲前提已开票才能红冲)
    public $plate             = null;    // 餐牌号advance
    public $is_advance        = null;    // 是否预结账    （1:已预结,0:未预结）（只有未支付的状态下才能预结账）
    public $is_ganged         = null;    // 1.联动启用(所有端都要显示订单数据),0.独立启用(只有PC端先订单数据)
    public $selfhelp_id       = null;    // 自助点餐机id
    public $customer_phone    = null;    // 订单消费顾客电话
    public $kitchen_status    = null;    // 制造状态（1.等待制作,2.制作完成)

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
        $this->order_id          = $cursor['order_id'];
        $this->customer_id       = $cursor['customer_id'];
        $this->employee_id       = $cursor['employee_id'];
        $this->order_from        = $cursor['order_from'];
        $this->order_water_num   = $cursor['order_water_num'];
        $this->shop_id           = $cursor['shop_id'];
        $this->dine_way          = $cursor['dine_way'];
        $this->pay_way           = $cursor['pay_way'];
        $this->pay_status        = $cursor['pay_status'];
        $this->order_status      = $cursor['order_status'];
        $this->order_sure_status = $cursor['order_sure_status'];
        $this->customer_num      = $cursor['customer_num'];
        $this->seat_id           = $cursor['seat_id'];
        $this->food_list         = OrderFoodInfo::ToList($cursor['food_list']);
        //$this->status_info       = New StatusInfo($cursor['status_info']);
        $this->order_time        = $cursor['order_time'];
        $this->pay_time          = $cursor['pay_time'];
        $this->checkout_time     = $cursor['checkout_time'];
        $this->refunds_time      = $cursor['refunds_time'];
        $this->refunds_fail_time = $cursor['refunds_fail_time'];
        $this->close_time        = $cursor['close_time'];
        $this->lastmodtime       = $cursor['lastmodtime'];
        $this->delete            = $cursor['delete'];
        $this->food_num_all      = $cursor['food_num_all'];
        $this->food_price_all    = $cursor['food_price_all'];
        $this->order_waiver_fee  = $cursor['order_waiver_fee'];
        $this->order_payable     = $cursor['order_payable'];
        $this->paid_price        = $cursor['paid_price'];
        $this->maling_price      = $cursor['maling_price'];
        $this->seat_price        = $cursor['seat_price'];
        $this->order_fee         = $cursor['order_fee'];
        $this->order_remark      = $cursor['order_remark'];
        $this->invoice           = $cursor['invoice'];
        $this->is_appraise       = $cursor['is_appraise'];
        $this->is_urge           = $cursor['is_urge'];
        $this->is_invoicing      = $cursor['is_invoicing'];
        $this->red_dashed        = $cursor['red_dashed'];
        $this->plate             = $cursor['plate'];
        $this->is_advance        = $cursor['is_advance'];
        $this->is_confirm        = $cursor['is_confirm'];
        $this->is_ganged         = $cursor['is_ganged'];
        $this->selfhelp_id       = $cursor['selfhelp_id'];
        $this->customer_phone    = $cursor['customer_phone'];
        $this->kitchen_status    = $cursor['kitchen_status'];
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
        if(null !== $info->is_ganged)
        {
            $set["is_ganged"] = (int)$info->is_ganged;
        }
        if(null !== $info->customer_phone)
        {
            $set["customer_phone"] = (string)$info->customer_phone;
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
        if(null !== $info->seat_price)
        {
            $set["seat_price"] = (float)$info->seat_price;
        }
        if(null !== $info->paid_price)
        {
            $set["paid_price"] = (float)$info->paid_price;
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
        if(null !== $info->pay_time)
        {
            $set["pay_time"] = (int)$info->pay_time;
        }
         if(null !== $info->pay_status)
        {
            $set["pay_status"] = (int)$info->pay_status;
        }
        if(null !== $info->is_confirm)
        {
            $set["is_confirm"] = (int)$info->is_confirm;
        }
        if(null !== $info->order_ready)
        {
            $set["order_ready"] = (int)$info->order_ready;
        }
        if(null !== $info->selfhelp_id)
        {
            $set["selfhelp_id"] = (string)$info->selfhelp_id;
        }
        if(null !== $info->kitchen_status)
        {
            $set["kitchen_status"] = (int)$info->kitchen_status;
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

    public function GetOrderList($filter=null, $field=[], $sortby=[])
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
        //var_dump($cond);
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