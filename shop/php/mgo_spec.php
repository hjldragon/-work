<?php
/*
 * [Rocky 2017-05-12 19:39:55]
 * 订单表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("/www/shop.sailing.com/php/db_pool.php");
require_once("redis_id.php");




class SpecListItem
{
    public $id             = null;      // 规格id
    public $title          = null;      // 规格名称
    public $original_price = null;      // 原价
    public $discount_price = null;      // 折扣价
    public $vip_price      = null;      // 会员价
    public $festival_price = null;      // 节日价
    //public $default        = null;    // 前端是否默认使用当前价格（1:使用, 0:不使用）
    public $is_use         = null;      // 是否选中使用此规格(0:不使用,1:使用)

    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    public function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->id               = $cursor['id'];
        $this->title            = $cursor['title'];
        $this->original_price   = $cursor['original_price'];
        $this->discount_price   = $cursor['discount_price'];
        $this->vip_price        = $cursor['vip_price'];
        $this->festival_price   = $cursor['festival_price'];
        //$this->default          = $cursor['default'];
        $this->is_use           = $cursor['is_use'];
    }

    public static function ToList($cursor)
    {
        $list = array();
        foreach($cursor as $item)
        {
            $entry = new self($item);
            array_push($list, $entry);
        }
        return $list;
    }
}

class SpecEntry
{
    public $spec_id     = null;    // 规格id
    public $food_id     = null;    // 菜品id
    public $title       = null;    // 规格名称
    public $list        = null;    // 规格值
    public $type        = null;    // 规格类型（1:对价格无影响的规格, 2:对价格有影响的规格）
    public $delete      = null;    // 0:未删除; 1:已删除
    public $lastmodtime = null;    // 最后修改时间
    public $is_use      = null;    // 是否选中使用此规格(0:不使用,1:使用)

    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    public function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->spec_id          = $cursor['spec_id'];
        $this->food_id          = $cursor['food_id'];
        $this->title            = $cursor['title'];
        $this->list             = SpecListItem::ToList($cursor['list']);
        $this->type             = $cursor['type'];
        $this->spec_sort        = $cursor['spec_sort'];
        $this->is_use           = $cursor['is_use'];
    }

    public static function ToList($cursor)
    {
        $lists = array();
        foreach($cursor as $item)
        {
            $entry = new self($item);
            array_push($lists, $entry);
        }
        return $lists;
    }
}

class Spec
{
    private function Tablename()
    {
        return 'spec';
    }

    public function Save(&$info)
    {   
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'spec_id' => (string)$info->spec_id
        );

        $set = array(
            "spec_id"   => (string)$info->spec_id,
        );

        if(null !== $info->food_id)
        {
            $set["food_id"] = (string)$info->food_id;
        }
        if(null !== $info->title)
        {
            $set["title"] = (string)$info->title;
        }

        if(null !== $info->type)
        {
            $set["type"] = (int)$info->type;
        }
        
        if(null !== $info->list)
        {
            $set["list"] = $info->list;
        }
        if(null !== $info->is_use)
        {
            $set["is_use"] = (int)$info->is_use;
        }

        $set['lastmodtime'] = time();
        
        $value = array(
            '$set' => $set
        );


        try
        {
            $ret = $table->update($cond, $value, ['upsert'=>true]);
            LogDebug("ret:" . json_encode($ret));
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }

        return 0;
    }

    //批量删除
    public function BatchDeleteById($spec_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($spec_id_list as $i => &$id)
        {
            $id = (int)$id;
        }

        $cond = array(
            'spec_id' => array('$in' => $spec_id_list)
        );

        $value = array(
            '$set'=>array(
                'delete'      => 1,
                'lastmodtime' => time(),
            )
        );


        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple' => true]);
            LogDebug("ret:" . json_encode($ret));
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
       
        return 0;
    }

    //查找单条规格
    public function GetSpecBySpecId($spec_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'spec_id' => (string)$spec_id,
            'delete'  => ['$ne'=>1],
        );
        $field["_id"] = 0;
        $cursor = $table->findOne($cond, $field);
        return new SpecEntry($cursor);
    }

    //根据id查找菜品的价格
    public function GetSpecPriceById($id)
    {  
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'list.id' => (string)$id
        );
        $field["_id"]    = 0;
        $field['list.$'] = 1;
        $cursor = $table->findOne($cond, $field);
        if(isset($cursor['list'])){
            return new SpecListItem($cursor['list'][0]);
        }
        return  null;
        
    }

    //根据菜品查找规格
    public function GetSpecList($food_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'food_id' => (string)$food_id,
            'delete'  => ['$ne'=>1],
        ];
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field);
        return SpecEntry::ToList($cursor);
    }


}


?>
