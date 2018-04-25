<?php
/*
 * [Rocky 2017-06-01 02:31:25]
 * 客户管理
 *
 */
require_once("current_dir_env.php");

// 页面中变量
$html = (object)array(
    'title' => '客户管理'
);
?><?php /******************************以下为html代码******************************/?>
<?php require("template/pageheader.php"); ?>
<script src="3rd/artTemplate/template.js"></script>
<style type="text/css">
table{
    text-align: center;
    margin: auto;
}
</style>
<script type="text/javascript">
$(function() {
    var CustomerList = new function(){
        var THIS = this;
        var $doing = $("#id_1496255526");

        function Delete()
        {
            var $all_button = $("");

            var customer_id_list = $("#id_customer_list_1496164314 .line .chk .value:checked").GetCheckBoxVal();

            if(customer_id_list.length == 0)
            {
                $doing.html("请先钩选删除的数据").SetErrStyle();
                return;
            }

            $all_button.Disabled(true);
            $doing.html("正在删除...").show().SetPromptStyle();

            Util.EncSubmit("customer_save.php",
                {
                    customer_id_list : $.Json.toStr(customer_id_list),
                    del_customer     : true
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
                for(var i in customer_id_list)
                {
                    var customer_id = customer_id_list[i];
                    var $line = $("#id_line_" + customer_id);
                    Util.DeleteTableLineStyle($line);
                }
            }
        }

        function CreateList(list)
        {
            if(!list)
            {
                return;
            }
            var data = {
                list : []
            }
            // 后台数据转为前台数据
            for(i in list)
            {
                var item = list[i];
                if("" == item.customer_id)
                {
                    continue;
                }
                data.list.push({
                    customer_id : item.customer_id,
                    phone       : item.phone,
                    is_vip      : IsVipCustomer.toString(item.is_vip),
                    customer_name: item.customer_name
                });
            }
            var html = template('tp_customer_list', data).trim();
            $("#tp_customer_list").prevAll(".line").remove();
            var $tr_ary = $(html).insertBefore("#tp_customer_list");
            $tr_ary.find(".customer_id").click(function(){
                var customer_id = $(this).text();
                window.CustomerEdit.Open(customer_id);
            })
            return $tr_ary;
        }

        function ReLoad()
        {
            var $all_button = $("");
            $all_button.Disabled(true);
            $doing.html("正在加载数据...").show().SetPromptStyle();

            Util.EncSubmit("customer_get.php",
                {
                    'list'  : true,
                    'customer_id': $("#id_query_1454244045 .customer_id .value").Value(),
                    'customer_name': $("#id_query_1454244045 .customer_name .value").Value(),
                    'phone': $("#id_query_1454244045 .phone .value").Value()
                },
                function(resp){
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
            $.get('customer_edit.html?'+Util.GetTimestamp(), function(resp){
                $("#id_customer_edit_box_1496166005").html($(resp));
            });
        }

        // 新建
        $("#id_ctrl_1496256803 .bt_new").click(function(){
            window.CustomerEdit.Open();
        })

        // 删除
        $("#id_ctrl_1496256803 .bt_del").click(function(){
            Delete();
        })

        $("#id_query_1454244045 .bt_query").click(function(){
            ReLoad();
        })

        Init();
    }//end of var CustomerList = new function(...
    window.CustomerList = CustomerList;
});
</script>


<body>
<table border="0" class="txt_left" width="98%"><tr><td>



<style type="text/css">
#id_query_1454244045 {
    margin: 1rem;
    height: 2rem;
}
#id_query_1454244045 .value {
    width: 10rem;
    padding: 0.2rem;
    font-size: 1.6rem;
}
#id_query_1454244045 .customer_id,
#id_query_1454244045 .phone {
    margin-right: 1.5rem;
}
#id_query_1454244045 .bt_query {
    font-size: 1.4rem;
}
</style>
<div id="id_query_1454244045">
    <span class="customer_id">客户ID: <input type="text" class="value" /></span>
    <span class="customer_name">客户姓名: <input type="text" class="value" /></span>
    <span class="phone">手机号: <input type="text" class="value" id="id_1479618919" /></span>
    <input type="button" class="bt_query" value="查询"/>
</div>


<style type="text/css">
#id_customer_list_1496164314 {
    margin-top: 2rem;
}
#id_customer_list_1496164314 td {
    padding: 0.2rem;
}
#id_customer_list_1496164314 input {
    margin-right: 0.6rem;
}
#id_customer_list_1496164314 .chk{
    width: 1rem;
}
</style>
<table id="id_customer_list_1496164314" class="table_hover table_head" border="1" width="99%">
    <tr class="list_head">
        <th class="chk"><input type="checkbox"/></th>
        <th class="customer_id">客户ID</th>
        <th class="customer_name">客户名称</th>
        <th class="phone">手机号</th>
        <th class="is_vip">是否会员</th>
    </tr>
    <!-- 模板 -->
    <script id="tp_customer_list" type="text/html">
    {{each list as item i}}
        <tr class="line" id="id_line_{{item.customer_id}}">
            <td class="chk"><input type="checkbox" class="value" value="{{item.customer_id}}"/></td>
            <td class="customer_id hand">{{item.customer_id}}</td>
            <td class="customer_name">{{item.customer_name}}</td>
            <td class="phone">{{item.phone}}</td>
            <td class="is_vip">{{item.is_vip}}</td>
        </tr>
    {{/each}}
    </script>
</table>


<br/>

<style type="text/css">
#id_ctrl_1496256803 {
    margin-top: 10px;
}
#id_ctrl_1496256803 .bt_new,
#id_ctrl_1496256803 .bt_del,
#id_ctrl_1496256803 .prev {
    margin-right: 5px;
}
</style>
<div id="id_ctrl_1496256803" class="txt_right txt_left" style="margin:5px;">
<span id="id_1496255526" class="msg align_left">&nbsp;</span>
<!--<input type="button" class="bt_new" value="创建" />-->
<input type="button" class="bt_del" value="删除" />
</div>


<div id="id_customer_edit_box_1496166005"></div>

</tr></td></table>
</body>
</html>
