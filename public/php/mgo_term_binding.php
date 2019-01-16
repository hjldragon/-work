<?php
/*
 * [Rocky 2017-06-02 02:35:27]
 * 终端绑定表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

class TermBindingEntry extends BaseInfo
{
    public $term_id         = null; // 终端id
    public $shop_id         = null; // 所属店铺id
    public $term_type       = null; // 终端类型(1:智能收银机,2:自助点餐机,4:平板智能点餐机,5:掌柜通)
    public $employee_id     = null; // 绑定员工id
    public $ctime           = null; // 创建时间
    public $login_time      = null; // 最后登录时间
    public $is_login        = null; // 登录状态（1登录0退出）
    public $lastmodtime     = null; // 数据最后修改时间
    public $delete          = null; // 0:正常, 1:已删除

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
        $this->term_id         = $cursor['term_id'];
        $this->shop_id         = $cursor['shop_id'];
        $this->term_type       = $cursor['term_type'];
        $this->employee_id     = $cursor['employee_id'];
        $this->ctime           = $cursor['ctime'];
        $this->login_time      = $cursor['login_time'];
        $this->is_login        = $cursor['is_login'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];
    }
}
class TermBinding extends MgoBase
{
    protected function Tablename()
    {
        return 'term_binding';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'term_id' => (string)$info->term_id
        );

        $set = array(
            "lastmodtime" => (null !== $info->lastmodtime) ? $info->lastmodtime : time()
        );

        if(null !== $info->shop_id)
        {
            $set["shop_id"] = (string)$info->shop_id;
        }
        if(null !== $info->term_type)
        {
            $set["term_type"] = (int)$info->term_type;
        }
        if(null !== $info->employee_id)
        {
            $set["employee_id"] = (string)$info->employee_id;
        }
        if(null !== $info->ctime)
        {
            $set["ctime"] = (int)$info->ctime;
        }
        if(null !== $info->login_time)
        {
            $set["login_time"] = (int)$info->login_time;
        }
        if(null !== $info->is_login)
        {
            $set["is_login"] = (int)$info->is_login;
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
        return parent::DoBatchDelete($id_list, "term_id");
    }
    public function GetTermById($term_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$term_id, "term_id");
        return TermBindingEntry::ToObj($cursor);
    }

    //获取列表数据
    public function GetTermBindList($filter=null, $page_size=10, $page_no=1, &$total=null)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = [
            'delete'      =>  ['$ne'=>1],
            "employee_id" => ['$nin'=>[null,"0"]]
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

            $employee_id_list = $filter['employee_id_list'];
            if(!empty($employee_id_list))
            {
                foreach($employee_id_list as $i => &$item)
                {
                   $item = (string)$item;
                }
                $cond['employee_id'] = ['$in' => $employee_id_list];
            }
            $term_type = $filter['term_type'];
            if (!empty($term_type)) {
                $cond['term_type'] = (int)$term_type;
            }

            $is_login = $filter['is_login'];
            if (null != $is_login) {
                $cond['is_login'] = (int)$is_login;
            }
        }

        $sortby['ctime'] = -1;

        $cursor = $table->find($cond)->sort($sortby)->skip(($page_no-1)*$page_size)->limit($page_size);
        if(null !== $total){
            $total = $table->count($cond);
        }
        return TermBindingEntry::ToList($cursor);
    }

    public function QueryByEmployeeId($employee_id)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'employee_id'  => (string)$employee_id,
            'delete'   => array('$ne'=>1)
        );
        $ret = $table->findOne($cond);
        return new TermBindingEntry($ret);
    }

}






?>
