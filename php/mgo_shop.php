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
    public $id           = null;            //轮播消息id
    public $shop_id      = null;            //轮播消息的店铺id
    public $type         = null;            // 1:按时间段展示（即从A时间戳到B时间戳），使用time_range_1中的数据
    // 2:按周展示，使用time_range_2中的数据（注：0～6对应周日到周六）
    // 3：既含有时间段也含有时间戳
    public $time_range_1 = null;            //时间段
    public $time_range_2 = null;            //时间周
    public $content      = null;            //喇叭内容

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
        $this->id           = $cursor['id'];
        $this->shop_id      = $cursor['shop_id'];
        $this->type         = $cursor['type'];
        $this->time_range_1 = $cursor['time_range_1'];
        $this->time_range_2 = $cursor['time_range_2'];
        $this->content      = $cursor['content'];
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
            'type'    => (string)$type,
        ];

        $field["_id"] = 0;
        $cursor = $table->find($cond, $field);

        return BroadcastEntry::ToList($cursor);
    }


}

class ShopBusiness
{
    public $company_name            = null;    //企业名称
    public $legal_person            = null;    //法人代表
    public $legal_phone             = null;    //法人电话
    public $legal_card              = null;    //法人身份证
    public $legal_card_photo        = null;    //法人身份照片
    public $business_num            = null;    //营业执照注册号
    public $business_date           = null;    //营业期限
    public $business_photo          = null;    //营业执照照片
    public $repast_permit_identity  = null;    //餐饮许省
    public $repast_permit_year      = null;    //餐饮许可年
    public $repast_permit_num       = null;    //餐饮许可号
    public $repast_permit_photo     = null;    //餐饮许可证件
    public $confirmation            = null;    //确认书
    public $business_scope          = null;    //经营范围

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
        $this->company_name           = $cursor['company_name'];
        $this->legal_person           = $cursor['legal_person'];
        $this->legal_phone            = $cursor['legal_phone'];
        $this->legal_card             = $cursor['legal_card'];
        $this->legal_card_photo       = $cursor['legal_card_photo'];
        $this->business_num           = $cursor['business_num'];
        $this->business_date          = $cursor['business_date'];
        $this->business_photo         = $cursor['business_photo'];
        $this->repast_permit_identity = $cursor['repast_permit_identity'];
        $this->repast_permit_year     = $cursor['repast_permit_year'];
        $this->repast_permit_num      = $cursor['repast_permit_num'];
        $this->repast_permit_photo    = $cursor['repast_permit_photo'];
        $this->confirmation           = $cursor['confirmation'];
        $this->business_scope         = $cursor['business_scope'];


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

class ShopInvoiceInfo
{
    public $is_invoice           = null;   // 是否提供发票(1:提供,0:不提供)
    public $invoice_type         = null;   // 发票抬头类型(1:增值普通发票,2:增值普通发票,3:电子发票)

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
        $this->is_invoice            = $cursor['is_invoice'];
        $this->invoice_type          = $cursor['invoice_type'];

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
        $this->to   = new Time($cursor['to']);
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

class MailVail
{
    public $mail                   = null;    //绑定邮箱
    public $passwd                 = null;    //邮箱验证密码


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
        $this->mail           = $cursor['mail'];
        $this->passwd         = $cursor['passwd'];

    }

}

class ShopEntry
{

    public $shop_id           = null;               // 餐馆店铺id
    public $shop_name         = null;               // 餐馆名
    public $shop_logo         = null;               // 商户标注
    public $classify_name     = null;               // 属性名
    public $contact           = null;               // 联系人
    public $telephone         = null;               // 联系电话
    public $email             = null;               // 电子邮件
    public $shop_area         = null;               // 店铺面积
    public $address           = null;               // 店铺地址
    public $address_num       = null;               // 门牌号
    public $lastmodtime       = null;               // 最后修改的时间
    public $praise_num        = null;               // 点赞数
    public $good_rate         = null;               // 好评率（0.0~1.0，按需要转为82.1%）
    public $open_time         = null;               // 每天的营业时间
    public $img_list          = null;               // 首页轮播图
    public $broadcast_content = null;               // 小喇叭消息（轮播）
    public $is_invoice_vat    = null;               // 是否支付增值税发票（1:支持, 0:未知）
    public $delete            = null;               // 0:未删除; 1:已删除
    public $weixin            = null;               // 信息相关配置
    public $food_attach_list  = null;               // 店铺口味可选附加属性配置list（加辣等）
    public $food_unit_list    = null;               // 店铺餐品可选单位list（份、碗、斤等）
    public $suspend           = null;               // 店铺是否暂停（0:正常使用, 1:被系统管理员暂停, 2:被店铺管理员暂停，参见const.php::ShopSuspend)
    public $is_seat_enable    = null;               // 店铺餐位费是否启用(0:不启用,1启用)
    public $opening_time      = null;               // 营业时间
    public $shop_pay_way      = null;               //店铺启用支付方式 1.现金支付 2:刷卡支付 3:微信支付 4:支付宝支付
    public $pay_time          = null;               //付款时间 1：餐前 2:餐后
    public $sale_way          = null;               //销售方式 1:在店吃 2:外卖 3:打包 4:自提
    public $shop_label        = null;               //店铺标签
    public $invoice_remark    = null;               //发票备注消息
    public $shop_seat_region  = null;               //店铺餐桌区域标签
    public $shop_seat_type    = null;               //店铺餐桌类型标签
    public $shop_seat_shape   = null;               //店铺餐桌桌型标签
    public $shop_composition  = null;               //店铺食材标签
    public $shop_feature      = null;               //店铺特色标签
    public $shop_business     = null;               //店铺工商信息
    public $mail_vali         = null;               //邮箱验证



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
        $this->shop_id             = $cursor['shop_id'];
        $this->shop_name           = $cursor['shop_name'];
        $this->shop_logo           = $cursor['shop_logo'];
        $this->contact             = $cursor['contact'];
        $this->classify_name       = $cursor['classify_name'];
        $this->telephone           = $cursor['telephone'];
        $this->email               = $cursor['email'];
        $this->address             = $cursor['address'];
        $this->shop_area           = $cursor['shop_area'];
        $this->address_num         = $cursor['address_num'];
        $this->praise_num          = $cursor['praise_num'];
        $this->good_rate           = $cursor['good_rate'];
        $this->open_time           = $cursor['open_time'];
        $this->img_list            = $cursor['img_list'];
        //$this->broadcast_content = Broadcast::ToList($cursor['broadcast_content']);
        $this->broadcast_content   = $cursor['broadcast_content'];
        $this->is_invoice_vat      = new ShopInvoiceInfo($cursor['is_invoice_vat']);
        $this->lastmodtime         = $cursor['lastmodtime'];
        $this->delete              = $cursor['delete'];
        $this->weixin              = new WeixinInfo($cursor['weixin']);
        $this->food_attach_list    = $cursor['food_attach_list'];
        $this->food_unit_list      = $cursor['food_unit_list'];
        $this->suspend             = $cursor['suspend'];
        $this->is_seat_enable      = $cursor['is_seat_enable'];
        $this->opening_time        = new OpenTime($cursor['opening_time']);
        $this->shop_pay_way        = $cursor['shop_pay_way'];
        $this->pay_time            = $cursor['pay_time'];
        $this->sale_way            = $cursor['sale_way'];
        $this->invoice_remark      = $cursor['invoice_remark'];
        $this->shop_label          = $cursor['shop_label'];
        $this->shop_seat_region    = $cursor['shop_seat_region'];
        $this->shop_seat_type      = $cursor['shop_seat_type'];
        $this->shop_seat_shape     = $cursor['shop_seat_shape'];
        $this->shop_composition    = $cursor['shop_composition'];
        $this->shop_feature        = $cursor['shop_feature'];
        $this->shop_business       = new ShopBusiness($cursor['shop_business']);
        $this->mail_vali           = new MailVail($cursor['mail_vali']);
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

        if (null !== $info->shop_id){
            $set["shop_id"] = (string)$info->shop_id;
        }
        if (null !== $info->shop_name){
            $set["shop_name"] = (string)$info->shop_name;
        }
        if (null !== $info->shop_logo){
            $set["shop_logo"] = (string)$info->shop_logo;
        }
        if (null !== $info->contact){
            $set["contact"] = (string)$info->contact;
        }
        if (null !== $info->classify_name){
            $set["classify_name"] = (string)$info->classify_name;
        }
        if (null !== $info->telephone){
            $set["telephone"] = (string)$info->telephone;
        }
        if (null !== $info->paise_num){
            $set["praise_num"] = (int)$info->paise_num;
        }
        if (null !== $info->email){
            $set["email"] = (string)$info->email;
        }
        if (null !== $info->address){
            $set["address"] = (string)$info->address;
        }
        if (null !== $info->shop_area){
            $set["shop_area"] = (float)$info->shop_area;
        }
        if (null !== $info->address_num){
            $set["address_num"] = (string)$info->address_num;
        }
        if (null !== $info->good_rate){
            $set["good_rate"] = (float)$info->good_rate;
        }
        if (null !== $info->weixin){
            if (null !== $info->weixin->sub_mch_id) {
                $set["weixin.sub_mch_id"] = (string)$info->weixin->sub_mch_id;
            }
        }
        if (null !== $info->opening_time){
            $set["opening_time"] = $info->opening_time;
        }
        if (null !== $info->img_list){
            $set["img_list"] = $info->img_list;
        }
        if (null !== $info->broadcast_content){
            $set["broadcast_content"] = (string)$info->broadcast_content;
        }
        if (null !== $info->is_invoice_vat){
            $set["is_invoice_vat"] = $info->is_invoice_vat;
        }
        if (null !== $info->is_seat_enable){
            $set["is_seat_enable"] =$info->is_seat_enable;
        }
        if (null !== $info->shop_pay_way){
            $set["shop_pay_way"] = $info->shop_pay_way;
        }
        if (null !== $info->pay_time){
            $set["pay_time"] = $info->pay_time;
        }
        if (null !== $info->sale_way){
            $set["sale_way"] = $info->sale_way;
        }
        if (null !== $info->open_time){
            $set["open_time"] = $info->open_time;
        }
        if (null !== $info->suspend){
            $set["suspend"] = (int)$info->suspend;
        }
        if (null !== $info->invoice_remark){
            $set["invoice_remark"] = (string)$info->invoice_remark;
        }
        if (null !== $info->shop_label){
            $set["shop_label"] = $info->shop_label;
        }
        if (null !== $info->shop_seat_region){
            $set["shop_seat_region"] = $info->shop_seat_region;
        }
        if (null !== $info->shop_seat_type){
            $set["shop_seat_type"] = $info->shop_seat_type;
        }
        if (null !== $info->shop_seat_shape){
            $set["shop_seat_shape"] = $info->shop_seat_shape;
        }
        if (null !== $info->shop_composition){
            $set["shop_composition"] = $info->shop_composition;
        }
        if (null !== $info->shop_feature){
            $set["shop_feature"] = $info->shop_feature;
        }
        if (null !== $info->shop_business){
            $set["shop_business"] = $info->shop_business;
        }
        if (null !== $info->mail_vali){
            $set["mail_vali"] = $info->mail_vali;
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
                'delete'      => 1,
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

