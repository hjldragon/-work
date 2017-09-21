<?php
/*
 * [Rocky 2017-05-04 23:58:34]
 * 餐馆店铺信息表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class WeixinInfo
{
    public $sub_mch_id = null;     // 微信支付分配的子商户号

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
        $this->sub_mch_id = $cursor['sub_mch_id'];
    }
}

class BroadcastEntry
{
    public $id = null;            //轮播消息id
    public $shop_id = null;       //轮播消息的店铺id
    public $type = null;          // 1:按时间段展示（即从A时间戳到B时间戳），使用time_range_1中的数据
    // 2:按周展示，使用time_range_2中的数据（注：0～6对应周日到周六）
    // 3：既含有时间段也含有时间戳
    public $time_range_1 = null;  //时间段
    public $time_range_2 = null;  //时间周
    public $content = null;       //喇叭内容

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
        $this->id = $cursor['id'];
        $this->shop_id = $cursor['shop_id'];
        $this->type = $cursor['type'];
        $this->time_range_1 = $cursor['time_range_1'];
        $this->time_range_2 = $cursor['time_range_2'];
        $this->content = $cursor['content'];
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

class Broadcast
{
    private function Tablename()
    {
        return 'broadcast';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'id' => (string)$info->id
        );
        $set = array(
            'id' => (string)$info->id
        );
        //var_dump($info);die;
        if (null !== $info->id) {
            $set["id"] = (string)$info->id;
        }
        if (null !== $info->shop_id) {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if (null !== $info->type) {
            $set["type"] = (string)$info->type;
        }

        if (null !== $info->time_range_1) {
            $set["time_range_1"] = $info->time_range_1;
        }
        //die;
        if (null !== $info->time_range_2) {
            $set["time_range_2"] = $info->time_range_2;
        }
        if (null !== $info->content) {
            $set["content"] = (string)$info->content;
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

    //通过shop_id来找到该店铺的轮播信息
    public function GetBroadcastByShopId($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id' => (string)$shop_id,

        ];

        $field["_id"] = 0;
        $cursor = $table->find($cond, $field);

        return BroadcastEntry::ToList($cursor);
    }

    //通过TYPE和店铺id来获取，该店铺的所有消息
    public function GetBroadcastAll($shop_id, $type)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id' => (string)$shop_id,
            'type' => (string)$type,
        ];

        $field["_id"] = 0;
        $cursor = $table->find($cond, $field);

        return BroadcastEntry::ToList($cursor);
    }


}

class OpenTime
{
    public $type = null;    //1:全天,2:早市,3:午市,4:晚市,5:夜宵
    public $from = null;    //时间开始（大于等于）
    public $to = null;      //时间终止（小于等于）（可能是第二天）

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
        $this->from = new Time($cursor['from']);
        $this->to = new Time($cursor['to']);
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

class Time
{
    public $hh = null;    //1:全天,2:早市,3:午市,4:晚市,5:夜宵
    public $mm = null;    //时间开始（大于等于）
    public $ss = null;    //时间终止（小于等于）（可能是第二天）

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
        $this->hh = $cursor['hh'];
        $this->mm = $cursor['mm'];
        $this->ss = $cursor['ss'];
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

class ShopEntry
{

    public $shop_id = null;               // 餐馆店铺id
    public $shop_name = null;             // 餐馆名
    public $classify_name = null;         // 属性名
    public $contact = null;               // 联系人
    public $telephone = null;             // 联系电话
    public $email = null;                 // 电子邮件
    public $address = null;               // 联系地址
    public $lastmodtime = null;           //最后修改的时间
    public $praise_num = null;            // 点赞数
    public $good_rate = null;             // 好评率（0.0~1.0，按需要转为82.1%）
    public $open_time = null;             // 每天的营业时间
    public $img_list = null;              //首页轮播图
    public $broadcast_content = null;     // 小喇叭消息（轮播）
    public $is_support_vat = null;        // 是否支付增值税发票（1:支持, 0:未知）
    public $delete = null;                // 0:未删除; 1:已删除
    public $weixin = null;                // 信息相关配置
    public $food_attach_list = null;      // 店铺口味可选附加属性配置list（加辣等）
    public $food_unit_list = null;        // 店铺餐品可选单位list（份、碗、斤等）
    public $suspend = null;               // 店铺是否暂停（0:正常使用, 1:被系统管理员暂停, 2:被店铺管理员暂停，参见const.php::ShopSuspend)
    public $is_seat_enable = null;        // 店铺餐位费是否启用(0:不启用,1启用)
    public $opening_time = null;          //营业时间

    function __construct($cursor = null)
    {
        //$this->img_list = new ImgList();
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    public function FromMgo($cursor)
    {
        if (!$cursor) {
            return;
        }
        $this->shop_id = $cursor['shop_id'];
        $this->shop_name = $cursor['shop_name'];
        $this->classify_name = $cursor['classify_name'];
        $this->telephone = $cursor['telephone'];
        $this->email = $cursor['email'];
        $this->address = $cursor['address'];
        $this->praise_num = $cursor['praise_num'];
        $this->good_rate = $cursor['good_rate'];
        $this->open_time = $cursor['open_time'];
        $this->img_list = $cursor['img_list'];
        //$this->broadcast_content = Broadcast::ToList($cursor['broadcast_content']);
        $this->broadcast_content = $cursor['broadcast_content'];
        $this->is_support_vat = $cursor['is_support_vat'];
        $this->lastmodtime = $cursor['lastmodtime'];
        $this->delete = $cursor['delete'];
        $this->weixin = new WeixinInfo($cursor['weixin']);
        $this->food_attach_list = $cursor['food_attach_list'];
        $this->food_unit_list = $cursor['food_unit_list'];
        $this->suspend = $cursor['suspend'];
        $this->is_seat_enable = $cursor['is_seat_enable'];
        $this->opening_time = OpenTime::ToList($cursor['opening_time']);
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

class Shop
{
    private function Tablename()
    {
        return 'shop';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'shop_id' => (string)$info->shop_id
        );
        $set = array(
            "shop_id" => (string)$info->shop_id,
            'lastmodtime' => time(),
        );

        if (null !== $info->shop_id) {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if (null !== $info->shop_name) {
            $set["shop_name"] = (string)$info->shop_name;
        }
        if (null !== $info->classify_name) {
            $set["classify_name"] = (string)$info->classify_name;
        }
        if (null !== $info->telephone) {
            $set["telephone"] = (string)$info->telephone;
        }
        if (null !== $info->paise_num) {
            $set["praise_num"] = (int)$info->paise_num;
        }
        if (null !== $info->address) {
            $set["address"] = (string)$info->address;
        }
        if (null !== $info->good_rate) {
            $set["good_rate"] = (float)$info->good_rate;
        }
        if (null !== $info->weixin) {
            if (null !== $info->weixin->sub_mch_id) {
                $set["weixin.sub_mch_id"] = (string)$info->weixin->sub_mch_id;
            }
        }
        if (null !== $info->opening_time) {
            $set["opening_time"] = (string)$info->opening_time;
        }
        if (null !== $info->img_list) {
            $set["img_list"] = (string)$info->img_list;
        }
        if (null !== $info->broadcast_content) {
            $set["broadcast_content"] = (string)$info->broadcast_content;
        }
        if (null !== $info->is_support_vat) {
            $set["is_support_vat"] = (int)$info->is_support_vat;
        }
        if (null !== $info->is_seat_enable) {
            $set["is_seat_enable"] = (int)$info->is_seat_enable;
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

    public function Delete($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'shop_id' => (string)$shop_id
        );

        $value = array(
            '$set' => array(
                'delete' => 1,
                'lastmodtime' => time()
            )
        );

        $table->update($cond, $value, array('upsert' => true));
        return 0;
    }

    public function GetShopById($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'shop_id' => (string)$shop_id
        );


        $cursor = $table->findOne($cond);

        return new ShopEntry($cursor);
    }

    public function GetShopList($filter = null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = ['delete' => ['$ne' => 1]];
        if (null != $filter) {
            $shop_id = $filter['shop_id'];
            if (!empty($shop_id)) {
                $cond['shop_id'] = (string)$shop_id;
            } else {
                $shop_name = $filter['shop_name'];
                if (!empty($shop_name)) {
                    $cond['shop_name'] = new \MongoRegex("/$shop_name/");
                }
            }
        }
        $cursor = $table->find($cond, ["_id" => 0])->sort(["shop_name" => 1]);
        return ShopEntry::ToList($cursor);
    }
}


?>
