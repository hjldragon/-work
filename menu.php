<?php
/*
 * [Rocky 2017-05-09 16:25:21]
 * 菜单列表
 *
 */
require_once("current_dir_env.php");
if(!$_REQUEST['openid'] && !$_REQUEST['debug'] && PageUtil::IsWeixin())
{
    $url = "http://wx.jzzwlcm.com/wx_openid.php?{$_SERVER['QUERY_STRING']}";
    header("Location: $url");
    exit();
}

// 页面中变量
$html = (object)array(
    'title' => '菜单列表'
);

?><?php /******************************以下为html代码******************************/?>
<?php require("template/pageheader.php"); ?>
<meta http-equiv="x-dns-prefetch-control" content="on">
<meta name="msapplication-tap-highlight" content="no">
<meta content="type" http-equiv="application/x-www-form-urlencoded">
<meta content="telephone=no" name="format-detection">
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
<meta content="black" name="apple-mobile-web-app-status-bar-style">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="browsermode" content="application">
<meta name="x5-page-mode" content="app">
<meta name="full-screen" content="yes">
<meta name="x5-fullscreen" content="true">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=0">
<meta name="screen-orientation" content="portrait">
<meta name="x5-orientation" content="portrait">
<meta content="yes" name="apple-mobile-web-app-capable">
<meta content="yes" name="apple-touch-fullscreen">
<!--script src="3rd/laydate/laydate.js"></script -->
<script src="3rd/artTemplate/template.js"></script>


<!-- --------------------消息框-------------------- -->
<script type="text/javascript">
$(function() {
    var Msg = new function(){
        var THIS = this;
        //console.log(THIS);
        var $doing = $("#id_msg_1494787933 .ctrl .doing");
        var box = new $.FloatBox($("#id_msg_1494787933"));
        var $bt_close = $("#id_msg_1494787933 .ctrl .close");
        //console.log($doing);
        function Open(msg, opt)
        {
            opt = opt||{};
            if(opt.bt_close == "hide")
            {
                $bt_close.hide();
            }
            box.Open();
            $("#id_msg_1494787933 .msg").html(msg);
        }
        THIS.Open = Open;

        function Init()
        {
        }

        $bt_close.click(function(){
            box.Close();
        })

        Init();
    }//end of var Msg = new function(...
    window.Msg = Msg;
});
</script>
<style type="text/css">
#id_msg_1494787933 {
    display: none;
    background-color: white;
    padding: 0.3rem;
    width: 80%;
}
#id_msg_1494787933 .msg {
    display: inline-block;
    vertical-align: bottom;
    text-align: center;
    min-width: 20.0723rem;
    min-height: 4.0032rem;
    margin-top: 1.3074rem;
    width: 100%;
}
#id_msg_1494787933 .ctrl {
    text-align: right;
    margin: 1rem 1rem 0.3rem 0;
}
</style>
<div id="id_msg_1494787933">
<fieldset>
<legend>提示</legend>
    <div class="msg"></div>
    <div class="ctrl">
        <span class="doing align_left">&nbsp;</span>
        <!-- <a href="#" class="btn logout hide">注销</a> -->
        <a href="#" class="btn close">关闭</a>
    </div>
</fieldset>
</div><!-- id="id_msg_1494787933" -->


<script type="text/javascript">
$(function() {
    var MenuDir = new function(){
        var THIS = this;
        //console.log(THIS);
        var $doing = $("");
       //console.log($doing);
        var $prev_click_category = $("");
        var SELECTED_COLOR = "#BDDD60";

        function Init()
        {
            $("#menu_dir_1494319505 .line").click(function(){
                 //console.log(this);//菜单栏
                var $this = $(this);
                $prev_click_category.SetBgColor("");
                // $this.SetBgColor("#BDDD60").css("margin-right", "-2px");
                $this.SetBgColor(SELECTED_COLOR);
                SetSelectedStyle($this);
                $prev_click_category = $this;

                // 滚动到顶
                var category_id = $this.data("category_id");
                var $category = $("#id_category_" + category_id);
                if($category)
                {
                    // 滚动菜单
                    var $menu_list = $("#menu_list_1494319500");
                    var scroll_to_top = $category.offset().top
                                      - $category.parent().offset().top
                                      + $menu_list.scrollTop();
                    $menu_list.animate({scrollTop: scroll_to_top});
                }
            });
            $("#menu_dir_1494319505 .line:first").trigger("click");
        }
        THIS.Init = Init;

        // 是否为当前选中行
        function SetSelectedStyle($line)
        {
            $prev_click_category.SetBgColor("");
            $line.SetBgColor(SELECTED_COLOR);
            $prev_click_category = $line;
        }
        THIS.SetSelectedStyle = SetSelectedStyle;

        // // 滚动合当前项可见
        // function Scroll($line)
        // {
        //     // 滚动菜单
        //     var $menu_dir = $("#menu_dir_1494319505 .dir_list");
        //     var scroll_to_top = $line.offset().top
        //                       - $line.parent().offset().top
        //                       + $menu_dir.scrollTop();
        //     $menu_dir.animate({scrollTop: scroll_to_top});
        // }
        // THIS.ScrollToTop = ScrollToTop;

        function FreshOrderingInfo()
        {
            var all_food_num = 0;
            var all_food_price = 0;
            // 每个类别
            $("#menu_dir_1494319505 .line").each(function(){
                var $this = $(this);
                //console.log($this);
                var category_id = $this.data("category_id");
                //console.log(category_id);
                var $category = $("#id_category_" + category_id);
                if(!$category)
                {
                    return;
                }
                // 当前类别下的数量
                var category_food_num = 0;
                //类别虾米的所有商品
                $category.find(".food_line").each(function(){
                    var $this = $(this);
                    //console.log($this);
                    // 当前类别下的数量
                    var num_value = $this.find(".food_num .num_value").Int();
                    if(0 == num_value)
                    {
                        return;
                    }
                    // 当前类别下的价格
                    var food_price = $this.find(".food_price .value").Float();
                    category_food_num += num_value;
                    all_food_price += food_price * num_value;
                })

                // 目录侧显示的数据
                var $food_num = $this.find(".food_num");
                if($food_num)
                {
                    if(category_food_num > 0)
                    {
                        $food_num.html(category_food_num).css('visibility', 'visible');
                    }
                    else
                    {
                        $food_num.html("").css('visibility', 'hidden');
                    }
                }
                all_food_num += category_food_num;
            })
            all_food_price = parseFloat(all_food_price.toFixed(2));
            $("#id_ordering_1494434664 .ordering_num .value").html(all_food_num);
            $("#id_ordering_1494434664 .ordering_price .value").html(all_food_price);
        }
        THIS.FreshOrderingInfo = FreshOrderingInfo;
    }//end of var MenuDir = new function(...
    window.MenuDir = MenuDir;

    var MenuList = new function(){
        var THIS = this;
        var $doing = $("");
        var menu_data = {};
        var id2foodinfo = {}; // food_id --> food_info
        var $query_food_name = $("#id_query_1454244045 .food_name .value");

        function CreateList(list)
        {
           // console.log(list);菜单栏
            if(!list)
            {
                return;
            }
            var page_data = {
                list : list
            }
            //console.log(list);
            for(var i in list)
            {
                var item = list[i];
                var food_list = item.food_list||[];
                for(var j in food_list)
                {
                    var foodinfo = food_list[j];
                    if(window.CustomerInfo.IsVip() && parseFloat(foodinfo.food_vip_price||0) > 0)
                    {
                        foodinfo.food_price = foodinfo.food_vip_price;
                        foodinfo.is_vip_price = true;
                    }
                    if(undefined == foodinfo.food_stock_num_day)
                    {
                        foodinfo.food_stock_num_day_txt = "充足";
                    }
                    else if(0 == foodinfo.food_stock_num_day)
                    {
                        foodinfo.food_stock_num_day_txt = "<font color=red>售罄</font>";
                        foodinfo.num_add_status = "hide";
                    }
                    else
                    {
                        foodinfo.food_stock_num_day_txt = foodinfo.food_stock_num_day;// + "份";
                    }
                    if(foodinfo.food_img_list && foodinfo.food_img_list.length > 0)
                    {
                        foodinfo.food_img_url = "img_get.php?width=240&height=240&img=1&imgname=" + foodinfo.food_img_list[0];
                    }
                    else
                    {
                        foodinfo.food_img_url = "img/none.png";
                    }
                    id2foodinfo[foodinfo.food_id] = foodinfo;
                }
            }
             //console.log(page_data);

            var html = '';
            html = template('tp_food_list', page_data);
            $("#tp_food_list").prevAll(".line").remove();
            $(html).insertBefore("#tp_food_list");

            html = template('tp_food_dir', page_data);
            $("#tp_food_dir").prevAll(".line").remove();
            $(html).insertBefore("#tp_food_dir");

            MenuDir.Init();

            $("#menu_list_1494319500 .food_line").click(function(){
                var food_id = GetFoodId($(this));
                OpenFoodDetailBox(food_id);
                return false;
            });

            var $bt_num_add = $("#menu_list_1494319500 .food_line .food_num .num_add").click(function(){
                var food_id = GetFoodId($(this));
                var food_info = id2foodinfo[food_id];
                if(NeedWaiterConfirm.YES == food_info.need_waiter_confirm)
                {
                    window.Msg.Open("当前餐品[<font color=red>" + food_info.food_name + "</font>]需要服务员下单，您先点其它餐品，下单后，再呼叫服务员。");
                    FoodNumAdd($(this), 0);
                }
                else
                {
                    FoodNumAdd($(this), 1);
                }
                MenuDir.FreshOrderingInfo();
                return false;
            });

            $("#menu_list_1494319500 .food_line .food_num .num_sub").click(function(){
                FoodNumAdd($(this), -1);
                MenuDir.FreshOrderingInfo();
                return false;
            });

            window.MenuDir.FreshOrderingInfo();

            // 缓存中的用户订单
            var order_info = window.CacheOrder.Get();
            if(order_info && order_info.food_list)
            {
                // console.log(order_info.food_list);

                for(var i in order_info.food_list)
                {
                    var food = order_info.food_list[i];
                    var $food_line = $("#id_food_line_" + food.food_id);
                    food.food_num = parseInt(food.food_num);
                    if(food.food_num > 100)
                    {
                        window.CacheOrder.Clear(); // 出错了
                    }
                    if(0 == $food_line.length || food.food_num <= 0)
                    {
                        continue;
                    }
                    // 注意，这句必须在模似点击操作之前
                    SetUiSelectedFoodAttach($food_line, food.food_attach_list);

                    // 模似点击
                    for(var n=0; n<food.food_num; n++)
                    {
                        $food_line.find(".num_add").get(0).click();
                    }
                }
            }
        }

        function SetUiSelectedFoodAttach($food_line, food_attach_list)
        {
            // 已选的口味
            var selected_food_attach_list = [];
            var $food_attach_list = $food_line.find(".food_attach_list").html("");
            for(var i in food_attach_list)
            {
                var food_attach = food_attach_list[i];
                $("<span class='attach_item'>").Value(food_attach).appendTo($food_attach_list);
            }
        }

        function GetSelectedFoodAttachFromUi($food_line)
        {
            var list = [];
            $food_line.find(".food_attach_list .attach_item").each(function(){
                var food_attach = $(this).text();
                list.push(food_attach);
            });
            return list;
        }

        function FoodNumAdd($bt_num, num)
        {
            var food_id = GetFoodId($bt_num);
            var $food_num = $("#id_food_line_" + food_id + " .food_num");
            if(!$food_num)
            {
                return;
            }
            var $num_value = $food_num.find(".num_value");
            if($num_value)
            {
                $num_value.html($num_value.Int() + num);
            }
            if($num_value.Int() > 0)
            {
                $num_value.show();
                $food_num.find(".num_sub").show();
            }
            else
            {
                $num_value.hide();
                $food_num.find(".num_sub").hide();
            }
            NewOrderToLocal();
        }

        function ReLoad()
        {
            window.Toast.Show("正在加载数据...");

            var food_name = $query_food_name.val().trim();
            if("查找餐品" == food_name)
            {
                food_name = "";
            }

            Util.EncSubmit("menu_get.php",
                {
                    foodlist  : true,
                    food_name : food_name,
                    shop_id   : window.ShopInfo.ShopId()
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        window.Toast.Show(errcode.toString(resp.ret));
                        return;
                    }
                    menu_data = resp.data||{};
                    SetUi(menu_data);
                }
            );

            function SetUi(data)
            {
                if(!data.shop_name)
                {
                    window.Msg.Open("<h1>请扫码点餐</h1>");
                    return;
                }
                if(!data.list || 0 == data.list.length)
                {
                    window.Msg.Open("<h1>店铺出错, 请联系服务员。</h1>");
                    return;
                }
                $("#main").show();
                CreateList(data.list);
            }
        }

        function OpenFoodDetailBox(food_id)
        {
            var food_info = id2foodinfo[food_id];
            if(!food_info)
            {
                window.Toast.Show("餐品出错,ID:[" + food_id + "]");
                return;
            }

            var $line = $("#id_food_line_" + food_id);

            // 已选的口味
            var selected_food_attach_list = [];
            $line.find(".food_attach_list .attach_item").each(function(){
                selected_food_attach_list.push($(this).text());
            })
            var other_info = {
                food_num : $line.find(".food_num .num_value").Int(),
                selected_food_attach_list : selected_food_attach_list
            }
            window.FoodDetail.Open(food_info, other_info, function(info) {
                if("add" == info.opr)
                {
                    $line.find(".food_num .num_add").get(0).click();
                }
                else if("sub" == info.opr)
                {
                    $line.find(".food_num .num_sub").get(0).click();
                }
                else if("attach" == info.opr)
                {
                    SetUiSelectedFoodAttach($line, info.food_attach_list);
                }
                NewOrderToLocal();
            });
        }

        function GetCategoryId($cur_elem)
        {
            return Util.FindDataElemValue($cur_elem, "category_id");
        }

        function GetFoodId($cur_elem)
        {
            return Util.FindDataElemValue($cur_elem, "food_id");
        }

        // 在当前数据中搜索
        function Search()
        {
            var food_name_search = $query_food_name.val().trim();
            if("查找餐品" == food_name_search) // 都不填时，重新向后台发出请求
            {
                food_name_search = "";
            }
            var list = [];
            if("" != food_name_search)
            {
                var cond = new RegExp(food_name_search, "i");
                // 类别
                for(var i in menu_data.list)
                {
                    // 类别下的餐品列表
                    var p = menu_data.list[i];
                    if(!p)
                    {
                        continue;
                    }
                    var food_list = [];
                    for(var j in p.food_list)
                    {
                        var food_info = p.food_list[j];
                        if(!food_info.food_name || -1 == food_info.food_name.search(cond))
                        {
                            continue;
                        }
                        food_list.push(food_info);
                    }

                    list.push({
                        'category_id'   : p.category_id,
                        'category_name' : p.category_name,
                        'food_list'     : food_list
                    });
                }
            }
            else
            {
                list = menu_data.list;
            }
            CreateList(list);
        }
        THIS.Search = Search;

        // 取已点餐品
        function GetSelectedFood()
        {
            var list = [];
            var food_num_all = 0;
            var food_price_all = 0;
            $("#menu_list_1494319500 .food_line").each(function(){
                var $this = $(this);
                var food_num = $this.find(".food_num .num_value").Int();
                if(0 == food_num)
                {
                    return;
                }
                var food_id = $this.data("food_id");
                var food_info = id2foodinfo[food_id];
                var food_attach_list = GetSelectedFoodAttachFromUi($this);
                // var food_category = $this.data("food_category");
                var food_price_sum = food_info.food_price * food_num;  // 注意，这里可能以后有单位加入计算(2017-07-11) [XXX]
                list.push({
                    "food_id"          : food_id,
                    "food_category"    : food_info.food_category,
                    "food_name"        : food_info.food_name,
                    "food_price"       : food_info.food_price,
                    "food_num"         : food_num,
                    "food_price_sum"   : food_price_sum,
                    "food_attach_list" : food_attach_list||[],
                });

                food_num_all += food_num;
                food_price_all += food_price_sum;
            })

            var food_info_selected = {
                'food_num_all'   : food_num_all,
                'food_price_all' : food_price_all,
                'food_list'      : list
            }
            //console.log(list);
            return food_info_selected;
        }

        // 新订单数据存到本在存储
        function NewOrderToLocal()
        {
            var selected_food_info = GetSelectedFood();
            var new_order_info = window.CacheOrder.Get();
            new_order_info.food_num_all = selected_food_info.food_num_all;
            new_order_info.food_price_all = selected_food_info.food_price_all;
            new_order_info.food_list = selected_food_info.food_list;
            new_order_info.seat = window.SeatInfo.GetData();
            new_order_info.customer_id = window.CustomerInfo.CustomerId();
            new_order_info.shop_id = window.ShopInfo.ShopId();
            window.CacheOrder.Set(new_order_info);
        }
        window.NewOrderToLocal = NewOrderToLocal;

        function Init()
        {
            ReLoad();
        }
        THIS.Init = Init;
   // console.log(Init);
        // 刷新
        $("#id_query_1454244045 .bt_fresh").click(function(){
            $query_food_name.val("查找餐品").addClass("empty");
            ReLoad();
        });
        // 打开查询
        $("#id_query_1454244045 .bt_query_open").click(function(){
            $("#id_query_1454244045 .box .food_name").animate({width:"11.48742rem", padding:"1rem"}, "fast");
            return false;
        });
        // 查询
        $("#id_query_1454244045 .bt_query_find").click(function(){
            var food_name_search = $query_food_name.val().trim();
            if("查找餐品" == food_name_search) // 都不填时，重新向后台发出请求
            {
                // food_name_search = "";
                ReLoad();
                return;
            }
            Search();// 不刷新（在原来数据中查找）
        });
        $query_food_name.click(function(){
            return false;
        }).blur(function(){
            // var $this = $(this);
            // var food_name = $this.val().trim();
            // if("" == food_name)
            // {
            //     $this.val("查找餐品").addClass("empty");
            // }
            // Search();
            // $("#id_query_1454244045 .box .food_name").animate({width:"0", padding:"0"}, "fast");
        }).focus(function(){
            var $this = $(this);
            $this.val("").removeClass("empty");
        }).keyup(function(event){
            //Search();
        }).val("查找餐品");
        $("body").click(function(){
            $("#id_query_1454244045 .box .food_name").animate({width:"0", padding:"0"}, "fast", null, function(){
                $query_food_name.val("查找餐品").addClass("empty");
            });
        });

        var pre_scroll_top = 0;
        var scroll_timer = null;
        $("#menu_list_1494319500").scroll(function(){
            var $this = $(this);
            if(null == scroll_timer)
            {
                scroll_timer = setInterval(function(){
                    var cur_scroll_top = $this.scrollTop();
                    if(cur_scroll_top == pre_scroll_top)
                    {
                        //console.log("scroll stop:", cur_scroll_top);
                        clearInterval(scroll_timer);
                        scroll_timer = null;
                        return;
                    }
                    pre_scroll_top = cur_scroll_top;
                    //
                    $("#menu_list_1494319500 .line").each(function(){
                        var $this = $(this);
                        var pos = $this.GetPos();
                        if(pos.top + pos.height > 30)
                        {
                            var category_id = $this.data('category_id');
                            var $dir_category_line = $("#id_menu_dir_category_" + category_id);
                            if($dir_category_line.length > 0)
                            {
                                window.MenuDir.SetSelectedStyle($dir_category_line);
                                return false;
                            }
                        }
                    })
                }, 300);
            }
        })
        // Init(); // 在用户操作类里调用
    }//end of var MenuList = new function(...
    window.MenuList = MenuList;

});
</script>
<body>

<style type="text/css">
#main {
    position: absolute;
    left: 0;
    top: -0.3rem;
    width: 100%;
    height: 100%
}
</style>
<table id="main" border="0" class="hide">
<tr><td height="100%" width="20%" valign="top"><!-- 1494487701 -->

<style type="text/css">
#menu_dir_1494319505 {
    margin: -0.8rem;
    overflow-x: hidden;
    overflow-y: auto;
    height: 100%;
}
#menu_dir_1494319505 .box {
    padding: 0.2rem;
    height: 95%;
}
#menu_dir_1494319505 .dir_list {
    overflow-y: auto;
    height: 100%;
}
#menu_dir_1494319505 .line {
    padding: 0.16rem;
    margin-top: 0.1rem;
    background: #DDDDDD;
    text-align: center;
    font-size: 1.6rem;
}
#menu_dir_1494319505 .line .food_num {
/*    padding: 0.2rem;*/
    margin-bottom: 0.1rem;
    border-radius: 50%;
    background-color: white;
    width: 1.6rem;
    height: 1.6rem;
    font-size: 1.2rem;
    visibility: hidden;
    margin-right: auto;
    margin-left: auto;
    vertical-align: middle;

    display: -webkit-box;
    display: -moz-box;
    display: -ms-flexbox;
    display: -o-box;
    display: box;

    -webkit-box-pack: center;
    -moz-box-pack: center;
    -ms-flex-pack: center;
    -o-box-pack: center;
    box-pack: center;

    -webkit-box-align: center;
    -moz-box-align: center;
    -ms-flex-align: center;
    -o-box-align: center;
    box-align: center;
}
</style>
<div id="menu_dir_1494319505">
<div class="box">
    <style type="text/css">
    #id_query_1454244045 {
        margin-top: 1rem;
        width: 100%;
        white-space: nowrap;
        overflow: hidden;
        vertical-align: middle;
    }
    #id_query_1454244045 .empty {
        font-style: italic;
        color: gray;
    }
    #id_query_1454244045 .box {
        display: -webkit-box;
        display: -moz-box;
        display: -ms-flexbox;
        display: -o-box;
        display: box;

        -webkit-box-pack: center;
        -moz-box-pack: center;
        -ms-flex-pack: center;
        -o-box-pack: center;
        box-pack: center;

        -webkit-box-align: center;
        -moz-box-align: center;
        -ms-flex-align: center;
        -o-box-align: center;
        box-align: center;
    }
    #id_query_1454244045 .box .food_name {
        background: #DCFFD5;
        position: absolute;
        top: 0;
        left: 0;
        padding: 0;  /* 在上面的animate()中设置 */
        width: 0;    /* 在上面的animate()中设置 */
        height: 2rem;
        overflow: hidden;
    }
    #id_query_1454244045 .box .food_name .value {
        border: 1px solid gray;
        /*border-radius: 10%;*/
        font-size: 1.4rem;
        width: 8rem;
        height: 2.00423rem;
    }
    #id_query_1454244045 .bt_fresh,
    #id_query_1454244045 .bt_query_open,
    #id_query_1454244045 .bt_query_find {
        cursor: pointer;
        vertical-align: middle;
        height: 2rem;
    }
    #id_query_1454244045 .bt_query_open {
        padding-left: 2.05343rem;
        padding-right: 2.05343rem;
    }
    #id_query_1454244045 .bt_query {
        margin-left: 0.2rem;
    }
    </style>
    <div id="id_query_1454244045" class="">
        <div class="box">
            <img src="img/fresh.png" class="bt_fresh hide"/>
            <img src="img/search.png" class="bt_query_open"/>
            <div class="food_name">
                <input type="text" class="value empty" />
                <img src="img/search.png" class="bt_query_find"/>
            </div>
        </div>
    </div>
    <div class="dir_list">
    <!-- 模板 -->
    <script id="tp_food_dir" type="text/html">
    {{each list as item i}}
        <div class="line hand" id="id_menu_dir_category_{{item.category_id}}" data-category_id="{{item.category_id}}">
            <div>&nbsp;</div>
            <div class="category_name hand">{{item.category_name}}</div>
            <div class="food_num"></div>
        </div>
    {{/each}}
    </script>
    <br/><br/><br/><br/><br/>
    </div>
    <div style="clear:both"></div>
</div>
</div><!-- id="menu_dir_1494319505" -->

</td><td><!-- 1494487701 -->

<style type="text/css">
#menu_list_1494319500 {
    border-left: 0.5rem solid #BDDD60;
    border-right: 0.1rem solid #BDDD60;
    padding: 0;
    overflow-y: auto;
    overflow-x: hidden;
    height: 100%;
    width: 99%;
    padding-top: 1rem;
}
#menu_list_1494319500 tr,
#menu_list_1494319500 td {
    padding: 0;
    margin: 0;
}
#menu_list_1494319500 .category > .value {
    background-color: #BDDD60;
    margin-bottom: 1px;
    margin-top: -0.9rem;
    margin-left: 2px;
    padding: 0.3rem;
    font-size: 1.6rem;
}
#menu_list_1494319500 .food_line {
    /*border-bottom: 2px dashed #BDDD60;*/
    padding: 0px;
    width: 100%;
    text-align: left;
    white-space: nowrap;
}
#menu_list_1494319500 .food_line .food_price .vip {
    /*border-bottom: 2px dashed #BDDD60;*/
    font-style: italic;
    color: #DF00E0;
}
#menu_list_1494319500 .food_unit_info {
    color: gray;
    font-size: 1.2rem;
}
#menu_list_1494319500 .food_line .food_img {
    /*width: 1px;*/
}
#menu_list_1494319500 .food_line .food_img img {
    height: 7rem;
    width: 7rem;
    border: 1px solid #E8E8E8;
}
#menu_list_1494319500 .food_line .food_name {
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 1.8rem;
    text-align: left;
    width: 16rem;
}
#menu_list_1494319500 .food_line .food_sold_num_day,
#menu_list_1494319500 .food_line .food_stock_num_day_txt {
    font-size: 1.2rem;
    color: gray;
}
#menu_list_1494319500 .food_line .food_num .num_add,
#menu_list_1494319500 .food_line .food_num .num_sub {
    width: 2.3rem;
}
#menu_list_1494319500 .food_line .food_num .num_value {
    /*font-size: 3rem;*/
}

#menu_list_1494319500 .food_line .food_attach_list .attach_item {
    font-size: 1.2086534rem;
    margin-left: 0.2rem;
    background-color: #b5bfaf;
}
</style>
<div id="menu_list_1494319500">
    <script id="tp_food_list" type="text/html">

    {{each list as item i}}
    <div class="category line"
            id="id_category_{{item.category_id}}"
            data-category_id="{{item.category_id}}">
        <div class="value">
        <a name="anchor_{{item.category_id}}">
        {{item.category_name}}
        </a>
        </div>
        {{if !item.food_list || item.food_list.length == 0}}
        <div style="height:1rem"></div>
        {{/if}}
        {{each item.food_list as item i}}
        {{if i > 0}}

        {{/if}}
        <table border="0" class="food_line hand" id="id_food_line_{{item.food_id}}"
                data-food_id="{{item.food_id}}" data-food_category="{{item.food_category}}">
            <tr>
                <!-- --------------------------------- -->
                <td>
                    <div class="food_img">
                        <img src="{{item.food_img_url}}"/>
                    </div>
                </td>

                <!-- --------------------------------- -->
                <td width="100%" valign="top">
                    <table border="0" class="txt_left" width="100%">
                        <tr><td width="100%">
                            <div class="food_name">{{item.food_name}}</div>
                        </td></tr>

                        <tr><td>
                            <div class="food_price">
                                ￥<span class="value">{{item.food_price}}</span>
                                {{if item.food_unit}}
                                    <span class="food_unit_info">
                                        (<span class="food_unit">{{item.food_unit}}</span>)
                                    </span>
                                {{/if}}
                                {{if item.is_vip_price}}
                                <span class="vip"> (会员价)</span>
                                {{/if}}
                            </div>
                        </td></tr>
                        <tr><td width="100%">
                            <div class="food_sold_num_day hide">
                            售出: <span class="value">{{item.food_sold_num_day}}</span>
                            </div>
                            <div class="food_stock_num_day_txt">
                            存量: <span class="value">{{#item.food_stock_num_day_txt}}</span>
                            </div>
                        </td></tr>
                        <tr><td>
                            <div class="food_attach_list">
                            <!-- 这里放已选好的口味 -->
                            <!-- <span class="attach_item">{{attach}}</span> -->
                            </div>
                        </td></tr>
                    </table>
                </td>

                <!-- --------------------------------- -->
                <td>
                    <table border="0" class="food_num">
                        <tr><td align="center">
                            <img src="img/add.png" class="num_add hand {{item.num_add_status}}" />
                        </td></tr>

                        <tr><td align="center">
                            <span class="num_value hide"></span>
                        </td></tr>

                        <tr><td align="center">
                            <img src="img/sub.png" class="num_sub hand hide" />
                        </td></tr>
                    </table>
                </td>
            </tr>
        </table>
        <div style="border-bottom: 2px dashed #BDDD60; margin: 5px 5px;"></div>
        {{/each}}
    </div>
    {{/each}}
    </script>
    <br><br><br><br><br><br>
</div>
<div style="clear:both"></div>


</td></tr><tr><td colspan="2" width="100%"><!-- 1494487701 -->

</td></tr><!-- 1494487701 -->
</table>

<!-- --------------------------底下操作栏-------------------------- -->
<script type="text/javascript">
$(function(){
    var BottomCtrl = new function(){
        function Init()
        {
            // ‘我’菜单
            Util.SimpleMenu($("#id_ordering_1494434664 .ordering_me"),
                [
                    "用户信息",
                    "我的订单"
                ],
                function(item){
                    if("用户信息" == item.title)
                    {
                        window.CustomerInfo.Open();
                    }
                    else if("我的订单" == item.title)
                    {
                        if($("#id_myorder_1494663243").length == 0)
                        {
                            $.get('menu.orderlist.html?'+Util.GetTimestamp(), function(resp){
                                $(resp).appendTo($("body"));
                                window.OrderList.Open();
                            });
                        }
                        else
                        {
                            window.OrderList.Open();
                        }
                    }
                    return false;
                },
                {
                    pop_up : 'right',
                    item_height : '3.3rem'
                }
            );

            // 下单
            $("#id_ordering_1494434664 .ordering_submit").click(function(){
                var order_info = window.CacheOrder.Get();
                window.OpenOrderDetail(order_info, function(mod_order_info){
                    window.CacheOrder.Set(mod_order_info);
                });
            })
        }

        Init();
    }
});
</script>
<style type="text/css">
#id_ordering_1494434664 {
    position: absolute;
    left: 0;
    bottom: 0;
    background: #DCEDAC;
    width: 100%;
}
#id_ordering_1494434664 .box {
    padding: 0.5rem;
}
#id_ordering_1494434664 .col {
    margin: 0rem 0.4rem;
    padding: 0.5rem 1rem;
    border-radius: 40%;
    background-color: white;
    font-size: 1.4rem;
}
#id_ordering_1494434664 .ordering_num {
    float: left;
    margin-right: 1rem;
}
#id_ordering_1494434664 .ordering_price {
    float: left;
}
#id_ordering_1494434664 .ordering_submit {
    float: right;
}
#id_ordering_1494434664 .ordering_me {
    float: right;
}
</style>
<div id="id_ordering_1494434664" class="">
<div class="box">
    <div class="ordering_num col">
    菜数: <span class="value">0</span>
    </div>
    <div class="ordering_price col">
    总价: ￥<span class="value">0.0</span>
    </div>
    <div class="ordering_me hand col">
    我的信息
    </div>
    <div class="ordering_submit hand col">
    下单
    </div>
    <div style="clear:both"></div>
</div>
</div> <!-- id="id_ordering_1494434664" -->





<!-- --------------------用户信息（客人）-------------------- -->
<script type="text/javascript">
$(function() {
    var ShopInfo = new function(){
        var THIS = this;
        var shop = {};

        function ShopName()
        {
            return shop.shop_name;
        }
        THIS.ShopName = ShopName;

        function ShopId()
        {
            return shop.shop_id;
        }
        THIS.ShopId = ShopId;

        function GetSubMchId()
        {
            if(!shop.weixin)
            {
                return "";
            }
            return shop.weixin.sub_mch_id||"";
        }
        THIS.GetSubMchId = GetSubMchId;

        // 是否开通了微信支付
        THIS.IsSupportWexinPlay = function() {
            return GetSubMchId() != "" ? true : false;
        }

        THIS.Init = function(info) {
            shop = info;
        }
    }
    window.ShopInfo = ShopInfo;

    var SeatInfo = new function(){
        var THIS = this;
        var seat = {};

        THIS.GetData = function()
        {
            return seat;
        }

        function SeatId()
        {
            return seat.seat_id;
        }
        THIS.SeatId = SeatId;

        function SeatName()
        {
            return seat.seat_name;
        }
        THIS.SeatName = SeatName;

        THIS.SeatPrice = function(seat_price)
        {
            if(Util.IsDefine(seat_price))
            {
                seat.seat_price = seat_price;
            }
            else
            {
                return Util.Float(seat.seat_price);
            }
        }

        THIS.Init = function(info) {
            seat = info;
        }
    }
    window.SeatInfo = SeatInfo;

    var CustomerInfo = new function(){
        var THIS = this;
        var $doing = $("#id_user_1494696280 .ctrl .doing");
        var box = new $.FloatBox($("#id_user_1494696280"));
        var info = {};
        var customer = {};

        function Open()
        {
            box.Open();
            // ReLoad();
        }
        THIS.Open = Open;

        function ReLoad()
        {
            console.log("begin");
            LoadUser();
        }
        THIS.ReLoad = ReLoad;

        function LoadUser()
        {
            console.log("LoadUser");
            // var async = $.ajaxSettings.async;
            // $.ajaxSettings.async = false; // 同步执行
            $.getJSON("customer_get.php?" + Util.GetRandString(6),
                {
                    'openid'  : $.query.get("openid"),
                    'shop_id' : $.query.get("shop")||$.query.get("shop_id"),
                    'seat_id' : $.query.get("seat")||$.query.get("seat_id")
                },
                function(resp){
                    if(-30023 == resp.ret)
                    {
                        window.Msg.Open("<h2>座位出错，请重新扫码点餐.</h2>", {"bt_close":"hide"});
                        return;
                    }
                    if(-30024 == resp.ret)
                    {
                        window.Msg.Open("<h2>店铺不存在，请重新扫码点餐.</h2>", {"bt_close":"hide"});
                        return;
                    }
                    if(-30030 == resp.ret)
                    {
                        window.Msg.Open("<h2>" + errcode.toString(resp.ret) + "</h2>", {"bt_close":"hide"});
                        return;
                    }

                    if(resp.ret < 0)
                    {
                        window.Toast.Show(errcode.toString(resp.ret));
                        return;
                    }
                    if(!resp.data.customer)
                    {
                        window.Msg.Open("<h2>用户信息出错，请重新扫码点餐.</h2>", {"bt_close":"hide"});
                        return;
                    }
                    customer = resp.data.customer;

                    if(!resp.data.seat || !resp.data.seat.seat_id)
                    {
                        window.Msg.Open("<h2>餐位信息出错，请重新扫码点餐.", {"bt_close":"hide"});
                        return;
                    }
                    window.SeatInfo.Init(resp.data.seat);

                    if(!resp.data.shop)
                    {
                        window.Msg.Open("<h2>店铺信息出错，请重新扫码点餐.", {"bt_close":"hide"});
                        return;
                    }
                    window.ShopInfo.Init(resp.data.shop);

                    info = resp.data;
                    var shop = resp.data.shop||{};
                    $("#id_user_1494696280 .customer_id .value").Value(customer.customer_id);
                    $("#id_user_1494696280 .phone .value").Value(customer.phone);
                    $("#id_user_1494696280 .is_vip .value").Value(IsVipCustomer.toString(customer.is_vip));
                    $("#id_user_1494696280 .shop_name .value").Value(shop.shop_name);
                    $(document).Title(shop.shop_name + "【桌号:" + resp.data.seat.seat_name + "】");
                    window.MenuList.Init();
                }
            );
            // $.ajaxSettings.async = async;
        }

        function CustomerId()
        {
            return customer.customer_id;
        }
        THIS.CustomerId = CustomerId;

        function IsVip()
        {
            return 1 == customer.is_vip;
        }
        THIS.IsVip = IsVip;

        function Init()
        {
            ReLoad();
        }

        $("#id_user_1494696280 .ctrl .logout").click(function(){
            ReLoad();
        })
        $("#id_user_1494696280 .ctrl .close").click(function(){
            box.Close();
        })

        Init();
    }//end of var CustomerInfo = new function(...
    window.CustomerInfo = CustomerInfo;
});
</script>
<style type="text/css">
#id_user_1494696280 {
    display: none;
    background-color: white;
    padding: 0.3rem;
    width: 80%
}
#id_user_1494696280 .line {
    margin-top: 0.8rem;
    white-space: nowrap;
}
#id_user_1494696280 .title {
    float: left;
    width: 25%;
    text-align: right;
    margin-right: 1rem;
}
#id_user_1494696280 .value {
    width: 70%;
    height: 2rem;
    font-size: 1.8rem;
    border: 1px solid #DEDEDE;
    padding: 0.3rem;
}
#id_user_1494696280 .ctrl {
    text-align: right;
    margin: 1rem 1rem 0.3rem 0;
}
.btn {
    display: inline-block;
    min-width: 6rem;
    height: 2.7rem;
    line-height: 2.7rem;
    vertical-align: middle;
    text-align: center;
    font-size: 1.8rem;
    text-decoration: none;
    border-width: 1px;
    border-style: solid;
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;
    cursor: pointer;
    background: #44b549;
    border-color: #44b549;
    color: #ffffff;
}
</style>
<div id="id_user_1494696280">
<fieldset>
<legend>我的信息</legend>
    <div class="shop_name line">
        <div class="title">店铺名称 :</div>
        <input type="text" class="value noempty" readonly="readonly" />
    </div>
    <div class="customer_id line">
        <div class="title">用户ID :</div>
        <input type="text" class="value noempty" readonly="readonly" />
    </div>
    <div class="is_vip line">
        <div class="title">是否会员 : </div>
        <input type="text" class="value noempty" readonly="readonly" />
    </div>
    <div class="phone line">
        <div class="title">手机 :</div>
        <input type="text" class="value noempty" readonly="readonly" />
    </div>

    <div class="ctrl">
        <span class="doing align_left">&nbsp;</span>
        <!-- <a href="#" class="btn logout hide">注销</a> -->
        <a href="#" class="btn close">关闭</a>
    </div>
</fieldset>
</div><!-- id="id_user_1494696280" -->



<!-- --------------------Init-------------------- -->
<script type="text/javascript">
$(function() {

    // 查看当前缓存订单（可能是上次客人中途意外退出未处理完成的）
    var CacheOrder = new function() {
        var THIS = this;

        function Set(order_info)
        {
            window.Store.SetGlobalData("new_order_info",
                $.Json.toStr(order_info));
        }
        THIS.Set = Set;

        function Get()
        {
            return $.Json.toObj(
                window.Store.GetGlobalData("new_order_info", "{}")
            );
        }
        THIS.Get = Get;

        function Clear()
        {
            window.Store.SetGlobalData("new_order_info", "{}");
        }
        THIS.Clear = Clear;

        function Init()
        {
            var order_info = Get();
            // console.log(order_info);
            if(order_info.order_id) // 有单号，说明上次订单已保存到服务器，不需缓存
            {
                Clear();
                return;
            }

            // 不是当前店数据
            var shop_id = $.query.get("shop")||$.query.get("shop_id");
            if(order_info.shop_id != shop_id)
            {
                Clear();
                return;
            }

            // // 查看后台中数据（有可能和当前缓存不一致，如服务从管理页操作了）
            // Util.EncSubmit("order_get.php",
            //     {
            //         orderinfo : true,
            //         order_id  : order_info.order_id
            //     },
            //     function(resp){
            //         if(resp.ret < 0
            //             || !resp.data.info
            //             || OrderStatus.IsTimeout(resp.data.info.order_time)
            //             || !OrderStatus.CanModify(resp.data.info.order_status))
            //         {
            //             // 超时订单，清空
            //             Clear();
            //         }
            //         window.CustomerInfo.ReLoad();
            //     }
            // );
        }

        Init();
    }
    window.CacheOrder = CacheOrder;

    window.OpenOrderDetail = function(order_info, close_callback) {
        if(window.OrderDetail && window.OrderDetail.Open)
        {
            window.OrderDetail.Open(order_info, close_callback);
            return;
        }
    }

    $.get('menu.customer_num_box.html?'+Util.GetTimestamp(), function(resp){
        $("#id_customer_num_box_1497177584").html(resp);
    });

    $.get('menu.food_detail.html?'+Util.GetTimestamp(), function(resp){
        $("#id_food_detail_box_1498725416").html(resp);
    });

    $.get('menu.order_foodlist.html?'+Util.GetTimestamp(), function(resp){
        $("#id_order_detail_1497354012").html(resp);
    });

    $.get('menu.order_summary.html?'+Util.GetTimestamp(), function(resp){
        $("#id_order_summary_1498502226").html(resp);
    });
});
</script>

<div id="id_order_detail_1497354012"></div>
<div id="id_order_summary_1498502226"></div>
<div id="id_customer_num_box_1497177584"></div>
<div id="id_food_detail_box_1498725416"></div>



</body>
</html>
