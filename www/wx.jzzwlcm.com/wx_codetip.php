<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>扫码提示</title>

    <script>
        !function (e, t) { function n() { t.body ? t.body.style.fontSize = 12 * o + "px" : t.addEventListener("DOMContentLoaded", n) } function d() { var e = i.clientWidth / 10; i.style.fontSize = e + "px" } var i = t.documentElement, o = e.devicePixelRatio || 1; if (n(), d(), e.addEventListener("resize", d), e.addEventListener("pageshow", function (e) { e.persisted && d() }), o >= 2) { var a = t.createElement("body"), s = t.createElement("div"); s.style.border = ".5px solid transparent", a.appendChild(s), i.appendChild(a), 1 === s.offsetHeight && i.classList.add("hairlines"), i.removeChild(a) } }(window, document);
    </script>

    <style>
        * {
            margin: 0;
            padding: 0;
        }

        p {
            text-align: center;
        }

        .fail-tip {
            color: #666666;
            font-size: 0.32rem;
            margin-top: 0.36rem;
        }

        .fail-msg {
            font-size: 0.4rem;
            color: #FF3738;
        }

        .icon {
            text-align: center;
            margin: 2.27rem 0 0.8rem;
        }

        .icon img {
            width: 1.68rem;
            height: 1.68rem;
        }

        .confirm-btn {
            width: 8.93rem;
            height: 1.06rem;
            background: #5A8CFF;
            border-radius: 0.53rem;
            margin: 1.89rem 0.51rem 0;
            color: #fff;
            font-size: 0.43rem;
            line-height: 1.06rem;
            text-align: center;
        }


        /* 成功弹窗 */
        .success-msg {
            font-size: 0.4rem;
            color: #5A8CFF;
            padding-bottom: 0.69rem;
        }

        .hide {
            display: none;
        }

        .show {
            display: block;
        }
    </style>

</head>

<body>
    <!-- 微信登录成功 -->
        <div class="hide <?php if($data == 1){echo 'show';} ?>">
        <div class="icon">
            <img src="./images/success.png" alt="">
        </div>
        <p class="success-msg">微信登录成功</p>
        <div class="confirm-btn" onclick="WeixinJSBridge.call('closeWindow');">确定</div>
    </div>

    <!-- 微信登录失败 此微信未绑定微信-->
    <div class="hide <?php if($data == 2){echo 'show';} ?>">
        <div class="icon">
            <img src="./images/fail.png" alt="">
        </div>
        <p class="fail-msg">微信登录失败</p>
        <p class="fail-tip">此微信未绑定店铺</p>
        <div class="confirm-btn" onclick="WeixinJSBridge.call('closeWindow');">确定</div>
    </div>
    
    <!-- 微信绑定成功 -->
    <div class="hide <?php if($data == 3){echo 'show';} ?>">
        <div class="icon">
            <img src="./images/success.png">
        </div>
        <p class="success-msg">微信绑定成功</p>
        <div class="confirm-btn" onclick="WeixinJSBridge.call('closeWindow');">确定</div>
    </div>

    <!-- 微信绑定失败 微信已绑定其他店铺 -->
    <div class="hide <?php if($data == 4){echo 'show';} ?>">
        <div class="icon">
            <img src="./images/fail.png">
        </div>
        <p class="fail-msg">微信绑定失败</p>
        <p class="fail-tip">微信已绑定其他店铺</p>
        <div class="confirm-btn" onclick="WeixinJSBridge.call('closeWindow');">确定</div>
    </div>



    <!-- 微信解绑成功 -->
    <div class="hide <?php if($data == 5){echo 'show';} ?>">
        <div class="icon">
            <img src="./images/success.png" alt="">
        </div>
        <p class="success-msg">微信解绑成功</p>
        <div class="confirm-btn" onclick="WeixinJSBridge.call('closeWindow');">确定</div>
    </div>

    <!-- 微信解绑失败 -->
    <div class="hide <?php if($data == 6){echo 'show';} ?>">
        <div class="icon">
            <img src="./images/fail.png" alt="">
        </div>
        <p class="fail-msg">微信解绑失败</p>
        <p class="fail-tip">微信与被绑店铺不符</p>
        <div class="confirm-btn" onclick="WeixinJSBridge.call('closeWindow');">确定</div>
    </div>
    <!-- 微信登陆点餐机失败 -->
    <div class="hide <?php if($data == 7){echo 'show';} ?>">
        <div class="icon">
            <img src="./images/fail.png" alt="">
        </div>
        <p class="fail-msg">微信登录失败</p>
        <p class="fail-tip">微信与被绑定点餐机不符</p>
        <div class="confirm-btn" onclick="WeixinJSBridge.call('closeWindow');">确定</div>
    </div>
    <!-- 微信登陆点餐机失败 -->
    <div class="hide <?php if($data == 8){echo 'show';} ?>">
        <div class="icon">
            <img src="./images/fail.png" alt="">
        </div>
        <p class="fail-msg">微信登录失败</p>
        <p class="fail-tip">账号已被冻结或未授权</p>
        <div class="confirm-btn" onclick="WeixinJSBridge.call('closeWindow');">确定</div>
    </div>
    <!-- 微信登陆点餐机失败 -->
    <div class="hide <?php if($data == 9){echo 'show';} ?>">
        <div class="icon">
            <img src="./images/fail.png" alt="">
        </div>
        <p class="fail-msg">微信登录失败</p>
        <p class="fail-tip">账号无店铺存在</p>
        <div class="confirm-btn" onclick="WeixinJSBridge.call('closeWindow');">确定</div>
    </div>
    <!-- 数据请求失败 -->
    <div class="hide <?php if($data == 10){echo 'show';} ?>">
        <div class="icon">
            <img src="./images/fail.png" alt="">
        </div>
        <p class="fail-msg">系统忙...</p>
        <div class="confirm-btn" onclick="WeixinJSBridge.call('closeWindow');">确定</div>
    </div>                
</body>

</html>
