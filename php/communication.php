<?php
//HJL创建的员工留言区
//加载配置文件
require_once ("current_dir_env.php");
//头部标题
$html=(object)array(
    'title'=>'留言区'
);
?><?php /******************************以下为html代码******************************/?>
<!--加载头部文件-->
<?php require ("template/pageheader.php");?>
<script type="text/javascript">
    $(function() {
        //console.log(&$resp);
        //console.log("communication.php");

        var CommunicationList = new function () {
            var THIS = this;
            //console.debug(THIS);
            var $doing = $("#id_setuser_box_1413132417 .doing");
            var $content = $("#id_setuser_box_1413132417 .content");
            var $title = $("#id_setuser_box_1413132417 .title");
            var $content_id = $("#id_setuser_box_1413132417 .content_id");

            function DoCommunication() {
                //$doing.html("留言成功").show().SetPromptStyle();
                Util.EncSubmit("communication_save.php",
                    {
                        save: 1,
                        content: $content.val().trim(),
                        title: $title.val().trim(),
                        content_id: $content_id.val().trim()
                    },
                )
            }

            function GetList(list) {
                console.log(list);//为什么是打印机的数据呢
                if (!list) {
                    return;
                }
                var data = {
                    list: list//分配获取的数据
                };
                console.log(data);
                //后台数据转为前台数据，list是后台传送过来的json数据
                for (i in list) {
                    var item = list[i];
//                    if ("" == item.content_id) {
//                        continue;
                }
                    data.list.push({
                        content: item.cotent,
                        title: item.title,
                        c_name: item.c_name,
                        c_time: item.c_time,
                        content_id: item.content_id
                    });

               // console.log(data);
                var html = template('tp_communication_list', data).trim();
                $("#tp_communication_list").prevAll(".line").remove();
                var $tr_ary = $(html).insertBefore("#tp_communication_list");
                $tr_ary.find(".open_edit").click(function () {
                    var content_id = $($this).text();
                    window.CommunicationList.Open(content_id);
                })

            }

            function ReLoad() {
                var $all_button = $("");
               // console.log($all_button);
                $all_button.Disabled(true);
                Util.EncSubmit("communication_get.php",
                    {
                        list: true
                    },

                    function (resp) {
                    //console.log(resp);//开始没有数据是因为我没有发送给前台
                        if (resp.ret < 0) {
                            //提示错误信息
                            //console.log(resp.ret);
                            $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                            $all_button.Disabled(false);
                            return 0;
                        }
                        SetUi(resp.data);
                        $all_button.Disabled(false);
                        $doing.html("");
                    }
                );
                function SetUi(data) {
                    //console.log(data);
                    GetList(data.list);
                }
            }

            THIS.ReLoad = ReLoad;
            function Init() {
                ReLoad();
                new Util.RememberMenu("id_1498746371", {});
            }

            //这个是保存事件
            $("#id_setuser_box_1413132417 .ok").click(function () {
                DoCommunication();
            })

            Init();
        }
        window.CommunicationList = CommunicationList;
    });
    </script>

    <style>
        .keleyitable {
            width: 800px;
        }

        .keleyitable table, td, th {
            border: 1px solid green;margin-top:10px;
        }
        .klytd {width:100px;text-align:right
        }
        .hvttd {
            width:500px}


    </style>
<body>
<div style="margin:0px auto;" class="keleyitable"><h2>留言列表</h2>

    <div id="id_communication_list_1207" class="table_hover table_head" border="1" width="99%">
    <script id="tp_communication_list" type="text/html">
    {{each list as item i}}
    <p><div class="content_id hand open_edit">留言ID:{{item.content_id}}&nbsp;&nbsp;留言时间:{{item.c_time}}
        &nbsp;&nbsp;留言人:{{item.c_name}}</div></p>
        <p><div class="c_name hand open_edit">留言标题:{{item.title}}</div></p>
        <p><div class="title hand open_edit">留言内容:{{item.content}}&nbsp;&nbsp;<a>回复</a></div></p>

    {{/each}}
    </script>
    </div>
</div>
<div style="margin:0px auto;" class="keleyitable"><h2>我要留言</h2>
    <style type="text/css">
        #id_1498746371{
            width: 19rem;
            margin: 0.3rem;
            height: 2rem;
            margin-top: 0.8rem;
            font-size: 2.4rem;
        }
        #id_2 {
            width: 50rem;
            margin: 0.3rem;
            height: 10rem;
            margin-top: 0.8rem;
            font-size: 2.4rem;
        }
    </style>
    <div id="id_setuser_box_1413132417" class="popup_box">
    <fieldset>
        <legend>留言区域</legend>
        <div>
            　<p>留言标题：</p>
            <p><input class="title" id="id_1498746371" /></p>
            <p>留言内容：</p>
            <p><textarea class="content" id="id_2"></textarea></p>
            <p><input class="content_id" type="hidden" value="<?php echo time().rand(0,9);?>"></p>

        </div>
        <div class="ctrl">
            <span class="doing"></span>
            <input type="button" class="ok" value="确定" />
        </div>
    </fieldset>
    </div>
<div style="width:800px;margin:10px auto;font-family:Arial, Helvetica, sans-serif;text-align:center;">赛领科技 &copy; 2017 www.sailing.com </div>

</body>

