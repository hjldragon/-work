<?php
/*
 * 评论表
 */
declare(encoding='UTF-8');

namespace DaoMongodb;
require_once("/www/shop.sailing.com/php/db_pool.php");


class EvaluationEntry
{
    public $id          = null;          //评论id
    public $customer_id = null;          //客户id
    public $food_id     = null;          //评价餐品id
    public $shop_id     = null;          //评价店铺id
    public $order_id    = null;          //订单id
    public $content     = null;          //评论内容
    public $lable       = null;          //评价标签
    public $ctime       = null;          //评价的时间（时间戳，秒数，按需要转为可读形式，如2017-09-04 19:03）
    public $star_num    = null;          //评价星数
    public $delete      = null;          //0:正常, 1:已删除
    public $to_id       = null;          //追加评论id

    function __construct($cursor = null)
    {

        $this->FromMgo($cursor);
    }

    public function FromMgo($cursor)
    {
        if (!$cursor) {
            return;
        }
        $this->id          = $cursor['id'];
        $this->customer_id = $cursor['customer_id'];
        $this->food_id     = $cursor['food_id'];
        $this->shop_id     = $cursor['shop_id'];
        $this->order_id    = $cursor['order_id'];
        $this->content     = $cursor['content'];
        $this->lable       = $cursor['lable'];
        $this->ctime       = $cursor['ctime'];
        $this->star_num    = $cursor['star_num'];
        $this->delete      = $cursor['delete'];
        $this->to_id       = $cursor['to_id'];
    }

    public static function ToList($cursor)
    {
        $list = array();
        foreach ($cursor as $item) {
            $entry = new self($item);
            array_push($list, $entry);
        }
        return $list;
    }
}

class Evaluation
{
    private function Tablename()
    {
        return 'evaluation';
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
        if (null !== $info->customer_id) {
            $set["customer_id"] = (string)$info->customer_id;
        }
        if (null !== $info->food_id) {
            $set["food_id"] = (string)$info->food_id;
        }
        if (null !== $info->shop_id) {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if (null !== $info->order_id) {
            $set["order_id"] = (string)$info->order_id;
        }
        if (null !== $info->lable) {
            $set["lable"] = (array)$info->lable;
        }
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

    public function GetEvaluationById($id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'id' => (string)$id,
            'delete' =>['$ne'=>1]
        );
        $cursor = $table->findOne($cond);
        return new EvaluationEntry($cursor);
    }

    public function GetEvaluationByToId($id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'to_id' => (string)$id,
            'delete' =>['$ne'=>1]
        );
        $cursor = $table->findOne($cond);
        return new EvaluationEntry($cursor);
    }

    public function GetEvaByCustomerList($customer_id)
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'customer_id' => (string)$customer_id,
            'delete' => ['$ne'=>1],
            'to_id'  => null
        ];
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(['ctime'=>-1]);
        return EvaluationEntry::ToList($cursor);
    }

    public function GetEvaByShopList($shop_id, $page_size=null, $page_no=null, &$total, $is_good=null)
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'shop_id' => (string)$shop_id,
            'delete' => ['$ne'=>1],
            'to_id'  => null
        ];
        if($is_good){
            $cond['star_num'] = ['$gt'=>3];
        }
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
        $total = $table->count($cond);
        return EvaluationEntry::ToList($cursor);
    }

    //根据餐品id来获取所有数据$page_size,$page_no是根据页数和条数来查询
    public function GetFoodIdByList($food_id, $page_size=null, $page_no=null, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'food_id' => (string)$food_id,
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
        // LogDebug($cond);
        // LogDebug($page_size);
        // LogDebug($page_no);
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(['ctime'=>-1])->skip(($page_no-1)*$page_size)->limit($page_size);
        $total  = $table->count($cond);
        return EvaluationEntry::ToList($cursor);
    }

    //通过餐品id找出所有的评论条数
    public function GetFoodAllCount($food_id, $is_good=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'food_id' => (string)$food_id,
            'delete' => ['$ne'=>1],
            'to_id'  => null
        ];
        if($is_good){
            $cond['star_num'] = ['$gt'=>3];
        }
        $cursor = $table->count($cond);
        return $cursor;
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