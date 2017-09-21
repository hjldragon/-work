/*
 * 通用函数
 * QQ:15586350 [rockyshi 2014-04-02]
 */
//window.onerror = function(sMessage, sUrl, sLine)
//{
//    var err = "window.onerror()报告:\n"
//              + "\nURL   : " + sUrl
//              + "\nLine  : " + sLine
//              + "\nEerror: " + sMessage;
//    Util.Alert(err);
//    return true;
//}

var Util = {
    /*
     *  功能：重写Util.Alert()，方便调试
     *  编写：Rocky 2009-12-07
     */
    Alert : function(/* 可变参数 */)
    {
        Util.Alert.i = (Util.Alert.i || 0) + 1;
        alert('----------[' + Util.Alert.i + ']----------\r\n' + '[' + [].slice.call(arguments).join(']\r\n******[') + ']');
    },
    // 取[begin, end]间的数据整数
    GetRandom : function(begin, end)
    {
        var num = Math.random() * 100000000;
        return Math.floor( num % (end-begin+1) + begin );
    },
    // 取len长的随机字符串
    GetRandString : function(len, range)
    {
        range = range || "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        var s = range.split('');
        var ret = '';
        for(var i=0; i<len; i++)
        {
            ret += s[ this.GetRandom(0, s.length-1) ];
        }
        return ret;
    },
    /*
     * other : {
     *      //dataType: xxx,
     *      mimeType: xxx,
     *      is_get_param: 1,            // 只返回要提交的参数（不执行提交动作）
     *      async: true                 // [true-异步, false-同步]
     *      encmode: xxx                // 数据加密方式[""|encrypt1]
     *      ...
     * }
     */
    EncSubmit : function(url, data, resp_func/*, other --> {dataType:xxx, mimeType:xxx, ...}*/)
    {
        if(! data instanceof Object)
        {
            // param err
            return;
        }
        var THIS = this;

        // 当前终端的标识
        var token = window.Store.GetGlobalData("token");
        if(!token)
        {
            token = "T1" + Util.GetRandString(14);
            window.Store.SetGlobalData("token", token);
        }

        // 前后台数据加密（验证）用随机密码
        var key = window.Store.GetGlobalData('key');
        if(!key)
        {
            ////////////// 下面几步使用同步请求方式 //////////////
            // 取公钥
            var publickey = (function(){
                var pubkey = "";
                $.ajax({
                    url: "rsa_info.php?" + Util.GetRandString(3),
                    dataType: "json",
                    async: false,
                    data: {
                        "publickey": 1
                    },
                    success: function(resp){
                        pubkey = resp.data.publickey;
                    }
                });
                return pubkey;
            })();

            // 加载rsa操作类（注：因rsa不常用，故动太加载，同步方式）
            $.ajax({
                url: "js/jsencrypt.js?" + Util.GetRandString(3),
                dataType: "script",
                async: false
            });

            // 提交key到服务器
            var submit_key_ok = (function(){
                key = Util.GetRandString(16); // 随机key
                console.log("key:" + key);
                var rsa = new JSEncrypt();
                rsa.setPublicKey(publickey);
                var key_enc = rsa.encrypt(key);
                var ok = false;
                $.ajax({
                    url: "rsa_info.php?" + Util.GetRandString(3),
                    dataType: "json",
                    async: false,
                    data: {
                        "save_key" : 1,
                        "is_plain" : 1, // 是明文（配合后台DecSubmitData()用）
                        "key_enc"  : key_enc,
                        "token"    : token
                    },
                    success: function(response,status){
                        ok = true;
                    }
                });
                return ok;
            })();

            if(!submit_key_ok)
            {
                return;
            }
            window.Store.SetGlobalData('key', key);
        }

        var data_type = null;
        var mime_type = null;
        var is_get_param = null;
        var is_async = true; // 默认异步
        var encmode = "";
        if(arguments.length > 3 && $.isPlainObject(arguments[3])) // 可变参数时
        {
            data_type    = "json"; //arguments[3].dataType;
            mime_type    = arguments[3].mimeType;
            is_get_param = arguments[3].is_get_param;
            if(Util.IsDefine(arguments[3].async))
            {
                is_async = arguments[3].async;
            }
            encmode    = arguments[3].encmode;
        }

        datastr = $.param(data);
        if("encrypt1" == encmode)
        {
            datastr = encrypt(key, datastr)
        }
        var param = {
                token   : token,
                userid  : window.Store.GetGlobalData('userid'),
                encmode : encmode,
                data    : datastr,
                sign    : $.md5(datastr + key)
        };

        // 只取参数
        if(is_get_param)
        {
            return param;
        }

        $.ajax({
            url      : url + "?" + (new Date()).getTime(),
            type     : "POST",
            async    : is_async,
            dataType : data_type || "json",
            mimeType : mime_type,
            timeout  : 60000000,
            data     : param,
            success: function(resp){
                resp = resp||{};
                if(-10020 == resp.ret)  // USER_NOLOGIN
                {
                    Public.ShowLoginBox();  // public.js
                    //return;
                }
                else if(-20011 == resp.ret)  // USER_PERMISSION_ERR
                {
                    var html = "<div style='margin:40px; font-size:32px; color:red;'>"
                             + errcode.toString(resp.ret)
                             + " <span style='font-size:20px; color:red; font-style: italic;'>"
                             + "[<a href='#' onclick='Public.ShowLoginBox({relogin:1})'>重新登录</a>]"
                             + "</span>"
                             + "</div>"
                             + "";
                    Public.MsgBox(html, {bt_close:true});
                }
                else if(-10022 == resp.ret)  // DATA_KEY_NOT_EXIST
                {
                    window.Store.SetGlobalData('key', '');
                }
                if($.isFunction(resp_func))
                {
                    if(0 === resp.ret && resp.crypt == "1" && resp.data !== "")
                    {
                        resp.data = $.Json.toObj(decrypt(key, resp.data))
                        delete resp.crypt;
                    }
                    return resp_func(resp);
                }
            },
            error: function(resp){
                if($.isFunction(resp_func))
                {
                    var ret = {
                        ret  : -1,
                        data : "resp.responseText: " + resp.responseText
                    };
                    return resp_func(ret);
                }
            }
        });// end of $.ajax({...
    },// end of EncSubmit : function(...
    // 取时间戳(秒)
    //  timestr = '2014-04-23 18:55:49:123';
    GetTimestamp : function(timestr)
    {
        var t;
        if(timestr)
        {
            t = new Date(timestr);
        }
        else
        {
            t = new Date();
        }

        return parseInt(t.getTime() / 1000);
    },
    /* 时间格式转换
     *
     * 对Date的扩展，将 Date 转化为指定格式的String
     * 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符，
     * 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)
     * 例子：
     * (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
     * (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18
     *
     * 参考: http://www.cnblogs.com/zhangpengshou/archive/2012/07/19/2599053.html
     */
    TimeTo : function(timestamp, format)
    {
        var t = timestamp > 0 ? new Date(timestamp * 1000) : new Date();
        var o = {
            "M+": t.getMonth() + 1,                      //月份
            "d+": t.getDate(),                           //日
            "h+": t.getHours(),                          //小时
            "m+": t.getMinutes(),                        //分
            "s+": t.getSeconds(),                        //秒
            "q+": Math.floor((t.getMonth() + 3) / 3),    //季度
            "S": t.getMilliseconds()                     //毫秒
        };
        var fmt = format || "yyyy-MM-dd hh:mm:ss";
        if (/(y+)/.test(fmt))
        {
            fmt = fmt.replace(RegExp.$1, (t.getFullYear() + "").substr(4 - RegExp.$1.length));
        }
        for (var k in o)
        {
            if (new RegExp("(" + k + ")").test(fmt))
            {
                fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
            }
        }
        return fmt;
    },
    ByteFormat : function(size)
    {
        size = parseInt(size)||0;
        if(size < 1024)
        {
            return size + "B";
        }
        else if(size < 1048576) // == 1024*1024
        {
            return (size / 1024).toFixed(2) + "K"; // 保留两位小数点
        }
        else if(size < 1073741824) // == 1024*1024*1024
        {
            return (size / 1048576).toFixed(2) + "M"; // 保留两位小数点
        }
        else
        {
            return (size / 1073741824).toFixed(2) + "G"; // 保留两位小数点
        }
    },
    Float : function(v, point)
    {
        v = v || 0;
        point = point||2;
        return parseFloat(parseFloat(v).toFixed(point));
    },
    // 分转为元
    FenToYuan: function(fen){
        return parseFloat((parseInt(fen) / 100).toFixed(2));
    },
    // html编码
    HtmlEncode : function(str)
    {
        str = str||"";
        return $('<div/>').text(str).html();
    },
    // html解码
    HtmlDecode : function(str)
    {
        str = str||"";
        return $('<div/>').html(str).text();
    },
    // 删除元素（禁用input及加中划线等）
    DeleteTableLineStyle : function($elem_ary)
    {
        $elem_ary.each(function(i,elem){
            $(elem).addClass("table_tr_del");
            $(elem).find("input,textarea").attr("disabled", true);
        });
    },
    /*
     * 生成菜单 [rockyshi 2014-08-30]
     *   使用:
     *       1. menu = new Menu($("#menu_id"), list, ...)
     *       2. 菜单获取当前选中的菜单值:  value = menu.GetValue()
     *       3. 设置当前选中的值: menu.SetValue(value)
     *
     *   $owner: 菜单所属的对象（标题）
     *   var list = [
     *       {value: 11, title:"-------1--------"},
     *       {value: 12, title:"-------2--------", ishtml: true},   // 菜单项是html格式代码（默认为text），用来支持菜单中设置按钮等;
     *       {value: 13, title:"--------3-------", selected:true}   // 菜单选中
     *       {value: 13, title:"--------3-------", data:xxx}        // 其它数据
     *   ];
     *   property = {
     *       ishtml         : true,     // 菜单显示为html格式
     *       align_l        : true,     // 菜单左对齐(默认)（注：不是菜单内容）
     *       align_r        : true,     // 菜单右对齐
     *       item_align     : xxx,      // 菜单项(文本)左对齐[left|right|center]
     *       max_height     : xx,       // 菜单最大高度(px)
     *       to_owner_w     : true,     // 宽调整为owner的同宽
     *       opacity        : xx,       // 透明度(0.0~1.0, 值越小越秀明)
     }
     *   }
     */
    Menu : function($owner, list, menu_click_func, property)
    {
        // if(list.length === 0)
        // {
        //     Util.Alert("warn: ment list empty");
        //     // return;
        // }
        list = list||[];
        property = property||{};
        var THIS = (this != window) ? this : (window.Menu={});
        var $menu = $('<div class="menu"/>');
        var m_bOpen= false;
        var $prev_sel_menu = null; // 上次选中的菜单项
        THIS.value_to_li = [];

        function Create()
        {
            var $ul = $('<ul/>');
            for(var i=0; i<list.length; i++)
            {
                AddItem($ul, list[i]);
            }
            $menu.append($ul).hide();
            $("body").append( $menu );

            // console.log(property, property.item_align);
            if(property.item_align == "left")
            {
                $ul.css("text-align", "left");
            }
            else if(property.item_align == "right")
            {
                $ul.css("text-align", "right");
            }
            else if(property.item_align == "center")
            {
                $ul.css("text-align", "center");
            }

            if(property.max_height)
            {
                $ul.css({
                    "max-height" : property.max_height,
                    "overflow-x" : "hidden",
                    "overflow-y" : "auto"
                });
            }

            if(Util.IsDefine(property.opacity))
            {
                $menu.css("opacity", property.opacity);
            }

            if(property.to_owner_w)
            {
                $ul.css({
                    width : $owner.GetPos().width - 1 // 小调整
                });
            }

            $owner.click(function(){
                m_bOpen ?  Close() : Open();
                return false;   // 不传递
            });

            $menu.mouseleave(function(){
                Close();
            });

            $("body").click(function(event){
                Close();
            });
        }

        // 调整位置
        function AdjuxPos()
        {
            var owner_pos = $owner.GetPos();
            var menu_pos = $menu.GetPos();
            // pos.width = $owner.outerWidth();    // innerWidth
            // pos.height = $owner.outerHeight();  // innerHeight
            var top = 0;
            var left = 0;
            if(property.align_r)  // 菜单右对齐（注：不是菜单内容）
            {
                top = owner_pos.top + owner_pos.height;
                left = owner_pos.left + owner_pos.width - menu_pos.width;
            }
            else
            {
                top = owner_pos.top + owner_pos.height;
                left = owner_pos.left;
            }
            $menu.css({
                position: "absolute",
                left: left + "px",
                top: top + "px",
                "z-index": $.MaxZindex()
            });
        }

        function Open()
        {
            AdjuxPos();
            $menu.show();
            // $owner.data("li").GetFocus();
            m_bOpen = true;
        }
        THIS.Open = Open;

        function Close()
        {
            $menu.hide();
            m_bOpen = false;
        }

        // 新加菜单项
        //  data -- {value:xx, title:xx, ...}
        //  pos -- 插入到的位置（从0开始计）
        function AddItem($ul, data, pos)
        {
            var $li = $("<li>").data("value", data.value)
                               .data("title", data.title)
                               .data("data",  data.data)
                               .click(function(){   // 点击菜单项时执行的动作
                                    var value = $(this).data("value");
                                    var title = $(this).data("title");
                                    var data = $(this).data("data") || "";
                                    var bRet = true;
                                    if($.isFunction(menu_click_func))
                                    {
                                        // 当动作返回false时，不设置值；
                                        bRet = menu_click_func.call(THIS, { 'i': $(this).index(),
                                                          'title': title,
                                                          'value': value,
                                                          'data' : data
                                        });
                                    }
                                    if(bRet !== false)
                                    {
                                        THIS.SetValue(value);
                                    }
                                    Close();
                                });
            if(data.ishtml)
            {
                $li.html(data.title);
            }
            else
            {
                $li.text(data.title);
            }
            THIS.value_to_li[ data.value ] = $li;
            if(data.selected)
            {
                THIS.SetValue(data.value);
            }
            $ul.append($li);    // 默认追加(最后)
        }

        // 删除值为value的菜单项
        THIS.DelItem = function(value)
        {
            var $li = THIS.value_to_li[value] || $("");

            // 删除下拉菜单中的项
            $li.remove();

            delete THIS.value_to_li[value];

            // 再清相关值
            if(value == $owner.data("value"))
            {
                THIS.SetValue(value);
            }
        }

        // 删除所有菜单项
        THIS.DelAllItem = function()
        {
            for(var i in THIS.value_to_li)
            {
                THIS.DelItem(i);
            }
        }

        // 设置菜单项
        THIS.SetItem = function(value, title)
        {
            var $ul = $menu.find("ul");
            var $li = THIS.value_to_li[value];
            if(!$li)  // insert
            {
                var data = {
                    value : value,
                    title : title
                };
                AddItem($ul, data, "top");
                return;
            }

            $li.data('value', value)
               .data('title', title)
               .text(title);

            if(value == $owner.data("value"))
            {
                $owner.data("title", title);
                THIS.SetValue(value);
            }
        }

        // 菜单获取当前选中的菜单值
        THIS.GetValue = function()
        {
            return $owner.data("value");
        }

        // 菜单获取当前选中的标题（显示值）
        THIS.GetTitle = function()
        {
            return $owner.data("title");
        }

        // 菜单获取当前选中的附带数据
        THIS.GetData = function()
        {
            return $owner.data("data");
        }

        // 设置当前选择的值，及更新界面显示
        THIS.SetValue = function(value)
        {
            var $li = THIS.value_to_li[value] || $("");
            var title = $li.data("title") || "";
            if($owner.is("input"))
            {
                $owner.val(Util.CutHtmlSymbol(title));
            }
            else
            {
                $owner.text(title);
            }
            if($prev_sel_menu)
            {
                $prev_sel_menu.removeClass('selected');
            }
            $li.addClass('selected');
            $owner.data("value", value)
                  .data("title", title)
                  .data("data", $li.data("data"));
            $prev_sel_menu = $li;
        }

        // 因多处共享一个菜单需求设置
        THIS.ChangeOwner = function(owner)
        {
            $owner = owner;
            return THIS;
        }


        Create();
    },// end of function Menu(...
    // /*
    //  * Rocky 2015-12-30 17:40:21
    //  * 多个控件共享一个菜单
    //  */
    // ShareMenu : function(list, menu_click_func, property)
    // {
    //     var $owner = $("<div/>").appendTo("body").hide();
    //     var $menu = new Util.Menu($owner, list, menu_click_func, property);
    //     this.Open = function(event){
    //         $menu.ChangeOwner($(event.target)).Open();
    //         return false;
    //     }
    // },
    /*
     * 简单菜单 [Rocky 2015-12-31 13:10:43]
     *   使用:
     *       1. menu = new SimpleMenu($owner, list, menu_click_func, property)
     *          其中$owner可为多个控件
     *
     *   $owner: 菜单所属的对象（标题）
     *   var list = ["中国", "美国"];
     *   property = {
     *       ishtml         : true,     // 菜单显示为html格式
     *       align_l        : true,     // 菜单左对齐(默认)（注：不是菜单内容）
     *       align_r        : true,     // 菜单右对齐
     *       item_align     : xxx,      // 菜单项(文本)左对齐[left|right|center]
     *       item_height    : xxx,      // 菜单项高度
     *       max_height     : xx,       // 菜单最大高度(px)
     *       to_owner_w     : true,     // 宽调整为owner的同宽
     *       opacity        : xx,       // 透明度(0.0~1.0, 值越小越秀明)
     *       pop_up         : xxx,      // 往上弹出(对齐方式[left|right|center])
     }
     *   }
     */
    SimpleMenu : function($owner, list, menu_click_func, property)
    {
        list = list||[];
        var THIS = (this != window) ? this : (window.SimpleMenu={});
        var m_property = property||{};
        var $menu = $('<div class="menu" id="id_' + Util.GetRandString(8) + '"/>');
        var $ul = $('<ul/>');
        var m_bOpen= false;
        var m_curOwner = null; // 当前点击的属主

        function Create()
        {
            for(var i=0; i<list.length; i++)
            {
                AddItem(list[i]);
            }
            $menu.append($ul).hide();
            $("body").append( $menu );

            if(m_property.item_align == "left")
            {
                $ul.css("text-align", "left");
            }
            else if(m_property.item_align == "right")
            {
                $ul.css("text-align", "right");
            }
            else if(m_property.item_align == "center")
            {
                $ul.css("text-align", "center");
            }

            if(m_property.item_height)
            {
                $ul.find(">li").css({
                    "height"      : m_property.item_height,
                    "line-height" : m_property.item_height
                });
            }

            if(m_property.max_height)
            {
                $ul.css({
                    "max-height" : m_property.max_height,
                    "overflow-x" : "hidden",
                    "overflow-y" : "auto"
                });
            }

            if(Util.IsDefine(m_property.opacity))
            {
                $menu.css("opacity", m_property.opacity);
            }

            THIS.Attach($owner);

            $menu.mouseleave(function(){
                Close();
            });

            $("body").click(function(event){
                Close();
            });
        }

        // 调整位置
        function AdjuxPos()
        {
            var owner_pos = $m_curOwner.GetPos();
            var menu_pos = $menu.GetPos();
            var top = 0;
            var left = 0;

            if(m_property.to_owner_w)
            {
                var w = $m_curOwner.GetPos().width - 1; // 小调整
                $menu.width(w);
                $ul.width(w);
            }
            if(m_property.pop_up)
            {
                top = owner_pos.top - (menu_pos.height);
                left = owner_pos.left; // 默认"left"
                if("right" == m_property.pop_up)
                {
                    left = owner_pos.left - (menu_pos.width - owner_pos.width);
                }
                else if("center" == m_property.pop_up)
                {
                    left = owner_pos.left - (menu_pos.width - owner_pos.width) / 2;
                }
            }
            else if(m_property.align_r)  // 菜单右对齐（注：不是菜单内容）
            {
                top = owner_pos.top + owner_pos.height;
                left = owner_pos.left + owner_pos.width - menu_pos.width;
            }
            else
            {
                top = owner_pos.top + owner_pos.height;
                left = owner_pos.left;
            }
            $menu.css({
                position: "absolute",
                left: left + "px",
                top: top + "px",
                "z-index": $.MaxZindex()
            });
        }

        // 调整状态
        function AdjuxStatus()
        {
            var owner_title = $m_curOwner.text();
            var $li = FindItem(owner_title);
            $menu.find("li").removeClass('selected');
            if($li)
            {
                $li.addClass('selected');
            }
        }

        function Open()
        {
            AdjuxPos();
            AdjuxStatus();
            $menu.show().SetZindexTop();
            m_bOpen = true;
        }

        function Close()
        {
            $menu.hide();
            m_bOpen = false;
        }

        // 新加菜单项
        function AddItem(title)
        {
            if(null != FindItem(title))
            {
                return;
            }
            var $li = $("<li>").click(function(){   // 点击菜单项时执行的动作
                var bRet = true;
                if($.isFunction(menu_click_func))
                {
                    // 当动作返回false时，不设置值；
                    bRet = menu_click_func({'i': $(this).index(),
                                            'title': title
                    });
                }
                if(bRet !== false)
                {
                    SetValue(title);
                }
                Close();
            });
            if(m_property.ishtml)
            {
                $li.html(title);
            }
            else
            {
                $li.text(title);
            }
            $ul.append($li); // 默认追加(最后)
        }
        THIS.AddItem = AddItem;

        // 查找菜单项
        function FindItem(title)
        {
            var $li = null;
            $menu.find("li").each(function(i, e){
                if($(e).text() == title)
                {
                    $li = $(e);
                    return;
                }
            })
            return $li;
        }
        THIS.FindItem = FindItem;

        // 设置当前选择的值，及更新界面显示
        function SetValue(title)
        {
            if($m_curOwner.is("input"))
            {
                $m_curOwner.val(title);
            }
            else
            {
                $m_curOwner.text(title);
            }
        }

        // 设置可触发菜单的控件
        THIS.Attach = function($owner_ary)
        {
            if($owner_ary)
            {
                $owner_ary.each(function(){
                    var $owner = $(this);
                    if($owner.data("has_attach")) // 避免重复操作
                    {
                        return;
                    }
                    $owner.click(function(event){
                        $m_curOwner = $(event.target);
                        m_bOpen ?  Close() : Open();
                        return false;   // 不传递
                    }).data("has_attach", true);
                });
            }
        }

        Create();
    },// end of function SimpleMenu(...
    /*
     * 生成备选菜单 [rockyshi 2014-08-30]
     *   使用:
     *   $owner: 菜单所属的对象（标题）
     *   property = {
     *       item_align     : xxx,      // 菜单项(文本)左对齐[left|right|center]
     *       max_height     : xx,       // 菜单最大高度(px)
     *       with           : xx,       // 宽度(owner|xxpx)
     *       opacity        : xx,       // 透明度(0.0~1.0, 值越小越秀明)
     *       max_item_num   : xx,       // 最大记下的菜单项数（默认不限制）
     *       when_remember  : blur,     // 什么时候记住输入项([blur|other])
     *   }
     */
    RememberMenu : function(owner_id, property)
    {
        property = property || {};
        property.max_height    = property.max_height || 300;
        property.max_item_num  = property.max_item_num || 0;
        property.when_remember = property.when_remember || "blur";
        var THIS = (this != window) ? this : (window.RememberMenu={});
        var $owner = $("#" + owner_id);
        var $menu = $('<div class="menu"/>').attr("id", "id_" + Util.GetRandString(10));
        var m_bOpen= false;


        function Create()
        {
            var list = GetData();
            var $ul = $('<ul/>');
            for(var i=0; i<list.length; i++)
            {
                var title = list[i];
                AddItem($ul, title);
            }
            $menu.append($ul).hide();
            $menu.data("ul", $ul);
            $("body").append( $menu );

            SetProperty(property);

            // 标题
            $owner.click(function(){
                if($menu.data("ul").children("li").size() == 0)
                {
                    return;
                }
                m_bOpen ?  Close() : Open();
                return false;   // 不传递
            })
            .keyup(function(event) {
                Filter($(this).val());
            });

            $menu.mouseleave(function(){
                Close();
            });

            $("body").click(function(event){
                Close();
            });
        }

        // pos = [top|bottom]
        function AddItem($ul, title, pos)
        {
            var $li = $("<li>").text(title)
                     .click(function(){   // 点击菜单项时执行的动作
                            $owner.val($(this).text());
                            Close();
                            Top($(this).index());
                    });
            if("top" == pos)
            {
                $li.prependTo($ul);
            }
            else
            {
                $li.appendTo($ul);
            }
            return $li;
        }

        // 设置属性
        function SetProperty(property)
        {
            var $ul = $menu.find("ul");
            property = property||{};

            // console.log(property, property.item_align);
            if(property.item_align == "left")
            {
                $ul.css("text-align", "left");
            }
            else if(property.item_align == "right")
            {
                $ul.css("text-align", "right");
            }
            else if(property.item_align == "center")
            {
                $ul.css("text-align", "center");
            }

            if(property.max_height)
            {
                $ul.css({
                    "max-height" : property.max_height,
                    "overflow-x" : "hidden",
                    "overflow-y" : "auto"
                });
            }

            if(Util.IsDefine(property.opacity))
            {
                $menu.css("opacity", property.opacity);
            }

            var width = parseInt(property.width||"0");
            if(!Util.IsDefine(property.width) || "owner" == property.width)
            {
                width = $owner.GetPos().width;
            }
            if(width > 0)
            {
                $ul.css({
                    width : width - 1 // 小调整
                });
            }

            if(property.when_remember == "blur")
            {
                $owner.blur(function(event){
                    Remember($(this).val());
                })
            }
        }

        function GetData()
        {
            // var page_data = window.PageStore.GetCurPageData();
            // if(!page_data[owner_id])
            // {
            //     page_data[owner_id] = {};
            // }
            // var list = page_data[owner_id].list || (page_data[owner_id].list=[]);
            // return list;
            var key = "remember&"+owner_id;
            var list = $.Json.toObj(window.Store.GetPageData(key, '[]'));
            return list || [];
        }

        function Save(list)
        {
            if(!list)
            {
                return;
            }
            var key = "remember&"+owner_id;
            window.Store.SetPageData(key, $.Json.toStr(list));
        }

        // 找到，返回下标，否则返回-1
        function Index(title)
        {
            var list = GetData();
            for(var i=0; i<list.length; i++)
            {
                if(list[i] == title)
                {
                    return i;
                }
            }
            return -1;
        }

        //
        function Filter(key)
        {
            var list = $menu.data("ul").children("li");
            if(!list || 0 == list.length)
            {
                return;
            }
            var count = 0; // 显示的菜单项数
            for(var i=0; i<list.length; i++)
            {
                var $li = $(list[i]) || $("");
                if($li.text().match(key))
                {
                    // console.log("show: ", $li.data("title"));
                    $li.show();
                    count++
                }
                else
                {
                    // console.log("hide: ", $li.data("title"));
                    $li.hide();
                }
            }
            if(0 == count)
            {
                Close(); // 一个菜单项都没有时，关闭菜单
            }
            else
            {
                Open();
            }
        }

        function Remember(title)
        {
            if(title === "")
            {
                return
            }
            var i = Index(title);
            if(i >= 0)
            {
                return;
            }

            var list = GetData();
            list.reverse();     // 在数组开头插入元素
            list.push(title);   //
            list.reverse();     //
            var $ul = $menu.find("ul");
            AddItem($ul, title, "top");

            if(property.max_item_num > 1 && list.length > property.max_item_num)
            {
                list.splice( -(list.length - property.max_item_num) );  // 如: list.splice( -3 ) --> 删除后面多出的３个元素
                $ul.children("li:gt(" + (property.max_item_num - 1) + ")").remove();
            }
            Save(list);
        }

        // 移到顶端
        function Top(index)
        {
            var $ul = $menu.find("ul");
            var $li = $ul.children("li");
            if(index <= 0 || index >= $li.length)
            {
                return;
            }
            $ul.prepend($li.eq(index));
            var list = GetData();
            Util.AryTop(list, index);
            Save(list);
        }

        // 调整位置
        function AdjuxPos()
        {
            var owner_pos = $owner.GetPos();
            var menu_pos = $menu.GetPos();
            // pos.width = $owner.outerWidth();    // innerWidth
            // pos.height = $owner.outerHeight();  // innerHeight
            var top = 0;
            var left = 0;
            if(property.align_r)  // 菜单右对齐（注：不是菜单内容）
            {
                top = owner_pos.top + owner_pos.height;
                left = owner_pos.left + owner_pos.width - menu_pos.width;
            }
            else
            {
                top = owner_pos.top + owner_pos.height;
                left = owner_pos.left;
            }
            $menu.css({
                position: "absolute",
                left: left + "px",
                top: top + "px",
                "z-index": $.MaxZindex()
            });
        }

        function Open()
        {
            AdjuxPos();
            $menu.show();
            // $owner.data("li").GetFocus();
            m_bOpen = true;
        }

        function Close()
        {
            $menu.hide();
            m_bOpen = false;
        }

        THIS.Remember = Remember;
        THIS.SetProperty = SetProperty;

        Create();
    },// end of function RememberMenu(...
    // 去掉前后空格
    TrimSpace:function(str)
    {
        return str.replace(/^([\s]*)|([\s]*)$/g, "");
    },
    // 变量已定义，返回true
    IsDefine : function(a)
    {
        return typeof(a) !== 'undefined';
    },
    // 只包含数和点(实数)
    IsFloat : function(str)
    {
        return str.match(/[^0-9.]/) == null;
    },
    // 只包含数
    IsDigit : function(str)
    {
        return str.match(/[^0-9]/) == null;
    },
    // 登录检测（已登录返回true）
    LoginCheck : function()
    {
        var info = window.PageStore.GetLoginInfo();
        if(!info.key
            || !info.userid
            || Util.GetTimestamp() - (info.login_time||0) > 3600*24*10   // 10天
          )
        {
            return false;
        }
        return true;
    },
    // 创建认证信息
    GenVerifInfo : function(field)
    {
        field = field||"";
        var s = Util.GetRandString(16);
        return {
            Magin : s,
            Key   : $.md5(field + "" + s + window.PageStore.GetLoginRandPasswd())
        }
    },
    // tab类设置
    TabBox : function($owner)
    {
        if(!$owner)
        {
            Util.Alert("$owner is null");
            return;
        }
        var THIS = (this != window) ? this : (window.TabBox={});
        var $prev = null;

        function Show($tabbox_head_bt)
        {
            var $tabbox_body = $tabbox_head_bt.data("tabbox_body");
            if(!$tabbox_body)
            {
                return;
            }
            if($prev)
            {
                $prev.hide();
            }
            $tabbox_head_bt.SetChecked();
            $tabbox_body.show();
            $prev = $tabbox_body;
        }

        // tabbox_head_bt1 对应于 tabbox_body1
        //$owner.find("[class^=tabbox_head_bt]").each(function(i, e)
        //{
        //    var $tabbox_head_bt = $("");
        //    Util.Alert(e);
        //});
        for(var i=0; i<20; i++)
        {
            var $tabbox_head_bt = $owner.find(".tabbox_head_bt"+ i);
            var $tabbox_body    = $owner.find(".tabbox_body"+ i);
            if($tabbox_head_bt.length == 0 || $tabbox_body.length == 0)
            {
                continue;
            }
            //console.log($tabbox_head_bt, $tabbox_body);

            $tabbox_head_bt.click(function(){
                Show($(this));
            }).data("tabbox_body", $tabbox_body);

            if($tabbox_head_bt.hasClass("selected"))
            {
                Show($tabbox_head_bt);
            }
        }

        // 选中tab
        THIS.Select = function($tabbox_head_bt)
        {
            Show($tabbox_head_bt);
            return THIS;
        }

        // THIS.Click = function()
        // {
        //     $owner.trigger('click');
        //     return THIS;
        // }

        return THIS;
    },//end of TabBox : function(...
    // 简单换页切换操作
    // Rocky 2016-01-02 18:26:26
    // <div id="xxx">
    //     <input type="button" class="prev" value="上一页" />
    //     <input type="button" class="next" value="下一页" />
    //     <span class="pageno">0</span>/<span class="total">0</span>
    // </div>
    SimplePageSwitch : function(id, click_callback, opt)
    {
        click_callback = click_callback || function(){}
        var THIS = this;
        var m_id = id;
        var m_opt = opt || {};
        var m_data = {
            pageno   : 1,   // 页码
            pagesize : 50,  // 页大小
            total    : 0    // 总页数
        }

        var $m_prev     = $("#" + m_id + " .prev");
        var $m_next     = $("#" + m_id + " .next");
        var $m_pageno   = $("#" + m_id + " .pageno");
        var $m_pagesize = $("#" + m_id + " .pagesize");
        var $m_total    = $("#" + m_id + " .total");

        THIS.SetTotal = function(total)
        {
            m_data.total = total;
            $m_total.text(total);
            return THIS;
        }

        THIS.SetPageNo = function(pageno)
        {
            m_data.pageno = pageno;
            $m_pageno.text(pageno);
            return THIS;
        }

        THIS.SetPageSize = function(pagesize)
        {
            m_data.pagesize = pagesize;
            $m_pagesize.text(pagesize);
            return THIS;
        }


        // 点击上一页
        $m_prev.click(function(event) {
            var pageno = parseInt($m_pageno.text());
            pageno--;
            if(pageno < 1 )
            {
                return;
            }
            m_data.pageno = pageno;
            $m_pageno.text(pageno)
            click_callback.call(this, m_data);
        });

        // 点击下一页
        $m_next.click(function(event) {
            var pageno = parseInt($m_pageno.text());
            var total = parseInt($m_total.text());
            if(pageno >= total)
            {
                return;
            }
            pageno =　pageno + 1;
            m_data.pageno = pageno;
            $m_pageno.text(pageno);
            click_callback.call(this, m_data);
        });
    },//end of SimplePageSwitch : function(...
    // Object转为Array
    o2a : function(obj)
    {
        var ary;
        for(var i in obj)
        {
            ary.push(obj[i]);
        }
        return ary;
    },
    // 数组完素对调(对换i、j位置元素)
    ArySwap : function(ary, i, j)
    {
        var tmp = ary[i];
        ary[i] = ary[j];
        ary[j] = tmp;
        return ary;
    },
    // 数组当前元素置顶（其它元素后移）
    AryTop : function(ary, index)
    {
        if(index >= ary.length)
        {
            index = ary.length - 1;
        }
        var sel = ary[index];
        for(var i=index; i>0; i--)
        {
            ary[i] = ary[i-1];
        }
        ary[0] = sel;
        return ary;
    },
    Uniq : function(ary)
    {
        var uniq = [];
        var exist = {}
        for(var i in ary)
        {
            var elem = ary[i];
            if(exist[elem])
            {
                continue;
            }
            exist[elem] = true;
            uniq.push(elem);
        }
        return uniq;
    },
    // 获取页面的高度、宽度
    // http://www.cnblogs.com/hoojo/archive/2012/02/16/2354663.html
    GetPageSize : function()
    {
        var xScroll, yScroll;
        if (window.innerHeight && window.scrollMaxY) {
            xScroll = window.innerWidth + window.scrollMaxX;
            yScroll = window.innerHeight + window.scrollMaxY;
        } else {
            if (document.body.scrollHeight > document.body.offsetHeight) { // all but Explorer Mac
                xScroll = document.body.scrollWidth;
                yScroll = document.body.scrollHeight;
            } else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
                xScroll = document.body.offsetWidth;
                yScroll = document.body.offsetHeight;
            }
        }
        var windowWidth, windowHeight;
        if (self.innerHeight) { // all except Explorer
            if (document.documentElement.clientWidth) {
                windowWidth = document.documentElement.clientWidth;
            } else {
                windowWidth = self.innerWidth;
            }
            windowHeight = self.innerHeight;
        } else {
            if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
                windowWidth = document.documentElement.clientWidth;
                windowHeight = document.documentElement.clientHeight;
            } else {
                if (document.body) { // other Explorers
                    windowWidth = document.body.clientWidth;
                    windowHeight = document.body.clientHeight;
                }
            }
        }
        // for small pages with total height less then height of the viewport
        if (yScroll < windowHeight) {
            pageHeight = windowHeight;
        } else {
            pageHeight = yScroll;
        }
        // for small pages with total width less then width of the viewport
        if (xScroll < windowWidth) {
            pageWidth = xScroll;
        } else {
            pageWidth = windowWidth;
        }
        return {
            page : {
                w : pageWidth,
                h : pageHeight
            },
            win : { // 这里会包含滚动条等
                w : windowWidth,
                h : windowHeight
            }
        };
    },//end of GetPageSize : function(...
    // 去除html标记
    CutHtmlSymbol : function(html)
    {
        return $('<span>' + html + '</span>').text();
    },
    /*
     * http://www.tuicool.com/articles/uaIr2mj
     * param 将要转为URL参数字符串的对象
     * key URL参数字符串的前缀
     * encode true/false 是否进行URL编码,默认为true
     *
     * return URL参数字符串
     *
     × 如：
     *    var obj={name:'tom','class':{className:'class1'},classMates:[{name:'lily'}]};
     *    console.log(urlEncode(obj));
     *    //output: &name=tom&class.className=class1&classMates[0].name=lily
     *    console.log(urlEncode(obj,'stu'));
     *    //output: &stu.name=tom&stu.class.className=class1&stu.classMates[0].name=lily
     */
    UrlEncode : function(param, key, encode)
    {
      if(param==null) return '';
      var paramStr = '';
      var t = typeof (param);
      if (t == 'string' || t == 'number' || t == 'boolean') {
        paramStr += '&' + key + '=' + ((encode==null||encode) ? encodeURIComponent(param) : param);
      } else {
        for (var i in param) {
          var k = key == null ? i : key + (param instanceof Array ? '[' + i + ']' : '.' + i);
          paramStr += Util.UrlEncode(param[i], k, encode);
        }
      }
      return paramStr;
    },
    GetQueryString : function(name)
    {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r!=null)
        {
            return  decodeURIComponent(r[2]);
        }
        return null;
    },
    GetPagename : function()
    {
        var n = window.location.pathname.lastIndexOf('/') + 1
        return window.location.pathname.substr(n);
    },
    // 设置多个checkbox中，只能选中一个。
    SetSelectOneByName:function(name)
    {
        return $("input[name=" + name + "]").click(function(event){
            var $target = $(event.target);
            $("input[name=" + name + "]").each(function(){
                var $this = $(this);
                if($this.is($target))
                {
                    return;
                }
                $this.SetChecked(false);
            })
            return;
        });
    },
    // 设置checkbox值(选中)
    SetCheckboxValue:function(name, vallist)
    {
        if(!$.isArray(vallist))
        {
            vallist = [vallist];
        }
        var p = {};
        for(var i in vallist)
        {
            p[ vallist[i] ] = true;
        }
        return $("input[name=" + name + "]").each(function(){
            var $this = $(this);
            // $this.attr("checked", p[$this.val()]?true:false);
            $this.SetChecked(p[$this.val()]?true:false);
        })
    },
    // 往上一层找指定的data数据
    FindDataElemValue:function($cur_elem, data_key)
    {
        var data_value = null;
        for(var i=0; i<100&&!$cur_elem.is(document); i++)
        {
            data_value=$cur_elem.data(data_key)
            if(null != data_value)
            {
                break;
            }
            $cur_elem = $cur_elem.parent();
        }
        return data_value;
    },
    // 提示设置
    TipsSetting:function()
    {
        return $(".tips").TitleToTips();
    }
};// end of Util...

(function($) {

if(!$)
{
    Util.Alert("$() init err");
    return false;
}

$.fn.extend({
    center:function()
    {
        return this.each(function()
        {
            var $this = $(this);
            var $win = $(window);
            $this.css({
                position:'absolute',
                top     : ($win.height() - $this.height()) / 2 + $win.scrollTop() + 'px',
                left    : ($win.width() - $this.width()) / 2 + $win.scrollLeft() + 'px'
                //border  : "1px dashed #000",
                //padding : "5px"
            })
        });
    },
    // $("input[name=datachk]:checked").val()
    GetCheckBoxVal:function()
    {
        var list = [];
        this.each(function()
        {
            list.push(this.value);
        });
        return list;
    },
    // 取选中的第一个checkbox的值
    GetCheckedVal:function()
    {
        var val = null;
        this.each(function()
        {
            var $this = $(this);
            if($this.IsChecked())
            {
                val = $this.val();
                return false;
            }
        });
        return val;
    },
    // checkbox、radio选中时，返回true
    IsChecked:function()
    {
        return $(this).is(":checked");
    },
    IsCheckbox:function()
    {
        return $(this).attr("type") == "checkbox";
    },
    IsRadio:function()
    {
        return $(this).attr("type") == "radio";
    },
    //
    SetChecked:function(sel)
    {
        sel = Util.IsDefine(sel) ? sel : true;
        return this.each(function(){
            $(this).get(0).checked = sel;
        })
    },
    // 设置提示信息为处理一般状态
    SetPromptStyle:function()
    {
        return $(this).css({"color":"gray","font-style":"italic","font-size":"1.4rem"});
    },
    // 设置提示信息为处理正确状态
    SetOkStyle:function()
    {
        return $(this).css({"color":"green","font-style":"italic","font-size":"1.4rem"});
    },
    // 设置提示信息为处理出错状态
    SetErrStyle:function()
    {
        return $(this).css({"color":"red","font-style":"italic","font-size":"1.4rem"});
    },
    // 设置透明度（opacity 属性能够设置的值从 0.0 到 1.0。值越小，越透明。）
    SetOpacity:function(opacity)
    {
        var ie = "alpha(opacity=" + (parseFloat(opacity)*100) + ");" // IE8以及更早的版本
        return $(this).css({"opacity":opacity, "filter":ie});
    },
    SetId:function(id)
    {
        return $(this).attr("id", id);
    },
    // 设置颜色
    SetColor:function(color)
    {
        return $(this).css({"color": color});
    },
    // 设置背景色
    SetBgColor:function(color)
    {
        return this.each(function(){
            $(this).css({"background-color": color});
        })
    },
    // 设置为粗体
    SetBold:function(yes)
    {
        return this.each(function(){
            $(this).css({"font-weight": yes?"bold":""});
        })
    },
    // 得到焦点（用下面的Focus()）
    GetFocus:function()
    {
        return this.trigger("focus");
    },
    // 得到焦点
    Focus:function()
    {
        return this.trigger("focus");
    },
    // 失去焦点
    Blur:function()
    {
        return this.trigger("blur");
    },
    // 按回车键的动作
    Enter:function(func)
    {
        // return this.each(function()
        // {
            this.keydown(function(event){
                var keycode = event.which;
                if(keycode == 13)
                {
                    if(func != null && $.isFunction(func))
                    {
                        return func.call(this, event);  // Rocky 2015-12-28 17:23:32
                    }
                }
            });
        // });
        return this;
    },
    // 按ESC建的动作
    // Rocky 2015-12-28 17:49:43
    Esc:function(func)
    {
        return this.KeyPress(27, func);
    },
    // 按上箭头键的动作
    // Rocky 2016-07-01 11:37:58
    ArrowUp:function(func)
    {
        return this.KeyPress(38, func);
    },
    // 按下箭头键的动作
    // Rocky 2016-07-01 11:37:58
    ArrowDown:function(func)
    {
        return this.KeyPress(40, func);
    },
    // 按下箭头键的动作
    // Rocky 2017-01-02 22:42:07
    ArrowLeft:function(func)
    {
        return this.KeyPress(37, func);
    },
    // 按下箭头键的动作
    // Rocky 2017-01-02 22:42:08
    ArrowRight:function(func)
    {
        return this.KeyPress(39, func);
    },
    // 按下箭头键的动作
    // Rocky 2016-07-01 11:37:58
    KeyPress:function(keycode, func)
    {
        this.keydown(function(event){
            if(event.which == keycode)
            {
                if(func != null && $.isFunction(func))
                {
                    return func.call(this, event);
                }
            }
        });
        return this;
    },
    // option={exclude:[$obj1, $obj2, ...]}
    // disabled:[true|false]
    Disabled:function(disabled, option)
    {
        option = option||{};
        option.exclude = option.exclude||{};
        this.each(function()
        {
            var $this = $(this);
            for(var i in option.exclude)
            {
                var item = option.exclude[i];
                if($this.is(item))
                {
                    return;
                }
            }
            this.disabled = disabled;
        });
        return this;
    },
    Readonly:function(readonly)
    {
        readonly = Util.IsDefine(readonly) ? readonly : true;
        this.each(function()
        {
            $(this).attr("readonly", readonly);
        });
        return this;
    },
    // 是否可编辑（enable：true-可编辑）
    Edit:function(enable)
    {
        enable = Util.IsDefine(enable) ? enable : true;
        this.each(function()
        {
            if((" " + this.className + " ").indexOf(" edit ") == -1)
            {
                return;
            }
            if(enable)
            {
                $(this).attr("contenteditable", true).addClass("edit_on");
            }
            else
            {
                $(this).attr("contenteditable", false).removeClass("edit_on");
            }
        });
        return this;
    },
    // 控件的title属性
    Title:function(title)
    {
        if(Util.IsDefine(title))
        {
            title = title||"";
            this.each(function()
            {
                $(this).attr("title", title);
            });
            return this;
        }
        else
        {
            return this.get(0).title;
        }
    },
    // 取位置（及长宽）
    GetPos:function()
    {
        var pos = this.offset();
        pos.top    = parseInt(pos.top);
        pos.left   = parseInt(pos.left);
        pos.width  = this.outerWidth();    // innerWidth
        pos.height = this.outerHeight();   // innerHeight
        // pos.zindex =
        return pos;
    },
    // 设置位置
    SetPos:function(pos)
    {
        if(!pos)
        {
            return;
        }

        var css = {
            'position'  : "absolute"
        };
        if(pos.left)
        {
            css['left']  = pos.left + "px";
        }
        if(pos.top)
        {
            css['top']  = pos.top + "px";
        }
        if(pos.width)
        {
            css['width']  = pos.width + "px";
        }
        if(pos.height)
        {
            css['height']  = pos.height + "px";
        }
        if(pos.zindex)
        {
            css['z-index']  = pos.zindex ;
        }

        return $(this).css(css);
    },
    // 当前层移到最上
    SetZindexTop:function()
    {
        return $(this).css({
            "z-index" : $.MaxZindex()
        });
    },
    // 是否包含classname
    HasClassname:function(classname)
    {
        if( (" " + this.attr("class") + " ").indexOf(" " + classname + " ") == -1)
        {
            return false;
        }
        return true;
    },
    // 检查合法值（不合法返回false）
    // 支持项：noempty-不能为空
    //         digit-只能是数字
    CheckValue:function()
    {
        var val = this.val();
        if('' === val)
        {
            val = this.text();
        }
        if(this.HasClassname("digit"))
        {
            return Util.IsDigit(val)
        }
        if(this.HasClassname("noempty"))
        {
            return val !== "";
        }

        return true;
    },
    // 转为整数
    Int:function(val)
    {
        if(undefined != val)
        {
            return this.Value(parseInt(val||0)||0);
        }
        return parseInt(this.Value()||0)||0;
    },
    // 转为整数
    Float:function(point, val)
    {
        point = point||2;
        if(undefined != val)
        {
            return this.Value(Util.Float(val, point));
        }
        return Util.Float(this.Value(), point);
    },
    // Rocky 2017-05-12 23:16:02
    Value:function(val)
    {
        if(this.is("input") || this.is("textarea"))
        {
            if(undefined !== val)
            {
                return this.val(val);
            }
            else
            {
                return this.val();
            }
        }
        else
        {
            if(undefined !== val)
            {
                return this.text(val);
            }
            else
            {
                return this.text();
            }
        }
        // return (this.is("input")||this.is("textarea")) ? this.val() : this.text();
        return this;
    },
    Tips:function(msg) // msg是数据，或func(tips) tips--当前显示框
    {
        var id = 'id_tips_1455637213';
        var $tips = $('#' + id);
        if(0 == $tips.length)
        {
            $tips = $('<div id="' + id + '" style="border: 1px solid #DEDEDE; padding: 8px; border-radius: 8px; color:gray; background:white; display:none;">[空]</div>')
                    .appendTo("body");
        }
        $tips.Open = function(){
            // if($tips.close_timer)
            // {
            //     clearTimeout($tips.close_timer);
            //     $tips.close_timer = null;
            // }
            return $tips.show();
        }
        $tips.Close = function(){
            // if(!$tips.close_timer)
            // {
            //     return;
            // }
            // $tips.close_timer = setTimeout(function(){
            //     $tips.hide();
            // }, 10);
            return $tips.hide();
        }
        this.each(function(){
            $(this).mousemove(function(event){
            // $(this).mouseover(function(event){
                if(event.ctrlKey) // ctrl键时，不移动；
                {
                    return;
                }
                if($tips.find(".fixed").IsChecked())
                {
                    return;
                }
                var str = $.isFunction(msg) ? msg.call(this, $tips) : msg;
                if(!str)
                {
                    return;
                }
                var pos = {};
                pos.top    = event.pageY + 10;
                pos.left   = event.pageX + 10;
                pos.zindex = Util.GetTimestamp();
                $tips.html(str).SetOpacity(0);
                $tips.Open();
                var cur_tip = $tips.GetPos();

                var diff_x = pos.left + cur_tip.width - document.body.scrollWidth;
                if(diff_x > 0)
                {
                    pos.left -= diff_x;
                }
                if(pos.left < event.pageX && event.pageX < pos.left + cur_tip.width)
                {
                    pos.left = event.pageX - cur_tip.width;
                }

                var diff_y = pos.top + cur_tip.height - document.body.scrollHeight;
                if(diff_y > 0)
                {
                    pos.top -= diff_y;
                }
                if(pos.top < event.pageY && event.pageY < pos.top + cur_tip.height)
                {
                    pos.top = event.pageY - cur_tip.height;
                }

                $tips.SetPos(pos).SetOpacity(1);
            }).mouseleave(function(event){  // mouseout
                if(event.ctrlKey) // ctrl键时，不移动；
                {
                    return;
                }
                if($tips.find(".fixed").IsChecked())
                {
                    return;
                }
                $tips.Close(); // Rocky 2016-12-09 19:59:01
            });
            $tips.mouseleave(function(event){ // mouseout
                if(event.ctrlKey) // ctrl键时，不移动；
                {
                    return;
                }
                if($tips.find(".fixed").IsChecked())
                {
                    return;
                }
                $tips.Close();
            });
        });// end of this.each(...

        return $tips;
    },//end of Tips:function(...
    // title转为tips层显示
    TitleToTips:function()
    {
        return $(this).each(function(){
            var $this = $(this);
            var title = $this.Title();
            if(!title)
            {
                return;
            }
            $this.Title("").Tips(title);
        });
    },
    // 用鼠标移动浮层
    MoveByMouse:function()
    {
        var begin = false;
        this.each(function(){
            var $this = $(this);
            $this.mousemove(function(event){
                // if(!event.ctrlKey) // ctrl键时，不移动；
                // {
                //     return;
                // }
                if(!begin)
                {
                    return;
                }

                var cur_pos = $this.GetPos();
                // 鼠标居中
                cur_pos.top    = event.pageY - cur_pos.height / 2;
                cur_pos.left   = event.pageX - cur_pos.width / 2;
                $this.SetPos(cur_pos);

                $this.html("top:" + cur_pos.top +
                    ", left:" + cur_pos.left +
                    ", width:" + cur_pos.width +
                    ", height:" + cur_pos.height
                );;
            }).mousedown(function(){
                $this.SetZindexTop();
            });
        });// end of this.each(...
        $("body").mousedown(function(){
            begin = true;
        }).mouseup(function(){
            begin = false;
        });
        return this;
    },
    // Rocky 2017-06-03 14:44:07
    // 闪动元素
    // $("#id_xxx").Flash().Start("green", 3000);
    // $("#id_xxx").Flash().Stop();
    Flash: function()
    {
        var $THIS = $(this);
        var flash_setting = $(this).data("flash_setting");
        if(flash_setting)
        {
            return flash_setting;
        }
        flash_setting = new function(){
            var THIS = this;
            var flash_timer = null;

            THIS.Start = function(color, open_msec, close_msec){
                open_msec = open_msec||1600;
                close_msec = close_msec||200;
                THIS.Stop();
                // 切换
                $THIS.css("background-color", color);
                flash_timer = setInterval(function(){
                    if(null == flash_timer)
                    {
                        return;
                    }
                    $THIS.css("background-color", "");
                    setTimeout(function(){
                        if(null != flash_timer)
                        {
                            $THIS.css("background-color", color);
                        }
                    }, close_msec);
                }, open_msec);
                return $THIS;
            }

            THIS.Stop = function(){
                if(flash_timer)
                {
                    clearInterval(flash_timer);
                    flash_timer = null;
                }
                return $THIS;
            }
        }
        $THIS.data("flash_setting", flash_setting);
        return flash_setting;
    }//end of Flash: function(...
}); // end of $.fn.extend({...

$.MaxZindex = function()
{
    if(!$.data)
    {
        $.data = {};
    }
    $.data.Zindex = parseInt($.data.Zindex||Util.GetTimestamp()) + 1;
    return $.data.Zindex;
}

// 再次封装json操作
$.Json = new function()
{
    var THIS = this;
    if($.isPlainObject(JSON) && $.isFunction(JSON.parse) && $.isFunction(JSON.stringify))
    {
        Init();
    }
    else
    {
        // http://www.x-non.com/json/jquery.json-2.4.min.js
        $.getScript("http://www.x-non.com/json/json2.min.js", function(){
            Init();
        });
    }
    function Init()
    {
        THIS.toObj = function(json_str)
        {
            try
            {
                if(json_str)
                {
                    return JSON.parse(json_str);
                }
            }
            catch(e)
            {
                Util.Alert(e.stack);
            }
            finally
            {
            }
            return {};
        };

        THIS.toStr = function(obj)
        {
            try
            {
                return JSON.stringify(obj);
            }
            catch(e)
            {
                Util.Alert(e.stack);
            }
            finally
            {
            }
            return "";
        };
    }
}

$.Overlayer = function($owner)
{
    var m_$owner = $owner || $("");
    var m_bOpen = false;
    var m_$layer = null;
    var m_bInit = false;

    function init()
    {
        if(m_bInit)
        {
            return true;
        }
        var OPACITY = 0.2;
        var BGCOLOR = "#000000";
        m_$layer = $("<div></div>").appendTo($("body"));
        var adjust = function()
        {
            var page_size = Util.GetPageSize();
            m_$layer.css({
                        position: "absolute",
                        top: 0 + 'px',
                        left: 0 + 'px',
                        margin: 0,
                        padding: 0,
                        width : page_size.page.w + 'px',
                        height : page_size.page.h + 'px',
                        background : BGCOLOR,  // http://www.xuwei.so/Test/ColorPicker/index.html
                        "font-size" : "37px",
                        "font-style": "italic",
                        "color" : "white",
                        opacity : OPACITY
                    });
        }
        $(window).resize(function(){
            adjust();
        });
        adjust();
        m_bOpen = false;
        m_bInit = true;
        return true;
    }

    this.Open = function()
    {
        if(m_bOpen)
        {
            return this;
        }
        if(!init())
        {
            return this;
        }
        m_$layer.show().SetZindexTop();
        m_$owner.SetZindexTop();
        m_bOpen = true;
        return this;
    }

    this.Close = function()
    {
        if(!m_bOpen)
        {
            return this;
        }
        m_$layer.fadeOut(300);
        m_bOpen = false;
        return this;
    }
}//end of Overlayer...

/*
 * 浮动窗口
 *  property = {
 *      is_center: true        // false:不居中
 *      is_top: false          // true:居顶
 *  }
 */
$.FloatBox = function($owner, property){
    property = property||{};
    var THIS = {};
    var bg = null;
    var adjust_interval = 10;

    THIS.Open = function(){
        if(!bg)
        {
            bg = new $.Overlayer($owner); // 在$owner下加一蒙盖层
        }
        bg.Open();
        $owner.fadeIn(100);
        AdjustPos(2);
        return THIS;
    }

    THIS.Close = function(){
        if(!bg)
        {
            return
        }
        $owner.fadeOut(300);
        bg.Close();
    }

    function AdjustPos(retry)
    {
        if(0 === retry)
        {
            adjust_interval = 10;
            return;
        }
        setTimeout(function(){
            //adjust_interval += 10;
            if(false !== property.is_center)
            {
                $owner.center();
            }
            if(true === property.is_top)
            {
                $owner.css({
                    top : 0
                });
            }
            if(retry > 0)
            {
                retry--;
                AdjustPos(retry);
            }
        }, adjust_interval);

    }

    // 窗口大小变动时，重置；
    $(window).resize(function(){
        AdjustPos(1);
    });
    // $(window).scroll(function(){
    //     AdjustPos(1);
    // });

    AdjustPos(5);

    return THIS;
}


})(jQuery);
