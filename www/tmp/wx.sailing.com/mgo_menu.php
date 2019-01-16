<?php
/*
 * [Rocky 2017-04-25 19:29:49]
 * 菜单信息表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("const.php");


class FoodSaleTime
{
    public $start_time = null;   //开始时间
    public $end_time   = null;   //结束时间

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
        $this->start_time = $cursor['start_time'];
        $this->end_time   = $cursor['end_time'];

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

class Attach
{
    public $title       = null;   //口味名称
    public $spc_value   = null;   //口味属性

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
        $this->title     = $cursor['title'];
        $this->spc_value = $cursor['spc_value'];
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

class Price
{
    public $spec_type      = null;      // 份量规格(0:无份量,1:大份,2:中份,3:小份)
    public $original_price = null;      // 原价
    public $discount_price = null;      // 折扣价
    public $vip_price      = null;      // 会员价
    public $festival_price = null;      // 节日价
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
        $this->spec_type        = $cursor['spec_type'];
        $this->original_price   = $cursor['original_price'];
        $this->discount_price   = $cursor['discount_price'];
        $this->vip_price        = $cursor['vip_price'];
        $this->festival_price   = $cursor['festival_price'];
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

class FoodPrice
{
    public $type  = null;    //1:无份量规格,2:有份量规格
    public $using = null;    //使用哪个价格的位标志(1:原价,2:折扣价,4:会员价,8:节日价)
    public $price = null;    //价格数组

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
        $this->type  = $cursor['type'];
        $this->using = $cursor['using'];
        $this->price = Price::ToList($cursor['price']);
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


class MenuInfoEntry
{
    public $food_id             = null;             // 餐品id
    public $shop_id             = null;             // 餐馆店铺id
    public $food_name           = null;             // 餐品名
    public $category_id         = null;             // 餐品分类id
    public $stock_num_day       = null;             // 每天预备份数
    public $food_price          = null;             // 餐品单价(分为单位)
    public $food_intro          = null;             // 餐品介绍
    //public $food_num_mon        = null;           // 当月销售量
    public $praise_num          = null;             // 点赞数
    public $food_img_list       = null;             // 餐品照片列表
    public $entry_time          = null;             // 录入时间
    public $lastmodtime         = null;             // 数据最后修改时间
    public $delete              = null;             // 是否删除(0:未删除; 1:已删除)
    public $food_attach_list    = null;             // 口味附加属性list
    public $food_unit           = null;             // 店铺餐品单位（份、碗、斤等）
    public $need_waiter_confirm = null;             // 本餐品是否需要服务员确认（如重量、价格不定的餐品：虾等）（0:不需要，1:需要）
    public $sale_off            = null;             // 是否下架（1:下架, 0:正常）
    public $accessory           = null;             // 菜品所用配件food_id
    public $accessory_num       = null;             // 单个菜品所用配件数
    public $pack_remark         = null;             // 打包备注
    public $composition         = null;             // 食材
    public $feature             = null;             // 特色
    public $sale_way            = null;             // 1.在店吃 2.自提 3.打包 4.外卖 
    public $sale_num            = null;             // 起售数量
    public $sale_off_way        = null;             // 0 不设置 1自定义 2按周期
    public $food_sale_time      = null;             // food出售时间段
    public $food_sale_week      = null;             // food出售时间周
    public $type                = null;             // 餐品类别的类型(1:一般类品分类，2:配件,3:酒水)
    public $is_draft            = null;             // 是否是草稿(0:不是,1:是草稿)

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
        $this->food_id             = $cursor['food_id'];
        $this->shop_id             = $cursor['shop_id'];
        $this->food_name           = $cursor['food_name'];
        $this->category_id         = $cursor['category_id'];
        $this->stock_num_day       = $cursor['stock_num_day'];
        //$this->food_num_mon        = $cursor['food_num_mon'];
        $this->praise_num          = $cursor['praise_num'];
        $this->food_intro          = $cursor['food_intro'];
        $this->food_img_list       = $cursor['food_img_list'];
        $this->entry_time          = $cursor['entry_time'];
        $this->lastmodtime         = $cursor['lastmodtime'];
        $this->food_attach_list    = Attach::ToList($cursor['food_attach_list']);
        $this->food_unit           = $cursor['food_unit'];
        $this->need_waiter_confirm = $cursor['need_waiter_confirm'];
        $this->sale_off            = $cursor['sale_off'];
        $this->accessory           = $cursor['accessory'];
        $this->accessory_num       = $cursor['accessory_num'];
        $this->pack_remark         = $cursor['pack_remark'];
        $this->composition         = $cursor['composition'];
        $this->feature             = $cursor['feature'];
        $this->is_draft            = $cursor['is_draft'];
        $this->type                = $cursor['type'];
        $this->food_sale_time      = FoodSaleTime::ToList($cursor['food_sale_time']);
        $this->food_sale_week      = $cursor['food_sale_week'];
        $this->sale_way            = $cursor['sale_way'];
        $this->sale_off_way        = $cursor['sale_off_way'];
        $this->sale_num            = $cursor['sale_num'];
        if(2 == $cursor['type']){
            $this->food_price      = $cursor['food_price'];
        }else{
            $this->food_price      = new FoodPrice($cursor['food_price']);
        }
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

class MenuInfo
{

    private function Tablename()
    {
        return 'foodinfo';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'food_id' => (string)$info->food_id
        );

        $set = array(
            "food_id" => (string)$info->food_id,
            "lastmodtime" => time()
        );

        if (null !== $info->shop_id) {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if (null !== $info->food_name) {
            $set["food_name"] = (string)$info->food_name;
        }
        if (null !== $info->category_id) {
            $set["category_id"] = (string)$info->category_id;
        }
        if (null !== $info->stock_num_day) {
            $set["stock_num_day"] = (int)$info->stock_num_day;
        }
        if (null !== $info->type) {
            $set["type"] = (int)$info->type;
        }
        if (null !== $info->food_price) {
            if(2 == $info->type){
                $set["food_price"] = (float)$info->food_price;
            }else{
                $price_list = [];
                foreach ($info->food_price->price as $val) {
                    $p = new Price();
                    $p->spec_type      = (int)$val->spec_type;
                    $p->original_price = (float)$val->original_price;
                    $p->discount_price = (float)$val->discount_price;
                    $p->vip_price      = (float)$val->vip_price;
                    $p->festival_price = (float)$val->festival_price;
                    $p->is_use         = (int)$val->is_use;
                    $price_list[] = $p;
                }
                $p = new FoodPrice();
                $p->type  = (int)$info->food_price->type;
                $p->price = $price_list;
                $p->using = (int)$info->food_price->using;
                $set["food_price"] = $p;
            }
        }

        // if (null !== $info->food_num_mon) {
        //     $set["food_num_mon"] = (int)$info->food_num_mon;
        // }
        if (null !== $info->praise_num) {
            $set["praise_num"] = (int)$info->praise_num;
        }
        if (null !== $info->food_intro) {
            $set["food_intro"] = (string)$info->food_intro;
        }
        if (null !== $info->food_img_list) {
            $set["food_img_list"] = (array)$info->food_img_list;
        }
        if (null !== $info->entry_time) {
            $set["entry_time"] = (int)$info->entry_time;
        }
        if (null !== $info->food_attach_list) {
            $attach = [];
            foreach ($info->food_attach_list as $v) {
                array_push($attach, new Attach([
                    "title"       => (string)$v->title,
                    "spc_value"   => (array)$v->spc_value
                ]));
            }
            $set["food_attach_list"] = $attach;
        }
        if (null !== $info->food_unit) {
            $set["food_unit"] = $info->food_unit;
        }
        if (null !== $info->need_waiter_confirm) {
            $set["need_waiter_confirm"] = (int)$info->need_waiter_confirm;
        }
        if (null !== $info->sale_off) {
            $set["sale_off"] = (int)$info->sale_off;
        }
        if (null !== $info->accessory) {
            $set["accessory"] = (string)$info->accessory;
        }
        if (null !== $info->accessory_num) {
            $set["accessory_num"] = (int)$info->accessory_num;
        }
        if (null !== $info->pack_remark) {
            $set["pack_remark"] = (string)$info->pack_remark;
        }
        if (null !== $info->composition) {
            $set["composition"] = (array)$info->composition;
        }
        if (null !== $info->feature) {
            $set["feature"] = (array)$info->feature;
        }
        if (null !== $info->food_sale_time) {
            $time = [];
            foreach ($info->food_sale_time as $item) {
                array_push($time, new FoodSaleTime([
                    "start_time" => (int)$item->start_time,
                    "end_time"   => (int)$item->end_time
                ]));
            }
            $set["food_sale_time"] = $time;
        }
        if (null !== $info->food_sale_week) {
            $set["food_sale_week"] = $info->food_sale_week;
        }
        if (null !== $info->is_draft) {
            $set["is_draft"] = (int)$info->is_draft;
        }
        if (null !== $info->sale_way) {
            $set["sale_way"] = (array)$info->sale_way;
        }
        if (null !== $info->sale_num) {
            $set["sale_num"] = (int)$info->sale_num;
        }
        if (null !== $info->sale_off_way) {
            $set["sale_off_way"] = (int)$info->sale_off_way;
        }
        $value = array(
            '$set' => $set
        );
       
        LogDebug($value);
        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true]);
            LogDebug("ret:" . json_encode($ret));
        } catch (MongoCursorException $e) {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function Delete($food_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        for ($i = 0; $i < count($food_id_list); $i++) {
            $food_id_list[$i] = (string)$food_id_list[$i];
        }

        $cond = array(
            'food_id' => ['$in' => $food_id_list]
        );

        $value = array(
            '$set' => array(
                'delete' => 1,
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


    public function SetSale($food_id_list,$sale_off)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        for ($i = 0; $i < count($food_id_list); $i++) {
            $food_id_list[$i] = (string)$food_id_list[$i];
        }

        $cond = array(
            'food_id' => ['$in' => $food_id_list]
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

    //批量改变餐品的设置方式
    public function SetSaleWay($food_id_list,$sale_off_way)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        for ($i = 0; $i < count($food_id_list); $i++) {
            $food_id_list[$i] = (string)$food_id_list[$i];
        }

        $cond = array(
            'food_id' => ['$in' => $food_id_list]
        );

        $value = array(
            '$set' => array(
                'sale_off_way' => (int)$sale_off_way,
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

    public function GetFoodList($shop_id, $filter = null, $sortby = [], &$total=null)
    {   
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'   => ['$ne' => 1],
            'is_draft' => ['$ne' => 1],
            'shop_id'  => (string)$shop_id
        ];
        if (null != $filter) {
            $food_id = $filter['food_id'];
            if (!empty($food_id)) {
                $cond['food_id'] = (string)$food_id;
            } else {
                $cate_id_list = $filter['cate_id_list'];
                if (!empty($cate_id_list)) {
                    for ($i = 0; $i < count($cate_id_list); $i++) {
                        $cate_id_list[$i] = (string)$cate_id_list[$i];
                    }
                    $cond['category_id'] = [
                        '$in' => $cate_id_list
                    ];
                }

                $sale_off = $filter['sale_off'];
                if (null !== $sale_off) {
                    $sale_off = (int)$sale_off;
                    if (0 == $sale_off) {
                        $cond['sale_off'] = ['$ne' => 1]; // ==0 或 ==null
                    } else {
                        $cond['sale_off'] = 1;
                    }
                }

                $food_name = $filter['food_name'];
                if (!empty($food_name)) {
                    $cond['$or'] = [
                        ["food_name" => new \MongoRegex("/$food_name/i")],
                        ["food_id" => new \MongoRegex("/$food_name/i")]
                    ];
                }
                // 草稿
                $is_draft = $filter['is_draft'];
                if (!empty($is_draft)) {
                    $cond['is_draft'] = (int)$is_draft;
                }
                // 除配件以外的餐品
                $type = $filter['type'];
                if (!empty($type)) {
                    $cond['type'] = ['$ne' => 2];
                }
            }
        }

        if(empty($sortby))
        {
            $sortby['_id'] = 1;
        }
        $field["_id"] = 0;
        //LogDebug($cond);
        $cursor = $table->find($cond, $field)->sort($sortby);
        if(null !== $total){
            $total = $table->count($cond);
        }
        
        //LogDebug(iterator_to_array($cursor));
        return MenuInfoEntry::ToList($cursor);
    }

    public function GetFoodinfoById($food_id)
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());;
        $cond = array(
            'food_id' => (string)$food_id
        );
        $cursor = $table->findOne($cond);
        return new MenuInfoEntry($cursor);
    }

    public function GetFoodinfoByName($food_name)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'food_name' => $food_name,
            'delete'    =>['$ne'=>1],
        );

        $cursor = $table->findOne($cond);

        return new MenuInfoEntry($cursor);
    }

    public function GetFoodinfoByCate($food_category)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'food_category' => $food_category
        );

        $cursor = $table->findOne($cond);

        return new MenuInfoEntry($cursor);
    }

    public function GetOrderFoodList($shop_id, $filter = null, $field = [])
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne' => 1],
            'shop_id' => (string)$shop_id
        ];
        if (null != $filter) {
            $food_id = $filter['food_id'];
            if (!empty($food_id)) {
                $cond['food_id'] = (string)$food_id;
            } else {
                $food_id_list = $filter['food_id_list'];
                if (!empty($food_id_list)) {
                    for ($i = 0; $i < count($food_id_list); $i++) {
                        $food_id_list[$i] = (string)$food_id_list[$i];
                    }
                    $cond['food_id'] = [
                        '$in' => $food_id_list
                    ];
                }

                $sale_off = $filter['sale_off'];
                if (null !== $sale_off) {
                    $sale_off = (int)$sale_off;
                    if (0 == $sale_off) {
                        $cond['sale_off'] = ['$ne' => 1]; // ==0 或 ==null
                    } else {
                        $cond['sale_off'] = 1;
                    }
                }

                $food_name = $filter['food_name'];
                if (!empty($food_name)) {
                    $cond['food_name'] = new \MongoRegex("/$food_name/i");
                }
            }
        }
        $field["_id"] = 0;
        LogDebug($cond);
        $cursor = $table->find($cond, $field)->sort(["food_category" => 1, "_id" => 1]);
        //LogDebug(iterator_to_array($cursor));
        return MenuInfoEntry::ToList($cursor);
    }
    //获取pad端菜品列表
    public function GetPadFoodList($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'   => ['$ne' => 1],
            'is_draft' => ['$ne' => 1],
            'sale_off' => ['$ne' => 1],
            'shop_id'  => (string)$shop_id
        ];

        $sortby['entry_time'] = -1;
        $field["_id"] = 0;
        //LogDebug($cond);
        $cursor = $table->find($cond)->sort($sortby);
        return MenuInfoEntry::ToList($cursor);
    }

    public function SetFoodSaleOn($food_id, $sale_off)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'food_id' => $food_id
        );

        $value = array(
            '$set' => array(
                'sale_off'    => (int)$sale_off,
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

    //获取该店铺下面所有餐品列表(用在推送)
    public function GetFoodAllList($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'   => ['$ne' => 1],
            'is_draft' => ['$ne' => 1],
            'shop_id'  => (string)$shop_id
        ];

        $sortby['entry_time'] = -1;
        $field["_id"] = 0;
        $cursor = $table->find($cond)->sort($sortby);
        return MenuInfoEntry::ToList($cursor);
    }
}


?>