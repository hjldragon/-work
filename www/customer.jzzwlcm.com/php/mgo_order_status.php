<?php
/*
 * [Rocky 2017-05-12 19:39:55]
 * 订单表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("redis_id.php");

class OrderStatusEntry
{
    public $id               = null;     // 主键id
    public $order_status     = null;     // 订单状态(1:未支付,2:已支付,3:已反结,4:退款成功,5:退款失败,6:已关闭,7:挂账,8退款中)
    public $order_id         = null;     // 订单id
    public $customer_id      = null;     // 客户id
    public $employee_id      = null;     // 操作人id
    public $made_time        = null;     // 操作时间
    public $made_reson       = null;     // 状态申请原因
    public $made_cz_reson    = null;     // 状态操作原因
    public $lastmodtime      = null;     // 最后修改时间(时间戳)
    public $delete           = null;     // 0:未删除; 1:已删除
    public $apply_time       = null;     // 申请时间(用于申请退款)


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
        $this->id            = $cursor['id'];
        $this->order_status  = $cursor['order_status'];
        $this->order_id      = $cursor['order_id'];
        $this->customer_id   = $cursor['customer_id'];
        $this->employee_id   = $cursor['employee_id'];
        $this->made_time     = $cursor['made_time'];
        $this->made_reson    = $cursor['made_reson'];
        $this->made_cz_reson = $cursor['made_cz_reson'];
        $this->lastmodtime   = $cursor['lastmodtime'];
        $this->delete        = $cursor['delete'];
        $this->apply_time    = $cursor['apply_time'];
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

class OrderStatus
{
    private function Tablename()
    {
        return 'order_status';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'id' => (string)$info->id
        );

        $set = array(
            "id"    => (string)$info->id,
            "lastmodtime" => (null === $info->lastmodtime) ? time() : (int)$info->lastmodtime,
        );
        if(null !== $info->order_id)
        {
            $set["order_id"] = (string)$info->order_id;
        }
        if(null !== $info->order_status)
        {
            $set["order_status"] = (int)$info->order_status;
        }
        if(null !== $info->customer_id)
        {
            $set["customer_id"] = (string)$info->customer_id;
        }
        if(null !== $info->made_time)
        {
            $set["made_time"] = (int)$info->made_time;
        }
        if(null !== $info->employee_id)
        {
            $set["employee_id"] = (string)$info->employee_id;
        }
        if(null !== $info->made_cz_reson)
        {
            $set["made_cz_reson"] = (string)$info->made_cz_reson;
        }
        if(null !== $info->made_time)
        {
            $set["made_time"] = (int)$info->made_time;
        }
        if(null !== $info->made_reson)
        {
            $set["made_reson"] = (string)$info->made_reson;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->lastmodtime)
        {
            $set["lastmodtime"] = (int)$info->lastmodtime;
        }
        if(null !== $info->apply_time)
        {
            $set["apply_time"] = (int)$info->apply_time;
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
            'order_id' => (string)$order_id,
            'delete'   => ['$ne'=>1]
        );
        $cursor = $table->find($cond);
        return OrderStatusEntry::Tolist($cursor);
    }


}


?>