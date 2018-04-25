<?php
/*
 * [Rocky 2017-05-05 11:44:02]
 * 新建代理商生成的数据文件
 */
require_once("current_dir_env.php");
require_once("mgo_agent_apply.php");
require_once("redis_id.php");
require_once("redis_login.php");
//Permission::PageCheck();
function SaveAgentApply(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $apply_name = $_['apply_name'];
    $telephone  = $_['telephone'];
    $company    = $_['company'];
    $email      = $_['email'];
    $address    = $_['address'];
    $city       = $_['city'];
    $province   = $_['province'];

    if(!$apply_name || !$telephone || !$company)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $req_p = PageUtil::GetPhone($telephone);
    if(!$req_p)
    {
        LogErr('telephone is not verify');
        return errcode::PHONE_ERR;
    }
    if($email)
    {
         $req = PageUtil::GetEmail($email);
         if(!$req)
         {
             LogErr('emial is not verify');
             return errcode::EMAIL_ERR;
         }
    }

    $apply_id = \DaoRedis\Id::GenAgentApplyId();
    $entry    = new \DaoMongodb\AgentApplyEntry;
    $mgo      = new \DaoMongodb\AgentApply;

    $entry->apply_name      = $apply_name;
    $entry->apply_id        = $apply_id;
    $entry->company         = $company;
    $entry->telephone       = $telephone;
    $entry->email           = $email;
    $entry->address         = $address;
    $entry->city            = $city;
    $entry->province        = $province;
    $entry->apply_time      = time();
    $entry->apply_status    = 0;
    $entry->delete          = 0;
    $ret = $mgo->Save($entry);
    if(0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)array(
    );
    LogInfo("save ok");
    return 0;
}
//批量删除和改变代理商申请列表
function ChangeAgentApply(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $apply_id_list = json_decode($_['apply_id_list']);
    $type          = (int)$_['type'];
    if(!$type)
    {
        LogErr("type is null");
        return errcode::PARAM_ERR;
    }
    $mongodb = new \DaoMongodb\AgentApply;
    $ret     = $mongodb->BatchChangeById($apply_id_list, $type);
    if(0 != $ret)
    {
        LogErr("Change err");
        return errcode::SYS_ERR;
    }
    $resp = (object)array(
    );
    LogInfo("change ok");
    return 0;
}
$ret  = -1;
$resp = (object)array();
if(isset($_['agent_apply_save']))
{
    $ret = SaveAgentApply($resp);

}elseif(isset($_['change_apply_status']))
{
    $ret = ChangeAgentApply($resp);
}

else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret' => $ret,
    'data' => $resp
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
