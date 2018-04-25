<?php
/*
 * [Rocky 2017-05-03 17:13:53]
 * 菜单列表
 *
 */
require_once("current_dir_env.php");

// 页面中变量
$html = (object)array(
    'title' => '菜单列表'
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
    var MenuList = new function(){
        var $doing = $("#id_1454249596");

        function Delete()
        {
            var $all_button = $("");
            var id_ary = [];
            var $del_tr_ary = {};

            $("#id_menu_list .food_line").each(function(){
                var $this = $(this);
                var $chk = $this.find(".chk .value");
                if(!$chk.IsChecked())
                {
                    return;
                }
                var food_id = $chk.val();
                id_ary.push(food_id);
                $del_tr_ary[food_id] = $this;
            })

            if(id_ary.length == 0)
            {
                return;
            }

            $all_button.Disabled(true);
            $doing.html("正在删除...").show().SetPromptStyle();

            Util.EncSubmit("menu_save.php",
                    {
                        food_id_list : $.Json.toStr(id_ary),
                        del_food     : true
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

            // {"ret":0,"data":{"newid":{"tmpid_pgjzyylzkblfoxrp":"16"},"errid":[]}}
            function UpdateUi(data)
            {
                (data.success_id||[]).forEach(function(item){
                    Util.DeleteTableLineStyle($del_tr_ary[item]);
                })
            }
        }

        function CreateList(list)
        {
            $("#tp_food_list").prevAll(".food_line").remove();
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
                if("" == item.food_id || "" == item.food_name)
                {
                    continue;
                }

                var food_img_url = "img/none.png";
                if(item.food_img_list && item.food_img_list.length > 0)
                {
                    food_img_url = "img_get.php?img=1&imgname=" + item.food_img_list[0];
                }

                var food_unit_txt = "";
                if(item.food_unit)
                {
                    food_unit_txt = " <font color=gray>(" + item.food_unit + ")</font>";
                }

                var food_price = "-";
                if(item.food_price)
                {
                    food_price = item.food_price + food_unit_txt;
                }

                var food_vip_price = "-";
                if(item.food_vip_price)
                {
                    food_vip_price = item.food_vip_price + food_unit_txt;
                }

                data.list.push({
                    food_id            : item.food_id,
                    food_name          : item.food_name,
                    food_category      : item.food_category,
                    category_name      : item.category_name,
                    food_price         : item.food_price,
                    food_vip_price     : item.food_vip_price,
                    food_price_txt     : food_price,
                    food_vip_price_txt : food_vip_price,
                    food_unit          : item.food_unit,
                    food_img_url       : food_img_url,
                    food_intro         : item.food_intro,
                    entry_time         : item.entry_time,
                    food_sold_num_day  : item.food_sold_num_day,
                    food_status_txt    : ToStatusTxt(item.sale_off),
                });
            }
            var html = template('tp_food_list', data).trim();
            var $tr_ary = $(html).insertBefore("#tp_food_list");

            $tr_ary.find(".open_edit").click(function(){
                var food_id = Util.FindDataElemValue($(this), "food_id");
                OpenFoodEditBox(food_id);
            });

            // 下架
            $tr_ary.find(".sale_off").click(function(){
                var food_id = Util.FindDataElemValue($(this), "food_id");
                SetSaleOff(food_id, FoodIsSaleOff.YES);
            });

            // 上架
            $tr_ary.find(".sale_on").click(function(){
                var food_id = Util.FindDataElemValue($(this), "food_id");
                SetSaleOff(food_id, FoodIsSaleOff.NO);
            });
        }

        function ReLoad()
        {
            var $all_button = $("#id_edit_1454045621 input");
            $all_button.Disabled(true);
            $doing.html("正在加载数据...").show().SetPromptStyle();

            var $food_id = $("#id_query_1454244045 .food_id .value");
            var $food_name = $("#id_query_1454244045 .food_name .value");
           // var $food_price = $("#id_query_1454244045 .food_price .value");
            Util.EncSubmit("menu_get.php",
                {
                    foodlist  : true,
                    food_id   : $food_id.val().trim(),
                    food_name : $food_name.val().trim(),
                    //food_price : $food_price.val().trim(),
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
                    $doing.fadeOut(1000);
                }
            );

            function SetUi(data)
            {
                CreateList(data.list);
            }
        }

        // 上、下架操作
        function SetSaleOff(food_id, is_sale_off)
        {
            var $line = $("#id_food_line_" + food_id);
            var $all_button = $line.find("button").Disabled(true);
            Util.EncSubmit("menu_save.php",
                {
                   'sale_off_opr' : true,
                   'food_id'      : food_id,
                   'is_sale_off'  : is_sale_off,
                },
                function(resp){
                    $all_button.Disabled(false);
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        return;
                    }
                    $line.find(".food_status").html(ToStatusTxt(is_sale_off));
                    $doing.html("保存成功").SetOkStyle().fadeOut(1000);
                }
            );
        }

        function ToStatusTxt(is_sale_off)
        {
            is_sale_off = parseInt(is_sale_off||0);
            var food_status_txt = FoodIsSaleOff.toString(is_sale_off);
            if(FoodIsSaleOff.YES == is_sale_off)
            {
                food_status_txt = "<font color=gray>" + food_status_txt + "</font>";
            }
            else
            {
                food_status_txt = "<font color=green>" + food_status_txt + "</font>";
            }
            return food_status_txt;
        }

        function OpenFoodEditBox(food_id)
        {
            if(window.FoodEdit)
            {
                window.FoodEdit.Open(food_id);
                return;
            }
        }

        function Init()
        {
            $.get('menu_edit.html?'+Util.GetTimestamp(), function(resp){
                $("body").append(resp);
                ReLoad();
            });
        }

        // 查询
        $("#id_query_1454244045 .bt_query").click(function(){
            ReLoad();
        })

        // 新建
        $("#id_ctrl_1454249585 .bt_new").click(function(){
            OpenFoodEditBox();
        })

        // 删除
        $("#id_ctrl_1454249585 .bt_del").click(function(){
            Delete();
        })

        $("#id_menu_list .list_head .chk .value").click(function(){
            var chk = $(this).IsChecked();
            $("#id_menu_list .food_line .chk .value").SetChecked(chk);
        })

        Init();
    }//end of var MenuList = new function(...
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
    padding: 0.2rem;
    font-size: 1.6rem;
    margin-right: 1.5rem;
}
#id_query_1454244045 .food_id .value {
    width: 6rem;
}
#id_query_1454244045 .food_name .value {
    width: 12rem;
}
#id_query_1454244045 .bt_query {
    font-size: 1.4rem;
}
</style>
<div id="id_query_1454244045">
    <span class="food_id">餐品ID: <input type="text" class="value" /></span>
    <span class="food_name">餐品名: <input type="text" class="value" id="id_1479618919" /></span>
    <input type="button" class="bt_query" value="查询"/>
</div>





<style type="text/css">
#id_menu_list {
    margin-top: 20px;
}
#id_menu_list td {
    padding: 2px;
}
#id_menu_list input {
    margin-right: 6px;
}
#id_menu_list .data {
    border: 1px solid #DEDEDE;
    height: 56.0834rem;
    overflow-y: auto;
}
#id_menu_list .index {
    width: 40px;
}
#id_menu_list .title {
    width: 400px;
}
#id_menu_list .chk{
    width: 10px;
}
#id_menu_list .food_id {
    /*display: none;*/
}
#id_menu_list .food_img_list img {
    border: 1px solid #DEDEDE;
    height: 60px;
    width: 60px;
}
#id_menu_list .food_status {
    white-space:nowrap;
}
#id_menu_list .opr {
    white-space:nowrap;
}
</style>
<div id="id_menu_list">
<div class="data">
<table class="table_hover table_head" border="1" width="99%">
    <tr class="list_head">
        <th class="chk"><input type="checkbox" class="value"/></th>
        <th class="index">序号</th>
        <th class="food_img_list">餐品照片</th>
        <th class="food_id">餐品id</th>
        <th class="food_name">餐品名称</th>
        <th class="food_category">餐品类别</th>
        <th class="food_price">餐品单价(元)</th>
        <th class="food_vip_price">会员单价(元)</th>
        <th class="food_sold_num_day">今天已售(份)</th>
        <th class="food_status">状态</th>
        <th class="opr">操作</th>
    </tr>
    <!-- 模板 -->
    <script id="tp_food_list" type="text/html">
    {{each list as item i}}
        <tr class="food_line hand" id="id_food_line_{{item.food_id}}" data-food_id="{{item.food_id}}">
            <td class="chk"><input type="checkbox" class="value" value="{{item.food_id}}"/></td>
            <td class="index">{{i+1}}</td>
            <td class="food_img_list open_edit"><img src="{{item.food_img_url}}"/></td>
            <td class="food_id open_edit">{{item.food_id}}</td>
            <td class="food_name hand open_edit">{{item.food_name}}</td>
            <td class="food_category open_edit">{{item.category_name}}</td>
            <td class="food_price open_edit">{{#item.food_price_txt}}</td>
            <td class="food_vip_price open_edit">{{#item.food_vip_price_txt}}</td>
            <td class="food_sold_num_day open_edit">{{item.food_sold_num_day}}</td>
            <td class="food_status">{{#item.food_status_txt}}</td>
            <td class="opr">
                <button class="sale_off">下架</button>
                <button class="sale_on">上架</button>
            </td>
        </tr>
    {{/each}}
    </script>
</table>
</div>
</div>


<br/>

<style type="text/css">
#id_ctrl_1454249585 {
    margin-top: 10px;
}
#id_ctrl_1454249585 .bt_new,
#id_ctrl_1454249585 .bt_del,
#id_ctrl_1454249585 .prev,
#id_ctrl_1454249585 .next {
    margin-right: 5px;
}
</style>
<div id="id_ctrl_1454249585" class="txt_right txt_left" style="margin:5px;">
<span id="id_1454249596" class="msg align_left">&nbsp;</span>
<input type="button" class="bt_new" value="创建" />
<input type="button" class="bt_del" value="删除" />
<!-- <input type ="button" class="prev" value="上一页" />
<input type ="button" class="next" value="下一页" />
<span class="pagesize hide">5</span>
<span class="pageno">1</span><span class="set_pagesize hand" title="点击设置每页显示行数">/</span><span class="total">1</span> -->
</div>

</tr></td></table>
</body>
</html>
