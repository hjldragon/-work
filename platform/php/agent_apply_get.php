<?php
/*
 * [Rocky 2017-06-20 00:10:18]
 * 员工信息表
 *
 */
require_once("current_dir_env.php");
require_once("redis_id.php");
require_once("cache.php");
require_once("mgo_agent_apply.php");


//Permission::PageCheck();

function GetAgentApplyList(&$resp)
{
    $_ = $GLOBALS["_"];
    //LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $begin_time      = $_["begin_time"];
    $end_time        = $_["end_time"];
    $apply_status    = $_['apply_status'];
    $page_size       = $_['page_size'];
    $page_no         = $_['page_no'];
    $sort_name       = $_['sort_name'];
    $desc            = $_['desc'];
    if (!$page_size)
    {
        $page_size = 10;//如果没有传默认10条
    }
    if (!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    if(!$begin_time && $end_time)
    {
        $begin_time = -28800; //默认时间
    }
    if(!$end_time && $begin_time)
    {
        $end_time = time();
    }
    switch ($sort_name) {
        case 'apply_time':
            $sort['_id'] = (int)$desc;
            break;
        default:
            break;
    }
    $total = 0;
    $mgo = new \DaoMongodb\AgentApply;
    $list = $mgo->GetAgentApplyList([
        'apply_status' => $apply_status,
        'begin_time'   => $begin_time,
        'end_time'     => $end_time,
    ],
    $page_size,
    $page_no,
    $sort,
    $total
    );

    $resp = (object)array(
        'apply_list'      => $list,
        'total'     => $total,
        'page_size' => $page_size,
        'page_no'   => $page_no,

    );
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

$ret = -1;
$resp = (object)array();
if(isset($_["agent_apply_list"]))
{
    $ret = GetAgentApplyList($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}


$html = json_encode((object)array(
    'ret' => $ret,
    'data'  => $resp
));
echo $html;
LogDebug($html);
// $callback = $_['callback'];
// $js =<<< eof
// $callback($html);
// eof;
?>