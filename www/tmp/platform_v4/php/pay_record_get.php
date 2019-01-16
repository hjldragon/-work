<?php
/*
 * [Rocky 2017-06-02 02:28:07]
 * 取餐桌位置信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("mgo_agent.php");
require_once("cache.php");
require_once("/www/public.sailing.com/php/mgo_pay_record.php");
require_once("/www/public.sailing.com/permission/permission_check.php");
require_once("/www/public.sailing.com/permission/permission.php");
use \Pub\Mongodb as Mgo;

//获取所有充值记录
function GetPayRecordList(&$resp)
{

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id       = $_['agent_id'];
    $platform       = $_['platform'];
    if(!$platform) {
        AgPermissionCheck::PageCheck(AgentPermissionCode::PAY_LIST);
    }
    if (!$agent_id) {
        LogErr("agent_id  is empty");
        return errcode::PARAM_ERR;
    }

    $page_size       = $_['page_size'];
    $page_no         = $_['page_no'];
    $sort_name       = $_['sort_name'];
    $desc            = $_['desc'];
    $pay_status      = $_['pay_status'];
    $pay_way         = $_['pay_way'];

    switch ($sort_name) {
        case 'shop_id':
            $sort['_id'] = (int)$desc;
            break;
        case 'ctime':
            $sort['ctime'] = (int)$desc;
            break;
        default:
            break;
    }
    //排序字段
    if (!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if (!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    $total      = 0;
    $mgo        = new Mgo\PayRecord;
    $agent_mgo  = new \DaoMongodb\Agent;
    $agent_info = $agent_mgo->QueryById($agent_id);
    $list       = $mgo->GetPayRecordList(
        [
            'agent_id'   => $agent_id,
            'pay_status' => $pay_status,
            'pay_way'    => $pay_way
        ],
        $page_size,
        $page_no,
        $sort,
        $total);
    foreach ($list as &$v)
    {
        if($v->pay_status == RecordPayStatus::PAY)
        {
            $v->old_money = $agent_info->money-$v->record_money;
            $v->new_money = $agent_info->money;
        }

    }
    $resp = (object)array(
        'list'      => $list,
        'total'     => $total,
        'page_all'  => ceil($total/$page_size),//总共页数,
        'page_no'   => $page_no
    );

    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["get_pay_record_list"]))
{
    $ret = GetPayRecordList($resp);
}

$result = (object)array(
    'ret' => $ret,
    'data' => $resp
);

if($GLOBALS['need_json_obj'])
{
    Output($result);
}
else
{
    $html =  json_encode($result);
    echo $html;
}
?><?php /******************************以下为html代码******************************/?>

