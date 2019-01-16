<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 代理商的各种设置
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class AgentCfgEntry extends BaseInfo
{

    public $id                = null; // 唯一主键id;<<<<<<<<<<<<<<测试产品需求
    public $agent_level       = null; // 代理级别(1:一级，2:二级，3:三级)
    public $agent_type        = null; // 代理类型(1:区域，2:行业)
    public $uplevel_money     = null; // 升级代理商登记的钱//万元为单位
    public $software_rebates  = null; // 软件折扣率
    public $hardware_rebates  = null; // 硬件折扣率
    public $supplies_rebates  = null; // 耗材折扣率
    public $banner            = null; // 广告图片
    public $url               = null; // 图片地址
    public $lastmodtime       = null; // 数据最后修改时间
    public $delete            = null; // 0:正常, 1:已删除

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
        $this->id                = $cursor['id'];
        $this->agent_level       = $cursor['agent_level'];
        $this->agent_type        = $cursor['agent_type'];
        $this->uplevel_money     = $cursor['uplevel_money'];
        $this->software_rebates  = $cursor['software_rebates'];
        $this->hardware_rebates  = $cursor['hardware_rebates'];
        $this->supplies_rebates  = $cursor['supplies_rebates'];
        $this->banner            = $cursor['banner'];
        $this->url               = $cursor['url'];
        $this->lastmodtime       = $cursor['lastmodtime'];
        $this->delete            = $cursor['delete'];

    }
}
class AgentCfg extends MgoBase
{
    protected function Tablename()
    {
        return 'agent_cfg';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
              'id'            => (string)$info->id,
//            'agent_type'    => (int)$info->agent_type,
//            'agent_level'   => (int)$info->agent_level,
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );
        if(null !== $info->agent_type)
        {
            $set["agent_type"] = (int)$info->agent_type;
        }
        if(null !== $info->agent_level)
        {
            $set["agent_level"] = (int)$info->agent_level;
        }
        if(null !== $info->uplevel_money)
        {
            $set["uplevel_money"] = (float)$info->uplevel_money;
        }
        if(null !== $info->software_rebates)
        {
            $set["software_rebates"] = (float)$info->software_rebates;
        }
        if(null !== $info->hardware_rebates)
        {
            $set["hardware_rebates"] = (float)$info->hardware_rebates;
        }
        if(null !== $info->supplies_rebates)
        {
            $set["supplies_rebates"] = (float)$info->supplies_rebates;
        }
        if(null !== $info->banner)
        {
            $set["banner"] = (string)$info->banner;
        }
        if(null !== $info->url)
        {
            $set["url"] = (string)$info->url;
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

    public function GetInfoByLevel($agent_type, $agent_level)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'agent_type'  => (int)$agent_type,
            'agent_level' => (int)$agent_level,
            'delete'      => ['$ne'=>1],
        ];
        $cursor = $table->findOne($cond);
        return new AgentCfgEntry($cursor);
    }
    public function GetInfoById($id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'id'     => (string)$id,
            'delete' => ['$ne'=>1],
        ];
        $cursor = $table->findOne($cond);
        return new AgentCfgEntry($cursor);
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
            $agent_type = $filter['agent_type'];
            if(!empty($agent_type))
            {
                $cond['agent_type'] = (int)$agent_type;
            }
            $agent_level = $filter['agent_level'];
            if(!empty($agent_level))
            {
                $cond['agent_level'] = (int)$agent_level;
            }
        }
        if(empty($sortby)){
            $sortby['_id'] = -1;
        }

        $cursor = $table->find($cond)->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return AgentCfgEntry::ToList($cursor);
    }

    public function DeleteByBanner($agent_type, $agent_level)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'agent_type'  => (int)$agent_type,
            'agent_level' => (int)$agent_level,
        ];

        $value = array(
            '$set' => array(
                'banner'      => '',
                "lastmodtime" => time()
            )
        );
        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true, 'multiple' => true]);
            LogDebug("ret:" . $ret["ok"]);
        } catch (\MongoCursorException $e) {
            LogErr($e->getMessage());
            return \errcode::DB_OPR_ERR;
        }
        return 0;
    }

    public function GetListCityLevel($agent_type)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'agent_type'  => (int)$agent_type,
            'delete'      => ['$ne'=>1],
        ];
        $cursor = $table->find($cond);
        return AgentCfgEntry::ToList($cursor);
    }

}






?>
