webpackJsonp([158],{BFYH:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s=n("4YfN"),a=n.n(s),i=n("cHcW"),c=n("9rMa"),u={components:{agentBusiness:i.a},data:function(){return{agent_id:"",upPath:"",apply:!1}},created:function(){this.upPath=this.$route.query.upPath,this.agent_id=this.$route.query.agent_id,this.apply=this.$route.query.apply},beforeRouteEnter:function(t,e,n){n(function(t){t.SET_SELECTMENU({selectMenu:"/official/agentmanage/certificate"})})},beforeRouteLeave:function(t,e,n){this.SET_SELECTMENU({selectMenu:""}),n()},methods:a()({},Object(c.c)(["SET_SELECTMENU"]),{close:function(){this.$router.push(this.upPath)},success:function(){this.$router.push(this.upPath)},goCertiList:function(){this.$router.push("/official/agentmanage/certificate")}})},r={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",[n("div",{staticClass:"nav-own-bar"},[n("span",{staticClass:"cant-click-nav"},[t._v("代理商管理")]),t._v("\n    >\n    "),n("span",{staticClass:"can-click-nav",on:{click:t.goCertiList}},[t._v("代理商认证")]),t._v("\n    >\n    "),n("span",{staticClass:"current-nav"},[t._v("代理商工商资料")])]),t._v(" "),n("agent-business",{attrs:{agent_id:t.agent_id,apply:t.apply},on:{close:t.close,success:t.success}})],1)},staticRenderFns:[]},o=n("C7Lr")(u,r,!1,null,null,null);e.default=o.exports}});