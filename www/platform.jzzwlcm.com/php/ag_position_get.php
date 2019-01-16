<?php
/*
 * [Rocky 2017-05-03 16:29:35]
 * 取店铺信息
 */
require_once("current_dir_env.php");
require_once("page_util.php");
require_once("const.php");
require_once("cache.php");
require_once("mgo_ag_position.php");
require_once("permission.php");
require_once("mgo_ag_employee.php");
Permission::PageLoginCheck();
//Permission::PageCheck();
// $_=$_REQUEST;
//获取一个职位及权限
function GetAgPositionInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $ag_position_id = $_['ag_position_id'];
    if(!$ag_position_id)
    {
        LogErr("ag_position_id is not");
        return errcode::PARAM_ERR;
    }
    $mgo         = new \DaoMongodb\AGPosition;
    $info        = $mgo->GetPositionById($ag_position_id);
    $permission  = PermissionSplit($info->ag_position_permission);
    $resp = (object)[
        'position_info' => $info,
        'permission'    => $permission,
    ];
    LogInfo("--ok--");
    return 0;
}
//位运算分解
function PermissionSplit($n) {
    $n   |= 0;
    $pad = 0;
    $arr = [];
    while ($n)
    {
        if ($n & 1) array_push($arr, 1 << $pad);
        $pad++;
        $n >>= 1;
    }
    return $arr;
}
//权限列表
function GetAgPositionList(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $agent_id    = $_['agent_id'];
    $page_size   = $_['page_szie'];
    $page_no     = $_['page_no'];
    $total = 0;
    if (!$page_size)
    {
        $page_size = 7;//如果没有传默认10条
    }
    if (!$page_no)
    {
        $page_no = 1; //第一页开始
    }
    $mgo         = new \DaoMongodb\AGPosition;
    $list        = $mgo->GetPositionByAgentID($agent_id);
//    foreach ($list as $v)
//    {
//        $list['position_permission']=[];
//        $permission  = PermissionSplit($v->position_permission);
//    }
    $resp = (object)array(
        'position_list' => $list,
        'total'         => $total,                                  //总单数
        'page_size'     => $page_size,
        'page_no'       => $page_no
    );
    LogInfo("--ok--");
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_["get_ag_position_info"]))
{
    $ret = GetAgPositionInfo($resp);
}
elseif(isset($_["get_ag_position_list"]))
{
    $ret = GetAgPositionList($resp);
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>