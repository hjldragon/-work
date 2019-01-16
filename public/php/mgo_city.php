<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 城市录入及等级表作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class CityEntry extends BaseInfo
{
    //public $city_id         = null; // 城市id
    public $city_name       = null; // 城市名
    public $city_level      = null; // 城市等级(1.一线,2.二线,3.三线,)
    public $lastmodtime     = null; // 数据最后修改时间
    public $delete          = null; // 0:正常, 1:已删除

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
        //$this->city_id       = $cursor['city_id'];
        $this->city_name     = $cursor['city_name'];
        $this->city_level    = $cursor['city_level'];
        $this->lastmodtime   = $cursor['lastmodtime'];
        $this->delete        = $cursor['delete'];

    }
}
class City extends MgoBase
{
    protected function Tablename()
    {
        return 'city';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            //'city_id'   => (string)$info->city_id,
            'city_name' => (string)$info->city_name
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->city_level)
        {
            $set["city_level"] = (int)$info->city_level;
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
        return parent::DoBatchDelete($id_list, "id_list");
    }

    public function GetCityByName($city_name)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'city_name' => (string)$city_name,
            'delete'    => 0,
        ];
        $cursor = $table->findOne($cond);
        return new CityEntry($cursor);
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
            $city_level = $filter['city_level'];
            if(!empty($city_level))
            {
                $cond['city_level'] = (int)$city_level;
            }
        }
        if(empty($sortby)){
            $sortby['ctime'] = -1;
        }

        $cursor = $table->find($cond)->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return CityEntry::ToList($cursor);
    }

    public function QueryById($city_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'city_id'  => (string)$city_id,
            'delete'   => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        return new CityEntry($ret);
    }

}






?>
