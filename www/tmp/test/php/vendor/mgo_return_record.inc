<?php
/*
记录
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");



class ReturnRecordEntry extends BaseInfo
{

    public $return_id         = null;     // 记录id
    public $shop_id           = null;     // 店铺ID
    public $vendor_goods_id   = null;     // 商品id
    public $category_id       = null;     // 商品分类id
    public $goods_num         = null;     // 商品数量
    public $return_time       = null;     // 退货时间
    public $return_address    = null;     // 退货地址
    public $return_status     = null;     // 退货状态(1.待退货中,2.退货成功)1.就是放在列表中的数据
    public $use_name          = null;     // 操作人
    public $lastmodtime       = null;     // 最后修改的时间
    public $delete            = null;     // 是否删除(0:未删除; 1:已删除)


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
        $this->return_id        = $cursor['return_id'];
        $this->shop_id          = $cursor['shop_id'];
        $this->vendor_goods_id  = $cursor['vendor_goods_id'];
        $this->category_id      = $cursor['category_id'];
        $this->goods_num        = $cursor['goods_num'];
        $this->return_address   = $cursor['return_address'];
        $this->return_time      = $cursor['return_time'];
        $this->return_status    = $cursor['return_status'];
        $this->use_name         = $cursor['use_name'];
        $this->lastmodtime      = $cursor['lastmodtime'];
        $this->delete           = $cursor['delete'];

    }

}

class ReturnRecord extends MgoBase
{
    private function Tablename()
    {
        return 'return_record';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'return_id'   => (string)$info->return_id,
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );
        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->vendor_goods_id)
        {
            $set["vendor_goods_id"] = (string)$info->vendor_goods_id;
        }
        if(null !== $info->category_id)
        {
            $set["category_id"] = $info->category_id;
        }
        if(null !== $info->goods_num)
        {
            $set["goods_num"] = (int)$info->goods_num;
        }
        if(null !== $info->return_address)
        {
            $set["return_address"] = (string)$info->return_address;
        }
        if(null !== $info->return_time)
        {
            $set["return_time"] = (int)$info->return_time;
        }
        if(null !== $info->return_status)
        {
            $set["return_status"] = (int)$info->return_status;
        }
        if(null !== $info->use_name)
        {
            $set["use_name"] = (string)$info->use_name;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
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

    public function GetListTotal($filter=null, &$total=null)
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
            $return_status = $filter['return_status'];
            if(!empty($return_status))
            {
                $cond['return_status'] = (int)$return_status;
            }
            $category_id = $filter['category_id'];
            if (!empty($category_id)) {
                $cond['category_id'] = (string)$category_id;
            }

        }
        $cursor = $table->find($cond, ["_id"=>0]);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return ReturnRecordEntry::ToList($cursor);
    }


}


?>
