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
    public $food_num         = null;     // 点餐数量
    public $pack_num         = null;     // 打包数量
    public $food_category    = null;     // 餐品分类
    public $food_price_sum   = null;     // 餐品费用(=food_price*food_num，及food_unit、unit_num)
    public $food_attach_list = null;     // 规格list（加辣等）
    public $food_unit        = null;     // 店铺餐品单位（份、碗、斤等）
    //public $unit_num         = null;     // 餐品量(即点了多少斤等)
    public $food_remark      = null;     // 餐品备注
    public $is_pack          = null;     // 是属于否打包（1.打包,0:不打包）
    public $is_send          = null;     // 是否属于赠送（1.赠送,0:不赠送）
    public $is_add           = null;     // 是否属于加菜（1.是,0:不是）
    public $send_remark      = null;     // 赠送理由
    public $weight           = null;     // 份量规格(0:无份量,1:大份,2:中份,3:小份)
    public $made_status      = null;     // 后厨制作状态(1.等待中,2.配菜中,3.制作中,4.制作完成(待传)，5.已传)
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
        $this->pack_num         = $cursor['pack_num'];
        $this->food_category    = $cursor['food_category'];
        $this->food_price_sum   = $cursor['food_price_sum'];
        $this->food_attach_list = SpecInfo::ToList($cursor['food_attach_list']);
        $this->food_unit        = $cursor['food_unit'];
        $this->food_remark      = $cursor['food_remark'];
        $this->is_pack          = $cursor['is_pack'];
        $this->is_send          = $cursor['is_send'];
        $this->is_add           = $cursor['is_add'];
        $this->send_remark      = $cursor['send_remark'];
        $this->weight           = $cursor['weight'];
        $this->made_status      = $cursor['made_status'];
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
    public $invoice_type   = null;   // 发票材质类型(0:不开发票,1:纸质,2:电子)




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
        $this->invoice_type   = $cursor['invoice_type'];
    }

};

class StatusInfo
{
    public $order_status     = null;     // 订单状态(1:未支付,2:已支付,3:已反结,4:退款成功,5:退款失败,6:已关闭,7:挂账)
    public $employee_id      = null;     // 操作人id
    public $made_time        = null;     // 操作时间
    public $made_reson       = null;     // 状态申请原因
    public $made_sf_reson    = null;     // 状态操作原因

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
        $this->order_status  = $cursor['order_status'];
        $this->employee_id   = $cursor['employee_id'];
        $this->made_time     = $cursor['made_time'];
        $this->made_reson    = $cursor['made_reson'];
        $this->made_sf_reson = $cursor['made_sf_reson'];
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

class SpecInfo
{
    public $title      = null;     // 口味
    public $spec_value = null;     // 口味属性

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
        $this->title      = $cursor['title'];
        $this->spec_value = $cursor['spec_value'];
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

class OrderEntry
{
    public $order_id         = null;    // 订单id
    public $customer_id      = null;    // 顾客用户id
    public $employee_id      = null;    // 收银员id
    public $order_from       = null;    // 订单来源(1:门店前台, 2:手持设备, 3:扫码自助)
    public $order_water_num  = null;    // 订单流水号
    public $shop_id          = null;    // 餐馆id
    public $dine_way         = null;    // 用餐方式(1:在店吃, 2:自提, 3:打包, 4:外卖)
    public $pay_way          = null;    // 支付方式(0:未确定,1:现金支付, 2:微信支付, 3:支付宝支付, 4:银行卡支付,5:挂账,6:餐后支付（需要pad端确认)
    public $pay_status       = null;    // 支付状态(0:未确定,1:未支付, 2:已支付, 3:挂账)
    public $order_sure_status= null;    // 订单确定状态(1:未下单,2:下单,3:下单并支付)
    public $order_status     = null;    // 订单状态(1:未支付(待付款),2:已支付(交易完成),3:已反结,4:退款成功,5:退款失败,6:已关闭,7:挂账,8:退款中)
    public $customer_num     = null;    // 顾客人数
    public $seat_id          = null;    // 餐桌座位
    public $food_list        = null;    // 餐品列表
    //public $status_info      = null;    // 状态信息
    public $order_time       = null;    // 下单时间及创建时间(时间戳)
    public $pay_time         = null;    // 支付时间(时间戳)
    public $checkout_time    = null;    // 反结时间(时间戳)
    public $refunds_time     = null;    // 退款时间(时间戳)
    public $refunds_fail_time= null;    // 退款失败时间(时间戳)
    public $close_time       = null;    // 关闭时间(时间戳)
    public $lastmodtime      = null;    // 最后修改时间(时间戳)
    public $delete           = null;    // 0:未删除; 1:已删除
    public $food_num_all     = null;    // 菜总数
    public $food_price_all   = null;    // 餐品总价
    public $order_waiver_fee = null;    // 减免金额
    public $order_payable    = null;    // 应付金额（客人应该支付的金额：order_fee-order_waiver_fee）
    public $paid_price       = null;    // 实收金额（收银员收款金额）
    public $maling_price     = null;    // 抹零金额
    public $seat_price       = null;    // 餐位费
    public $order_fee        = null;    // 订单金额（按定价算出来的当前消费额）
    public $order_remark     = null;    // 订单备注
    public $invoice          = null;    // 发票信息
    public $is_appraise      = null;    // 是否评价（1:已评价,0:待评价)
    public $is_urge          = null;    // 是否催单（1:已催单,0:未催单)
    public $is_invoicing     = null;    // 是否开票（1:已普通开票,0:未普通开票)
    public $red_dashed       = null;    // 是否红冲（1:红冲,0:2未红冲前提已开票才能红冲)
    public $plate            = null;    // 餐牌号
    public $is_advance       = null;    // 是否预结账    （1:已预结,0:未预结）（只有未支付的状态下才能预结账）
    public $is_confirm       = null;    // 是否已确认订单 （1:已确认,0:未确认）手机端的订单都属于未确认
    public $is_ganged         = null;    // 启用类型(1.联动启用(pc,pad有订单数据显示),2.独立启用(PAD没有订单数据显示))
    public $selfhelp_id       = null;    // 自助点餐机id
    public $customer_phone    = null;    // 订单消费顾客电话

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
            $set["customer_id"] = (string)$info->customer_id;
        }
        if(null !== $info->employee_id)
        {
            $set["employee_id"] = (string)$info->employee_id;
        }
        if(null !== $info->order_from)
        {
            $set["order_from"] = (int)$info->order_from;
        }
        if(null !== $info->order_water_num)
        {
            $set["order_water_num"] = (string)$info->order_water_num;
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
        if(null !== $info->pay_status)
        {
            $set["pay_status"] = (int)$info->pay_status;
        }
        if(null !== $info->order_status)
        {
            $set["order_status"] = (int)$info->order_status;
        }

        if(null !== $info->order_sure_status)
        {

            $set["order_sure_status"] = (int)$info->order_sure_status;
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
                $spec_info = [];
                if(count($item->food_attach_list) > 0)
                {
                    foreach ($item->food_attach_list as $key => $value)
                    {
                        $s = new SpecInfo();
                        $s->title      = (string)$value->title;
                        $s->spec_value = (string)$value->spec_value;
                        $spec_info[] = $s;
                    }
                }
                $p = new OrderFoodInfo();
                $p->id               = (string)$id;
                $p->food_id          = (string)$item->food_id;
                $p->food_name        = (string)$item->food_name;
                $p->food_price       = (float)$item->food_price;
                $p->food_price_sum   = (float)$item->food_price_sum;
                $p->food_num         = (int)$item->food_num;
                $p->food_category    = (string)$item->food_category;
                $p->food_unit        = (string)$item->food_unit;
                $p->unit_num         = (float)$item->unit_num;
                $p->is_pack          = (int)$item->is_pack;
                $p->is_send          = (int)$item->is_send;
                $p->is_add           = (int)$item->is_add;
                $p->send_remark      = (string)$item->send_remark;
                $p->pack_num         = (int)$item->pack_num;
                $p->weight           = (int)$item->weight;
                $p->made_status      = (int)$item->made_status;
                $p->food_attach_list = $spec_info;
                $food_list[] = $p;
            }
            $set["food_list"] = $food_list;;
        }
//        if(null !== $info->status_info)
//        {
////            $set["status_info"] = new StatusInfo([
////                'order_status'  => (int)$info->status_info->order_status,
////                'employee_id'   => (string)$info->status_info->employee_id,
////                'made_time'     => (int)$info->status_info->made_time,
////                'made_reson'    => (string)$info->status_info->made_reson,
////                'made_sf_reson' => (string)$info->status_info->made_sf_reson,
////            ]);
//            if(null !== $info->status_info->order_status){
//                $set["status_info.order_status"] = (int)$info->status_info->order_status;
//            }
//            if(null !== $info->status_info->employee_id){
//                $set["status_info.employee_id"] = (string)$info->status_info->employee_id;
//            }
//            if(null !== $info->status_info->made_time){
//                $set["status_info.made_time"] = (int)$info->status_info->made_time;
//            }
//            if(null !== $info->status_info->made_reson){
//                $set["status_info.made_reson"] = (string)$info->status_info->made_reson;
//            }
//            if(null !== $info->status_info->made_sf_reson){
//                $set["status_info.made_sf_reson"] = (string)$info->status_info->made_sf_reson;
//            }
//        }
        if(null !== $info->order_time)
        {
            $set["order_time"] = (int)$info->order_time;
        }
        if(null !== $info->pay_time)
        {
            $set["pay_time"] = (int)$info->pay_time;
        }
        if(null !== $info->checkout_time)
        {
            $set["checkout_time"] = (int)$info->checkout_time;
        }
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
        if($info->food_num_all > 0)
        {
            $set["food_num_all"] = (int)$info->food_num_all;
        }
        if(null !== $info->food_price_all)
        {
            $set["food_price_all"] = (float)$info->food_price_all;
        }
        if(null !== $info->order_waiver_fee)
        {
            $set["order_waiver_fee"] = (float)$info->order_waiver_fee;
        }
        if(null !== $info->order_waiver_fee
            && null !== $info->order_fee )
        {
            $info->order_payable = (float)$info->order_fee - (float)$info->order_waiver_fee;  //减免的费用
        }
        if(null !== $info->order_payable)
        {
            $set["order_payable"] = (float)$info->order_payable;
        }
        if(null !== $info->paid_price)
        {
            $set["paid_price"] = (float)$info->paid_price;
        }
        if(null !== $info->maling_price)
        {
            $set["maling_price"] = (float)$info->maling_price;
        }
        if(null !== $info->seat_price)
        {
            $set["seat_price"] = (float)$info->seat_price;
        }
        if(null !== $info->order_fee)
        {
            $set["order_fee"] = (float)$info->order_fee;
        }
        if(null === $info->order_fee
            && null !== $info->food_price_all
            && null !== $info->seat_price
            && null !== $info->customer_num )
        {
            $info->order_fee = (float)$info->food_price_all  // 餐品总费用
                + (float)$info->seat_price * (int)$info->customer_num;  // 餐位费
        }
        if(null !== $info->order_remark)
        {
            $set["order_remark"] = (string)$info->order_remark;
        }
        if(null !== $info->invoice)
        {
//            $set["invoice"] = new  InvoiceOrederInfo([
//                'type'           => (int)$info->invoice->type,
//                'title_type'     => (int)$info->invoice->title_type,
//                'invoice_title'  => (string)$info->invoice->invoice_title,
//                'duty_paragraph' => (string)$info->invoice->duty_paragraph,
//                'phone'          => (string)$info->invoice->phone,
//                'address'        => (string)$info->invoice->address,
//                'bank_name'      => (string)$info->invoice->bank_name,
//                'email'          => (string)$info->invoice->email,
//                'invoice_type'   => (int)$info->invoice->invoice_type
//            ]);
            $set["invoice"] = $info->invoice;
        }
        if(null !== $info->is_appraise)
        {
            $set["is_appraise"] = (int)$info->is_appraise;
        }
        if(null !== $info->is_urge)
        {
            $set["is_urge"] = (int)$info->is_urge;
        }
        if(null !== $info->is_invoicing)
        {
            $set["is_invoicing"] = (int)$info->is_invoicing;
        }
        if(null !== $info->red_dashed)
        {
            $set["red_dashed"] = (int)$info->red_dashed;
        }
        if(!empty($info->plate))
        {
            $set["plate"] = (string)$info->plate;
        }
        if(null !== $info->is_advance)
        {
            $set["is_advance"] = (int)$info->is_advance;
        }
        if(null !== $info->is_confirm)
        {
            $set["is_confirm"] = (int)$info->is_confirm;
        }
        if(null !== $info->is_ganged)
        {
            $set["is_ganged"] = (int)$info->is_ganged;
        }
        if(null !== $info->customer_phone)
        {
            $set["customer_phone"] = (string)$info->customer_phone;
        }
        $value = array(
            '$set' => $set
        );

        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true]);
            LogDebug("ret:" . $ret['ok']);
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
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

    public function GetOrderList($filter=null,$sortby=[])
    {
        if(!$filter['shop_id'])
        {
            return [];
        }
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
            $customer_id = $filter['customer_id'];
            if(!empty($customer_id))
            {
                $cond['customer_id'] = (string)$customer_id;
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
            $dine_way = $filter['dine_way'];
            if(!empty($dine_way))
            {
                $cond['dine_way'] = (int)$dine_way;
            }
            $is_invoicing = $filter['is_invoicing'];
            if(!empty($is_invoicing))
            {
                $cond['is_invoicing'] = (int)$is_invoicing;
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

    public function GetOrderAllList($filter=null,$sortby=[],$page_size, $page_no,&$total=null,&$price_list=null)
    {

        if (!$filter['shop_id'])
        {
            return [];
        }

        $db    = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond  = ['delete' => ['$ne' => 1]];
        if (null != $filter)
        {
            $order_id = $filter['order_id'];
            if (null !== $order_id)
            {
                $cond['order_id'] = new \MongoRegex("/$order_id/");
                //$cond['order_id'] = (string)$order_id;
            }
            $shop_id = $filter['shop_id'];
            if (null !== $shop_id)
            {
                $cond['shop_id'] = (string)$shop_id;
            }

            $seat_id_list = $filter['seat_id_list'];
            if (null !== $seat_id_list)
            {
                foreach ($seat_id_list as $i => &$item) {
                    $item = (string)$item;
                }
                $cond = [
                    'seat_id' => ['$in' => $seat_id_list],
                ];
            }
            $dine_way = $filter['dine_way'];
            if (null !== $dine_way)
            {
                $cond['dine_way'] = (int)$dine_way;
            }
            $is_invoicing = $filter['is_invoicing'];
            if (null !== $is_invoicing)
            {
                $cond['is_invoicing'] = (int)$is_invoicing;
            }
            $pay_way = $filter['pay_way'];
            if (null !== $pay_way)
            {
                $cond['pay_way'] = (int)$pay_way;
            }
            $order_begin_time = $filter['order_begin_time'];
            $order_end_time   = $filter['order_end_time'];
            if (!empty($order_begin_time))
            {
                $cond['order_time'] = [
                    '$gte' => (int)$order_begin_time,
                    '$lte' => (int)$order_end_time,
                ];
            }
            // 订单状态(1:未支付,2:已支付,3:已反结,4:退款成功,5:退款失败,6:已关闭,7:挂账)
            $order_status = $filter['order_status'];
            if (!empty($order_status))
            {
                if($order_status == \NewOrderStatus::PAY)
                {
                        $cond['order_status'] = [
                            '$in'=>[2,3,4,5]
                        ];

                }elseif($order_status == \NewOrderStatus::NOPAY)
                {
                    $cond['order_status'] = [
                        '$in'=>[1,7]
                    ];
                }else{
                    $cond['order_status'] = (int)$order_status;
                }
            }
            //LogDebug($cond['order_status']);
            $begin_time = $filter['begin_time'];
            $end_time   = $filter['end_time'];
            if (!empty($begin_time))
            {
                switch ($order_status)
                {
                    case \NewOrderStatus::PAY:
                        $cond['pay_time'] = [
                            '$gte' => (int)$begin_time,
                            '$lte' => (int)$end_time,
                        ];
                        break;
                    case \NewOrderStatus::KNOT:
                        $cond['checkout_time'] = [
                            '$gte' => (int)$begin_time,
                            '$lte' => (int)$end_time,
                        ];
                        break;
                    case \NewOrderStatus::REFUND:
                        $cond['refunds_time'] = [
                            '$gte' => (int)$begin_time,
                            '$lte' => (int)$end_time,
                        ];
                        break;
                    case \NewOrderStatus::REFUNDFAIL:
                        $cond['refunds_fail_time'] = [
                            '$gte' => (int)$begin_time,
                            '$lte' => (int)$end_time,
                        ];
                        break;
                    case \NewOrderStatus::CLOSER:
                        $cond['close_time'] = [
                            '$gte' => (int)$begin_time,
                            '$lte' => (int)$end_time,
                        ];
                        break;
                    default:
                        break;
                }
            }
        }
        if (empty($sortby))
        {
            $sortby['_id'] = -1;
        }
        // LogDebug($cond);
        $field["_id"] = 0;
        $cursor       = $table->find($cond,$field)->sort($sortby)->skip(($page_no - 1) * $page_size)->limit($page_size);
        if (null !== $total)
        {
            $total = $table->count($cond);
        }

        //聚合条件算出:订单总价，订单总人数，订单总减价
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'               => null,
                    'all_customer_num'  => ['$sum' => '$customer_num'],
                    'all_order_fee'     => ['$sum' => '$order_fee'],
                    'all_order_payable' => ['$sum' => '$order_payable'],
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
        return OrderEntry::ToList($cursor);

    }
    // 取客人30天内最近的一条订单
    public function GetLastOrder($customer_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'customer_id' => $customer_id,
            'order_status' => 2,
            'order_time' =>['$gt'=>(time()-30*24*60*60)]
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
    // 删除餐桌查看订单中是否还又该餐桌
    public function GetOrderSeat ($seat_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'order_id' => (string)$seat_id
        );
        $cursor = $table->findOne($cond);
        return new OrderEntry($cursor);
    }
}


?>
