<?php
/*
 * [Rocky 2017-06-22 02:36:53]
 * 店铺统计操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class StatCustomerEntry
{
    public $day             = null;  // 日期(如: int(20170622) )活跃时间
    public $shop_id         = null;  // 店铺ID
    public $customer_id     = null;  // 顾客idID
    public $lastmodtime     = null;  // 最后登录时间
    public $delete          = null;  // 0:正常, 1:已删除

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
        $this->day            = $cursor['day'];
        $this->shop_id        = $cursor['shop_id'];
        $this->customer_id    = $cursor['customer_id'];
        $this->lastmodtime    = $cursor['lastmodtime'];
        $this->delete         = $cursor['delete'];
    }

    public static function ToList($cursor)
    {
        $list = [];
        foreach($cursor as $item)
        {
            $entry = new self($item);
            array_push($list, $entry);
        }
        return $list;
    }
}

class StatCustomer
{
    private function Tablename()
    {
        return 'stat_customer_byday';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'customer_id' => (string)$info->customer_id,
            "day"         => (int)$info->day,
        );
        $set = array(
            "shop_id"     => (string)$info->shop_id,
            'lastmodtime' => time(),
            'customer_id' => (string)$info->customer_id,
            "day"         => (int)$info->day,
        );

        $value = array(
            '$set' => $set
        );
        try {
            $ret = $table->update($cond, $value, ['upsert' => true]);
            LogDebug("ret:" . json_encode($ret));
        } catch (\MongoCursorException $e) {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function SellShopNumAdd($shop_id, $day, $num=[], $filter=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'shop_id'     => (string)$shop_id,
            'day'         => (int)$day,
            //'platform_id' => 1,
        );
        if(null != $filter){
            $agent_id = $filter['agent_id'];
            if(!empty($agent_id)){
                $cond['agent_id'] = (string)$agent_id;
            }
        }
        $value = array(
            '$set' => array(
                'lastmodtime' => time(),
                'delete'      => 0
            ),
            '$inc' => array(
                'qr_order_num'    => (int)$num['qr_order_num'],
                'new_cus_num'     => (int)$num['new_cus_num'],
                'customer_num'    => (int)$num['customer_num'],
                'consume_amount'  => (float)$num['consume_amount'],
            ),
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
        return $ret;
    }

    public function GetByDayShop($shop_id, $day, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id' => (string)$shop_id,
            'day'     => (int)$day,
        ];
        $cursor = $table->find($cond);
        if (null !== $total)
        {
            $total = $table->count($cond);
        }
        return StatCustomerEntry::ToList($cursor);
    }
    //根据当天找出所有活跃人数
    public function GetByDay($day, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'day'     => (int)$day,
        ];
        $cursor = $table->find($cond);
        if (null !== $total)
        {
            $total = $table->count($cond);
        }
        return StatCustomerEntry::ToList($cursor);
    }

    public function GetNumByShop($shop_id, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id' => (string)$shop_id,
        ];
        $cursor = $table->find($cond);
        if (null !== $total)
        {
            $total = $table->count($cond);
        }
        return StatCustomerEntry::ToList($cursor);
    }

    public function GetStatList($filter)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1]
        ];
        //
        $shop_id = $filter['shop_id'];
        if($shop_id )
        {
            $cond['shop_id'] = (string)$shop_id;
        }
        //
        $shop_id = $filter['shop_id'];
        if($shop_id)
        {
            $cond['shop_id'] = (string)$shop_id;
        }
        //
        $shop_id_list = $filter['shop_id_list'];
        if($shop_id_list)
        {
            foreach($shop_id_list as $i => &$item)
            {
                $item = (string)$item;
            }
            $cond['shop_id'] = ['$in' => $shop_id_list];
        }
        //
        $begin_day = $filter['begin_day'];
        $end_day = $filter['end_day'];
        if($begin_day && $end_day)
        {
            $cond['day'] = [
                '$gte' => (int)$begin_day,
                '$lte' => (int)$end_day
            ];
        }

        $cursor = $table->find($cond, ["_id"=>0]);
        return StatShopEntry::ToList($cursor);
    }
}


?>
