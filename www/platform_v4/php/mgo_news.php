<?php
/*
 * [Rocky 2017-05-12 19:39:55]
 * 消息表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("redis_id.php");



class NewsEntry
{
    public $news_id           = null;    // 消息id
    public $shop_id           = null;    // 店铺id
    public $title             = null;    // 标题
    public $content           = null;    // 内容
    public $delete            = null;    // 0:未删除; 1:已删除
    public $lastmodtime       = null;    // 最后修改时间
    public $send_time         = null;    // 发送时间
    public $ctime             = null;    // 创建时间
    public $is_system         = null;    // 是否是平台消息（0:不是, 1:是）
    public $send_type         = null;    // 发送对象（0:未定,1:全部,2:商户,3:代理商）
    public $platfomer_id      = null;    // 发送人ID
    public $business_status   = null;    // 发送对象工商认证状态(0:未认证,1:待认证,2:认证成功,3:认证失败,4:全部状态)
    //public $is_send           = null;    // 是否已发送（0:未发, 1:已发送）
    //public $is_draft          = null;    // 是否草稿（0:不是, 1:是）

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
        $this->news_id           = $cursor['news_id'];
        $this->shop_id           = $cursor['shop_id'];
        $this->title             = $cursor['title'];
        $this->content           = $cursor['content'];
        $this->delete            = $cursor['delete'];
        $this->lastmodtime       = $cursor['lastmodtime'];
        $this->send_time         = $cursor['send_time'];
        $this->ctime             = $cursor['ctime'];
        $this->is_system         = $cursor['is_system'];
        $this->send_type         = $cursor['send_type'];
        $this->platfomer_id      = $cursor['platfomer_id'];
        $this->business_status   = $cursor['business_status'];
        //$this->is_send           = $cursor['is_send'];
        //$this->is_draft          = $cursor['is_draft'];
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

class News
{
    private function Tablename()
    {
        return 'news';
    }

    public function Save(&$info)
    {   
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'news_id' => (string)$info->news_id
        );

        $set = array(
            "news_id"   => (string)$info->news_id,
        );

        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        
        if(null !== $info->title)
        {
            $set["title"] = (string)$info->title;
        }

        if(null !== $info->content)
        {
            $set["content"] = (string)$info->content;
        }

        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->send_time)
        {
            $set["send_time"] = (int)$info->send_time;
        }
        if(null !== $info->ctime)
        {
            $set["ctime"] = (int)$info->ctime;
        }
        if(null !== $info->is_system)
        {
            $set["is_system"] = (int)$info->is_system;
        }
        if(null !== $info->send_type)
        {
            $set["send_type"] = (int)$info->send_type;
        }
        if(null !== $info->platfomer_id)
        {
            $set["platfomer_id"] = (string)$info->platfomer_id;
        }
        if(null !== $info->business_status)
        {
            $set["business_status"] = (int)$info->business_status;
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

    //批量删除
    public function BatchDelete($news_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($news_id_list as $i => &$id)
        {
            $id = (string)$id;
        }

        $cond = array(
            'news_id' => array('$in' => $news_id_list)
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

    //查找单条消息
    public function GetNewsById($news_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'news_id' => (string)$news_id,
            'delete'  => ['$ne'=>1],
        );
        $field["_id"] = 0;
        $cursor = $table->findOne($cond, $field);
        return new NewsEntry($cursor);
    }

    //查找待发送消息
    public function GetNewsBySendTime($time)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'delete'    => ['$ne'=>1],
            'is_draft'  => ['$ne'=>1],
            'is_send'   => ['$ne'=>1],
            'is_system' => ['$ne'=>1],
            'send_time' => $time
        );
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort($sortby);
        return NewsEntry::ToList($cursor);
    }

    //查找消息列表
    public function GetnNewsList($filter=null, $sortby=[], $page_size, $page_no, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'delete'    => ['$ne'=>1],
        ];
        if (null != $filter)
        {   
            $is_draft = $filter['is_draft'];
            if(null != $is_draft)
            {
                $cond['is_draft'] = (int)$is_draft;
            }
            $shop_id = $filter['shop_id'];
            if (null != $shop_id)
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $is_send = $filter['is_send'];
            if (null != $is_send) {
                $cond['is_send'] = (int)$is_send;
            }
            $platfomer_id = $filter['platfomer_id'];
            if (null != $platfomer_id) {
                $cond['platfomer_id'] = (string)$platfomer_id;
            }
            $send_type = $filter['send_type'];
            if (null != $send_type) {
                $cond['send_type'] = (int)$send_type;
            }
            $is_system = $filter['is_system'];
            if (null != $is_system) {
                $cond['is_system'] = (int)$is_system;
            }
            $business_status = $filter['business_status'];
            if (null != $business_status) {
                $cond['business_status'] = (int)$business_status;
            }
            $begin_time = $filter['begin_time'];
            $end_time   = $filter['end_time'];
            if(!empty($begin_time))
            {
                $cond['send_time'] = [
                    '$gte' => (int)$begin_time,
                    '$lte' => (int)$end_time
                ];
            }
        }
        if(empty($sortby))
        {
            $sortby['ctime'] = -1;
        }
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort($sortby)->skip(($page_no - 1) * $page_size)->limit($page_size);
        if (null !== $total)
        {
            $total = $table->count($cond);
        }
        return NewsEntry::ToList($cursor);
    }


}



class NewsReadyEntry
{   
    public $id          = null;    // id
    public $news_id     = null;    // 消息id
    public $shop_id     = null;    // 发送或接受消息店铺id
    public $customer_id = null;    // 消息接受顾客id
    public $agent_id    = null;    // 代理商id
    public $ctime       = null;    // 创建时间（即接受时间）
    public $is_system   = null;    // 是否是系统消息（0:不是, 1:是）
    public $is_ready    = null;    // 是否已读（0:未读, 1:已读）
    public $delete      = null;    // 

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
        $this->id          = $cursor['id'];
        $this->news_id     = $cursor['news_id'];
        $this->shop_id     = $cursor['shop_id'];
        $this->customer_id = $cursor['customer_id'];
        $this->agent_id    = $cursor['agent_id'];
        $this->delete      = $cursor['delete'];
        $this->is_ready    = $cursor['is_ready'];
        $this->ctime       = $cursor['ctime'];
        $this->is_system   = $cursor['is_system'];
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

class NewsReady
{
    private function Tablename()
    {
        return 'newsready';
    }

    public function Save(&$info)
    {   
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'id' => (string)$info->id
        );

        $set = array(
            "id"   => (string)$info->id,
        );

        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        
        if(null !== $info->news_id)
        {
            $set["news_id"] = (string)$info->news_id;
        }

        if(null !== $info->customer_id)
        {
            $set["customer_id"] = (string)$info->customer_id;
        }
        
        if(null !== $info->is_ready)
        {
            $set["is_ready"] = (int)$info->is_ready;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->ctime)
        {
            $set["ctime"] = (int)$info->ctime;
        }
        if(null !== $info->is_system)
        {
            $set["is_system"] = (int)$info->is_system;
        }
        if(null !== $info->agent_id)
        {
            $set["agent_id"] = (string)$info->agent_id;
        }
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
    
    public function GetNewsReadyById($id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'id' => (string)$id,
            'delete'  => ['$ne'=>1],
        );
        $field["_id"] = 0;
        $cursor = $table->findOne($cond, $field);
        return new NewsReadyEntry($cursor);
    }

    public function GetNewsReadyByShopId($shop_id,$page_no,$page_size,&$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'shop_id'   => (string)$shop_id,
            'is_system' => 1,
            'delete'    => ['$ne'=>1],
            'ctime'     => ['$lte'=>time()]
        );
        $field["_id"] = 0;
        $sortby['ctime'] = -1;
        $cursor = $table->find($cond, $field)->sort($sortby)->skip(($page_no - 1) * $page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return NewsReadyEntry::ToList($cursor);
    }

    public function GetnNewsReadyList($filter=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'delete'  => ['$ne'=>1],
            'ctime'   => ['$lte'=>time()]
        ];
        if (null != $filter)
        {
            $shop_id = $filter['shop_id'];
            if (null !== $shop_id)
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $is_draft = $filter['is_draft'];
            if (null !== $is_draft)
            {
                $cond['is_draft'] = (string)$is_draft;
            }
            $is_send = $filter['is_send'];
            if (null != $is_send) {
                $cond['is_send'] = (int)$is_send;
            }
        }
        $sortby['ctime'] = -1;
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort($sortby);
        return NewsReadyEntry::ToList($cursor);
    }

    //标记已读
    public function ReadyNews($shop_id, $news_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

       
        $cond = array(
            'shop_id' => $shop_id,
            'news_id' => $news_id
        );
        $value = array(
            '$set'=>array(
                'is_ready'  => 1
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

    //批量标记已读/删除
    public function BatchReady($id_list, $type=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        foreach($id_list as $i => &$id)
        {
            $id = (string)$id;
        }
        $cond = array(
            'id' => array('$in' => $id_list)
        );
        if($type){
            $value = array(
                '$set'=>array(
                    'delete'  => 1
                )
            );
        }else{
            $value = array(
                '$set'=>array(
                    'is_ready'  => 1
                )
            );
        }
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
    //批量删除
    public function BatchDeleteByNewsId($id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($id_list as $i => &$id)
        {
            $id = (string)$id;
        }

        $cond = array(
            'news_id' => array('$in' => $id_list)
        );
        
        $value = array(
            '$set'=>array(
                'delete'  => 1
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
    //代理商标记已读/删除
    public function AgentReadyNews($agent_id, $news_id_list, $type=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($news_id_list as $i => &$id)
        {
            $id =(string)$id;
        }
        $cond = array(
            'news_id'  => array('$in' => $news_id_list),
            'agent_id' => $agent_id
        );
        if($type){
            $value = array(
                '$set'=>array(
                    'delete'  => 1
                )
            );
        }else {
            $value = [
                '$set' => [
                    'is_ready' => 1
                ]
            ];
        }
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

    public function GetNewsReadyByNewsId($agent_id, $news_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'agent_id' =>(string)$agent_id,
            'news_id'  => (string)$news_id,
            'delete'   => ['$ne'=>1],
        );
        $field["_id"] = 0;
        $cursor = $table->findOne($cond, $field);
        return new NewsReadyEntry($cursor);
    }

    //查找代理商系统公告列表
    //查找消息列表
    public function GetAgentNewsList($filter=null, $sortby=[], $page_size, $page_no, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'delete'    => ['$ne'=>1],
            'is_system' => 1
        ];
        if (null != $filter)
        {
            $agent_id = $filter['agent_id'];
            if(null != $agent_id)
            {
                $cond['agent_id'] = (string)$agent_id;
            }
            $begin_time = $filter['begin_time'];
            $end_time   = $filter['end_time'];
            if(!empty($begin_time))
            {
                $cond['ctime'] = [
                    '$gte' => (int)$begin_time,
                    '$lte' => (int)$end_time
                ];
            }
        }
        if(empty($sortby))
        {
            $sortby['ctime'] = -1;
        }
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort($sortby)->skip(($page_no - 1) * $page_size)->limit($page_size);
        if (null !== $total)
        {
            $total = $table->count($cond);
        }
        return NewsReadyEntry::ToList($cursor);
    }

}


?>
