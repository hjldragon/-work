<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 城市录入及等级表作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class FromEntry extends BaseInfo
{
    public $from_id         = null; // 来源id
    public $ctime           = null; // 创建时间
    public $from            = null; // 来源名
    public $platformer_id   = null; // 创建人id
    public $is_edit         = null; // 是否可编辑
    public $delete          = null; // 0:正常, 1:已删除
    public $lastmodtime     = null; // 最后修改时间

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
        $this->from_id          = $cursor['from_id'];
        $this->ctime            = $cursor['ctime'];
        $this->from             = $cursor['from'];
        $this->platformer_id    = $cursor['platformer_id'];
        $this->is_edit          = $cursor['is_edit'];
        $this->delete           = $cursor['delete'];
        $this->lastmodtime      = $cursor['lastmodtime'];

    }
}
class From extends MgoBase
{
    protected function Tablename()
    {
        return 'from';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'from_id'   => (string)$info->from_id,
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->from)
        {
            $set["from"] = (string)$info->from;
        }
        if(null !== $info->platformer_id)
        {
            $set["platformer_id"] = (string)$info->platformer_id;
        }
        if(null !== $info->is_edit)
        {
            $set["is_edit"] = (int)$info->is_edit;
        }
        if(null !== $info->ctime)
        {
            $set["ctime"] = (int)$info->ctime;
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
        return parent::DoBatchDelete($id_list, "from_id");
    }

    public function GetByFromName($from)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'from'    => (string)$from,
            'delete'  => 0,
        ];
        $cursor = $table->findOne($cond);
        return new FromEntry($cursor);
    }
    public function GetByFromId($from_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'from_id'    => (string)$from_id,
            'delete'  => 0,
        ];
        $cursor = $table->findOne($cond);
        return new FromEntry($cursor);
    }
    //获取列表数据
    public function GetList($filter=null, $page_size, $page_no, $sortby = [], &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' =>  ['$ne'=>1] ,
        ];
        if(null != $filter)
        {
            $platformer_id = $filter['platformer_id'];
            if(!empty($platformer_id))
            {
                $cond['platformer_id'] = (string)$platformer_id;
            }
        }
        if(empty($sortby)){
            $sortby['ctime'] = -1;
        }
        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return FromEntry::ToList($cursor);
    }

    public function GetFromList($filter=null, $sortby=[])
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'      => ['$ne'=>1],
        ];
        if(null != $filter)
        {
            $platformer_id = $filter['platformer_id'];
            if(!empty($platformer_id))
            {
                $cond['platformer_id'] = (string)$platformer_id;
            }
        }
        if(empty($sortby))
        {
            $sortby['_id'] = -1;
        }

        $field["_id"] = 0;

        $cursor = $table->find($cond, $field)->sort($sortby);
        return FromEntry::ToList($cursor);
    }
}






?>
