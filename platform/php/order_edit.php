<?php
/*
 * [Rocky 2017-05-14 20:14:43]
 * 订单信息
 *
 */
require_once("current_dir_env.php");
require_once("mgo_order.php");


// 页面中变量
$html = (object)array(
    'title' => '订单信息',
);
?><?php /******************************以下为html代码******************************/?>
<?php require("template/pageheader.php"); ?>
<script src="3rd/artTemplate/template.js"></script>





<script type="text/javascript">
$(function(){
    // 订单详细信息窗口
    var Order = new function(){
        var THIS = this;
        var $doing = $("#id_order_box .doing");
        var $order_id = $("#id_order_1494523635 .order_id .value");
        var $seat = $("#id_order_1494523635 .seat .value");
        var $order_time = $("#id_order_1494523635 .order_time .value");
        var $customer_num = $("#id_order_1494523635 .customer_num .value");
        var $pay_way = $("#id_order_1494523635 .pay_way .value");
        var $dine_way = $("#id_order_1494523635 .dine_way .value");
        var $order_status = $("#id_order_1494523635 .order_status .value");
        var $food_num_all = $("#id_food_num_all_1497374266");
        var $food_price_all = $("#id_food_price_all_1497374266");
        var $order_waiver_fee = $("#id_order_box .order_sum .order_waiver_fee");
        var $order_payable = $("#id_order_box .order_sum .order_payable");
        var $bt_order_food_add = $("#id_order_food_put_1499667299");
        var $bt_ok = $("#id_order_1494498145 .ctrl .ok");
        var $bt_close = $("#id_order_1494498145 .ctrl .close");
        var $bt_sure_again = $("#id_sure_1500389370");
        var $bt_print = $("#id_order_1494498145 .ctrl .print_manual");
        var $other_num_all = $("#id_other_num_all_1497374360");
        var $other_price_all = $("#id_other_price_all_1497374360");
        var $order_fee = $("#id_order_fee_1497446627");
        var $order_remark = $("#id_remark_1500612923");
        var id2foodinfo = {};


        function CreateFoodList(order_info)
        {
            if(!order_info.food_list
                || !order_info.food_list
                || 0 == order_info.food_list.length)
            {
                $("#tp_order_food_list").prevAll(".line").remove();
                return;
            }
            var data = {
                list : order_info.food_list
            }
            for(var i in order_info.food_list)
            {
                var item = order_info.food_list[i];
                item.food_price = Util.Float(item.food_price);
                item.food_price_sum = Util.Float(item.food_price_sum);
                id2foodinfo[item.id] = item;
            }

            var html = template('tp_order_food_list', data).trim();
            $("#tp_order_food_list").prevAll(".line").remove();
            var $lines = $(html).insertBefore("#tp_order_food_list");

            $lines.click(function(){
                var order_food_id = Util.FindDataElemValue($(this), 'order_food_id');
                OpenFoodSelectBox(order_food_id);
            });

            $lines.find(".bt_delete").click(function(){
                var $this = $(this);
                var order_food_id = Util.FindDataElemValue($this, 'order_food_id');
                Delete(order_food_id, function(){
                    ReLoad();
                });
                return false;
            });

            // 餐品数量、总价
            $food_num_all.html(order_info.food_num_all);
            $food_price_all.html(Util.Float(order_info.food_price_all));
        }

        function CreateOtherList(order_info)
        {
            var list = [
                {
                    food_id        : "11",    // 特殊的id  <<<<<<<<
                    food_name      : "餐位费",
                    food_price     : order_info.seat.seat_price,
                    food_num       : order_info.customer_num,
                    food_price_sum : Util.Float(order_info.seat.seat_price * order_info.customer_num),
                }
            ];
            var data = {
                list : list
            }

            var other_num_all = 0;
            var other_price_all = 0;

            for(var i in list)
            {
                var item = list[i];
                other_num_all += item.food_num;
                other_price_all += item.food_price_sum;
            }

            $other_num_all.Value(other_num_all);
            $other_price_all.Value(Util.Float(other_price_all));

            var html = template('tp_order_other_list', data).trim();
            $("#tp_order_other_list").prevAll(".line").remove();
            $(html).insertBefore("#tp_order_other_list");
        }

        function CalOrderFee(order_info)
        {
            $order_fee.Value(order_info.order_fee);
        }

        function OpenFoodSelectBox(order_food_id)
        {
            var order_food_info = id2foodinfo[order_food_id];
            if(window.FoodList)
            {
                window.FoodList.Open(order_food_info, FoodSelectBoxSaveCallback);
                return;
            }
            $.get('order_edit.foodlist.html?'+Util.GetTimestamp(), function(resp){
                $("#id_order_edit_foodlist_html").html(resp);
                window.FoodList.Open(order_food_info, FoodSelectBoxSaveCallback);
            });
        }
        function FoodSelectBoxSaveCallback()
        {
            ReLoad();
        }


        function ReLoad()
        {
            // $("#id_order_box input").Disabled(true, {exclude:[$("#id_order_box .ctrl .close")]});
            $doing.html("正在加载数据...").show().SetPromptStyle();

            Util.EncSubmit("order_get.php",
                {
                    orderinfo : true,
                    order_id  : Util.GetQueryString('id')
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        return;
                    }
                    SetUi(resp.data);
                    THIS.CustomerInfo.Init(resp.data.info.customer);
                    $doing.html("");
                    // $("#id_order_box input").Disabled(false);
                }
            );

            function SetUi(data)
            {
                var order_info = data.info;

                $order_id.Value(order_info.order_id||"");
                $seat.Value(order_info.seat.seat_name||"").data("seat_id", order_info.seat.seat_id);
                $order_time.Value(order_info.order_time?Util.TimeTo(order_info.order_time):"").data("order_time", order_info.order_time);
                $customer_num.Value(order_info.customer_num||"");
                $order_status.Value(OrderStatus.toString(order_info.order_status)).data("order_status", order_info.order_status);
                $order_remark.Value(order_info.order_remark||"");
                Util.SetCheckboxValue("name_1494522528", order_info.dine_way);
                Util.SetCheckboxValue("name_1494651781", order_info.pay_way);

                CreateFoodList(order_info);
                CreateOtherList(order_info);
                CalOrderFee(order_info);

                $order_waiver_fee.Value(Util.Float(order_info.order_waiver_fee));
                $order_payable.Value(Util.Float(order_info.order_payable));
                SetElemStatus(order_info.order_status);
                return;
            }
        }
        window.p = ReLoad;

        function SetElemStatus(order_status)
        {
            if(OrderStatus.PENDING != order_status)
            {
                $("#id_order_box input").Disabled(true);
            }
            if(OrderStatus.HadConfirmed(order_status))
            {
                $bt_sure_again.Disabled(false);
                $bt_order_food_add.Disabled(false);
                $order_waiver_fee.Disabled(false);
                $bt_ok.Disabled(false);
                $("#id_order_1494498145 .order_food_list .bt_delete").Disabled(false);
                $order_remark.Disabled(false);
            }
            $bt_close.Disabled(false);
            $bt_print.Disabled(false);
        }

        // 注，不是food_id
        function Delete(order_food_id, success_callback)
        {
            var $bt = $("#id_order_1494498145 .order_food_list .bt_delete").Disabled(true);
            $doing.html("正在提交...").show().SetPromptStyle();
            Util.EncSubmit("order_save.php",
                {
                    order_food_delete : true,
                    order_id          : $order_id.Value(),
                    order_food_id     : order_food_id,
                },
                function(resp){
                    $bt.Disabled(false);
                    if(resp.ret < 0)
                    {
                        var msg = errcode.toString(resp.ret);
                        $doing.html(msg).show().SetErrStyle();
                        Public.MsgBox(msg, {bt_close:true});
                        return;
                    }
                    if($.isFunction(success_callback))
                    {
                        success_callback();
                    }
                    $doing.html("删除成功").fadeOut(1000).SetOkStyle();
                }
            );
        }

        // 重新出单等
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
                    if(-30025 == resp.ret)
                    {
                        var msg = errcode.toString(resp.ret) + ": " + resp.data.food_name
                        $doing.html(msg).SetErrStyle();
                        return;
                    }
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
                // SetOrderStatus(order_id, order_status)
            }
        }

        function Save()
        {
            if($order_payable.Float() < 0)
            {
                $doing.html("金额出错").SetErrStyle();
                return;
            }

            $doing.html("正在提交...").show().SetPromptStyle();
            Util.EncSubmit("order_save.php",
                {
                    order_save       : true,
                    order_id         : $order_id.Value(),
                    dine_way         : $dine_way.GetCheckedVal()||"",
                    pay_way          : $pay_way.GetCheckedVal()||"",
                    customer_num     : $customer_num.Int(),
                    food_num_all     : $food_num_all.Int(),
                    food_price_all   : $food_price_all.Float(),
                    order_waiver_fee : $order_waiver_fee.Value(),
                    order_payable    : $order_payable.Float(),
                    order_status     : $order_status.data("order_status"),
                    order_remark     : $order_remark.Value(),
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        var msg = errcode.toString(resp.ret);
                        $doing.html(msg).show().SetErrStyle();
                        return;
                    }
                    //
                    $doing.html("保存成功").fadeOut(1000).SetOkStyle();
                }
            );
        }

        function CustomerInfo()
        {
            var THIS = this;
            THIS.info = {};

            THIS.Init = function(info) {
                THIS.info = info;
            }

            THIS.IsVip = function() {
                return THIS.info.is_vip == IsVipCustomer.YES;
            }
        }
        THIS.CustomerInfo = new CustomerInfo;

        function Init()
        {
            // 单选的checkbox
            Util.SetSelectOneByName("name_1494522528");
            Util.SetSelectOneByName("name_1494651781");

            var order_waiver_fee = function(obj){
                var $this = $(obj);
                var payable = $order_fee.Float() - $this.Float();
                if(payable < 0)
                {
                    $doing.html("金额出错").SetErrStyle();
                    return;
                }
                payable = Util.Float(payable);
                if($order_payable.Float() == payable)
                {
                    return;
                }
                $order_payable.Value(payable).SetColor("red");
            }
            $order_waiver_fee.blur(function(){
                order_waiver_fee(this);
            })
            $order_waiver_fee.keyup(function(){
                order_waiver_fee(this);
            })

            ReLoad();
        }

        $bt_ok.click(function(){
            Save();
        })

        $bt_close.click(function(){
            $("<a href='order.php'/>").get(0).click();
        })

        $bt_order_food_add.click(function(){
            OpenFoodSelectBox();
        })

        $bt_sure_again.click(function(){
            ModifyOrderStatus($order_id.Value(), OrderStatus.CONFIRMED);
        })

        $bt_print.click(function(){
            if(window.OrderPrint)
            {
                window.OrderPrint.Open($order_id.Value());
                return;
            }
            $.get('order.printer.html?'+Util.GetTimestamp(), function(resp){
                $("#id_order_printer_html").html(resp);
                window.OrderPrint.Open($order_id.Value());
            });
        })

        Init();
    }//end of var Order = new function(...
    window.Order = Order;
});
</script>


<style type="text/css">
#id_order_box {
    width: 75rem;
}
#id_order_box .box {
    padding: 1rem;
}
</style>
<div id="id_order_box">
<div class="box">



<style type="text/css">
#id_order_1494523635 {
    font-size: 1.6rem;
    width: 40rem;
}
#id_order_1494523635 .line {
    height: 3rem;
}
#id_order_1494523635 .title {
    width: 4rem;
    text-align: right;
    padding-right: 0.3rem;
    white-space: nowrap;
}
#id_order_1494523635 .value {
    width: 20rem;
    font-size: 1.8rem;
    display: inline-block;
    vertical-align: bottom;
    padding: 0.2rem;
}
#id_order_1494523635 input[type=text] {
    padding: 0.1rem 0.2rem;
}
#id_order_1494523635 input[type=checkbox] {
    height: 1.6rem;
    width: 1.6rem;
}
</style>
<div id="id_order_1494523635">
    <table border="0">
        <tr class="line">
            <td class="title">订单号:</td>
            <td class="order_id"><input type="text" class="value" readonly="readonly" /></td>
        </tr>
        <tr class="line">
            <td class="title">桌号:</td>
            <td class="seat"><input type="text" class="value" readonly="readonly" /></td>
        </tr>
        <tr class="line">
            <td class="title">人数:</td>
            <td class="customer_num"><input type="text" class="value" readonly="readonly" /></td>
        </tr>
        <tr class="line">
            <td class="title">下单时间:</td>
            <td class="order_time"><input type="text" class="value" readonly="readonly" /></td>
        </tr>
        <tr class="line">
            <td class="title">用餐方式:</td>
            <td class="dine_way" valign="middle">
                <label>
                    <input type="checkbox" name="name_1494522528" class="value" value="1" />在店吃&nbsp;
                </label>
                <label>
                    <input type="checkbox" name="name_1494522528" class="value" value="2" />打包
                </label>
            </td>
        </tr>
        <tr class="line">
            <td class="title">支付方式:</td>
            <td class="pay_way" valign="middle">
                <label>
                    <input type="checkbox" name="name_1494651781" class="value noempty" value="2" />微信　&nbsp;
                </label>
                <label>
                    <input type="checkbox" name="name_1494651781" class="value noempty" value="1" />现金
                </label>
            </td>
        </tr>
        <tr class="line">
            <td class="title">订单状态:</td>
            <td class="order_status"><input type="text" class="value" readonly="readonly" /></td>
        </tr>
    </table>
</div>


<div class="split_line"></div>




<style type="text/css">
#id_order_1494498145 {
    padding: 0;
    margin: 0;
}
#id_order_1494498145 .split_line {
    background: gray;
    height: 1px;
    margin: 1rem 0;
}
#id_order_1494498145 .split_line_dashed {
    border-top: 1px dashed gray;
    margin: 0.3rem 0;
}
#id_order_1494498145 td {
    padding: 0.2rem;
    text-align: left;
}
#id_order_1494498145 .line {
    color: #2B8106;
}
#id_order_1494498145 .remark_1499668541 {
    color: gray;
    font-style: italic;
    font-size: 1.40983rem;
}
#id_order_1494498145 .food_price {
    font-size: 1.2rem;
}
#id_order_1494498145 .food_num .value {
    margin-left: 0.1rem;
}
#id_order_1494498145 .ctrl {
    margin-top: 0.6rem;
    text-align: right;
}
#id_order_1494498145 .ctrl .doing {
    text-align: left;
    margin-top: 0.6rem;
}
#id_order_1494498145 .ctrl .ok,
#id_order_1494498145 .ctrl .close {
    margin: 0 0.2rem;
}

#id_order_1494498145 .seat_name,
#id_order_1494498145 .customer_num {
    font-size: 1.4rem;
}
#id_order_1494498145 fieldset {
    border-width: 0.2rem;
    border-style: dashed;
    padding: 0 0.50073rem;
    margin-bottom: 2.08472rem;
}
#id_order_1494498145 fieldset legend {
    font-size: 1.60037rem;
    padding: 0.20075rem;
}
#id_order_1494498145 .order_food_list,
#id_order_1494498145 .order_other_list {
    max-height: 50.032rem;
    overflow-x: hidden;
    overflow-y: auto;
    min-height: 3.07634rem;
}
#id_order_1494498145 .food_attach_list .attach_box {
    background-color: #d7d8d6;
}
#id_order_1494498145 .food_attach_list .attach_item {
    font-size: 1.2086534rem;
    margin-left: 0.1rem;
    background-color: #b5bfaf;
    color: #727275;
}

#id_order_1494498145 .width_food_name {
    width: 40rem;
}
#id_order_1494498145 .width_food_num {
    width: 10rem;
}
#id_order_1494498145 .width_food_price_sum {
    width: 10rem;
}
#id_order_1494498145 .width_bt_delete {
    width: 5rem;
}
</style>
<div id="id_order_1494498145">
<div class="split_line"></div>

<fieldset>
<legend>已点餐品（数量: <span id="id_food_num_all_1497374266">0</span>份,
       总价: ￥<span id="id_food_price_all_1497374266">0</span>）
       <input type="button" id="id_order_food_put_1499667299" value="加菜">
</legend>
<div class="order_food_list">
<table border="0" width="100%">
    <!-- 模板 -->
    <script id="tp_order_food_list" type="text/html">
    {{each list as item i}}
    {{if i > 0}}
    <tr class="line">
        <td colspan="4">
            <div class="split_line_dashed"></div>
        </td>
    </tr>
    {{/if}}
    <tr class="line hand" data-food_id="{{item.food_id}}" data-order_food_id="{{item.id}}">
        <td>
            <div class="food_name width_food_name">
                <span class="value">{{item.food_name}}</span>
                {{if item.unit_num}}
                    <span class="remark_1499668541">({{item.unit_num}}{{item.food_unit||"份"}})</span>
                {{/if}}
            </div>
            <div class="food_price" title="单价">
                ￥<span class="value">{{item.food_price}}</span>
            </div>
            <div class="food_attach_list">
                <span class="attach_box">
                {{each item.food_attach_list as food_attach i}}
                <span class="value attach_item">{{food_attach}}</span>
                {{/each}}
                </span>
            </div>
        </td>
        <td>
            <div class="food_num width_food_num">
                ×<span class="value">{{item.food_num}}</span>
            </div>
        </td>
        <td>
            <div class="food_price_all width_food_price_sum">
                ￥<span class="value">{{item.food_price_sum}}</span>
            </div>
        </td>
        <td>
            <div class="width_bt_delete">
                <input type="button" class="bt_delete" value="删除">
            </div>
        </td>
    </tr>
    {{/each}}
    </script>
</table>
</div>
</fieldset>


<fieldset id="id_1497378407">
<legend>其它（数量: <span id="id_other_num_all_1497374360">0</span>份,
       总价: ￥<span id="id_other_price_all_1497374360">0</span>）
</legend>
<div class="order_other_list">
<table border="0" width="100%">
    <!-- 模板 -->
    <script id="tp_order_other_list" type="text/html">
    {{each list as item i}}
    {{if i > 0}}
    <tr class="line">
        <td colspan="3">
            <div class="split_line_dashed"></div>
        </td>
    </tr>
    {{/if}}
    <tr class="line">
        <td>
            <div class="food_name width_food_name">
                <span class="value">{{item.food_name}}</span>
            </div>
            <div class="food_price">
                ￥<span class="value">{{item.food_price}}</span>
            </div>
        </td>
        <td>
            <div class="food_num width_food_num">
                ×<span class="value">{{item.food_num}}</span>
            </div>
        </td>
        <td>
            <div class="food_price_sum width_food_price_sum">
                ￥<span class="value">{{item.food_price_sum}}</span>
            </div>
        </td>
        <td>
            <div class="width_bt_delete" style="visibility:hidden">
                <input type="button" class="bt_delete" value="删除">
            </div>
        </td>
    </tr>
    {{/each}}
    </script>
</table>
</div>
</fieldset>

<div class="split_line"></div>





<style type="text/css">
#id_order_box .order_sum {
    margin-top: 1rem;
}
#id_order_box .order_sum .title {
    margin-left: 2rem;
    width: 3rem;
}
#id_order_box .order_sum .value {
    font-weight: bold;
}
#id_order_box .order_sum .order_waiver_fee {
    font-size: 1.6rem;
    width: 4.67324rem;
    padding-left: 0.3rem;
}
#id_order_box .order_sum .order_payable {
    font-size: 3.0834rem;
    color: #ff24de;
}
#id_order_box .ctrl {
    margin-top: 1.308rem;
}
</style>
<div class="order_sum">
    <span class="title">总计: </span><span id="id_order_fee_1497446627">0</span>元
    <span class="title">减免: </span><input class="order_waiver_fee value" value="0">元
    <span class="title">应付: </span><span class="order_payable value">0</span>元
    <span class="title">备注: </span><input id="id_remark_1500612923" class="remark" />
</div>
<div class="ctrl">
    <span class="doing align_left" style="color: gray; font-style: italic; font-size: 1.4rem;"></span>
    <input type="button" class="ok" value="保存">
    <input type="button" class="close" value="返回">

    <input type="button" id="id_sure_1500389370" value="重新出单">
    <input type="button" id="id_print_manual_1500312200" class="print_manual" value="手动再次打印">
</div>
</div>

</div>
</div><!-- id="id_order_box" -->

<div id="id_order_edit_foodlist_html"></div>
<div id="id_order_printer_html"></div>
