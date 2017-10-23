<?php
/*
 * 安装脚本
 * [rockyshi 2014-03-26 12:48:47]
 *
 */
require_once("current_dir_env.php");
require_once("db_pool.php");
require_once("sqlopr.php");
require_once("dbcfg.php");
require_once("page_util.php");

LogInfo("--begin--");
LogDebug($_REQUEST);
LogDebug($GLOBALS["sys_setup"]);

// 系统已安装，跳到登录页；
if($GLOBALS["sys_setup"])
{
    PageUtil::PageLocation("login.php");
    exit;
}

function CreateDb()
{
    LogDebug("begin...");

    $mysql_cfg = $GLOBALS["dbcfg"];


    $obj = new SqlOpr($mysql_cfg->host,
                        $mysql_cfg->port,
                        $mysql_cfg->user,
                        $mysql_cfg->passwd
                    );
    $sql = "CREATE DATABASE IF NOT EXISTS $mysql_cfg->dbname";
    $ret = $obj->Exec($sql);
    if($ret < 0)
    {
        $msg = $obj->GetErrMsg();
        LogErr("create table err, ret=[$ret], msg=[$msg]");
        return $ret;
    }

    LogInfo("create table ok");
}

function CreateTable($sql)
{
    LogDebug("begin...");

    $obj = new SqlOpr( DbPool::GetDb() );
    $ret = $obj->Exec($sql);
    if($ret < 0)
    {
        $msg = $obj->GetErrMsg();
        LogErr("create table err, ret=[$ret], msg=[$msg]");
        return $ret;
    }

    LogInfo("create table ok");
    return 0;
}
function CreateCfgTable()
{
    $sql =<<<eof
CREATE TABLE IF NOT EXISTS `t_config` (
  `f_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `f_key` varchar(128) NOT NULL DEFAULT '' COMMENT '配置项',
  `f_value` varchar(256) NOT NULL DEFAULT '' COMMENT '配置值',
  `f_operator` varchar(256) NOT NULL DEFAULT '' COMMENT '操作人',
  `f_ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `f_mtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `f_delete` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0:未删除; 1:已删除',
  PRIMARY KEY (`f_id`),
  UNIQUE KEY `f_key` (`f_key`)
) ENGINE=InnoDB AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8 COMMENT='配置表'
eof;
    return CreateTable($sql);
}

function CreateUserTable()
{
    $sql =<<<eof
CREATE TABLE IF NOT EXISTS `t_user` (
  `f_userid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `f_name` varchar(64) NOT NULL DEFAULT '' COMMENT '用户名',
  `f_passwd` varchar(128) NOT NULL DEFAULT '' COMMENT '密码',
  `f_question` varchar(128) NOT NULL DEFAULT '' COMMENT '问题',
  `f_answer` varchar(128) NOT NULL DEFAULT '' COMMENT '答案',
  `f_passwd_prompt` varchar(256) NOT NULL DEFAULT '' COMMENT '密码提示',
  `f_property` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户属性(位字段，1bit:管理员)',
  `f_ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `f_mtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `f_delete` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0:未删除; 1:已删除',
  PRIMARY KEY (`f_userid`),
  UNIQUE KEY `f_name` (`f_name`)
) ENGINE=InnoDB AUTO_INCREMENT=20000 DEFAULT CHARSET=utf8 COMMENT='用户表'
eof;
    return CreateTable($sql);
}

function CreateLoginTable()
{
    $sql =<<<eof
CREATE TABLE IF NOT EXISTS `t_login` (
  `f_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '登录id',
  `f_userid` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '用户id',
  `f_key` varchar(32) NOT NULL DEFAULT '' COMMENT '登录标识(随机生成)',
  `f_ip` varchar(20) NOT NULL DEFAULT '' COMMENT '登录ip',
  `f_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `f_logout_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退出时间',
  `f_rand_passwd` varchar(128) NOT NULL DEFAULT '' COMMENT '用于前、后台加密通讯的随机密码',
  `f_ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `f_mtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `f_delete` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0:未删除; 1:已删除',
  PRIMARY KEY (`f_id`),
  UNIQUE KEY (`f_key`)
) ENGINE=InnoDB AUTO_INCREMENT=40000 DEFAULT CHARSET=utf8 COMMENT='登录信息表'
eof;
    return CreateTable($sql);
}

///////////////////////////////////// 业务表 /////////////////////////////////////
// function CreateTaskListTable()
// {
//     $sql =<<<eof
// CREATE TABLE `t_task_list` (
//   `f_task_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '任务ID',
//   `f_title` varchar(100) NOT NULL DEFAULT '' COMMENT '任务标题',
//   `f_parent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '父任务ID',
//   `f_creater` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人（任务下发者）',
//   `f_executor` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当前任务处理人',
//   `f_priority` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '优先级（1:高, 2:中, 3:低)',
//   `f_status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '状态（1:进行中, 2:未开始, 3:暂停: 4:已完成)',
//   `f_begin_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间（时间戳，秒）',
//   `f_end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间（时间戳，秒）',
//   `f_estimate_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '预估花费时间（秒）',
//   `f_completion` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '目前完成度(百分比)',
//   `f_reserve` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '预留字段（位字段，1bit:正文中有图片，2bit:有附件）',
//   `f_ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当前记录创建时间',
//   `f_mtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当前记录最后修改时间 ',
//   `f_delete` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0:未删除; 1:已删除',
//   PRIMARY KEY (`f_task_id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='任务列表表'
// eof;
//     return CreateTable($sql);
// }



// 设置初始配置
function SetDbCfg($finish)
{
    if(!$finish)
    {
        $finish = 0;
    }

    $mysql_cfg = $GLOBALS["dbcfg"];

    $db_user  = $_GET[db_user];
    if(!$db_user)
    {
        $db_user = $mysql_cfg->user;
    }

    $db_passwd  = $_GET[db_passwd];
    if(!$db_passwd)
    {
        $db_passwd = $mysql_cfg->passwd;
    }

    $db_dbname  = $_GET[db_dbname];
    if(!$db_dbname)
    {
        $db_dbname = $mysql_cfg->dbname;
    }

    $db_host  = $_GET[db_host];
    if(!$db_host)
    {
        $db_host = $mysql_cfg->host;
    }

    $db_port  = $_GET[db_port];
    if(!$db_port)
    {
        $db_port = $mysql_cfg->port;
    }

    $db_charset  = $_GET[db_charset];
    if(!$db_charset)
    {
        $db_charset = $mysql_cfg->charset;
    }


    $file = "./dbcfg.php";
    $cfg_content =<<<eof
<?php
/*
 *
 * 本文件由安装程序生成
 *
 */
\$dbcfg = (object)array(
                    'dbms'      => 'mysql',
                    'passwd'    => '{$db_passwd}',
                    'user'      => '{$db_user}',
                    'charset'   => '{$db_charset}',
                    'port'      => '{$db_port}',
                    'dbname'    => '{$db_dbname}',
                    'host'      => '{$db_host}'
);

\$sys_setup = $finish;  // 0:系统未安装（初始） 1:系统已安装

?>

eof;
    $ret = file_put_contents($file, $cfg_content);
    if("" == $ret)
    {
        LogErr("file_put_contents err, file:[$file], ret:[$ret]");
        return errcode::CFG_WRITE_ERR;
    }
    LogDebug($cfg_content);
    LogInfo("write file:[$file], ret:[$ret]");
    return 0;
}

function SetMongodbCfg()
{
    LogDebug("begin...");

    $user   = Util::EmptyToDefault($_GET[mongodb_user], "");
    $passwd = Util::EmptyToDefault($_GET[mongodb_passwd], "");
    $host   = Util::EmptyToDefault($_GET[mongodb_host], "");
    $port   = Util::EmptyToDefault($_GET[mongodb_port], "");
    $dbname = Util::EmptyToDefault($_GET[mongodb_dbname], "");

    $dao = new DaoCfg(Cfg::instance()->db->mysql);
    $ret += $dao->Set("db.mongodb.user", $user);
    $ret += $dao->Set("db.mongodb.passwd", $passwd);
    $ret += $dao->Set("db.mongodb.host", $host);
    $ret += $dao->Set("db.mongodb.port", $port);
    $ret += $dao->Set("db.mongodb.dbname", $dbname);
    if($ret != 0)
    {
        LogErr("dao->CfgEntry err, ret=[$ret]");
        return -2;
    }
    return 0;
}

function SetAdmin()
{
    LogDebug("begin...");

    $user   = Util::EmptyToDefault($_GET[admin_user], "");
    $passwd = Util::EmptyToDefault($_GET[admin_passwd], "");
    $prompt = Util::EmptyToDefault($_GET[passwd_prompt], "");

    $dao = new DaoUser(Cfg::instance()->db->mysql);
    $mod = new UserModEntry;

    $mod->SetField('name', $user);
    $mod->SetField('passwd', $passwd);
    $mod->SetField('passwd_prompt', $prompt);
    $mod->SetField('property', UserEntry::PROP_IS_ADMIN);

    $ret = $dao->Insert($mod->data);
    if($ret < 0)
    {
        $mod->SetEqCond('name', $user);
        $ret = $dao->Update($mod);
        LogErr("Insert err, ret=[$ret]");
        return $ret;
    }

    return 0;
}

function CreateAllTable(&$resp)
{
    $msg = (object)array();

    /*
     * 创建数据库
     */
    $ret = CreateDb();
    if($ret < 0)
    {
        LogErr("CreateDb err");
        $msg->db = 'err';
    }
    else
    {
        $msg->db = 'ok';
    }

    /*
     * 创建表
     */
    $ret = CreateCfgTable();
    if($ret < 0)
    {
        LogErr("CreateCfgTable err");
        $msg->table->cfg = 'err';
    }
    else
    {
        $msg->table->cfg = 'ok';
    }

    $ret = CreateUserTable();
    if($ret < 0)
    {
        LogErr("CreateUserTable err");
        $msg->table->user = 'err';
    }
    else
    {
        $msg->table->user = 'ok';
    }

    $ret = CreateLoginTable();
    if($ret < 0)
    {
        LogErr("CreateLoginTable err");
        $msg->table->login = 'err';
    }
    else
    {
      $msg->table->login = 'ok';
    }

    /////////////////////////////////////////////////

    // $ret = CreateTaskListTable();
    // if($ret < 0)
    // {
    //     LogErr("CreateTaskListTable err");
    //     $msg->table->tasklist = 'err';
    //     //return $ret;
    // }
    // else
    // {
    //   $msg->table->tasklist = 'ok';
    // }

    LogInfo("create all table finish, retsult:" . json_encode($msg));
    $resp = (object)array(
        msg => $msg
    );
    return 0;
}

//
if($_REQUEST['step'] == 1)
{
    $ret = SetDbCfg();
    $data = array(
        ret  => $ret,
        data => ''
    );
    $html = json_encode($data);
    echo $html;
    exit(0);
}
elseif($_REQUEST['step'] == 2)
{
    $resp = NULL;
    $ret = CreateAllTable($resp);
    $data = array(
        ret  => $ret,
        data => $resp->msg
    );
    $html = json_encode($data);
    echo $html;
    exit(0);
}
elseif($_REQUEST['step'] == 3)
{
    $ret = SetMongodbCfg();
    $data = array(
        ret  => $ret,
        data => ''
    );
    $html = json_encode($data);
    echo $html;
    exit(0);
}
elseif($_REQUEST['step'] == 4)
{
    $ret = SetAdmin();
    $data = array(
        ret  => $ret,
        data => ''
    );
    $html = json_encode($data);
    echo $html;
    exit(0);
}
elseif($_REQUEST['finish'])
{
    $ret = SetDbCfg(1);
    $data = array(
        ret  => $ret,
        data => ''
    );
    $html = json_encode($data);
    echo $html;
    exit(0);
}

// 页面变量
$html = (object)array(
    mysql     => Cfg::instance()->db->mysql,
    mongodb   => Cfg::instance()->db->mongodb
);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>安装</title>
<script type="text/javascript" src="./js/jquery-1.5.2.min.js"></script>
<script type="text/javascript" src="./js/jquery.jcryption.js"></script>
<script type="text/javascript" src="./js/util.js"></script>
<script type="text/javascript">
$(function() {
    $("#id_setup").click(function(){
        var THIS = this;


        // 生成mysql数据库配置文件
        function Step1()
        {
            var args = {
                step        : 1,
                db_host     : $('#db_host').val(),
                db_port     : $('#db_port').val(),
                db_user     : $('#db_user').val(),
                db_passwd   : $('#db_passwd').val(),
                db_dbname   : $('#db_dbname').val(),
                db_charset  : $('#db_charset').val()
            };
            $.getJSON('', args, function(resp) {
                if(resp.ret < 0)
                {
                    alert(resp.ret);
                    return;
                }
                $("#id_result").html($("#id_result").html() + '<br>' + '生成数据库配置文件ok')
                Step2();
            });
        }

        // 创建各个表
        function Step2()
        {
            var args = {
                step : 2
            };
            $.getJSON('', args, function(resp) {
                if(resp.ret < 0)
                {
                    alert(resp.ret);
                    return;
                }
                var msg = '<br>创建数据库' + resp.data.db
                        + '<br>创建配置表' + resp.data.table.cfg
                        + '<br>创建用户表' + resp.data.table.user
                        + '<br>登录信息表表' + resp.data.table.login
                        // + '<br>任务列表表' + resp.data.table.tasklist
                        + '';
                $("#id_result").html($("#id_result").html() + msg);
                Step3();
            });
        }

        // mongodb数据库配置
        function Step3()
        {
            var args = {
                step             : 3,
                mongodb_host     : $('#id_mongodb_1411489612 .host').val(),
                mongodb_port     : $('#id_mongodb_1411489612 .port').val(),
                mongodb_user     : $('#id_mongodb_1411489612 .user').val(),
                mongodb_passwd   : $('#id_mongodb_1411489612 .passwd').val(),
                mongodb_dbname   : $('#id_mongodb_1411489612 .dbname').val(),
                mongodb_charset  : $('#id_mongodb_1411489612 .charset').val()
            };
            $.getJSON('', args, function(resp) {
                if(resp.ret < 0)
                {
                    alert(resp.ret);
                    return;
                }
                $("#id_result").html($("#id_result").html() + '<br>' + '设置mongodb数据库配置ok')
                Step4();
            });
        }

        // 设置管理员
        function Step4()
        {
            var args = {
                step          : 4,
                admin_user    : $('#admin_user').val(),
                admin_passwd  : $('#admin_passwd').val(),
                passwd_prompt : $('#passwd_prompt').val(),
            };
            $.getJSON('', args, function(resp) {
                if(resp.ret < 0)
                {
                    alert(resp.ret);
                    return;
                }
                $("#id_result").html($("#id_result").html() + '<br>' + '设置管理员账户ok')
                Finish();
            });
        }

        // 完成
        function Finish()
        {
            var args = {
                finish : 1
            };
            $.getJSON('', args, function(resp) {
                if(resp.ret < 0)
                {
                    alert(resp.ret);
                    return;
                }
                var msg = '<br>安装完成，请<a href="login.php">登录</a>';
                $("#id_result").html($("#id_result").html() + msg);
                // setTimeout(function(){
                //     location.href = "login.php";
                // }, 3000)
            });
        }

        $("#id_result").html("");
        Step1();
    });
});
</script>
<body>

<fieldset>
<legend>管理员设置</legend>
　　账号：<input type="text" id="admin_user" value="admin" /> <br>
　　密码：<input type="text" id="admin_passwd" value="123456" /> <br>
密码提示：<input type="text" id="passwd_prompt" value="1******" /> <br>
</fieldset>

<br>

<fieldset>
<legend>mysql数据库设置</legend>
主机：<input type="text" id="db_host" value="<?=$html->mysql->host?>" /> <br>
端口：<input type="text" id="db_port" value="<?=$html->mysql->port?>" /> <br>
用户：<input type="text" id="db_user" value="<?=$html->mysql->user?>" /> <br>
密码：<input type="text" id="db_passwd" value="<?=$html->mysql->passwd?>" /> <br>
库名：<input type="text" id="db_dbname" value="<?=$html->mysql->dbname?>" /> <br>
编码：<input type="text" id="db_charset" value="<?=$html->mysql->charset?>" /> <br>
</fieldset>

<br>

<fieldset>
<legend>mongodb数据库设置</legend>
<div id="id_mongodb_1411489612">
主机：<input type="text" class="host" value="<?=$html->mongodb->host?>" /> <br>
端口：<input type="text" class="port" value="<?=$html->mongodb->port?>" /> <br>
用户：<input type="text" class="user" value="<?=$html->mongodb->user?>" /> <br>
密码：<input type="text" class="passwd" value="<?=$html->mongodb->passwd?>" /> <br>
库名：<input type="text" class="dbname" value="<?=$html->mongodb->dbname?>" /> <br>
</div>
</fieldset>

<input id="id_setup" type="button" value="开始安装">

<div id="id_result"></div>

</body>
</html>
