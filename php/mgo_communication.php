<?php
//留言表操作
//设置文字格式
declare(encoding='UTF-8');
//数据库命名空间
namespace DaoMongodb;
//加载数据文件
require_once ("db_pool.php");
require_once ("const.php");
//设置留言版的定义
class CommunicationEntry{
    //设置留言字段
    public $content_id = null; //留言id
    public $content    = null; //留言内容
    public $title      = null ;//留言标题
    public $c_name     = null; //留言人
    public $c_time     = null;//留言时间
    public $delete     =null ;//删除字段
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
    //数据显示列表
    public static function ToList($cursor){
        //设置变量来保存数组
        $list=array();

        //遍历出所有数据结果
        foreach ($cursor as $item){
            $entry= new self($item);
            array_push($list,$entry);
        }
        return $list;
    }
};
class Communication
{
    //数据库名
    private function Tablename()
    {
        return 'communication';
    }
    //保存数据设置
    public function Save(&$info){
        LogDebug($info);
        //连接数据库
        $db=\DbPool::GetMongoDb();
        LogDebug($db);//保存时候连接成功了
        //查询数据库连接
        $table = $db->selectCollection($this->Tablename());
        LogDebug($table);
        $cond = array(
            'content_id'=>(string)$info->content_id
        );
        LogDebug($cond);

        $set = array(
          'content_id'=>(string)$info->content_id,
            'c_time'=>time()
        );
        //定义字段样式
        if(null !== $info->content){
            $set['content']=(string)$info->content;
        }
        if(null !== $info->title){
            $set['title']=(string)$info->title;
        }
        if(null !== $info->c_name){
            $set['c_name']=(string)$info->c_name;
        }
        if(null !== $info->delete){
            $set['delete']=(int)$info->delete;
        }
        LogDebug($set);
        $value = array(
            '$set'=>$set
        );
        //var_dump($value);die;
        LogDebug($value);//保存的时候没有自动生成id
        //事物回滚
        try
        {

            $ret =$table->update($cond,$value,['safe'=>true,'upsert'=>true]);//upsert表示如果没有数据就自动添加一条数据
            LogDebug("ret:".$ret['ok']);//1
        }
        catch (\MongoCursorException $e){
            //提示错误信息
            LogDebug($e->getMessage());
            return \errcode::DB_OPR_ERR;

        }
        return 0;
    }
    //获取留言列表
    public function GetContentList(){
        $db   = \DbPool::GetMongoDb();
        $table= $db->selectCollection($this->Tablename());
        $cond = [
            'delete'=>1,
        ];
        //查找所有留言列表，并根据时间排序、
        LogDebug($cond);

        $cursor=$table->find($cond,array("_id"=>0))->sort(['c_time'=>-1]);

        //返回结果
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