<?php
/*
 * [Rocky 2017-06-02 02:14:13]
 * 餐桌位置管理
 *
 */
require_once("current_dir_env.php");

// 页面中变量
$html = (object)array(
    title => '餐桌状态',
    user  => (object)array(
             )
);
?><?php /******************************以下为html代码******************************/?>
<?php require("template/pageheader.php"); ?>
<script src="3rd/artTemplate/template.js"></script>
<script src="js/toast.js"></script>
<script src="js/websocket.js"></script>
<body>
<script type="text/javascript">
$(function() {

    var DiningtableList = new function(){
        var $doing = $("#id_1496341127");
        // var order_prompt_player = new Audio("audio/notify.wav");
        var id2seatinfo = {}; // seat_id --> info
        var player_timer = null;

        function CreateList(list)
        {
            var data = {
                list : []
            }

            // 后台数据转为前台数据
            for(var i in list)
            {
                var item = list[i];
                data.list.push(item);
                id2seatinfo[item.seat_id] = item;
            }

            var html = template('tp_seatlist_icon', data).trim();
            var $tr_ary = $(html).insertBefore("#tp_seatlist_icon");

            $("#id_seat_list .line").find(".icon").click(function(){
                var seat_id = $(this).data("seat_id");
                var seatinfo = id2seatinfo[seat_id];
                if(seatinfo)
                {
                    OpenOrderList(seatinfo);
                }
            })

            var need_beel = false;
            for(var i in list)
            {
                var seat_info = list[i];
                SetSeatStatus(seat_info);
                if(SeatStatus.ALERT == seat_info.seat_status)
                {
                    need_beel = true; // 只要有一桌需要响铃
                }
            }
            if(need_beel)
            {
                PlayPrompt();
            }
        }

        function ReLoad()
        {
            $("#id_ctrl_1451727849 input").Disabled(true);

            $doing.html("正在加载数据...").show().SetPromptStyle();

            Util.EncSubmit("seat_get.php",
                {
                    list : true
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        $("#id_ctrl_1451727849 input").Disabled(false);
                        return;
                    }
                    SetUi(resp.data);
                    $("#id_ctrl_1451727849 input").Disabled(false);
                    $doing.fadeOut(1000);
                }
            );

            function SetUi(data)
            {
                if(!data || !data.list)
                {
                    $doing.html("请重试").SetErrStyle();
                    return
                }
                $("#id_seat_list .line").remove();
                CreateList(data.list);
            }
        }
        this.ReLoad = ReLoad;

        function PlayPrompt()
        {
            if(null != player_timer)
            {
                return;
                //clearInterval(player_timer);
            }
            player_timer = setInterval(function(){
                $("#id_player_box_1498822701").get(0).play();
                $("#id_player_box_1499272041").html('<embed volume="100" hidden=true src="audio/notify.wav" />');
            }, 1300);
        }
        function StopPrompt()
        {
            if(null != player_timer)
            {
                clearInterval(player_timer);
                player_timer = null;
            }
        }

        function SetSeatStatus(seat_info)
        {
            // 标记相应的餐桌
            var $seat = $("#id_line_seat_id_" + seat_info.seat_id + " .icon");
            if(0 == $seat.length)
            {
                $doing.html("接收到订单信息，但桌号不存在:[" + seat_info.seat_id + "]").show().SetErrStyle();
                return;
            }

            $seat.Flash().Stop();
            StopPrompt();
            switch(seat_info.seat_status)
            {
                case SeatStatus.ALERT:
                    $seat.Flash().Start("#BDC000");   // 有订单来了，需要处理
                    PlayPrompt();
                    break;
                case SeatStatus.INUSE:      // 有客人正在用餐
                    $seat.SetBgColor("green");
                    break;
                default:
                    $seat.SetBgColor("#989898");  // 桌空闲
                    break;
            }
        }

        function OpenOrderList(order_list)
        {
            if(window.OrderList)
            {
                window.OrderList.Open(order_list);
                return;
            }
            $.get('seat.orderlist.html?'+Util.GetTimestamp(), function(resp){
                $("#id_orderlist_1496687719").html(resp);
                window.OrderList.Open(order_list);
            });
        }

        // 更新餐桌状态
        function UpdateSeatStatus(seat_id)
        {
            $doing.html("正在加载数据...").show().SetPromptStyle();
            Util.EncSubmit("seat_get.php",
                {
                    'info'    : true,
                    'seat_id' : seat_id
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        return;
                    }
                    SetUi(resp.data);
                    $doing.fadeOut(1000);
                }
            );

            function SetUi(data)
            {
                SetSeatStatus(data.info);
            }
        }

        // 和原来订单信息合并
        function JoinOrderList(order)
        {
            order = {
                order_id       : order.OrderId,
                userid         : order.UserId,
                shop_id        : order.ShopId,
                dine_way       : order.DineWay,
                pay_way        : order.PayWay,
                customer_num   : order.CustomerNum,
                seat_id        : order.SeatId,
                food_list      : order.FoodList,
                order_status   : order.OrderStatus,
                order_time     : order.OrderTime,
                lastmodtime    : order.LastModTime,
                delete         : order.Delete,
                food_num_all   : order.FoodNumAll,
                food_price_all : order.FoodPriceAll,
            }
            var seatinfo = id2seatinfo[order.seat_id];
            if(null == seatinfo || null == seatinfo.order_list)
            {
                return;
            }
            var ret = seatinfo.order_list.findIndex(function(value, index, arr) {
                return value.order_id == order.order_id;
            })
            if(-1 != ret)
            {
                seatinfo.order_list[ret] = order; // 存在
            }
            else
            {
                seatinfo.order_list.unshift(order);
            }
        }

        function Init()
        {
            window.WebSock.Init(function(){
                // 在断开重连后，再次更新
                ReLoad();

                // 上报店铺连接
                window.WebSock.Call("report_shopinfo1496595625",
                    {
                        'ShopId' : Public.LoginInfo.ShopInfo.shop_id,
                        'TermType' : 1496645173
                    },
                    function(resp){
                        if(resp.Ret < 0)
                        {
                            window.Toast.Show(errcode.toString(resp.Ret)).show().SetErrStyle();
                            return;
                        }
                    }
                )

                window.WebSock.Register("Order1496596644",
                    function(resp){
                        if(resp.Ret < 0)
                        {
                            window.Toast.Show(errcode.toString(resp.Ret));
                            return;
                        }
                        // console.log("Order:", resp.Data);
                        JoinOrderList(resp.Data);
                        UpdateSeatStatus(resp.Data.SeatId);
                    }
                )
            });
            ReLoad();
        }

        $("#id_listshow").click(function(){
            window.Store.SetPageData("is_list_format", "1");
            ReLoad();
        });
        $("#id_iconshow").click(function(){
            window.Store.SetPageData("is_list_format", "0");
            ReLoad();
        });

        Init();
    }//end of var DiningtableList = new function(...

    // $("body").click(function(){
    //     // $("#id_player_box_1498822701").get(0).play();
    //     window.PlayPrompt();
    // });
    // window.PlayPrompt();
});
</script>

<style type="text/css">
#id_seat_list {
    border: 1px solid #DEDEDE;
    margin: 10px;
    margin-top:20px;
}
#id_seat_list ul{
    padding-left: 0;
    padding-right: 0;
}
#id_seat_list ul li{
    display: inline-block;
    white-space:nowrap;
}
#id_seat_list .format_icon .icon {
    border: 1px solid #DEDEDE;
    padding: 0px;
    margin: 0px;
    width: 6rem;
    height: 6rem;
    line-height: 6rem;
    border-radius: 0.5rem;
    background: #989898;
    overflow: hidden;
}

#id_note_1499966949 p {
    margin: 0;
    margin-left: 1rem;
    padding: 0;
    font-size: 1.8rem;
}
#id_note_1499966949 .p1 {
    color: #989898;
}
#id_note_1499966949 .p2 {
    color: green;
}
#id_note_1499966949 .p3 {
    color: #BDC000;
}
</style>

<div id="id_note_1499966949">
<p class="p1">灰色：当前桌子空闲</p>
<p class="p2">绿色：当前桌子有客人正在用餐</p>
<p class="p3">黄色闪动：当前桌子有客人点餐，需要服务员去处理</p>
</div>

<fieldset id="id_seat_list">
<legend></legend>
    <ul class="format_icon">
        <script id="tp_seatlist_icon" type="text/html">
        {{each list as item i}}
        <li class="line" id="id_line_seat_id_{{item.seat_id}}">
            <div class="icon hand" align="center" data-seat_id="{{item.seat_id}}">
                {{item.seat_name}}
            </div>
        </li>
        {{/each}}
        </script>
    </ul>
</fieldset>

<br/>

<div id="id_ctrl_1451727849" class="txt_right txt_left hide" style="margin:5px;">
<span id="id_1496341127" class="msg align_left">&nbsp;</span>
<input type="button" class="bt_new" value="新建" />
<input type="button" class="bt_del" value="删除" />
</div>


<!-- 当前桌上的订单列表 -->
<div id="id_orderlist_1496687719"></div>


<audio id="id_player_box_1498822701" controls="controls" style="position:absolute; visibility:hidden;">
    <source src="audio/notify.wav"></source>
</audio>
<div id="id_player_box_1499272041"></div>


</body>
</html>
