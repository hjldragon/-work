<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 店铺发票表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class ShopInvoiceEntry extends BaseInfo
{
    public $invoice_id                   = null; // id
    public $shop_id                      = null; // 店铺id
    public $taxpayer_name                = null; // 企业名称
    public $taxpayer_num                 = null; // 纳税人识别号(税号)
    public $legal_person_name            = null; // 注册企业法人代表名称
    public $contacts_name                = null; // 联系人
    public $email                        = null; // 联系人邮箱地址
    public $business_mobile              = null; // 企业电话
    public $phone                        = null; // 联系人手机号
    public $bank_name                    = null; // 银行名称
    public $bank_account                 = null; // 银行账号
    public $address                      = null; // 不包含省市名称的地址
    public $province_name                = null; // 省名称
    public $city_name                    = null; // 市(地区)名称
    public $drawer                       = null; // 开票人
    public $reviewer                     = null; // 复核人
    public $payee                        = null; // 收款人
    public $tax_registration_certificate = null; // 税务登记证图片（Base64）字符串
    public $client_name                  = null; // 收件人名称
    public $client_address               = null; // 收件人地址
    public $client_phone                 = null; // 收件人电话
    public $lastmodtime                  = null; // 数据最后修改时间
    public $delete                       = null; // 0:正常, 1:已删除
    public $ctime                        = null; // 创建时间

    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    protected function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->invoice_id                   = $cursor["invoice_id"];
        $this->shop_id                      = $cursor["shop_id"];
        $this->taxpayer_name                = $cursor["taxpayer_name"];
        $this->taxpayer_num                 = $cursor["taxpayer_num"];
        $this->legal_person_name            = $cursor["legal_person_name"];
        $this->contacts_name                = $cursor["contacts_name"];
        $this->email                        = $cursor["email"];
        $this->business_mobile              = $cursor["business_mobile"];
        $this->phone                        = $cursor["phone"];
        $this->bank_name                    = $cursor["bank_name"];
        $this->bank_account                 = $cursor["bank_account"];
        $this->address                      = $cursor["address"];
        $this->province_name                = $cursor["province_name"];
        $this->city_name                    = $cursor["city_name"];
        $this->drawer                       = $cursor["drawer"];
        $this->reviewer                     = $cursor["reviewer"];
        $this->payee                        = $cursor["payee"];
        $this->tax_registration_certificate = $cursor["tax_registration_certificate"];
        $this->client_name                  = $cursor["client_name"];
        $this->client_address               = $cursor["client_address"];
        $this->client_phone                 = $cursor["client_phone"];
        $this->lastmodtime                  = $cursor["lastmodtime"];
        $this->delete                       = $cursor["delete"];
        $this->ctime                        = $cursor["ctime"];
    }
}
class ShopInvoice extends MgoBase
{
    protected function Tablename()
    {
        return 'shop_invoice';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'invoice_id' => (string)$info->invoice_id
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }

        if(null !== $info->taxpayer_name)
        {
            $set["taxpayer_name"] = (string)$info->taxpayer_name;
        }

        if(null !== $info->taxpayer_num)
        {
            $set["taxpayer_num"] = (string)$info->taxpayer_num;
        }

        if(null !== $info->legal_person_name)
        {
            $set["legal_person_name"] = (string)$info->legal_person_name;
        }

        if(null !== $info->email)
        {
            $set["email"] = (string)$info->email;
        }

        if(null !== $info->business_mobile)
        {
            $set["business_mobile"] = (string)$info->business_mobile;
        }

        if(null !== $info->phone)
        {
               $set["phone"] = (string)$info->phone;
        }

        if(null !== $info->bank_name)
        {
            $set["bank_name"] = (string)$info->bank_name;
        }

        if(null !== $info->bank_account)
        {
            $set["bank_account"] = (string)$info->bank_account;
        }
         if(null !== $info->address)
        {
            $set["address"] = (string)$info->address;
        }
         if(null !== $info->province_name)
        {
            $set["province_name"] = (string)$info->province_name;
        }

        if(null !== $info->city_name)
        {
            $set["city_name"] = (string)$info->city_name;
        }
        if(null !== $info->drawer)
        {
            $set["drawer"] = (string)$info->drawer;
        }
        if(null !== $info->reviewer)
        {
            $set["reviewer"] = (string)$info->reviewer;
        }
         if(null !== $info->payee)
        {
            $set["payee"] = (string)$info->payee;
        }
         if(null !== $info->tax_registration_certificate)
        {
            $set["tax_registration_certificate"] = (string)$info->tax_registration_certificate;
        }
        if(null !== $info->client_name)
        {
            $set["client_name"] = (string)$info->client_name;
        }
        if(null !== $info->client_address)
        {
            $set["client_address"] = (string)$info->client_address;
        }
         if(null !== $info->client_phone)
        {
            $set["client_phone"] = (string)$info->client_phone;
        }

        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->ctime)
        {
            $set["ctime"] = (int)$info->ctime;
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

    public function GetinvoiceById($invoice_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$invoice_id, "invoice_id");
        return GoodsinvoiceEntry::ToObj($cursor);
    }
    // public function GetinvoiceById($invoice_id)
    // {
    //     $db = \DbPool::GetMongoDb();
    //     $table = $db->selectCollection($this->Tablename());

    //     $cond = [
    //         'invoice_id' => (string)$invoice_id,
    //         'delete'      => 0,
    //     ];
    //     $cursor = $table->findOne($cond);
    //     return new GoodsinvoiceEntry($cursor);
    // }
}



?>