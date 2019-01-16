<?php
/*
 * [Rocky 2018-06-07]
 * 工商信息表 
 */
///declare(encoding='UTF-8');
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

//店铺工商信息管理
class BusinessEntry extends BaseInfo
{
    //public $business_id                  = null;    //工商信息id
    public $shop_id                      = null;    //店铺id
    public $agent_id                     = null;    //代理商id
    public $corporate_num                = null;    //商户名对公账户
    public $legal_person                 = null;    //法人代表
    public $legal_card                   = null;    //法人身份证
    public $legal_card_photo             = null;    //法人身份照片
    public $business_num                 = null;    //营业执照注册号
    public $business_date                = null;    //营业期限
    public $business_photo               = null;    //营业执照照片
    public $repast_permit_num            = null;    //餐饮许可编号
    public $repast_permit_photo          = null;    //餐饮许可扫描证件
    public $taxpayer_num                 = null;    //纳税人识别号
    public $taxpayer_photo               = null;    //纳税人扫描证
    public $business_scope               = null;    //经营范围
    public $business_sever_money         = null;    //商户服务费(财务审核的时候收取)
    public $water_num                    = null;    //水单号(财务审核填补上的）
    //public $is_take_money                = null;    //是否已经收取服务费(1.收取,0.未收取)
    public $merchant_num                 = null;    //开户支行
    public $delete                       = null;    //
    public $lastmodtime                  = null;    //



    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    private function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        //$this->business_id               = $cursor['business_id'];
        $this->shop_id                   = $cursor['shop_id'];
        $this->agent_id                  = $cursor['agent_id'];
        $this->corporate_num             = $cursor['corporate_num'];
        $this->legal_person              = $cursor['legal_person'];
        $this->legal_card                = $cursor['legal_card'];
        $this->legal_card_photo          = $cursor['legal_card_photo'];
        $this->business_num              = $cursor['business_num'];
        $this->business_date             = $cursor['business_date'];
        $this->business_photo            = $cursor['business_photo'];
        $this->repast_permit_num         = $cursor['repast_permit_num'];
        $this->repast_permit_photo       = $cursor['repast_permit_photo'];
        $this->taxpayer_num              = $cursor['taxpayer_num'];
        $this->taxpayer_photo            = $cursor['taxpayer_photo'];
        $this->business_scope            = $cursor['business_scope'];
        $this->business_sever_money      = $cursor['business_sever_money'];
        $this->water_num                 = $cursor['water_num'];
        //$this->is_take_money             = $cursor['is_take_money'];
        $this->merchant_num              = $cursor['merchant_num'];
        $this->lastmodtime               = $cursor['lastmodtime'];
        $this->delete                    = $cursor['delete'];
    }
}

class Business extends MgoBase
{
    protected function Tablename()
    {
        return 'business';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'shop_id'   => (string)$info->shop_id,
            'agent_id'  => (string)$info->agent_id,
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->corporate_num)
        {
            $set["corporate_num"] = (string)$info->corporate_num;
        }

        if(null !== $info->legal_person)
        {
            $set["legal_person"] = (string)$info->legal_person;
        }

        if(null !== $info->legal_card)
        {
            $set["legal_card"] = (string)$info->legal_card;
        }

        if(null !== $info->legal_card_photo)
        {
            $set["legal_card_photo"] = $info->legal_card_photo;
        }

        if(null !== $info->business_num)
        {
            $set["business_num"] = (string)$info->business_num;
        }

        if(null !== $info->business_date)
        {
            $set["business_date"] = $info->business_date;
        }

        if(null !== $info->business_photo)
        {
            $set["business_photo"] = (string)$info->business_photo;
        }

        if(null !== $info->repast_permit_num)
        {
            $set["repast_permit_num"] = (string)$info->repast_permit_num;
        }

        if(null !== $info->repast_permit_photo)
        {
            $set["repast_permit_photo"] = (string)$info->repast_permit_photo;
        }

        if(null !== $info->taxpayer_num)
        {
            $set["taxpayer_num"] = (string)$info->taxpayer_num;
        }

        if(null !== $info->taxpayer_photo)
        {
            $set["taxpayer_photo"] = (string)$info->taxpayer_photo;
        }

        if(null !== $info->business_scope)
        {
            $set["business_scope"] = (string)$info->business_scope;
        }
        if(null !== $info->business_sever_money)
        {
            $set["business_sever_money"] = (string)$info->business_sever_money;
        }
        if(null !== $info->water_num)
        {
            $set["water_num"] = (string)$info->water_num;
        }
//        if(null !== $info->is_take_money)
//        {
//            $set["is_take_money"] = (int)$info->is_take_money;
//        }
        if(null !== $info->merchant_num)
        {
            $set["merchant_num"] = (string)$info->merchant_num;
        }

        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        $value = array(
            '$set' => $set
        );

        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true]);
            LogDebug("ret:" . $ret["ok"]);
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function BatchDelete($id_list)
    {
        return parent::DoBatchDelete($id_list, "id_list");
    }

    public function GetExampleById($example_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$example_id, "example_id");
        return BusinessEntry::ToObj($cursor);
    }

    public function GetByShopId($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'shop_id' => (string)$shop_id,
            'delete'  => ['$ne'=>1],
        ];
        $cursor = $table->findOne($cond);
        return new BusinessEntry($cursor);
    }
    public function GetByAgentId($agent_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'agent_id' => (string)$agent_id,
            'delete'   => ['$ne'=>1],
        ];
        $cursor = $table->findOne($cond);
        return new BusinessEntry($cursor);
    }
    public function GetExampleList($filter=null)
    {
        // ...
    }
}
