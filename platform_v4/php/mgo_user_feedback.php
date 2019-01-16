<?php
/*
 * [Rocky 2017-05-12 19:39:55]
 * 消息表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("redis_id.php");



class UserFeedbackEntry
{
    public $feedback_id    = null;    // 反馈id
    public $feedback_phone = null;    // 反馈账号
    public $feedback_time  = null;    // 反馈时间
    public $feedback_type  = null;    // 反馈类别(0.默认分类,1.吐槽错误,2.无法使用,3.优化建议)
    public $feedback_from  = null;    // 反馈来源(0.未确定,1.商户,2.代理商)
    public $content        = null;    // 反馈内容
    public $is_ready       = null;    // 是否已读（0:未读,1:已读)
    public $delete         = null;    // 0:未删除; 1:已删除
    public $lastmodtime    = null;    // 最后修改时间

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
        $this->feedback_id    = $cursor['feedback_id'];
        $this->feedback_phone = $cursor['feedback_phone'];
        $this->feedback_time  = $cursor['feedback_time'];
        $this->feedback_from  = $cursor['feedback_from'];
        $this->feedback_type  = $cursor['feedback_type'];
        $this->content        = $cursor['content'];
        $this->delete         = $cursor['delete'];
        $this->is_ready       = $cursor['is_ready'];
        $this->lastmodtime    = $cursor['lastmodtime'];

    }

    public static function ToList($cursor)
    {
        $lists = array();
        foreach($cursor as $item)
        {
            $entry = new self($item);
            array_push($lists, $entry);
        }
        return $lists;
    }
}

class UserFeedback
{
    private function Tablename()
    {
        return 'user_feedback';
    }

    public function Save(&$info)
    {   
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'feedback_id' => (string)$info->feedback_id
        );

        $set = array(
            "feedback_id"   => (string)$info->feedback_id,
        );

        if(null !== $info->feedback_phone)
        {
            $set["feedback_phone"] = (string)$info->feedback_phone;
        }
        
        if(null !== $info->feedback_time)
        {
            $set["feedback_time"] = (int)$info->feedback_time;
        }
        if(null !== $info->feedback_type)
        {
            $set["feedback_type"] = (int)$info->feedback_type;
        }
        if(null !== $info->feedback_from)
        {
            $set["feedback_from"] = (int)$info->feedback_from;
        }
        if(null !== $info->content)
        {
            $set["content"] = (string)$info->content;
        }
        if(null !== $info->is_ready)
        {
            $set["is_ready"] = (int)$info->is_ready;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        $set['lastmodtime'] = time();
        $value = array(
            '$set' => $set
        );
        try
        {
            $ret = $table->update($cond, $value, ['upsert'=>true]);
            LogDebug("ret:" . json_encode($ret));
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }

        return 0;
    }

    public function GetUserFeedbackList($filter=null, $page_size, $page_no, $sortby = [], &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1]
        ];
        //搜索功能
        if(null != $filter)
        {
            $feedback_type = $filter['feedback_type'];
            if( null != $feedback_type)
            {
                $cond['feedback_type'] = (int)$feedback_type;
            }
              $feedback_from = $filter['feedback_from'];
            if( null != $feedback_from)
            {
                $cond['feedback_from'] = (int)$feedback_from;
            }
            $begin_time = $filter['begin_time'];
            $end_time   = $filter['end_time'];
            if(!empty($begin_time))
            {
                $cond['feedback_time'] = [
                    '$gte' => (int)$begin_time,
                    '$lte' => (int)$end_time
                ];
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
        return UserFeedbackEntry::ToList($cursor);
    }
    //批量删除
    public function BatchDelete($feedback_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($feedback_id_list as $i => &$id)
        {
            $id = (string)$id;
        }

        $cond = array(
            'feedback_id' => array('$in' => $feedback_id_list)
        );
        $value = array(
            '$set'=>array(
                'delete'      => 1,
                'lastmodtime' => time(),
            )
        );
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple' => true]);
            LogDebug("ret:" . json_encode($ret));
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function GetFeedbackTotal(&$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1]
        ];
        $cursor = $table->find($cond);
        if (null !== $total)
        {
            $total = $table->count($cond);
        }
        return UserFeedbackEntry::ToList($cursor);
    }

    public function GetFeedbackReadyTotal($is_ready, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'is_ready' => (int)$is_ready,
            'delete'   => ['$ne'=>1]
        ];
        $cursor = $table->find($cond);
        if (null !== $total)
        {
            $total = $table->count($cond);
        }
        return UserFeedbackEntry::ToList($cursor);
    }
    //批量阅读
    public function BatchReady($feedback_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'feedback_id' => $feedback_id
        );
        $value = array(
            '$set'=>array(
                'is_ready'    => 1,
                'lastmodtime' => time(),
            )
        );
        try
        {
            $ret = $table->update($cond, $value, ['safe'=>true, 'upsert'=>true, 'multiple' => true]);
            LogDebug("ret:" . json_encode($ret));
        }
        catch(MongoCursorException $e)
        {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }
}

?>
