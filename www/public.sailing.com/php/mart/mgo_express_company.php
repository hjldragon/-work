<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 快递公司表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class ExpressCompanyEntry extends BaseInfo
{
    public $express_company_id    = null; // 快递公司id
    public $express_company_name  = null; // 快递公司名称
    public $express_company_code  = null; // 快递公司编码
    public $express_company_logo  = null; // 快递公司LOGO
    public $express_company_phone = null; // 快递公司电话
    public $lastmodtime           = null; // 数据最后修改时间
    public $delete                = null; // 0:正常, 1:已删除
    public $ctime                 = null; // 创建时间

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
        $this->express_company_id    = $cursor["express_company_id"];
        $this->express_company_name  = $cursor["express_company_name"];
        $this->express_company_code  = $cursor["express_company_code"];
        $this->express_company_logo  = $cursor["express_company_logo"];
        $this->express_company_phone = $cursor["express_company_phone"];
        $this->lastmodtime           = $cursor["lastmodtime"];
        $this->delete                = $cursor["delete"];
        $this->ctime                 = $cursor["ctime"];
    }
}
class ExpressCompany extends MgoBase
{
    protected function Tablename()
    {
        return 'express_company';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'express_company_id' => (string)$info->express_company_id
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->express_company_name)
        {
            $set["express_company_name"] = (string)$info->express_company_name;
        }
        if(null !== $info->express_company_code)
        {
            $set["express_company_code"] = (string)$info->express_company_code;
        }
        if(null !== $info->express_company_logo)
        {
            $set["express_company_logo"] = (string)$info->express_company_logo;
        }
        if(null !== $info->express_company_phone)
        {
            $set["express_company_phone"] = (string)$info->express_company_phone;
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
        return parent::DoBatchDelete($id_list, "express_company_id");
    }

    public function GetExpressCompanyById($express_company_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$express_company_id, "express_company_id");
        return ExpressCompanyEntry::ToObj($cursor);
    }



    public function GetExpressCompanyList()
    {

        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete' => ['$ne'=>1]
        ];
        $cursor = $table->find($cond, ["_id"=>0])->sort(["ctime"=>1]);

        return ExpressCompanyEntry::ToList($cursor);
    }

      public function GetExpressCompanyByName($express_company_name)
      {
        $db =\DbPool::GetMongoDb();
        $table =$db->selectCollection($this->Tablename());
        $cond = array(
            'delete'  => ['$ne'=>1],
            'express_company_name'=>(string)$express_company_name
        );
        $cursor = $table->findOne($cond);
        return new  ExpressCompanyEntry($cursor);
    }
}



?>