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
    public $to   = null;      //时间终止（小于等于）（可能是第二天）

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

class ShopBusinessStatus
{
    public $bs_code = 0;    //（int）企业营业认证状态（0:未认证;1:认证中;2:认证通过;3:认证未通过;
    //下面是认证失败原因（暂时为用都到）
                               //01:营业执照全称格式校验不通过;(company_name)
                               //02:营业执照注册号格式校验不通过;(business_num)
                               //03:营业执照期限校验不通过;(business_date)
                               //04:营业执照期照片限校验不通过;(business_photo)
    public $id_code = 0;    //（int）身份认证状态（0:未认证;1:认证中;2:认证通过;3:认证未通过;
    //下面是认证失败原因（暂时为用都到）
                               //01:真实姓名效验不通过;(legal_person)
                               //02:身份证号效验不通过;(legal_card)
                               //03:身份证照片效验不通过;(legal_card_photo)
    public $rs_code = 0;    //（int）餐饮认证状态（0:未认证;1:认证中;2:认证通过;3:认证未通过;
    //下面是认证失败原因（暂时为用都到）
                               //01:餐饮许可编号效验不通过;(repast_permit_identity,repast_permit_year,repast_permit_num)
                               //02:餐饮服务许可证照片效验不通过;(repast_permit_photo)
                               //03:确认书照片效验不通过;(confirmation)
                               //04:经营范围效验不通过;(business_scope)
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
        $this->bs_code = $cursor['bs_code'];
        $this->id_code = $cursor['id_code'];
        $this->rs_code = $cursor['rs_code'];
    }

}

class MailVail
{
    public $mail                   = null;    //绑定邮箱
    public $passwd                 = null;    //邮箱验证密码
    public $mail_time              = null;    //邮箱有效时间


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
        $this->mail_time      = $cursor['mail_time'];

    }

}

class CollectionSet
{
    public $is_debt                 = null;    // 是否挂账（1:是,0:否）
    public $is_mailing              = null;    // 是否支持抹零（1:是,0:否）
    public $mailing_type            = null;    // 抹零方式(1:抹除分,2:取整数元）

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
        $this->is_debt           = $cursor['is_debt'];
        $this->is_mailing        = $cursor['is_mailing'];
        $this->mailing_type      = $cursor['mailing_type'];

    }

}

class WeixinPaySet
{
    public $pay_way                 = null;    // 收款方式（1:微信个人收款码,2:微信支付）
    public $code_img                = null;    // 个人付款码图片
    public $code_show               = null;    // 付款码展示（1:展示,0:不展示)
    public $sub_mch_id              = null;    // 微信支付商户号
    public $api_key                 = null;    // 微信密钥
    public $spc_sub                 = null;    // 特约商户（1:是,0:不是)
    public $tenpay_img              = null;    // 财付通商户证书

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
        $this->pay_way    = $cursor['pay_way'];
        $this->code_img   = $cursor['code_img'];
        $this->sub_mch_id = $cursor['sub_mch_id'];
        $this->code_show  = $cursor['code_show'];
        $this->api_key    = $cursor['api_key'];
        $this->spc_sub    = $cursor['spc_sub'];
        $this->tenpay_img = $cursor['tenpay_img'];

    }

}

class AlipaySet
{
    public $pay_way                = null;    // 收款方式（1:支付宝个人收款码,2:支付宝当面支付）
    public $code_img               = null;    // 个人付款码图片
    public $code_show              = null;    // 付款码展示（1:展示,0:不展示)
    public $alipay_app_id          = null;    // 支付宝AppID
    public $public_key             = null;    // RSA私钥
    public $private_key            = null;    // 支付宝公钥
    public $safe_code              = null;    // 安全校验码
    public $hz_identity            = null;    // 合作者身份
    public $alipay_num             = null;    // 支付宝账号

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
        $this->pay_way       = $cursor['pay_way'];
        $this->code_img      = $cursor['code_img'];
        $this->alipay_app_id = $cursor['alipay_app_id'];
        $this->code_show     = $cursor['code_show'];
        $this->public_key    = $cursor['public_key'];
        $this->private_key   = $cursor['private_key'];
        $this->safe_code     = $cursor['safe_code'];
        $this->hz_identity   = $cursor['hz_identity'];
        $this->alipay_num    = $cursor['alipay_num'];

    }

}

class ShopEntry
{

    public $shop_id           = null;               // 餐馆店铺id
    public $shop_name         = null;               // 餐馆名
    public $shop_logo         = null;               // 商户标注logo
    public $classify_name     = null;               // 属性名
    public $contact           = null;               // 联系人
    public $telephone         = null;               // 联系电话
    public $shop_area         = null;               // 店铺面积
    public $address           = null;               // 店铺地址
    public $address_num       = null;               // 门牌号
    public $ctime             = null;               // 创建时间
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
    public $shop_pay_way      = null;               // 店铺启用支付方式 1.现金支付 2:刷卡支付 3:微信支付 4:支付宝支付
    public $pay_time          = null;               // 付款时间 1：餐前 2:餐后
    public $sale_way          = null;               // 销售方式 1:在店吃 2:外卖 3:打包 4:自提
    public $shop_label        = null;               // 店铺标签
    public $invoice_remark    = null;               // 发票备注消息
    public $shop_seat_region  = null;               // 店铺餐桌区域标签
    public $shop_seat_type    = null;               // 店铺餐桌类型标签
    public $shop_seat_shape   = null;               // 店铺餐桌桌型标签
    public $shop_composition  = null;               // 店铺食材标签
    public $shop_feature      = null;               // 店铺特色标签
    public $shop_business     = null;               // 店铺工商信息
    public $mail_vali         = null;               // 邮箱验证
    public $shop_bs_status    = null;               // 店铺工商信息认证状态
    public $shop_food_attach  = null;               // 店铺口味标签
    public $shop_model        = null;               // 营业模式
    public $collection_set    = null;               // 收银设置
    public $weixin_pay_set    = null;               // 微信支付设置
    public $alipay_set        = null;               // 微信支付设置
    public $weixin_seting     = null;               // 微信支付是否设置(0:不启用,1启用)
    public $alipay_seting     = null;               // 支付宝支付是否设置(0:不启用,1启用)
    public $auto_order        = null;               // 是否自动下单(0:否,1是)
    public $menu_sort         = null;               // 0.未确定,1热度排序,2 时间排序
    public $custom_screen     = null;               // 客屏是否显示(0:否,1是)
    public $meal_after        = null;               // 是否支持餐前（1.支持,0.不支持）
    public $agent_id          = null;               // 代理商id
    public $agent_type        = null;               // 签约类型(1:区域，2:行业)
    public $province          = null;               // 省
    public $city              = null;               // 市
    public $area              = null;               // 区
    public $from              = null;               // 来源 (1.电话,2.网络,3.展会)
    public $from_salesman     = null;               // 来源销售
    public $from_employee     = null;               // 销售人员
    public $shop_bs_time      = null;               // 店铺工商信息认证申请时间
    public $logo_img_time     = null;               // logo时间（用于判断是否超过一个月）
    public $business_status   = null;               // 工商认证状态(0:未认证,1:待认证,2:认证成功,3:认证失败)
    public $audit_plan        = null;               // 工商信息审核进度(1.销售人员,2.销售经理,3,运营人员,4.运营经理,5.财务人员,6.财务经理)
    public $apply_time        = null;               // 店铺工商信息申请时间
    public $service_status    = null;               // 店铺服务状态（0:无服务，1:服务中,2:服务已过期）
    public $is_freeze         = null;               // 店铺是否冻结（1:冻结，0未冻结,）




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
        $this->address             = $cursor['address'];
        $this->shop_area           = $cursor['shop_area'];
        $this->address_num         = $cursor['address_num'];
        $this->ctime               = $cursor['ctime'];
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
        $this->opening_time        = OpenTime::ToList($cursor['opening_time']);
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
        $this->shop_bs_status      = new ShopBusinessStatus($cursor['shop_bs_status']);
        $this->shop_food_attach    = $cursor['shop_food_attach'];
        $this->shop_model          = $cursor['shop_model'];
        $this->collection_set      = new CollectionSet($cursor['collection_set']);
        $this->weixin_pay_set      = new WeixinPaySet($cursor['weixin_pay_set']);
        $this->alipay_set          = new AlipaySet($cursor['alipay_set']);
        $this->weixin_seting       = $cursor['weixin_seting'];
        $this->alipay_seting       = $cursor['alipay_seting'];
        $this->auto_order          = $cursor['auto_order'];
        $this->custom_screen       = $cursor['custom_screen'];
        $this->meal_after          = $cursor['meal_after'];
        $this->menu_sort           = $cursor['menu_sort'];
        $this->agent_id            = $cursor['agent_id'];
        $this->agent_type          = $cursor['agent_type'];
        $this->province            = $cursor['province'];
        $this->city                = $cursor['city'];
        $this->area                = $cursor['area'];
        $this->from                = $cursor['from'];
        $this->from_salesman       = $cursor['from_salesman'];
        $this->from_employee       = $cursor['from_employee'];
        $this->shop_bs_time        = $cursor['shop_bs_time'];
        $this->logo_img_time       = $cursor['logo_img_time'];
        $this->business_status     = $cursor['business_status'];
        $this->apply_time          = $cursor['apply_time'];
        $this->audit_plan          = $cursor['audit_plan'];
        $this->is_signing          = $cursor['is_signing'];
        $this->service_status      = $cursor['service_status'];
        $this->is_freeze           = $cursor['is_freeze'];

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
        if (null !== $info->address){
            $set["address"] = (string)$info->address;
        }
        if (null !== $info->shop_area){
            $set["shop_area"] = (float)$info->shop_area;
        }
        if (null !== $info->address_num){
            $set["address_num"] = (string)$info->address_num;
        }
        if (null !== $info->ctime){
            $set["ctime"] = $info->ctime;
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
            $time = [];
            foreach ($info->opening_time as $v) {
                $from     = new Time();
                $from->hh = (string)$v->from->hh;
                $from->mm = (string)$v->from->mm;
                $from->ss = (string)$v->from->ss;
                $to       = new Time();
                $to->hh   = (string)$v->to->hh;
                $to->mm   = (string)$v->to->mm;
                $to->ss   = (string)$v->to->ss;
                $p        = new OpenTime();
                $p->type  = (int)$v->type;
                $p->from  = $from;
                $p->to    = $to;
                $time[]   = $p;
            }
            $set["opening_time"] = $time;
        }
        if (null !== $info->img_list){
            $set["img_list"] = $info->img_list;
        }
        if (null !== $info->broadcast_content){
            $set["broadcast_content"] = (string)$info->broadcast_content;
        }
        if (null !== $info->is_invoice_vat){
            if (null !== $info->is_invoice_vat->is_invoice) {
                $set["is_invoice_vat.is_invoice"] = (int)$info->is_invoice_vat->is_invoice;
            }
            if(null !== $info->is_invoice_vat->invoice_type){
                foreach ($info->is_invoice_vat->invoice_type as &$v){
                    $v = (int)$v;
                }
                $set["is_invoice_vat.invoice_type"] = $info->is_invoice_vat->invoice_type;
            }
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
            $set["shop_business"] = new ShopBusiness([
                'company_name'           => (string)$info->shop_business->company_name,
                'legal_person'           => (string)$info->shop_business->legal_person,
                'legal_phone'            => (string)$info->shop_business->legal_phone,
                'legal_card'             => (string)$info->shop_business->legal_card,
                'legal_card_photo'       => $info->shop_business->legal_card_photo,
                'business_date'          => $info->shop_business->business_date,
                'business_num'           => (string)$info->shop_business->business_num,
                'business_photo'         => (string)$info->shop_business->business_photo,
                'repast_permit_identity' => (string)$info->shop_business->repast_permit_identity,
                'repast_permit_year'     => (int)$info->shop_business->repast_permit_year,
                'repast_permit_num'      => (string)$info->shop_business->repast_permit_num,
                'repast_permit_photo'    => (string)$info->shop_business->repast_permit_photo,
                'confirmation'           => (string)$info->shop_business->confirmation,
                'business_scope'         => (string)$info->shop_business->business_scope,
            ]);
        }
        if (null !== $info->mail_vali){
            if (null !== $info->mail_vali->mail) {
                $set["mail_vali.mail"] = (string)$info->mail_vali->mail;
            }
            if (null !== $info->mail_vali->passwd) {
                $set["mail_vali.passwd"] = (string)$info->mail_vali->passwd;
            }
            if (null !== $info->mail_vali->mail_time) {
                $set["mail_vali.mail_time"] = (string)$info->mail_vali->mail_time;
            }
        }
        if (null !== $info->shop_bs_status){
            if (null !== $info->shop_bs_status->bs_code) {
                $set["shop_bs_status.bs_code"] = (int)$info->shop_bs_status->bs_code;
            }
            if (null !== $info->shop_bs_status->id_code) {
                $set["shop_bs_status.id_code"] = (int)$info->shop_bs_status->id_code;
            }
            if (null !== $info->shop_bs_status->rs_code) {
                $set["shop_bs_status.rs_code"] = (int)$info->shop_bs_status->rs_code;
            }
        }
        if (null !== $info->shop_food_attach){
            $set["shop_food_attach"] = $info->shop_food_attach;
        }
        if (null !== $info->food_attach_list){
            $set["food_attach_list"] = $info->food_attach_list;
        }
        if (null !== $info->food_unit_list){
            $set["food_unit_list"] = $info->food_unit_list;
        }
        if (null !== $info->shop_model){
            $set["shop_model"] = $info->shop_model;
        }
        if (null !== $info->collection_set){
            $set["collection_set"] = new CollectionSet([
                'is_debt'      => (int)$info->collection_set->is_debt,
                'is_mailing'   => (int)$info->collection_set->is_mailing,
                'mailing_type' => (int)$info->collection_set->mailing_type,
            ]);
        }
        if (null !== $info->weixin_pay_set){
            if (null !== $info->weixin_pay_set->pay_way) {
                $set["weixin_pay_set.pay_way"] = (int)$info->weixin_pay_set->pay_way;
            }
            if (null !== $info->weixin_pay_set->code_show) {
                $set["weixin_pay_set.code_show"] = (int)$info->weixin_pay_set->code_show;
            }
            if (null !== $info->weixin_pay_set->code_img) {
                $set["weixin_pay_set.code_img"] = (string)$info->weixin_pay_set->code_img;
            }
            if (null !== $info->weixin_pay_set->sub_mch_id) {
                $set["weixin_pay_set.sub_mch_id"] = (string)$info->weixin_pay_set->sub_mch_id;
            }
            if (null !== $info->weixin_pay_set->api_key) {
                $set["weixin_pay_set.api_key"] = (string)$info->weixin_pay_set->api_key;
            }
            if (null !== $info->weixin_pay_set->spc_sub) {
                $set["weixin_pay_set.spc_sub"] = (int)$info->weixin_pay_set->spc_sub;
            }
            if (null !== $info->weixin_pay_set->tenpay_img) {
                $set["weixin_pay_set.tenpay_img"] = (string)$info->weixin_pay_set->tenpay_img;
            }
//                $p                     = new WeixinPaySet();
//                $p->pay_way            = (int)$info->weixin_pay_set->pay_way;
//                $p->code_show          = (int)$info->weixin_pay_set->code_show;
//                $p->code_img           = (string)$info->weixin_pay_set->code_img;
//                $p->sub_mch_id         = (string)$info->weixin_pay_set->sub_mch_id;
//                $p->api_key            = (string)$info->weixin_pay_set->api_key;
//                $p->spc_sub            = (int)$info->weixin_pay_set->spc_sub;
//                $p->tenpay_img         = (string)$info->weixin_pay_set->tenpay_img;
//                $set["weixin_pay_set"] = $p;
        }
        if (null !== $info->alipay_set){
            if (null !== $info->alipay_set->pay_way) {
                $set["alipay_set.pay_way"] = (int)$info->alipay_set->pay_way;
            }
            if (null !== $info->alipay_set->code_show) {
                $set["alipay_set.code_show"] = (int)$info->alipay_set->code_show;
            }
            if (null !== $info->alipay_set->code_img) {
                $set["alipay_set.code_img"] = (string)$info->alipay_set->code_img;
            }
            if (null !== $info->alipay_set->alipay_app_id) {
                $set["alipay_set.alipay_app_id"] = (string)$info->alipay_set->alipay_app_id;
            }
            if (null !== $info->alipay_set->public_key) {
                $set["alipay_set.public_key"] = (string)$info->alipay_set->public_key;
            }
            if (null !== $info->alipay_set->private_key) {
                $set["alipay_set.private_key"] = (string)$info->alipay_set->private_key;
            }
            if (null !== $info->alipay_set->safe_code) {
                $set["alipay_set.safe_code"] = (string)$info->alipay_set->safe_code;
            }
            if (null !== $info->alipay_set->hz_identity) {
                $set["alipay_set.hz_identity"] = (string)$info->alipay_set->hz_identity;
            }
            if (null !== $info->alipay_set->alipay_num) {
                $set["alipay_set.alipay_num"] = (string)$info->alipay_set->alipay_num;
            }
//                $p                     = new AlipaySet();
//                $p->pay_way            = (int)$info->alipay_set->pay_way;
//                $p->code_show          = (int)$info->alipay_set->code_show;
//                $p->code_img           = (string)$info->alipay_set->code_img;
//                $p->alipay_app_id      = (string)$info->alipay_set->alipay_app_id;
//                $p->public_key         = (string)$info->alipay_set->public_key;
//                $p->private_key        = (string)$info->alipay_set->private_key;
//                $p->safe_code          = (string)$info->alipay_set->safe_code;
//                $p->hz_identity        = (string)$info->alipay_set->hz_identity;
//                $p->alipay_num         = (string)$info->alipay_set->alipay_num;
//                $set["alipay_set"] = $p;

        }
        if (null !== $info->weixin_seting){
            $set["weixin_seting"] = $info->weixin_seting;
        }
        if (null !== $info->alipay_seting){
            $set["alipay_seting"] = $info->alipay_seting;
        }
        if (null !== $info->auto_order){
            $set["auto_order"] = (int)$info->auto_order;
        }
        if (null !== $info->custom_screen){
            $set["custom_screen"] = (int)$info->custom_screen;
        }
        if (null !== $info->meal_after){
            $set["meal_after"] = (int)$info->meal_after;
        }
        if (null !== $info->menu_sort){
            $set["menu_sort"] = (int)$info->menu_sort;
        }
        if (null !== $info->agent_id){
            $set["agent_id"] = (string)$info->agent_id;
        }
        if (null !== $info->agent_type){
            $set["agent_type"] = (int)$info->agent_type;
        }
        if (null !== $info->province){
            $set["province"] = (string)$info->province;
        }
        if (null !== $info->city){
            $set["city"] = (string)$info->city;
        }
        if (null !== $info->area){
            $set["area"] = (string)$info->area;
        }
        if (null !== $info->from_salesman){
            $set["from_salesman"] = (string)$info->from_salesman;
        }
        if (null !== $info->from){
            $set["from"] = (int)$info->from;
        }
        if (null !== $info->shop_bs_time){
            $set["from"] = (int)$info->shop_bs_time;
        }
        if (null !== $info->logo_img_time){
            $set["logo_img_time"] = (int)$info->logo_img_time;
        }
        if (null !== $info->business_status){
            $set["business_status"] = (int)$info->business_status;
        }
        if (null !== $info->apply_time){
            $set["apply_time"] = (int)$info->apply_time;
        }
        if (null !== $info->audit_plan){
            $set["audit_plan"] = (int)$info->audit_plan;
        }
        if (null !== $info->from_employee){
            $set["from_employee"] = (string)$info->from_employee;
        }
        if (null !== $info->is_signing){
            $set["is_signing"] = (int)$info->is_signing;
        }
        if (null !== $info->service_status){
            $set["service_status"] = (int)$info->service_status;
        }
        if (null !== $info->is_freeze){
            $set["is_freeze"] = (int)$info->is_freeze;
        }
        $value = array(
            '$set' => $set
        );
        LogDebug($set['weixin_pay_set']);
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

    public function GetShopByAgentId($agent_id, &$total)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'agent_id' => (string)$agent_id,
            'delete'   => ['$ne' => 1]
        );

        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(["lastmodtime"=>-1]);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return  ShopEntry::ToList($cursor);
    }

    public function GetShopCountByAgentId($agent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'agent_id' => ['$in' => $agent_id],
            'delete'   => ['$ne' => 1]
        );
        $field["_id"] = 0;
        $count = $table->count($cond);
        return  $count;
    }

    public function GetShopList($filter = null, $page_size, $page_no, $sortby = [], &$total=null)
    {
        $db    = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond  = ['delete' => ['$ne' => 1]];
        if (null != $filter) {
//             $shop_id = $filter['shop_id'];
//             if (!empty($shop_id)) {
//                 $cond['shop_id'] = (string)$shop_id;
//             }
            $agent_id = $filter['agent_id'];
            if (!empty($agent_id)) {
                $cond['agent_id'] = (string)$agent_id;
            }
            $shop_name = $filter['shop_name'];
            if (!empty($shop_name)) {
                $cond['shop_name'] = new \MongoRegex("/$shop_name/");
            }
            $agent_id = $filter['agent_id'];
            if (!empty($agent_id)) {
                $cond['agent_id'] = (string)$agent_id;
            }
            $province = $filter['province']; 
            if (!empty($province)) {
                $cond['province'] = (string)$province;
            }
            $city = $filter['city'];
            if (!empty($city)) {
                $cond['city'] = (string)$city;
            }
            $area = $filter['area'];
            if (!empty($area)) {
                $cond['area'] = (string)$area;
            }
            $agent_type = $filter['agent_type'];
            if (!empty($agent_type)) {
                $cond['agent_type'] = (int)$agent_type;
            }
            $business_status = $filter['business_status'];
            if (null != $business_status) {
                $cond['business_status'] = (int)$business_status;
            }
            $business_status_list = $filter['business_status_list'];
            if(!empty($business_status_list))
            {
                foreach($business_status_list as $i => &$item)
                {
                    $item = (int)$item;
                }
                $cond["business_status"] = ['$in' => $business_status_list];
            }

            $shop_model = $filter['shop_model'];
            if (!empty($shop_model)) {
                $cond['shop_model'] = $shop_model;
            }
            $from = $filter['from'];
            if (!empty($from)) {
                $cond['from'] = (int)$from;
            }
            $from_employee = $filter['from_employee'];
            if (!empty($from_employee)) {
                $cond['from_employee'] = (string)$from_employee;
            }
            $begin_time = $filter['begin_time'];
            $end_time   = $filter['end_time'];
            if(!empty($begin_time))
            {
                $cond['ctime'] = [
                    '$gte' => (int)$begin_time,
                    '$lte' => (int)$end_time
                ];
            }
            $begin_bs_time = $filter['begin_bs_time'];
            $end_bs_time   = $filter['end_bs_time'];
            if(!empty($begin_bs_time))
            {
                $cond['shop_bs_time'] = [
                    '$gte' => (int)$begin_bs_time,
                    '$lte' => (int)$end_bs_time
                ];
            }
        }
        if(empty($sortby)){
            $sortby['ctime'] = -1;
        }
        //LogDebug($cond);
        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return ShopEntry::ToList($cursor);
    }

    public function GetAllShopList($filter = null)
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
            $business_status = $filter['business_status'];
            if (null != $business_status) {
                $cond['business_status'] = (int)$business_status;
            }
        }
        $cursor = $table->find($cond, ["_id" => 0])->sort(["shop_name" => 1]);
        return ShopEntry::ToList($cursor);
    }

    public function GetByFrom($from, $agent_id, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'from'     => (int)$from,
            'agent_id' => (string)$agent_id,
            'delete'   => ['$ne' => 1]
        );

        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(["lastmodtime"=>-1]);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return  ShopEntry::ToList($cursor);
    }
    public function GetByFromEmployee($from_employee, $agent_id, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'from_employee' => (string)$from_employee,
            'agent_id'      => (string)$agent_id,
            'delete'        => ['$ne' => 1]
        );
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(["lastmodtime"=>-1]);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return  ShopEntry::ToList($cursor);
    }

    public function GetByBusinessStatus($business_status, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'business_status' => (int)$business_status,
            'delete'          => ['$ne' => 1]
        );

        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(["lastmodtime"=>-1]);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return  ShopEntry::ToList($cursor);
    }

    public function GetShopTotal($filter = null, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'delete'          => ['$ne' => 1]
        );
        if (null != $filter) {
            $agent_type = $filter['agent_type'];
            if (!empty($agent_type)) {
                $cond['agent_type'] = (int)$agent_type;
            }
            $city = $filter['city'];
            if (!empty($city)) {
                $cond['city'] = (string)$city;
            }
            $business_status = $filter['business_status'];
            if (!empty($business_status)) {
                $cond['business_status'] = (int)$business_status;
            }
        }
        $field["_id"] = 0;

        $cursor = $table->find($cond, $field)->sort(["lastmodtime"=>-1]);

        if(null !== $total){
            $total = $table->count($cond);
        }
        return  ShopEntry::ToList($cursor);
    }
}


?>

