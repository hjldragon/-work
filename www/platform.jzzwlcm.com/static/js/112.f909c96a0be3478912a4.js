webpackJsonp([112],{LkFj:function(t,s,e){"use strict";Object.defineProperty(s,"__esModule",{value:!0});var a=e("EuEE"),i=e("6nXL"),n={props:{agentinfo:{type:Object}},data:function(){return{leftData:{agent_type:{label:"代理商类型",value:"",show:!0},agent_name:{label:"代理商名称",value:"",show:!0},agent_level:{label:"代理商级别",value:"",show:!0},login_phone:{label:"登录手机号",value:"",show:!0}},rightData:{attach_people:{label:"联系人",value:"",show:!0},attach_phone:{label:"联系号码",value:"",show:!0},email:{label:"邮箱",value:"",show:!0},adress:{label:"地址",value:"",show:!0}},businessStatus:0,businessStr:""}},computed:{isShowBtn:function(){return this.businessStatus===i.b.NO||this.businessStatus===i.b.FAIL},bussinessClass:function(){return this.businessStatus===i.b.NO?"business-blue":this.businessStatus===i.b.DURING?"business-purple":this.businessStatus===i.b.SUC?"business-green":this.businessStatus===i.b.FAIL?"business-red":void 0}},watch:{agentinfo:{handler:function(){this.init()},deep:!0}},methods:{init:function(){if(this.businessStatus=this.agentinfo.business_status,this.businessStr="（"+i.b.toString(this.businessStatus)+"）",!a.a.isEmpty(this.agentinfo.agent_type)){var t=this.agentinfo.agent_type;this.leftData.agent_type.value=i.f.toString(t)}this.leftData.agent_name.value=this.agentinfo.agent_name||"",a.a.isEmpty(this.agentinfo.agent_level)||(this.leftData.agent_level.value=i.d.toString(this.agentinfo.agent_level)),this.leftData.login_phone.value=this.agentinfo.phone,this.rightData.attach_people.value=this.agentinfo.real_name,this.rightData.attach_phone.value=this.agentinfo.telephone,this.rightData.email.value=this.agentinfo.email||"--",this.rightData.adress.value=this.agentinfo.address},goEditor:function(){this.$router.push("/indusagentinfo/editorbase")}}},l={render:function(){var t=this,s=t.$createElement,e=t._self._c||s;return e("div",{attrs:{id:"industry-agent-baseinfo"}},[e("div",{staticClass:"title clearfix"},[e("span",[t._v("基础信息")]),t._v(" "),e("div",{staticClass:"btn-group fr"},[t.isShowBtn?t._e():e("div",{staticClass:"btn-edit-gray"},[t._v("编辑")]),t._v(" "),t.isShowBtn?e("div",{staticClass:"btn-edit",on:{click:t.goEditor}},[t._v("编辑")]):t._e()])]),t._v(" "),e("div",{staticClass:"content clearfix"},[e("div",{staticClass:"item-content-left fl"},t._l(t.leftData,function(s,a){return s.show?e("div",{staticClass:"item clearfix"},[e("div",{staticClass:"item-left fl"},[e("span",[t._v(t._s(s.label))])]),t._v(" "),e("div",{staticClass:"item-right fl"},[e("span",[t._v(t._s(s.value))]),t._v(" "),"代理商名称"===s.label?e("span",{class:t.bussinessClass},[t._v(t._s(t.businessStr))]):t._e()])]):t._e()})),t._v(" "),e("div",{staticClass:"item-content-right fl"},t._l(t.rightData,function(s,a){return s.show?e("div",{staticClass:"item clearfix"},[e("div",{staticClass:"item-left fl"},[e("span",[t._v(t._s(s.label))])]),t._v(" "),e("div",{staticClass:"item-right fl"},[e("span",[t._v(t._s(s.value))])])]):t._e()}))])])},staticRenderFns:[]};var u=e("C7Lr")(n,l,!1,function(t){e("uFNH")},"data-v-7bd58131",null);s.default=u.exports},uFNH:function(t,s){}});