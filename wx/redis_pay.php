<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 支付表操作类
 */
declare(encoding='UTF-8');
namespace DaoRedis;
require_once("db_pool.php");
require_once("redis_public.php");


# t_login
class PayEntry
{
    public $order_id       = null;      // 订单id
    public $transaction_id = null;      // 对应微信订单id
    public $out_trade_no   = null;      // 商户系统内部订单号
    public $is_pay         = null;      // 是否已支付(0:否,1:已支付)
    public $pay_price      = null;      // 支付金额
    public $lastmodtime    = null;      // 最后修改时间


    function __construct($cursor=null)
    {
        $this->FromRedis($cursor);
    }

    // mongodb查询结果转为结构体
    public function FromRedis($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->order_id       = $cursor['order_id'];
        $this->transaction_id = $cursor['transaction_id'];
        $this->out_trade_no   = $cursor['out_trade_no'];
        $this->is_pay         = $cursor['is_pay'];
        $this->pay_price      = $cursor['pay_price'];
        $this->lastmodtime    = $cursor['lastmodtime'];
    }
};

class Pay
{
    private function Tablename()
    {
        return DB_PAY; // 注意各个表使用序号
    }

    public function Save($entry)
    {
        if(!$entry || null === $entry->order_id)
        {
            LogDebug($entry);
            LogErr("param err");
            return -1;
        }
        $db = \DbPool::GetRedis($this->Tablename());
        $data = [
            'order_id' => $entry->order_id,
            'lastmodtime' => time(),
        ];
        if(null !== $entry->transaction_id)
        {
            $data["transaction_id"] = (string)$entry->transaction_id;
        }
        if(null !== $entry->out_trade_no)
        {
            $data["out_trade_no"] = (string)$entry->out_trade_no;
        }
        if(null !== $entry->is_pay)
        {
            $data["is_pay"] = (int)$entry->is_pay;
        }
        if(null !== $entry->pay_price)
        {
            $data["pay_price"] = (float)$entry->pay_price;
        }

        $ret = $db->hmset($entry->order_id, $data);
        if($ret < 0)
        {
            LogErr("hmset err, ret:[$ret]");
            return $ret;
        }
        LogDebug("ret:$ret");
        return 0;
    }

    public function Get($order_id)
    {
        try
        {
            $db = \DbPool::GetRedis($this->Tablename());
            $ret = $db->hgetall($order_id);
            return new PayEntry($ret);
        }
        catch(RedisException $e)
        {
            LogErr($e->getMessage());
        }
        catch(Exception $e)
        {
            LogErr($e->getMessage());
        }
    }
}


?>
