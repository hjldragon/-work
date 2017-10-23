<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * cfg表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");


# t_user
class CfgEntry
{
    public $key      = "";  // 配置项
    public $value    = "";  // 配置值
    public $operator = "";  // 操作人
    public $ctime    = 0;   // 创建时间
    public $mtime    = 0;   // 修改时间
    public $delete   = null;// 0:未删除; 1:已删除

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
        $this->key      = $cursor['key'];
        $this->value    = $cursor['value'];
        $this->operator = $cursor['operator'];
        $this->ctime    = $cursor['ctime'];
        $this->mtime    = $cursor['mtime'];
        $this->delete   = $cursor['delete'];
    }

    public static function ToList($cursor)
    {
        $list = array();
        foreach($cursor as $item)
        {
            $entry = new CfgEntry($item);
            array_push($list, $entry);
        }
        return $list;
    }
};

class Cfg
{
    private function Tablename()
    {
        return 'cfg';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'key' => $info->key
        );

        $set = array(
            "key"       => $info->key,
            "mtime"     => time()
        );
        if("" != $info->value)
        {
            $set["value"] = $info->value;
        }
        if("" != $info->operator)
        {
            $set["operator"] = $info->operator;
        }
        if((int)$info->mtime > 0)
        {
            $set["mtime"] = (int)$info->mtime;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        $value = array(
            '$set' => $set
        );

        $info = $table->update($cond, $value, array('upsert'=>true));
        return 0;
    }

    public function Set($key, $value)
    {
        $info = new CfgEntry;
        $info->key = $key;
        $info->value = $value;
        $this->Save($info);
    }

    public function QueryByKeyPrefix($prefix)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'delete'=> array('$ne'=>1),
            'key' => new \mongoregex("/^$prefix/")
        );
        $cursor = $table->find($cond, array("_id"=>0));
        return CfgEntry::ToList($cursor);
    }
}


?>
