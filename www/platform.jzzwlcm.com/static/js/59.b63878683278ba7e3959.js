webpackJsonp([59],{BXRQ:function(e,t,n){"use strict";(function(e){var r=n("a2vD"),a=n("6nXL"),i=n("USo7");t.a={props:["shopId"],data:function(){var e=this;return{height:0,ruleForm:{pc_num:2,pad_num:2,cashier_num:2,app_num:2,machine_num:2,save_authorize:1,shop_id:""},rules:{pc_num:[{required:!0,validator:function(t,n,r){var a=/^[0-9]\d*$/;return""===a.test(e.ruleForm.pc_num)?r(new Error("请输入登录授权数")):!1===a.test(e.ruleForm.pc_num)?r(new Error("请输入格式正确的整数")):void r()},trigger:"change,input,click"}],pad_num:[{required:!0,validator:function(t,n,r){var a=/^[0-9]\d*$/;return""===a.test(e.ruleForm.pad_num)?r(new Error("请输入登录授权数")):!1===a.test(e.ruleForm.pad_num)?r(new Error("请输入格式正确的整数")):void r()},trigger:"change,input,click"}],cashier_num:[{required:!0,validator:function(t,n,r){var a=/^[0-9]\d*$/;return""===a.test(e.ruleForm.cashier_num)?r(new Error("请输入登录授权数")):!1===a.test(e.ruleForm.cashier_num)?r(new Error("请输入格式正确的整数")):void r()},trigger:"change,input,click"}],app_num:[{required:!0,validator:function(t,n,r){var a=/^[0-9]\d*$/;return""===a.test(e.ruleForm.app_num)?r(new Error("请输入登录授权数")):!1===a.test(e.ruleForm.app_num)?r(new Error("请输入格式正确的整数")):void r()},trigger:"change,input,click"}],machine_num:[{required:!0,validator:function(t,n,r){var a=/^[0-9]\d*$/;return""===a.test(e.ruleForm.app_num)?r(new Error("请输入登录授权数")):!1===a.test(e.ruleForm.app_num)?r(new Error("请输入格式正确的整数")):void r()},trigger:"change,input,click"}]},isCantClick:!0}},created:function(){var t=this;this.height=document.documentElement.clientHeight-146+"px",e(window).resize(function(){t.height=document.documentElement.clientHeight-146+"px"}),this.staffId=r.a.getUserid(),this.ruleForm.shop_id=this.$route.query.shopId,this.getServicData()},mounted:function(){this.checkNumNone()},components:{},computed:{refionAgent:function(){return a.t.toString(a.t.REGION)}},methods:{getServicData:function(){var e=this,t={get_shop_info:1,shop_id:this.ruleForm.shop_id};Object(i.e)(t).then(function(t){if(0===t.ret){var n=t.data.shopinfo.authorize;e.ruleForm.pc_num=null!==n.pc_num?n.pc_num:2,e.ruleForm.app_num=null!==n.app_num?n.app_num:2,e.ruleForm.pad_num=null!==n.pad_num?n.pad_num:2,e.ruleForm.cashier_num=null!==n.cashier_num?n.cashier_num:2,e.ruleForm.machine_num=null!==n.machine_num?n.machine_num:2}})},handleChangePcNum:function(){this.checkNumNone()},handleChangePadNum:function(){this.checkNumNone()},handleChangeCounterNum:function(){this.checkNumNone()},handleChangeAppNum:function(){this.checkNumNone()},handleChangeMacNum:function(){this.checkNumNone()},checkNumNone:function(){var e=this;this.$refs.ruleForm.validate(function(t){e.isCantClick=!t})},saveInput:function(){var e=this;Object(i.f)(this.ruleForm).then(function(t){0===t.ret&&e.$router.push({path:"/merchantlist/editMerchantDetail",query:{merchantInfo:e.$route.query.shopId}})})},pleaseFinish:function(){this.checkNumNone()},cancelEdit:function(){this.$router.push({path:"/merchantlist/editMerchantDetail",query:{merchantInfo:this.$route.query.shopId}})}}}}).call(t,n("L7Pj"))},TBdU:function(e,t){},nKsX:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=n("BXRQ"),a={render:function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",{attrs:{id:"sear-info"}},[r("div",{staticClass:"header"},[r("p",[r("router-link",{attrs:{to:{path:"/merchant/list"}}},[r("span",{staticClass:"black"},[e._v("商户列表")])]),e._v(" "),r("span",{staticClass:"text-green"},[e._v(">")]),e._v(" "),r("router-link",{attrs:{to:{path:"/merchantlist/editMerchantDetail",query:{merchantInfo:e.ruleForm.shop_id}}}},[r("span",{staticClass:"black"},[e._v("商户详情")])]),e._v(" "),r("span",{staticClass:"text-green"},[e._v(">")]),e._v(" "),r("span",{staticClass:"text-green"},[e._v("编辑服务信息")])],1)]),e._v(" "),r("div",{staticClass:"basic-info"},[e._m(0),e._v(" "),r("div",{staticClass:"info-board",style:{height:e.height}},[r("div",[e._v("登录授权数")]),e._v(" "),r("el-form",{ref:"ruleForm",staticClass:"demo-ruleForm",attrs:{model:e.ruleForm,rules:e.rules,"label-width":"0px"}},[r("el-form-item",{attrs:{label:"",prop:"pc_num"}},[r("div",{staticClass:"item-block"},[r("img",{attrs:{src:n("dWqg"),alt:""}}),e._v(" "),r("span",{staticClass:"item-name"},[e._v("商家运营平台")]),e._v(" "),r("el-input-number",{attrs:{min:0,label:"描述文字"},on:{change:e.handleChangePcNum},model:{value:e.ruleForm.pc_num,callback:function(t){e.$set(e.ruleForm,"pc_num",t)},expression:"ruleForm.pc_num"}})],1)]),e._v(" "),r("el-form-item",{attrs:{label:"",prop:"pad_num"}},[r("div",{staticClass:"item-block"},[r("img",{attrs:{src:n("8ve9"),alt:""}}),e._v(" "),r("span",{staticClass:"item-name"},[e._v("平板智能点餐机")]),e._v(" "),r("el-input-number",{attrs:{min:0,label:"描述文字"},on:{change:e.handleChangePadNum},model:{value:e.ruleForm.pad_num,callback:function(t){e.$set(e.ruleForm,"pad_num",t)},expression:"ruleForm.pad_num"}})],1)]),e._v(" "),r("el-form-item",{attrs:{label:"",prop:"cashier_num"}},[r("div",{staticClass:"item-block"},[r("img",{attrs:{src:n("QRCU"),alt:""}}),e._v(" "),r("span",{staticClass:"item-name"},[e._v("智能收银机")]),e._v(" "),r("el-input-number",{attrs:{min:0,label:"描述文字"},on:{change:e.handleChangeCounterNum},model:{value:e.ruleForm.cashier_num,callback:function(t){e.$set(e.ruleForm,"cashier_num",t)},expression:"ruleForm.cashier_num"}})],1)]),e._v(" "),r("el-form-item",{attrs:{label:"",prop:"app_num"}},[r("div",{staticClass:"item-block"},[r("img",{attrs:{src:n("ruh7"),alt:""}}),e._v(" "),r("span",{staticClass:"item-name"},[e._v("掌柜通")]),e._v(" "),r("el-input-number",{attrs:{min:0,label:"描述文字"},on:{change:e.handleChangeAppNum},model:{value:e.ruleForm.app_num,callback:function(t){e.$set(e.ruleForm,"app_num",t)},expression:"ruleForm.app_num"}})],1)]),e._v(" "),r("el-form-item",{attrs:{label:"",prop:"machine_num"}},[r("div",{staticClass:"item-block"},[r("img",{staticClass:"machine",attrs:{src:n("N5cs"),alt:""}}),e._v(" "),r("span",{staticClass:"item-name"},[e._v("自助点餐机")]),e._v(" "),r("el-input-number",{attrs:{min:0,label:"描述文字"},on:{change:e.handleChangeMacNum},model:{value:e.ruleForm.machine_num,callback:function(t){e.$set(e.ruleForm,"machine_num",t)},expression:"ruleForm.machine_num"}})],1)])],1)],1)]),e._v(" "),r("div",{staticClass:"btn-group"},[e.isCantClick?r("div",{staticClass:"save-btn cant-click",on:{click:e.pleaseFinish}},[e._v("保存")]):r("div",{staticClass:"save-btn",on:{click:e.saveInput}},[e._v(" 保存")]),e._v(" "),r("div",{staticClass:"cancel-btn",on:{click:e.cancelEdit}},[e._v("取消")])])])},staticRenderFns:[function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"title-bar"},[t("span",{staticClass:"text-strong"},[this._v("服务信息")])])}]};var i=function(e){n("q5AC"),n("TBdU")},s=n("C7Lr")(r.a,a,!1,i,"data-v-95fead30",null);t.default=s.exports},q5AC:function(e,t){}});