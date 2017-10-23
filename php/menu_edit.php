<?php
/*
 * [Rocky 2017-04-25 19:25:51]
 * 菜单编辑
 *
 */
require_once("current_dir_env.php");
require_once("mgo_menu.php");

$food_id = $_GET['id'];
if(empty($food_id))
{
    require_once("redis_id.php");
    $food_id = \DaoRedis\Id::GenFoodId();
}

// 页面中变量
$html = (object)array(
    'title' => '餐品编辑',
    'food_id' => $food_id
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

    // function Printer($owner)
    // {
    //     var THIS = this;
    //     var $doing = $("#id_printer_1493870670 .doing");
    //     var printer_box = $.FloatBox($("#id_printer_1493870670"));
    //     var $printer_id = $("#id_printer_1493870670 .printer_id .value");
    //     var $printer_name = $("#id_printer_1493870670 .printer_name .value");
    //     var menu = null;    // 供外部使用

    //     function Open()
    //     {
    //         var printer = (menu && menu.GetData()) || null;
    //         if(printer)
    //         {
    //             $printer_id.val(printer.printer_id);
    //             $printer_name.val(printer.printer_name);
    //         }
    //         else
    //         {
    //             NewPrinter();
    //         }

    //         printer_box.Open();
    //     }
    //     THIS.Open = Open;

    //     // 加载完成调用callback
    //     function Reload(callback)
    //     {
    //         $doing.html("正在加载...").SetPromptStyle();
    //         Util.EncSubmit("printer_get.php",
    //             {
    //                 list : 1
    //             },
    //             function(resp){
    //                 if(resp.ret < 0)
    //                 {
    //                     $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
    //                     return;
    //                 }
    //                 Fill(resp.data.list||[]);
    //                 if($.isFunction(callback))
    //                 {
    //                     callback();
    //                 }
    //                 $doing.html("");
    //             }
    //         );

    //         function Fill(list)
    //         {
    //             var menulist = [
    //                 // {
    //                 //     value: "",
    //                 //     title: "<span class='size14 italic gray'>--[未选择]--</span>",
    //                 //     ishtml: true
    //                 // }
    //             ];
    //             for(i in list)
    //             {
    //                 var item = list[i];
    //                 menulist.push({
    //                     value:item.printer_id,
    //                     title:item.printer_name,
    //                     data:item
    //                 });
    //             }

    //             menu = new Util.Menu($owner,
    //                 menulist,
    //                 function(data){
    //                     //$owner.data('value', data.value);
    //                 },
    //                 {max_height:400}
    //             );
    //         }
    //     }

    //     function Select(printer_id)
    //     {
    //         if(null == menu)
    //         {
    //             // 未加载时，先加载后再设置
    //             Reload(function(){
    //                 menu.SetValue(printer_id);
    //             })
    //             return;
    //         }
    //         menu.SetValue(printer_id);
    //     }
    //     THIS.Select = Select;

    //     function GetValue()
    //     {
    //         if(null == menu)
    //         {
    //             return "";
    //         }
    //         return menu.GetValue();
    //     }
    //     THIS.GetValue = GetValue;

    //     function GetOwner()
    //     {
    //         return $owner;
    //     }
    //     THIS.GetOwner = GetOwner;

    //     function Save()
    //     {
    //         $doing.html("正在保存...").SetPromptStyle();
    //         Util.EncSubmit("printer_save.php",
    //             {
    //                 printer_id   : $printer_id.val().trim(),
    //                 printer_name : $printer_name.val().trim(),
    //                 save          : 1
    //             },
    //             function(resp){
    //                 if(resp.ret < 0)
    //                 {
    //                     $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
    //                     return;
    //                 }
    //                 Reload();
    //                 $doing.html("已保存").SetOkStyle();
    //             }
    //         );
    //     }

    //     function NewPrinter(){
    //         Util.EncSubmit("gen_id.php",
    //             {
    //                 genid : 1,
    //                 type  : "printer"
    //             },
    //             function(resp){
    //                 if(resp.ret < 0)
    //                 {
    //                     $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
    //                     return;
    //                 }
    //                 $printer_id.val(resp.data.id);
    //                 $printer_name.val("");
    //             }
    //         );
    //     }


    //     $("#id_printer_1493870670 .ctrl .bt_new").click(function(){
    //         NewPrinter();
    //     });

    //     $("#id_printer_1493870670 .ctrl .bt_ok").click(function(){
    //         Save();
    //     });

    //     $("#id_printer_1493870670 .ctrl .bt_close").click(function(){
    //         printer_box.Close();
    //     });

    //     // Reload();
    // }//end of var Printer = new function(){...

    function FoodCategory($owner)
    {
        var THIS = this;
        var $doing = $("#id_category_1493192417 .doing");
        var category_box = $.FloatBox($("#id_category_1493192417"));
        var $category_id = $("#id_category_1493192417 .category_id .value");
        var $category_name = $("#id_category_1493192417 .category_name .value");
        // var printer_id = new Printer($("#id_category_1493192417 .printer_id .value"));
        var menu = null;    // 供外部使用

        function Open()
        {
            var category = (menu && menu.GetData()) || null;
            if(category)
            {
                $category_id.val(category.category_id);
                $category_name.val(category.category_name);
                // printer_id.Select(category.printer_id);
            }
            else
            {
                NewCategory();
            }

            category_box.Open();
        }
        THIS.Open = Open;

        //
        function Reload(callback)
        {
            $doing.html("正在加载...").SetPromptStyle();
            Util.EncSubmit("category_get.php",
                {
                    list : 1
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        return;
                    }
                    Fill(resp.data.list||[]);
                    if($.isFunction(callback))
                    {
                        callback();
                    }
                    $doing.html("");
                }
            );

            function Fill(list)
            {
                var menulist = [
                    // {
                    //     value: "",
                    //     title: "<span class='size14 italic gray'>--[未选择]--</span>",
                    //     ishtml: true
                    // }
                ];
                for(i in list)
                {
                    var item = list[i];
                    menulist.push({
                        value:item.category_id,
                        title:item.category_name,
                        data:item
                    });
                }

                menu = new Util.Menu($owner,
                    menulist,
                    function(data){
                        //$owner.data('value', data.value);
                    },
                    {max_height:400}
                );
            }
        }

        function Select(category_id){
            if(null == menu)
            {
                // 未加载时，先加载后再设置
                Reload(function(){
                    menu.SetValue(category_id);
                })
                return;
            }
            menu.SetValue(category_id);
        }
        THIS.Select = Select;

        function GetValue()
        {
            return menu.GetValue();
        }
        THIS.GetValue = GetValue;

        function GetOwner()
        {
            return $owner;
        }
        THIS.GetOwner = GetOwner;

        function Save()
        {
            if(!$category_name.CheckValue())
            {
                $doing.html("名称不能为空").SetErrStyle();
                return;
            }

            // if(!printer_id.GetOwner().CheckValue())
            // {
            //     $doing.html("打印机不能为空").SetErrStyle();
            //     return;
            // }

            $doing.html("正在保存...").SetPromptStyle();
            Util.EncSubmit("category_save.php",
                {
                    category_id   : $category_id.val().trim(),
                    category_name : $category_name.val().trim(),
                    // printer_id    : printer_id.GetValue(),
                    save          : 1
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        return;
                    }
                    Reload();
                    $doing.html("已保存").SetOkStyle();
                }
            );
        }

        function NewCategory(){
            Util.EncSubmit("gen_id.php",
                {
                    genid : 1,
                    type  : "category"
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                        return;
                    }
                    $category_id.val(resp.data.id);
                    $category_name.val("");
                }
            );
        }

        // $("#id_category_1493192417 .printer_id .setting").click(function(){
        //     printer_id.Open();
        // });

        $("#id_category_1493192417 .ctrl .bt_new").click(function(){
            NewCategory();
        });

        $("#id_category_1493192417 .ctrl .bt_ok").click(function(){
            Save();
        });

        $("#id_category_1493192417 .ctrl .bt_close").click(function(){
            category_box.Close();
        });

        // Reload();
    }//end of var FoodCategory = new function(){...

    var FoodInfo = new function(){
        var $doing = $("#id_food_1454245190 .doing");
        var $food_id = $("#id_food_1454245190 .food_id .value").val("<?=$html->food_id?>");
        var $food_name = $("#id_food_1454245190 .food_name .value");
        var food_category = new FoodCategory($("#id_food_1454245190 .food_category .value"));
        var $food_price = $("#id_food_1454245190 .food_price .value");
        var $food_vip_price = $("#id_food_1454245190 .food_vip_price .value");
        var food_img_list = new FoodImgDeal();
        var $food_intro = $("#id_food_1454245190 .food_intro .value");

        function Reload()
        {
            if($food_id.val() == "")
            {
                return;
            }
            var $all_button = $("#id_food_1454245190 input");
            $all_button.Disabled(true);
            $doing.html("正在加载...").SetPromptStyle();

            Util.EncSubmit("menu_get.php",
                {
                    food_id : $food_id.val().trim(),
                    foodinfo: true
                },
                function(resp){
                    if(resp.ret < 0)
                    {
                        $doing.html(errcode.toString(resp.ret)).SetErrStyle();
                        $all_button.Disabled(false);
                        return;
                    }
                    UpdateUi(resp.data.info);
                    $all_button.Disabled(false);
                    $doing.html("");
                }
            );

            function UpdateUi(info)
            {
                if(!info)
                {
                    return
                }
                //$("#id_food_1454245190 .food_id .value").val(info.food_id||"");
                $food_name.val(info.food_name||"");
                food_category.Select(info.food_category);
                $food_price.val(info.food_price||"");
                $food_vip_price.val(info.food_vip_price||"");
                food_img_list.Set(info.food_img_list||[]);
                $food_intro.val(info.food_intro||"");
            }
        }

        function Save()
        {
            if(!$food_name.CheckValue())
            {
                $doing.html("餐品名称不能为空").SetErrStyle();
                return;
            }

            if(!food_category.GetOwner().CheckValue())
            {
                $doing.html("餐品类别不能为空").SetErrStyle();
                return;
            }

            if(!$food_price.CheckValue())
            {
                $doing.html("餐品单价不能为空").SetErrStyle();
                return;
            }

            var $all_button = $("#id_ctrl_1454301572 input");
            $all_button.Disabled(true);
            $doing.html("正在保存...").show().SetPromptStyle();

            Util.EncSubmit("menu_save.php",
                    {
                       save          : true,
                       food_id       : $food_id.val().trim(),
                       food_name     : $food_name.val().trim(),
                       food_category : food_category.GetValue()||0,
                       food_price    : $food_price.val().trim()||0,
                       food_vip_price: $food_vip_price.val().trim()||0,
                       food_img_list : $.Json.toStr(food_img_list.GetImgList()),
                       food_intro    : $food_intro.val().trim()
                    },
                    function(resp){
                        if(resp.ret < 0)
                        {
                            $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                            $all_button.Disabled(false);
                            return;
                        }
                        $all_button.Disabled(false);
                        $doing.html("保存成功");
                    }
            );
        }

        function FoodImgDeal()
        {
            var $progress = $("#id_food_1454245190 .food_img_list .progress");

            function Set(list)
            {
                if(typeof list != "object")
                {
                    return;
                }
                list.forEach(function(imgname){
                    if(Find(imgname))
                    {
                        return;
                    }
                    var url = "img_get.php?img=1&imgname=" + encodeURIComponent(imgname);
                    var $img = $('<img/>').attr("src", url).data("imgname", imgname).load(function(){
                        // $("#id_food_1454245190 .food_img_list .value .bottom").append($img);
                    }).dblclick(function(){
                        var imgname = $(this).data('imgname');
                        var $p = Find(imgname);
                        if($p)
                        {
                            $p.remove();
                        }
                    });
                    $("#id_food_1454245190 .food_img_list .value .bottom").append($img);
                });
            }
            this.Set = Set;

            function Find(imgname)
            {
                var list = $("#id_food_1454245190 .food_img_list .value img");
                for(var i=0; i<list.length; i++)
                {
                    var $img = $(list[i]);
                    if($img.data("imgname") == imgname)
                    {
                        return $img;
                    }
                }
                return null;
            }

            function Delete(imgname)
            {
                Util.EncSubmit("img_save.php",
                        {
                            delete  : true,
                            imgname : imgname
                        },
                        function(resp){
                            if(resp.ret < 0)
                            {
                                $doing.html(errcode.toString(resp.ret)).SetErrStyle();
                                $all_button.Disabled(false);
                                return;
                            }
                            UpdateUi(resp.data.info);
                            $all_button.Disabled(false);
                            $doing.html("");
                        }
                );
            }

            function UploadImg()
            {
                var imglist = GetImgList();
                if(imglist && imglist.length >= 5)
                {
                    $doing.html("图片过多（最多5张）").SetErrStyle();  // MAX_FOODIMG_NUM
                    return;
                }
                var upload_file = $("#id_food_1454245190 .food_img_list .file").get(0).files[0]

                var args = {
                    'upload' : 1
                };

                // 只取加密后参数，不执行提交；
                var submit_param = Util.EncSubmit("", args, null, {"is_get_param":1});
                if(!submit_param)
                {
                    $doing.html("系统出错").SetErrStyle();
                    return;
                }

                /*
                 * 改用html5处理
                 */
                var formdata = new FormData();
                for(var i in submit_param)
                {
                    formdata.append(i, submit_param[i]);
                }
                formdata.append("imgfile", upload_file);

                $progress.html("").show().SetPromptStyle();
                $.ajax({
                    url         : 'img_save.php',
                    type        : 'POST',
                    cache       : false,
                    data        : formdata,
                    dataType    : "json",
                    processData : false,
                    contentType : false,
                    xhr : function(){
                        var myXhr = $.ajaxSettings.xhr();
                        if(myXhr.upload){
                            myXhr.upload.addEventListener('progress', Progress, false);
                        }
                        return myXhr; //xhr对象返回给jQuery使用
                    },
                    success: function(resp){
                        if(resp.ret < 0)
                        {
                            $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                            return;
                        }
                        $progress.html("上传完成").SetOkStyle().fadeOut();
                        Set([resp.data.filename]);
                    },
                    error: function(resp){
                        $("#id1458203035").Disabled(false);
                        $progress.html("上传出错").SetErrStyle();
                    },
                    complete: function(resp){
                        $progress.html("");
                        return;
                    }
                });

                //上传进度回调函数：
                function Progress(e) {
                    if (e.lengthComputable) {
                        var percent = (e.loaded/e.total*100).toFixed(2);
                        $progress.html("" + percent + "%");
                    }
                }
            }//end of function Upload(...

            function FreshImgList()
            {
                $("#id_food_1454245190 .food_img_list .value img").remove();
                Util.EncSubmit("menu_get.php",
                    {
                        food_id : $food_id.val().trim(),
                        foodinfo: true
                    },
                    function(resp){
                        if(resp.ret < 0)
                        {
                            $doing.html(errcode.toString(resp.ret)).SetErrStyle();
                            $all_button.Disabled(false);
                            return;
                        }
                        food_img_list.Set(resp.data.info.food_img_list||[]);
                    }
                );
            }

            function GetImgList()
            {
                var list = [];
                $("#id_food_1454245190 .food_img_list .value img").each(function(){
                    list.push($(this).data("imgname"));
                });
                return list;
            }
            this.GetImgList = GetImgList;

            // 文件选择
            $("#id_food_1454245190 .food_img_list .bt_upload").click(function(){
                $("#id_food_1454245190 .food_img_list .file").trigger('click');
                return;
            })
            $("#id_food_1454245190 .food_img_list .file").change(function(){
                UploadImg();
                return;
            })

            $("#id_food_1454245190 .food_img_list .fresh").click(function(){
                FreshImgList();
            })
        }

        $("#id_food_1454245190 .ok").click(function(){
            Save();
        });

        $("#id_food_1454245190 .back").click(function(){
            location.href = "menu.php";
        });

        $("#id_food_1454245190 .food_category .setting").click(function(){
            food_category.Open();
        });

        // setTimeout(function(){
        Reload();
        // }, 1000);
    }//end of var FoodInfo = new function(){...
});
</script>


<body>
<table border="0" class="txt_left" width="98%"><tr><td>


<style type="text/css">
#id_food_1454245190 {
    font-size: 20px;
    margin-top: 20px;
}
#id_food_1454245190 .value {
    width: 280px;
    height: 30px;
    font-size: 20px;
}
#id_food_1454245190 .line {
    margin: 8px 8px;
}
#id_food_1454245190 .title {
    float: left;
    width: 160px;
    text-align: right;
    margin-right: 10px;
}
#id_food_1454245190 .food_intro .value {
    height: 60px;
}
#id_food_1454245190 .food_img_list .bt_upload {
    width: 60px;
    height: 60px;
    font-size: 40px;
    vertical-align: middle;
    border: 2px dashed #DEDEDE;
}
#id_food_1454245190 .food_img_list .value img {
    width: 80px;
    height: 80px;
    vertical-align: middle;
    border: 2px solid #DEDEDE;
    margin-right: 5px;
}
#id_food_1454245190 .ctrl {
    text-align: right;
    width: 600px;
}
</style>

<div id="id_food_1454245190">
    <div class="food_id line">
        <div class="title">餐品id : </div>
        <input type="text" class="value noempty" readonly="readonly" /><font color="red">*</font>
    </div>
    <div class="food_name line">
        <div class="title">餐品名称 :</div>
        <input type="text" class="value noempty" /><font color="red">*</font>
    </div>
    <div class="food_category line">
        <div class="title">餐品类别 :</div>
        <input type="text" class="value noempty hand" readonly="readonly" />
        <font color="red">*</font>
        <a class="setting hand gray italic size16">[设置]</a>
    </div>
    <div class="food_price line">
        <div class="title">餐品单价(元) :</div>
        <input type="text" class="value noempty" /><font color="red">*</font>
    </div>
    <div class="food_vip_price line">
        <div class="title">餐品会员价(元) :</div>
        <input type="text" class="value noempty" /><font color="red">*</font>
    </div>
    <div class="food_img_list line">
        <input type="file" size="30" class="file hide" />
        <div class="title fresh hand" title="刷新图片列表">餐品照片 :</div>
        <span type="text" class="value" title="双击删除图片">
            <span class="bottom"></span>
            <font class="bt_upload hand" title="添加图片">＋</font>
        </span>
        <span class="progress">&nbsp;</span>
    </div>
    <div class="food_intro line">
        <div class="title">餐品介绍 :</div> <textarea class="value"></textarea>
    </div>

    <div class="ctrl">
        <span class="doing align_left">&nbsp;</span>
        <input type="button" class="ok" value="保存" title="快捷键：Ctrl + Enter" />
        <input type="button" class="back" value="返回" />
    </div>
</div>


<!-- ---------------------------类别设置--------------------------- -->
<style type="text/css">
#id_category_1493192417 {
    font-size: 20px;
    margin-top: 20px;
    padding: 3px;
    background-color: white;
}
#id_category_1493192417 .value {
    width: 200px;
    height: 30px;
    font-size: 20px;
}
#id_category_1493192417 .category_id,
#id_category_1493192417 .category_name,
#id_category_1493192417 .printer_id {
    margin-top: 8px;
}
#id_category_1493192417 .title {
    float: left;
    width: 130px;
    text-align: right;
    margin-right: 10px;
}
#id_category_1493192417 .ctrl {
    text-align: right;
    width: 400px;
    margin-top: 10px;
}
#id_category_1493192417 .ctrl .bt_new {
    font-color: green;
    font-size: 18px;
}
</style>

<div id="id_category_1493192417" class="hide">
<fieldset>
<legend>类别设置</legend>

    <div class="category_id">
        <div class="title">类别ID : </div>
        <input type="text" class="value noempty" readonly="readonly" /><font color="red">*</font>
    </div>
    <div class="category_name">
        <div class="title">类别名称 : </div>
        <input type="text" class="value noempty" /><font color="red">*</font>
    </div>
    <div class="printer_id hide">
        <div class="title">票据打印机 : </div>
        <input type="text" class="value noempty hand"  readonly="readonly"/><font color="red">*</font>
        <a class="setting hand gray italic size16">[设置]</a>
    </div>
    <div class="ctrl">
        <span class="doing align_left">&nbsp;</span>
        <a class="bt_new hand green" >添加新类别</a>
        <input type="button" class="bt_ok" value="保存" />
        <input type="button" class="bt_close" value="关闭" />
        <!-- <input type="button" class="bt_new" value="添加" /> -->
    </div>

</fieldset>
</div>



<!-- ---------------------------打印机设置--------------------------- -->
<style type="text/css">
#id_printer_1493870670 {
    font-size: 20px;
    margin-top: 20px;
    padding: 3px;
    background-color: white;
}
#id_printer_1493870670 .value {
    width: 200px;
    height: 30px;
    font-size: 20px;
}
#id_printer_1493870670 .printer_id,
#id_printer_1493870670 .printer_name {
    margin-top: 5px;
}
#id_printer_1493870670 .title {
    float: left;
    width: 130px;
    text-align: right;
    margin-right: 10px;
}
#id_printer_1493870670 .ctrl {
    text-align: right;
    width: 400px;
    margin-top: 10px;
}
</style>

<div id="id_printer_1493870670" class="hide">
<fieldset>
<legend>打印机设置</legend>

    <div class="printer_id">
        <div class="title">打印机ID : </div>
        <input type="text" class="value noempty" readonly="readonly" /><font color="red">*</font>
    </div>
    <div class="printer_name">
        <div class="title">打印机名称 : </div>
        <input type="text" class="value noempty" /><font color="red">*</font>
    </div>
    <div class="ctrl">
        <span class="doing align_left">&nbsp;</span>
        <a class="bt_new hand green" >添加新打印机</a>
        <input type="button" class="bt_ok" value="保存" />
        <input type="button" class="bt_close" value="关闭" />
        <!-- <input type="button" class="bt_new" value="添加" /> -->
    </div>

</fieldset>
</div>

</tr></td></table>
</body>
</html>
