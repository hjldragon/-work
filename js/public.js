/*
 * Rocky 2016-04-22 11:21:00
 * 各页面共用代码
 */

var Public = {
    LoginInfo: new function(){
        this.Str = function(){
            var userid = parseInt(window.Store.GetGlobalData('userid', 0));
            if(userid > 0)
            {
                var username = window.Store.GetGlobalData('username')||userid;
                var str  = "用户ID : " + userid + "\n"
                         // + "登录IP　：" + info.login_ip + "\n"
                         // + "登录时间：" + Util.TimeTo(info.login_time, "yyyy-MM-dd hh:mm  ")
                         + "";
                return "<font color=blue title='" + str +"'>" + username + "</font>，迎欢您！";
            }
            else
            {
                return "<a href='login.php'><font color=red>未登录</font></a>";
            }
        }
    },
    // 显示登录窗口
    ShowLoginBox: function(opt){
        opt = opt||{};
        if(opt.relogin)
        {
            window.Store.SetGlobalData("userid", "");
        }
        if(window.LoginBox)
        {
            window.LoginBox.Open();
            return;
        }
        $.get('box.login.html?'+Util.GetTimestamp(), function(resp){
            if($("#id_login_box_create_flag").length > 0)
            {
                return
            }
            resp += "<div id='id_login_box_create_flag'></div>";
            $(resp).appendTo($("body"));
        });
    },
    // 提示信息窗口
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
    }
}
