const Crypt = {
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
}