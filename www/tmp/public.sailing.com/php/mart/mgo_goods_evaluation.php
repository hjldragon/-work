<?php
/*
 * 评论表
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class GoodsEvaluationEntry extends BaseInfo
{
    public $id             = null;          //评论id
    public $shop_id        = null;          //店铺购买时对应id
    public $agent_id       = null;          //代理商购买时对应id
    public $goods_id       = null;          //评价商品id
    public $goods_order_id = null;          //订单id
    public $content        = null;          //评论内容
    //public $lable          = null;          //评价标签
    public $ctime          = null;          //评价的时间（时间戳，秒数，按需要转为可读形式，如2017-09-04 19:03）
    public $star_num       = null;          //评价星数
    public $delete         = null;          //0:正常, 1:已删除
    public $to_id          = null;          //回复评论id

    function __construct($cursor = null)
    {

        $this->FromMgo($cursor);
    }

    public function FromMgo($cursor)
    {
        if (!$cursor) {
            return;
        }
        $this->id             = $cursor['id'];
        $this->agent_id       = $cursor['agent_id'];
        $this->shop_id        = $cursor['shop_id'];
        $this->goods_id       = $cursor['goods_id'];
        $this->goods_order_id = $cursor['goods_order_id'];
        $this->content        = $cursor['content'];
        //$this->lable          = $cursor['lable'];
        $this->ctime          = $cursor['ctime'];
        $this->star_num       = $cursor['star_num'];
        $this->delete         = $cursor['delete'];
        $this->to_id          = $cursor['to_id'];
    }
}

class GoodsEvaluation extends MgoBase
{
    private function Tablename()
    {
        return 'goods_evaluation';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'id' => (string)$info->id
        );
        $set = array(
            'id' => (string)$info->id,
            'ctime' => time()
        );

        if (null !== $info->id) {
            $set["id"] = (string)$info->id;
        }
        if (null !== $info->agent_id) {
            $set["agent_id"] = (string)$info->agent_id;
        }
        if (null !== $info->shop_id) {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if (null !== $info->goods_id) {
            $set["goods_id"] = (string)$info->goods_id;
        }
        if (null !== $info->goods_order_id) {
            $set["goods_order_id"] = (string)$info->goods_order_id;
        }
        // if (null !== $info->lable) {
        //     $set["lable"] = (array)$info->lable;
        // }
        if (null !== $info->content) {
            $set["content"] = (string)$info->content;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
        }
        if (null !== $info->star_num) {
            $set["star_num"] = (int)$info->star_num;
        }
        if (null !== $info->to_id) {
            $set["to_id"] = (string)$info->to_id;
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

    public function GetGoodsEvaluationById($id)
    {
        $cursor = parent::DoGetInfoByKey((string)$id, "id");
        return GoodsEvaluationEntry::ToObj($cursor);
    }

    public function GetGoodsEvaluationByToId($id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'to_id' => (string)$id,
            'delete' =>['$ne'=>1]
        );
        $cursor = $table->findOne($cond);
        return new GoodsEvaluationEntry($cursor);
    }

    public function GetEvaByCustomerList($agent_id)
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'agent_id' => (string)$agent_id,
            'delete' => ['$ne'=>1],
            'to_id'  => null
        ];
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(['ctime'=>-1]);
        return GoodsEvaluationEntry::ToList($cursor);
    }

    public function GetEvaByOrderList($goods_order_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'goods_order_id' => (string)$goods_order_id,
            'delete' => ['$ne'=>1],
            'to_id'  => null
        ];
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(['ctime'=>-1]);
        return GoodsEvaluationEntry::ToList($cursor);
    }

    //根据商品id来获取所有数据$page_size,$page_no是根据页数和条数来查询
    public function GetGoodsEvaList($goods_id, $page_size=null, $page_no=null, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'goods_id' => (string)$goods_id,
            'delete'  => ['$ne'=>1],
            'to_id'   => null
        ];
        if(null == $page_size)
        {
            $page_size = 5;//如果没有传默认5条
        }
        if(null == $page_no)
        {
            $page_no = 1; //第一页开始
        }

        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(['ctime'=>-1])->skip(($page_no-1)*$page_size)->limit($page_size);
        $total  = $table->count($cond);
        return GoodsEvaluationEntry::ToList($cursor);
    }

    //通过餐品id找出所有的评论条数
    public function GetGoodsAllCount($goods_id, $is_good=null, &$all=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'goods_id' => (string)$goods_id,
            'delete' => ['$ne'=>1],
            'to_id'  => null
        ];
        if($is_good){
            $cond['star_num'] = ['$gt'=>3];
        }
        $cursor = $table->count($cond);
        //聚合条件算出:评价总星数
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'              => null,
                    'all_star_num'     => ['$sum' => '$star_num']
                ],
            ],
        ];
        //LogDebug($pipe);
        $all_list = $table->aggregate($pipe);
        if ($all_list['ok'] == 1) {
            $all = $all_list['result'][0];
        } else {
            $all = null;
        }
        return $cursor;
    }

    public function BatchDelete($id_list)
    {
        return parent::DoBatchDelete($id_list, "id");
    }

    public function Delete($id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'id' => (string)$id
        );
        $value = array(
            '$set' => array(
                'delete'  => 1
            )
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
}

?>