<?php
/*
 * 权限操作
 * [Rocky 2017-05-05 13:31:16]
 *
 */
class Permission {

    const CHK_LOGIN         = 1;         // 登录用户
    const CHK_ADMIN         = 2;         // 必须是管理员
    const CHK_PC_R          = 4;         // 必须有登录授权
    const CHK_SHOP_AZ       = 8;         // 必须非冻结员工
    //const CHK_ORDER_R       = 4;         // 订单读权限
    //const CHK_ORDER_W       = 8;         // 订单写权限
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
//         if(($chk & Permission::CHK_ADMIN) && !Cfg::instance()->IsAdmin(\Cache\Login::GetUsername()))
//         {
//             return errcode::USER_PERMISSION_ERR;
//         }
        return 0;
    }
    // 一般性页面权限检查
    public static function PageCheck($srctype)
    {
//        $ret = Permission::Check(Permission::CHK_LOGIN);
//        if(0 != $ret)
//        {
//            echo json_encode((object)array('ret'=> $ret));
//            LogErr("permission err, ret:$ret, userid:" . \Cache\Login::GetUserid());
//            exit(0);
//        }
        $ret = Permission::PhonePermissionCheck($srctype);
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
        $userid       = $loginuser->userid;
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

    //检查登录账号是否且有权限
    public static function PhonePermissionCheck($srctype)
    {
        // 登录检查
        // 是否已登录
        if(!PageUtil::LoginCheck())
        {
            LogErr("user no login");
            return errcode::USER_NOLOGIN;
        }
        if(!$srctype)
        {
            $srctype = 1;
        }

        $loginuser = \Cache\Login::UserInfo();
        $shop_id   = \Cache\Login::GetShopId();
        LogDebug($srctype);
        LogDebug($shop_id);
        if(!$shop_id)
        {
            LogErr("no shop_id");
            return errcode::SHOP_NOT_WEIXIN;
        }
        if(!$loginuser->userid)
        {
            LogErr("user no login");
            return errcode::USER_NOLOGIN;
        }

        // 当前登录用户

        $userid       = $loginuser->userid;
        $employeeinfo = \Cache\Employee::GetInfo($userid, $shop_id);
        $shop_info    = \Cache\Shop::Get($shop_id);
        if(!$employeeinfo->userid)
        {
            LogErr("no employeeinfo:[$userid]");
            return errcode::USER_NOLOGIN;
        }
        //店铺是否被冻结
        if($shop_info->is_freeze == \EmployeeFreeze::FREEZE)
        {
            LogErr("shop freeze");
            return errcode::SHOP_IS_FREEZE;
        }
        if($employeeinfo->is_admin != 1)
        {
            //判断APP是否具有权限登录
            if($srctype == 2)
            {
                if(($employeeinfo->authorize & \AUTHORIZE::APP) == 0)
                {
                    LogErr("this employee can not login");
                    return errcode::EMPLOYEE_NOT_LOGIN;
                }
            }
            //判断是否具有PAD端的登录权限
            if($srctype == 3)
            {
                //先判断是否有登录权限
                LogDebug($employeeinfo->authorize);
                if((($employeeinfo->authorize & \AUTHORIZE::SHOUYIN) == 0) && (($employeeinfo->authorize & \AUTHORIZE::PAD) == 0))
                {
                    LogErr("this employee can not login");
                    return errcode::EMPLOYEE_NOT_LOGIN;
                }
//                //再判断是否具有相应的操作权限
//               $pad_ret = Permission::PadCheckIsPermission($employeeinfo);
            }
            //判断PC端的登录权限
            if($srctype == 1)
            {
                if(($employeeinfo->authorize & \AUTHORIZE::PC) == 0)
                {
                    LogErr("this employee can not login");
                    return errcode::EMPLOYEE_NOT_LOGIN;
                }
                $position = \Cache\Position::Get($shop_id, $employeeinfo->position_id);

                if(($position->position_permission & \Position::ALLBACKSTAGE) == 0)
                {
                    LogErr("this employee permission is not enough");
                    return errcode::USER_PERMISSION_ERR;
                }
            }

            if($employeeinfo->is_freeze == EmployeeFreeze::FREEZE)
            {
                LogErr("this employee is freeze");
                return errcode::EMPLOYEE_IS_FREEZE;
            }
        }

        // 有权限
        LogDebug("permission ok");
        return 0;
    }
    //检测PAD用户是否且有相应权限
    public static function PadUserPermissionCheck($permission_value, $employee_info=null)
    {
        $ret = Permission::PadCheckIsPermission($permission_value, $employee_info);
        if(0 != $ret)
        {
            echo json_encode((object)array('ret'=> $ret));
            LogErr("permission err, username:" . \Cache\Login::GetUsername());
            exit(0);
        }
    }
    //检测pad用户是否且有相应的操作权限
    public static function PadCheckIsPermission($permission_value, $employee_info=null)
    {
        $loginuser = \Cache\Login::UserInfo();
        $shop_id   = \Cache\Login::GetShopId();
        LogDebug($shop_id);
        if(!$shop_id)
        {
            LogErr("no shop_id");
            return errcode::SHOP_NOT_WEIXIN;
        }
        if(!$employee_info)
        {
            if(!$loginuser->userid)
            {
                LogErr("user no login");
                return errcode::USER_NOLOGIN;
            }
            // 当前登录用户
            $userid       = $loginuser->userid;
            $employee = \Cache\Employee::GetInfo($userid, $shop_id);
            if(!$employee->employee_id)
            {
                LogErr("no employeeinfo:[$employee->userid]");
                return errcode::USER_NOLOGIN;
            }
        }else{
            $employee = $employee_info;
        }


        //获取权限信息
        $positioninfo = \Cache\Position::Get($shop_id,$employee->position_id);
        //LogDebug($positioninfo->position_permission);
        //LogDebug($permission_value);
         //判断是否具有该权限操作
        if($employee->is_admin !=1)//超级管理员是不需要权限
        {
            if(($positioninfo->position_permission & \Position::ALLWEB) == 0)
            {
                //LogDebug((int)(($positioninfo->position_permission & $permission_value) == 0));
                if(($positioninfo->position_permission & $permission_value) == 0)
                {
                    LogErr("this employee permission is can not user this play");
                    return errcode::USER_PERMISSION_ERR;
                }
            }
        }


        // 有权限
        LogDebug("permission ok");
        return 0;
    }
}// end of class Permission {...
?>
