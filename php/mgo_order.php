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
    public $shop_id          = null;    // 餐馆id
    public $dine_way         = null;    // 用餐方式(0:未确定, 1:在店吃, 2:打包, 3:自提, 4:外卖)
    public $pay_way          = null;    // 支付方式(0:未确定,1:现金支付, 2:微信支付, 3:支付宝支付, 4:银行卡支付, 5:挂账)
    public $customer_num     = null;    // 顾客人数
    public $seat_id          = null;    // 餐桌座位
    public $food_list        = null;    // 餐品列表
    public $order_status     = null;    // 订单状态(0:未支付,1:已支付,2:挂账,4:已反结,5:退款成功,6:退款失败,7:已关闭)
    public $order_time       = null;    // 下单时间(时间戳)
    public $lastmodtime      = null;    //
    public $delete           = null;    // 0:未删除; 1:已删除
    public $food_num_all     = null;    // 菜总数
    public $food_price_all   = null;    // 餐品总价
    public $order_waiver_fee = null;    // 减免金额
    public $order_payable    = null;    // 应付金额（客人应该支付的金额：order_fee-order_waiver_fee）
    public $seat_price       = null;    // 餐位费
    public $order_fee        = null;    // 订单金额（按定价算出来的当前消费额）
    public $order_remark     = null;    // 订单备注
    public $is_invoicing     = null;    // 是否开票（1:已开票,2:未开票)

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
        $this->order_time       = $cursor['order_time'];
        $this->lastmodtime      = $cursor['lastmodtime'];
        $this->delete           = $cursor['delete'];
        $this->food_num_all     = $cursor['food_num_all'];
        $this->food_price_all   = $cursor['food_price_all'];
        $this->order_waiver_fee = $cursor['order_waiver_fee'];
        $this->order_payable    = $cursor['order_payable'];
        $this->seat_price       = $cursor['seat_price'];
        $this->order_fee        = $cursor['order_fee'];
        $this->order_remark     = $cursor['order_remark'];
        $this->is_invoicing     = $cursor['is_invoicing'];
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
                    'food_category'    => (string)$item->food_category,
                    'food_attach_list' => (array)$item->food_attach_list,
                    'food_unit'        => (string)$item->food_unit,
                    'unit_num'         => (float)$item->unit_num,
                ]));
            }
            $set["food_list"] = $food_list;
        }
        if(null !== $info->order_status)
        {
            $set["order_status"] = (int)$info->order_status;
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
        if(null !== $info->order_remark)
        {
            $set["order_remark"] = (string)$info->order_remark;
        }
        if(null !== $info->is_invoicing)
        {
            $set["is_invoicing"] = (int)$info->is_invoicing;
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

    public function GetOrderList($filter=null, $field=[], $sortby=[])
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
    public function GetOrderAllList($filter=null,$sortby=[],$page_size, $page_no,&$total=null,&$price_list=null)
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
            $pay_way = $filter['pay_way'];
            if(!empty($pay_way))
            {
                $cond['pay_way'] = (int)$pay_way;
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
        if(empty($sortby))
        {
            $sortby['_id'] = -1;
        }
         LogDebug($cond);
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }

        //聚合条件算出:订单总价，订单总人数，订单总减价
        $pipe = [
            array('$match'=>$cond),
            array(
                '$group' => array(
                    '_id' => null,
                    'all_customer_num' => array('$sum' => '$customer_num'),
                    'all_order_fee' => array('$sum' => '$order_fee'),
                    'all_order_payable' => array('$sum' => '$order_payable'),
                ),
            ),
        ];
        LogDebug($pipe);
        $all_list = $table->aggregate($pipe);
        if($all_list['ok'] == 1)
        {
            $price_list = $all_list['result'][0];
        }else
        {
            $price_list = null;
        }
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
