webpackJsonp([72],{JhnS:function(a,t,i){"use strict";function o(a){i("cMPw"),i("wipT")}Object.defineProperty(t,"__esModule",{value:!0});var e=i("hFGm"),n=i("dEQA"),s=i("XyMi"),p=o,r=Object(s.a)(e.a,n.a,n.b,!1,p,"data-v-399d7496",null);t.default=r.exports},NO5l:function(a,t,i){"use strict";i.d(t,"a",function(){return e});var o=i("EuEE"),e={silverSet:function(a,t){o.a.DataEncSubmit("shopinfo_save.php",a,function(a){t&&"function"==typeof t&&t(a)})},getShopData:function(a,t){o.a.DataEncSubmit("shopinfo_get.php",a,function(a){t&&"function"==typeof t&&t(a)})}}},bFQb:function(a,t,i){"use strict";i.d(t,"a",function(){return e});var o=i("NO5l"),e={saveSeting:function(a,t,i){o.a.silverSet(a,function(a){0===a.ret?t.validate(function(a){if(!a)return!1;i({message:"保存成功"})}):t.validate(function(){return!1})})}}},cMPw:function(a,t,i){var o=i("qpmX");"string"==typeof o&&(o=[[a.i,o,""]]),o.locals&&(a.exports=o.locals);i("rjj0")("74d454db",o,!0,{})},dEQA:function(a,t,i){"use strict";i.d(t,"a",function(){return o}),i.d(t,"b",function(){return e});var o=function(){var a=this,t=a.$createElement,i=a._self._c||t;return i("div",{attrs:{id:"openWechatPay"}},[0==a.isPremission?i("div",{staticClass:"noPremission"},[a._m(0)]):i("div",{staticClass:"formConfig"},[i("el-form",{ref:"noConfigForm",attrs:{"label-width":"152px",model:a.noConfigForm,"label-position":"right"}},[i("el-form-item",{attrs:{label:"支付宝APPID"}},[i("span",[a._v(a._s(a.noConfigForm.appId))])]),a._v(" "),i("el-form-item",{attrs:{label:"RSA私钥"}},[i("div",{staticClass:"passbox"},[a._v(a._s(a.noConfigForm.passWord))])]),a._v(" "),i("el-form-item",{attrs:{label:"支付宝公钥"}},[i("div",{staticClass:"passbox"},[a._v(a._s(a.noConfigForm.publicKey))])]),a._v(" "),i("el-form-item",{attrs:{label:"安全校验码"}},[i("div",{staticClass:"passbox"},[a._v(a._s(a.noConfigForm.test))])]),a._v(" "),i("el-form-item",{attrs:{label:"合作者身份"}},[i("div",{staticClass:"passbox"},[a._v(a._s(a.noConfigForm.partner))])]),a._v(" "),i("el-form-item",{attrs:{label:"支付宝账号"}},[i("div",{staticClass:"passbox"},[a._v(a._s(a.noConfigForm.accountNum))])])],1),a._v(" "),i("div",{staticClass:"save"},[i("el-button",{on:{click:a.silverSetSave}},[a._v("保存")])],1)],1)])},e=[function(){var a=this,t=a.$createElement,i=a._self._c||t;return i("div",{staticClass:"text left"},[i("p",{staticClass:"bigTop"},[a._v("使用支付宝支付，需先开通参数配置权限，请联系客服："),i("span",{staticClass:"blue"},[a._v("400-0020-158")])]),a._v(" "),i("p",[a._v("若您未在本平台内录入完整的工商和账户信息，您需要准备以下资料：")]),a._v(" "),i("p",[a._v("1.营业执照注册号、经营范围、营业期限、营业执照扫描件")]),a._v(" "),i("p",[a._v("2.法人代表姓名、法人手机号码、法人身份证正面和反面扫描件、身份证号码、身份证有效期")]),a._v(" "),i("p",[a._v("3.《餐饮服务许可证》（《食品卫生许可证》）或者提供实体店铺的三张照片（包含：1. 门头照含店名；2. 店内全景照；3. 店内收银台照）须为彩")]),a._v(" "),i("p",[a._v("色图片且小于2M，文件格式为bmp、png、jpeg、jpg或gif")]),a._v(" "),i("p",[a._v("4.开户银行名称、开户名称、银行账号")])])}]},hFGm:function(a,t,i){"use strict";var o=i("NO5l"),e=(i("bFQb"),i("6nXL")),n=i("a2vD");t.a={props:["config"],data:function(){return{isConfig:!1,noConfigForm:{appId:"",passWord:"",publicKey:"",test:"",partner:"",accountNum:""}}},created:function(){this.shopId=n.a.getShopid(),this.initData()},computed:{isPremission:function(){return this.config}},methods:{initData:function(){var a=this,t={select_alipay_set:1,shop_id:this.shopId,pay_way:2};o.a.getShopData(t,function(t){0===t.ret&&(a.noConfigForm.appId=void 0===t.data.alipay||0===t.data.alipay.length?"":t.data.alipay[e.S.NO].alipay_app_id,a.noConfigForm.passWord=void 0===t.data.alipay||0===t.data.alipay.length?"":t.data.alipay[e.S.NO].private_key,a.noConfigForm.publicKey=void 0===t.data.alipay||0===t.data.alipay.length?"":t.data.alipay[e.S.NO].public_key,a.noConfigForm.test=void 0===t.data.alipay||0===t.data.alipay.length?"":t.data.alipay[e.S.NO].safe_code,a.noConfigForm.partner=void 0===t.data.alipay||0===t.data.alipay.length?"":t.data.alipay[e.S.NO].hz_identity,a.noConfigForm.accountNum=void 0===t.data.alipay||0===t.data.alipay.length?"":t.data.alipay[e.S.NO].alipay_num,""!==a.noConfigForm.imgSrc&&(a.isImg=!0))})},silverSetSave:function(){var a=this,t=n.a.getShopid(),i={save_alipay_set:1,pay_way:2,shop_id:t};o.a.silverSet(i,function(t){0===t.ret?a.$slnotify({message:"保存成功"}):a.$slnotify({message:e.D.toString(t.ret)})})}}}},qpmX:function(a,t,i){t=a.exports=i("FZ+f")(!1),t.push([a.i,"#openWechatPay[data-v-399d7496]{margin-top:87px;margin-left:-40px}.passbox[data-v-399d7496]{width:90%;word-break:break-all}.save[data-v-399d7496]{width:100%;background:#fff;padding-left:38.5%}.img[data-v-399d7496]{width:50px;height:50px;margin-right:30px}.text-header[data-v-399d7496]{padding-left:75px}img[data-v-399d7496]{width:100%;height:100%}.text[data-v-399d7496]{width:80%;margin-left:35px}p[data-v-399d7496]{font-size:12px;color:#9b9b9b;height:30px;line-height:30px}.small[data-v-399d7496]{font-size:12px;color:#a9a9a9}.blue[data-v-399d7496]{font-size:12px;color:#4877e7}.bigTop[data-v-399d7496]{height:50px;line-height:50px}.notConfig[data-v-399d7496]{width:60%}.img[data-v-399d7496]{width:100px;height:100px;margin-right:20px;position:relative}.img img[data-v-399d7496]{width:100%;height:100%}.img:hover .el-icon-zoom-in[data-v-399d7496],.img:hover .mask[data-v-399d7496]{display:block}.mask[data-v-399d7496]{display:none;position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5)}.el-icon-zoom-in[data-v-399d7496]{display:none;position:absolute;opacity:1;top:40%;left:40%;font-size:20px;color:#fff;cursor:pointer}",""])},sTjx:function(a,t,i){t=a.exports=i("FZ+f")(!1),t.push([a.i,"#openWechatPay .el-radio.yes{margin-right:100px}#openWechatPay .el-button.submitBtn{margin:0 30px 0 200px}#openWechatPay .el-button.cancelBtn{background:#fff;color:#4877e7}#openWechatPay .el-button.downLoad{background:#fff;color:#666;border:1px solid #666}#openWechatPay .el-form-item__label{text-align:right;padding-right:47px}#openWechatPay .formConfig .el-form{margin-left:5px;padding-left:0}",""])},wipT:function(a,t,i){var o=i("sTjx");"string"==typeof o&&(o=[[a.i,o,""]]),o.locals&&(a.exports=o.locals);i("rjj0")("bf5db032",o,!0,{})}});