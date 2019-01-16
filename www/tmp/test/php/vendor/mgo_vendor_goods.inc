<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 商品表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");


class VendorGoodsEntry extends BaseInfo
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
    public $is_weight         = null;      // 是否支持称重(1.是,0.否）
    public $goods_weight      = null;      // 商品重量

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
        $this->goods_stock       = $cursor["goods_stock"];
        $this->goods_bar_code    = $cursor["goods_bar_code"];
        $this->shop_id           = $cursor['shop_id'];
        $this->lastmodtime       = $cursor["lastmodtime"];
        $this->delete            = $cursor["delete"];
        $this->ctime             = $cursor["ctime"];
        $this->is_weight         = $cursor['is_weight'];
        $this->goods_weight      = $cursor["goods_weight"];
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
        if(null !== $info->goods_stock)
        {
            $set["goods_stock"] = (int)$info->goods_stock;
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

        if(null !== $info->goods_spec)
        {
            $set["goods_spec"] = (string)$info->goods_spec;
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
        if(null !== $info->is_weight)
        {
            $set["is_weight"] = (int)$info->is_weight;
        }
        if(null !== $info->goods_weight)
        {
            $set["goods_weight"] = (int)$info->goods_weight;
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
    public function GetVendorGoodsById($vendor_goods_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$vendor_goods_id, "vendor_goods_id");
        return VendorGoodsEntry::ToObj($cursor);
    }

    public function GetVendorGoodsList($filter=null,$sortby=[],$page_size=20, $page_no=1, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'     => ['$ne' => 1]
        ];
        if (null != $filter) {
            $vendor_goods_id = $filter['vendor_goods_id'];
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
                $shop_id = $filter['shop_id'];
                if (!empty($shop_id)) {
                    $cond['shop_id'] = (string)$shop_id;
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

                $search = $filter['search'];
                if (!empty($search)) {
                    $cond['$or'] = [
                        ["vendor_goods_name" => new \MongoRegex("/$search/i")],
                        ["vendor_goods_id" => new \MongoRegex("/$search/i")]
                    ];
                }
            }
        }

        if(empty($sortby))
        {
            $sortby['_id'] = 1;
        }
        $field["_id"] = 0;
        LogDebug($cond);
        LogDebug($sortby);
        $cursor = $table->find($cond,$field)->sort($sortby)->skip(($page_no - 1) * $page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return VendorGoodsEntry::ToList($cursor);
    }
    //批量上下架及回收站
    public function SetSale($goods_id_list, $sale_off)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        for ($i = 0; $i < count($goods_id_list); $i++) {
            $goods_id_list[$i] = (string)$goods_id_list[$i];
        }

        $cond = array(
            'vendor_goods_id' => ['$in' => $goods_id_list]
        );

        $set = array(
            'sale_off' => (int)$sale_off,
            'lastmodtime' => time()
        );

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
    public function GetListTotal($filter=null, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'  => ['$ne'=>1],
        ];
        if(null != $filter)
        {
            $shop_id = $filter['shop_id'];
            if(!empty($shop_id))
            {
                $cond['shop_id'] = (string)$shop_id;
            }
            $goods_stock = $filter['goods_stock'];
            if(null !== $goods_stock)
            {
                $cond['goods_stock'] = (int)$goods_stock;
            }
            $search = $filter['search'];
            if (!empty($search)) {
                $cond['$or'] = [
                    ["vendor_goods_name" => new \MongoRegex("/$search/i")],
                    ["vendor_goods_id"   => new \MongoRegex("/$search/i")]
                ];
            }
            $category_id = $filter['category_id'];
            if (!empty($category_id)) {
                $cond['category_id'] = (string)$category_id;
            }
        }
        $cursor = $table->find($cond, ["_id"=>0]);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return VendorGoodsEntry::ToList($cursor);
    }

    public function GetByCate($shop_id, $category_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'category_id' => $category_id,
            'shop_id'     => (string)$shop_id,
            'delete'      => ['$ne' => 1],
        );

        $cursor = $table->find($cond);
        return VendorGoodsEntry::ToList($cursor);
    }

}



?>