<?php
/*
 * [rockyshi 2014-10-04]
 * 用户信息表
 *
 */
require_once("current_dir_env.php");
require_once("mgo_user.php");
require_once("mgo_employee.php");
require_once("redis_id.php");
require_once("class.smtp.php");
require_once("class.phpmailer.php");
Permission::PageCheck();
//$_=$_REQUEST;
function GetUserList(&$resp)
{
    $_ = $GLOBALS["_"];
    //LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $dao = new \DaoMongodb\User;
    $list = $dao->GetUserList($list);
    $ret = [];
    foreach($list as $i => &$user) {
        $employee = \Cache\Employee::Get($user->userid);
        if(null == $employee)
        {
            $employee = (object)array();
        }
        array_push($ret, [
            'userid'    => $user->userid,
            'username'  => $user->username,
            'shop_id'   => $employee->shop_id,
            'shop_name' => \Cache\Shop::GetShopName($employee->shop_id),
            'ctime'     => $user->ctime,
        ]);
    }

    $resp = (object)array(
        'list' => $ret
    );
    // LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function GetUserInfo(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid = $_["user_id"];

    $dao = new \DaoMongodb\User;
    $userinfo = $dao->QueryById($userid);
    if($userinfo->userid != $userid)
    {
        LogErr("dao->QueryById err, userid=[$userid]");
        return -1;
    }

    // 是店铺用户，取店铺信息
    $shopinfo = (object)array();;
    $employee = (object)array();;
    if(($userinfo->property & UserProperty::SHOP_USER) != 0)
    {
        $employee = \Cache\Employee::Get($userinfo->userid);
        if($employee)
        {
            $shopinfo = \Cache\Shop::Get($employee->shop_id);
        }
    }

    // if("" != $userinfo->password)
    // {
    //     $userinfo->password = str_repeat("*", strlen($userinfo->password));
    // }
    $userinfo->password = "";

    $resp = (object)array(
        'logininfo' => \Cache\Login::Get(),
        'userinfo' => $userinfo,
        'shopinfo' => $shopinfo,
        'employeeinfo' => $employee,
    );
    LogDebug($resp);
    LogInfo("--ok--");
    return 0;
}

function DelUser(&$resp)
{
    Permission::AdminCheck();

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $user_id_list = json_decode($_["userlist"]);

    LogDebug($user_id_list);

    $dao = new \DaoMongodb\User;
    $ret = $dao->BatchDeleteById($user_id_list);
    if($ret < 0)
    {
        LogErr("dao->BatchDeleteById err, ret=[$ret]");
        return $ret;
    }
    LogInfo("--ok--");
    return 0;
}
// 修改或注册用户
function UserSetting(&$resp)
{
    Permission::AdminCheck();

    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid       = $_["user_id"];
    $new_username = $_["new_username"];
    $new_password = $_["new_password"];
    $new_prompt   = $_["new_prompt"];
    $phone        = $_["phone"];
    $shop_id      = (string)$_["shop_id"];

    if("" == $new_username)
    {
        LogErr("param err, new_username:[$new_username]");
        return errcode::USER_NAME_EMPTY;
    }

    if(!$userid)
    {
        $userid = \DaoRedis\Id::GenUserId();
    }

    $dao = new \DaoMongodb\User;

    // 是否已注册过
    $ret = $dao->IsExist([
        'userid' => $userid,
        'username' => $new_username,
        'phone' => $phone,
    ]);
    //LogDebug($userlist);
    if(null != $ret && $ret->userid != $userid)
    {
        if($ret->username)
        {
            LogErr("username exist:[{$ret->username}]");
            return errcode::USER_HAD_REG;
        }
        else if($ret->phone)
        {
            LogErr("phone exist:[{$ret->phone}");
            return errcode::PHONE_IS_EXIST;
        }
    }

    $entry = new \DaoMongodb\UserEntry;
    $entry->userid        = $userid;
    //$entry->shop_id       = $shop_id;
    $entry->username      = $new_username;
    $entry->password      = $new_password;
    $entry->passwd_prompt = $new_prompt;
    $entry->ctime         = time();
    $entry->delete        = 0;
    $entry->phone         = $phone;
    if($shop_id)
    {
        $entry->property |= UserProperty::SHOP_USER;
    }
    LogDebug($entry);

    $ret = $dao->Save($entry);
    if($ret < 0)
    {
        LogErr("Update err, ret=[$ret]");
        return $ret;
    }

    if($shop_id)
    {
        // 登记到员工表
        $mgo_employee = new \DaoMongodb\Employee;
        $employee_info = new \DaoMongodb\EmployeeEntry;
        $employee_info->userid = $userid;
        $employee_info->shop_id = $shop_id;
        $employee_info->duty = EmployeeDuty::SYS_SHOP_ADMIN;
        $employee_info->real_name = ""; //$shopinfo->shop_name;
        $employee_info->delete = 0;
        $employee_info->permission = EmployeePermission::AllPermission();

        $ret = $mgo_employee->Save($employee_info);
        if(0 != $ret)
        {
            LogErr("Save err");
            return $ret;
        }
    }

    $resp = (object)array(
        'userid' => $userid
    );
    LogDebug($resp);

    LogInfo("register ok, userid=[{$userid}], ip:[{$_SERVER['REMOTE_ADDR']}]");
    return 0;
}
//修改用户密码
function EditUserPassword(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userid = \Cache\Login::GetUserid();
    if (!$userid)
    {
        return errcode::USER_NOLOGIN;
    }
    $old_password       = md5($_['old_password']);
    $new_password       = $_['new_password'];
    $new_password_again = $_['new_password_again'];

    $mgo      = new \DaoMongodb\User;
    $entry    = new \DaoMongodb\UserEntry;
    $password = $mgo->QueryById($userid)->password;

    if ($old_password !== md5($password))
    {
        return errcode::USER_PASSWD_ERR;
    }
    if($password === $new_password)
    {
        return errcode::PASSWORD_TWO_SAME;
    }
    if ($new_password != $new_password_again)
    {
        return errcode::DATA_PASSWD_ERR;
    }

    $entry->userid   = $userid;
    $entry->password = $new_password;
    $ret = $mgo->Save($entry);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)[];
    LogInfo("save ok");
    return 0;

}
//绑定用户手机号码
function PhoneBind(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = \Cache\Login::GetShopId();
    $userid  = \Cache\Login::GetUserid();

    if (!$userid || !$shop_id)
    {
        return errcode::USER_NOLOGIN;
    }
    $token      = $_['token'];
    $phone      = $_['phone'];
    if(!preg_match('/^1([0-9]{9})/',$phone)){
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }
    $mgo   = new \DaoMongodb\User;
    $mgo2  = new \DaoMongodb\Shop;
    $userinfo = $mgo->QueryById($userid);
    $shopinfo = $mgo2->GetShopById($shop_id);
    //验证user表和shop表联系人的电话是否一样
    if($userinfo->phone != $shopinfo->telephone)
    {
        return errcode::DATA_CHANGE;
    }
    $info  = $mgo->QueryByPhone($phone);
    //验证手机号码是否重复
    if ($info->phone)
    {
        LogErr("phone is exist");
        return errcode::PHONE_IS_EXIST;
    }
    $code             = $_['phone_code'];//手机验证码
    $redis            = new \DaoRedis\Login();
    $data             = $redis->Get($token);//获取手机号上面的验证码
    if ($code != $data->phone_code)
    {
        LogErr("phone_code is err");
        return errcode::PHONE_COKE_ERR;
    }
    //保存绑定手机号码
    $userinfo->userid    = $userid;
    $userinfo->phone     = $phone;
    $shopinfo->shop_id   = $shop_id;
    $shopinfo->telephone = $phone;
    $ret2                = $mgo2->Save($shopinfo);
    $ret                 = $mgo->Save($userinfo);
    if (0 != $ret || 0 != $ret2)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)['bind_phone'=>"手机绑定成功"];
    LogInfo("phone binding successful");
    return 0;
}
//用户发送绑定邮箱
function BindEmail(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $email = $_['email'];
    if (!preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $email))
    {
        LogErr("email err");
        return errcode::EMAIL_ERR;
    }
    $userid  = \Cache\Login::GetUserid();
    $shop_id = \Cache\Login::GetShopId();
    //$shop_id = $_['shop_id'];
    //$userid  = $_['userid'];//<<<<<<<<<<<<<<<<<测试数据\
    if (!$userid)
    {
        return errcode::USER_NOLOGIN;
    }
    //验证邮箱是否已绑定
    $userinfo = new \DaoMongodb\User;
    $info  = $userinfo->QueryByEmail($email);
    if($info->email)
    {
        LogErr("email is exist");
        return errcode::EMAIL_IS_EXIST;
    }
    $email_url = ShopIsSuspend::MAIL_URL;
    $passwd    = md5(rand(10000, 99999));

    $mgo                = new \DaoMongodb\Shop;
    $entry              = \Cache\Shop::Get($shop_id);
    $shop_email->mail   = $_['email'];
    $shop_email->passwd = $passwd;
    $entry->mail_vali   = $shop_email;
    $entry->shop_id     = $shop_id;
    $ret                = $mgo->Save($entry);
    $url                = $email_url . 'passwd=' . $passwd . '&bind_email=1&userid=' . $userid . '&shop_id=' . $shop_id;
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    //执行邮箱发送
   $send = Cfg::GetMail($email,$url);
    if(0!=$send){
        LogDebug('send failed'.$send);
        return errcode::EMAIL_SEND_FAIL;
    }

    $resp = (object)[
        'send_email'=>"发送邮件成功",
    ];
    LogInfo("save ok");
    return 0;
}
//解绑邮箱
function UnBindEmail(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $userid  = \Cache\Login::GetUserid();
    $shop_id = \Cache\Login::GetShopId();
    //$shop_id = $_['shop_id'];
    //$userid  = $_['userid'];//<<<<<<<<<<<<<<<<<测试数据\
    if (!$userid || !$shop_id)
    {
        return errcode::USER_NOLOGIN;
    }
    $email_url = ShopIsSuspend::MAIL_URL;
    $entry     = \Cache\Shop::Get($shop_id);
    $mgo       = \Cache\UsernInfo::Get($userid);
    if ($entry->email != $mgo->email)
    {
        return errcode::FILE_WRITE_ERR;
    }
    $email = $entry->email;
    $url   = $email_url . '&unbind_email=1&userid=' . $userid . '&shop_id=' . $shop_id;
    //执行邮箱发送
    $send = Cfg::GetMail($email, $url);
    if (0 != $send) {
        LogDebug('send failed' . $send);
        return errcode::EMAIL_SEND_FAIL;
    }
    $resp = (object)[
        'send_email' => "发送邮件成功",
    ];
    LogInfo("save ok");
    return 0;
}
//解绑用户手机号码
function UnBindPhone(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $shop_id = \Cache\Login::GetShopId();
    $userid  = \Cache\Login::GetUserid();
    //$userid = $_['userid'];//<<<<<<<<<<<<<<测试数据
    if (!$userid || !$shop_id)
    {
        return errcode::USER_NOLOGIN;
    }
    $token    = $_['token'];
    $mgo      = new \DaoMongodb\User;
    $mgo2     = new \DaoMongodb\Shop;
    $userinfo = $mgo->QueryById($userid);
    $shopinfo = $mgo2->GetShopById($shop_id);
    //验证user表和shop表联系人的电话是否一样
    if ($userinfo->phone != $shopinfo->telephone)
    {
        return errcode::DATA_CHANGE;
    }

    $code             = $_['phone_code'];//手机验证码
    $redis            = new \DaoRedis\Login();
    $data             = $redis->Get($token);//获取手机号上面的验证码
    if ($code != $data->phone_code)
    {
        LogErr("phone_code is err");
        return errcode::COKE_ERR;
    }
    //保存绑定手机号码
    $userinfo->userid    = $userid;
    $userinfo->phone     = ""; //解绑手机号
    $shopinfo->shop_id   = $shop_id;
    $shopinfo->telephone = ""; //解绑手机号
    $ret2                = $mgo2->Save($shopinfo);
    $ret                 = $mgo->Save($userinfo);
    if (0 != $ret || 0 != $ret2) {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }

    $resp = (object)['unbind_phone' => "手机解绑成功"];
    LogInfo("phone binding successful");
    return 0;
}
$ret = -1;
$resp = null;
if($_["list"])
{
    $ret =  GetUserList($resp);
}
elseif($_["info"])
{
    $ret =  GetUserInfo($resp);
}
elseif($_["delete"])
{
    $ret =  DelUser($resp);
}
elseif($_["user_setting"])
{
    $ret =  UserSetting($resp);
}elseif (isset($_['save_password']))
{
    $ret = EditUserPassword($resp);
}elseif (isset($_['bind_phone']))
{
    $ret = PhoneBind($resp);
}elseif (isset($_['bind_email']))
{
    $ret = BindEmail($resp);
}elseif (isset($_['unbind_email']))
{
    $ret = UnBindEmail($resp);
}elseif (isset($_['unbind_phone']))
{
    $ret = UnBindPhone($resp);
}else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret' => $ret,
    // data  => $resp
    'crypt' => 1, // 是加密数据标记
    'data'  => PageUtil::EncRespData(json_encode($resp))
));
?><?php /******************************以下为html代码******************************/?>
<?=$html?>
