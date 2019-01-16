<?php
/*
 * 运营平台权限操作
 * [Rocky 2017-05-05 13:31:16]
 *
 */
class PlPermissionCheck {

    const PL_CHK_LOGIN         = 1;         // 登录用户

    // $chk 权限值
    public static function Check($chk)
    {
        if(($chk & PlPermissionCheck:: PL_CHK_LOGIN) && !PageUtil::LoginCheck())
        {
            return errcode::USER_NOLOGIN;
        }

        return 0;
    }

    // 一般性页面权限检查
    public static function PageCheck($permission)
    {
        $ret = PlPermissionCheck::Check(PlPermissionCheck::PL_CHK_LOGIN);
        if(0 != $ret)
        {
            echo json_encode((object)array('ret'=> $ret));
            LogErr("permission err, ret:$ret, userid:" . \Cache\Login::GetUserid());
            exit(0);
        }

        $ret = PlPermissionCheck::PermissionCheck($permission);
        if(0 != $ret)
        {
            echo json_encode((object)array('ret'=> $ret));
            LogErr("employee permission err or not login again");
            exit(0);
        }
    }

    //检查登陆账号是否具有相应的设置权限
    public static function PermissionCheck($permission)
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
        if(!$platformer_info->userid)
        {
            LogErr("no platformer_info:[$userid]");
            return errcode::USER_NO_EXIST;
        }

        if($platformer_info->is_admin != 1)
        {
            //1.先检测员工是否被冻结
            if($platformer_info->is_freeze == EmployeeFreeze::FREEZE)
            {
                LogErr("this platformer is freeze");
                return errcode::EMPLOYEE_IS_FREEZE;
            }
            //2.根据选择按钮判断是否具有相应的权限

            $role_ifno         = \Cache\PlRole::Get($platformer_info->pl_role_id);
            $position          = \Cache\PlPosition::Get($role_ifno->pl_position_id);
             $permission_list  = $position->pl_position_permission;
             foreach ($permission_list as $i=>$value)
             {

                 if($i == $permission && $value != 1)
                 {
                     LogDebug('permission num is'.$permission.'and the value is'.$value);
                     return errcode::USER_PERMISSION_ERR;
                 }

             }

        }
        // 有权限
        LogDebug("permission ok");
        return 0;
    }

}

class AgPermissionCheck {

    const AG_CHK_LOGIN         = 1;         // 登录用户

    // $chk 权限值
    public static function Check($chk)
    {
        if(($chk & AgPermissionCheck::AG_CHK_LOGIN) && !PageUtil::LoginCheck())
        {
            return errcode::USER_NOLOGIN;
        }

        return 0;
    }

    // 一般性页面权限检查
    public static function PageCheck($permission)
    {
        $ret = AgPermissionCheck::Check(AgPermissionCheck::AG_CHK_LOGIN);
        if(0 != $ret)
        {
            echo json_encode((object)array('ret'=> $ret));
            LogErr("permission err, ret:$ret, userid:" . \Cache\Login::GetUserid());
            exit(0);
        }

        $ret = AgPermissionCheck::PermissionCheck($permission);
        if(0 != $ret)
        {
            echo json_encode((object)array('ret'=> $ret));
            LogErr("employee permission err or not login again");
            exit(0);
        }
    }

    //检查登陆账号是否具有相应的设置权限
    public static function PermissionCheck($permission)
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
        $ag_employee     = \Cache\AgentEmployee::Get($userid);

        if(!$ag_employee->userid)
        {
            LogErr("no ag_employee:[$userid]");
            return errcode::USER_NO_EXIST;
        }
        if($ag_employee->is_admin != 1)
        {
            //1.先检测员工是否被冻结
            if($ag_employee->is_freeze == EmployeeFreeze::FREEZE)
            {
                LogErr("this platformer is freeze");
                return errcode::EMPLOYEE_IS_FREEZE;
            }
            //2.根据选择按钮判断是否具有相应的权限

            $role_info    = \Cache\AgRole::Get($ag_employee->ag_role_id);
            $position     = \Cache\AgPosition::Get($role_info->ag_position_id);
            $permission_list  = $position->ag_position_permission;
            foreach ($permission_list as $i=>$value)
            {

                if($i == $permission && $value != 1)
                {
                    LogDebug('permission nu·m is'.$permission.'and is'.$value);
                    return errcode::USER_PERMISSION_ERR;
                }

            }

        }
        // 有权限
        LogDebug("permission ok");
        return 0;
    }

}

class ShopPermissionCheck {

    const SHOP_CHK_LOGIN         = 1;         // 登录用户

    // $chk 权限值
    public static function Check($chk)
    {
        if(($chk & ShopPermissionCheck::SHOP_CHK_LOGIN) && !PageUtil::LoginCheck())
        {
            return errcode::USER_NOLOGIN;
        }

        return 0;
    }

    // 一般性页面权限检查
    public static function PageCheck($permission,$type_id=null)// type_id外包（收银机）订单接口必须这个字段返回。
    {
        LogDebug($type_id);
        $ret = ShopPermissionCheck::Check(ShopPermissionCheck::SHOP_CHK_LOGIN);
        if(0 != $ret)
        {
            echo json_encode((object)array('ret'=> $ret));
            LogErr("not login, ret:$ret, userid:" . \Cache\Login::GetUserid());
            exit(0);
        }

        $ret = ShopPermissionCheck::PermissionCheck($permission);
        if(0 != $ret)
        {
            LogDebug($type_id);
            echo json_encode((object)array('ret'=> $ret,'msg'=>errcode::toString($ret),'type_id'=>$type_id));

            LogErr("employee permission err or not login again",'type_id='.$type_id);
            exit(0);
        }
    }


    //检查登陆账号是否具有相应的设置权限
    public static function PermissionCheck($permission)
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
        $userid       = $loginuser->userid;
        $shop_id      = \Cache\Login::GetShopId();
        $employee     = \Cache\Employee::GetInfo($userid,$shop_id);
        if(!$employee->userid)
        {
            LogErr("no ag_employee:[$userid]");
            return errcode::USER_NO_EXIST;
        }

        if($employee->is_admin != 1)
        {
            //1.先检测员工是否被冻结
            if($employee->is_freeze == EmployeeFreeze::FREEZE)
            {
                LogErr("this platformer is freeze");
                return errcode::EMPLOYEE_IS_FREEZE;
            }
            //2.根据选择按钮判断是否具有相应的权限
            $position         = \Cache\Position::Get($shop_id, $employee->position_id);
            if($position->is_start == 2)
            {
                LogDebug('position is not start,'.'position_id:'.$position->position_id);
                return errcode::SHOP_PERMISSION_START;
            }
            $permission_list  = $position->position_permission;

            foreach ($permission_list as $i=>$value)
            {
                if($i == $permission && $value != 1)
                {
                    LogDebug('permission num :'.$permission.':'.$value);
                    return errcode::USER_PERMISSION_ERR;
                }

            }

        }
        // 有权限
        LogDebug("permission ok");
        return 0;
    }

}
?>
