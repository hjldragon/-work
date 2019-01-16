<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 平台表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class Picture extends BaseInfo
{
    public $img_name   = null; // 图片名
    public $link_url   = null; // 链接地址

    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    protected function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->img_name = $cursor["img_name"];
        $this->link_url = $cursor["link_url"];
    }
}

class Advertise extends BaseInfo
{
    public $banner_picture   = null; // banner图片
    public $hotgoods_picture = null; // 热卖商品
    public $hardware_adpic   = null; // 硬件广告
    public $hardware_picture = null; // 硬件商品
    public $software_adpic   = null; // 软件广告
    public $software_picture = null; // 软件商品
    public $consum_adpic     = null; // 耗材广告
    public $consum_picture   = null; // 耗材商品
    public $access_adpic     = null; // 配件广告
    public $access_picture   = null; // 配件商品

    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    protected function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->banner_picture   = Picture::ToList($cursor["banner_picture"]);
        $this->hotgoods_picture = Picture::ToList($cursor["hotgoods_picture"]);
        $this->hardware_adpic   = Picture::ToList($cursor["hardware_adpic"]);
        $this->hardware_picture = Picture::ToList($cursor["hardware_picture"]);
        $this->software_adpic   = Picture::ToList($cursor["software_adpic"]);
        $this->software_picture = Picture::ToList($cursor["software_picture"]);
        $this->consum_adpic     = Picture::ToList($cursor["consum_adpic"]);
        $this->consum_picture   = Picture::ToList($cursor["consum_picture"]);
        $this->access_adpic     = Picture::ToList($cursor["access_adpic"]);
        $this->access_picture   = Picture::ToList($cursor["access_picture"]);
    }
}

class PlatformEntry extends BaseInfo
{
    public $platform_id      = null; // 平台id
    public $platform_name    = null; // 平台名称
    public $pc_advertise     = null; // pc端广告图片
    public $phone_advertise  = null; // 手机端广告图片
    public $first_fee        = null; // 默认首费
    public $add_fee          = null; // 默认续费
    public $first_weight     = null; // 默认首重
    public $add_weight       = null; // 默认续重
    public $lastmodtime      = null; // 数据最后修改时间
    public $delete           = null; // 0:正常, 1:已删除
    public $ctime            = null; // 创建时间

    function __construct($cursor=null)
    {
        $this->FromMgo($cursor);
    }

    // mongodb查询结果转为结构体
    protected function FromMgo($cursor)
    {
        if(!$cursor)
        {
            return;
        }
        $this->platform_id     = $cursor["platform_id"];
        $this->platform_name   = $cursor["platform_name"];
        $this->pc_advertise    = new Advertise($cursor["pc_advertise"]);
        $this->phone_advertise = new Advertise($cursor["phone_advertise"]);
        $this->first_fee       = $cursor["first_fee"];
        $this->add_fee         = $cursor["add_fee"];
        $this->first_weight    = $cursor["first_weight"];
        $this->add_weight      = $cursor["add_weight"];
        $this->lastmodtime     = $cursor["lastmodtime"];
        $this->delete          = $cursor["delete"];
        $this->ctime           = $cursor["ctime"];
    }
}

class Platform extends MgoBase
{
    protected function Tablename()
    {
        return 'platform';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'platform_id' => (string)$info->platform_id
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->platform_name)
        {
            $set["platform_name"] = (string)$info->platform_name;
        }

        if(null !== $info->pc_advertise)
        {
           $set['pc_advertise'] = $this->advertise($info->pc_advertise);
        }
        if(null !== $info->phone_advertise)
        {
           $set['phone_advertise'] = $this->advertise($info->phone_advertise);
        }
        if(null !== $info->first_fee)
        {
            $set["first_fee"] = (float)$info->first_fee;
        }
        if(null !== $info->add_fee)
        {
            $set["add_fee"] = (float)$info->add_fee;
        }
        if(null !== $info->first_weight)
        {
            $set["first_weight"] = (float)$info->first_weight;
        }
        if(null !== $info->add_weight)
        {
            $set["add_weight"] = (float)$info->add_weight;
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

    protected function advertise($info)
    {
        $list = (object)[];
        if(null !== $info->banner_picture)
        {
            $img_list = [];
            foreach($info->banner_picture as $i => $item)
            {
                array_push($img_list, new Picture([
                    'img_name' => (string)$item->img_name,
                    'link_url' => (string)$item->link_url
                ]));
            }
            $list->banner_picture = $img_list;
        }

        if(null !== $info->hotgoods_picture)
        {
            $img_list = [];
            foreach($info->hotgoods_picture as $i => $item)
            {
                array_push($img_list, new Picture([
                    'img_name' => (string)$item->img_name,
                    'link_url' => (string)$item->link_url
                ]));
            }
            $list->hotgoods_picture = $img_list;
        }

        if(null !== $info->hardware_adpic)
        {
            $img_list = [];
            foreach($info->hardware_adpic as $i => $item)
            {
                array_push($img_list, new Picture([
                    'img_name' => (string)$item->img_name,
                    'link_url' => (string)$item->link_url
                ]));
            }
            $list->hardware_adpic = $img_list;
        }

        if(null !== $info->hardware_picture)
        {
            $img_list = [];
            foreach($info->hardware_picture as $i => $item)
            {
                array_push($img_list, new Picture([
                    'img_name' => (string)$item->img_name,
                    'link_url' => (string)$item->link_url
                ]));
            }
            $list->hardware_picture = $img_list;
        }
        if(null !== $info->software_adpic)
        {
            $img_list = [];
            foreach($info->software_adpic as $i => $item)
            {
                array_push($img_list, new Picture([
                    'img_name' => (string)$item->img_name,
                    'link_url' => (string)$item->link_url
                ]));
            }
            $list->software_adpic = $img_list;
        }
        if(null !== $info->software_picture)
        {
            $img_list = [];
            foreach($info->software_picture as $i => $item)
            {
                array_push($img_list, new Picture([
                    'img_name' => (string)$item->img_name,
                    'link_url' => (string)$item->link_url
                ]));
            }
            $list->software_picture = $img_list;
        }
        if(null !== $info->consum_adpic)
        {
            $img_list = [];
            foreach($info->consum_adpic as $i => $item)
            {
                array_push($img_list, new Picture([
                    'img_name' => (string)$item->img_name,
                    'link_url' => (string)$item->link_url
                ]));
            }
            $list->consum_adpic = $img_list;
        }
        if(null !== $info->consum_picture)
        {
            $img_list = [];
            foreach($info->consum_picture as $i => $item)
            {
                array_push($img_list, new Picture([
                    'img_name' => (string)$item->img_name,
                    'link_url' => (string)$item->link_url
                ]));
            }
            $list->consum_picture = $img_list;
        }
        if(null !== $info->access_adpic)
        {
            $img_list = [];
            foreach($info->access_adpic as $i => $item)
            {
                array_push($img_list, new Picture([
                    'img_name' => (string)$item->img_name,
                    'link_url' => (string)$item->link_url
                ]));
            }
            $list->access_adpic = $img_list;
        }
        if(null !== $info->access_picture)
        {
            $img_list = [];
            foreach($info->access_picture as $i => $item)
            {
                array_push($img_list, new Picture([
                    'img_name' => (string)$item->img_name,
                    'link_url' => (string)$item->link_url
                ]));
            }
            $list->access_picture = $img_list;
        }
        return $list;
    }

    public function BatchDelete($id_list)
    {
        return parent::DoBatchDelete($id_list, "platform_id");
    }

    public function GetPlatformById($platform_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$platform_id, "platform_id");
        return PlatformEntry::ToObj($cursor);
    }



}



?>