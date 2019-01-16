<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 店铺资源表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class ResourcesEntry extends BaseInfo
{
    public $resources_id     = null; // 资源id
    public $shop_id          = null; // 店铺id
    public $resources_type   = null; // 资源类型(1:智能收银机,2:自助点餐机,4:平板智能点餐机,5:掌柜通)3:PC这里用不上
    public $time_long        = null; // 授权时长
    public $valid_begin_time = null; // 有效开始时间
    public $valid_end_time   = null; // 有效结束时间
    public $last_use_time    = null; // 最后使用时间
    public $term_id          = null; // 正在使用终端id
    public $lastmodtime      = null; // 数据最后修改时间
    public $delete           = null; // 0:正常, 1:已删除
    public $time             = null; //

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
        $this->resources_id     = $cursor['resources_id'];
        $this->shop_id          = $cursor['shop_id'];
        $this->resources_type   = $cursor['resources_type'];
        $this->time_long        = $cursor['time_long'];
        $this->valid_begin_time = $cursor['valid_begin_time'];
        $this->valid_end_time   = $cursor['valid_end_time'];
        $this->last_use_time    = $cursor['last_use_time'];
        $this->term_id          = $cursor['term_id'];
        $this->lastmodtime      = $cursor['lastmodtime'];
        $this->delete           = $cursor['delete'];
        $this->ctime            = $cursor['ctime'];
    }
}
class Resources extends MgoBase
{
    protected function Tablename()
    {
        return 'resources';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'resources_id' => (string)$info->resources_id
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->resources_type)
        {
            $set["resources_type"] = (int)$info->resources_type;
        }
        if(null !== $info->time_long)
        {
            $set["time_long"] = (string)$info->time_long;
        }
        if(null !== $info->valid_begin_time)
        {
            $set["valid_begin_time"] = (int)$info->valid_begin_time;
        }
        if(null !== $info->valid_end_time)
        {
            $set["valid_end_time"] = (int)$info->valid_end_time;
        }
        if(null !== $info->last_use_time)
        {
            $set["last_use_time"] = (int)$info->last_use_time;
        }
        if(null !== $info->term_id)
        {
            $set["term_id"] = (string)$info->term_id;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->ctime)
        {
            $set["ctime"] = (int)$info->ctime;
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
        return new ResourcesEntry($cursor);
    }
    //获取列表数据
    public function GetList($filter=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' =>  ['$ne'=>1] ,
        ];
        if(null != $filter)
        {
            $time = time();
            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $resources_type = $filter['resources_type'];
            if(!empty($resources_type))
            {
                $cond['resources_type'] = (int)$resources_type;
            }
            $login = $filter['login'];
            if(!empty($login))
            {
                //$cond['last_use_time']    = ['$lte' => $time - 90];
                $cond['valid_begin_time'] = ['$lte' => $time];
                $cond['valid_end_time']   = ['$gte' => $time];
            }
        }
        if(empty($sortby)){
            $sortby['_id'] = -1;
        }

        $cursor = $table->find($cond)->sort($sortby);

        return ResourcesEntry::ToList($cursor);
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
        return new ResourcesEntry($ret);
    }


    public function QueryByToken($term_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'term_id'  => (string)$term_id,
            'delete'   => array('$ne'=>1)
        );
        $field["_id"] = 0;
        $cursor = $table->find($cond, $field)->sort(['last_use_time'=>-1])->limit(1);
        $list = ResourcesEntry::ToList($cursor);
        if(count($list) > 0)
        {
            return $list[0];
        }
        return null;
    }

    //获取列表数据
    public function GetResourcesList($filter=null, $page_size=10, $page_no=1, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' =>  ['$ne'=>1] ,
        ];
        if(null != $filter)
        {
            $shop_id_list = $filter['shop_id_list'];
            if(!empty($shop_id_list))
            {
                foreach($shop_id_list as $i => &$item)
                {
                   $item = (string)$item;
                }
                $cond['shop_id'] = ['$in' => $shop_id_list];
            }

            $resources_id = $filter['resources_id'];
            if (!empty($resources_id)) {
                $cond['resources_id'] = new \MongoRegex("/$resources_id/");
            }

            $resources_type = $filter['resources_type'];
            if (!empty($resources_type)) {
                $cond['resources_type'] = (int)$resources_type;
            }

            $shop_id = $filter['shop_id'];
            if (!empty($shop_id)) {
                $cond['shop_id'] = (string)$shop_id;
            }
            $end_time = $filter['end_time'];//过期时间
            if (!empty($end_time)) {
                $cond['valid_end_time'] = [
                    '$gte' => time(),
                    '$lte' => (int)$end_time
                ];
            }
            $begin_time = $filter['begin_time'];//永久
            if (!empty($begin_time)) {
                $cond['valid_begin_time'] = 0;
            }
            $overdue = $filter['overdue'];//过期
            if (!empty($overdue)) {
                $cond['valid_end_time'] = [
                    '$lt' => time()
                ];
            }

            $login_begin_time = $filter['login_begin_time'];
            $login_end_time   = $filter['login_end_time'];
            if(!empty($login_begin_time))
            {
                $cond['last_use_time'] = [
                    '$gte' => (int)$login_begin_time,
                    '$lte' => (int)$login_end_time
                ];
            }
        }
        //LogDebug($cond);
        $sortby['ctime'] = -1;

        $cursor = $table->find($cond)->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return ResourcesEntry::ToList($cursor);
    }


     //通过店铺id找出有效的资源数
    public function GetResourcesCount($shop_id, $resources_type)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());
        $cond = [
            'shop_id'        => (string)$shop_id,
            'resources_type' => (int)$resources_type,
            'valid_end_time' => ['$gte' => time()],
            'delete'         => ['$ne'=>1]
        ];
        $cursor = $table->count($cond);
        return $cursor;
    }

}






?>
