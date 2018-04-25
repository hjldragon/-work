<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 运营平台人员操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("const.php");


class PlatformerEntry
{
    public $platformer_id    = null;           // 运营平台人员id
    public $platform_id      = null;           // 所属运营平台id
    public $userid           = null;           // 用户id
    public $pl_position_id   = null;           // 平台职位id
    public $pl_department_id = null;           // 平台部门id
    public $pl_name          = null;           // 姓名
    public $remark           = null;           // 备注
    public $ctime            = null;           // 创建时间
    public $lastmodtime      = null;           // 最后修改的时间
    public $delete           = null;           // 是否删除(0:未删除; 1:已删除)
    public $from_record      = null;           // 来源历史输入记录
    public $salesman_record  = null;           // 所属销售历史输入记录
    public $is_admin         = null;           // 是否管理员(0:否; 1:是)
    public $is_freeze        = null;           // 员工冻结 0:正常, 1:已冻结
    
    


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
        $this->platformer_id    = $cursor['platformer_id'];
        $this->platform_id      = $cursor['platform_id'];
        $this->userid           = $cursor['userid'];
        $this->pl_position_id   = $cursor['pl_position_id'];
        $this->pl_department_id = $cursor['pl_department_id'];
        $this->pl_name          = $cursor['pl_name'];
        $this->remark           = $cursor['remark'];
        $this->ctime            = $cursor['ctime'];
        $this->lastmodtime      = $cursor['lastmodtime'];
        $this->delete           = $cursor['delete'];
        $this->from_record      = $cursor['from_record'];
        $this->salesman_record  = $cursor['salesman_record'];
        $this->is_admin         = $cursor['is_admin'];
        $this->is_freeze        = $cursor['is_freeze'];
    }

    public static function ToList($cursor)
    {
        $list = array();
        foreach ($cursor as $item) {
            $entry = new platformerEntry($item);
            array_push($list, $entry);
        }
        return $list;
    }
   
}

class Platformer
{
    private function Tablename()
    {
        return 'platformer';
    }

    public function Save(&$info)
    {
        if (!$info->platformer_id)
        {
            LogErr("param err:" . json_encode($info));
            return \errcode::PARAM_ERR;
        }
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'platformer_id' => (string)$info->platformer_id
        );

        $set = array(
            "platformer_id" => (string)$info->platformer_id,
            "lastmodtime" => time()
        );

        if (null !== $info->userid) {
            $set["userid"] = (int)$info->userid;
        }
        if (null !== $info->platform_id) {
            $set["platform_id"] = (string)$info->platform_id;
        }
        if (null !== $info->pl_position_id) {
            $set["pl_position_id"] = (string)$info->pl_position_id;
        }
        if (null !== $info->pl_department_id) {
            $set["pl_department_id"] = (string)$info->pl_department_id;
        }
        if (null !== $info->pl_name) {
            $set["pl_name"] = (string)$info->pl_name;
        }
        if (null !== $info->ctime) {
            $set["ctime"] = (int)$info->ctime;
        }
        if (null !== $info->delete) {
            $set["delete"] = (int)$info->delete;
        }
        if (null !== $info->remark) {
            $set["remark"] = (string)$info->remark;
        }
        if (null !== $info->from_record) {
            $push["from_record"] = (string)$info->from_record;
        }
        if (null !== $info->salesman_record) {
            $push["salesman_record"] = (string)$info->salesman_record;
        }
        if (null !== $info->is_admin) {
            $set["is_admin"] = (int)$info->is_admin;
        }
        if(null !== $info->is_freeze)
        {
            $set["is_freeze"] = (int)$info->is_freeze;
        }
        
        //LogDebug($set);
        $value['$set'] = $set;
        if($push)
        {
            $value['$push'] = $push;
        }

        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true]);
            LogDebug("ret:" . $ret['ok']);
        } catch (\MongoCursorException $e) {

            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    // 返回 platformerEntry
    public function QueryById($platformer_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'platformer_id' => (string)$platformer_id,
            'delete' => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        return new PlatformerEntry($ret);
    }

    public function QueryByPlatformId($platform_id)
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'platform_id' => (string)$platform_id,
            'delete' => array('$ne'=>1)
        );
        $cursor = $table->find($cond, ["_id"=>0]);
        return platformerEntry::ToList($cursor);
    }

    // 返回 platformerEntry
    public function QueryByOpenid($openid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'openid' => $openid,
            'delete' => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        // LogDebug("[$ret]");
        return new platformerEntry($ret);
    }

    public function GetplatformerList($filter=null, $page_size, $page_no, $sortby = [], &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'      => ['$ne'=>1],
            'platform_id' => \PlatformID::ID
        ];
        //搜索功能
        if(null != $filter)
        {
            $is_freeze = $filter['is_freeze'];
            if($is_freeze != null)
            {   
                if($is_freeze != 1)
                {
                    $cond['is_freeze'] = ['$ne'=>1];
                }
                else
                {
                    $cond['is_freeze'] = 1;
                }
                
            }
            $pl_name = $filter['pl_name'];
            if(!empty($pl_name))
            {
                $cond['pl_name'] = new \MongoRegex("/$pl_name/");
            }
            $pl_department_id = $filter['pl_department_id'];
            if(!empty($pl_department_id))
            {
                $cond['pl_department_id'] = (string)$pl_department_id;
            }
        }
        if(empty($sortby)){
            $sortby['ctime'] = -1;
        }
        
        LogDebug($cond);
        $cursor = $table->find($cond, ["_id"=>0])->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return platformerEntry::ToList($cursor);
    }

    public function BatchDeleteById($platformer_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'platformer_id'  => $platformer_id
        ];
        $set = array(
            "lastmodtime" => time(),
            "delete"      => 1
        );
        $value = array(
            '$set' => $set
        );
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple' => true]);
            LogDebug("ret:" . $ret['ok']);
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function QueryByUserId($userid)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'userid'  => (int)$userid,
            'delete'  => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        return new platformerEntry($ret);
    }
    // 冻结/启用
    public function BatchFreeze($platformer_id, $is_freeze)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'platformer_id' => $platformer_id
        ];
        $set = array(
            "is_freeze"   => (int)$is_freeze,
            "lastmodtime" => time(),
        );
        $value = array(
            '$set' => $set
        );
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple'=>true]);
            LogDebug("ret:" . $ret["ok"]);
        }
        catch(\MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }
    //通过平台部门下所有员工
    public function GetDepartmentPlatformer($pl_department_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'          => ['$ne'=>1],
            'pl_department_id'=>(string)$pl_department_id
        ];

        $cursor = $table->find($cond, ["_id"=>0]);
        return PlatformerEntry::ToList($cursor);
    }

    public function GetPlatformId($platform_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'platform_id' => (string)$platform_id,
            'delete'      => array('$ne'=>1)
        );

        $cursor = $table->find($cond, ["_id"=>0]);
        return PlatformerEntry::ToList($cursor);
    }

    //查找职位下面是否有员工
    //通过平台部门下所有员工
    public function GetPositionPlatformer($pl_position_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'          => ['$ne'=>1],
            'pl_position_id'=>(string)$pl_position_id
        ];

        $cursor = $table->find($cond, ["_id"=>0]);
        return PlatformerEntry::ToList($cursor);
    }
}


?>
