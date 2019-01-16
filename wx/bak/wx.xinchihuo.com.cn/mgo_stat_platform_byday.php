<?php
/*
 * [Rocky 2017-06-22 02:36:53]
 * 平台统计操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class StatPlatformEntry
{
    public $day                = null;     // 日期(如: int(20170622) )
    public $platform_id        = null;     // 平台ID
    public $industry_agent_num = null;     // 新增行业代理商数
    public $region_agent_num   = null;     // 新增区域代理商数
    public $consume_amount     = null;     // 消费额
    public $customer_num       = null;     // 消费者数
    public $qr_order_num       = null;     // 扫码点餐次数
    public $new_shop_num       = null;     // 新增店铺数
    public $industry_shop_num  = null;     // 新增签约行业店铺数
    public $region_shop_num    = null;     // 新增签约区域店铺数
    public $new_cus_num        = null;     // 新增签约区域店铺数
    public $lastmodtime        = null;     // 数据最后修改时间
    public $delete             = null;     // 0:正常, 1:已删除

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
        $this->day                = $cursor['day'];
        $this->platform_id        = $cursor['platform_id'];
        $this->industry_agent_num = $cursor['industry_agent_num'];
        $this->region_agent_num   = $cursor['region_agent_num'];
        $this->consume_amount     = $cursor['consume_amount'];
        $this->customer_num       = $cursor['customer_num'];
        $this->qr_order_num       = $cursor['qr_order_num'];
        $this->new_shop_num       = $cursor['new_shop_num'];
        $this->industry_shop_num  = $cursor['industry_shop_num'];
        $this->region_shop_num    = $cursor['region_shop_num'];
        $this->new_cus_num        = $cursor['new_cus_num'];
        $this->lastmodtime        = $cursor['lastmodtime'];
        $this->delete             = $cursor['delete'];
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

class StatPlatform
{
    private function Tablename()
    {
        return 'stat_platform_byday';
    }

    public function SellNumAdd($platform_id, $day, $num=[])
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'platform_id' => (string)$platform_id,
            'day'         => (int)$day,
        );
        $value = array(
            '$set' => array(
                'lastmodtime' => time(),
                'delete'      => 0
            ),
            '$inc' => array(
                'industry_agent_num' => (int)$num['industry_agent_num'],
                'region_agent_num'   => (int)$num['region_agent_num'],
                'consume_amount'     => (float)$num['consume_amount'],
                'customer_num'       => (int)$num['customer_num'],
                'qr_order_num'       => (int)$num['qr_order_num'],
                'new_cus_num'        => (int)$num['new_cus_num'],
                'industry_shop_num'  => (int)$num['industry_shop_num'],
                'region_shop_num'    => (int)$num['region_shop_num']
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
