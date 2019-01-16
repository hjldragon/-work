<?php
/*
 * [Rocky 2017-04-25 19:29:49]
 * 菜单信息表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("const.php");



class GoodsEntry
{
    public $goods_id            = null;             // 商品id
    public $goods_name          = null;             // 商品名
    public $goods_price         = null;             // 商品价格
    public $goods_num           = null;             // 商品数量
    public $goods_img           = null;             // 商品照片
    public $entry_time          = null;             // 录入时间
    public $lastmodtime         = null;             // 数据最后修改时间
    public $delete              = null;             // 是否删除(0:未删除; 1:已删除)
    public $sale_off            = null;             // 是否下架（1:下架, 0:正常）
    public $describe            = null;             // 商品描述
    public $category_id         = null;             // 分类id
    public $goods_sort          = null;             // 商品排序（手动输入)


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
        $this->goods_id          = $cursor['goods_id'];
        $this->goods_name        = $cursor['goods_name'];
        $this->goods_price       = $cursor['goods_price'];
        $this->goods_num         = $cursor['goods_num'];
        $this->goods_img         = $cursor['goods_img'];
        $this->entry_time        = $cursor['entry_time'];
        $this->lastmodtime       = $cursor['lastmodtime'];
        $this->delete            = $cursor['delete'];
        $this->sale_off          = $cursor['sale_off'];
        $this->describe          = $cursor['describe'];
        $this->category_id       = $cursor['category_id'];
        $this->goods_sort        = $cursor['goods_sort'];

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

class Goods
{

    private function Tablename()
    {
        return 'goods';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'goods_id' => (string)$info->goods_id
        );

        $set = array(
            "goods_id" => (string)$info->goods_id,
            "lastmodtime" => time()
        );

        if (null !== $info->goods_name) {
            $set["goods_name"] = (string)$info->goods_name;
        }
        if (null !== $info->goods_price) {
            $set["goods_price"] = (float)$info->goods_price;
        }
        if (null !== $info->goods_num) {
            $set["goods_num"] = (int)$info->goods_num;
        }
        if (null !== $info->goods_img) {
            $set["goods_img"] = (string)$info->goods_img;
        }
        if (null !== $info->entry_time) {
            $set["entry_time"] = (int)$info->entry_time;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
        }
        if (null !== $info->sale_off) {
            $set["sale_off"] = (int)$info->sale_off;
        }
        if (null !== $info->describe) {
            $set["describe"] = (string)$info->describe;
        }
        if (null !== $info->category_id) {
            $set["category_id"] = (string)$info->category_id;
        }
        if (null !== $info->goods_sort) {
            $set["goods_sort"] = (int)$info->goods_sort;
        }
        $value = array(
            '$set' => $set
        );
        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true]);
            LogDebug("ret:" . json_encode($ret));
        } catch (MongoCursorException $e) {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function Delete($goods_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        for ($i = 0; $i < count($goods_id_list); $i++) {
            $goods_id_list[$i] = (string)$goods_id_list[$i];
        }

        $cond = array(
            'goods_id' => ['$in' => $goods_id_list]
        );

        $value = array(
            '$set' => array(
                'delete'      => 1,
                'lastmodtime' => time()
            )
        );

        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true, 'multiple' => true]);
            LogDebug("ret:" . json_encode($ret["ok"]));
        } catch (MongoCursorException $e) {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function SetSale($goods_id_list, $sale_off)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        for ($i = 0; $i < count($goods_id_list); $i++) {
            $goods_id_list[$i] = (string)$goods_id_list[$i];
        }

        $cond = array(
            'goods_id' => ['$in' => $goods_id_list]
        );

        $value = array(
            '$set' => array(
                'sale_off' => (int)$sale_off,
                'lastmodtime' => time()
            )
        );

        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true, 'multiple' => true]);
            LogDebug("ret:" . json_encode($ret["ok"]));
            
        } catch (MongoCursorException $e) {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function GetGoodsList($filter=null, $page_size, $page_no, $sortby = [], &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1]
        ];
        //搜索功能
        if(null != $filter)
        {
            $category_id = $filter['category_id'];
            if( null != $category_id)
            {
                $cond['category_id'] = (string)$category_id;
            }
            $goods_name = $filter['goods_name'];
            if( null != $goods_name)
            {
                $cond['goods_name'] = new \MongoRegex("/$goods_name/");
            }
            $sale_off = $filter['sale_off'];
            if( null != $sale_off)
            {
                $cond['sale_off'] = (int)$sale_off;
            }

            $begin_time = $filter['begin_time'];
            $end_time   = $filter['end_time'];
            if(!empty($begin_time))
            {
                $cond['apply_time'] = [
                    '$gte' => (int)$begin_time,
                    '$lte' => (int)$end_time
                ];
            }
        }
        if(empty($sortby)){
            $sortby['ctime'] = -1;
        }
        LogDebug($cond);
        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return GoodsEntry::ToList($cursor);
    }

    public function GetGoodsinfoById($goods_id)
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());;
        $cond = array(
            'goods_id' => (string)$goods_id
        );
        $cursor = $table->findOne($cond);
        return new GoodsEntry($cursor);
    }

    public function GetByCateForGoodsList($filter = null, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'   => ['$ne' => 1],
        ];

        if (null != $filter) {
            $cate_id_list = $filter['cate_id_list'];
                if (!empty($cate_id_list)) {
                    for ($i = 0; $i < count($cate_id_list); $i++) {
                        $cate_id_list[$i] = (string)$cate_id_list[$i];
                    }
                    $cond['category_id'] = [
                        '$in' => $cate_id_list
                    ];
                }
        }
        if(empty($sortby))
        {
            $sortby['_id'] = 1;
        }
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort($sortby);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return GoodsEntry::ToList($cursor);
    }

    public function GetByParentList($parent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'parent_id' => (string)$parent_id,
        ];
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(["lastmodtime"=>-1]);
        return GoodsCategoryEntry::ToList($cursor);
    }

    public function GetGoodsMaxSort($category_id, &$max=null)
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());;
        $cond = array(
            'category_id' => (string)$category_id
        );
        //$cursor = $table->find($cond);
        //聚合条件算出:订单总价，订单总人数，订单总减价
        $pipe = [
            ['$match' => $cond],
            [
                '$group' => [
                    '_id'             => null,
                    'max_goods_sort'  => ['$max' => '$goods_sort'],
                ],
            ],
        ];
        $all_list = $table->aggregate($pipe);
        if ($all_list['ok'] == 1) {
            $max = $all_list['result'][0];
        } else {
            $max = null;
        }
        //return new GoodsEntry($cursor);
    }

    public function GoodsSortChange($category_id, $min_sort, $max_sort, $value)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'category_id' => (string)$category_id,
            'goods_sort'  => [
                '$gte'  => (int)$min_sort,
                '$lte'  => (int)$max_sort
            ]
        );

        $value = array(
            '$inc' => array(
                'goods_sort'  => $value,
                'lastmodtime' => time()
            )
        );

        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true, 'multiple' => true]);
            LogDebug("ret:" . json_encode($ret["ok"]));
        } catch (MongoCursorException $e) {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }
}


?>

