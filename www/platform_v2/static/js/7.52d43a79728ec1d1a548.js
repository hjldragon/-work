webpackJsonp([7,77,94,108,110,117],{"/wbk":function(t,a,e){"use strict";Object.defineProperty(a,"__esModule",{value:!0});var n={props:{agent_all:{type:Number,default:0},guild_total:{type:Number,default:0},area_total:{type:Number,default:0},pay_money:{type:Number,default:0},order_all:{type:Number,default:0},ys_agent:{type:[Number,String],default:0},end_date:{type:[Number,String],default:function(){return""}},hideShow:{type:[Boolean],default:function(){return!1}}},computed:{agent_all_data:function(){return{icon:e("HInx"),num:this.agent_all,title:"全部代理商",key:"1"}},guild_total_data:function(){return{icon:e("y3sc"),num:this.guild_total,title:"行业代理商",key:"2"}},area_total_data:function(){return{icon:e("Rjo3"),num:this.area_total,title:"区域代理商",key:"3"}},pay_money_data:function(){return{icon:e("013G"),num:this.pay_money,title:"充值金额",key:"4"}},order_all_data:function(){return{icon:e("ZmhC"),num:this.order_all,title:"总订单数",key:"5"}},ys_agent_data:function(){return{icon:e("zkNq"),num:this.ys_agent,title:"昨日新增代理商",key:"6"}},shopData:function(){return[this.agent_all_data,this.guild_total_data,this.area_total_data,this.pay_money_data,this.order_all_data,this.ys_agent_data]}}},i={render:function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",{staticClass:"chart-card"},[t.hideShow?t._e():e("div",{staticClass:"change-titlerr clearfix"},[e("span",[t._v("代理商总数据")]),t._v(" "),e("span",{staticClass:"fr now-text"},[t._v("数据截止日期："+t._s(t.end_date))])]),t._v(" "),e("div",{staticClass:"card-content clearfix"},t._l(t.shopData,function(a){return e("div",{key:a.key,staticClass:"card-item fl"},[e("div",{staticClass:"chart-card-item"},[e("div",{staticClass:"icon"},[e("img",{attrs:{src:a.icon,alt:"card-icon"}})]),t._v(" "),e("div",{staticClass:"num",class:["color-g-"+a.key]},[e("span",[t._v(t._s(a.num))])]),t._v(" "),e("div",{staticClass:"title"},[e("span",[t._v(t._s(a.title))])])])])}))])},staticRenderFns:[]};var s=e("C7Lr")(n,i,!1,function(t){e("Z81n")},"data-v-066c0f2e",null);a.default=s.exports},"0lnh":function(t,a){},"2z9l":function(t,a,e){"use strict";Object.defineProperty(a,"__esModule",{value:!0});var n=e("4YfN"),i=e.n(n),s=e("U81z"),r=e("fK/j"),c=e("P9l9"),o=e("6nXL"),l=e("EuEE"),d=e("9rMa"),u={components:{ChartLine:s.a},data:function(){return{use_way:2,numList:[],dayArr:[],lineFormat:r.b,agent_type:1,yName:"单位（元）"}},created:function(){this.getBoardOrder()},computed:i()({},Object(d.d)({ACS:function(t){return t.perimission.sysPermis},PL_K:function(t){return t.perimission.PL_K}})),methods:{change:function(t){this.use_way=t,this.ACS[this.PL_K.PL_AGENT_BOARD]?this.getBoardOrder():this.$slnotify({message:"操作权限不足"})},changeAgentType:function(t){this.agent_type=t,this.ACS[this.PL_K.PL_AGENT_BOARD]?this.getBoardOrder():this.$slnotify({message:"操作权限不足"})},getBoardOrder:function(){var t=this,a={agent_pay_date:1,use_way:this.use_way,agent_type:this.agent_type};Object(c.N)(a).then(function(a){if(0===a.ret||a.ret===o.N.USER_PERMISSION_ERR){a.data||(a.data={});var e=a.data.list||[];switch(t.use_way){case 1:case 2:var n=1==t.use_way?"week":"month",i=[],s=[],r=l.a.handlerAgentData(t.end_date,n);e.forEach(function(t){for(var a=t.day,e=0;e<r.length;e++)if(r[e].day==a){r[e].data[0]=t.money;break}}),r.forEach(function(t){i.push(t.day),s.push(t.data)}),t.dayArr=i,t.numList=s;break;case 3:i=[],s=[];e.forEach(function(t){if(t){i.unshift(t.mouth.slice(0,6));var a=[t.money||0,0];s.unshift(a)}}),t.dayArr=i,t.numList=s}}else{if(-20011===a.ret)return;t.$slnotify({message:o.N.toString(a.ret)})}})}}},_={render:function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",{staticClass:"chartline-order"},[t._m(0),t._v(" "),e("div",{staticClass:"chart-content"},[e("chart-line",{attrs:{yName:t.yName,numList:t.numList,dayArr:t.dayArr,type:t.lineFormat.operateP}}),t._v(" "),e("div",{staticClass:"text"},[e("el-radio-group",{on:{change:t.change},model:{value:t.use_way,callback:function(a){t.use_way=a},expression:"use_way"}},[e("el-radio",{attrs:{label:1}},[t._v("周")]),t._v(" "),e("el-radio",{attrs:{label:2}},[t._v("月")]),t._v(" "),e("el-radio",{attrs:{label:3}},[t._v("年")])],1)],1),t._v(" "),e("div",{staticClass:"agent-type"},[e("span",{staticClass:"map-btn map-btn-padd",class:[2==t.agent_type&&"active"],on:{click:function(a){t.changeAgentType(2)}}},[t._v("行业")]),t._v(" "),e("span",{staticClass:"map-btn map-btn-padd",class:[1==t.agent_type&&"active"],on:{click:function(a){t.changeAgentType(1)}}},[t._v("区域")])])],1)])},staticRenderFns:[function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"change-titlerr"},[a("span",[this._v("代理商充值数据")])])}]};var h=e("C7Lr")(u,_,!1,function(t){e("xOsL")},"data-v-4366f450",null);a.default=h.exports},Fb4K:function(t,a){},UTT5:function(t,a,e){"use strict";Object.defineProperty(a,"__esModule",{value:!0});var n=e("4YfN"),i=e.n(n),s=e("U81z"),r=e("fK/j"),c=e("P9l9"),o=e("6nXL"),l=e("EuEE"),d=e("9rMa"),u={components:{ChartLine:s.a},data:function(){return{use_way:2,numList:[],dayArr:[],lineFormat:r.b,list:[]}},props:{agentType:{type:[Number,String],default:1}},watch:{agentType:function(t){this.getPayTop()}},computed:i()({},Object(d.d)({ACS:function(t){return t.perimission.sysPermis},PL_K:function(t){return t.perimission.PL_K}})),created:function(){this.getBoardOrder(),this.getPayTop()},methods:{change:function(t){this.use_way=t,this.ACS[this.PL_K.PL_AGENT_BOARD]?this.getBoardOrder():this.$slnotify({message:"操作权限不足"})},getBoardOrder:function(){var t=this,a={goods_order_stat:1,use_way:this.use_way};Object(c.N)(a).then(function(a){t.ACS[t.PL_K.PL_AGENT_BOARD]&&a.data||(a.data={});var e=a.data.list||[];switch(t.use_way){case 1:case 2:var n=1==t.use_way?"week":"month",i=[],s=[],r=l.a.handlerAgentData(t.end_date,n);e.forEach(function(t){for(var a=t.day,e=0;e<r.length;e++)if(r[e].day==a){r[e].data[0]=t.region,r[e].data[1]=t.industry;break}}),r.forEach(function(t){i.push(t.day),s.push(t.data)}),t.dayArr=i,t.numList=s;break;case 3:i=[],s=[];e.forEach(function(t){if(t){i.unshift(t.mouth.slice(0,6));var a=[t.region||0,t.industry||0];s.unshift(a)}}),t.dayArr=i,t.numList=s}})},getPayTop:function(){var t=this,a={get_agent_pay:1,agent_type:this.agentType};Object(c.N)(a).then(function(a){if(0===a.ret){var e=a.data.top||[];e=e.filter(function(t){return t.num}),t.list=e}else{if(-20011===a.ret)return;t.$slnotify({message:o.N.toString(a.ret)})}})}}},_={render:function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",{staticClass:"chartline-box"},[e("div",{staticClass:"chart-content"},[e("div",{staticClass:"chart-content-bg"},[t._m(0),t._v(" "),e("chart-line",{attrs:{numList:t.numList,dayArr:t.dayArr,type:t.lineFormat.operate}}),t._v(" "),e("div",{staticClass:"text"},[e("el-radio-group",{on:{change:t.change},model:{value:t.use_way,callback:function(a){t.use_way=a},expression:"use_way"}},[e("el-radio",{attrs:{label:1}},[t._v("周")]),t._v(" "),e("el-radio",{attrs:{label:2}},[t._v("月")])],1)],1)],1)]),t._v(" "),e("div",{staticClass:"turnover-top"},[e("div",{staticClass:"chart-content-bg"},[t._m(1),t._v(" "),e("div",{staticClass:"turn-content"},[e("ul",t._l(t.list,function(a,n){return e("li",{key:n,staticClass:"clearfix"},[e("span",{staticClass:"rank"},[t._v(t._s(1==String(n+1).length?"0"+(n+1):n+1))]),t._v(" "),e("span",{staticClass:"shop-name"},[t._v(t._s(a.city_name))]),t._v(" "),e("span",{staticClass:"turnover fr"},[t._v(t._s(a.num))])])}))])])])])},staticRenderFns:[function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"chart-line-titlerr"},[a("span",[this._v("订单管理")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"chart-line-titlerr"},[a("span",[this._v("代理商充值榜")])])}]};var h=e("C7Lr")(u,_,!1,function(t){e("qrNm")},"data-v-2a05b57c",null);a.default=h.exports},Z81n:function(t,a){},e3rS:function(t,a,e){"use strict";Object.defineProperty(a,"__esModule",{value:!0});var n=e("4YfN"),i=e.n(n),s=e("myvb"),r=e("P9l9"),c=e("6nXL"),o=e("9rMa"),l=e("6ROu"),d=e.n(l),u={components:{ChartMap:s.a},data:function(){return{numList:[],agent_type:1,rankArr:[],end_date:d()().format("YYYY-MM-DD ")}},created:function(){this.getMap()},computed:i()({title:function(){return 1==this.agent_type?"区域代理Top10":"行业代理Top10"}},Object(o.d)({ACS:function(t){return t.perimission.sysPermis},PL_K:function(t){return t.perimission.PL_K}})),methods:{getMap:function(){var t=this;if(this.ACS[this.PL_K.PL_AGENT_BOARD]){var a={agent_board:1,get_agent_map:1,agent_type:this.agent_type};Object(r.N)(a).then(function(a){if(0===a.ret)t.numList=a.data.list?a.data.list:[];else{if(-20011===a.ret)return;t.$slnotify({message:c.N.toString(a.ret)})}})}},change:function(t){this.agent_type=t,this.getMap(),this.$emit("change",t)}}},_={render:function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",{staticClass:"chartline-order"},[e("div",{staticClass:"change-titlerr"},[e("span",[t._v("代理商分布及充值概况")]),t._v(" "),e("span",{staticClass:"fr now-text"},[t._v("数据截止日期："+t._s(t.end_date))])]),t._v(" "),e("chart-map",{attrs:{numList:t.numList}}),t._v(" "),e("div",{staticClass:"text"},[e("span",{staticClass:"map-btn map-btn-padd",class:[2==t.agent_type&&"active"],on:{click:function(a){t.change(2)}}},[t._v("行业")]),t._v(" "),e("span",{staticClass:"map-btn map-btn-padd",class:[1==t.agent_type&&"active"],on:{click:function(a){t.change(1)}}},[t._v("区域")])])],1)},staticRenderFns:[]};var h=e("C7Lr")(u,_,!1,function(t){e("hkWM")},"data-v-6fdfaf39",null);a.default=h.exports},hkWM:function(t,a){},jtkB:function(t,a,e){"use strict";Object.defineProperty(a,"__esModule",{value:!0});var n=e("4YfN"),i=e.n(n),s=e("/wbk"),r=e("zP2D"),c=e("e3rS"),o=e("UTT5"),l=e("2z9l"),d=e("6nXL"),u=e("a2vD"),_=e("P9l9"),h=e("6ROu"),f=e.n(h),v=e("9rMa"),p={components:{ChartCard:s.default,ChartPie:r.default,AgentMap:c.default,ChartLineOrder:o.default,ChartLine:l.default},data:function(){return{agent_id:u.a.getAgentid(),agent_all:0,guild_total:0,area_total:0,pay_money:0,order_all:0,ys_agent:0,end_date:f()().format("YYYY-MM-DD "),agentType:1}},created:function(){this.getBoard()},computed:i()({},Object(v.d)({ACS:function(t){return t.perimission.sysPermis},PL_K:function(t){return t.perimission.PL_K}})),methods:{getBoard:function(){var t=this;Object(_.N)({get_agent_board:1}).then(function(a){if(0===a.ret||a.ret===d.N.USER_PERMISSION_ERR)a.data||(a.data=0),a.data.agent_all&&(t.agent_all=a.data.agent_all),a.data.guild_total&&(t.guild_total=a.data.guild_total),a.data.area_total&&(t.area_total=a.data.area_total),a.data.pay_money&&(t.pay_money=a.data.pay_money),a.data.order_all&&(t.order_all=a.data.order_all),a.data.ys_agent&&(t.ys_agent=a.data.ys_agent),a.data.end_date&&(t.end_date=a.data.end_date);else{if(-20011===a.ret)return;t.$slnotify({message:d.N.toString(a.ret)})}})},change:function(t){if(this.agentType=t,!this.ACS[this.PL_K.PL_AGENT_BOARD])return this.$slnotify({message:"操作权限不足"})}}},m={render:function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",[e("chart-card",{attrs:{agent_all:t.agent_all,guild_total:t.guild_total,area_total:t.area_total,pay_money:t.pay_money,order_all:t.order_all,ys_agent:t.ys_agent,end_date:t.end_date}}),t._v(" "),e("div",{staticClass:"bg"}),t._v(" "),e("agent-map",{on:{change:t.change}}),t._v(" "),e("chart-line-order",{attrs:{agentType:t.agentType}}),t._v(" "),e("div",{staticClass:"bg"}),t._v(" "),e("chart-line"),t._v(" "),e("div",{staticClass:"bg"}),t._v(" "),e("chart-pie")],1)},staticRenderFns:[]};var g=e("C7Lr")(p,m,!1,function(t){e("0lnh")},"data-v-6233b526",null);a.default=g.exports},qrNm:function(t,a){},xOsL:function(t,a){},zP2D:function(t,a,e){"use strict";Object.defineProperty(a,"__esModule",{value:!0});var n=e("PZPB"),i=e("P9l9"),s=e("6nXL"),r=(e("a2vD"),{components:{ChartPieCom:n.default},data:function(){return{one:{},two:{},three:{},four:{}}},created:function(){this.getShopTop()},methods:{getShopTop:function(){var t=this;Object(i.N)({agent_shop_num_top:1}).then(function(a){if(0===a.ret||a.ret===s.N.USER_PERMISSION_ERR){a.data||(a.data={});var e={one:{title:"区域代理商Top10",data:[]},two:{title:"行业代理商Top10",data:[]}};a.data.area_top&&a.data.area_top.forEach(function(t){var a={};a.value=t.num,a.name=t.agent_name,e.one.data.push(a)}),0===e.one.data.length&&e.one.data.push({name:"无数据",value:"100"}),t.one=e.one,a.data.guild_top&&a.data.guild_top.forEach(function(t){var a={};a.value=t.num,a.name=t.agent_name,e.two.data.push(a)}),0===e.two.data.length&&e.two.data.push({name:"无数据",value:"100"}),t.two=e.two}else{if(-20011===a.ret)return;t.$slnotify({message:s.N.toString(a.ret)})}});Object(i.N)({agent_from_top:1}).then(function(a){if(0===a.ret||a.ret===s.N.USER_PERMISSION_ERR){a.data||(a.data={});var e={three:{title:"区域代理商Top10",data:[]},four:{title:"行业代理商Top10",data:[]}};a.data.area_top&&a.data.area_top.forEach(function(t){var a={};a.value=t.num,a.name=t.pl_name,e.three.data.push(a)}),0===e.three.data.length&&e.three.data.push({name:"无数据",value:"100"}),t.three=e.three,a.data.guild_top&&a.data.guild_top.forEach(function(t){var a={};a.value=t.num,a.name=t.pl_name,e.four.data.push(a)}),0===e.four.data.length&&e.four.data.push({name:"无数据",value:"100"}),t.four=e.four}else{if(-20011===a.ret)return;t.$slnotify({message:s.N.toString(a.ret)})}})}}}),c={render:function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",{staticClass:"ecology-pie"},[e("div",{staticClass:"ecology-item"},[t._m(0),t._v(" "),e("div",{staticClass:"chart-content clearfix"},[e("div",{staticClass:"ecology-title"},[t._v("代理商商户数")]),t._v(" "),e("div",{staticClass:"pie-item fl"},[e("chart-pie-com",{attrs:{data:t.one}})],1),t._v(" "),e("div",{staticClass:"pie-item fl"},[e("chart-pie-com",{attrs:{data:t.two}})],1)])]),t._v(" "),e("div",{staticClass:"ecology-item margin-14"},[t._m(1),t._v(" "),e("div",{staticClass:"chart-content clearfix"},[e("div",{staticClass:"ecology-title"},[t._v("销售人员签约排行榜")]),t._v(" "),e("div",{staticClass:"pie-item fl"},[e("chart-pie-com",{attrs:{data:t.three}})],1),t._v(" "),e("div",{staticClass:"pie-item fl"},[e("chart-pie-com",{attrs:{data:t.four}})],1)])])])},staticRenderFns:[function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"change-titlerr"},[a("span",[this._v("商户排行榜")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"change-titlerr"},[a("span",[this._v("代理商商户数")])])}]};var o=e("C7Lr")(r,c,!1,function(t){e("Fb4K")},"data-v-4d15143c",null);a.default=o.exports},zkNq:function(t,a){t.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACUAAAAkCAYAAAAOwvOmAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjEyQzBBRDkzNkQzRDExRThBMzkwQ0MyRTYzOTdCQTA1IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjEyQzBBRDk0NkQzRDExRThBMzkwQ0MyRTYzOTdCQTA1Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6MTJDMEFEOTE2RDNEMTFFOEEzOTBDQzJFNjM5N0JBMDUiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6MTJDMEFEOTI2RDNEMTFFOEEzOTBDQzJFNjM5N0JBMDUiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5QiemlAAADXUlEQVR42qSYPW8UMRCG5/UtFz4LEGUqREdPSUFFRZEOmhRUFAhRRFARCVEckYDUSNR00PALgALxG/gHUEZBoNyZ2S97bI93vZuVRrN7e2fPzTx+Z704vvliSUQv2e6xbdPoAbKEj5bMLgFH7CkwLLpz9uh8c976TXS9/e1uMoNhW7HtlQXkjh227xzg9TpIZ4AL3Hu4a0vhbZsZvA5qd0Iw3UDNqDf4/Af7OzKLaUDtb2wQIMS1HtQVmnR0AzVZwWUe/DP7pz5NPmu5YGyUQS2o6QGFgy148hXbB57sQh+ITcqHqFx9cMosDLqdElQ7If8XxIAbD7gD3p9vNOCb84rW2Krtz5q2Pq2xfFjN5EmBNMcTBniC/GPn+PP7XLwTcwqeFDbCUsXBpAGF1mYfO+aUPHWTe44aD10OrJL1vuzkM3axyi6B0RKW6FPrbPC9Pivt+bUvt+evvmGeaCZPej6qAWEd4SktURzQOE+Gft762qy+Tb0Cacl+WZopZORBsNEbKFkAOk/U8ESOp34lYpp4pq3BK/d0niTcvfa13szjSQkuyeg4T21mEaw+Ks+U5EmWAkpAen+ziY75btDpUycRRUFBVe6ovz3j60s84L7W/a3Kl84TTWFqiCe+95rdEX+w8relUqf6ZCHV3PNUzFTKT8LTSef/afxk+x08R/K+KcsQiVU2hSdSGXQsiaeNpoyYWL500kivoCl0DnjJUpjJAp1ChicKmEiyluHJJjz5MsrAJq4+8AYDZ9xM3svywDafOzvL18/HeLIie2ZcNAOeDj3UQzwFY/xl99ZfeykgIZxUDnqi0I/YFkl5kXImysH7SjyOufHZkcreJr0q48kdb2rzTDjeIHiyMU8U8CSUvLuOpaMwUxh4TIGWHXVLFYIdNmNbArrC00ADlm1HGSPRNtmI+89MyZMnMvqEAZ4wkrFwW+ayB1nubFAqT5E+BfpVdTwt1YCyPGmtiEp1apSnJ2znedA9Kx7wpPCmQmkCluQzWzVv05ksggP+3sFYO/KZMyKbJmnyZv6mMyzldJ6MylMf1K8SnqzYMtnkPjJ/QMhBIU91PPU33hXzhLx+2dxLMZDSnMOnhKjhH9ZM7XdsPWC7WvYSQ3vFM/QOKtiWi/sBT7/Z3rO9+i/AALQwleU/MJoHAAAAAElFTkSuQmCC"}});