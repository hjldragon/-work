/*
CryptoJS v3.1.2
code.google.com/p/crypto-js
(c) 2009-2013 by Jeff Mott. All rights reserved.
code.google.com/p/crypto-js/wiki/License
*/
/**
 * Zero padding strategy.
 */
CryptoJS.pad.ZeroPadding = {
    pad: function (data, blockSize) {
        // Shortcut
        var blockSizeBytes = blockSize * 4;

        // Pad
        data.clamp();
        data.sigBytes += blockSizeBytes - ((data.sigBytes % blockSizeBytes) || blockSizeBytes);
    },

    unpad: function (data) {
        // Shortcut
        var dataWords = data.words;

        // Unpad
        var i = data.sigBytes - 1;
        while (!((dataWords[i >>> 2] >>> (24 - (i % 4) * 8)) & 0xff)) {
            i--;
        }
        data.sigBytes = i + 1;
    }
};
;const Crypt = {
    // 加密模式
    Encmode : Object.freeze({
        AES : "aes",
    }),
    Md5 : function(str) {
        return md5(str);
    },
    Rsa : new function(){
        let rsa = null;
        this.SetPublicKey = (pub_key_str)=>{
            // -----BEGIN PUBLIC KEY-----
            // MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQClnAfSpNh3EMKoMGN10MWlCmV+
            // 8lcYU92GnvgHVlFn9rS9aZEig9Dy+9Wos13Zfszp3qfPo7NlnXP59CUKlC07zw/Z
            // 8VPJsHQrsah2HX6nQXKlgyFcqB6q6GoRI4Vp36Vdu8XoNSiWsz7KpBY7MHgMy4uA
            // xsH7vYPq9U30Q0sBlwIDAQAB
            // -----END PUBLIC KEY-----
            rsa = new JSEncrypt();
            rsa.setPublicKey(pub_key_str);
        }
        this.Encrypt = (str)=>{
            if(null == rsa)
            {
                return str;
            }
            return rsa.encrypt(str);
        }
        this.Decrypt = (enc)=>{
            if(null == rsa)
            {
                return enc;
            }
            return rsa.decrypt(enc);
        }
    },
    // <script src="aes.js"></script>
    // <script src="pad-zeropadding.js"></script>
    Aes: new function(){
        let key = null;
        let iv = null;
        this.SetKey = (key_str)=>{
            key_str = Crypt.Md5(key_str).substr(0, 16);
            key = CryptoJS.enc.Utf8.parse(key_str);
            iv = key;
            if(!CryptoJS.pad.ZeroPadding)
            {
                console.log("CryptoJS.pad.ZeroPadding lose");
            }
        }
        this.Encrypt = (str)=>{
            if(null == key)
            {
                return str;
            }
            return CryptoJS.AES.encrypt(str, key, {
                iv      : iv,
                mode    : CryptoJS.mode.CBC,
                padding : CryptoJS.pad.ZeroPadding
            }).toString();
        }
        this.Decrypt = (enc)=>{
            if(null == key)
            {
                return enc;
            }
            return CryptoJS.AES.decrypt(enc, key, {
                iv      : iv,
                mode    : CryptoJS.mode.CBC,
                padding : CryptoJS.pad.ZeroPadding
            }).toString(CryptoJS.enc.Utf8);
        }
    }
};window.WebSock = new function(){
    const THIS = this;
    // const wsuri = "ws://" + location.hostname + ":13010/websocket";  // [XXX]
    const wsuri = "ws://srv.jzzwlcm.com:13010/websocket";
    let sock = null;
    let callback_set = {};
    let connection_timer = null;
    let ping_timer = null;
    let binit = false;
    let last_id = (new Date).getTime();
    let data_key = "";

    function Init(url, callback)
    {
        if(binit)
        {
            return;
        }
        if(!window.WebSocket)
        {
            Public.MsgBox("当前浏览器不支持实时通知", {bt_close:true});
            return;
        }
        if(!url)
        {
            wsuri = url;
        }
        ReConnect(callback);

        clearInterval(connection_timer);
        connection_timer = setInterval(function(){
            if(1 != sock.readyState)
            {
                ReConnect(callback);
            }
        }, 10000);

        clearInterval(ping_timer);
        ping_timer = setInterval(function(){
            if(1 == sock.readyState)
            {
                window.WebSock.Call("cmd_ping",
                    {
                        Timestamp: true,
                    },
                    (v)=>{
                        console.log(v);
                    } // 不需要处理
                );
            }
        }, 60000);
        binit = true;
    }
    THIS.Init = Init;

    function ReConnect(callback)
    {
        if(typeof callback === "function")
        {
            // 连接信息
            DoCall(
                "#1509508494",
                {},
                resp => {
                    if(resp.data.rsa_pub_key)
                    {
                        Crypt.Rsa.SetPublicKey(resp.data.rsa_pub_key);
                        SubmitKey(resp.data.rsa_pub_key, () => {
                            callback(resp);
                        });
                    }
                },
                {once:false, reg:true}
            );
        }

        sock = new WebSocket(wsuri);
        sock.onopen = function() {
            console.log("connected to:[" + wsuri + "]");
        };
        sock.onclose = function(e) {
            console.log("connection closed:[" + e.code + "]");
        };
        sock.onerror = function(e) {
            window.Toast.Show("网络或服务出错，请重新刷新页面试试。");
        };
        //监听窗口关闭事件，当窗口关闭时，主动去关闭websocket连接，防止连接还没断开就关闭窗口，server端会抛异常。
        window.onbeforeunload = ()=>{
            sock.close();
        };
        sock.onmessage = Recv;
    }

    function Close()
    {
        clearInterval(connection_timer);
        sock.close();
    }
    THIS.Close = Close;

    function SubmitKey(rsa_pub_key, callback)
    {
        data_key = "K1" + (Math.random() + "" + Math.random()).replace(/\./g, "").substr(2, 14);
        console.log("data_key:", data_key);
        DoCall(
            "cmd_save_data_key",
            {
                "data_key" : Crypt.Rsa.Encrypt(data_key),
            },
            resp => {
                console.log("SubmitKey", resp);
                if(0 == resp.ret)
                {
                    Crypt.Aes.SetKey(data_key);
                }
                callback(resp.ret);
            },
            {
                once: true,
                is_plain: true,
            }
        );
    }

    function Recv(e)
    {
        let resp = JSON.parse(e.data);
        if(Crypt.Encmode.AES == resp.encmode)
        {
            resp.data = JSON.parse(Crypt.Aes.Decrypt(resp.data));
        }
        let callback = resp.id?callback_set[resp.id]:undefined;
        if(undefined == callback)
        {
            callback = callback_set[resp.name];
        }
        if(callback && typeof callback.func === "function")
        {
            resp.ret = parseInt(resp.ret);
            callback.func({
                id   : resp.id,
                ret  : resp.ret,
                data : resp.data,
            });
            if(callback.once && resp.id)
            {
                delete(callback_set[resp.id]);
            }
        }
        else
        {
            console.log("data err:", resp);
        }
    }

    /*
     * opt.once -- true:只调用一次
     * opt.reg -- true:只注册，不发送请求
     */
    function DoCall(interface_name, interface_param, callback, opt)
    {
        opt = opt||{};

        let data = JSON.stringify(interface_param);
        if("aes" == opt.encmode)
        {
            data = Crypt.Aes.Encrypt(data);
        }
        // 签名:md5(data+key)，注意，前后台应一致[XXX:1510827733]
        let sign = Crypt.Md5(data + data_key);
        req = {
            token    : this.token||"",
            sign     : sign,
            encmode  : opt.encmode||"",
            is_plain : opt.is_plain?1:0,
            id       : "#id#" + (last_id++).toString(),
            name     : interface_name.trim(),
            param    : data
        };
        // 只注册，不发请求
        if(opt.reg)
        {
            callback_set[req.name] = {
                func : callback,
                once : opt.once,
            };
            console.log("register, id:" + req.id + ", cmd:" + req.name);
        }
        // 发送请求
        else
        {
            if(1 != sock.readyState)
            {
                console.log("no connect");
                return;
            }
            callback_set[req.id] = {
                func : callback,
                once : opt.once,
            };
            sock.send(JSON.stringify(req));
            console.log("send, id:" + req.id + ", cmd:" + req.name);
        }
    }

    // 一次请求，一次返回
    THIS.Call = function(interface_name, interface_paramstr, callback, opt){
        opt = opt||{};
        opt.once = true;
        return DoCall(
            interface_name,
            interface_paramstr,
            callback,
            opt
        );
    };

    // 订阅
    THIS.Subscribe = function(opr, param, callback, opt){
        opt = opt||{};
        opt.once = false; {false;} // 可多次接收数据
        return DoCall(
            "cmd_subscribe",
            {
                'opr'   : opr,
                'param' : param,
            },
            callback,
            opt
        );
    };

    // 发布
    THIS.Publish = function(opr, data, callback, opt){
        opt = opt||{};
        opt.once = true; // 只接收一次
        return DoCall(
            "cmd_publish",
            {
                'opr'   : opr,
                'param' : data,
            },
            callback,
            opt
        );
    };

    if(!window.Toast)
    {
        window.Toast = {};
    }
    if(!window.Toast.Show)
    {
        window.Toast.Show = (v => {
            console.log(v);
        });
    }
    if(!window.Public)
    {
        window.Public = {};
    }
    if(!window.Public.MsgBox)
    {
        window.Public.MsgBox = (v => {
            console.log(v);
        });
    }
};
