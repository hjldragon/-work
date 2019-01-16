const Crypt = new function(){
    // 加密模式
    this.Encmode = Object.freeze({
        AES : "aes",
    })

    this.Md5 = function(str) {
        return md5(str);
    }

    this.Rsa = new function(){
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
    }

    // <script src="aes.js"></script>
    // <script src="pad-zeropadding.js"></script>
    this.Aes = new function(){
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

    this.Encrypt = function(password, data){
        try
        {
            return encode(password, data);
        }
        catch(e)
        {
            Util.Alert(e.stack);
        }
        return "";
    }

    this.Decrypt = function(password, data){
        try
        {
            return decode(password, data);
        }
        catch(e)
        {
            Util.Alert(e.stack);
        }
        return "";
    }

    /*
     * 功能：打乱及还原字符串
     * 编写：Rocky 2010-05-14 10:42:45
     */
    const Swap = {
        // 把str顺序打乱
        doit : function(seed, str)
        {
            var result = str.split('');
            var len = result.length;
            seed += len; // 再加串的长度做相关性

            for(var i=0; i<len; i++)
            {
                var range = len - i - 1;    //// alert(range);
                var m = this.Rand(seed, i, 0, range);

                var tmp = result[ range ];
                result[ range ] = result[ m ];
                result[ m ] = tmp;
            }
            //result[ len ] = '\0';
            return result.join('');
        },

        // 对应于Swap0()，即还原str串；
        undo : function(seed, str)
        {
            var result = str.split('');
            var len = result.length;
            seed += len; // 再加串的长度做相关性

            for(var i=len-1; i>=0; i--)
            {
                var range = len - i - 1;
                var m = this.Rand(seed, i, 0, range);

                var tmp = result[ m ];
                result[ m ] = result[ range ];
                result[ range ] = tmp;
            }
            return result.join('');
        },

        // 取随机数（伪）
        Rand : function(seed, n, min, max)
        {
            var m = ( ~(seed * 262147 * n) ) & 0x0FFFFFFF;
            var range = max + 1 - min;
            if(range <= 0)
            {
                range = 1;
            }
            var ret = (min + m) % range;
            //alert(seed + ',' + n + ',' + min + ',' + max + ',' + m + ',' + range + ',' + ret);
            return ret;
        }
    };

    // 计算种子（简单相加
    const CalSeed = function(str)
    {
        var seed = 0;
        for(var i=0; i<str.length; i++)
        {
            seed = (seed + str.charCodeAt(i)) & 0x7FFFFFFF;
        }
        return seed & 0x7FFFFFFF;
    }

    const encode = function(password, data)
    {
        var pasd_b64 = encodeURIComponent(password);
        var plaintext_b64 = encodeURIComponent(data);
        var ciphertext = '';
        var pasd_i = 0;

        for(var data_i=0; data_i<plaintext_b64.length; data_i++)
        {
            //ciphertext += String.fromCharCode(plaintext_b64.charCodeAt(data_i) ^ pasd_b64.charCodeAt(pasd_i));
            var num = (plaintext_b64.charCodeAt(data_i) ^ pasd_b64.charCodeAt(pasd_i)).toString(16);
            if(num.length < 2)
            {
                num = '0' + num;
            }
            ciphertext += num;
            pasd_i++;
            if(pasd_i == pasd_b64.length)
            {
                pasd_i = 0;
            }
        }

        var seed = this.CalSeed(pasd_b64);
        return this.Swap.doit(seed, ciphertext);
    }

    const decode = function(password, data)
    {
        var pasd_b64 = encodeURIComponent(password);
        var seed = this.CalSeed(pasd_b64);
        var ciphertext = this.Swap.undo(seed, data);
        var plaintext_b64 = '';
        var pasd_i = 0;

        for(var i=0; i<ciphertext.length; i+=2)
        {
            //plaintext_b64 += String.fromCharCode(ciphertext.charCodeAt(i) ^ pasd_b64.charCodeAt(pasd_i));
            var hex = parseInt('0x' + ciphertext.charAt(i) + ciphertext.charAt(i+1));
            var ascii = hex ^ pasd_b64.charCodeAt(pasd_i);
            plaintext_b64 += String.fromCharCode(ascii);
            pasd_i++;
            if(pasd_i == pasd_b64.length)
            {
                pasd_i = 0;
            }
        }

        return decodeURIComponent(plaintext_b64);
    }
}
