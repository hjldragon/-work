
$(function() {
    window.WebSock = new function(){
        var THIS = this;
        var sock = null;
        var wsuri = "ws://" + location.hostname + ":21121/websocket";  // [XXX]
        var callback_set = {};
        var conn_test = null;
        var data_key = "";
        var connect_timer = null;

        function Init(callback)
        {
            if(!Util.IsDefine(window.WebSocket))
            {
                Public.MsgBox("当前浏览器不支持实时通知", {bt_close:true});
                return;
            }
            ReConnect(callback);
            if(null != connect_timer)
            {
                clearInterval(connection_timer);
            }
            connection_timer = setInterval(function(){
                if(1 != sock.readyState)
                {
                    ReConnect(callback);
                }
            }, 3000);
        }
        THIS.Init = Init;

        function ReConnect(callback)
        {
            sock = new WebSocket(wsuri);
            sock.onopen = function() {
                console.log("connected to:[" + wsuri + "]");
                if($.isFunction(callback))
                {
                    callback();
                }
            }
            sock.onclose = function(e) {
                console.log("connection closed:[" + e.code + "]");
            }
            sock.onerror = function(e) {
                window.Toast.Show("网络或服务出错，请重新刷新页面试试。");
            }
            sock.onmessage = Recv;
        }

        function Recv(e)
        {
            var resp = $.Json.toObj(e.data);
            var callback = callback_set[resp.Name];
            if(callback != null && $.isFunction(callback))
            {
                resp.Ret = parseInt(resp.Ret);
                return callback(resp);
            }
        }

        function Call(interface_name, interface_paramstr, callback)
        {
            if(1 != sock.readyState)
            {
                return;
            }
            req = {
                Name: interface_name,
                Param: interface_paramstr
            }
            sock.send($.Json.toStr(req));
            callback_set[interface_name] = callback;
        }
        THIS.Call = Call;


        function Register(interface_name, callback)
        {
            callback_set[interface_name] = callback;
        }
        THIS.Register = Register;
    }
});
