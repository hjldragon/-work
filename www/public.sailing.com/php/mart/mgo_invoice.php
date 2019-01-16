<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 发票信息表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

//发票信息
class InvoiceEntry extends BaseInfo
{
    public $invoice_id          = null;   // 发票id
    public $userid              = null;   // 用户id
    public $invoice_type        = null;   // 1普通发票,2专用发票
    public $title_type          = null;   // 1个人,2单位
    public $invoice_title       = null;   // 发票抬头名称
    public $duty_paragraph      = null;   // 税号
    public $phone               = null;   // 收票人电话号码
    public $email               = null;   // 邮箱
    public $unit_phone          = null;   // 单位号码
    public $unit_address        = null;   // 单位地址
    public $bank_name           = null;   // 单位的开户行名称
    public $bank_account        = null;   // 单位的银行账号
    public $lastmodtime         = null;   // 最后修改时间
    public $delete              = null;   // 0:未删除; 1:已删除
    public $ctime               = null;

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
        $this->invoice_id      = $cursor['invoice_id'];
        $this->userid          = $cursor['userid'];
        $this->invoice_type    = $cursor['invoice_type'];
        $this->title_type      = $cursor['title_type'];
        $this->invoice_title   = $cursor['invoice_title'];
        $this->duty_paragraph  = $cursor['duty_paragraph'];
        $this->phone           = $cursor['phone'];
        $this->email           = $cursor['email'];
        $this->unit_phone      = $cursor['unit_phone'];
        $this->unit_address    = $cursor['unit_address'];
        $this->bank_name       = $cursor['bank_name'];
        $this->bank_account    = $cursor['bank_account'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];
        $this->ctime           = $cursor['ctime'];
    }
}

class Invoice extends MgoBase
{
    protected function Tablename()
    {
        return 'invoice';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            "invoice_id"  => (string)$info->invoice_id
        );
        $set = [
            "invoice_id"  => (string)$info->invoice_id,
            'lastmodtime' => time()
        ];

        if(null !== $info->userid)
        {
            $set["userid"] = (int)$info->userid;
        }
        if(null !== $info->invoice_type)
        {
            $set["invoice_type"] = (int)$info->invoice_type;
        }
        if(null !== $info->title_type)
        {
            $set["title_type"] = (int)$info->title_type;
        }
        if(null !== $info->invoice_title)
        {
            $set["invoice_title"] = (string)$info->invoice_title;
        }
        if(null !== $info->duty_paragraph)
        {
            $set["duty_paragraph"] = (string)$info->duty_paragraph;
        }
        if(null !== $info->phone)
        {
            $set["phone"] = (string)$info->phone;
        }
        if(null !== $info->email)
        {
            $set["email"] = (string)$info->email;
        }
            if(null !== $info->unit_phone)
        {
            $set["unit_phone"] = (string)$info->unit_phone;
        }
        if(null !== $info->unit_address)
        {
            $set["unit_address"] = (string)$info->unit_address;
        }
        if(null !== $info->bank_name)
        {
            $set["bank_name"] = (string)$info->bank_name;
        }
        if(null !== $info->bank_account)
        {
            $set["bank_account"] = (string)$info->bank_account;
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


     //批量删除
    // public function BatchDeleteById($invoice_id_list)
    // {
    //     $db = \DbPool::GetMongoDb();
    //     $table = $db->selectCollection($this->Tablename());

    //     foreach($invoice_id_list as $i => &$id)
    //     {
    //         $id = (int)$id;
    //     }

    //     $cond = array(
    //         'invoice_id' => array('$in' => $invoice_id_list)
    //     );

    //     $value = array(
    //         'delete' => 1,
    //         'lastmodtime'=> time(),
    //     );


    //     try
    //     {
    //         $ret = $table->update($cond,$value, ['safe'=>true, 'upsert'=>true, 'multiple' => true]);
    //         LogDebug("ret:" . json_encode($ret));
    //     }
    //     catch(MongoCursorException $e)
    //     {
    //         LogErr($e->getMessage());
    //         return errcode::DB_OPR_ERR;
    //     }

    //     return 0;
    // }

     public function BatchDelete($id_list)
    {
        return parent::DoBatchDelete($id_list, "invoice_id");
    }


    public function GetInvoiceById($invoice_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$invoice_id, "invoice_id");
        return InvoiceEntry::ToObj($cursor);
    }


    //根据用户查找发票信息
    public function GetInvoiceByUserid($userid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'userid'  => (int)$userid,
            'delete'   => ['$ne'=>1],
        ];
        if(empty($sortby))
        {
            $sortby['_id'] = 1;
        }
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort($sortby);
        return InvoiceEntry::ToList($cursor);
    }


}



?>