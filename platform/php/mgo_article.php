<?php
/*
 * [Rocky 2017-05-12 19:39:55]
 * 消息表操作类
 */
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once("db_pool.php");
require_once("redis_id.php");



class ArticleEntry
{
    public $article_id    = null;    // 文章id
    public $title         = null;    // 标题
    public $content       = null;    // 内容
    public $platformer_id = null;    // 录入者平台员工id
    public $delete        = null;    // 0:未删除; 1:已删除
    public $lastmodtime   = null;    // 最后修改时间
    public $send_time     = null;    // 发送时间
    public $is_send       = null;    // 是否已发送（0:未发, 1:已发送）
    public $ctime         = null;    // 创建时间
    public $article_type  = null;    // 文章类型（0:未定,1:公司动态）
    public $article_state = null;    // 文章状态（0:正常,1:关闭）

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
        $this->article_id      = $cursor['article_id'];
        $this->platformer_id   = $cursor['platformer_id'];
        $this->title           = $cursor['title'];
        $this->content         = $cursor['content'];
        $this->delete          = $cursor['delete'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->send_time       = $cursor['send_time'];
        $this->is_send         = $cursor['is_send'];
        $this->ctime           = $cursor['ctime'];
        $this->article_type    = $cursor['article_type'];
        $this->article_state   = $cursor['article_state'];
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

class Article
{
    private function Tablename()
    {
        return 'article';
    }

    public function Save(&$info)
    {   
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'article_id' => (string)$info->article_id
        );

        $set = array(
            "article_id"   => (string)$info->article_id,
        );
        if(null !== $info->platformer_id)
        {
            $set["platformer_id"] = (string)$info->platformer_id;
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
        if(null !== $info->is_send)
        {
            $set["is_send"] = (int)$info->is_send;
        }
        if(null !== $info->ctime)
        {
            $set["ctime"] = (int)$info->ctime;
        }
        if(null !== $info->article_type)
        {
            $set["article_type"] = (int)$info->article_type;
        }
        if(null !== $info->article_state)
        {
            $set["article_state"] = (int)$info->article_state;
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
    public function BatchDelete($article_id_list)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        foreach($article_id_list as $i => &$id)
        {
            $id = (string)$id;
        }
        $cond = array(
            'article_id' => array('$in' => $article_id_list)
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

    //查找文章列表
    public function GetArticleList($filter=null, $sortby=[], $page_size, $page_no, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'delete'    => ['$ne'=>1],
        ];
        if (null != $filter)
        {
            $is_send = $filter['is_send'];
            if(null != $is_send)
            {
                $cond['is_send'] = (int)$is_send;
            }
            $platformer_id = $filter['platformer_id'];
            if (null != $platformer_id)
            {
                $cond['platformer_id'] = (string)$platformer_id;
            }
            $article_type = $filter['article_type'];
            if (null != $article_type) {
                $cond['article_type'] = (int)$article_type;
            }
            $article_state = $filter['article_state'];
            if (null != $article_state) {
                $cond['article_state'] = (int)$article_state;
            }
            $title = $filter['title'];
            if (null != $title) {
                $cond['title'] = new \MongoRegex("/$title/");
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
        return ArticleEntry::ToList($cursor);
    }

    // 关闭/启用
    public function BatchOpen($article_id, $article_state)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'article_id' => $article_id
        ];
        $set = array(
            "article_state"  => (int)$article_state,
            "lastmodtime"    => time(),
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

    public function GetArticleById($article_id)
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());;
        $cond = array(
            'article_id' => (string)$article_id
        );
        $cursor = $table->findOne($cond);
        return new ArticleEntry($cursor);
    }


}

?>
