webpackJsonp([140],{UTT5:function(t,a,e){"use strict";Object.defineProperty(a,"__esModule",{value:!0});var n=e("4YfN"),s=e.n(n),i=e("U81z"),r=e("fK/j"),c=e("P9l9"),o=e("6nXL"),u=e("EuEE"),l=e("9rMa"),d={components:{ChartLine:i.a},data:function(){return{use_way:2,numList:[],dayArr:[],lineFormat:r.e,list:[]}},props:{agentType:{type:[Number,String],default:1}},watch:{agentType:function(t){this.getPayTop()}},computed:s()({},Object(l.d)({ACS:function(t){return t.perimission.sysPermis},PL_K:function(t){return t.perimission.PL_K}})),created:function(){this.getBoardOrder(),this.getPayTop()},methods:{change:function(t){this.use_way=t,this.ACS[this.PL_K.PL_AGENT_BOARD]?this.getBoardOrder():this.$slnotify({message:"操作权限不足"})},getBoardOrder:function(){var t=this,a={goods_order_stat:1,use_way:this.use_way};Object(c.Q)(a).then(function(a){t.ACS[t.PL_K.PL_AGENT_BOARD]&&a.data||(a.data={});var e=a.data.list||[];switch(t.use_way){case 1:case 2:var n=1==t.use_way?"week":"month",s=[],i=[],r=u.a.handlerAgentData(t.end_date,n);e.forEach(function(t){for(var a=t.day,e=0;e<r.length;e++)if(r[e].day==a){r[e].data[0]=t.region,r[e].data[1]=t.industry;break}}),r.forEach(function(t){s.push(t.day),i.push(t.data)}),t.dayArr=s,t.numList=i;break;case 3:s=[],i=[];e.forEach(function(t){if(t){s.unshift(t.mouth.slice(0,6));var a=[t.region||0,t.industry||0];i.unshift(a)}}),t.dayArr=s,t.numList=i}})},getPayTop:function(){var t=this,a={get_agent_pay:1,agent_type:this.agentType};Object(c.Q)(a).then(function(a){if(0===a.ret){var e=a.data.top||[];e=e.filter(function(t){return t.num}),t.list=e}else{if(-20011===a.ret)return;t.$slnotify({message:o.X.toString(a.ret)})}})}}},_={render:function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",{staticClass:"chartline-box"},[e("div",{staticClass:"chart-content"},[e("div",{staticClass:"chart-content-bg"},[t._m(0),t._v(" "),e("chart-line",{attrs:{numList:t.numList,dayArr:t.dayArr,type:t.lineFormat.operate}}),t._v(" "),e("div",{staticClass:"text"},[e("el-radio-group",{on:{change:t.change},model:{value:t.use_way,callback:function(a){t.use_way=a},expression:"use_way"}},[e("el-radio",{attrs:{label:1}},[t._v("周")]),t._v(" "),e("el-radio",{attrs:{label:2}},[t._v("月")])],1)],1)],1)]),t._v(" "),e("div",{staticClass:"turnover-top"},[e("div",{staticClass:"chart-content-bg"},[t._m(1),t._v(" "),e("div",{staticClass:"turn-content"},[e("ul",t._l(t.list,function(a,n){return e("li",{key:n,staticClass:"clearfix"},[e("span",{staticClass:"rank"},[t._v(t._s(1==String(n+1).length?"0"+(n+1):n+1))]),t._v(" "),e("span",{staticClass:"shop-name"},[t._v(t._s(a.city_name))]),t._v(" "),e("span",{staticClass:"turnover fr"},[t._v(t._s(a.num))])])}))])])])])},staticRenderFns:[function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"chart-line-titlerr"},[a("span",[this._v("订单管理")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"chart-line-titlerr"},[a("span",[this._v("代理商充值榜")])])}]};var h=e("C7Lr")(d,_,!1,function(t){e("d20i")},"data-v-2a05b57c",null);a.default=h.exports},d20i:function(t,a){}});