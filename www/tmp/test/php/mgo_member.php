<?php
/*
 * [Rocky 2017-04-26 18:00:57]
 * 会员表操作类
 */
namespace Pub\Mongodb;
require_once("db_pool.php");
require_once("mgo_base.php");
require_once("mgo_baseinfo.php");

# t_member
class MemberEntry extends BaseInfo
{
    public $member_id       = null;   // 会员id
    public $member_name     = null;   // 会员姓名
    public $password        = null;   // 密码
    public $ctime           = null;   // 创建时间
    public $lastmodtime     = null;   // 修改时间
    public $delete          = null;   // 0:未删除; 1:已删除
    public $member_phone    = null;   // 手机号
    public $member_birthday = null;   // 会员生日
    public $member_sex      = null;   // 会员的性别，值为1时是男性，值为2时是女性，值为0时是未知
    public $member_email    = null;   // 会员邮箱
    public $member_province = null;   // 省
    public $member_city     = null;   // 市
    public $member_area     = null;   // 区
    public $member_address  = null;   // 地址
    public $member_level    = null;   // 会员等级

    // // 具体业务数据


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
        $this->member_id       = $cursor['member_id'];
        $this->member_name     = $cursor['member_name'];
        $this->password        = $cursor['password'];
        $this->ctime           = $cursor['ctime'];
        $this->lastmodtime     = $cursor['lastmodtime'];
        $this->delete          = $cursor['delete'];
        $this->member_phone    = $cursor['member_phone'];
        $this->member_birthday = $cursor['member_birthday'];
        $this->member_sex      = $cursor['member_sex'];
        $this->member_email    = $cursor['member_email'];
        $this->member_province = $cursor['member_province'];
        $this->member_city     = $cursor['member_city'];
        $this->member_area     = $cursor['member_area'];
        $this->member_address  = $cursor['member_address'];
    }
};

class Member extends BaseInfo
{
    protected function Tablename()
    {
        return 'member';
    }

    public function Save(&$info)
    {
        $db = \DbPool::GetMongoDb();
        $table = $db->selectCollection($this->Tablename());

        $cond = array(
            'member_id' => (string)$info->member_id,
        );
        $set = [
            "member_id"      => (string)$info->member_id,
            "lastmodtime" => (int)time(),
        ];
        if(null !== $info->member_name)
        {
            $set["member_name"] = (string)$info->member_name;
        }
        if(null !== $info->password)
        {
            $set["password"] = (string)$info->password;
        }
        if((int)$info->ctime > 0)
        {
            $set["ctime"] = (int)$info->ctime;
        }
        if(null !== $info->delete)
        {
            $set["delete"] = (int)$info->delete;
        }
        if(null !== $info->member_phone)
        {
            $set["member_phone"] = (string)$info->member_phone;
        }
        if (null !== $info->member_birthday) {
            $set["member_birthday"] = (int)$info->member_birthday;
        }
        if (null !== $info->member_sex) {
            $set["member_sex"] = (int)$info->member_sex;
        }
        if (null !== $info->member_email) {
            $set["member_email"] = (string)$info->member_email;
        }
        if (null !== $info->member_province) {
            $set["member_province"] = (string)$info->member_province;
        }
        if (null !== $info->member_city) {
            $set["member_city"] = (string)$info->member_city;
        }
        if (null !== $info->member_area) {
            $set["member_area"] = (string)$info->member_area;
        }
        if (null !== $info->member_address) {
            $set["member_address"] = (string)$info->member_address;
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

    public function GetMemberById($member_id)
    {
        $cursor = parent::DoGetInfoByKey((string)$member_id, "member_id");
        return MemberEntry::ToObj($cursor);
    }

    public function BatchDelete($id_list)
    {
        return parent::DoBatchDelete($id_list, "member_id");
    }



}




?>
