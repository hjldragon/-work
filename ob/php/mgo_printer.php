<?php
/*
 * [Rocky 2017-05-04 11:35:11]
 * 打印机表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


class PrinterEntry
{
    public $printer_id            = null;     // 打印机id
    public $printer_name          = null;     // 打印机名
    // 注：printer_category字段改名为receipt_type [Rocky 2017-12-14]
    public $receipt_type          = null;     // 支持单据类型  1:'点菜单（后厨）'2:'作废单（后厨）'3:'点菜单（消费者）'4:'结账单'5:'预结账单'
    public $printer_size          = null;     // 打印机规格（58、80）
    public $printer_brand         = null;     // 打印机型号
    public $food_category_list    = null;     // 指定的菜类别（不指定时，为全部）
    public $shop_id               = null;     // 餐馆id
    public $lastmodtime           = null;     // 数据最后修改时间
    public $delete                = null;     // 0:正常, 1:已删除
    public $print_position_left   = null;     // 打印位置调整(左加起始位置，注：不同型号，处理的位置不可能同)
    public $print_position_top    = null;     // 打印位置调整
    public $print_position_width  = null;     // 打印位置调整
    public $print_position_height = null;     // 打印位置调整
    public $print_ip              = null;     // ip地址
    public $copies_num            = null;     // 打印份数
    public $printer_note          = null;     // 备注

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
        $this->printer_id            = $cursor['printer_id'];
        $this->printer_name          = $cursor['printer_name'];
        $this->receipt_type          = $cursor['receipt_type'];
        $this->printer_size          = $cursor['printer_size'];
        $this->printer_brand         = $cursor['printer_brand'];
        $this->food_category_list    = $cursor['food_category_list'];
        $this->shop_id               = $cursor['shop_id'];
        $this->lastmodtime           = $cursor['lastmodtime'];
        $this->print_position_left   = $cursor['print_position_left'];
        $this->print_position_top    = $cursor['print_position_top'];
        $this->print_position_width  = $cursor['print_position_width'];
        $this->print_position_height = $cursor['print_position_height'];
        $this->copies_num            = $cursor['copies_num'];
        $this->print_ip              = $cursor['print_ip'];
        $this->printer_note          = $cursor['printer_note'];
    }

    public static function ToList($cursor)
    {
        $list = [];
        foreach($cursor as $item)
        {
            $entry = new self($item);
            array_push($list, $entry);
        }
        return $list;
    }

}

class Printer
{
    private function Tablename()
    {
        return 'printer';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'printer_id' => (string)$info->printer_id
        );

        $set = array(
            "printer_id" => (string)$info->printer_id,
            "lastmodtime" => time()
        );

        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->printer_id)
        {
            $set["printer_id"] = (string)$info->printer_id;
        }
        if(null !== $info->printer_name)
        {
            $set["printer_name"] = $info->printer_name;
        }
        if(null !== $info->receipt_type)
        {
            $info->receipt_type = (array)$info->receipt_type;
            foreach($info->receipt_type as &$item)
            {
                $item = (int)$item;
            }
            $set["receipt_type"] = $info->receipt_type;
        }
        if(null !== $info->printer_size)
        {
            $set["printer_size"] = (int)$info->printer_size;
        }
        if(null !== $info->printer_brand)
        {
            $set["printer_brand"] = $info->printer_brand;
        }
        if(null !== $info->food_category_list)
        {
            foreach($info->food_category_list as $i => &$item)
            {
                $item = (string)$item;
            }
            usort($info->food_category_list, function($a, $b)
            {
                return strcmp($a, $b);
            });
            $set["food_category_list"] = $info->food_category_list;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->print_position_top)
        {
            $set["print_position_top"] = (float)$info->print_position_top;
        }
        if(null !== $info->print_position_left)
        {
            $set["print_position_left"] = (float)$info->print_position_left;
        }
        if(null !== $info->print_position_width)
        {
            $set["print_position_width"] = (float)$info->print_position_width;
        }
        if(null !== $info->print_position_height)
        {
            $set["print_position_height"] = (float)$info->print_position_height;
        }
        if(null !== $info->printer_note)
        {
            $set["printer_note"] = (string)$info->printer_note;
        }
        if(null !== $info->print_ip)
        {
            $set["print_ip"] = (string)$info->print_ip;
        }
        if(null !== $info->copies_num)
        {
            $set["copies_num"] = (int)$info->copies_num;
        }

        $value = array(
            '$set' => $set
        );

        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true]);
            LogDebug("ret:" . json_encode($ret));
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function BatchDelete($printer_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($printer_id_list as $i => &$item)
        {
            $item = (string)$item;
        }

        $set = array(
            "delete" => 1,
            "lastmodtime" => time()
        );
        $value = array(
            '$set' => $set
        );
        $cond = [
            'printer_id' => ['$in' => $printer_id_list]
        ];
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple'=>true]);
            LogDebug("ret:" . json_encode($ret));
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function GetPrinterById($printer_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'printer_id' => (string)$printer_id
        ];
        $cursor = $table->findOne($cond);
        return new PrinterEntry($cursor);
    }

    public function GetList($shop_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
            'shop_id' => ['$in' => [0,(string)$shop_id]]
        ];
        $cursor = $table->find($cond, ["_id"=>0]);
        // LogDebug(iterator_to_array($cursor));
        return PrinterEntry::ToList($cursor);
    }
}


?>
