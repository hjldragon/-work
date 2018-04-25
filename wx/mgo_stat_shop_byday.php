<?php
/*
 * [Rocky 2017-06-22 02:36:53]
 * 店铺统计操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class StatShopEntry
{
    public $day             = null;  // 日期(如: int(20170622) )
    public $shop_id         = null;  // 店铺ID
    public $agent_id        = null;  // 代理商ID
    public $qr_order_num    = null;     // 扫码点餐次数
    public $consume_amount  = null;     // 消费总额
    //public $guest_price     = 0;     // 客单价
    public $customer_num    = null;     // 消费者数
    public $platform_id     = null;  // 平台ID
    public $new_cus_num     = null;     // 新增客户数
    public $active_cus_num  = null;     // 活跃者数
    public $lastmodtime     = null;  // 数据最后修改时间
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
        $this->agent_id       = $cursor['agent_id'];
        $this->platform_id    = $cursor['platform_id'];
        $this->new_cus_num    = $cursor['new_cus_num'];
        $this->active_cus_num = $cursor['active_cus_num'];
        $this->consume_amount = $cursor['consume_amount'];
        $this->customer_num   = $cursor['customer_num'];
        $this->qr_order_num   = $cursor['qr_order_num'];
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

class StatShop
{
    private function Tablename()
    {
        return 'stat_shop_byday';
    }

    public function SellShopNumAdd($shop_id, $day, $num=[], $filter = null, $agent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'shop_id'     => (string)$shop_id,
            'day'         => (int)$day,
            'platform_id' => 1,
            'agent_id'    => $agent_id
        );

        $value = array(
            '$set' => array(
                'lastmodtime' => time(),
                'delete'      => 0
            ),
            '$inc' => array(
                'qr_order_num'    => (int)$num['qr_order_num'],
                'active_cus_num'  => (int)$num['active_cus_num'],
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

}


?>
