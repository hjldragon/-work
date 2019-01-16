<?php
//留言表
declare(encoding='UTF-8');
namespace DaoMongodb;
require_once ("/www/shop.sailing.com/php/db_pool.php");
require_once ("const.php");
class CommunicationEntry{
    //设置留言字段
    public $content_id = null; //留言id
    public $content    = null; //留言内容
    public $title      = null ;//留言标题
    public $c_name     = null; //留言人
    public $c_time     = null;//留言时间
    public $delete     = null ;//删除字段
    //私有构造函数
    function  __construct($cursor=null)
    {
        LogDebug($cursor);
        $this->FromMgo($cursor);
    }
    //命名查询结果转为结构体的方法
    public  function FromMgo($cursor){
        LogDebug($cursor);
        //如果没有这个变量就直接返回
        if(!$cursor)
        {
            return;
        }
        //设置留言板块所有字段并保存到$cursor中
        $this->content_id = $cursor['content_id'];
        $this->content    = $cursor['content'];
        $this->title      = $cursor['title'];
        $this->c_name     = $cursor['c_name'];
        $this->c_time     = $cursor['c_time'];
        $this->delete     = $cursor['delete'];

    }
    public static function ToList($cursor){
        $list = array();
        foreach ($cursor as $item){
            $entry = new self($item);
            array_push($list,$entry);
        }
        return $list;
    }
};
class Communication
{

    private function Tablename()
    {
        return 'communication';
    }

    public function Save(&$info){
        LogDebug($info);
        $db=\DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = array(
            'content_id' => (string)$info->content_id
        );
        LogDebug($cond);
        $set = array(
          'content_id' => (string)$info->content_id,
            'c_time' => time()
        );
        if(null !== $info->content){
            $set['content'] = (string)$info->content;
        }
        if(null !== $info->title){
            $set['title'] = (string)$info->title;
        }
        if(null !== $info->c_name){
            $set['c_name'] = (string)$info->c_name;
        }
        if(null !== $info->delete){
            $set['delete'] = (int)$info->delete;
        }
        LogDebug($set);
        $value = array(
            '$set' => $set
        );
        try
        {

            $ret =$table->update($cond,$value,['safe'=>true,'upsert'=>true]);//upsert表示如果没有数据就自动添加一条数据
            LogDebug("ret:".$ret['ok']);//1
        }
        catch (\MongoCursorException $e){
            LogDebug($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }
    public function GetContentList(){
        $db   = \DbPool::GetMongoDb();
        $table= $db->selectCollection($this->Tablename());
        $cond = [
            'delete'=>1,
        ];
        $cursor=$table->find($cond,array("_id"=>0))->sort(['c_time'=>-1]);
        return CommunicationEntry::ToList($cursor);

    }
    //查询id条件的留言
    public function QueryById($content_id){
            $db=\DbPool::GetMongoDb();
            $table =$db->selectCollection($this->Tablename());
            $cond=array(
                'content_id'=>(string)$content_id
            );
            $cursor= $table->findOne($cond);
            return new CommunicationEntry($cursor);
    }
}