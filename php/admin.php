<?php
/*
 * [rockyshi 2014-09-28 12:31:00]
 * 管理员
 */
require_once("current_dir_env.php");

// 页面变量
$html = (object)array(
    'title' => '管理员'
);
LogDebug($html);
?><?php /******************************以下为html代码******************************/?>
<?php require("template/pageheader.php"); ?>
<script src="3rd/artTemplate/template.js"></script>
<style type="text/css">
#id_body_1411880670 .file_name{
    width: 360px;
}
.filelist ul{
    list-style:none;
    cursor:pointer;
    margin:2px;
    padding:1px;
    float:left;
}
.filelist li{
    margin:1px;
}
.filelist li:hover {
    background:#CCCCCC;
}
.filelist .selected {
    background:#CCCCCC;
}
/*#id_body_1411880670 .xxx,.log,.sysinfo,.admin,.mysql,.mongodb,.user,.file,.xxx div{
    padding:10px;
    min-height: 200px;
}*/
</style>
<script type="text/javascript">
$(function() {

    $("#id_index_1495733175 .item").hide();
    $("#id_index_1495733175 .logout").show();
    $("#id_tabbox_1495436780").hide();

    // 权限检测
    Util.EncSubmit("permission_check.php",
        {
            check : true,
            login : 1,
            admin : 1
        },
        function(resp){
            if(resp.ret < 0)
            {
                $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                return;
            }
            if(0 == resp.data.check)
            {
                $("#id_tabbox_1495436780").show();
            }
        }
    );

    var $doing = $('#id_doing_1411910108');

    var log_info = new function()
    {
        var THIS = this;

        THIS.GetLogInfo = function()
        {
            $doing.html("正在获取信息...").SetPromptStyle();
            Util.EncSubmit("cfg_info.php",
                {
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        return;
                    }

                    // 日志路径
                    $('#id_log_path_1400125044').html(resp.data.log.path);

                    // 日志级别显示
                    resp.data.log.level = resp.data.log.level || "0";
                    $("input[name='radio_log_level'][value=" + resp.data.log.level + "]").attr("checked",'checked');
                    $doing.html("");
                }
            );
        }

        THIS.GetLogContent = function()
        {
            var $doing = $("#id_doing_1412784091");
            if(THIS.is_get_content)
            {
                $doing.html("正在获取信息, 请稍候...").SetPromptStyle();
                return;
            }
            THIS.is_get_content = true;
            $doing.html("正在获取信息...").SetPromptStyle();
            Util.EncSubmit("log_show.php",
                                {
                                    logsize     : $("#id_log_sho_size").val()||10,
                                    get         : "content"
                                },
                                function(resp){
                                    THIS.is_get_content = false;
                                    if(resp.ret < 0)
                                    {
                                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                                        return;
                                    }
                                    $("#id_log_content").html(resp.data.content);
                                    $doing.html("");
                                });
        }

        function SaveLogPath()
        {
            var $this = $("#id_log_path_1400125044");
            var $doing = $("#id_1400128996");
            var logpath = $this.text().trim();
            if($this.data("logpath") == logpath)
            {
                $doing.html("");
                $(this).attr('contenteditable', false);
                return; // 值没变动
            }

            $doing.html("正在保存...").show().SetPromptStyle();
            Util.EncSubmit("log_save.php",
                    {
                        path  : logpath,
                        save  : 'path'
                    },
                    function(resp){
                        $(this).attr('contenteditable', false);
                        if(resp.ret < 0)
                        {
                            $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                            return;
                        }
                        $doing.fadeOut(1000);
                        $this.data("logpath", logpath); // 记录原值
                    }
            );
        }

        $("#id_log_show").click(function(){
            $(this).hide();
            $("#id_log_show_panel").show();
        });

        $("#id_log_refresh,#id_log_show").click(function(){
            THIS.GetLogContent();
        });

        $("#id_log_close").click(function(){
            $("#id_log_show_panel").hide();
            $("#id_log_show").show();
            THIS.is_get_content = false;
        });

        $("#id_log_clear").click(function(event) {
            var $doing = $("#id_doing_1412784091");
            $doing.html("正在处理...").show().SetPromptStyle();
            Util.EncSubmit("log_save.php",
                    {
                        path  : $("#id_log_path_1400125044").text().trim(),
                        save  : 'clear'
                    },
                    function(resp){
                        $(this).attr('contenteditable', false);
                        if(resp.ret < 0)
                        {
                            $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                            return;
                        }
                        $doing.fadeOut(1000);
                        $("#id_log_content").html("");
                    }
            );
        });

        // 日志路径
        $("#id_log_path_1400125044").on({
            dblclick: function(){
                var $this = $(this);
                $this.attr('contenteditable', true);
                $this.data("logpath", $this.text().trim()); // 记录原值
                $("#id_1400128996").html("可编辑").show();
            },
            blur: function()
            {
                SaveLogPath();
                var $this = $(this);
                var $doing = $("#id_1400128996");
                var logpath = $this.text().trim();
                if($this.data("logpath") == logpath)
                {
                    $doing.html("");
                    $(this).attr('contenteditable', false);
                    return; // 值没变动
                }

                $doing.html("正在保存...").show().SetPromptStyle();
                Util.EncSubmit("log_save.php",
                        {
                            path  : logpath,
                            save  : 'path'
                        },
                        function(resp){
                            $(this).attr('contenteditable', false);
                            if(resp.ret < 0)
                            {
                                $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                                return;
                            }
                            $doing.fadeOut(1000);
                            $this.data("logpath", logpath); // 记录原值
                        }
                );
            }
        }).Enter(function(){
            SaveLogPath();
            return false;
        });

        // 日志级别
        //$("input[name='radio_log_level']:checked").val()
        $("input[name='radio_log_level']").click(function(){
            var $doing = $("#id_1399986374");
            $doing.html("正在保存...").show().SetPromptStyle();
            Util.EncSubmit("log_save.php",
                    {
                        level  : $(this).val(),
                        save  : 'level'
                    },
                    function(resp){
                        $(this).attr('contenteditable', false);
                        if(resp.ret < 0)
                        {
                            $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                            return;
                        }
                        $doing.fadeOut(1000);
                    }
            );
        });
    }

    var sys_info = new function()
    {
        var THIS = this;

        THIS.GetSysInfo = function()
        {
            $doing.html("正在获取信息...").SetPromptStyle();
            Util.EncSubmit("sys_info.php",
                                {
                                },
                                function(resp){
                                    if(resp.ret < 0)
                                    {
                                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                                        return;
                                    }
                                    //
                                    $("#id_body_1411880670 .sysinfo").html(resp.data.info);
                                    $doing.html("");
                                });
        }

    }

    var db_info = new function()
    {
        var THIS = this;

        THIS.GetDbInfo = function()
        {
            $doing.html("正在获取信息...").SetPromptStyle();
            Util.EncSubmit("cfg_info.php",
                {
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        return;
                    }
                    // //
                    // $("#id_body_1411880670 .mysql_host").val(resp.data.db.mysql.host);
                    // $("#id_body_1411880670 .mysql_port").val(resp.data.db.mysql.port);
                    // $("#id_body_1411880670 .mysql_user").val(resp.data.db.mysql.user);
                    // $("#id_body_1411880670 .mysql_passwd").val(resp.data.db.mysql.passwd);
                    // $("#id_body_1411880670 .mysql_dbname").val(resp.data.db.mysql.dbname);
                    // $("#id_body_1411880670 .mysql_charset").val(resp.data.db.mysql.charset);
                    //
                    $("#id_body_1411880670 .mongodb_host").val(resp.data.db.mongodb.host);
                    $("#id_body_1411880670 .mongodb_port").val(resp.data.db.mongodb.port);
                    $("#id_body_1411880670 .mongodb_user").val(resp.data.db.mongodb.user);
                    $("#id_body_1411880670 .mongodb_passwd").val(resp.data.db.mongodb.passwd);
                    $("#id_body_1411880670 .mongodb_dbname").val(resp.data.db.mongodb.dbname);
                    $doing.html("");
                }
            );
        }

        THIS.Save = function()
        {
            $doing.html("正在保存...").SetPromptStyle();
            Util.EncSubmit("cfg_save.php",
                {
                    // //
                    // mysql_host      : $("#id_body_1411880670 .mysql_host").val(),
                    // mysql_port      : $("#id_body_1411880670 .mysql_port").val(),
                    // mysql_user      : $("#id_body_1411880670 .mysql_user").val(),
                    // mysql_passwd    : $("#id_body_1411880670 .mysql_passwd").val(),
                    // mysql_dbname    : $("#id_body_1411880670 .mysql_dbname").val(),
                    // mysql_charset   :  $("#id_body_1411880670 .mysql_charset").val(),
                    //
                    mongodb_host    : $("#id_body_1411880670 .mongodb_host").val(),
                    mongodb_port    : $("#id_body_1411880670 .mongodb_port").val(),
                    mongodb_user    : $("#id_body_1411880670 .mongodb_user").val(),
                    mongodb_passwd  : $("#id_body_1411880670 .mongodb_passwd").val(),
                    mongodb_dbname  : $("#id_body_1411880670 .mongodb_dbname").val(),
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        return;
                    }
                    //
                    $doing.html("");
                }
            );
        }
    }

    var user_info = new function(){
        $("#id_user_1411880499").click(function(){
            if(!this.userinfo)
            {
                $.get('admin.user.html?'+Util.GetTimestamp(), function(resp){
                    $(resp).appendTo($("#id_body_1411880670 .user"));
                });
                this.userinfo = true;
            }
            else
            {
                window.UserInfo.Reload();
            }
        });
    }//end of var user_info = new function(...

    var file_info = new function()
    {
        var THIS = this;
        var $li_selected = null;

        // 保存当前文件
        THIS.Save = function()
        {
            var filename = $("#id_body_1411880670 .file_name").val();
            if(filename === "")
            {
                $doing.html("没有文件变动").SetErrStyle();
                return;
            }
            //console.log('filename', filename);

            $doing.html("正在获取文件列表...").SetPromptStyle();
            Util.EncSubmit("file_info.php",
                            {
                                userid      : window.PageStore.GetLoginUserId(),
                                username    : window.PageStore.GetLoginUserName(),
                                filename    : filename,
                                content     : $("#id_body_1411880670 .file_content").val(),
                                need_bak    : $("#id_body_1411880670 .file_need_bak").IsChecked() ? 1 : 0,
                                filesave    : 1
                            },
                            function(resp){
                                if(resp.ret < 0)
                                {
                                    $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                                    return;
                                }
                                $doing.html("");
                            });
        }

        // 取文件列表
        THIS.GetFileList = function(path)
        {
            path = path || ".";
            //console.log("path", path);


            $doing.html("正在文件列表...").SetPromptStyle();
            $("#id_body_1411880670 .file_content").val("");
            // $("#id_body_1411880670 .file_name").val("正在文件列表...");
            Util.EncSubmit("file_info.php",
                            {
                                path        : path,
                                filelist    : 1
                            },
                            function(resp){
                                if(resp.ret < 0)
                                {
                                    $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                                    return;
                                }
                                RefreshList(resp.data.list);
                                $("#id_body_1411880670 .file_name").val(resp.data.path);
                                $doing.html("");
                            }
            );
            // filelist[xxx] => stdClass Object
            //     (
            //         [dev] => 2054
            //         [ino] => 921890
            //         [mode] => 41471
            //         [nlink] => 1
            //         [uid] => 1000
            //         [gid] => 1000
            //         [rdev] => 0
            //         [size] => 37
            //         [atime] => 1412011282
            //         [mtime] => 1410966560
            //         [ctime] => 1411579342
            //         [blksize] => 4096
            //         [blocks] => 0
            //         [filename] => x.html
            //         [filetype] => file|dir
            //         [path] => ./text/x.html
            //     )
            function RefreshList(filelist)
            {
                // //console.log("filelist", $.Json.toStr(filelist));
                var $ul = $("#id_filelist_1412178390 ul");
                $ul.children("li").remove();
                for(var i=0; i<filelist.length; i++)
                {
                    // //console.log("data.type", data.type, "data.filename", data.filename);
                    var data = filelist[i];
                    var filename = data.filename;
                    if("." == filename)
                    {
                        $("#id_body_1411880670 .file_content").val(data.content);
                        $("#id_body_1411880670 .file_size").text(data.size);
                        $("#id_body_1411880670 .file_mtime").text(Util.TimeTo(data.mtime, "yyyy-MM-dd.hh:mm:ss"));
                        continue;
                    }
                    else if(".." == filename)
                    {
                        filename = "<i><b>[上级目录]</b></i>";
                    }
                    else if("dir" == data.filetype)
                    {
                        filename = "<b>" + filename + "</b>";
                    }
                    var $li = $("<li>").data("data",  data)
                                       .click(function(){   // 点击菜单项时执行的动作
                                            var data = $(this).data("data") || {};
                                            //console.log("data.filetype", data.filetype);
                                            if(data.filetype == 'dir')
                                            {
                                                THIS.GetFileList(data.path);
                                            }
                                            else if(data.filetype == 'file')
                                            {
                                                GetFileContent(data);
                                            }
                                            if($li_selected)
                                            {
                                                $li_selected.removeClass('selected');
                                            }
                                            $li_selected = $(this).addClass('selected');
                                        })
                                       .html(filename);
                    $ul.append($li);
                }
            }
        }

        // 取文件内容
        function GetFileContent(file)
        {
            // //console.log(file.filetype, file.filename);
            if(file.filetype != "file")
            {
                return;
            }
            $doing.html("正在打开文件...").SetPromptStyle();
            $("#id_body_1411880670 .file_content").val("");
            Util.EncSubmit("file_info.php",
                            {
                                userid      : window.PageStore.GetLoginUserId(),
                                username    : window.PageStore.GetLoginUserName(),
                                filename    : file.path,
                                fileinfo    : 1
                            },
                            function(resp){
                                if(resp.ret < 0)
                                {
                                    $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                                    return;
                                }
                                if(!resp.data)
                                {
                                    $doing.html(errcode.toString(-1)).show().SetErrStyle();
                                    return;
                                }
                                // //console.log("resp.data.attr.size", resp.data.attr.size);
                                $("#id_body_1411880670 .file_content").val(resp.data.content);
                                $("#id_body_1411880670 .file_size").text(resp.data.attr.size);
                                $("#id_body_1411880670 .file_mtime").text(Util.TimeTo(resp.data.attr.mtime, "yyyy-MM-dd hh:mm:ss"));
                                if(resp.data.is_read)
                                {
                                    $("#id_body_1411880670 .file_is_r").addClass("green");
                                }
                                else
                                {
                                    $("#id_body_1411880670 .file_is_r").removeClass("green");
                                }
                                if(resp.data.is_write)
                                {
                                    $("#id_body_1411880670 .file_is_w").addClass("green");
                                }
                                else
                                {
                                    $("#id_body_1411880670 .file_is_w").removeClass("green");
                                }
                                if(resp.data.is_exec)
                                {
                                    $("#id_body_1411880670 .file_is_x").addClass("green");
                                }
                                else
                                {
                                    $("#id_body_1411880670 .file_is_x").removeClass("green");
                                }
                                $("#id_body_1411880670 .file_name").val(file.path);
                                $doing.html("");
                            });
        }
    }

    var shop_info = new function()
    {
        $("#id_shop_1411880499").click(function(){
            if(window.ShopInfo)
            {
                window.ShopInfo.Reload();
                return;
            }
            if(!this.shop)
            {
                $.get('admin.shop.html?'+Util.GetTimestamp(), function(resp){
                    $(resp).appendTo($("#id_body_1411880670 .shop"));
                });
                this.shop = true;
            }
            if(!this.edit)
            {
                $.get('admin.shop_edit.html?'+Util.GetTimestamp(), function(resp){
                    $(resp).appendTo($("#id_body_1411880670 .shop"));
                });
                this.edit = true;
            }
        });
    }

    $("#id_log_1411880499").click(function(event) {
        log_info.GetLogInfo();
    });

    $("#id_sysinfo_1411880499").click(function(event) {
        sys_info.GetSysInfo();
    });

    $("#id_mysql_1411880499, #id_mongodb_1411880499").click(function(event) {
        db_info.GetDbInfo();
    });

    $("#id_ok_1412052378, #id_ok_1412052379").click(function(event) {
        db_info.Save();
    });

    $("#id_file_1411880499").click(function()
    {
        file_info.GetFileList();
    });

    $("#id_body_1411880670 .file_save").click(function()
    {
        file_info.Save();
    });

    var tab = Util.TabBox($("#id_tabbox_1495436780"));
    var $prev_tab = null;
    $("#id_tabbox_1495436780 .tabbox_head label").click(function(event){
        var $click_tab = $(this);
        if($prev_tab)
        {
            $prev_tab.css({"background":"", "color":""});
        }
        $click_tab.css({"background":"#ACACAC", "color":"#005E04"});
        $prev_tab = $click_tab;
    });
    var id = $.query.get("tab") || "id_log_1411880499";
    if(id)
    {
        tab.Select($("#" + id));
        $("#" + id).trigger('click');
    }
    window.Tab = tab;
});
</script>


<body>
    <style type="text/css">
    #id_tabbox_1495436780 {
        margin-top: 1rem;
    }
    </style>
    <div id="id_tabbox_1495436780" class="tabbox">
        <style type="text/css">
        #id_tabbox_1495436780 .tabbox_head label {
            background: #CCCCCC;
            color: #909090;
            padding: 5px 9px 5px 5px;
            margin: 5px;
            margin-right: 0px;
        }
        #id_tabbox_1495436780 .tabbox_head .space{
            position: relative;
            height: 6px;
            margin: 5px;
            top: -5px;
            background: #ACACAC;
        }
        </style>
        <div class="tabbox_head">
            <label for="id_log_1411880499">
                <input type="radio" class="tabbox_head_bt7 selected" name="name_1411880499" id="id_log_1411880499" checked>日志</label>
            <label for="id_sysinfo_1411880499">
                <input type="radio" class="tabbox_head_bt1 selected" name="name_1411880499" id="id_sysinfo_1411880499" checked>系统信息</label>
            <!--label for="id_admin_1411880499">
                <input type="radio" class="tabbox_head_bt2" name="name_1411880499" id="id_admin_1411880499" />管理员设置</label-->
            <!-- <label for="id_mysql_1411880499">
                <input type="radio" class="tabbox_head_bt3" name="name_1411880499" id="id_mysql_1411880499" />mysql数据库设置</label> -->
            <label for="id_mongodb_1411880499">
                <input type="radio" class="tabbox_head_bt4" name="name_1411880499" id="id_mongodb_1411880499" />mongodb数据库设置</label>
            <label for="id_user_1411880499">
                <input type="radio" class="tabbox_head_bt5" name="name_1411880499" id="id_user_1411880499" />用户管理</label>
            <label for="id_shop_1411880499">
                <input type="radio" class="tabbox_head_bt8" name="name_1411880499" id="id_shop_1411880499" />店铺管理</label>
            <label for="id_file_1411880499">
                <input type="radio" class="tabbox_head_bt6" name="name_1411880499" id="id_file_1411880499" />文件编辑</label>
            <span id="id_doing_1411910108"></span>
            <div class="space"></div>
        </div>

        <style type="text/css">
        #id_body_1411880670 {
            border:1px dashed green;
            margin:0.40823rem;
            padding: 1.07442rem;
            min-height:40.0751rem;
            /*max-height:500px;*/
            overflow-x:hidden;
            overflow-y:auto;
        }
        </style>
        <div id="id_body_1411880670">

            <div class="tabbox_body8 shop" style="display:none;">
                <!-- 动态 -->
            </div>

            <div class="tabbox_body7 log line_break" style="display:none;">
                级别：
                      <input type="radio" name="radio_log_level" id="radio_log_level0" value="0"><label for="radio_log_level0">关闭</label>
                      <input type="radio" name="radio_log_level" id="radio_log_level1" value="1"><label for="radio_log_level1">错误</label>
                      <input type="radio" name="radio_log_level" id="radio_log_level2" value="2"><label for="radio_log_level2">信息</label>
                      <input type="radio" name="radio_log_level" id="radio_log_level3" value="3"><label for="radio_log_level3">调试</label>
                      <span id="id_1399986374" class="msg"></span>
                <br>
                路径：
                      <span id="id_log_path_1400125044" title="双击可编辑"><?=$html->log->path?></span> <span id="id_1400128996" class="msg"></span>
                <br>
                内容：
                      <input type="button" id="id_log_show" value="显示"/>
                      <span id="id_log_show_panel" style="display:none">
                        <input type="button" id="id_log_refresh" value="刷新"/>
                        <input type="button" id="id_log_close" value="关闭"/>
                        <input type="button" id="id_log_clear" value="清除"/>
                        显示长度:<input id="id_log_sho_size" size="2" value="10"/>KB
                        <span id="id_doing_1412784091"></span>
                      <div id="id_log_content" style="border:1px solid #000; min-height:30rem; max-height:50rem; overflow-x:hidden; overflow-y:auto; word-break: break-all; word-wrap:break-word;" contenteditable=false></div>
                      </span>
            </div>

            <div class="tabbox_body1 sysinfo line_break" style="display:none;">
            </div>

            <div class="tabbox_body2 admin" style="display:none">
                　　账号：<input type="text" class="admin_user" value="admin" /> <br>
                　　密码：<input type="text" class="admin_passwd" value="123456" /> <br>
                密码提示：<input type="text" class="passwd_prompt" value="1******" /> <br>
                <input type="button" id="id_ok_1412052367" value="保存" />
            </div>

            <div class="tabbox_body3 mysql" style="display:none">
                主机：<input type="text" class="mysql_host" value="<?=$html->mysql->host?>" /> <br>
                端口：<input type="text" class="mysql_port" value="<?=$html->mysql->port?>" /> <br>
                用户：<input type="text" class="mysql_user" value="<?=$html->mysql->user?>" /> <br>
                密码：<input type="text" class="mysql_passwd" value="<?=$html->mysql->passwd?>" /> <br>
                库名：<input type="text" class="mysql_dbname" value="<?=$html->mysql->dbname?>" /> <br>
                编码：<input type="text" class="mysql_charset" value="<?=$html->mysql->charset?>" /> <br>
                <input type="button" id="id_ok_1412052378" value="保存" disabled="disabled" />
            </div>

            <div class="tabbox_body4 mongodb" style="display:none;">
                主机：<input type="text" class="mongodb_host" value="<?=$html->mongodb->host?>" /> <br>
                端口：<input type="text" class="mongodb_port" value="<?=$html->mongodb->port?>" /> <br>
                用户：<input type="text" class="mongodb_user" value="<?=$html->mongodb->user?>" /> <br>
                密码：<input type="text" class="mongodb_passwd" value="<?=$html->mongodb->passwd?>" /> <br>
                库名：<input type="text" class="mongodb_dbname" value="<?=$html->mongodb->dbname?>" /> <br>
                <input type="button" id="id_ok_1412052379" value="保存" disabled="disabled" />
            </div>

            <div class="tabbox_body5 user" style="display:none;">
                <!-- 动态 -->
            </div>

            <div class="tabbox_body6 file" style="display:none; padding:5px 2px 0px 2px; height:400px;">
                当前：<input class="file_name" />
                <input type="button" class="file_save" value="保存" />
                <label for="id_bak_1412417133"><input type="checkbox" class="file_need_bak" id="id_bak_1412417133" checked />备份</label>
                <div style="color:gray; margin:2px; float:right;">
                    <span class="gray1" title="权限">
                        <span class="file_is_r" title="r:可读">r</span><span class="file_is_w" title="w:可写">w</span><span class="file_is_x" title="x:可执行">x</span>
                    </span>
                    <span class="file_size bg_gray1" title="文件大小()字节">0</span>
                    <span class="file_mtime bg_gray1" title="最后修改">0</span>
                </div>
                <br>
                <div class="line_not_break">
                    <div id="id_filelist_1412178390"
                         class="filelist"
                         style="height: 340px; width:250px; margin-top:5px; float:left; padding:2px; border:1px solid green;overflow-x:auto; overflow-y:auto;">
                        <ul>
                        </ul>
                    </div>
                    <div style="float:left;border:0px solid green; margin-top:5px; margin-left:1px; height:340px;">
                        <textarea class="file_content" style="width:530px; height:100%; padding:2px;"></textarea>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
