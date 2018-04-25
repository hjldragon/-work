/*
 * Rocky 2016-04-22 11:21:00
 * 各页面共用代码
 */
$(function() {
window.Public = {
    LoginInfo: new function(){
        var THIS = this;
        THIS.LoginInfo = $.Json.toObj(window.Store.GetGlobalData("logininfo", "{}"));
        THIS.ShopInfo = $.Json.toObj(window.Store.GetGlobalData("shopinfo", "{}"));
        THIS.UserInfo = $.Json.toObj(window.Store.GetGlobalData("userinfo", "{}"));
        THIS.EmployeeInfo = $.Json.toObj(window.Store.GetGlobalData("employeeinfo", "{}"));
        THIS.Str = function(){
            var userid = parseInt(window.Store.GetGlobalData("userid", 0));
            if(userid > 0)
            {
                var username = "";
                if(UserProperty.IsShopUser(this.UserInfo.property))
                {
                    username = this.EmployeeInfo.real_name;
                }
                if("" == username)
                {
                    username = this.UserInfo.username||userid;
                }
                var str  = "用户ID : " + userid + "<br/>\n";
                         // + "登录IP　：" + info.login_ip + "\n"
                         // + "登录时间：" + Util.TimeTo(info.login_time, "yyyy-MM-dd hh:mm  ")
                if(this.ShopInfo.shop_name)
                {
                    str += "店铺 : " + this.ShopInfo.shop_name + "\n";
                }
                return "<font color=blue class='tips' title='" + str +"'>" + username + "</font>，迎欢您！";
            }
            else
            {
                return "<a href='login.php'><font color=red>未登录</font></a>";
            }
        }
        THIS.Set = function(data){
            window.Store.SetGlobalData("userid", data.userinfo.userid);
            window.Store.SetGlobalData("username", data.userinfo.username);
            //$.cookie("userid", resp.data.userid, {expires:7, path:'/'});
            window.Store.SetGlobalData("logininfo", $.Json.toStr(data.logininfo));
            window.Store.SetGlobalData("userinfo", $.Json.toStr(data.userinfo));
            window.Store.SetGlobalData("shopinfo", $.Json.toStr(data.shopinfo));
            window.Store.SetGlobalData("employeeinfo", $.Json.toStr(data.employeeinfo));
        }
        THIS.Clear = function(){
            window.Store.DeleteGlobalData("userid");
            window.Store.DeleteGlobalData("username");
            window.Store.DeleteGlobalData("logininfo");
            THIS.LoginInfo = {};
            window.Store.DeleteGlobalData("userinfo");
            THIS.UserInfo = {};
            window.Store.DeleteGlobalData("shopinfo");
            THIS.ShopInfo = {};
            window.Store.DeleteGlobalData("employeeinfo");
            THIS.EmployeeInfo = {};
        }
        function Reload()
        {
        }
        Reload();  // 加载前后问题 [XXX]
    },
    // 显示登录窗口
    ShowLoginBox: function(opt){
        opt = opt||{};
        if(opt.relogin)
        {
            Public.LoginInfo.Clear();
        }
        if(window.LoginBox && $.isFunction(window.LoginBox.Open))
        {
            window.LoginBox.Open();
            return;
        }
        $.get('box.login.html?'+Util.GetTimestamp(), function(resp){
            $("body").append(resp);
            window.LoginBox.Open();
        });
    },
    /* 提示信息窗口
     * opt = {
     *      bt_close : [true|false],
     *      bt_ok : [true|false],
     *  }
     */
    MsgBox: function(msg, opt){
        if(window.MsgBox)
        {
            window.MsgBox.Show(msg, opt);
            return;
        }
        $.get('box.msg.html?'+Util.GetTimestamp(), function(resp){
            if($("#id_msg_box_create_flag").length > 0)
            {
                return
            }
            resp += "<div id='id_msg_box_create_flag'></div>";
            $(resp).appendTo($("body"));
            window.MsgBox.Show(msg, opt);
        });
    },
    // 向后台申请id, 并填充到$owner上
    // Rocky 2017-06-21 17:14:46
    GenId: function(type, $owner, callback){
        Util.EncSubmit("gen_id.php",
            {
                'genid' : 1,
                'type'  : type
            },
            function(resp){
                if(resp.ret < 0)
                {
                    $doing.html(errcode.toString(resp.ret)).show().SetErrStyle();
                    return;
                }
                if($owner)
                {
                    $owner.Value(resp.data.id);
                }
                if($.isFunction(callback))
                {
                    callback(resp.data.id);
                }
            }
        );
    }
}
});
