<?php
/*
 * [Rocky 2017-06-17 23:14:54]
 * 员工管理
 *
 */
require_once("current_dir_env.php");

// 页面中变量
$html = (object)array(
    'title' => '客户管理'
);
?><?php /******************************以下为html代码******************************/?>
<?php require("template/pageheader.php"); ?>

<script type="text/javascript">
$(function() {
    var EmployeeList = new function(){
        var THIS = this;
        var $doing = $("#id_1497713338");

        function Delete()
        {
            var $all_button = $("");

            var userid_list = $("#id_employee_list_1497712653 .line .chk .value:checked").GetCheckBoxVal();

            if(userid_list.length == 0)
            {
                $doing.html("请先钩选删除的数据").SetErrStyle();
                return;
            }

            $all_button.Disabled(true);
            $doing.html("正在删除...").show().SetPromptStyle();

            Util.EncSubmit("employee_save.php",
                {
                    user_id_list : $.Json.toStr(userid_list),
                    del_employee : true
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        $all_button.Disabled(false);
                        return;
                    }
                    UpdateUi(resp.data);
                    $all_button.Disabled(false);
                    $doing.fadeOut(1000);
                }
            );

            function UpdateUi()
            {
                for(var i in userid_list)
                {
                    var userid = userid_list[i];
                    var $line = $("#id_line_" + userid);
                    Util.DeleteTableLineStyle($line);
                }
            }
        }

        function CreateList(list)
        {
            console.log(list);
            if(!list)
            {
                return;
            }
            var data = {
                list : list
            }
            // 后台数据转为前台数据
            for(i in list)
            {
                var item = list[i];
                item.duty_txt = EmployeeDuty.toString(item.duty);
            }
            var html = template('tp_employee_list', data).trim();
            $("#tp_employee_list").prevAll(".line").remove();
            var $tr_ary = $(html).insertBefore("#tp_employee_list");

            $tr_ary.find(".userid").click(function(){
                var userid = $(this).text();
                window.EmployeeEdit.Open(userid);
            })
        }

        function ReLoad()
        {
            var $all_button = $("");
            $all_button.Disabled(true);
            //console.log($all_button);
            $doing.html("正在加载数据...").show().SetPromptStyle();

            Util.EncSubmit("employee_get.php",
                {
                    'list'  : true,
                    'user': $("#id_query_1454244045 .userid .value").Value(),
                },
                function(resp){
                console.log(resp);
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        $all_button.Disabled(false);
                        return;
                    }
                    SetUi(resp.data);
                    $all_button.Disabled(false);
                    $doing.html("");
                }
            );

            function SetUi(data)
            {
                CreateList(data.list);
            }
        }
        THIS.ReLoad = ReLoad;

        function Init()
        {
            ReLoad();
            $.get('employee_edit.html?'+Util.GetTimestamp(), function(resp){
                $("#id_employee_edit_box_1497713288").html($(resp));
            });
        }

        // 新建
        $("#id_ctrl_1497713261 .bt_new").click(function(){
            window.EmployeeEdit.Open();
        })

        // 删除
        $("#id_ctrl_1497713261 .bt_del").click(function(){
            Delete();
        })

        $("#id_query_1454244045 .bt_query").click(function(){
            ReLoad();
        })

        Init();
    }//end of var EmployeeList = new function(...
    window.EmployeeList = EmployeeList;
});
</script>
<!-- <style type="text/css">
#id_query_1454244045 {
    margin: 1rem;
    height: 2rem;
}
#id_query_1454244045 .value {
    width: 10rem;
    padding: 0.2rem;
    font-size: 1.6rem;
}
#id_query_1454244045 .userid,
#id_query_1454244045 .phone {
    margin-right: 1.5rem;
}
#id_query_1454244045 .bt_query {
    font-size: 1.4rem;
}
</style>
<div id="id_query_1454244045">
    <span class="userid">客户ID: <input type="text" class="value" /></span>
    <span class="phone">手机号: <input type="text" class="value" id="id_1479618919" /></span>
    <input type="button" class="bt_query" value="查询"/>
</div> -->


<style type="text/css">
#id_employee_list_1497712653 {
    margin-top: 2rem;
}
#id_employee_list_1497712653 td {
    padding: 0.2rem;
}
#id_employee_list_1497712653 input {
    margin-right: 0.6rem;
}
#id_employee_list_1497712653 .chk{
    width: 1rem;
}
</style>
<table id="id_employee_list_1497712653" class="table_hover table_head" border="1" width="99%">
    <tr class="list_head">
        <th class="chk"><input type="checkbox" class="value" /></th>
        <th class="userid">用户id</th>
        <th class="real_name">姓名</th>
        <th class="employee_no">工号</th>
        <th class="duty">职务</th>
        <th class="permission hide">权限</th>
    </tr>
    <!-- 模板 -->
    <script id="tp_employee_list" type="text/html">
    {{each list as item i}}
        <tr class="line">
            <td class="chk"><input type="checkbox" class="value" value="{{item.userid}}" /></td>
            <td class="userid hand">{{item.userid}}</td>
            <td class="real_name">{{item.real_name}}</td>
            <td class="employee_no">{{item.employee_no}}</td>
            <td class="duty">{{item.duty_txt}}</td>
            <td class="permission hide">{{item.permission}}</td>
        </tr>
    {{/each}}
    </script>
</table>


<br/>

<style type="text/css">
#id_ctrl_1497713261 {
    margin-top: 10px;
}
#id_ctrl_1497713261 .bt_new,
#id_ctrl_1497713261 .bt_del,
#id_ctrl_1497713261 .prev {
    margin-right: 5px;
}
</style>
<div id="id_ctrl_1497713261" class="txt_right txt_left" style="margin:5px;">
<span id="id_1497713338" class="msg align_left">&nbsp;</span>
<input type="button" class="bt_new" value="创建" />
<input type="button" class="bt_del" value="删除" />
</div>


<div id="id_employee_edit_box_1497713288"></div>

</body>
</html>
