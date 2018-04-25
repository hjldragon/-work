 <?php
/*
 * [Rocky 2017-06-06 17:36:01]
 * 餐桌位列表
 *
 */
require_once("current_dir_env.php");

// 页面中变量
$html = (object)array(
    'title' => '餐桌列表'
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
    var SeatList = new function(){
        var THIS = this;
        var $doing = $("#id_1496742075");

        function Delete()
        {
            var $all_button = $("#id_ctrl_1496742036 input");
            var seat_id_list = [];

            $("#id_seat_list_1496741856 .line .chk .value:checked").GetCheckBoxVal().forEach(function(data){
                seat_id_list.push(data);
                return;
            })

            if(seat_id_list.length == 0)
            {
                return;
            }

            $all_button.Disabled(true);
            $doing.html("正在删除...").show().SetPromptStyle();

            Util.EncSubmit("seat_save.php",
                    {
                        seat_id_list : $.Json.toStr(seat_id_list),
                        del          : true
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
                for(var i in seat_id_list)
                {
                    var seat_id = seat_id_list[i];
                    var $line = $("#id_line_seat_" + seat_id);
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
                list : list
            }
            // 后台数据转为前台数据
            for(i in list)
            {
                var item = list[i];
                if("" == item.seat_id)
                {
                    continue;
                }
                data.list.push({
                    seat_id : item.customer_id,
                    seat_name       : item.seat_name,
                    seat_size: item.seat_size
                });
            }
            var html = template('tp_seat_list', data).trim();
            $("#tp_seat_list").prevAll(".line").remove();
            var $tr_ary = $(html).insertBefore("#tp_seat_list");
            $tr_ary.find(".open_edit").click(function(){
                var seat_id = Util.FindDataElemValue($(this), "seat_id");
                SeatEdit(seat_id);
            })
            $tr_ary.find(".seat_qrcode").click(function(){
                ShowQrcode($(this));
            })
            return $tr_ary;
        }

        function ReLoad()
        {
            var $all_button = $("");
            $all_button.Disabled(true);
            $doing.html("正在加载数据...").show().SetPromptStyle();

            Util.EncSubmit("seat_get.php",
                {
                    list  : true,
                    'seat_name': $("#id_query_1454244045 .seat_name .value").Value(),
                    'seat_size': $("#id_query_1454244045 .seat_size .value").Value(),
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

        function SeatEdit(seat_id)
        {
            if(window.SeatEdit)
            {
                window.SeatEdit.Open(seat_id);
                return;
            }
            $.get('seat_edit.html?'+Util.GetTimestamp(), function(resp){
                $("body").append($(resp));
                window.SeatEdit.Open(seat_id);
            });
        }

        function ShowQrcode($obj)
        {
            var seat_id = Util.FindDataElemValue($obj, "seat_id");
            if(!$obj.data("img"))
            {
                var url = "img_get.php?get_seat_qrcode=1"
                        + "&shop_id=" + Public.LoginInfo.ShopInfo.shop_id
                        + "&seat_id=" + seat_id;
                $obj.html($("<img/>").attr("src", url));
                $obj.data("img", url);
            }
        }

        function ExportQrcode()
        {
            var list = $("#id_seat_list_1496741856 .line .chk .value:checked").GetCheckBoxVal();
            var url = "img_get.php?export_seat_qrcode=1"
                    + "&shop_id=" + Public.LoginInfo.ShopInfo.shop_id
                    + "&seat_list=" + encodeURIComponent($.Json.toStr(list));
           $("<a/>").attr("href", url).get(0).click();
        }

        function Init()
        {
            ReLoad();
            $.get('seat_edit.html?'+Util.GetTimestamp(), function(resp){
                $("#id_seat_edit_box_1496166005").html($(resp));
            });
        }

        // 全先/反选
        $("#id_seat_list_1496741856 .list_head .chk .value").click(function(){
            var chk = $(this).IsChecked();
            $("#id_seat_list_1496741856 .line .chk .value").SetChecked(chk);
        })

        // 新建
        $("#id_ctrl_1496742036 .bt_new").click(function(){
            SeatEdit();
        })

        // 删除
        $("#id_ctrl_1496742036 .bt_del").click(function(){
            Delete();
        })

        // 导出二维码
        $("#id_ctrl_1496742036 .bt_export_qrcode").click(function(){
            ExportQrcode();
        })
        //查询结果
        $("#id_query_1454244045 .bt_query").click(function(){
            ReLoad();
        })

        Init();
    }//end of var SeatList = new function(...
    window.SeatList = SeatList;
});
</script>


<body>
<table border="0" class="txt_left" width="98%"><tr><td>


            <div id="id_query_1454244045">
                <span class="seat_name">餐桌名: <input type="text" class="value" /></span>
                <span class="seat_size">座位数: <input type="text" class="value" /></span>
                <input type="button" class="bt_query" value="查询"/>
            </div>

<style type="text/css">
#id_seat_list_1496741856 {
    margin-top: 2rem;
}
#id_seat_list_1496741856 td {
    padding: 0.2rem;
}
#id_seat_list_1496741856 input {
    margin-right: 0.6rem;
}
#id_seat_list_1496741856 .chk {
    width: 1rem;
}
#id_seat_list_1496741856 .chk .value {
    width: 1.8076rem;
    height: 1.8076rem;
}
#id_seat_list_1496741856 .seat_qrcode img {
    width: 6.990863rem;
}
</style>
<table id="id_seat_list_1496741856" class="table_hover table_head" border="1" width="99%">
    <tr class="list_head">
        <th class="chk"><input type="checkbox" class="value" /></th>
        <th class="seat_id">ID</th>
        <th class="seat_name">餐桌号</th>
        <th class="seat_size">座位数</th>
        <th class="seat_price">餐位费(元)</th>
        <th class="seat_qrcode">二维码</th>
    </tr>
    <!-- 模板 -->
    <script id="tp_seat_list" type="text/html">
    {{each list as item i}}
        <tr class="line" id="id_line_seat_{{item.seat_id}}" data-seat_id="{{item.seat_id}}">
            <td class="chk"><input type="checkbox" class="value" value="{{item.seat_id}}"/></td>
            <td class="seat_id hand open_edit">{{item.seat_id}}</td>
            <td class="seat_name hand open_edit">{{item.seat_name}}</td>
            <td class="seat_size hand open_edit">{{item.seat_size}}</td>
            <td class="seat_price hand open_edit">{{item.seat_price}}</td>
            <td class="seat_qrcode hand">查看</td>
        </tr>
    {{/each}}
    </script>
</table>


<br/>

<style type="text/css">
#id_ctrl_1496742036 {
    margin-top: 10px;
}
#id_ctrl_1496742036 .bt_new,
#id_ctrl_1496742036 .bt_del,
#id_ctrl_1496742036 .prev,
#id_ctrl_1496742036 .next {
    margin-right: 5px;
}
</style>
<div id="id_ctrl_1496742036" class="txt_right txt_left" style="margin:5px;">
<span id="id_1496742075" class="msg align_left">&nbsp;</span>
<input type="button" class="bt_new" value="创建" />
<input type="button" class="bt_del" value="删除" />
<input type="button" class="bt_export_qrcode" value="导出二维码" />
</div>


<div id="id_seat_edit_box_1496166005"></div>

</tr></td></table>
</body>
</html>
