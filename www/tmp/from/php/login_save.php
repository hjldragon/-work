<?php
require_once("current_dir_env.php");
require_once("mgo_login.php");
require_once("mgo_user.php");
require_once("redis_login.php");
require_once("redis_id.php");
require_once("mgo_employee.php");
require_once("mgo_position.php");
require_once("mgo_department.php");
require_once("mgo_user.php");
require_once("ChuanglanSmsHelper/ChuanglanSmsApi.php");
require_once("/www/public.sailing.com/php/send_sms.php");
require_once("/www/wx.jzzwlcm.com/WxUtil.php");
require_once("mgo_env.php");
require_once("/www/public.sailing.com/php/mgo_resources.php");
require_once("/www/public.sailing.com/php/mgo_term_binding.php");
use \Pub\Mongodb as Mgo;
function Login(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $token        = $_["token"];
    $phone        = $_["phone"];
    $password_md5 = $_["password_md5"];
    $srctype      = $_['srctype'];
    //$password_md5 = md5($_["password_md5"]);//<<<<<<<<<<接口测试用的
    $page_code    = strtolower($_['page_code']);
    $no_need_code = $GLOBALS["no_need_code"];
    $no_code      = $_['no_code'];
    $logininfo = (object)array();
    if(empty($phone))
    {
        LogErr("param err, phone empty");
        return errcode::USER_NAME_EMPTY;
    }

     if(!$no_need_code && !$no_code)
     {
         $db = new \DaoRedis\Login();
         $radis = $db->Get($token);
         $radis_code = $radis->page_code;
         if($radis_code != $page_code){
             LogErr("coke err");
             return errcode::COKE_ERR;
         }
     }
    $user     = new \DaoMongodb\User;
    $employee = new DaoMongodb\Employee;
    $weixin   = new \DaoMongodb\Weixin;
    $position = new \DaoMongodb\Position;
    $userinfo = $user->QueryUser($phone, $phone, $password_md5, UserSrc::SHOP);
    if(null == $userinfo)
    {
        LogErr("user is password err");
        return errcode::USER_PASSWD_ERR;
    }
    $time = time();
    $resources = new Mgo\Resources;
    //需要找出只有一个店铺的情况
    $employee_user = $employee->GetEmployeeById($userinfo->userid);
    if($srctype)
    {
        if(count($employee_user) == 1)
        {
            $employee_one = $employee->QueryByUserId($employee_user[0]->shop_id,$userinfo->userid);

            $shop     = new \DaoMongodb\Shop;
            $shopinfo = $shop->GetShopById($employee_one->shop_id);
            //店铺是否被冻结
            if($shopinfo->is_freeze == \EmployeeFreeze::FREEZE)
            {
                LogErr("shop freeze");
                return errcode::SHOP_IS_FREEZE;
            }
            if($employee_one->is_admin != 1)
            {
                //员工是否被冻结
                if($employee_one->is_freeze == \EmployeeFreeze::FREEZE)
                {
                    LogErr("employee freeze");
                    return errcode::EMPLOYEE_IS_FREEZE;
                }
                $resources_info = $resources->GetList(
                    [
                        'shop_id'        => $employee_one->shop_id,
                        'resources_type' => $srctype,
                        'login'          => 1 // 登录
                    ]
                );
                if(empty($resources_info))
                {
                    LogErr("resources not enough");
                    return errcode::RESOURCES_NOT_ENOUGH;
                }
                //取出该设备上一次登录的资源
                foreach ($resources_info as $value)
                {
                    if($value->term_id == $token)
                    {
                        $resources_id = $value->resources_id;
                        break;
                    }
                }
                //取出新的资源
                if(!$resources_id)
                {
                    foreach ($resources_info as $v)
                    {
                        if($v->last_use_time < $time - 90)
                        {
                            $resources_id = $v->resources_id;
                            break;
                        }
                    }
                }
                if(!$resources_id)
                {
                    LogErr("resources not enough");
                    return errcode::RESOURCES_NOT_ENOUGH;
                }
                $term =  new Mgo\TermBinding;
                $term_info = $term->QueryByEmployeeId($employee_one->employee_id);
                if($term_info->term_binding_id && $term_info->term_id != $token)
                {
                    LogErr($employee_one->employee_id."not binging term");
                    return errcode::NOT_BIND_TERM;
                }
                $terminfo = $term->GetTermById($token);
                if(!empty($terminfo->employee_id) && $terminfo->employee_id != $employee_one->employee_id)
                {
                    LogErr($employee_one->employee_id."not binging user");
                    return errcode::NOT_BIND_USER;
                }

                $res_entry = new Mgo\ResourcesEntry;
                $res_entry->resources_id  = $resources_id;
                $res_entry->last_use_time = $time;
                $res_entry->term_id       = $token;

                $ret = $resources->Save($res_entry);
                if(0 != $ret)
                {
                    LogErr("resources save err");
                    return errcode::SYS_ERR;
                }
                $term_entry = new Mgo\TermBindingEntry;
                $term_id = $term_info->term_id;
                if(!$term_id)
                {
                    $term_id = $token;
                    $term_entry->ctime = $time;
                }
                $term_entry->shop_id         = $employee_one->shop_id;
                $term_entry->term_id         = $term_id;
                $term_entry->term_type       = $srctype;
                $term_entry->is_login        = 1;
                $term_entry->login_time      = $time;
                $term_entry->employee_id     = $employee_one->employee_id;

                $ret = $term->Save($term_entry);
                if(0 != $ret)
                {
                    LogErr("term_binding save err");
                    return errcode::SYS_ERR;
                }
                $position_info = $position->GetPositionById($employee_one->shop_id,$employee_one->position_id);
                //收银机的所有权限
                if( $position_info->position_permission['020101'] == 0 && $position_info->position_permission['020102'] == 0 &&
                    $position_info->position_permission['020103'] == 0 && $position_info->position_permission['020104'] == 0 &&
                    $position_info->position_permission['020105'] == 0 && $position_info->position_permission['020106'] == 0 &&
                    $position_info->position_permission['020107'] == 0 && $position_info->position_permission['020108'] == 0 &&
                    $position_info->position_permission['020109'] == 0 && $position_info->position_permission['020110'] == 0 &&
                    $position_info->position_permission['020111'] == 0 && $position_info->position_permission['020112'] == 0 &&
                    $position_info->position_permission['020113'] == 0 && $position_info->position_permission['020114'] == 0 &&
                    $position_info->position_permission['020115'] == 0 && $position_info->position_permission['020116'] == 0 &&
                    $position_info->position_permission['020117'] == 0 && $position_info->position_permission['020118'] == 0 &&
                    $position_info->position_permission['020119'] == 0 && $position_info->position_permission['020120'] == 0 &&
                    $position_info->position_permission['020121'] == 0 &&
                    $position_info->position_permission['020201'] == 0 && $position_info->position_permission['020202'] == 0 &&
                    $position_info->position_permission['020203'] == 0 && $position_info->position_permission['020204'] == 0 &&
                    $position_info->position_permission['020205'] == 0 &&
                    $position_info->position_permission['020301'] == 0 && $position_info->position_permission['020302'] == 0 &&
                    $position_info->position_permission['020303'] == 0 && $position_info->position_permission['020304'] == 0 &&
                    $position_info->position_permission['020305'] == 0 && $position_info->position_permission['020306'] == 0 &&
                    $position_info->position_permission['020307'] == 0 && $position_info->position_permission['020308'] == 0 &&
                    $position_info->position_permission['020401'] == 0 &&
                    $position_info->position_permission['020501'] == 0 && $position_info->position_permission['020502'] == 0 &&
                    $position_info->position_permission['020503'] == 0 && $position_info->position_permission['020504'] == 0
                ){
                    LogDebug('no permission');
                    return errcode::USER_PERMISSION_ERR;
                }
            }
            $is_overdue = 0;
            //过期时间小于1周的资源
            $overdue_resources = $resources->GetResourcesList(
                [
                    'shop_id'   => $employee_one->shop_id,
                    'end_time'  => $time + 7*24*60*60
                ]
            );
            if(count($overdue_resources)>0)
            {
                $is_overdue = 1;
            }
        }
    }

    $src         = Src::SHOP;
    $type        = 3;//<<<<<<<<<<<<<<因为之前做的只能默认为3了.
    $weixin_info = $weixin->UserIdAndSrctype($userinfo->userid, $type, $src);
    if($weixin_info->srctype == SrcType::APP)
    {
        $userinfo->is_weixin = 1;
    }else{
        $userinfo->is_weixin = 0;
    }
    $ret = LoginSave($userinfo, $token, $resp);
    if(0 != $ret)
    {
        LogErr("Login err");
        return errcode::SYS_ERR;
    }
    if(isset($is_overdue))
    {
        $resp->is_overdue = $is_overdue;
    }
    return 0;
}

function LoginWxApp(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token   = $_['token'];
    $code    = $_['code'];
    $srctype = $_['srctype'];
    if(!$code)
    {
        LogErr("no code");
        return errcode::PARAM_ERR;
    }
    $env_id = EnvId::APP;
    //找到app配置的数据
    $env      = new \DaoMongodb\Env;
    $env_info = $env->QueryByEnvId($env_id);
    //LogDebug($env_info);
    //先获取用户openid
    $req = \Pub\Wx\Util::GetAppOpenid($env_info->appid, $env_info->secret, $code);
    //LogDebug($req);
    if(!$req->openid)
    {
        LogErr("weixin code err:$req->errcode");
        return errcode::PARAM_ERR;
    }
    $openid = $req->openid;

    $src    = BindingSrc::SHOP;//来源是属于商户
    $weixin = \Cache\Weixin::Get($openid, $src);
    $userid = $weixin->userid;
    if(!$userid)
    {
        LogErr("WeixinUser err");
        return errcode::WEIXIN_NO_LOGIN;
    }
    $userinfo = \Cache\UsernInfo::Get($userid);
    if(null == $userinfo)
    {
        LogErr("user is password err");
        return errcode::USER_PASSWD_ERR;
    }

    $weixin      = new \DaoMongodb\Weixin;
    $src         = Src::SHOP;
    $weixin_info = $weixin->UserIdAndSrctype($userinfo->userid, $srctype, $src);
    if($weixin_info->srctype == SrcType::APP)
    {
        $userinfo->is_weixin = 1;
    }else{
        $userinfo->is_weixin = 0;
    }

    $ret      = LoginSave($userinfo, $token, $resp);
    if(0 != $ret)
    {
        LogErr("Login err");
        return errcode::SYS_ERR;
    }
    return 0;
}

function LoginWx(&$resp)
{
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token  = $_["token"];
    $userid = $_['user_id'];
    if(!$userid)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $userinfo = \Cache\UsernInfo::Get($userid);
    LogDebug($userinfo);
    $ret = LoginSave($userinfo, $token, $resp);
    if(0 != $ret)
    {
        LogErr("Login err");
        return errcode::SYS_ERR;
    }
    return 0;
}

function LoginSave($userinfo, $token, &$resp)
{
    $employee = (object)array();
    $shopinfo = array();
    // 再查员工表
    $mgo          = new \DaoMongodb\Shop;
    $employee_mgo = new \DaoMongodb\Employee;
    $position     = new \DaoMongodb\Position;
    $employee = $employee_mgo->GetEmployeeById($userinfo->userid);
    foreach ($employee as $key => $value) {
        if($value->shop_id){
           $shop = $mgo->GetShopById($value->shop_id);
           //$shop =  \Cache\Shop::Get($value->shop_id);
           if($shop->shop_id){
               $shop_info['shop_id']        = $shop->shop_id;
               $shop_info['shop_name']      = $shop->shop_name;
               $shop_info['shop_logo']      = $shop->shop_logo;
               $shop_info['is_freeze']      = $shop->is_freeze;
               if($value->is_admin != 1){
                   $position_info                    = $position->GetPositionById($shop->shop_id, $value->position_id);
                   $department                       = new \DaoMongodb\Department;
                   $department_info                  = $department->QueryByDepartmentId($shop->shop_id, $value->department_id);
                   $shop_info['position_name']       = $position_info->position_name;
                   $shop_info['position_permission'] = $position_info->position_permission;
                   $shop_info['department_name']     = $department_info->department_name;
                   $shop_info['is_myshop']           = 0;
                   //是否是管理员0:不是,1:是
                   $shop_info['employee_is_admin']   = 0;
               }else{
                   $shop_info['position_name']  = '超级管理员';
                   $shop_info['department_name']= '--';
                   $shop_info['is_myshop']      = 1;
                   //是否是管理员
                   $shop_info['employee_is_admin'] = 1;
               }
           	 array_push($shopinfo, $shop_info);
           }
        }
    }
    $mongodb = new \DaoMongodb\Login();
    $entry   = new \DaoMongodb\LoginEntry();

    $now = time();
    $entry->id          = \DaoRedis\Id::GenLoginId();
    $entry->userid      = $userinfo->userid;
    $entry->ip          = $_SERVER['REMOTE_ADDR'];
    $entry->login_time  = $now;
    $entry->logout_time = 0;
    $entry->ctime       = $now;
    $entry->mtime       = $now;
    $entry->delete      = 0;
    $ret = $mongodb->Save($entry);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }

    $redis = new \DaoRedis\Login();
    $info  = new \DaoRedis\LoginEntry();

    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->userid   = $userinfo->userid;
    $info->username = $userinfo->username;
    $info->shop_id  = '';
    $info->login    = 1;
    LogDebug($info);

    $ret = $redis->Save($info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }

    $userinfo->answer   = null;
    $userinfo->password = null;

    $resp = (object)array(
        'logininfo'    => $entry,
        'userinfo'     => $userinfo,
        'shopinfo'     => $shopinfo,
    );
    //LogDebug($resp);
    LogInfo("login ok, userid:{$userinfo->userid}");
    return 0;
}

function LoginShop(&$resp){
    $_ = $GLOBALS["_"];
    LogDebug($_);
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token    = $_["token"];
    $shop_id  = $_['shop_id'];
    $srctype  = $_['srctype'];
    $userid   = \Cache\Login::GetUserid();
    LogDebug($userid);
    $shop     = new \DaoMongodb\Shop;
    $shopinfo = $shop->GetShopById($shop_id);
    //$shopinfo = \Cache\Shop::Get($shop_id);
    $mgo      = new \DaoMongodb\Employee;
    $employee = $mgo->QueryByShopId($userid, $shop_id);
    $time = time();
    //店铺是否被冻结
    if($shopinfo->is_freeze == \EmployeeFreeze::FREEZE)
    {
        LogErr("shop freeze");
        return errcode::SHOP_IS_FREEZE;
    }
    //员工是否被冻结
    if($employee->is_freeze == \EmployeeFreeze::FREEZE && $employee->is_admin != 1)
    {
        LogErr("employee freeze");
        return errcode::EMPLOYEE_IS_FREEZE;
    }
    $resources = new Mgo\Resources;
    //判断该店铺是否拥有足够登录授权数及是否绑定终端
    if($employee->is_admin != 1 && $srctype)
    {
        LogDebug($srctype);
        $resources_info = $resources->GetList(
            [
                'shop_id'        => $shop_id,
                'resources_type' => $srctype,
                'login'          => 1 // 登录
            ]
        );

       if(empty($resources_info))
        {
            LogErr("resources not enough");
            return errcode::RESOURCES_NOT_ENOUGH;
        }
        //取出该设备上一次登录的资源
        foreach ($resources_info as $value)
        {
            if($value->term_id == $token)
            {
                $resources_id = $value->resources_id;
                break;
            }
        }
        //取出新的资源
        if(!$resources_id)
        {
            foreach ($resources_info as $v)
            {
                if($v->last_use_time < $time - 90)
                {
                    $resources_id = $v->resources_id;
                    break;
                }
            }
        }
        if(!$resources_id)
        {
            LogErr("resources not enough");
            return errcode::RESOURCES_NOT_ENOUGH;
        }

        $term =  new Mgo\TermBinding;
        $term_info = $term->QueryByEmployeeId($employee->employee_id);
        if($term_info->term_id && $term_info->term_id != $token)
        {
            LogErr($employee->employee_id."not binging term");
            return errcode::NOT_BIND_TERM;
        }
        $terminfo = $term->GetTermById($token);
        if(!empty($terminfo->employee_id) && $terminfo->employee_id != $employee->employee_id)
        {
            LogErr($employee->employee_id."not binging user");
            return errcode::NOT_BIND_USER;
        }

        $res_entry = new Mgo\ResourcesEntry;
        $res_entry->resources_id  = $resources_id;
        $res_entry->last_use_time = $time;
        $res_entry->term_id       = $token;
        $ret = $resources->Save($res_entry);
        if(0 != $ret)
        {
            LogErr("resources save err");
            return errcode::SYS_ERR;
        }
        $term_entry = new Mgo\TermBindingEntry;
        $term_id = $term_info->term_id;
        if(!$term_id)
        {
            $term_id = $token;
            $term_entry->ctime = $time;
        }
        $term_entry->shop_id         = $shop_id;
        $term_entry->term_id         = $term_id;
        $term_entry->term_type       = $srctype;
        $term_entry->is_login        = 1;
        $term_entry->login_time      = $time;
        $term_entry->employee_id     = $employee->employee_id;
        $ret = $term->Save($term_entry);
        if(0 != $ret)
        {
            LogErr("term_binding save err");
            return errcode::SYS_ERR;
        }
    }
    $overdue_resources = $resources->GetResourcesList(//过期时间小于1周的资源
            [
                'shop_id'   => $shop_id,
                'end_time'  => $time + 7*24*60*60
            ]);
    if(count($overdue_resources)>0)
    {
        $is_overdue = 1;
    }
    else
    {
        $is_overdue = 0;
    }
    //用于判断手机app端的权限登录
    // if($srctype == 2)
    // {
    //     if(($employee->authorize & 8) == 0 && $employee->is_admin != 1)
    //     {
    //         LogErr("employee authorize:".$employee->authorize);
    //         return errcode::EMPLOYEE_NOT_LOGIN;
    //     }
    // }else{
    //     //员工是否具有pc端登录权限(位运算1:pc,2:pad,4:收银,8:app)
    //     if(($employee->authorize & 1) == 0 && $employee->is_admin != 1)
    //     {
    //         LogErr("employee authorize:".$employee->authorize);
    //         return errcode::EMPLOYEE_NOT_LOGIN;
    //     }
    // }

    //判断该登录用户是否有权限
    $redis         = new \DaoRedis\Login();
    $info          = new \DaoRedis\LoginEntry();
    $info->token   = $token;
    $info->userid  = $userid;
    $info->shop_id = $shop_id;
    $info->login   = 1;
    $redis->Save($info);
    $resp = (object)array(
        'employeeinfo' => $employee,
        'shopinfo'     => $shopinfo,
        'is_overdue'   => $is_overdue
    );
    return 0;
}

function Logout(&$resp)
{
    $_ = $GLOBALS["_"];
    if(!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token = $_["token"];

    $redis = new \DaoRedis\Login();
    $info  = new \DaoRedis\LoginEntry();

    // 注：$info->key字段在终端初次提交时设置
    $info->token    = $token;
    $info->login    = 0;
    $info->userid   = 0;
    LogDebug($info);
    $ret = $redis->Save($info);
    if(0 != $ret)
    {
        LogErr("SaveKey err");
        return errcode::SYS_ERR;
    }
    $term =  new Mgo\TermBinding;
    $term_info = $term->GetTermById($token);
    if($term_info->term_id)
    {
        $term_entry = new Mgo\TermBindingEntry;
        $term_entry->term_id  = $token;
        $term_entry->is_login = 0;
        LogDebug($term_entry);
        $ret = $term->Save($term_entry);
        if(0 != $ret)
        {
            LogErr("term_binding save err");
            return errcode::SYS_ERR;
        }
    }

    $resp = (object)array(
    );
    LogInfo("logout ok, token:{$token}");
    return 0;
}
//发送图形手机验证码
function GetCoke(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token = $_['token'];
    $phone = $_['phone'];
    $name  = $_['name'];
    $mgo   = new \DaoMongodb\User;
    // 查找手机号码
    $ret = $mgo->QueryByPhone($phone,UserSrc::SHOP);
    //LogDebug($ret);
    switch ($name)
    {
        case 'forgetPwd':
            if(!$ret->phone){
                return errcode::USER_NOT_ZC;
            }
            break;
        case 'registrate':
            if ($ret->phone) {
                return errcode::PHONE_IS_EXIST;
            }
            break;
        default:
            return errcode::PARAM_ERR;
            break;
    }
    $page_code  = strtolower($_['page_code']);
    $db         = new \DaoRedis\Login;
    $redis      = $db->Get($token);
    $radis_code = $redis->page_code;
    //验证验证码
    if ($radis_code != $page_code)
    {
        LogErr("coke err");
        return errcode::COKE_ERR;
    }

    if (!preg_match('/^\d{11}$/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }
    $code   = mt_rand(100000, 999999);
    Sms::GetSms($phone,$code);//发送手机验证码
    $redis            = new \DaoRedis\Login();
    $data             = new \DaoRedis\LoginEntry();
    $data->phone      = $phone;
    $data->token      = $token;
    $data->phone_code = $code;
    $data->code_time  = time() + 5 * 60 * 1000;
    LogDebug($data);
    $redis->Save($data);
    $resp = (object)[
        //'phone_code' => $code,
    ];
    return 0;
}
//无图形验证码发送手机验证码
function GetPhoneCode(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token      = $_['token'];
    $phone      = $_['phone'];
    $user       = new \DaoMongodb\User;
    if(!$_['srctype'])
    {
        $userinfo   = $user->QueryByPhone($phone, UserSrc::SHOP);
        if(!$userinfo->phone)
        {
            LogDebug("not user message");
            return errcode::USER_NOT_ZC;
        }
    }
    if (!preg_match('/^\d{11}$/', $phone))
    {
        LogErr("phone err");
        return errcode::PHONE_ERR;
    }
    $code   = mt_rand(100000, 999999);
    Sms::GetSms($phone,$code);//发送手机验证码
    $redis            = new \DaoRedis\Login();
    $data             = new \DaoRedis\LoginEntry();
    $data->phone      = $phone;
    $data->token      = $token;
    $data->phone_code = $code;
    $data->code_time  = time() + 5 * 60;
    //LogDebug($data);
    $redis->Save($data);
    $resp = (object)[
        //'phone_code' => $code,
    ];
    return 0;
}
//忘记密码
function UserNewPasswd(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $token      = $_['token'];
    $phone      = $_['phone'];
    $mgo        = new \DaoMongodb\User;
    $user = $mgo->QueryByPhone($phone, UserSrc::SHOP);

    if(!$user->userid)
    {
        LogDebug($phone);
        return errcode::PHONE_ERR;
    }
    $phone_code = $_['phone_code'];//手机验证码
    $result     = PageUtil::VerifyPhoneCode($token, $phone, $phone_code);
    if ($result != 0)
    {
        LogDebug($result);
        return $result;
    }
    $userid = $user->userid;
    if (!$userid)
    {
        return errcode::PARAM_ERR;
    }
    $new_passwd       = $_['new_passwd'];
    $new_passwd_again = $_['new_passwd_again'];

    $user             = new \DaoMongodb\UserEntry;
    if ($new_passwd != $new_passwd_again)
    {
        LogDebug($new_passwd, $new_passwd_again, 'is not same so err');
        return errcode::PASSWORD_TWO_SAME;
    }
    $user->userid   = $userid;
    $user->password = $new_passwd;
    $ret            = $mgo->Save($user);
    if (0 != $ret)
    {
        LogErr("Save err");
        return errcode::SYS_ERR;
    }
    $resp = (object)[];
    LogInfo("save ok");
    return 0;
}
//注册账号
function UserEmployeeSetting(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $real_name = $_['real_name'];
    if (!$real_name)
    {
        LogErr("name is empty,have to");
        return errcode::PARAM_ERR;
    }
    $sex        = $_['sex'];
//    if(!$sex)
//    {
//        LogErr("sex is no,have to");
//        return errcode::PARAM_ERR;
//    }
    $health_certificate  = json_decode($_['health_certificate']);
    $identity            = $_['identity'];
    $token               = $_['token'];
    $phone               = $_['phone'];
    $phone_code          = $_['phone_code'];//手机验证码
    $result     = PageUtil::VerifyPhoneCode($token, $phone, $phone_code);
    //验证手机结果
    if ($result != 0)
    {
        return $result;
    }
    $mgo    = new \DaoMongodb\User;
    $userid = \DaoRedis\Id::GenUserId();
    // 是否已注册过
    $ret = $mgo->IsExist([
        'userid'    => $userid,
        'real_name' => $real_name,
        'phone'     => $phone,
        'src'       => UserSrc::SHOP
    ]);
    LogDebug($ret);
    if (null != $ret) {
        if ($ret->phone) {
            LogErr("phone exist:[{$ret->phone}");
            return errcode::PHONE_IS_EXIST;
        }
    }

    $passwd = $_['passwd'];
    if (!$passwd) {
        LogErr("passwd is empty,have to");
        return errcode::PARAM_ERR;
    }
    $passwd_again = $_['passwd_again'];
    if ($passwd != $passwd_again) {
        LogDebug($passwd, $passwd_again);
        return errcode::PASSWORD_TWO_SAME;
    }


    //创建生成用户表
    $user   = new \DaoMongodb\UserEntry;
    $user->userid             = $userid;
    $user->sex                = $sex;
    $user->real_name          = $real_name;
    $user->delete             = 0;
    $user->password           = $passwd;
    $user->phone              = $phone;
    $user->src                = UserSrc::SHOP;
    $user->ctime              = time();
    $user->health_certificate = $health_certificate;
    $user->identity           = $identity;
    $ret                      = $mgo->Save($user);
    if ($ret != 0) {
        LogErr("Save err, ret=[$ret]");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    return 0;
}
//app无图形验证码发送手机验证码注册账号
function GetPhoneCodeSetUser(&$resp){
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $token               = $_['token'];
    $phone               = $_['phone'];
    $phone_code          = $_['phone_code'];//手机验证码
    $new_password        = $_["new_password"];
    $password_again      = $_['password_again'];
    $result     = PageUtil::VerifyPhoneCode($token, $phone, $phone_code);
    //验证手机结果
    if ($result != 0)
    {
        return $result;
    }
    $mgo    = new \DaoMongodb\User;
    $userid = \DaoRedis\Id::GenUserId();
    // 是否已注册过
    $ret = $mgo->IsExist([
        'phone'     => $phone,
        'src'       => UserSrc::SHOP
    ]);
    LogDebug($ret);
    if (null != $ret) {
        if ($ret->phone) {
            LogErr("phone exist:[{$ret->phone}");
            return errcode::PHONE_IS_EXIST;
        }
    }

    if (!$new_password) {
        LogErr("passwd is empty,have to");
        return errcode::PARAM_ERR;
    }

    if ($new_password != $password_again) {
        LogDebug($new_password, $password_again);
        return errcode::PASSWORD_TWO_SAME;
    }

    //创建生成用户表
    $user   = new \DaoMongodb\UserEntry;
    $user->userid             = $userid;
    $user->delete             = 0;
    $user->password           = $new_password;
    $user->phone              = $phone;
    $user->src                = UserSrc::SHOP;
    $user->ctime              = time();
    $ret                      = $mgo->Save($user);
    if ($ret != 0) {
        LogErr("Save err, ret=[$ret]");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
        'userid'=>$userid
    ];
    return 0;
}
//app完善账号资料
function UserSetting(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }

    $real_name = $_['real_name'];
    if (!$real_name)
    {
        LogErr("name is empty,have to");
        return errcode::PARAM_ERR;
    }
    $sex                 = $_['sex'];
    $userid              = $_['userid'];
    $health_certificate  = json_decode($_['health_certificate']);
    $identity            = $_['identity'];
    $real_name           = $_['real_name'];
    $is_weixin           = $_['is_weixin'];
    if(null == $sex || !$real_name || !$userid)
    {
        LogErr("real_name or sex or userid is empty");
        return errcode::PARAM_ERR;
    }
    $mgo    = new \DaoMongodb\User;
    $user   = new \DaoMongodb\UserEntry;
    $user->userid             = $userid;
    $user->sex                = $sex;
    $user->real_name          = $real_name;
    $user->is_weixin          = $is_weixin;
    $user->health_certificate = $health_certificate;
    $user->identity           = $identity;
    $ret                      = $mgo->Save($user);
    if ($ret != 0) {
        LogErr("Save err, ret=[$ret]");
        return errcode::SYS_ERR;
    }
    $resp = (object)[
    ];
    return 0;
}


$ret = -1;
$resp = (object)array();
if(isset($_['login']))
{
    $ret = Login($resp);
}
else if(isset($_['login_wx']))
{
    $ret = LoginWx($resp);
}
else if(isset($_['login_shop']))
{
    $ret = LoginShop($resp);
}
else if(isset($_['logout']))
{
    $ret = Logout($resp);
}elseif(isset($_['save_new_passwd']))
{
    $ret = UserNewPasswd($resp);
}elseif(isset($_['setting_user']))
{
    $ret = UserEmployeeSetting($resp);
}elseif (isset($_['get_coke'])) {

    $ret = GetCoke($resp);
}elseif (isset($_['get_phone_code'])) {

    $ret = GetPhoneCode($resp);
}else if(isset($_['login_wx_app']))
{
    $ret = LoginWxApp($resp);
}else if(isset($_['app_set_user']))
{
    $ret = GetPhoneCodeSetUser($resp);
}
else if(isset($_['app_user_setting']))
{
    $ret = UserSetting($resp);
}
else
{
    $ret = -1;
    LogErr("param err");
}

// $html = json_encode((object)array(
//     'ret' => $ret,
//     'data' => $resp
// ));
$result = (object)array(
    'ret'  => $ret,
    'msg'  => errcode::toString($ret),
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
