webpackJsonp([61],{"/X8t":function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s=i("rVsN"),a=i.n(s),l=i("4YfN"),n=i.n(l),r=i("9rMa"),o=i("6ROu"),c=i.n(o),u=i("r2UX"),p=i("CW5y"),d=i("EuEE"),h=i("P9l9"),f=i("6nXL"),_={components:{EmptyTable:u.a},data:function(){return{pickerOptions:{shortcuts:[{text:"最近一周",onClick:function(t){var e=new Date,i=new Date;i.setTime(i.getTime()-6048e5),t.$emit("pick",[i,e])}},{text:"最近一个月",onClick:function(t){var e=new Date,i=new Date;i.setTime(i.getTime()-2592e6),t.$emit("pick",[i,e])}},{text:"最近三个月",onClick:function(t){var e=new Date,i=new Date;i.setTime(i.getTime()-7776e6),t.$emit("pick",[i,e])}}]},articleTypeOption:[{label:"全部",value:0},{label:f.g.toString(f.g.COMPANY),value:f.g.COMPANY}],articleStatusOption:[{label:"全部",value:2},{label:f.f.toString(f.f.OPEN),value:f.f.OPEN},{label:f.f.toString(f.f.CLOSE),value:f.f.CLOSE}],publishStatusOptions:[{label:"全部",value:2},{label:f.e.toString(f.e.NO),value:f.e.NO},{label:f.e.toString(f.e.YES),value:f.e.YES}],articleAuthorOptions:[],publishTime:null,articleType:"",publishStatus:"",articleStatus:"",articleTitle:"",articleAuthor:"",list:[],emptyType:1,multipleSelection:[],total:100,listQuery:{page:1,limit:10},isSort:!1,sortLabel:"",sortOrder:1,singleDelArr:[],isSingleDel:!0,detOperateDialog:!1,detOperateDialogText:"删除待发布文章，消息到发送时间不会发布，确定删除？",isDeleteNotSend:!0,TipDialog:!1,TipText:"",TipImg:"",ArticleType:f.g,ArticleIsSend:f.e,ArticleStatus:f.f}},computed:n()({isListEmpty:function(){return 0===this.list.length}},Object(r.d)({ACS:function(t){return t.perimission.sysPermis},PL_K:function(t){return t.perimission.PL_K}})),created:function(){this.listQuery.limit=p.a.get(this.$route),this.getPlatformer(),this.getList()},mounted:function(){this.$refs.articaltable.$refs.bodyWrapper.style.minHeight="460px"},methods:{getPlatformer:function(){var t=this;return new a.a(function(e){Object(h.Y)({get_platformer_name:1}).then(function(i){0===i.ret?(t.articleAuthorOptions=i.data.platformer_list||[],t.articleAuthorOptions.unshift({platformer_id:0,pl_name:"全部"}),e()):t.$slnotify({message:f.N.toString(i.ret)})})})},getList:function(){var t=this;if(this.ACS[this.PL_K.VISIT_ARTICLE]){var e={get_article_list:1,page_size:this.listQuery.limit,page_no:this.listQuery.page};if(!d.a.isEmpty(this.publishTime)){var i=this.publishTime[0],s=this.publishTime[1];e.begin_time=parseInt(c()(i).format("X")),e.end_time=parseInt(c()(s).format("X"))}this.articleType&&(e.article_type=this.articleType),""!==this.publishStatus&&2!==this.publishStatus&&(e.is_send=this.publishStatus),this.articleTitle&&(e.title=this.articleTitle),this.articleAuthor&&(e.platformer_id=this.articleAuthor),""!==this.articleStatus&&2!==this.articleStatus&&(e.article_state=this.articleStatus),this.isSort&&(e.sort_name=this.sortLabel,e.desc=this.sortOrder),Object(h.p)(e).then(function(e){0===e.ret?(t.list=e.data.list||[],t.total=e.data.total,t.list.map(function(e){e.send_time&&(e.send_time=c()(1e3*e.send_time).format("YYYY.MM.DD")),e.article_type&&(e.article_type_str=f.g.toString(e.article_type));var i=void 0;return t.articleAuthorOptions.forEach(function(t){t.platformer_id!==e.platformer_id||(i=t.pl_name)}),e.pl_name=i,e.is_send_str=f.e.toString(e.is_send),e.article_state_str=f.f.toString(e.article_state),e})):t.$slnotify({message:f.N.toString(e.ret),duration:1e3})})}else this.$slnotify({message:"操作权限不足"})},search:function(){this.ACS[this.PL_K.SEEK_ARTICLE]?(this.listQuery.page=1,this.emptyType=2,this.getList()):this.$slnotify({message:"操作权限不足"})},sort:function(t){this.sortLabel=t.prop,"descending"===t.order?(this.isSort=!0,this.sortOrder=-1):"ascending"===t.order?(this.isSort=!0,this.sortOrder=1):this.isSort=!1,this.getList()},handleSelectionChange:function(t){this.multipleSelection=t},handleSizeChange:function(t){this.listQuery.limit=t,this.getList(),p.a.set(this.$route,t)},handleCurrentChange:function(t){this.listQuery.page=t,this.getList()},addArticle:function(){this.ACS[this.PL_K.ADD_ARTICLE]?this.$router.push("/officialmanage/companynews"):this.$slnotify({message:"操作权限不足"})},editorArticle:function(t){this.ACS[this.PL_K.EDIT_ARTICLE]?this.$router.push({path:"/officialmanage/companynews",query:{articleid:t}}):this.$slnotify({message:"操作权限不足"})},opendetOperateDialog:function(){this.detOperateDialog=!0},hidedetOperateDialog:function(){this.detOperateDialog=!1},delOPerateOk:function(){this.delete_art()},openNextDialog:function(){var t=this;this.hidedetOperateDialog(),this.$nextTick(function(){setTimeout(function(){t.detOperateDialogText="确定要删除状态为正常的文章？",t.isDeleteNotSend=!1,t.opendetOperateDialog()},400)})},openTilSuc:function(t,e){var i=this;this.TipText=t,setTimeout(function(){i.TipDialog=!0},500)},hideTipSuc:function(){this.TipDialog=!1},isOpenDeleteDialog:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],e=!1,i=t.some(function(t){return t.article_state===f.f.OPEN}),s=t.some(function(t){return t.is_send===f.e.NO});return i&&s?(e=!0,this.detOperateDialogText="删除待发布文章，消息到发送时间不会发布，确定删除？",this.isDeleteNotSend=!0,this.opendetOperateDialog()):i||s?(e=!0,s?(this.detOperateDialogText="删除待发布文章，消息到发送时间不会发布，确定删除？",this.isDeleteNotSend=!1,this.opendetOperateDialog()):(this.detOperateDialogText="确定要删除状态为正常的文章？",this.isDeleteNotSend=!1,this.opendetOperateDialog())):e=!1,e},articleStatusOpen:function(t){var e=this,i={article_open:1,article_state:f.f.OPEN,article_id:t};Object(h.q)(i).then(function(t){0===t.ret?(e.$slnotify({message:"开启成功!",duration:1e3}),e.getList()):e.slnotify(t.ret)})},articleStatusClose:function(t){var e=this;if(this.ACS[this.PL_K.CLOSE_ARTICLE]){var i={article_open:1,article_state:f.f.CLOSE,article_id:t};Object(h.q)(i).then(function(t){0===t.ret?(e.$slnotify({message:"关闭成功!",duration:1e3}),e.getList()):e.slnotify(t.ret)})}else this.$slnotify({message:"操作权限不足"})},singleDel:function(t){this.ACS[this.PL_K.DEL_ARTICLE]||this.$slnotify({message:"操作权限不足"}),this.isSingleDel=!0,this.singleDelArr=[],this.singleDelArr.push(t);var e=[];e.push(t),this.isOpenDeleteDialog(e)||this.delete_art()},mulDelete:function(){0!==this.multipleSelection.length?(this.isSingleDel=!1,this.isOpenDeleteDialog(this.multipleSelection)||this.delete_art()):this.$slnotify({message:"您还没有勾选任何文章！",duration:1500})},delete_art:function(){var t=this,e=void 0;this.isSingleDel?e=this.singleDelArr.map(function(t){return t.article_id}):this.isSingleDel||(e=this.multipleSelection.map(function(t){return t.article_id}));var i={article_del:1,article_id_list:e};Object(h.q)(i).then(function(e){0===e.ret?(t.hidedetOperateDialog(),t.$slnotify({message:"删除成功！",duration:1500}),t.listQuery.page=1,t.getList()):t.slnotify(e.ret)})},slnotify:function(t){this.$slnotify({message:f.N.toString(t),duration:1500})}}},g={render:function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{attrs:{id:"article-list"}},[i("div",{staticClass:"search-content clearfix"},[i("div",{staticClass:"search-item-content clearfix"},[i("div",{staticClass:"first-line"},[i("div",{staticClass:"fl clearfix search-item"},[t._m(0),t._v(" "),i("div",{staticClass:"fl"},[i("el-date-picker",{staticClass:"long-input",attrs:{"unlink-panels":"",editable:!1,type:"datetimerange","picker-options":t.pickerOptions,"range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期"},model:{value:t.publishTime,callback:function(e){t.publishTime=e},expression:"publishTime"}})],1)]),t._v(" "),i("div",{staticClass:"article-author fl clearfix search-item"},[t._m(1),t._v(" "),i("div",{staticClass:"fl"},[i("el-select",{attrs:{placeholder:"请选择"},model:{value:t.articleAuthor,callback:function(e){t.articleAuthor=e},expression:"articleAuthor"}},t._l(t.articleAuthorOptions,function(t){return i("el-option",{key:t.platformer_id,attrs:{label:t.pl_name,value:t.platformer_id}})}))],1)]),t._v(" "),i("div",{staticClass:"fl clearfix search-item"},[t._m(2),t._v(" "),i("div",{staticClass:"fl"},[i("el-select",{attrs:{placeholder:"请选择"},model:{value:t.articleStatus,callback:function(e){t.articleStatus=e},expression:"articleStatus"}},t._l(t.articleStatusOption,function(t){return i("el-option",{key:t.value,attrs:{label:t.label,value:t.value}})}))],1)]),t._v(" "),i("div",{staticClass:"of-btn-bd-search fl",on:{click:t.addArticle}},[t._v("创建文章")])]),t._v(" "),i("div",{staticClass:"first-line"},[i("div",{staticClass:"fl clearfix search-item"},[t._m(3),t._v(" "),i("div",{staticClass:"fl"},[i("el-select",{staticClass:"long-input",attrs:{placeholder:"请选择"},model:{value:t.articleType,callback:function(e){t.articleType=e},expression:"articleType"}},t._l(t.articleTypeOption,function(t){return i("el-option",{key:t.value,attrs:{label:t.label,value:t.value}})}))],1)]),t._v(" "),i("div",{staticClass:"article-title fl clearfix search-item"},[t._m(4),t._v(" "),i("div",{staticClass:"fl"},[i("el-input",{staticClass:"title-input",attrs:{placeholder:"请输入文章标题"},model:{value:t.articleTitle,callback:function(e){t.articleTitle=e},expression:"articleTitle"}})],1)]),t._v(" "),i("div",{staticClass:"search-btn fl",on:{click:t.search}},[t._v("搜索")])])])]),t._v(" "),i("div",{staticClass:"table-content change-default"},[i("el-table",{ref:"articaltable",attrs:{data:t.list,stripe:"","default-sort":{prop:"send_time",order:"descending"}},on:{"selection-change":t.handleSelectionChange,"sort-change":t.sort}},[i("el-table-column",{attrs:{prop:"send_time",label:"发布时间","min-width":"80",align:"center",sortable:"true"},scopedSlots:t._u([{key:"default",fn:function(e){return[e.row.send_time?i("span",{staticClass:"adjust-center"},[t._v(t._s(e.row.send_time))]):i("span",{staticClass:"adjust-center"},[t._v("--")])]}}])}),t._v(" "),i("el-table-column",{attrs:{prop:"article_type",label:"文章类型","min-width":"80",align:"center"},scopedSlots:t._u([{key:"default",fn:function(e){return[e.row.article_type?i("span",[t._v(t._s(e.row.article_type_str))]):i("span",[t._v("--")])]}}])}),t._v(" "),i("el-table-column",{attrs:{prop:"title",label:"文章标题","min-width":"120",align:"center","show-overflow-tooltip":""},scopedSlots:t._u([{key:"default",fn:function(e){return[e.row.title?i("span",[t._v(t._s(e.row.title))]):i("span",[t._v("--")])]}}])}),t._v(" "),i("el-table-column",{attrs:{prop:"platformer_id",label:"发布者","min-width":"80",align:"center"},scopedSlots:t._u([{key:"default",fn:function(e){return[e.row.pl_name?i("span",[t._v(t._s(e.row.pl_name))]):i("span",[t._v("--")])]}}])}),t._v(" "),i("el-table-column",{attrs:{prop:"is_send",label:"发布状态","min-width":"80",align:"center"},scopedSlots:t._u([{key:"default",fn:function(e){return[e.row.is_send?i("span",[t._v(t._s(e.row.is_send_str))]):i("span",[t._v("--")])]}}])}),t._v(" "),i("el-table-column",{attrs:{prop:"article_state",label:"文章状态","min-width":"80",align:"center"},scopedSlots:t._u([{key:"default",fn:function(e){return[""!==e.row.article_state?i("span",[t._v(t._s(e.row.article_state_str))]):i("span",[t._v("--")])]}}])}),t._v(" "),i("el-table-column",{attrs:{prop:"opearte",label:"操作","min-width":"100",align:"center"},scopedSlots:t._u([{key:"default",fn:function(e){return[e.row.article_state===t.ArticleStatus.CLOSE?i("span",{staticClass:"btn-oprate-green",on:{click:function(i){t.articleStatusOpen(e.row.article_id)}}},[t._v("开启")]):i("span",{staticClass:"btn-oprate-red",on:{click:function(i){t.articleStatusClose(e.row.article_id)}}},[t._v("关闭")]),t._v(" "),i("span",{staticClass:"btn-oprate-blue",on:{click:function(i){t.editorArticle(e.row.article_id)}}},[t._v("编辑")]),t._v(" "),i("span",{staticClass:"btn-oprate-red",on:{click:function(i){t.singleDel(e.row)}}},[t._v("删除")])]}}])})],1),t._v(" "),t.isListEmpty?i("empty-table",{attrs:{type:t.emptyType}}):t._e()],1),t._v(" "),i("div",{staticClass:"pagination-content change-pagination-default"},[i("el-pagination",{staticClass:"sl-pagination",attrs:{"current-page":t.listQuery.page,"page-sizes":[10,20,40],"page-size":t.listQuery.limit,layout:"sizes, jumper, prev, pager, next",total:t.total},on:{"size-change":t.handleSizeChange,"current-change":t.handleCurrentChange,"update:currentPage":function(e){t.$set(t.listQuery,"page",e)}}})],1),t._v(" "),i("el-dialog",{staticClass:"nopadding operate-dialog",attrs:{visible:t.detOperateDialog,width:"520px",top:"35vh",center:""},on:{"update:visible":function(e){t.detOperateDialog=e}}},[i("div",{staticClass:"head-nav"},[i("span",[t._v("提示消息")])]),t._v(" "),i("div",{staticClass:"operate-content"},[i("span",{staticClass:"delet-img"}),t._v(" "),i("span",{staticClass:"delet-text"},[t._v(t._s(t.detOperateDialogText))])]),t._v(" "),i("div",{staticClass:"btn-dialog",attrs:{slot:"footer"},slot:"footer"},[t.isDeleteNotSend?i("div",{staticClass:"btn-Dialogok of-btn-bd-b-blue",on:{click:t.openNextDialog}},[t._v("确认")]):i("div",{staticClass:"btn-Dialogok of-btn-bd-b-blue",on:{click:t.delOPerateOk}},[t._v("确认")]),t._v(" "),i("div",{staticClass:"btn-Dialogcancel save-m-gradient ",on:{click:t.hidedetOperateDialog}},[t._v("取消")])])])],1)},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"fl"},[e("span",{staticClass:"label-text"},[this._v("发布时间")])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"fl"},[e("span",{staticClass:"label-text"},[this._v("发布者")])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"fl"},[e("span",{staticClass:"label-text"},[this._v("文章状态")])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"fl"},[e("span",{staticClass:"label-text"},[this._v("文章类型")])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"fl"},[e("span",{staticClass:"label-text"},[this._v("文章标题")])])}]};var v=i("C7Lr")(_,g,!1,function(t){i("gpjc"),i("HvXs")},"data-v-77b1cbea",null);e.default=v.exports},HvXs:function(t,e){},gpjc:function(t,e){}});