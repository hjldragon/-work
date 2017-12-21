<?php
/*
 * [Rocky 2017-05-31 01:10:04]
 * 打印机列表
 *
 */
require_once("current_dir_env.php");

// if(!PageUtil::LoginCheck())
// {
//     LogDebug("not login, token:{$_['token']}");
//     PageUtil::PageLocation("login.php");
//     exit(0);
// }

// 页面中变量
$html = (object)array(
    'title' => '打印机列表'
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
    var PrinterList = new function(){
        var THIS = this;
        var $doing = $("#id_1496164652");

        function Delete()
        {
            var $all_button = $("");
            var id_ary = [];
            var $del_tr_ary = {};

            $("#id_printer_list_1496164314 .line").each(function(){
                var $this = $(this);
                var $chk = $this.find(".chk .value");
                if(!$chk.IsChecked())
                {
                    return;
                }
                var printer_id = $chk.val();
                id_ary.push(printer_id);
                $del_tr_ary[printer_id] = $this;
            })

            if(id_ary.length == 0)
            {
                return;
            }

            $all_button.Disabled(true);
            $doing.html("正在删除...").show().SetPromptStyle();

            Util.EncSubmit("printer_save.php",
                    {
                        printer_id_list : $.Json.toStr(id_ary),
                        del             : true
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

            function UpdateUi(data)
            {
                for(var i in $del_tr_ary)
                {
                    var $item = $del_tr_ary[i];
                    Util.DeleteTableLineStyle($item);
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
            for(var i in list)
            {
                var item = list[i];
                if("" == item.printer_id || "" == item.printer_name)
                {
                    continue;
                }

                var food_category_list = [];
                for(var j in item.food_category_map)
                {
                    var food_category = item.food_category_map[j];
                    console.log(food_category);
                    food_category_list.push(food_category);
                }
                data.list.push({
                    printer_id         : item.printer_id,
                    printer_name       : item.printer_name,
                    printer_category   : PrinterCategory.toString(item.printer_category),
                    printer_size       : PrinterSize.toString(item.printer_size),
                    printer_brand      : item.printer_brand,
                    food_category_list : food_category_list,
                    printer_note       : item.printer_note,
                });
            }
                console.log(data);
            var html = template('tp_printer_list', data).trim();
            $("#tp_printer_list").prevAll(".line").remove();
            var $tr_ary = $(html).insertBefore("#tp_printer_list");
            $tr_ary.find(".open_edit_box").click(function(){
                var printer_id = Util.FindDataElemValue($(this), "printer_id");
                window.PrinterEdit.Open(printer_id);
            })
            return $tr_ary;
        }

        function ReLoad()
        {
            var $all_button = $("");
            $all_button.Disabled(true);
            $doing.html("正在加载数据...").show().SetPromptStyle();

            Util.EncSubmit("printer_get.php",
                {
                    list  : true,
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
            $.get('printer_edit.html?'+Util.GetTimestamp(), function(resp){
                $("#id_printer_edit_box_1496166005").html($(resp));
            });
        }

        // 新建
        $("#id_ctrl_1496164952 .bt_new").click(function(){
            window.PrinterEdit.Open();
        })

        // 删除
        $("#id_ctrl_1496164952 .bt_del").click(function(){
            Delete();
        })

        Init();
    }//end of var PrinterList = new function(...
    window.PrinterList = PrinterList;
});
</script>


<body>


<style type="text/css">
#id_printer_list_1496164314 {
    margin-top: 2rem;
}
#id_printer_list_1496164314 td {
    padding: 0.2rem;
}
#id_printer_list_1496164314 input {
    margin-right: 0.6rem;
}
#id_printer_list_1496164314 .chk {
    width: 1rem;
}
#id_printer_list_1496164314 .line .food_category .food_category_list {
    width: 30rem;
    margin: 0 auto;
}
#id_printer_list_1496164314 .line .food_category .category_name {
    margin: 0.1rem;
    padding: 1px;
    background-color: #cacaca;
    white-space:nowrap;
}
</style>
<table id="id_printer_list_1496164314" class="table_hover table_head" border="1" width="99%">
    <tr class="list_head">
        <th class="chk"></th>
        <th class="printer_id">打印机ID</th>
        <th class="printer_name">名称</th>
        <th class="printer_category">类别</th>
        <th class="food_category">在当前打印机上<br/>打印的餐品分类</th>
        <th class="printer_size">规格</th>
        <th class="printer_brand">型号</th>
        <th class="printer_note">备注</th>
    </tr>
    <!-- 模板 -->
    <script id="tp_printer_list" type="text/html">
    {{each list as item i}}
        <tr class="line hand" data-printer_id="{{item.printer_id}}">
            <td class="chk"><input type="checkbox" class="value" value="{{item.printer_id}}"/></td>
            <td class="printer_id open_edit_box">{{item.printer_id}}</td>
            <td class="printer_name open_edit_box">{{item.printer_name}}</td>
            <td class="printer_category open_edit_box">{{item.printer_category}}</td>
            <td class="food_category open_edit_box">
                <div class="food_category_list">
                {{each item.food_category_list as food_category i}}
                    <span class="category_name">{{food_category.category_name}}</span>
                {{/each}}
                </div>
            </td>
            <td class="printer_size open_edit_box">{{item.printer_size}}</td>
            <td class="printer_brand open_edit_box">{{item.printer_brand}}</td>
            <td class="printer_note open_edit_box">{{item.printer_note}}</td>
        </tr>
    {{/each}}
    </script>
</table>


<br/>

<style type="text/css">
#id_ctrl_1496164952 {
    margin-top: 10px;
}
#id_ctrl_1496164952 .bt_new,
#id_ctrl_1496164952 .bt_del,
#id_ctrl_1496164952 .prev,
#id_ctrl_1496164952 .next {
    margin-right: 5px;
}
</style>
<div id="id_ctrl_1496164952" class="txt_right txt_left" style="margin:5px;">
<span id="id_1496164652" class="msg align_left">&nbsp;</span>
<input type="button" class="bt_new" value="创建" />
<input type="button" class="bt_del" value="删除" />
<!-- <input type ="button" class="prev" value="上一页" />
<input type ="button" class="next" value="下一页" />
<span class="pagesize hide">5</span>
<span class="pageno">1</span><span class="set_pagesize hand" title="点击设置每页显示行数">/</span><span class="total">1</span> -->
</div>


<div id="id_printer_edit_box_1496166005"></div>

</body>
</html>
