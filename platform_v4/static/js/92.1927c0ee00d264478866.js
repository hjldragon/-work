webpackJsonp([92],{"+2ry":function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s=i("4YfN"),a=i.n(s),n=i("6ROu"),l=i.n(n),r=i("9rMa"),o=i("r2UX"),c=i("CW5y"),d=i("EuEE"),p=i("a2vD"),u=i("6nXL"),h=i("P9l9"),g={components:{EmptyTable:o.a},data:function(){return{pickerOptions:{shortcuts:[{text:"最近一周",onClick:function(t){var e=new Date,i=new Date;i.setTime(i.getTime()-6048e5),t.$emit("pick",[i,e])}},{text:"最近一个月",onClick:function(t){var e=new Date,i=new Date;i.setTime(i.getTime()-2592e6),t.$emit("pick",[i,e])}},{text:"最近三个月",onClick:function(t){var e=new Date,i=new Date;i.setTime(i.getTime()-7776e6),t.$emit("pick",[i,e])}}]},recverTime:null,list:[],emptyType:1,multipleSelection:[],total:100,listQuery:{page:1,limit:10},isSort:!1,sortLabel:"",sortOrder:1,detOperateDialog:!1,TipDialog:!1,TipText:"",TipImg:""}},computed:a()({isListEmpty:function(){return 0===this.list.length}},Object(r.d)({ACS:function(t){return t.perimission.sysPermis},AG_K:function(t){return t.perimission.AG_K}})),created:function(){this.listQuery.limit=c.a.get(this.$route),this.getList()},mounted:function(){var t=this;this.$nextTick(function(){t.$refs.table.$refs.bodyWrapper.style.minHeight="460px"})},methods:{getList:function(){var t=this;if(this.ACS[this.AG_K.NEW_SEEK]){var e={get_agent_news_list:1,agent_id:p.a.getAgentid(),page_size:this.listQuery.limit,page_no:this.listQuery.page};if(!d.a.isEmpty(this.recverTime)){var i=this.recverTime[0],s=this.recverTime[1];e.begin_time=parseInt(l()(i).format("X")),e.end_time=parseInt(l()(s).format("X"))}this.isSort&&(e.sort_name=this.sortLabel,e.desc=this.sortOrder),Object(h.S)(e).then(function(e){0===e.ret?(t.list=e.data.list||[],t.total=e.data.total,t.list=t.list.map(function(t){return t.is_ready===u.w.YES?t.is_read=!0:t.is_read=!1,t})):t.$slnotify({message:u.X.toString(e.ret)})})}else this.$slnotify({message:"操作权限不足"})},multiRead:function(){var t=this;if(this.ACS[this.AG_K.ADD_NEW]){if(0===this.multipleSelection.length)return void this.$slnotify({message:"您还没有勾选任何选项！",duration:1500});var e=this.multipleSelection.map(function(t){return t.news_id}),i={agent_ready_news:1,agent_id:p.a.getAgentid(),news_id_list:e,read:1,type:0};Object(h.T)(i).then(function(e){0===e.ret?(t.openTilSuc("已全部设置为已读！"),t.getList()):t.$slnotify({message:u.X.toString(e.ret)})})}else this.$slnotify({message:"操作权限不足"})},multiDel:function(){var t=this;if(this.ACS[this.AG_K.DEL_NEW]){var e=this.multipleSelection.map(function(t){return t.news_id}),i={agent_ready_news:1,agent_id:p.a.getAgentid(),news_id_list:e,type:1};Object(h.T)(i).then(function(e){0===e.ret?(t.hidedetOperateDialog(),t.$slnotify({message:"删除成功"}),t.getList()):t.$slnotify({message:u.X.toString(e.ret)})})}else this.$slnotify({message:"操作权限不足"})},search:function(){this.listQuery.page=1,this.emptyType=2,this.getList()},sort:function(t){this.sortLabel=t.prop,"descending"===t.order?(this.isSort=!0,this.sortOrder=-1):"ascending"===t.order?(this.isSort=!0,this.sortOrder=1):this.isSort=!1,this.getList()},handleSelectionChange:function(t){this.multipleSelection=t},handleSizeChange:function(t){this.listQuery.limit=t,this.getList(),c.a.set(this.$route,t)},handleCurrentChange:function(t){this.listQuery.page=t,this.getList()},openTilSuc:function(t,e){var i=this;this.TipText=t,e&&(this.TipImg=""),setTimeout(function(){i.TipDialog=!0},500)},hideTipSuc:function(){this.TipDialog=!1},opendetOperateDialog:function(){0!==this.multipleSelection.length?this.multipleSelection.some(function(t){return t.is_ready===u.w.NO})?this.detOperateDialog=!0:this.multiDel():this.$slnotify({message:"您还没有勾选任何选项！",duration:1500})},hidedetOperateDialog:function(){this.detOperateDialog=!1},goDetail:function(t){this.ACS[this.AG_K.NEW_INFO]?this.$router.push({path:"/areaagent/messagedetail",query:{newid:t}}):this.$slnotify({message:"操作权限不足"})}}},f={render:function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{attrs:{id:"areaagent-message"}},[t._m(0),t._v(" "),t._m(1),t._v(" "),i("div",{staticClass:"search-content clearfix"},[i("div",{staticClass:"fl clearfix search-item"},[t._m(2),t._v(" "),i("div",{staticClass:"fl"},[i("el-date-picker",{attrs:{"unlink-panels":"",editable:!1,type:"datetimerange","picker-options":t.pickerOptions,"range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期"},model:{value:t.recverTime,callback:function(e){t.recverTime=e},expression:"recverTime"}})],1)]),t._v(" "),i("div",{staticClass:"search-btn fl of-btn-bd-search",on:{click:t.search}},[t._v("搜索")]),t._v(" "),i("div",{staticClass:"btn-group clearfix fr"},[i("div",{staticClass:"create-btn of-btn-bd-search fl",on:{click:t.multiRead}},[t._v("标记为已读")]),t._v(" "),i("div",{staticClass:"delete-btn fl of-btn-bd-search",on:{click:t.opendetOperateDialog}},[t._v("删除")])])]),t._v(" "),i("div",{staticClass:"table-content change-default"},[i("el-table",{ref:"table",attrs:{data:t.list,stripe:""},on:{"selection-change":t.handleSelectionChange,"sort-change":t.sort}},[i("el-table-column",{attrs:{type:"selection",align:"center",width:"70"}}),t._v(" "),i("el-table-column",{attrs:{prop:"title","header-align":"center",label:"标题","min-width":"200",align:"left","show-overflow-tooltip":""},scopedSlots:t._u([{key:"default",fn:function(e){return[i("span",{staticClass:"table-new-title",class:{read:e.row.is_read},on:{click:function(i){t.goDetail(e.row.news_id)}}},[t._v("\n              "+t._s(e.row.title)+"\n              ")])]}}])}),t._v(" "),i("el-table-column",{attrs:{prop:"send_type",label:"发送人","min-width":"100",align:"center"},scopedSlots:t._u([{key:"default",fn:function(e){return[i("span",[t._v(t._s(e.row.send_name))])]}}])}),t._v(" "),i("el-table-column",{attrs:{prop:"ctime",label:"接收时间","min-width":"100",align:"center",sortable:"true"},scopedSlots:t._u([{key:"default",fn:function(e){return[i("span",[t._v(t._s(t._f("formatTimeS")(e.row.ctime)))])]}}])})],1),t._v(" "),t.isListEmpty?i("empty-table",{attrs:{type:t.emptyType}}):t._e()],1),t._v(" "),i("div",{staticClass:"pagination-content change-pagination-default"},[i("el-pagination",{staticClass:"sl-pagination",attrs:{"current-page":t.listQuery.page,"page-sizes":[10,20,40],"page-size":t.listQuery.limit,layout:"sizes, jumper, prev, pager, next",total:t.total},on:{"size-change":t.handleSizeChange,"current-change":t.handleCurrentChange,"update:currentPage":function(e){t.$set(t.listQuery,"page",e)}}})],1),t._v(" "),i("el-dialog",{staticClass:"nopadding operate-dialog",attrs:{visible:t.detOperateDialog,width:"600px",top:"35vh",center:""},on:{"update:visible":function(e){t.detOperateDialog=e}}},[i("div",{staticClass:"operate-content"},[i("span",[t._v("要删除项包括未读公告，是否确定删除？")])]),t._v(" "),i("div",{staticClass:"btn-dialog",attrs:{slot:"footer"},slot:"footer"},[i("div",{staticClass:"btn-Dialogok sl-btn-bd-b-blue",on:{click:t.multiDel}},[t._v("确认")]),t._v(" "),i("div",{staticClass:"btn-Dialogcancel sl-btn-bd-b-blue",on:{click:t.hidedetOperateDialog}},[t._v("取消")])])]),t._v(" "),i("el-dialog",{staticClass:"nopadding change-tip-dialog",attrs:{visible:t.TipDialog,width:"500px",top:"35vh",center:""},on:{"update:visible":function(e){t.TipDialog=e}}},[i("div",{staticClass:"change-tip-content"},[i("div",{staticClass:"icon"},[i("img",{attrs:{src:t.TipImg}})]),t._v(" "),i("div",{staticClass:"text"},[i("span",[t._v(t._s(t.TipText))])])]),t._v(" "),i("div",{staticClass:"btn-dialog",attrs:{slot:"footer"},slot:"footer"},[i("div",{staticClass:"btn-tip sl-btn-bd-b-blue",on:{click:t.hideTipSuc}},[t._v("知道了")])])])],1)},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"nav-own-bar"},[e("span",{staticClass:"cant-click-nav"},[this._v("消息管理")]),this._v("\n    >\n    "),e("span",{staticClass:"current-nav"},[this._v("消息列表")])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"official-title-bar official-title-width"},[e("span",[this._v("发送消息列表")])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"fl"},[e("span",{staticClass:"label-text"},[this._v("发送时间")])])}]};var m=i("C7Lr")(g,f,!1,function(t){i("/8AK"),i("fX5X")},"data-v-149fa8fa",null);e.default=m.exports},"/8AK":function(t,e){},fX5X:function(t,e){}});