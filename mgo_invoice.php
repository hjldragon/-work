<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 发票信息表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("const.php");



class InvoiceEntry
{   
    public $invoice_id     = null;   // 发票id
    public $userid         = null;   // 用户id
    public $type           = null;   // 发票类型(1:普通发票,2:专用发票)
    public $title_type     = null;   // 发票抬头类型(1:单位,2:个人)
    public $invoice_title  = null;   // 发票抬头名称
    public $duty_paragraph = null;   // 税号
    public $phone          = null;   // 电话号码
    public $address        = null;   // 地址
    public $bank_name      = null;   // 单位的开户行名称
    public $bank_account   = null;   // 单位的银行账号
    public $email          = null;   // 电子邮箱
    public $lastmodtime    = null;   // 最后修改时间
    public $delete         = null;   // 0:未删除; 1:已删除
    


    // // 具体业务数据
    // public $shop_id = null;     // 当前用户所属的店 （注，此这段分出到员工表）

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
        $this->invoice_id     = $cursor['invoice_id'];
        $this->userid         = $cursor['userid'];
        $this->type           = $cursor['type'];
        $this->title_type     = $cursor['title_type'];
        $this->invoice_title  = $cursor['invoice_title'];
        $this->duty_paragraph = $cursor['duty_paragraph'];
        $this->phone          = $cursor['phone'];
        $this->address        = $cursor['address'];
        $this->bank_name      = $cursor['bank_name'];
        $this->bank_account   = $cursor['bank_account'];
        $this->email          = $cursor['email'];
        $this->lastmodtime    = $cursor['lastmodtime'];
        $this->delete         = $cursor['delete'];
        
    }

    public static function ToList($cursor)
    {
        $list = array();
        foreach($cursor as $item)
        {
            $entry = new UserEntry($item);
            array_push($list, $entry);
        }
        return $list;
    }
    
};

class Invoice
{
    private function Tablename()
    {
        return 'invoice';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'invoice_id' => (string)$info->invoice_id
        );

        
        $set = [
            "invoice_id"  => (string)$info->invoice_id,
        ];
        if(null !== $info->userid)
        {
            $set["userid"] = (int)$info->userid;
        }
        if(null !== $info->type)
        {
            $set["type"] = (int)$info->type;
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
        if(null !== $info->address)
        {
            $set["address"] = (string)$info->address;
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
        if(null !== $info->email)
        {
            $set["email"] = (string)$info->email;
        }
        $set["lastmodtime"] = time();
        
        // LogDebug($set);

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
    public function BatchDeleteById($invoice_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($spec_id_list as $i => &$id)
        {
            $id = (int)$id;
        }

        $cond = array(
            'invoice_id' => array('$in' => $invoice_id_list)
        );

        $value = array(
            'delete' => 1,
            'lastmodtime'=> time(),
        );


        try
        {
            $ret = $table->update($cond,$value, ['safe'=>true, 'upsert'=>true, 'multiple' => true]);
            LogDebug("ret:" . json_encode($ret));
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
       
        return 0;
    }

    //查找单条发票信息
    public function GetInvoiceById($invoice_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'invoice_id' => (string)$invoice_id,
            'delete'  => ['$ne'=>1],
        );
        $field["_id"] = 0;
        $cursor = $table->findOne($cond, $field);
        return new InvoiceEntry($cursor);
    }

    

    //根据用户查找发票信息
    public function GetInvoiceList($userid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'userid'  => (int)$userid,
            'delete'   => ['$ne'=>1],
        ];
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field);
        return InvoiceEntry::ToList($cursor);
    }


}



?>