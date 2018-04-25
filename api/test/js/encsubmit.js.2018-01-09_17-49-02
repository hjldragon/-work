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
const Http = new function(){
    const THIS = this;
    const RSA_SVC_PATH = "./php/rsa_info.php";
    THIS.token = window.Store.GetGlobalData("token", "");
    THIS.data_key = window.Store.GetGlobalData("key", "");

    window.Store.GlobalWatch('key', v=>{
        console.log(v);
        THIS.data_key = v.new_value;
    })
    window.Store.GlobalWatch('token', v=>{
        //console.log(v);
        THIS.token = v.new_value;
    })

    const JsonToUrlParam = function(param){
        let p = new URLSearchParams();
        for(var i in param)
        {
            p.append(i, param[i]);
        }
        return p;
    }

    const Post = function(url, param, callback){
        let p = JsonToUrlParam(param);
        axios.post(url, p)
        .then((resp)=>{
            resp = resp||{};
            resp = resp.data||{};
            resp.ret = parseInt(resp.ret)
            if(isNaN(resp.ret))
            {
                resp.ret = -1;
            }
            resp.data = resp.data||{};
            callback(resp);
        }).catch(function (e) {
            let msg = "";
            if(e.response)
            {
                msg = e.response.status + ", " + e.response.statusText;
            }
            else
            {
                msg = e.message;
            }
            callback({
                ret: -1,
                msg: msg
            });
        });
    }

    const GetPublicKey = function(callback){
        Post(RSA_SVC_PATH, {publickey:1}, callback)
    }

    const SubmitDataKey = function(publickey, callback) {
        let key = Util.GetRandString(16); // 随机key
        console.log("key:" + key);
        var rsa = new JSEncrypt();
        rsa.setPublicKey(publickey);
        var key_enc = rsa.encrypt(key);
        let p = {
            "save_key" : 1,
            "is_plain" : 1,
            "key_enc"  : key_enc,
            "token"    : THIS.token,
        };
        Post(RSA_SVC_PATH, p, resp=>{
            THIS.data_key = key;
            window.Store.SetGlobalData('key', key);
            callback(resp);
        })
    }

    THIS.EncSubmit = function(url, data, resp_callback/*, other --> {dataType:xxx, mimeType:xxx, ...}*/){
        if(! data instanceof Object)
        {
            // param err
            return;
        }
        resp_callback = resp_callback||function(v){};
        let data_type = null;
        let mime_type = null;
        let is_get_param = null;
        //let is_async = true; // 默认异步
        let encmode = "";
        if(arguments.length > 3) // 可变参数时
        {
            is_get_param = arguments[3].is_get_param;
            encmode = arguments[3].encmode;
        }

        // 当前终端的标识
        if(!THIS.token)
        {
            THIS.token = "T3" + Util.GetRandString(14);
            window.Store.SetGlobalData("token", THIS.token);
        }

        const ToServer = function(){
            let datastr = JsonToUrlParam(data).toString();
            if("encrypt1" == encmode)
            {
                datastr = encrypt(THIS.data_key, datastr)
            }
            var param = {
                token   : THIS.token,
                encmode : encmode,
                data    : datastr,
                sign    : Crypt.Md5(datastr + THIS.data_key)
            };

            // 只取参数
            if(is_get_param)
            {
                return param;
            }

            Post(
                url + "?" + (new Date()).getTime(),
                param,
                resp => {
                    if(-10020 == resp.ret)  // USER_NOLOGIN
                    {
                        resp_callback(resp);
                    }
                    else if(-10022 == resp.ret)  // DATA_KEY_NOT_EXIST
                    {
                        resp_callback(resp);
                        window.Store.SetGlobalData('key', '');
                    }
                    if(0 === resp.ret && resp.crypt == "1" && resp.data !== "")
                    {
                        resp.data = JSON.parse(decrypt(THIS.data_key, resp.data))
                        delete resp.crypt;
                    }
                    return resp_callback(resp);
                }
            );
        }

        // 前后台数据加密（验证）用随机密码
        if(!THIS.data_key)
        {
            GetPublicKey(resp=>{
                console.log(resp.data.publickey);
                if(0 !== resp.ret)
                {
                    resp_callback(resp);
                    return;
                }
                SubmitDataKey(resp.data.publickey, v=>{
                    if(0 !== resp.ret)
                    {
                        resp_callback(resp);
                        return;
                    }
                    console.log(v);
                    ToServer();
                });
            })
        }
        else
        {
            ToServer();
        }
    }// end of EncSubmit : function(...
}
