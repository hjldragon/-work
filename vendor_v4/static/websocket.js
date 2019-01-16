window.WebSock = new function () {
    const THIS = this;
    let wsuri = `ws://srv${window.full_url}:13010/websocket`;  // [XXX]
    // let wsuri = "ws://srv.jzzwlcm.com:13010/websocket"; 
    let sock = null;
    let callback_set = {};
    let connection_timer = null;
    let ping_timer = null;
    let binit = false;
    let last_id = (new Date).getTime();
    THIS.data_key = null;
    THIS.token = null;

    // token -- 当前终端标识
    // data_key -- 前后台数据传输的加密key
    function Init(url, token, data_key, callback) {
        if (binit) {
            return;
        }
        if (!window.WebSocket) {
            Public.MsgBox("当前浏览器不支持实时通知", { bt_close: true });
            return;
        }
        if (!token || !data_key) {
            Public.MsgBox("参数出错", { bt_close: true });
            return;
        }
        if (url) {
            wsuri = url;
        }
        THIS.token = token;
        THIS.data_key = data_key;

        ReConnect(callback);

        clearInterval(connection_timer);
        connection_timer = setInterval(() => {
            if (1 != sock.readyState) {
                ReConnect(callback);
            }
        }, 10000);

        clearInterval(ping_timer);
        ping_timer = setInterval(() => {
            if (1 == sock.readyState) {
                window.WebSock.Call("cmd_ping",
                    {
                        Timestamp: true,
                    },
                    (v) => {
                        //console.log(v);
                    } // 不需要处理
                );
            }
        }, 60000);
        binit = true;
    }
    THIS.Init = Init;

    // 单独更新datakey
    function SetDataKey(key) {
        THIS.data_key = key;
    }
    THIS.SetDataKey = SetDataKey;

    function ReConnect(callback) {
        if (typeof callback === "function") {
            // 连接信息
            DoCall(
                "#1509508494",
                {},
                resp => {
                    callback(resp);
                },
                { once: false, reg: true }
            );
        }
        sock = new WebSocket(wsuri);
        sock.onopen = function () {
            //console.log("connected to:[" + wsuri + "]");
        };
        sock.onclose = function (e) {
            // console.log("connection closed:[" + e.code + "]");
        };
        sock.onerror = function (e) {
            window.Toast.Show("网络或服务出错，请重新刷新页面试试。");
        };
        //监听窗口关闭事件，当窗口关闭时，主动去关闭websocket连接，防止连接还没断开就关闭窗口，server端会抛异常。
        window.onbeforeunload = () => {
            sock.close();
        };
        sock.onmessage = Recv;
    }

    function Close() {
        clearInterval(connection_timer);
        sock.close();
    }
    THIS.Close = Close;

    function Recv(e) {
        let resp = JSON.parse(e.data);
        if (Crypt.Encmode.AES == resp.encmode) {
            resp.data = JSON.parse(Crypt.Aes.Decrypt(resp.data));
        }
        let callback = resp.id ? callback_set[resp.id] : undefined;
        if (undefined == callback) {
            callback = callback_set[resp.name];
        }
        if (callback && typeof callback.func === "function") {
            resp.ret = parseInt(resp.ret);
       
            callback.func({
                id: resp.id,
                ret: resp.ret,
                data: resp.data,
            });
            if (callback.once && resp.id) {
                delete (callback_set[resp.id]);
            }
        }
        else {
            // console.log("data err:", resp);
        }
    }

    /*
     * opt.once -- true:只调用一次
     * opt.reg -- true:只注册，不发送请求
     */
    function DoCall(interface_name, interface_param, callback, opt) {
        opt = opt || {};

        let data = JSON.stringify(interface_param);
        if ("aes" == opt.encmode) {
            data = Crypt.Aes.Encrypt(data);
        }
        // 签名:md5(data+key)，注意，前后台应一致[XXX:1510827733]
        let sign = Crypt.Md5(data + THIS.data_key);
        req = {
            token: THIS.token,
            sign: sign,
            encmode: opt.encmode || "",
            is_plain: opt.is_plain ? 1 : 0,
            id: "#id#" + (last_id++).toString(),
            name: interface_name.trim(),
            param: data
        };
        // 只注册，不发请求
        if (opt.reg) {
            callback_set[req.name] = {
                func: callback,
                once: opt.once,
            };
            //console.log("register, id:" + req.id + ", cmd:" + req.name);
        }
        // 发送请求
        else {
            if (sock==null || 1 != sock.readyState) {
               // console.log("no connect");
                return;
            }
           
            callback_set[req.id] = {
                func: callback,
                once: opt.once,
            };
            sock.send(JSON.stringify(req));
            //  console.log("send, id:" + req.id + ", cmd:" + req.name);
        }
    }

    // 一次请求，一次返回
    THIS.Call = function (interface_name, interface_paramstr, callback, opt) {
        opt = opt || {};
        opt.once = true;
        return DoCall(
            interface_name,
            interface_paramstr,
            callback,
            opt
        );
    };

    // 订阅
    THIS.Subscribe = function (opr, param, callback) {
        return DoCall(
            "cmd_subscribe",
            {
                'opr': opr,
                'param': param,
            },
            callback,
            { once: false } // 可多次接收数据
        );
    };

    // 发布
    THIS.Publish = function (opr, data, callback) {
        return DoCall(
            "cmd_publish",
            {
                'opr': opr,
                'param': data,
            },
            callback,
            { once: true } // 只接收一次
        );
    };

    if (!window.Toast) {
        window.Toast = {};
    }
    if (!window.Toast.Show) {
        window.Toast.Show = (v => {
            // console.log(v);
        });
    }
    if (!window.Public) {
        window.Public = {};
    }
    if (!window.Public.MsgBox) {
        window.Public.MsgBox = (v => {
            // console.log(v);
        });
    }
};
