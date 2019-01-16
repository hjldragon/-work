<?php
/*
订单统计
 */
namespace Pub\Vendor\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class StatVendorGoodsEntry extends \Pub\Mongodb\BaseInfo
{

    public $day                     = null;  // 日期(如: int(20170622))
    public $shop_id                 = null;  // 店铺Id
    public $vendor_goods_id         = null;  // 商品id
    public $all_money               = null;  // 交易金额
    public $all_num                 = null;  // 商品数量
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
        $this->vendor_goods_id = $cursor['vendor_goods_id'];
        $this->shop_id         = $cursor['shop_id'];
        $this->all_money       = $cursor['all_money'];
        $this->all_num         = $cursor['all_num'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];

    }

}

class StatVendorGoods extends \Pub\Mongodb\MgoBase
{
    private function Tablename()
    {
        return 'stat_vendor_goods_byday';
    }

    public function SellNumAdd($day, $shop_id, $vendor_goods_id, $num=[])
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'day'             => (int)$day,
            'shop_id'         => (string)$shop_id,
            'vendor_goods_id' => (string)$vendor_goods_id,
        );
        $value = array(
            '$set' => array(
                'lastmodtime' => time(),
                'delete'      => 0
            ),
            '$inc' => array(
                'all_money' => (float)$num['all_money'],
                'all_num'   => (int)$num['all_num'],

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

    public function GetList($filter=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
        ];
        if(null != $filter)
        {
            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $begin_day = $filter['begin_day'];
            $end_day   = $filter['end_day'];
            if (!empty($begin_day)) {
                $cond['day'] = [
                    '$gte' => (int)$begin_day,
                    '$lte' => (int)$end_day,
                ];
            }
            $day = $filter['day'];
            if (null !== $day) {
                $cond['day'] = (int)$day;
            }

        }
        $cursor = $table->find($cond, ["_id"=>0]);

        return StatVendorGoodsEntry::ToList($cursor);
    }


}


?>
