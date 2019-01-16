<?php
/*
 * 权限操作
 * [Rocky 2017-05-05 13:31:16]
 *
 */
class Permission {

    const CHK_LOGIN         = 1;         // 登录用户
    const CHK_ADMIN         = 2;         // 必须是管理员
    const CHK_ORDER_R       = 4;         // 订单读权限
    const CHK_ORDER_W       = 8;         // 订单写权限
    const CHK_FOOD_R        = 16;        // 餐品读权限
    const CHK_FOOD_W        = 32;        // 餐品写权限
    const CHK_REPORT_R      = 64;        // 订单读权限
    const CHK_SHOP_ADMIN    = 128;       // 必须是店铺管理员

    // $chk 权限值
    public static function Check($chk)
    {
        // 是否已登录
        if(($chk & Permission::CHK_LOGIN) && !PageUtil::LoginCheck())
        {
            return errcode::USER_NOLOGIN;
        }
        // 是否系统管理员
        // if(($chk & Permission::CHK_ADMIN) && !Cfg::instance()->IsAdmin(\Cache\Login::GetUsername()))
        // {
        //     return errcode::USER_PERMISSION_ERR;
        // }

        return 0;
    }

    // 一般性页面权限检查
    public static function PageCheck()
    {
//        $ret = Permission::Check(Permission::CHK_LOGIN);
//        if(0 != $ret)
//        {
//            echo json_encode((object)array('ret'=> $ret));
//            LogErr("permission err, ret:$ret, userid:" . \Cache\Login::GetUserid());
//            exit(0);
//        }
        $ret = Permission::PlPermissionCheck();
        if(0 != $ret)
        {
            echo json_encode((object)array('ret'=> $ret));
            LogErr("employee permission err or not login again");
            exit(0);
        }
    }

    // 管理员权限检查
    public static function AdminCheck()
    {
        $ret = Permission::Check(Permission::CHK_LOGIN|Permission::CHK_ADMIN);
        if(0 != $ret)
        {
            echo json_encode((object)array('ret'=> $ret));
            LogErr("permission err, username:" . \Cache\Login::GetUsername());
            exit(0);
        }
    }

    // 是个店铺用户，且有相应权限
    public static function EmployeePermissionCheck($permission_bit)
    {
        $ret = Permission::DoEmployeePermissionCheck($permission_bit);
        if(0 != $ret)
        {
            echo json_encode((object)array('ret'=> $ret));
            LogErr("permission err, username:" . \Cache\Login::GetUsername());
            exit(0);
        }
    }
    public static function DoEmployeePermissionCheck($permission_bit)
    {
        // 登录检查
        // 是否已登录
        if(!PageUtil::LoginCheck())
        {
            LogErr("user no login");
            return errcode::USER_NOLOGIN;
        }

        $loginuser = \Cache\Login::UserInfo();

        if(!$loginuser)
        {
            LogErr("user no login");
            return errcode::USER_NOLOGIN;
        }

        // 是否系统管理员

        if(($permission_bit & Permission::CHK_ADMIN)
            && (!Cfg::instance()->IsAdmin($loginuser->username)
                || !UserProperty::IsAdmin($loginuser->property)))
        {
            return 0;
        }

        // 当前登录用户
        $userid = $loginuser->userid;
        $employeeinfo = \Cache\Employee::Get($userid);
        if(!$employeeinfo)
        {
            LogErr("no employeeinfo:[$userid]");
            return errcode::USER_NOLOGIN;
        }

        // 是否为店铺用户
        $permission = PageUtil::CurLoginEmployeePermission();
        if(!$permission)
        {
            LogErr("no employee or no permission");
            return errcode::USER_PERMISSION_ERR;
        }

        // 是否为店铺管理员
        if(($permission_bit & Permission::CHK_SHOP_ADMIN) != 0
            && !EmployeeDuty::IsShopAdmin($employeeinfo->duty))
        {
            LogErr("no permission: CHK_SHOP_ADMIN");
            return errcode::USER_PERMISSION_ERR;
        }

        // 是否有相应权限
        // "permission" : {
        //         "order_read" : 1,
        //         "order_write" : 0,
        //         "food_read" : 1,
        //         "food_write" : 0,
        //         "report_read" : 1
        // }

        // order
        if(($permission_bit & Permission::CHK_ORDER_R) != 0
            && $permission['order_read'] != 1)
        {
            LogErr("no permission: order_read");
            return errcode::USER_PERMISSION_ERR;
        }
        if(($permission_bit & Permission::CHK_ORDER_W) != 0
            && $permission['order_write'] != 1)
        {
            LogErr("no permission: order_write");
            return errcode::USER_PERMISSION_ERR;
        }

        // food
        if(($permission_bit & Permission::CHK_FOOD_R) != 0
            && $permission['food_read'] != 1)
        {
            LogErr("no permission: food_read, " . json_encode($permission));
            return errcode::USER_PERMISSION_ERR;
        }
        if(($permission_bit & Permission::CHK_FOOD_W) != 0
            && $permission['food_write'] != 1)
        {
            LogErr("no permission: food_write");
            return errcode::USER_PERMISSION_ERR;
        }

        // report
        if(($permission_bit & Permission::CHK_REPORT_R) != 0
            && $permission['report_read'] != 1)
        {
            LogErr("no permission: report_read");
            return errcode::USER_PERMISSION_ERR;
        }

        // 有权限
        LogDebug("permission ok");
        return 0;
    }
    public static function CheckIsPermission($employee)
    {

        // 登录检查
        // 是否已登录
        if(!PageUtil::LoginCheck())
        {
            LogErr("user no login");
            return errcode::USER_NOLOGIN;
        }
        $loginuser = \Cache\Login::UserInfo();

        if(!$loginuser)
        {
            LogErr("user no login");
            return errcode::USER_NOLOGIN;
        }
        if(!$employee)
        {
            LogErr("no employeeinfo:[$employee->userid]");
            return errcode::USER_NOLOGIN;
        }

        //获取权限信息
        $positioninfo = \Cache\Position::Get($employee->shop_id,$employee->position_id);

        // 是否为超级管理员
        $permission = PageUtil::IsLoginEmployeePermission($employee->shop_id);
        // 检查权限是否只够
        if($permission != 1 && !Position::IsAdmin($positioninfo->position_permission))
        {
            LogErr("no permission: CHK_SHOP_ADMIN");
            return errcode::USER_PERMISSION_ERR;
        }
        // 有权限
        LogDebug("permission ok");
        return 0;
    }
    //检测用户是否且有相应权限
    public static function UserPermissionCheck($employee)
    {
        $ret = Permission::CheckIsPermission($employee);
        if(0 != $ret)
        {
            echo json_encode((object)array('ret'=> $ret));
            LogErr("permission err, username:" . \Cache\Login::GetUsername());
            exit(0);
        }
    }
    //店铺操作权限
    public static function ShopCheck($chk)
    {
        if($chk->is_admin != 1 && $chk->position_id)
        {
            return errcode::USER_NO_EXIST;
        }
        // 是否系统管理员
        if(($chk & Permission::CHK_ADMIN) && !Cfg::instance()->IsAdmin(\Cache\Login::GetUsername()))
        {
            return errcode::USER_PERMISSION_ERR;
        }
        return 0;
    }
    //平台登陆员工一般页面权限检查
    public static function PageLoginCheck()
    {

        $ret = Permission::PlPermissionCheck();
        if(0 != $ret)
        {
            echo json_encode((object)array('ret'=> $ret));
            LogErr("employee permission err or not login again");
            exit(0);
        }
    }

    //检查登陆账号是否具有权限
    public static function PlPermissionCheck()
    {
        // 登录检查
        // 是否已登录

        if(!PageUtil::LoginCheck())
        {
            LogErr("user no login");
            return errcode::USER_NOLOGIN;
        }

        $loginuser = \Cache\Login::UserInfo();

        if(!$loginuser->userid)
        {
            LogErr("user no login");
            return errcode::USER_NOLOGIN;
        }

        // 当前登录用户
        $userid          = $loginuser->userid;
        $platformer_info = \Cache\Platformer::Get($userid);
        $agent_employee  = \Cache\AgentEmployee::Get($userid);
        if(!$platformer_info->userid && !$agent_employee->userid )
        {
            LogErr("no platformer_info:[$userid]");
            return errcode::USER_NOLOGIN;
        }
        $agentinfo        = \Cache\Agent::Get($agent_employee->agent_id);

        //代理商是否被冻结
        if($agentinfo->is_freeze == \EmployeeFreeze::FREEZE)
        {
            LogErr("agent freeze");
            return errcode::AGENT_IS_FREEZE;
        }

        if($platformer_info->is_admin != 1 && $agent_employee->is_admin != 1)
        {

            if($platformer_info->is_freeze == EmployeeFreeze::FREEZE || $agent_employee->is_freeze == EmployeeFreeze::FREEZE)
            {
                LogErr("this platformer is freeze");
                return errcode::EMPLOYEE_IS_FREEZE;
            }

//            $position    = \Cache\PlPosition::Get($platformer_info->position_id);
//            $ag_position = \Cache\AgentPermission::Get($agent_employee->position_id);
//            if(($position->pl_position_permission & \PlPosition::ALL) == 0 && ($ag_position->pl_position_permission & \PlPosition::ALL) == 0 )
//            {
//                    LogErr("this loginer permission is not enough");
//                    return errcode::USER_PERMISSION_ERR;
//            }

        }


        // 有权限
        LogDebug("permission ok");
        return 0;
    }

}// end of class Permission {...
?>
