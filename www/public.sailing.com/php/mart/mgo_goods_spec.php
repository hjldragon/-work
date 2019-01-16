<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 餐桌位置表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class GoodsSpecEntry extends BaseInfo
{
    public $spec_id     = null; // 规格id
    public $spec_name   = null; // 规格名称
    public $package     = null; // 套餐名
    public $goods_id    = null; // 商品id
    public $price       = null; // 价格
    public $stock_num   = null; // 库存数量
    public $sale_price  = null; // 促销价格
    public $sale_time   = null; // 促销时间
    public $time        = null; // 时长
    public $time_unit   = null; // 时长单位(1.日,2.月,3.季,4.年)
    public $terminal    = null; // 授权端（1:智能收银机,2:自助点餐机,4:平板智能点餐机,5:掌柜通）3:PC这里用不上
    public $lastmodtime = null; // 数据最后修改时间
    public $delete      = null; // 0:正常, 1:已删除
    public $ctime       = null; // 创建时间

    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    protected function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->spec_id     = $cursor["spec_id"];
        $this->spec_name   = $cursor["spec_name"];
        $this->package     = $cursor["package"];
        $this->goods_id    = $cursor["goods_id"];
        $this->price       = $cursor["price"];
        $this->stock_num   = $cursor["stock_num"];
        $this->sale_price  = $cursor["sale_price"];
        $this->sale_time   = $cursor["sale_time"];
        $this->time        = $cursor["time"];
        $this->time_unit   = $cursor["time_unit"];
        $this->terminal    = $cursor["terminal"];
        $this->lastmodtime = $cursor["lastmodtime"];
        $this->delete      = $cursor["delete"];
        $this->ctime       = $cursor["ctime"];
    }
}
class GoodsSpec extends MgoBase
{
    protected function Tablename()
    {
        return 'goods_spec';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'spec_id' => (string)$info->spec_id
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->spec_name)
        {
            $set["spec_name"] = (string)$info->spec_name;
        }

        if(null !== $info->goods_id)
        {
            $set["goods_id"] = (string)$info->goods_id;
        }

        if(null !== $info->package)
        {
            $set["package"] = (string)$info->package;
        }

        if(null !== $info->price)
        {
            $set["price"] = (float)$info->price;
        }

        if(null !== $info->stock_num)
        {
            $set["stock_num"] = (int)$info->stock_num;
        }
        if(null !== $info->sale_time)
        {
            $set["sale_time"] = $info->sale_time;
            $set["sale_price"] = (float)$info->sale_price;
            // 无促销价时
            if(0 == $info->sale_time)
            {
                $set["sale_time"]  = null;
                $set["sale_price"] = null;
            }
        }

        if(null !== $info->time)
        {
            $set["time"] = (int)$info->time;
        }
         if(null !== $info->time_unit)
        {
            $set["time_unit"] = (int)$info->time_unit;
        }
         if(null !== $info->terminal)
        {
            $set["terminal"] = (int)$info->terminal;
        }

        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->ctime)
        {
            $set["ctime"] = (int)$info->ctime;
        }
        $value = array(
            '$set' => $set
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
        return 0;
    }
    // 库存变动
    public function StockNumDec($spec_id, $num)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'spec_id' => (string)$spec_id
        );
        $value = array(
            '$set' => array(
                'lastmodtime' => time(),
            ),
            '$inc' => array(
                'stock_num' => (int)$num
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

    public function BatchDelete($id_list)
    {
        return parent::DoBatchDelete($id_list, "id_list");
    }

    public function GetSpecById($spec_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$spec_id, "spec_id");
        return GoodsSpecEntry::ToObj($cursor);
    }

    public function GetSpecByName($goods_id, $spec_name, $package)
    {
        $db =\DbPool::GetMongoDb();
        $table =$db->selectCollection($this->Tablename());
        $cond = array(
            'delete'    => ['$ne'=>1],
            'goods_id'  =>(string)$goods_id,
            'spec_name' =>(string)$spec_name,
            'package'   =>(string)$package
        );
        $cursor = $table->findOne($cond);
        return new  GoodsSpecEntry($cursor);
    }

     public function GetSpecList($filter = null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' => ['$ne' => 1]
        ];
        if (null != $filter) {
            $foods_id = $filter['goods_id'];
            if (!empty($goods_id)) {
                $cond['goods_id'] = (string)$goods_id;
            }

            $spec_id_list = $filter['spec_id_list'];
            if (!empty($spec_id_list)) {
               foreach($spec_id_list as $i => &$item)
                {
                    $item = (string)$item;
                }
                $cond["spec_id"] = ['$in' => $spec_id_list];
            }

        }
        $sortby['_id'] = 1;

        $field["_id"] = 0;

        $cursor = $table->find($cond, $field)->sort($sortby);

        return GoodsSpecEntry::ToList($cursor);
    }

}



?>