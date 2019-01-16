<?php
/*
 * 权限操作
 * [Rocky 2017-05-05 13:31:16]
 *
 */
class Permission {

    const CHK_LOGIN  = 0x1;         // 登录用户
    const CHK_ADMIN  = 0x2;         // 必须是管理员

    // $chk 权限值
    public static function Check($chk)
    {
        if(($chk & Permission::CHK_LOGIN) && !PageUtil::LoginCheck())
        {
            return errcode::USER_NOLOGIN;
        }
        if(($chk & Permission::CHK_ADMIN) && !Cfg::instance()->IsAdmin(\Cache\Login::GetUsername()))
        {
            return errcode::USER_PERMISSION_ERR;
        }
        return 0;
    }

    // 一般性页面权限检查
    public static function PageCheck()
    {
        $ret = Permission::Check(Permission::CHK_LOGIN);
        if(0 != $ret)
        {
            echo json_encode((object)array('ret'=> $ret));
            LogErr("permission err, username:" . \Cache\Login::GetUsername());
            exit(0);
        }
    }

}// end of class Permission {...
?>
