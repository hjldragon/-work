webpackJsonp([25,75,131],{"/V9z":function(t,e){},"16Jx":function(t,e){},"1TV1":function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i=a("6ROu"),n=a.n(i),s=a("EuEE"),l=a("6nXL"),o={props:{agentinfo:{type:Object}},data:function(){return{leftData:{creat_time:{label:"创建时间",value:"--",show:!0},agent_type:{label:"代理商类型",value:"--",show:!0},agent_name:{label:"代理商名称",value:"--",isAgentName:!0},agent_level:{label:"级别",value:"--",show:!0},agent_area:{label:"代理区域",value:"",show:!0}},centerData:{login_phone:{label:"登录手机号",value:"--",show:!0},resouvce:{label:"来源",value:"--",show:!0},saler:{label:"销售人员",value:"--",show:!0},attach_people:{label:"联系人",value:"--",show:!0},attach_phone:{label:"联系电话",value:"--",show:!0}},rightData:{email:{label:"邮箱",value:"--",show:!0},adress:{label:"地址",value:"--",isAdress:!0},agent_logo:{label:"logo",value:"",img:!0}},agent_logo:"",base_url:"./php"}},watch:{agentinfo:{handler:function(){this.init()},deep:!0}},methods:{init:function(){if(s.a.isEmpty(this.agentinfo.ctime)||(this.leftData.creat_time.value=n()(1e3*this.agentinfo.ctime).format("YYYY-MM-DD")),!s.a.isEmpty(this.agentinfo.agent_type)){var t=this.agentinfo.agent_type;this.leftData.agent_type.value=l.d.toString(t)}this.leftData.agent_name.value=this.agentinfo.agent_name||"",s.a.isEmpty(this.agentinfo.agent_level)||(this.leftData.agent_level.value=l.c.toString(this.agentinfo.agent_level)+"代理"),this.leftData.agent_area.value=""+this.agentinfo.agent_province,this.agentinfo.agent_city&&(this.leftData.agent_area.value=this.agentinfo.agent_province+"-"+this.agentinfo.agent_city),this.agentinfo.agent_area&&(this.leftData.agent_area.value=this.agentinfo.agent_province+"-"+this.agentinfo.agent_city+"-"+this.agentinfo.agent_area),this.centerData.login_phone.value=this.agentinfo.phone,this.agentinfo.from?this.centerData.resouvce.value=this.agentinfo.from:this.centerData.resouvce.value="--",this.centerData.saler.value=this.agentinfo.real_name,this.centerData.attach_people.value=this.agentinfo.relation_name,this.centerData.attach_phone.value=this.agentinfo.telephone,this.rightData.email.value=this.agentinfo.email||"--",this.rightData.adress.value=this.agentinfo.agent_province+this.agentinfo.agent_city+this.agentinfo.agent_area+this.agentinfo.address,this.rightData.agent_logo.value=this.agentinfo.agent_logo}}},r={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{attrs:{id:"agnet-detail-baseinfo"}},[a("div",{staticClass:"content clearfix"},[a("div",{staticClass:"item-content-left fl"},t._l(t.leftData,function(e,i){return a("div",{key:i,staticClass:"item clearfix"},[a("div",{staticClass:"item-left fl"},[a("span",[t._v(t._s(e.label))])]),t._v(" "),a("div",{staticClass:"item-right fl"},[a("span",{staticClass:"width-limit",class:{"pwd-item":"login_pwd"===i}},[t._v(t._s(e.value))]),t._v(" "),a("span",{directives:[{name:"show",rawName:"v-show",value:e.isAgentName,expression:"item.isAgentName"}],staticClass:"text-green-blue"},[t._v("(已认证)")])])])})),t._v(" "),a("div",{staticClass:"item-content-left fl"},t._l(t.centerData,function(e,i){return a("div",{key:i,staticClass:"item clearfix"},[a("div",{staticClass:"item-left fl"},[a("span",[t._v(t._s(e.label))])]),t._v(" "),a("div",{staticClass:"item-right fl"},[a("span",{class:{"pwd-item":"login_pwd"===i}},[t._v(t._s(e.value))])])])})),t._v(" "),a("div",{staticClass:"item-content-right fl"},t._l(t.rightData,function(e,i){return a("div",{key:i,staticClass:"item clearfix"},[a("div",{staticClass:"item-left fl"},[a("span",[t._v(t._s(e.label))])]),t._v(" "),a("div",{staticClass:"item-right fl"},[e.img?a("img",{attrs:{src:t.base_url+"/img_get.php?img=1&imgname="+e.value,alt:""}}):a("span",{class:{"address-style":e.isAdress}},[t._v(t._s(e.value))])])])}))])])},staticRenderFns:[]};var c=a("C7Lr")(o,r,!1,function(t){a("16Jx")},"data-v-426dd285",null);e.default=c.exports},"3uqo":function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i=a("4YfN"),n=a.n(i),s=a("9rMa"),l=a("1TV1"),o=a("CFmy"),r=a("P9l9"),c=a("6nXL"),v={components:{agentBase:l.default,agentDetailTable:o.default},data:function(){return{agentinfo:{}}},created:function(){this.getAgentInfo()},beforeRouteEnter:function(t,e,a){a(function(t){t.SET_SELECTMENU({selectMenu:"/official/agentmanage/list"})})},beforeRouteLeave:function(t,e,a){this.SET_SELECTMENU({selectMenu:""}),a()},methods:n()({},Object(s.c)(["SET_SELECTMENU"]),{getAgentInfo:function(){var t=this,e={agent_info:1,platform:1,agent_id:this.$route.query.agent_id};Object(r.n)(e).then(function(e){0===e.ret?t.agentinfo=e.data.agent_info:t.$slnotify({message:c.X.toString(e.ret)})})},goAgentList:function(){this.$router.push("/official/agentmanage/list")}})},f={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"agent-detail-official"},[a("div",{staticClass:"nav-own-bar"},[a("span",{staticClass:"cant-click-nav"},[t._v("代理商管理")]),t._v("\n      >\n      "),a("span",{staticClass:"can-click-nav",on:{click:t.goAgentList}},[t._v("代理商列表")]),t._v("\n      >\n      "),a("span",{staticClass:"current-nav"},[t._v("代理商详情")])]),t._v(" "),a("div",{staticClass:"official-title-bar"},[t._v("基本信息")]),t._v(" "),a("agent-base",{attrs:{agentinfo:t.agentinfo}}),t._v(" "),a("agent-detail-table")],1)},staticRenderFns:[]};var g=a("C7Lr")(v,f,!1,function(t){a("/V9z")},"data-v-62a8619f",null);e.default=g.exports},CFmy:function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i=a("Zlgw"),n=a("Htth"),s=a("CJBq"),l={components:{merchantInfoTable:i.default,rechargeRecord:n.default,orderRecord:s.default},data:function(){return{activeName:"first"}},methods:{handleClick:function(t){t.name}}},o={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"agent-detail-table"},[a("el-tabs",{staticClass:"official-title-bar",on:{"tab-click":t.handleClick},model:{value:t.activeName,callback:function(e){t.activeName=e},expression:"activeName"}},[a("el-tab-pane",{attrs:{name:"first"}},[a("span",{staticClass:"label-title",attrs:{slot:"label"},slot:"label"},[t._v("商户信息")]),t._v(" "),a("div",{staticClass:"wait-certicifate"})]),t._v(" "),a("el-tab-pane",{attrs:{name:"second"}},[a("span",{staticClass:"label-title",attrs:{slot:"label"},slot:"label"},[t._v("充值记录")]),t._v(" "),a("div",{staticClass:"suc-certicifate"})]),t._v(" "),a("el-tab-pane",{attrs:{name:"third"}},[a("span",{staticClass:"label-title",attrs:{slot:"label"},slot:"label"},[t._v("订单记录")]),t._v(" "),a("div",{staticClass:"fail-certicifate"})])],1),t._v(" "),a("merchant-info-table",{directives:[{name:"show",rawName:"v-show",value:"first"===t.activeName,expression:"activeName==='first'"}]}),t._v(" "),a("recharge-record",{directives:[{name:"show",rawName:"v-show",value:"second"===t.activeName,expression:"activeName==='second'"}]}),t._v(" "),a("order-record",{directives:[{name:"show",rawName:"v-show",value:"third"===t.activeName,expression:"activeName==='third'"}]})],1)},staticRenderFns:[]};var r=a("C7Lr")(l,o,!1,function(t){a("mtqC"),a("TVia")},"data-v-743ffc86",null);e.default=r.exports},TVia:function(t,e){},mtqC:function(t,e){}});