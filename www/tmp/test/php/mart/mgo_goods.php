<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 商品表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class GoodsDescribe extends BaseInfo
{
    public $name               = null;     //商品名称
    public $spec               = null;     //商品规格
    public $material           = null;     //商品材质
    public $weight             = null;     //商品重量
    public $model              = null;     //商品型号
    public $brand              = null;     //品牌
    public $screen             = null;     //显示屏
    public $screen_size        = null;     //屏幕尺寸
    public $screen_num         = null;     //屏幕数量
    public $touch_type         = null;     //触摸类型
    public $cpu                = null;     //处理器
    public $system             = null;     //操作系统
    public $ram                = null;     //内存空间（RAM)
    public $rom                = null;     //存储空间（ROM）
    public $printer            = null;     //打印机
    public $power              = null;     //电源适配器
    public $horn               = null;     //喇叭
    public $bluetooth          = null;     //蓝牙
    public $key                = null;     //按键
    public $external_interface = null;     //外部接口
    public $work_temp          = null;     //外部温度
    public $standard           = null;     //商品标准
    public $pack               = null;     //包装清单
    public $software_name      = null;     //虚拟商品名称
    public $software_version   = null;     //虚拟商品适用系统
    public $software_system    = null;     //虚拟商品适用系统


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
        $this->name               = $cursor["name"];
        $this->spec               = $cursor["spec"];
        $this->material           = $cursor["material"];
        $this->weight             = $cursor["weight"];
        $this->model              = $cursor["model"];
        $this->brand              = $cursor["brand"];
        $this->screen             = $cursor["screen"];
        $this->screen_size        = $cursor["screen_size"];
        $this->screen_num         = $cursor["screen_num"];
        $this->touch_type         = $cursor["touch_type"];
        $this->cpu                = $cursor["cpu"];
        $this->system             = $cursor["system"];
        $this->ram                = $cursor["ram"];
        $this->rom                = $cursor["rom"];
        $this->printer            = $cursor["printer"];
        $this->power              = $cursor["power"];
        $this->horn               = $cursor["horn"];
        $this->bluetooth          = $cursor["bluetooth"];
        $this->key                = $cursor["key"];
        $this->external_interface = $cursor["external_interface"];
        $this->work_temp          = $cursor["work_temp"];
        $this->standard           = $cursor["standard"];
        $this->pack               = $cursor["pack"];
        $this->software_name      = $cursor["software_name"];
        $this->software_version   = $cursor["software_version"];
        $this->software_system    = $cursor["software_system"];
    }
}



class GoodsEntry extends BaseInfo
{
    public $goods_id        = null;      // 商品id
    public $goods_name      = null;      // 商品名称
    public $goods_type      = null;      // 1：实体商品，2：虚拟商品
    public $sale_off        = null;      // 0上架 1下架
    public $is_draft        = null;      // 0,正式 1,草稿
    public $is_recycle      = null;      // 0,正式 1,回收站
    public $category_id     = null;      // 三级分类ID
    public $goods_img_list  = null;      // 图片
    public $spec_type       = null;      // 1:规格（颜色） 2：套餐 3：规格（颜色）和套餐 4：授权端;5：服务年限
    public $first_sale_num  = null;      // 起售数量
    public $sale_off_way    = null;      // 上架方式0 不设置 1自定义
    public $goods_sale_time = null;      // (int)时间戳，自定义时间，上架方式选择自定义是上传
    public $freight_type    = null;      // 1:免运费 2：按重量 3：按件
    public $freight         = null;      // 重量时是重量， 按件时是一件收费
    public $invoice         = null;      // 1：不提供发票  2: 提供发票
    public $goods_describe  = null;      // 商品的一些描述，不填则不传
    public $desc_img_pc     = null;      // pc图文描述
    public $desc_img_phone  = null;      // phone图文描述
    public $spec_id_list    = null;      // 所含规格id
    public $cate_type       = null;      // 类别属性(1:硬件,2:耗材,3:软件)
    public $is_hot          = null;      // 是否热卖(0否 1是)
    public $lastmodtime     = null;      // 最后修改时间
    public $delete          = null;      //
    public $ctime           = null;      // 创建时间

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
        $this->goods_id        = $cursor["goods_id"];
        $this->goods_name      = $cursor["goods_name"];
        $this->goods_type      = $cursor["goods_type"];
        $this->sale_off        = $cursor["sale_off"];
        $this->is_draft        = $cursor["is_draft"];
        $this->is_recycle      = $cursor["is_recycle"];
        $this->category_id     = $cursor["category_id"];
        $this->goods_img_list  = $cursor["goods_img_list"];
        $this->spec_type       = $cursor["spec_type"];
        $this->first_sale_num  = $cursor["first_sale_num"];
        $this->sale_off_way    = $cursor["sale_off_way"];
        $this->goods_sale_time = $cursor["goods_sale_time"];
        $this->freight_type    = $cursor["freight_type"];
        $this->freight         = $cursor["freight"];
        $this->invoice         = $cursor["invoice"];
        $this->goods_describe  = new GoodsDescribe($cursor["goods_describe"]);
        $this->desc_img_pc     = $cursor["desc_img_pc"];
        $this->desc_img_phone  = $cursor["desc_img_phone"];
        $this->spec_id_list    = $cursor["spec_id_list"];
        $this->cate_type       = $cursor['cate_type'];
        $this->is_hot          = $cursor['is_hot'];
        $this->lastmodtime     = $cursor["lastmodtime"];
        $this->delete          = $cursor["delete"];
        $this->ctime           = $cursor["ctime"];
    }
}

class Goods extends MgoBase
{
    protected function Tablename()
    {
        return 'goods';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'goods_id' => (string)$info->goods_id
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->goods_type)
        {
            $set["goods_type"] = (int)$info->goods_type;
        }

        if(null !== $info->goods_name)
        {
            $set["goods_name"] = (string)$info->goods_name;
        }

        if(null !== $info->sale_off)
        {
            $set["sale_off"] = (int)$info->sale_off;
        }

        if(null !== $info->is_draft)
        {
            $set["is_draft"] = (int)$info->is_draft;
        }

        if(null !== $info->is_recycle)
        {
            $set["is_recycle"] = (int)$info->is_recycle;
        }

        if(null !== $info->category_id)
        {
            $set["category_id"] = $info->category_id;
        }

        if(null !== $info->goods_img_list)
        {
               $set["goods_img_list"] = $info->goods_img_list;
        }

        if(null !== $info->spec_type)
        {
            $set["spec_type"] = (int)$info->spec_type;
        }
        if(null !== $info->first_sale_num)
        {
            $set["first_sale_num"] = (int)$info->first_sale_num;
        }
        if(null !== $info->sale_off_way)
        {
            $set["sale_off_way"] = (int)$info->sale_off_way;
        }
        if(null !== $info->goods_sale_time)
        {
            $set["goods_sale_time"] = (int)$info->goods_sale_time;
        }
        if(null !== $info->freight_type)
        {
            $set["freight_type"] = (int)$info->freight_type;
        }
        if(null !== $info->freight)
        {
            $set["freight"] = (float)$info->freight;
        }
        if(null !== $info->invoice)
        {
            $set["invoice"] = (int)$info->invoice;
        }
        if(null !== $info->goods_describe)
        {
            $p = new GoodsDescribe();
            $p->name               = (string)$info->goods_describe->name;
            $p->spec               = (string)$info->goods_describe->spec;
            $p->material           = (string)$info->goods_describe->material;
            $p->weight             = (string)$info->goods_describe->weight;
            $p->model              = (string)$info->goods_describe->model;
            $p->brand              = (string)$info->goods_describe->brand;
            $p->screen             = (string)$info->goods_describe->screen;
            $p->screen_size        = (string)$info->goods_describe->screen_size;
            $p->screen_num         = (string)$info->goods_describe->screen_num;
            $p->touch_type         = (string)$info->goods_describe->touch_type;
            $p->cpu                = (string)$info->goods_describe->cpu;
            $p->system             = (string)$info->goods_describe->system;
            $p->ram                = (string)$info->goods_describe->ram;
            $p->rom                = (string)$info->goods_describe->rom;
            $p->printer            = (string)$info->goods_describe->printer;
            $p->power              = (string)$info->goods_describe->power;
            $p->horn               = (string)$info->goods_describe->horn;
            $p->bluetooth          = (string)$info->goods_describe->bluetooth;
            $p->key                = (string)$info->goods_describe->key;
            $p->external_interface = (string)$info->goods_describe->external_interface;
            $p->work_temp          = (string)$info->goods_describe->work_temp;
            $p->standard           = (string)$info->goods_describe->standard;
            $p->pack               = (string)$info->goods_describe->pack;
            $p->software_name      = (string)$info->goods_describe->software_name;
            $p->software_version   = (string)$info->goods_describe->software_version;
            $p->software_system    = (string)$info->goods_describe->software_system;
            $set["goods_describe"]  = $p;
        }
        if(null !== $info->desc_img_pc)
        {
            $set["desc_img_pc"] = $info->desc_img_pc;
        }
        if(null !== $info->desc_img_phone)
        {
            $set["desc_img_phone"] = $info->desc_img_phone;
        }
        if(null !== $info->spec_id_list)
        {
            $set["spec_id_list"] = $info->spec_id_list;
        }
        if (null !== $info->cate_type) {
            $set["cate_type"] = (int)$info->cate_type;
        }
        if (null !== $info->is_hot) {
            $set["is_hot"] = (int)$info->is_hot;
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
        return parent::DoBatchDelete($id_list, "goods_id");
    }

    // public function GetExampleById($goods_id)
    // {
    //     $db = \DbPool::GetMongoDb();
    //     $table = $db->selectCollection($this->Tablename());

    //     $cond = [
    //         'goods_id' => (string)$goods_id,
    //         'delete'      => 0,
    //     ];
    //     $cursor = $table->findOne($cond);
    //     return new GoodsEntry($cursor);
    // }
    public function GetGoodsById($goods_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$goods_id, "goods_id");
        return GoodsEntry::ToObj($cursor);
    }

    public function GetGoodsList($filter = null, &$total=null, $sortby = [])
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'     => ['$ne' => 1],
            'is_draft'   => ['$ne' => 1],
            'is_recycle' => ['$ne' => 1]
        ];
        if (null != $filter) {
            $foods_id = $filter['goods_id'];
            if (!empty($goods_id)) {
                $cond['goods_id'] = (string)$goods_id;
            } else {
                $goods_id_list = $filter['goods_id_list'];
                if(!empty($goods_id_list))
                {
                    foreach($goods_id_list as $i => &$item)
                    {
                        $item = (string)$item;
                    }
                    $cond["goods_id"] = ['$in' => $goods_id_list];
                }
                $category_id = $filter['category_id'];
                if (!empty($category_id)) {
                    $cond['category_id'] = (string)$category_id;
                }

                $sale_off = $filter['sale_off'];
                if (null !== $sale_off) {
                    $sale_off = (int)$sale_off;
                    if (0 == $sale_off) {
                        $cond['sale_off'] = ['$ne' => 1]; // ==0 或 ==null
                    } else {
                        $cond['sale_off'] = 1;
                    }
                }

                $goods_name = $filter['goods_name'];
                if (!empty($goods_name)) {
                    $cond['$or'] = [
                        ["goods_name" => new \MongoRegex("/$goods_name/i")],
                        ["goods_id" => new \MongoRegex("/$goods_name/i")]
                    ];
                }
                // 草稿
                $is_draft = $filter['is_draft'];
                if (!empty($is_draft)) {
                    $cond['is_draft'] = (int)$is_draft;
                }
                // 草稿
                $is_recycle = $filter['is_recycle'];
                if (!empty($is_recycle)) {
                    $cond['is_recycle'] = (int)$is_recycle;
                }
                $is_hot = $filter['is_hot'];
                if (!empty($is_hot)) {
                    $cond['is_hot'] = (int)$is_hot;
                }
            }
        }

        if(empty($sortby))
        {
            $sortby['_id'] = 1;
        }
        $field["_id"] = 0;
        LogDebug($cond);
        $cursor = $table->find($cond, $field)->sort($sortby);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return GoodsEntry::ToList($cursor);
    }
    //批量上下架及回收站
    public function SetSale($goods_id_list, $sale_off, $type=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        for ($i = 0; $i < count($goods_id_list); $i++) {
            $goods_id_list[$i] = (string)$goods_id_list[$i];
        }

        $cond = array(
            'goods_id' => ['$in' => $goods_id_list]
        );
        $set['lastmodtime'] = time();
        if(!$type)
        {
            $set['sale_off'] = (int)$sale_off;
            if(1 == $sale_off)
            {
                $set['sale_off_way'] = 0;//取消定时上架
            }
        }
        else
        {
            $set['is_recycle'] = (int)$sale_off;
        }
        if(1 == $sale_off)
        {
            $set['is_hot']       = 0;//取消热门
        }
        $value = array(
            '$set' => $set
        );

        try {
            $ret = $table->update($cond, $value, ['safe' => true, 'upsert' => true, 'multiple' => true]);
            LogDebug("ret:" . json_encode($ret["ok"]));

        } catch (MongoCursorException $e) {
            LogErr($e->getMessage());
            return errcode::DB_OPR_ERR;
        }
        return 0;
    }
}



?>