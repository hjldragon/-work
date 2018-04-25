<?php
/*
 * [Rocky 2017-06-20 00:10:18]
 * 运营平台员工信息表
 *
 */
require_once("current_dir_env.php");
require_once("mgo_platformer.php");
require_once("redis_id.php");
require_once("cache.php");
require_once("mgo_pl_department.php");
require_once("mgo_pl_position.php");
require_once("mgo_user.php");
//Permission::PageCheck();


function GetPlatformerInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $platformer_id = $_['platformer_id'];

    $mgo         = new \DaoMongodb\Platformer;
    $platformer  = $mgo->QueryById($platformer_id);
    if(!$platformer->platformer_id)
    {
       LogErr('[platformer_id] err');
       return errcode::USER_NO_EXIST;
    }
    if(1 == $platformer->is_admin)
    {
        $platformer->position_name = '超级管理员';
    }
    else
    {
        $department                  = new \DaoMongodb\PLDepartment;
        $department_info             = $department->QueryByDepartmentId($platformer->pl_department_id);
        $platformer->department_name = $department_info->pl_department_name;
        $position                    = new \DaoMongodb\PLPosition;
        $position_info               = $position->GetPositionById($platformer->pl_position_id);
        $platformer->position_name   = $position_info->pl_position_name;
    }
    if($platformer->from_record)
    {
        $from_record = array_unique(array_filter($platformer->from_record));
        $platformer->from_record = array_values($from_record);
    }
    if($platformer->salesman_record)
    {
        $salesman_record = array_unique(array_filter($platformer->salesman_record));
        $platformer->salesman_record = array_values($salesman_record);
    }
    $user = new \DaoMongodb\User;
    $user_info = $user->QueryById($platformer->userid);
    $platformer->phone    = $user_info->phone;
    $platformer->password = $user_info->password;
    $resp = (object)[
        'platformer' => $platformer
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetPlatformerList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $page_size        = $_['page_size'];
    $page_no          = $_['page_no'];
    $sort_name        = $_['sort_name'];
    $pl_name          = $_['pl_name'];
    $is_freeze        = $_['is_freeze'];
    $desc             = $_['desc'];
    $pl_department_id = $_['pl_department_id'];

        switch ($sort_name) {
            case 'platformer_id':
                $sort['_id'] = (int)$desc;
                break;
            case 'is_freeze'://反结账
                $sort['is_freeze'] = (int)$desc;
                break;
            default:
                break;
        }

    if(!$page_size)
    {
        $page_size  = 10;
    }
    if(!$page_no)
    {
        $page_no = 1;
    }
    $mgo  = new \DaoMongodb\Platformer;
    $user = new \DaoMongodb\User;
    $total= 0;
    $list = $mgo->GetplatformerList(
        [
            'is_freeze'        => $is_freeze,
            'pl_name'          => $pl_name,
            'pl_department_id' => $pl_department_id
        ],
        $page_size,
        $page_no,
        $sort,
        $total
    );
    $info = [];
    foreach ($list as $v)
    {   
        if($v->is_admin == 1)
        {
            continue;
        }

        $department            = new \DaoMongodb\PLDepartment;
        $department_info       = $department->QueryByDepartmentId($v->pl_department_id);
        $position              = new \DaoMongodb\PLPosition;
        $position_info         = $position->GetPositionById($v->pl_position_id);
        $user_info             = $user->QueryById($v->userid);
        $v->phone              = $user_info->phone;
        $v->pl_position_name   = $position_info->pl_position_name;
        $v->pl_department_name = $department_info->pl_department_name;
        array_push($info, $v);
    }
    $resp = (object)[
        'platformer_list' => $info,
        'total'           => $total,
        'page_size'       => $page_size,
        'page_no'         => $page_no,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetPlatformerNameList(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $mgo  = new \DaoMongodb\Platformer;
    $platform_id = PlatformID::ID;

    $list = $mgo->GetPlatformId($platform_id);
    $all  = array();
    foreach ($list as $value) {
        $info = (object)array();
        $info->platformer_id  = $value->platformer_id;
        $info->pl_name        = $value->pl_name;
        array_push($all, $info);
    }

    $resp = (object)[
        'platformer_list' => $all,
    ];
    //LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}
$ret = -1;
$resp = (object)array();
if(isset($_["get_platformer_info"]))
{
    $ret = GetPlatformerInfo($resp);
}elseif(isset($_["get_platformer_list"]))
{
    $ret = GetPlatformerList($resp);
}elseif(isset($_["get_platformer_name"]))
{
    $ret = GetPlatformerNameList($resp);
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