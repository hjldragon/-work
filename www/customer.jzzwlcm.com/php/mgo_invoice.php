<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 发票信息表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("const.php");
//纸质个人发票
class PaperIndInvoiceEntry
{

    public $invoice_title  = null;   // 发票抬头名称
    public $phone          = null;   // 电话号码
    public $mail_address   = null;   // 邮寄地址

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

        $this->invoice_title  = $cursor['invoice_title'];
        $this->phone          = $cursor['phone'];
        $this->mail_address   = $cursor['mail_address'];


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

}
//纸质单位发票
class PaperUnitInvoiceEntry
{

    public $invoice_title  = null;   // 发票抬头名称
    public $duty_paragraph = null;   // 税号
    public $phone          = null;   // 电话号码
    public $unit_address   = null;   // 单位地址
    public $bank_name      = null;   // 单位的开户行名称
    public $bank_account   = null;   // 单位的开户行名称






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

        $this->invoice_title  = $cursor['invoice_title'];
        $this->duty_paragraph = $cursor['duty_paragraph'];
        $this->phone          = $cursor['phone'];
        $this->unit_address   = $cursor['unit_address'];
        $this->bank_name      = $cursor['bank_name'];
        $this->bank_account   = $cursor['bank_account'];

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

}
//电子单位发票
class EleUnitInvoiceEntry
{

    public $invoice_title  = null;   // 发票抬头名称
    public $duty_paragraph = null;   // 税号
    public $phone          = null;   // 电话号码
    public $email          = null;   // 电子邮箱

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
        $this->invoice_title  = $cursor['invoice_title'];
        $this->duty_paragraph = $cursor['duty_paragraph'];
        $this->phone          = $cursor['phone'];
        $this->email          = $cursor['email'];
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

}
//电子个人发票
class EleIndInvoiceEntry
{

    public $invoice_title  = null;   // 发票抬头名称
    public $phone          = null;   // 电话号码
    public $email          = null;   // 电子邮箱


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

        $this->invoice_title  = $cursor['invoice_title'];
        $this->phone          = $cursor['phone'];
        $this->email          = $cursor['email'];


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

}
//发票信息
class InvoiceEntry
{

    public $userid           = null;   // 用户id
    public $paperindinvoice  = null;   // 纸质个人发票
    public $paperunitinvoice = null;   // 纸质单位发票
    public $eleunitinvoice   = null;   // 电子单位发票
    public $eleindinvoice    = null;   // 电子个人发票
    public $lastmodtime      = null;   // 最后修改时间
    public $delete           = null;   // 0:未删除; 1:已删除
    


   

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
       // $this->invoice_id       = $cursor['invoice_id'];
        $this->userid           = $cursor['userid'];
        $this->paperindinvoice  = new PaperIndInvoiceEntry($cursor['paperindinvoice']);
        $this->paperunitinvoice = new PaperUnitInvoiceEntry($cursor['paperunitinvoice']);
        $this->eleunitinvoice   = new EleUnitInvoiceEntry($cursor['eleunitinvoice']);
        $this->eleindinvoice    = new EleIndInvoiceEntry($cursor['eleindinvoice']);
        $this->lastmodtime      = $cursor['lastmodtime'];
        $this->delete           = $cursor['delete'];
        
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
    
}

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
            "userid"  => (int)$info->userid
        );
        $set = [
            "userid"  => (int)$info->userid,
            'lastmodtime' => time()

        ];

        if(null !== $info->paperindinvoice)
        {
            if(null !== $info->paperindinvoice->invoice_title){
                $set["paperindinvoice.invoice_title"] = (string)$info->paperindinvoice->invoice_title;
            }
            if(null !== $info->paperindinvoice->phone){
                $set["paperindinvoice.phone"] = (string)$info->paperindinvoice->phone;
            }
            if(null !== $info->paperindinvoice->mail_address){
                $set["paperindinvoice.mail_address"] = (string)$info->paperindinvoice->mail_address;
            }
        }
        if(null !== $info->paperunitinvoice)
        {
            if(null !== $info->paperunitinvoice->invoice_title){
                $set["paperunitinvoice.invoice_title"] = (string)$info->paperunitinvoice->invoice_title;
            }
            if(null !== $info->paperunitinvoice->phone){
                $set["paperunitinvoice.phone"] = (string)$info->paperunitinvoice->phone;
            }
            if(null !== $info->paperunitinvoice->duty_paragraph){
                $set["paperunitinvoice.duty_paragraph"] = (string)$info->paperunitinvoice->duty_paragraph;
            }
            if(null !== $info->paperunitinvoice->unit_address){
                $set["paperunitinvoice.unit_address"] = (string)$info->paperunitinvoice->unit_address;
            }
            if(null !== $info->paperunitinvoice->bank_name){
                $set["paperunitinvoice.bank_name"] = (string)$info->paperunitinvoice->bank_name;
            }
            if(null !== $info->paperunitinvoice->bank_account){
                $set["paperunitinvoice.bank_account"] = (string)$info->paperunitinvoice->bank_account;
            }
        }
        if(null !==$info->eleunitinvoice)
        {
            if(null !== $info->eleunitinvoice->invoice_title){
                $set["eleunitinvoice.invoice_title"] = (string)$info->eleunitinvoice->invoice_title;
            }
            if(null !== $info->eleunitinvoice->phone){
                $set["eleunitinvoice.phone"] = (string)$info->eleunitinvoice->phone;
            }
            if(null !== $info->eleunitinvoice->duty_paragraph){
                $set["eleunitinvoice.duty_paragraph"] = (string)$info->eleunitinvoice->duty_paragraph;
            }
            if(null !== $info->eleunitinvoice->email){
                $set["eleunitinvoice.email"] = (string)$info->eleunitinvoice->email;
            }
        }
        if(null !== $info->eleindinvoice)
        {
            if(null !== $info->eleindinvoice->invoice_title){
                $set["eleindinvoice.invoice_title"] = (string)$info->eleindinvoice->invoice_title;
            }
            if(null !== $info->eleindinvoice->phone){
                $set["eleindinvoice.phone"] = (string)$info->eleindinvoice->phone;
            }
            if(null !== $info->eleindinvoice->email){
                $set["eleindinvoice.email"] = (string)$info->eleindinvoice->email;
            }
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


     //批量删除
    public function BatchDeleteById($invoice_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($invoice_id_list as $i => &$id)
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
    public function GetInvoiceByUserid($userid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'userid'  => (int)$userid,
            'delete'   => ['$ne'=>1],
        ];
        $field["_id"] = 0;
        $cursor = $table->findOne($cond, $field);
        return new InvoiceEntry($cursor);
    }


}



?>