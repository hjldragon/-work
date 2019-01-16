<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 商品表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class VendorGoods extends BaseInfo
{
    public $vendor_goods_id   = null;      // 商品id
    public $vendor_goods_name = null;      // 商品名称
    public $sale_off          = null;      // 0上架 1下架
    public $goods_img_list    = null;      // 图片
    public $goods_spec        = null;      // 商品规格
    public $category_id       = null;      // 分类id
    public $from_company      = null;      // 生产企业
    public $goods_price       = null;      // 商品售价
    public $goods_cost_price  = null;      // 商品成本价
    public $goods_stock       = null;      // 商品库存
    public $goods_bar_code    = null;      // 商品条码
    public $shop_id           = null;      // 所属店铺id
    public $lastmodtime       = null;      // 最后修改时间
    public $delete            = null;      //
    public $ctime             = null;      // 创建时间

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
        $this->vendor_goods_id   = $cursor["vendor_goods_id"];
        $this->vendor_goods_name = $cursor["vendor_goods_name"];
        $this->sale_off          = $cursor["sale_off"];
        $this->goods_img_list    = $cursor["goods_img_list"];
        $this->goods_spec        = $cursor["goods_spec"];
        $this->category_id       = $cursor["category_id"];
        $this->from_company      = $cursor["from_company"];
        $this->goods_price       = $cursor["goods_price"];
        $this->goods_cost_price  = $cursor["goods_cost_price"];
        $this->goods_bar_code    = $cursor["goods_bar_code"];
        $this->shop_id           = $cursor['shop_id'];
        $this->lastmodtime       = $cursor["lastmodtime"];
        $this->delete            = $cursor["delete"];
        $this->ctime             = $cursor["ctime"];
    }
}

class VendorGoods extends MgoBase
{
    protected function Tablename()
    {
        return 'vendor_goods';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'vendor_goods_id' => (string)$info->vendor_goods_id
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->vendor_goods_type)
        {
            $set["vendor_goods_type"] = (int)$info->vendor_goods_type;
        }

        if(null !== $info->vendor_goods_name)
        {
            $set["vendor_goods_name"] = (string)$info->vendor_goods_name;
        }

        if(null !== $info->sale_off)
        {
            $set["sale_off"] = (int)$info->sale_off;
        }

        if(null !== $info->goods_img_list)
        {
            $set["goods_img_list"] = $info->goods_img_list;
        }
        if(null !== $info->category_id)
        {
            $set["category_id"] = $info->category_id;
        }

        if(null !== $info->from_company)
        {
            $set["from_company"] = (string)$info->from_company;
        }
        if(null !== $info->goods_price)
        {
            $set["goods_price"] = (float)$info->goods_price;
        }
        if(null !== $info->goods_cost_price)
        {
            $set["goods_cost_price"] = (float)$info->goods_cost_price;
        }
        if(null !== $info->goods_bar_code)
        {
            $set["goods_bar_code"] = (string)$info->goods_bar_code;
        }
        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
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
        return parent::DoBatchDelete($id_list, "vendor_goods_id");
    }

    // public function GetExampleById($vendor_goods_id)
    // {
    //     $db = \DbPool::GetMongoDb();
    //     $table = $db->selectCollection($this->Tablename());

    //     $cond = [
    //         'vendor_goods_id' => (string)$vendor_goods_id,
    //         'delete'      => 0,
    //     ];
    //     $cursor = $table->findOne($cond);
    //     return new VendorGoods($cursor);
    // }
    public function Getvendor_goodsById($vendor_goods_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$vendor_goods_id, "vendor_goods_id");
        return VendorGoods::ToObj($cursor);
    }

    public function Getvendor_goodsList($filter = null, &$total=null, $sortby = [])
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'     => ['$ne' => 1],
            'is_draft'   => ['$ne' => 1],
            'is_recycle' => ['$ne' => 1]
        ];
        if (null != $filter) {
            $foods_id = $filter['vendor_goods_id'];
            if (!empty($vendor_goods_id)) {
                $cond['vendor_goods_id'] = (string)$vendor_goods_id;
            } else {
                $vendor_goods_id_list = $filter['vendor_goods_id_list'];
                if(!empty($vendor_goods_id_list))
                {
                    foreach($vendor_goods_id_list as $i => &$item)
                    {
                        $item = (string)$item;
                    }
                    $cond["vendor_goods_id"] = ['$in' => $vendor_goods_id_list];
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

                $vendor_goods_name = $filter['vendor_goods_name'];
                if (!empty($vendor_goods_name)) {
                    $cond['$or'] = [
                        ["vendor_goods_name" => new \MongoRegex("/$vendor_goods_name/i")],
                        ["vendor_goods_id" => new \MongoRegex("/$vendor_goods_name/i")]
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
        return VendorGoods::ToList($cursor);
    }
    //批量上下架及回收站
    public function SetSale($vendor_goods_id_list, $sale_off, $type=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        for ($i = 0; $i < count($vendor_goods_id_list); $i++) {
            $vendor_goods_id_list[$i] = (string)$vendor_goods_id_list[$i];
        }

        $cond = array(
            'vendor_goods_id' => ['$in' => $vendor_goods_id_list]
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