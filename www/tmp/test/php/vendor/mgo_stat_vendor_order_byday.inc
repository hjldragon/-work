<?php
/*
订单统计
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class StatVendorOrderEntry extends BaseInfo
{
    public $day                     = null;  // 日期(如: int(20170622))
    public $shop_id                 = null;  // 店铺Id
    public $order_num               = null;  // 订单量(交易数量)
    public $all_money               = null;  // 交易金额
    public $goods_num               = null;  // 商品数量
    public $lastmodtime             = null;  // 数据最后修改时间
    public $delete                  = null;  // 0:正常, 1:已删除


    function __construct($cursor = null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    public function FromMgo($cursor)
    {
        if (!$cursor) {
            return;
        }
        $this->day             = $cursor['day'];
        $this->shop_id         = $cursor['shop_id'];
        $this->order_num       = $cursor['order_num'];
        $this->all_money       = $cursor['all_money'];
        $this->goods_num       = $cursor['goods_num'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];

    }

}

class StatVendorOrder extends MgoBase
{
    private function Tablename()
    {
        return 'stat_vendor_order_byday';
    }

    public function SellNumAdd($shop_id, $day, $num=[])
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'shop_id'  => (string)$shop_id,
            'day'      => (int)$day,
        );
        $value = array(
            '$set' => array(
                'lastmodtime' => time(),
                'delete'      => 0
            ),
            '$inc' => array(
                'all_money' => (float)$num['all_money'],
                'order_num' => (int)$num['order_num'],
                'goods_num' => (int)$num['goods_num']
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

    public function QueryByDayAll($filter=null ,&$num_all=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'delete'  => array('$ne'=>1)
        );

        if (null != $filter) {

            $shop_id = $filter['shop_id'];
            if (null !== $shop_id) {
                $cond['shop_id'] = (string)$shop_id;
            }

            $day = $filter['day'];
            if (null !== $day) {
                $cond['day'] = (int)$day;
            }

            $begin_day = $filter['begin_day'];
            $end_day   = $filter['end_day'];
            if (!empty($begin_day)) {
                $cond['day'] = [
                    '$gte' => (int)$begin_day,
                    '$lte' => (int)$end_day,
                ];
            }
        }
        //聚合条件算出
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'    => null,
                    'all_money'  => ['$sum' => '$all_money'],
                    'order_num'  => ['$sum' => '$order_num'],
                    'goods_num'  => ['$sum' => '$goods_num'],
                ],
            ],
        ];
        $all_list = $table->aggregate($pipe);
        if ($all_list['ok'] == 1) {
            $num_all = $all_list['result'][0];
        } else {
            $num_all = null;
        }
        $ret = $table->find($cond);
        return StatVendorOrderEntry::ToList($ret);
    }

}


?>
