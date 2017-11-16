<?php
/*
 * [Rocky 2017-05-12 19:39:55]
 * 订单表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("redis_id.php");

class ReservationEntry
{
    public $reservation_id      = null;    // 预约单号
    public $customer_name       = null;    // 预约顾客名
    public $employee_id         = null;    // 接受员工id
    public $customer_phone      = null;    // 预约人电话
    public $customer_num        = null;    // 预约人数
    public $shop_id             = null;    // 餐馆id
    public $seat_id             = null;    // 餐卓id
    public $reservation_status  = null;    // 预约状态(0:未确定,1:未签到, 2:签到, 3:已取消)
    public $reservation_time    = null;    // 预约时间
    public $sign_time           = null;    // 签到时间
    public $delete              = null;    // 删除（0:未删除,1:删除）
    public $lastmodtime         = null;    // 最后修改时间(时间戳)
    public $note                = null;    // 备注

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
        $this->reservation_id     = $cursor['reservation_id'];
        $this->customer_name      = $cursor['customer_name'];
        $this->employee_id        = $cursor['employee_id'];
        $this->customer_phone     = $cursor['customer_phone'];
        $this->shop_id            = $cursor['shop_id'];
        $this->customer_num       = $cursor['customer_num'];
        $this->seat_id            = $cursor['seat_id'];
        $this->reservation_status = $cursor['reservation_status'];
        $this->reservation_time   = $cursor['reservation_time'];
        $this->sign_time          = $cursor['sign_time'];
        $this->delete             = $cursor['delete'];
        $this->lastmodtime        = $cursor['lastmodtime'];
        $this->note               = $cursor['note'];

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

class Reservation
{
    private function Tablename()
    {
        return 'reservation';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'reservation_id' => (string)$info->reservation_id
        );

        $set = array(
            "reservation_id"    => (string)$info->reservation_id,
            "lastmodtime" => (null === $info->lastmodtime) ? time() : (int)$info->lastmodtime,
        );

        if(null !== $info->customer_name)
        {
            $set["customer_name"] = (string)$info->customer_name;
        }
        if(null !== $info->employee_id)
        {
            $set["employee_id"] = (string)$info->employee_id;
        }
        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->customer_phone)
        {
            $set["customer_phone"] = (string)$info->customer_phone;
        }
        if(null !== $info->customer_num)
        {
            $set["customer_num"] = (int)$info->customer_num;
        }
        if(null !== $info->seat_id)
        {
            $set["seat_id"] = (string)$info->seat_id;
        }
        if(null !== $info->reservation_status)
        {
            $set["reservation_status"] = (int)$info->reservation_status;
        }
        if(null !== $info->reservation_time)
        {
            $set["reservation_time"] = (int)$info->reservation_time;
        }
        if(null !== $info->sign_time)
        {
            $set["sign_time"] = (int)$info->sign_time;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->note)
        {
            $set["note"] = (int)$info->note;
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
    public function GetReservationAllList($filter=null,$sortby=[],$page_size, $page_no,&$total=null)
    {
        if(!$filter['shop_id'])
        {
            return [];
        }
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
       //搜索功能
        $cond = ['delete'  => ['$ne'=>1]];
        if(null != $filter)
        {
            $customer_name = $filter['customer_name'];
            if(!empty($customer_name))
            {
                $cond['customer_name'] = new \MongoRegex("/$customer_name/");
                //$cond['customer_name'] = (string)$customer_name;
            }
            $customer_phone = $filter['customer_phone'];
            if(!empty($customer_phone))
            {
                $cond['customer_phone'] = new \MongoRegex("/$customer_phone/");
                //$cond['customer_phone'] = (string)$customer_phone;
            }
            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $employee_id = $filter['employee_id'];
            if(!empty($employee_id))
            {
                $cond['employee_id'] = (string)$employee_id;
            }
            $reservation_status = $filter['reservation_status'];
            if(null !== $reservation_status)
            {
                $cond['reservation_status'] = (int)$reservation_status;
            }

            $begin_time = $filter['begin_time'];
            $end_time   = $filter['end_time'];
            if(!empty($begin_time))
            {
                $cond['reservation_time'] = [
                    '$gte' => (int)$begin_time,
                    '$lte' => (int)$end_time
                ];
            }
        }
        if(empty($sortby))
        {
            $sortby['_id'] = -1;
        }
         LogDebug($cond);
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort($sortby)->skip(($page_no - 1) * $page_size)->limit($page_size);
        if (null !== $total)
        {
            $total = $table->count($cond);
        }
        return ReservationEntry::ToList($cursor);
    }


}


?>
