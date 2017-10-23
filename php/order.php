<?php
/*
 * [Rocky 2017-05-14 19:22:13]
 * 订单列表
 *
 */
require_once("current_dir_env.php");


// 页面中变量
$html = (object)array(
    'title' => '订单列表'
);
?><?php /******************************以下为html代码******************************/?>
<?php require("template/pageheader.php"); ?>
<script src="3rd/artTemplate/template.js"></script>
<script src="3rd/laydate/laydate.js"></script>
<script src="js/jquery.query.js"></script>
<body>






<!-- --------------------我的订单-------------------- -->
<script type="text/javascript">
$(function() {
    var OrderList = new function(){
        var THIS = this;
        //console.log(THIS);
        var $doing = $("#id_order_1494761624 .ctrl .doing");
        var $begin_time = $('#id_query_1494761077 .begin_time');
        var $end_time = $('#id_query_1494761077 .end_time');
        var $seat = $("#id_query_1494761077 .seat .value").data("seat_id", Util.GetQueryString('seat_id'));

        function CreateList(list)
        {
            if(!list)
            {
                return;
            }
            var data = {
                list : []
            }
            for(var i in list)
            {
                var item = list[i];
                item.dine_way = DineWay.toString(item.dine_way);
                item.pay_way = PayWay.toString(item.pay_way);
                if(item.order_time)
                {
                    item.order_time = Util.TimeTo(item.order_time);
                }
                item.order_status = item.order_status;
                item.seat_name = item.seat.seat_name;
                item.order_status_html = OrderStatusToHtml(item.order_status);
                item.order_opr_html = OrderOprButton(item.order_id, item.order_status);
                data.list.push(item);
            }
            var html = template('tp_myorder_list', data).trim();
            $("#tp_myorder_list").prevAll(".line").remove();
            var $ary = $(html).insertBefore("#tp_myorder_list");
            $ary.find(".order_id").click(function(){
                var url = "order_edit.php?id=" + $(this).text();
                $("<a target=_blank/>").attr("href", url).get(0).click();
            });
            $ary.find(".bt_sure").click(function(){
                var order_id = $(this).val();
                ModifyOrderStatus(order_id, OrderStatus.CONFIRMED);
            });
            $ary.find(".bt_postponed").click(function(){
                var order_id = $(this).val();
                ModifyOrderStatus(order_id, OrderStatus.POSTPONED);
            });
            $ary.find(".bt_cancel").click(function(){
                var order_id = $(this).val();
                ModifyOrderStatus(order_id, OrderStatus.CANCEL);
            });
            $ary.find(".bt_finish").click(function(){
                var order_id = $(this).val();
                ModifyOrderStatus(order_id, OrderStatus.FINISH);
            });
            // 设置按操作按钮
            $ary.each(function(){
                var $this = $(this);
                var order_id = $this.find(".order_id").text();
                var order_status = $this.find(".order_status").data("order_status");
                SetOrderStatus(order_id, order_status);
            });
        }

        function ReLoad()
        {
            $doing.html("正在加载数据...").show().SetPromptStyle();

            Util.EncSubmit("order_get.php",
                {
                     orderlist  : true,
                     order_id   : $("#id_query_1494761077 .order_id .value").val(),
                     seat_id    : $seat.data("seat_id")||"",
                     begin_time : $begin_time.val().replace(/-/g, ""),
                     end_time   : $end_time.val().replace(/-/g, "")
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        return;
                    }
                    SetUi(resp.data);
                    $doing.html("");
                }
            );

            function SetUi(data)
            {
                CreateList(data.list);
            }
        }

        // 生成操作按钮代码
        function OrderOprButton(order_id, order_status)
        {
            var html = "<button value='"+ order_id +"'' class='bnt bt_sure'>确认</button>"
                     + "<button value='"+ order_id +"'' class='bnt bt_postponed'>叫 起</button>"
                     + "<button value='"+ order_id +"'' class='bnt bt_cancel'>取消</button>"
                     + "<button value='"+ order_id +"'' class='bnt bt_finish'>完成</button>"
                     + "";
            return html;
        }

        function OrderStatusToHtml(order_status)
        {
            var html = OrderStatus.toString(order_status);
            if(OrderStatus.PENDING == order_status
                || OrderStatus.ERR == order_status)
            {
                html = "<font color=red>" + html + "</font>";
            }
            else if(OrderStatus.PAID == order_status
                    || OrderStatus.CONFIRMED == order_status
                    || OrderStatus.POSTPONED == order_status
                    || OrderStatus.PRINTED == order_status)
            {
                html = "<font color=green>" + html + "</font>";
            }
            else if(OrderStatus.CANCEL == order_status)
            {
                html = "<font color=gray>" + html + "</font>";
            }
            return html;
        }

        // 设置按钮状态
        function SetOrderStatus(order_id, order_status)
        {
            var $line = $("#id_line_" + order_id);
            if(!$line)
            {
                return;
            }
            var order = {}
            $line.find(".order_status").html(OrderStatusToHtml(order_status));

            $line.find(".bnt").Disabled(true);

            if(OrderStatus.IsPending(order_status))
            {
                $line.find(".bt_finish").Disabled(false);
                $line.find(".bt_cancel").Disabled(false);
                $line.find(".bt_sure").Disabled(false);
                $line.find(".bt_postponed").Disabled(false);
            }
            else if(OrderStatus.HadConfirmed(order_status))
            {
                $line.find(".bt_finish").Disabled(false);
            }
        }

        function ModifyOrderStatus(order_id, order_status)
        {
            $doing.html("正在处理...").SetPromptStyle();
            Util.EncSubmit("order_save.php",
                {
                    modify_status : true,
                    order_id      : order_id,
                    order_status  : order_status
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).SetErrStyle();
                        return;
                    }
                    SetUi();
                    $doing.html("");
                }
            );
            function SetUi()
            {
                SetOrderStatus(order_id, order_status)
            }
        }

        function FillSeatDropbox()
        {
            Util.EncSubmit("seat_get.php",
                {
                    list : true
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        return;
                    }
                    Fill(resp.data.list);
                }
            );
            function Fill(list)
            {
                var menulist = [{
                    value : "",
                    title : "不限"
                }];
                for(i in list)
                {
                    var item = list[i];
                    menulist.push({
                        value : item.seat_id,
                        title : item.seat_name
                    });
                }

                (new Util.Menu($seat,
                    menulist,
                    function(data){
                        $seat.html(data.title).data("seat_id", data.value);
                    },
                    {max_height:400}
                )).SetValue($seat.data("seat_id"));
            }
        }

        function Init()
        {
            $("#id_order_1494761624 > .box").height(window.innerHeight * 0.8);
            // 最近一周
            var now = Util.GetTimestamp();
            var begin = Util.TimeTo(now - 3600*24*7, "yyyy-MM-dd");
            var end = Util.TimeTo(now, "yyyy-MM-dd");
            $begin_time.val(begin);
            $end_time.val(end);
            FillSeatDropbox();
            ReLoad();
        }

        $('#id_query_1494761077 .begin_time, #id_query_1494761077 .end_time').click(function(event){
            laydate();
        })

        $("#id_query_1494761077 .bt_query").click(function(){
            ReLoad();
        })

        $("#id_order_1494761624 .ctrl .close").click(function(){
        })

        Init();
    }//end of var OrderList = new function(...
    window.OrderList = OrderList;
});
</script>


<style type="text/css">
#id_query_1494761077 {
    margin-top: 1.04282rem;
    margin-bottom: 0.3rem;
    padding-left: 1rem;
}
#id_query_1494761077 .value {
    font-size: 1.6rem;
    height: 2rem;
    padding: 0;
    margin: 0;
}
#id_query_1494761077 .order_id .value,
#id_query_1494761077 .seat .value,
#id_query_1494761077 .end_time {
    margin-right: 0.807234rem;
}
#id_query_1494761077 .begin_time,
#id_query_1494761077 .end_time {
    width: 9rem;
}
#id_query_1494761077 .order_id .value {
    width: 10rem;
}
#id_query_1494761077 .seat .value {
    width: 6rem;
}
#id_query_1494761077 .bt_query {
    height: 2.3rem;
    margin-left: 0.5rem;
    font-size: 1.4rem;
}
</style>
<div id="id_query_1494761077">
    <span class="order_id">单号: <input type="text" class="value" /></span>
    <span class="seat">桌号: <input type="text" class="value hand" readonly="readonly" /></span>
    日期: <input class="begin_time value" /> - <input class="end_time value" />
    <input type="button" class="bt_query" value="查询"/>
</div>




<style type="text/css">
#id_order_1494761624 {
    padding: 0.2rem;
    padding-left: 1rem;
}
#id_order_1494761624 > .box {
    border: 1px solid #DEDEDE;
    overflow-y: auto;
    padding: 0.2rem 0;
}
#id_order_1494761624 td {
    padding-left: 0.3rem;
    text-align: center;
}
#id_order_1494761624 .line .order_opr {
    text-align: center;
    white-space:nowrap;
    font-size: smaller;
}
#id_order_1494761624 .line .order_opr button {
    margin: 0 0.2076rem;
}
#id_order_1494761624 .ctrl {
    text-align: right;
    margin: 0.6rem 1rem 0.3rem 0;
}
</style>
<div id="id_order_1494761624">
<div class="box">
<table class="table_hover table_head" border="1" width="99%">
    <tr class="list_head">
        <th class="order_id">单号</th>
        <th class="customer_id">客户ID</th>
        <th class="order_time">下单时间</th>
        <th class="seat">餐桌号</th>
        <th class="customer_num">人数</th>
        <th class="dine_way">用餐方式</th>
        <th class="pay_way">支付方式</th>
        <th class="order_status">状态</th>
        <th class="order_opr">操作</th>
    </tr>
    <!-- 模板 -->
    <script id="tp_myorder_list" type="text/html">
    {{each list as item i}}
        <tr class="line" id="id_line_{{item.order_id}}">
            <td class="order_id hand">{{item.order_id}}</td>
            <td class="customer_id">{{item.customer_id}}</td>
            <td class="order_time">{{item.order_time}}</td>
            <td class="seat">{{item.seat_name}}</td>
            <td class="customer_num">{{item.customer_num}}</td>
            <td class="dine_way">{{item.dine_way}}</td>
            <td class="pay_way">{{item.pay_way}}</td>
            <td class="order_status" data-order_status={{item.order_status}}>{{#item.order_status_html}}</td>
            <td class="order_opr">{{#item.order_opr_html}}</td>
        </tr>
    {{/each}}
    </script>
</table>
</div>
<div class="ctrl">
    <span class="doing align_left">&nbsp;</span>
    <input type="button" class="close hide" value="关闭" />
</div>


</div><!-- id="id_order_1494761624" -->


</body>
</html>
