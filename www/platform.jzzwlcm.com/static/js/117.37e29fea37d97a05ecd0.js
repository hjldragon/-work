webpackJsonp([117],{"5Y/b":function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s=i("D9vu"),a=i("6nXL"),n=i("EuEE"),l=i("a2vD"),o={data:function(){return{shop_id:"",agent_name:"",from:"",from_salesman:"",agent_id:"",fromOptions:[],salesmanOptions:[],cancelDialogVisible:!1}},computed:{showBtn:function(){return!(!this.from||!this.from_salesman)}},created:function(){this.shop_id=this.$route.query.shopid||"",this.getShopinfo(),this.getPlatformerinfo()},mounted:function(){var t=this;this.$nextTick(function(){var e=t.$refs.adjustHeight;n.a.AdjustHeight(e)})},methods:{goDetail:function(){this.$router.push({path:"/indusmerchant/detail",query:{shopid:this.shop_id}})},getShopinfo:function(){var t=this,e={get_shop_info:1,shop_id:this.shop_id};Object(s.l)(e).then(function(e){if(0===e.ret){var i=e.data.shopinfo;t.agent_name=i.agent_name,t.from=i.from,t.from_salesman=i.from_salesman,t.agent_id=i.agent_id}else t.$slnotify({message:a.C.toString(e.ret),duration:1500})})},openCancelDialog:function(){this.cancelDialogVisible=!0},hideCancelDialog:function(){this.cancelDialogVisible=!1},save:function(){var t=this,e={save_shop_sign:1,ag_employee_id:l.a.getAgEmployeeid(),shop_id:this.shop_id,agent_type:1,agent_id:this.agent_id,from:this.from,from_salesman:this.from_salesman};Object(s.m)(e).then(function(e){0===e.ret?(t.$slnotify({message:"保存成功",duration:1500}),setTimeout(function(){t.goDetail()},1500)):t.$slnotify({message:a.C.toString(e.ret),duration:1500})})},getPlatformerinfo:function(){var t=this,e={get_ag_employee_info:1,ag_employee_id:l.a.getAgEmployeeid()};Object(s.a)(e).then(function(e){if(0===e.ret){var i=e.data.agemployee;t.salesmanOptions=i.salesman_record||[],t.fromOptions=i.from_record||[]}else t.$slnotify({message:a.C.toString(e.ret),duration:1500})})}}},r={render:function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{attrs:{id:"indusmerchant-agentinfo-editor"}},[i("div",{staticClass:"breadcrumb"},[i("el-breadcrumb",{attrs:{separator:">"}},[i("el-breadcrumb-item",{attrs:{to:{path:"/industryagent/merchant"}}},[t._v("商户列表")]),t._v(" "),i("el-breadcrumb-item",{nativeOn:{click:function(e){return t.goDetail(e)}}},[t._v("商户详情")]),t._v(" "),i("el-breadcrumb-item",[t._v("编辑签约信息")])],1)],1),t._v(" "),i("div",{staticClass:"editor-content"},[t._m(0),t._v(" "),i("div",{staticClass:"content"},[t._m(1),t._v(" "),i("div",{staticClass:"item clearfix"},[t._m(2),t._v(" "),i("div",{staticClass:"item-right fl"},[i("span",[t._v(t._s(t.agent_name))])])]),t._v(" "),i("div",{staticClass:"item clearfix"},[t._m(3),t._v(" "),i("div",{staticClass:"item-right fl"},[i("el-select",{attrs:{filterable:"","allow-create":"","default-first-option":"",placeholder:"请选择来源"},model:{value:t.from,callback:function(e){t.from=e},expression:"from"}},t._l(t.fromOptions,function(t){return i("el-option",{key:t,attrs:{label:t,value:t}})}))],1)]),t._v(" "),i("div",{staticClass:"item clearfix"},[t._m(4),t._v(" "),i("div",{staticClass:"item-right fl"},[i("el-select",{attrs:{filterable:"","allow-create":"","default-first-option":"",placeholder:"请选择所属销售"},model:{value:t.from_salesman,callback:function(e){t.from_salesman=e},expression:"from_salesman"}},t._l(t.salesmanOptions,function(t){return i("el-option",{key:t,attrs:{label:t,value:t}})}))],1)])]),t._v(" "),i("div",{ref:"adjustHeight"}),t._v(" "),i("div",{staticClass:"btn-group"},[t.showBtn?i("div",{staticClass:"create",on:{click:t.save}},[t._v("保存")]):i("div",{staticClass:"create-disabled"},[t._v("保存")]),t._v(" "),i("div",{staticClass:"cancel",on:{click:t.openCancelDialog}},[t._v("取消")])])]),t._v(" "),i("el-dialog",{staticClass:"nopadding cancel-dialog",attrs:{visible:t.cancelDialogVisible,width:"600px",top:"35vh",center:""},on:{"update:visible":function(e){t.cancelDialogVisible=e}}},[i("div",{staticClass:"cancel-content"},[i("span",[t._v("将放弃填写内容，确定取消本次编辑？")])]),t._v(" "),i("div",{staticClass:"btn-dialog",attrs:{slot:"footer"},slot:"footer"},[i("div",{staticClass:"btn-Dialogok",on:{click:t.goDetail}},[t._v("确认")]),t._v(" "),i("div",{staticClass:"btn-Dialogcancel",on:{click:t.hideCancelDialog}},[t._v("取消")])])])],1)},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"title clearfix"},[e("span",[this._v("签约信息")])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"item clearfix"},[e("div",{staticClass:"item-left fl"},[e("span",{staticClass:"sl-must"},[this._v("签约类型")])]),this._v(" "),e("div",{staticClass:"item-right fl"},[e("span",[this._v("行业代理")])])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"item-left fl"},[e("span",{staticClass:"sl-must"},[this._v("签约代理商")])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"item-left fl"},[e("span",{staticClass:"sl-must"},[this._v("来源")])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"item-left fl"},[e("span",{staticClass:"sl-must"},[this._v("所属销售")])])}]};var c=i("C7Lr")(o,r,!1,function(t){i("h9JP")},"data-v-639b8fac",null);e.default=c.exports},h9JP:function(t,e){}});