<?php
/*
订单统计
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class StatVendorGoodsEntry extends BaseInfo
{

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
        $this->vendor_goods_id = $cursor['vendor_goods_id'];
        $this->shop_id         = $cursor['shop_id'];
        $this->all_money       = $cursor['all_money'];
        $this->all_num         = $cursor['all_num'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];

    }

}

class StatVendorGoods extends MgoBase
{
    private function Tablename()
    {
        return 'stat_vendor_goods_byday';
    }

    public function SellNumAdd($shop_id, $vendor_goods_id, $num=[])
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
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

        }
        $cursor = $table->find($cond, ["_id"=>0]);

        return StatVendorGoodsEntry::ToList($cursor);
    }


}


?>
