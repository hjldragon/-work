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

    public $type = null;              // 1:按时间段展示（即从A时间戳到B时间戳），使用time_range_stamp中的数据
                                      // 2:按周展示，使用time_range_week中的数据（注：0～6对应周一到周日）
                                      // 3:包含时间段也包含时间周
    public $time_range_stamp = null;  //时间段
    public $time_range_week = null;   //时间周

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
        $this->type = $cursor['type'];
        $this->time_range_stamp = $cursor['time_range_stamp'];
        $this->time_range_week = $cursor['time_range_week'];

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

class FoodPrice
{
    public $type = null;                // 餐品类型(1:使用这里设置的价格,2:使用规格的价格)
    public $original_price = null;      // 原价
    public $discount_price = null;      // 折扣价
    public $vip_price = null;           // 会员价
    public $festival_price = null;      // 节日价
    public $using = null;               // 使用哪个价格的位标志(1:原价,2:折扣价,4:会员价,8:节日价)

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
        $this->type = $cursor['type'];
        $this->original_price = $cursor['original_price'];
        $this->discount_price = $cursor['discount_price'];
        $this->vip_price = $cursor['vip_price'];
        $this->festival_price = $cursor['festival_price'];
        $this->using = $cursor['using'];
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
    public $food_id = null;             // 餐品id
    public $shop_id = null;             // 餐馆店铺id
    public $food_name = null;           // 餐品名
    public $food_category = null;       // 餐品分类
    public $food_num_day = null;        // 每天预备份数
    public $food_price = null;          // 餐品单价(分为单位)
    public $food_intro = null;          // 餐品介绍
    public $food_num_mon = null;        // 当月销售量
    public $praise_num = null;          // 点赞数
    public $food_img_list = null;       // 餐品照片列表
    public $entry_time = null;          // 录入时间
    public $lastmodtime = null;         // 数据最后修改时间
    public $delete = null;              //是否删除(0:未删除; 1:已删除)
    public $food_attach_list = null;    // 口味附加属性list（加辣等）//这个字段在shop表中
    public $food_unit = null;           // 店铺餐品单位（份、碗、斤等）
    public $need_waiter_confirm = null; // 本餐品是否需要服务员确认（如重量、价格不定的餐品：虾等）（0:不需要，1:需要）
    public $sale_off = null;            // 是否下架（1:下架, 0:正常）
    public $accessory = null;           // 菜品所用配件food_id
    public $composition = null;         // 食材
    public $feature = null;             // 特色
    //public $is_spec       = null;     // 是否有不同规格(0:无不同规格,1:有不同规格)
    public $food_sale_time = null;      //food出售时间

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
        $this->food_id = $cursor['food_id'];
        $this->shop_id = $cursor['shop_id'];
        $this->food_name = $cursor['food_name'];
        $this->food_category = $cursor['food_category'];
        $this->food_num_day = $cursor['food_num_day'];
        $this->food_price = new FoodPrice($cursor['food_price']);
        $this->food_vip_price = $cursor['food_vip_price'];
        $this->food_num_mon = $cursor['food_num_mon'];
        $this->praise_num = $cursor['praise_num'];
        $this->food_intro = $cursor['food_intro'];
        $this->food_img_list = $cursor['food_img_list'];
        $this->entry_time = $cursor['entry_time'];
        $this->lastmodtime = $cursor['lastmodtime'];
        $this->food_attach_list = $cursor['food_attach_list'];
        $this->food_unit = $cursor['food_unit'];
        $this->need_waiter_confirm = $cursor['need_waiter_confirm'];
        $this->sale_off = $cursor['sale_off'];
        $this->accessory = $cursor['accessory'];
        $this->composition = $cursor['composition'];
        $this->feature = $cursor['feature'];
        $this->food_sale_time = FoodSaleTime::ToList($cursor['food_sale_time']);
        //$this->is_spec             = $cursor['is_spec'];
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
        if (null !== $info->food_category) {
            $set["food_category"] = (string)$info->food_category;
        }
        if (null !== $info->food_num_day) {
            $set["food_num_day"] = (int)$info->food_num_day;
        }

        if (null !== $info->food_price) {
            //$using = 0;
            // foreach ($info->food_price->using as $value) {
            //     $using = $using | (int)$value;
            // }

            foreach ($info->food_price as $item) {
                $set["food_price"] = new FoodPrice([
                    'type' => (int)$item->type,
                    'original_price' => (float)$item->original_price,
                    'discount_price' => (float)$item->discount_price,
                    'vip_price' => (float)$item->vip_price,
                    'festival_price' => (float)$item->festival_price,
                    'using' => (int)$item->using,
                ]);
            }

        }
        if (null !== $info->food_num_mon) {
            $set["food_num_mon"] = (int)$info->food_num_mon;
        }
        if (null !== $info->praise_num) {
            $set["praise_num"] = (int)$info->praise_num;
        }
        if (null !== $info->food_intro) {
            $set["food_intro"] = (string)$info->food_intro;
        }
        if (null !== $info->food_img_list) {
            $set["food_img_list"] = $info->food_img_list;
        }
        if (null !== $info->entry_time) {
            $set["entry_time"] = (int)$info->entry_time;
        }
        if (null !== $info->food_attach_list) {
            $set["food_attach_list"] = (string)$info->food_attach_list;
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

        if (null !== $info->composition) {
            $set["composition"] = (array)$info->composition;
        }

        if (null !== $info->feature) {
            $set["feature"] = (array)$info->feature;
        }

        if (null !== $info->food_sale_time) {
            $set["food_sale_time"] = $info->food_sale_time;
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

    public function GetFoodList($shop_id, $filter = null, $field = [])
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' => ['$ne' => 1],
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
        LogDebug(iterator_to_array($cursor));
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
            'food_name' => $food_name
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
}


?>
