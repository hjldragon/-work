<?php
/*
 * 点赞收藏表
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class GoodsPraiseEntry extends BaseInfo
{
    public $type      = null;          //1:点赞，2:收藏
    public $agent_id  = null;          //代理商对应id
    public $shop_id   = null;          //店铺对应id
    public $goods_id  = null;          //点赞商品id
    public $is_praise = null;          //是否点赞(1点赞)
    public $ctime     = null;          //点赞的时间（时间戳，秒数，按需要转为可读形式，如2017-09-04 19:03）
    public $delete    = null;          //0:正常, 1:已删除

    function __construct($cursor = null)
    {

        $this->FromMgo($cursor);
    }

    public function FromMgo($cursor)
    {
        if (!$cursor) {
            return;
        }
        $this->type      = $cursor['type'];
        $this->agent_id  = $cursor['agent_id'];
        $this->shop_id   = $cursor['shop_id'];
        $this->goods_id  = $cursor['goods_id'];
        $this->is_praise = $cursor['is_praise'];
        $this->ctime     = $cursor['ctime'];
        $this->delete    = $cursor['delete'];
    }
}

class GoodsPraise extends MgoBase
{
    private function Tablename()
    {
        return 'goods_praise';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            "type"     => (int)$info->type,
            "agent_id" => (string)$info->agent_id,
            "goods_id" => (string)$info->goods_id,
            "shop_id"  => (string)$info->shop_id
        );
        $set = array(
            "type"     => (int)$info->type,
            "agent_id" => (string)$info->agent_id,
            "goods_id" => (string)$info->goods_id,
            "shop_id"  => (string)$info->shop_id,
            'ctime'    => time()
        );

        if (null !== $info->is_praise) {
            $set["is_praise"] = (int)$info->is_praise;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
        }
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

    public function GetPraiseById($id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'id' => (string)$id,
            'delete' =>['$ne'=>1]
        );
        $cursor = $table->findOne($cond);
        return new GoodsPraiseEntry($cursor);
    }


    public function GetPraiseByCustomer($agent_id, $shop_id, $goods_id, $type)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            "agent_id" => (string)$agent_id,
            "goods_id" => (string)$goods_id,
            "shop_id"  => (string)$shop_id,
            "type"     => (int)$type,
            'is_praise' => 1
        ];
        $field["_id"] = 0;
        $cursor = $table->findOne($cond, $field);
        return new GoodsPraiseEntry($cursor);
    }

    public function GetPraiseList($filter=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'delete'  => ['$ne'=>1],
            'is_praise' => 1
        ];
        if(null != $filter)
        {
            $agent_id = $filter['agent_id'];
            if(!empty($agent_id))
            {
                $cond['agent_id'] = (string)$agent_id;
            }
            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $type = $filter['type'];
            if(!empty($type))
            {
                $cond['type'] = (int)$type;
            }
        }
        if(empty($sortby))
        {
            $sortby['_id'] = -1;
        }
        //LogDebug($cond);
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort($sortby);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return GoodsPraiseEntry::ToList($cursor);
    }

    //通过商品id找出所有点赞条数或收藏数
    public function GetGoodsAllCount($goods_id, $type=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'goods_id'  => (string)$goods_id,
            'type'      => 1,
            'is_praise' => 1,
            'delete'    =>['$ne'=>1]
        ];
        if($type)
        {
           $cond['type'] = $type;
        }
        $cursor = $table->count($cond);
        return $cursor;
    }
}

?>