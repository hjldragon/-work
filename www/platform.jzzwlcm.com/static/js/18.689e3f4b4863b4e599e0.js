webpackJsonp([18,83,102],{"+UqE":function(e,t){},"78zf":function(e,t){},CISB:function(e,t,i){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var s=i("6ROu"),a=i.n(s),n=i("Fzxs"),l=i("CW5y"),r=i("EuEE"),o=i("lbRB"),c=i("6nXL"),u={name:"industryTable",components:{EmptyTable:n.a},data:function(){return{pickerOptions:{shortcuts:[{text:"最近一周",onClick:function(e){var t=new Date,i=new Date;i.setTime(i.getTime()-6048e5),e.$emit("pick",[i,t])}},{text:"最近一个月",onClick:function(e){var t=new Date,i=new Date;i.setTime(i.getTime()-2592e6),e.$emit("pick",[i,t])}},{text:"最近三个月",onClick:function(e){var t=new Date,i=new Date;i.setTime(i.getTime()-7776e6),e.$emit("pick",[i,t])}}]},agentName:"",creatTime:null,list:[],emptyType:1,total:100,listQuery:{page:1,limit:10},isSort:!1,sortLabel:"",sortOrder:1,dialogAgent:{},freezeDialogVisible:!1,delAgentDialogVisible:!1,sucTipDialogVisible:!1,sucTipText:"",sucTipImg:i("qrEq"),AgentIsFreeze:c.c}},created:function(){this.listQuery.limit=l.a.get(this.$route,2)},mounted:function(){this.getList()},computed:{isListEmpty:function(){return 0===this.list.length}},methods:{getList:function(){var e=this,t={agent_list:1,agent_type:c.f.INDUSTRY,page_size:this.listQuery.limit,page_no:this.listQuery.page};if(!r.a.isEmpty(this.creatTime)){var i=this.creatTime[0],s=this.creatTime[1];t.begin_time=parseInt(a()(i).format("X")),t.end_time=parseInt(a()(s).format("X"))+86400}""!==this.agentName&&(t.agent_name=this.agentName),this.isSort&&(t.sort_name=this.sortLabel,t.desc=this.sortOrder),Object(o.c)(t).then(function(t){0===t.ret?(e.list=t.data.list||[],e.total=t.data.total,e.list=e.list.map(function(e){return e.ctime&&(e.ctime=a()(1e3*e.ctime).format("YYYY.MM.DD")),e.agent_level&&(e.agent_level=c.d.toString(e.agent_level)),e.business_time?e.business_time=a()(1e3*e.business_time).format("YYYY.MM.DD"):e.business_time="--",r.a.isEmpty(e.business_status)||(e.business_status=c.b.toString(e.business_status)),e.is_freeze=e.is_freeze||c.c.NO,e.is_freeze_text=c.c.toString(e.is_freeze),e})):console.warn("获取列表数据错误")})},search:function(){this.emptyType=2,this.listQuery.page=1,this.getList()},sort:function(e){this.sortLabel=e.prop,"descending"===e.order?(this.isSort=!0,this.sortOrder=-1):"ascending"===e.order?(this.isSort=!0,this.sortOrder=1):this.isSort=!1,this.getList()},handleSizeChange:function(e){this.listQuery.limit=e,this.getList(),l.a.set(this.$route,e,2)},handleCurrentChange:function(e){this.listQuery.page=e,this.getList()},goeditor:function(e){this.$router.push({path:"/agentlist/editor",query:{agentid:e}})},godetail:function(e){this.$router.push({path:"/agentlist/detail",query:{agentid:e}})},openDelAgentDialog:function(e){this.dialogAgent=e,this.delAgentDialogVisible=!0},hideDelAgentDialog:function(){this.delAgentDialogVisible=!1},deleteAgentOk:function(){var e=this,t=[];t.push(this.dialogAgent.agent_id);var i={agent_del:1,agent_id:t};Object(o.d)(i).then(function(t){0===t.ret?(e.hideDelAgentDialog(),e.getList(),e.openTilSuc("数据删除成功！")):e.$slnotify({message:c.C.toString(t.ret)})})},openReezeDialog:function(e){this.dialogAgent=e,this.freezeDialogVisible=!0},hideReezeDialog:function(){this.freezeDialogVisible=!1},hideReezeDialogHandle:function(){var e=this;setTimeout(function(){e.dialogAgent={}},300)},freezeOk:function(){var e=this,t={agent_freeze:1,agent_id:this.dialogAgent.agent_id,is_freeze:c.c.YES};Object(o.d)(t).then(function(t){0===t.ret?(e.hideReezeDialog(),e.getList(),e.openTilSuc("冻结成功！")):e.$slnotify({message:c.C.toString(t.ret)})})},freeEnable:function(){var e=this,t={agent_freeze:1,agent_id:this.dialogAgent.agent_id,is_freeze:c.c.NO};Object(o.d)(t).then(function(t){0===t.ret?(e.hideReezeDialog(),e.getList(),e.openTilSuc("启用成功！")):e.$slnotify({message:c.C.toString(t.ret)})})},openTilSuc:function(e,t){var s=this;this.sucTipText=e,t&&(this.sucTipImg=i("6MC9")),setTimeout(function(){s.sucTipDialogVisible=!0},500)},hideTipSuc:function(){this.sucTipDialogVisible=!1}}},d={render:function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("div",{attrs:{id:"industry-table"}},[i("div",{staticClass:"search-content clearfix"},[i("div",{staticClass:"time fl clearfix search-item"},[e._m(0),e._v(" "),i("div",{staticClass:"fl"},[i("el-date-picker",{attrs:{"unlink-panels":"",editable:!1,type:"daterange","picker-options":e.pickerOptions,"range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期"},model:{value:e.creatTime,callback:function(t){e.creatTime=t},expression:"creatTime"}})],1)]),e._v(" "),i("div",{staticClass:"name fl clearfix search-item"},[e._m(1),e._v(" "),i("div",{staticClass:"fl"},[i("el-input",{attrs:{placeholder:"请输入代理商名称"},model:{value:e.agentName,callback:function(t){e.agentName=t},expression:"agentName"}})],1)]),e._v(" "),i("div",{staticClass:"search-btn fl",on:{click:e.search}},[e._v("搜索")])]),e._v(" "),i("div",{staticClass:"table-content change-default"},[i("el-table",{ref:"industryTable",attrs:{data:e.list,stripe:""},on:{"sort-change":e.sort}},[i("el-table-column",{attrs:{prop:"ctime",label:"创建时间","min-width":"80",align:"center",sortable:"true"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjust-center"},[e._v(e._s(t.row.ctime))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"agent_name",label:"名称","min-width":"80",align:"center","show-overflow-tooltip":""},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",[e._v(e._s(t.row.agent_name))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"agent_level",label:"级别","min-width":"80",align:"center",sortable:"true"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjust-center"},[e._v(e._s(t.row.agent_level))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"business_time",label:"审核时间","min-width":"80",align:"center",sortable:"true"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjust-center"},[e._v(e._s(t.row.business_time))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"business_status",label:"审核状态","min-width":"80",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",[e._v(e._s(t.row.business_status))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"ctime",label:"首次业务时间","min-width":"110",align:"center",sortable:"true"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjust-center"},[e._v(e._s(t.row.ctime))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"from_salesman",label:"所属销售","min-width":"80",align:"center","show-overflow-tooltip":""},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjuest-spacing"},[e._v(e._s(t.row.from_salesman))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"from",label:"来源","min-width":"80",align:"center","show-overflow-tooltip":""},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjuest-spacing"},[e._v(e._s(t.row.from))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"is_freeze",label:"启用状态","min-width":"80",align:"center",sortable:"true"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjust-center"},[e._v(e._s(t.row.is_freeze_text))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"date",label:"操作","min-width":"115",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[t.row.is_freeze===e.AgentIsFreeze.NO?i("span",{staticClass:"red freeze-btn",on:{click:function(i){e.openReezeDialog(t.row)}}},[e._v("冻结")]):i("span",{staticClass:"green",on:{click:function(i){e.openReezeDialog(t.row)}}},[e._v("启用")]),e._v(" "),i("span",{staticClass:"blue no-margin",on:{click:function(i){e.godetail(t.row.agent_id)}}},[e._v("查看")]),e._v(" "),i("br"),e._v(" "),i("span",{staticClass:"blue",on:{click:function(i){e.goeditor(t.row.agent_id)}}},[e._v("编辑")]),e._v(" "),i("span",{staticClass:"red no-margin",on:{click:function(i){e.openDelAgentDialog(t.row)}}},[e._v("删除")])]}}])})],1),e._v(" "),e.isListEmpty?i("empty-table",{attrs:{type:e.emptyType}}):e._e()],1),e._v(" "),i("div",{staticClass:"pagination-content change-pagination-default"},[i("el-pagination",{staticClass:"sl-pagination",attrs:{"current-page":e.listQuery.page,"page-sizes":[10,20,40],"page-size":e.listQuery.limit,layout:"sizes, jumper, prev, pager, next",total:e.total},on:{"size-change":e.handleSizeChange,"current-change":e.handleCurrentChange,"update:currentPage":function(t){e.$set(e.listQuery,"page",t)}}})],1),e._v(" "),i("el-dialog",{staticClass:"nopadding freeze-dialog",attrs:{visible:e.freezeDialogVisible,width:"600px",top:"35vh",center:""},on:{"update:visible":function(t){e.freezeDialogVisible=t},close:e.hideReezeDialogHandle}},[i("div",{staticClass:"freeze-content"},[i("span",[e._v("确定要执行冻结操作？")])]),e._v(" "),i("div",{staticClass:"btn-dialog",attrs:{slot:"footer"},slot:"footer"},[this.dialogAgent.is_freeze===e.AgentIsFreeze.NO?i("div",{staticClass:"btn-Dialogok",on:{click:e.freezeOk}},[e._v("确认")]):i("div",{staticClass:"btn-DialogEnable",on:{click:e.freeEnable}},[e._v("确认")]),e._v(" "),i("div",{staticClass:"btn-Dialogcancel",on:{click:e.hideReezeDialog}},[e._v("取消")])])]),e._v(" "),i("el-dialog",{staticClass:"nopadding change-tip-dialog",attrs:{visible:e.sucTipDialogVisible,width:"500px",top:"35vh",center:""},on:{"update:visible":function(t){e.sucTipDialogVisible=t}}},[i("div",{staticClass:"change-tip-content"},[i("div",{staticClass:"icon"},[i("img",{attrs:{src:e.sucTipImg}})]),e._v(" "),i("div",{staticClass:"text"},[i("span",[e._v(e._s(e.sucTipText))])])]),e._v(" "),i("div",{staticClass:"btn-dialog",attrs:{slot:"footer"},slot:"footer"},[i("div",{staticClass:"btn-tip",on:{click:e.hideTipSuc}},[e._v("知道了")])])]),e._v(" "),i("el-dialog",{staticClass:"nopadding freeze-dialog",attrs:{visible:e.delAgentDialogVisible,width:"600px",top:"35vh",center:""},on:{"update:visible":function(t){e.delAgentDialogVisible=t},close:e.hideReezeDialogHandle}},[i("div",{staticClass:"freeze-content"},[i("span",[e._v("确定要删除此数据？")])]),e._v(" "),i("div",{staticClass:"btn-dialog",attrs:{slot:"footer"},slot:"footer"},[i("div",{staticClass:"btn-Dialogok",on:{click:e.deleteAgentOk}},[e._v("确认")]),e._v(" "),i("div",{staticClass:"btn-Dialogcancel",on:{click:e.hideDelAgentDialog}},[e._v("取消")])])])],1)},staticRenderFns:[function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"fl"},[t("span",{staticClass:"label-text"},[this._v("创建时间")])])},function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"fl"},[t("span",{staticClass:"label-text"},[this._v("名称")])])}]};var g=i("C7Lr")(u,d,!1,function(e){i("78zf"),i("Nf3i")},"data-v-14360eee",null);t.default=g.exports},Nf3i:function(e,t){},RCdB:function(e,t){},Wc25:function(e,t){},e0c2:function(e,t){},imFx:function(e,t){e.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAAXNSR0IArs4c6QAAAkdJREFUSA3tlj1oFEEUgN+b3dtbdWNEGxNQUlipSBpb5ZT8GLARLSzEMq0RK0E8xdY7AlaWiqWVoCBnLioIaiGCF7CwE0REQTnPy+3uPGc27jCzubg67CUIDgzz/r+d27c3g/BrnFiqj3Q5nyeCSWEaTu2FrYgfAOnOrr3BxZs4G7pp4R+c3waCo6le+Eo0IupfeL/UaYvaV5gCEB5S8iAFosOyvAIjkjNInqpNKxwFVo51Ev5ZcGT7A1nvGBnOuzuCYUR4ZgO3BgPR03ujsx0C9mJ9wTY0Lcd+x1oRG3HDwOov83dPjYivxTv9asQw51OiI71Dgie6jxB3i/gx3ZaVc8GI7Nb9/XNns4mp/mDf+RtCllONKhF73qq/FfA9ypgRcsEAdGCmVR93HNfc8Vbvo+zqU63a9q5TMk6zl63aGADuzLAMNRdMROPiyV9FvGck4pfeSWG42ya8BGHvnOFMFFpt0iwb1lz/wdpbyBOZJyMQKFnzorP+3ObKJqQ6AZ879uZ6IJpPNtlfD2uwuD8dFDQ5jcHDCMJvXQDODXuiiKOsNORvkbI9eHXZxLL8uQ1xx/z09FC+HI5Kvfiujtb+fpGxSMzTElz4jmXRfkNCWVCeaFaqj6W/+B33oUqo6zlTi5XqYuoeOFicbLFTLk0vTF1bSKFy1cHWFze9oC6Le1nsDfnTzcmrj3S7lBVYtEQz67TRCVeyJNTd7M80KtVGvzqquYJy6cz3blQTQRMieVu/4D+xuYHvhHFM3ib/eOPI5Ydr5fwE4+OfipvGvd8AAAAASUVORK5CYII="},qMZq:function(e,t,i){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var s=i("CISB"),a=i("zD6h"),n=i("EuEE"),l={data:function(){return{activeName:"first",isShowIndustry:!1}},watch:{activeName:function(e){"second"===e&&(this.isShowIndustry=!0)}},components:{IndustryTable:s.default,AreaTable:a.default},created:function(){},mounted:function(){var e=this,t=this.$route.query.isindustry;this.activeName=!0===t||"true"===t?"second":"first","second"===this.activeName?this.$nextTick(function(){var t=e.$refs.industry.$refs.industryTable.$refs.bodyWrapper;n.a.AdjustHeight(t)}):this.$nextTick(function(){var t=e.$refs.area.$refs.areaTable.$refs.bodyWrapper;n.a.AdjustHeight(t)})},methods:{handleClick:function(e){var t=this;"second"===e.name?(this.$router.replace({path:"/agent/list",query:{isindustry:!0}}),this.$nextTick(function(){var e=t.$refs.industry.$refs.industryTable.$refs.bodyWrapper;n.a.AdjustHeight(e)})):(this.$router.replace({path:"/agent/list",query:{isindustry:!1}}),this.$nextTick(function(){var e=t.$refs.area.$refs.areaTable.$refs.bodyWrapper;n.a.AdjustHeight(e)}))},gocreate:function(){this.$router.push("/agent/create"),this.$store.commit("CHANGE_SELECTMENU","/agent/create")}}},r={render:function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{attrs:{id:"agent-list"}},[s("el-tabs",{on:{"tab-click":e.handleClick},model:{value:e.activeName,callback:function(t){e.activeName=t},expression:"activeName"}},[s("el-tab-pane",{attrs:{name:"first"}},[s("span",{staticClass:"label-title",attrs:{slot:"label"},slot:"label"},[e._v("区域代理")]),e._v(" "),s("div",{staticClass:"area-content"},[s("area-table",{ref:"area"})],1)]),e._v(" "),s("el-tab-pane",{attrs:{name:"second"}},[s("span",{staticClass:"label-title",attrs:{slot:"label"},slot:"label"},[e._v("行业代理")]),e._v(" "),e.isShowIndustry?s("div",{staticClass:"list-content"},[s("industry-table",{ref:"industry"})],1):e._e()])],1),e._v(" "),s("div",{staticClass:"go-creat",on:{click:e.gocreate}},[s("img",{attrs:{src:i("imFx"),alt:""}}),e._v(" "),s("span",[e._v("创建")])])],1)},staticRenderFns:[]};var o=i("C7Lr")(l,r,!1,function(e){i("+UqE"),i("Wc25")},"data-v-c8215932",null);t.default=o.exports},zD6h:function(e,t,i){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var s=i("6ROu"),a=i.n(s),n=i("Fzxs"),l=i("CW5y"),r=i("aFBZ"),o=i("EuEE"),c=i("lbRB"),u=i("6nXL"),d={components:{EmptyTable:n.a},data:function(){return{pickerOptions:{shortcuts:[{text:"最近一周",onClick:function(e){var t=new Date,i=new Date;i.setTime(i.getTime()-6048e5),e.$emit("pick",[i,t])}},{text:"最近一个月",onClick:function(e){var t=new Date,i=new Date;i.setTime(i.getTime()-2592e6),e.$emit("pick",[i,t])}},{text:"最近三个月",onClick:function(e){var t=new Date,i=new Date;i.setTime(i.getTime()-7776e6),e.$emit("pick",[i,t])}}]},agentName:"",creatTime:null,provinces:"",city:"",area:"",list:[],emptyType:1,total:100,listQuery:{page:1,limit:10},isSort:!1,sortLabel:"",sortOrder:1,areaTree:[],defaultProps:{children:"children",label:"label"},isHighlight:!0,dialogAgent:{},freezeDialogVisible:!1,delAgentDialogVisible:!1,sucTipDialogVisible:!1,sucTipText:"",sucTipImg:i("qrEq"),AgentIsFreeze:u.c,isSelect:!0}},created:function(){this.areaTree=r.a.getAreaArr(),this.listQuery.limit=l.a.get(this.$route,1)},mounted:function(){this.getList()},computed:{isListEmpty:function(){return 0===this.list.length}},methods:{search:function(){this.emptyType=2,this.listQuery.page=1,this.getList()},currentChange:function(e,t){this.isSelect=!1,this.isHighlight=!0,1===t.level?(this.area="",this.city="",this.provinces=t.label):2===t.level?(this.area="",this.city=t.label,this.provinces=t.parent.label):3===t.level&&(this.area=t.label,this.city=t.parent.label,this.provinces=t.parent.parent.label),this.creatTime=null,this.agentName="",this.getList()},nodeExpand:function(e,t){var i=t.key;this.$refs.tree.setCurrentKey(i),this.isSelect=!1,this.isHighlight=!0,1===t.level?(this.area="",this.city="",this.provinces=t.label):2===t.level?(this.area="",this.city=t.label,this.provinces=t.parent.label):3===t.level&&(this.area=t.label,this.city=t.parent.label,this.provinces=t.parent.parent.label),this.creatTime=null,this.agentName="",this.getList()},getList:function(){var e=this,t={agent_list:1,agent_type:u.f.AREA,page_size:this.listQuery.limit,page_no:this.listQuery.page};if(!o.a.isEmpty(this.creatTime)){var i=this.creatTime[0],s=this.creatTime[1];t.begin_time=parseInt(a()(i).format("X")),t.end_time=parseInt(a()(s).format("X"))+86400}""!==this.agentName&&(t.agent_name=this.agentName),this.isSort&&(t.sort_name=this.sortLabel,t.desc=this.sortOrder),this.provinces&&(t.agent_province=this.provinces),this.city&&this.provinces&&(t.agent_city=this.city),this.area&&this.city&&this.provinces&&(t.agent_area=this.area),Object(c.c)(t).then(function(t){0===t.ret&&(e.list=t.data.list||[],e.total=t.data.total,e.list=e.list.map(function(e){e.ctime&&(e.ctime=a()(1e3*e.ctime).format("YYYY.MM.DD")),e.agent_level&&(e.agent_level=u.d.toString(e.agent_level)),e.business_time?e.business_time=a()(1e3*e.business_time).format("YYYY.MM.DD"):e.business_time="--",o.a.isEmpty(e.business_status)||(e.business_status=u.b.toString(e.business_status)),e.is_freeze=e.is_freeze||u.c.NO,e.is_freeze_text=u.c.toString(e.is_freeze);var t="";return e.agent_province&&(t+=e.agent_province),e.agent_city&&(t=t+"-"+e.agent_city),e.agent_city&&e.agent_area&&(t=t+"-"+e.agent_area),e.agent_city_area=t,e}))})},sort:function(e){this.sortLabel=e.prop,"descending"===e.order?(this.isSort=!0,this.sortOrder=-1):"ascending"===e.order?(this.isSort=!0,this.sortOrder=1):this.isSort=!1,this.getList()},handleSizeChange:function(e){this.listQuery.limit=e,this.getList(),l.a.set(this.$route,e,1)},handleCurrentChange:function(e){this.listQuery.page=e,this.getList()},goeditor:function(e){this.$router.push({path:"/agentlist/editor",query:{agentid:e}})},godetail:function(e){this.$router.push({path:"/agentlist/detail",query:{agentid:e}})},openDelAgentDialog:function(e){this.dialogAgent=e,this.delAgentDialogVisible=!0},hideDelAgentDialog:function(){this.delAgentDialogVisible=!1},deleteAgentOk:function(){var e=this,t=[];t.push(this.dialogAgent.agent_id);var i={agent_del:1,agent_id:t};Object(c.d)(i).then(function(t){0===t.ret?(e.hideDelAgentDialog(),e.getList(),e.openTilSuc("数据删除成功！")):e.$slnotify({message:u.C.toString(t.ret)})})},openReezeDialog:function(e){this.dialogAgent=e,this.freezeDialogVisible=!0},hideReezeDialog:function(){this.freezeDialogVisible=!1},hideReezeDialogHandle:function(){var e=this;setTimeout(function(){e.dialogAgent={}},300)},freezeOk:function(){var e=this,t={agent_freeze:1,agent_id:this.dialogAgent.agent_id,is_freeze:u.c.YES};Object(c.d)(t).then(function(t){0===t.ret?(e.hideReezeDialog(),e.getList(),e.openTilSuc("冻结成功！")):e.$slnotify({message:u.C.toString(t.ret)})})},freeEnable:function(){var e=this,t={agent_freeze:1,agent_id:this.dialogAgent.agent_id,is_freeze:u.c.NO};Object(c.d)(t).then(function(t){0===t.ret?(e.hideReezeDialog(),e.getList(),e.openTilSuc("启用成功！")):e.$slnotify({message:u.C.toString(t.ret)})})},openTilSuc:function(e,t){var s=this;this.sucTipText=e,t&&(this.sucTipImg=i("6MC9")),setTimeout(function(){s.sucTipDialogVisible=!0},500)},hideTipSuc:function(){this.sucTipDialogVisible=!1},resetChecked:function(){this.isSelect=!0,this.isHighlight=!1,this.area="",this.city="",this.provinces="",this.getList()}}},g={render:function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("div",{attrs:{id:"area-table"}},[i("div",{staticClass:"area-tree change-tree-default"},[i("div",{staticClass:"area-all",class:{active:e.isSelect},on:{click:e.resetChecked}},[i("span",{staticClass:"el-tree-node__expand-icon el-icon-caret-right expanded"}),e._v(" "),i("span",[e._v("代理区域")])]),e._v(" "),i("el-tree",{ref:"tree",attrs:{data:e.areaTree,props:e.defaultProps,"node-key":"adcode","highlight-current":e.isHighlight,"expand-on-click-node":!0},on:{"current-change":e.currentChange,"node-expand":e.nodeExpand,"node-collapse":e.nodeExpand}})],1),e._v(" "),i("div",{staticClass:"area-list"},[i("div",{staticClass:"search-content clearfix"},[i("div",{staticClass:"time fl clearfix search-item"},[e._m(0),e._v(" "),i("div",{staticClass:"fl"},[i("el-date-picker",{attrs:{"unlink-panels":"",editable:!1,type:"daterange","picker-options":e.pickerOptions,"range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期"},model:{value:e.creatTime,callback:function(t){e.creatTime=t},expression:"creatTime"}})],1)]),e._v(" "),i("div",{staticClass:"name fl clearfix search-item"},[e._m(1),e._v(" "),i("div",{staticClass:"fl"},[i("el-input",{attrs:{placeholder:"请输入代理商名称"},model:{value:e.agentName,callback:function(t){e.agentName=t},expression:"agentName"}})],1)]),e._v(" "),i("div",{staticClass:"search-btn fl",on:{click:e.search}},[e._v("搜索")])]),e._v(" "),i("div",{staticClass:"table-content change-default"},[i("el-table",{ref:"areaTable",attrs:{data:e.list,stripe:""},on:{"sort-change":e.sort}},[i("el-table-column",{attrs:{prop:"ctime",label:"创建时间","min-width":"80",align:"center",sortable:"true"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjust-center"},[e._v(e._s(t.row.ctime))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"agent_name",label:"名称","min-width":"80",align:"center","show-overflow-tooltip":""},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",[e._v(e._s(t.row.agent_name))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"agent_level",label:"级别","min-width":"80",align:"center",sortable:"true"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjust-center"},[e._v(e._s(t.row.agent_level))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"business_time",label:"审核时间","min-width":"80",align:"center",sortable:"true"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjust-center"},[e._v(e._s(t.row.business_time))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"business_status",label:"审核状态","min-width":"80",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",[e._v(e._s(t.row.business_status))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"ctime",label:"首次业务时间","min-width":"110",align:"center",sortable:"true"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjust-center"},[e._v(e._s(t.row.ctime))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"date",label:"区域","min-width":"110",align:"center","show-overflow-tooltip":""},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjuest-spacing"},[e._v(e._s(t.row.agent_city_area))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"from_salesman",label:"所属销售","min-width":"80",align:"center","show-overflow-tooltip":""},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjuest-spacing"},[e._v(e._s(t.row.from_salesman))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"from",label:"来源","min-width":"80",align:"center","show-overflow-tooltip":""},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjuest-spacing"},[e._v(e._s(t.row.from))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"is_freeze",label:"启用状态","min-width":"80",align:"center",sortable:"true"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("span",{staticClass:"adjust-center"},[e._v(e._s(t.row.is_freeze_text))])]}}])}),e._v(" "),i("el-table-column",{attrs:{prop:"date",label:"操作","min-width":"115",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[t.row.is_freeze===e.AgentIsFreeze.NO?i("span",{staticClass:"red freeze-btn",on:{click:function(i){e.openReezeDialog(t.row)}}},[e._v("冻结")]):i("span",{staticClass:"green",on:{click:function(i){e.openReezeDialog(t.row)}}},[e._v("启用")]),e._v(" "),i("span",{staticClass:"blue no-margin",on:{click:function(i){e.godetail(t.row.agent_id)}}},[e._v("查看")]),e._v(" "),i("br"),e._v(" "),i("span",{staticClass:"blue",on:{click:function(i){e.goeditor(t.row.agent_id)}}},[e._v("编辑")]),e._v(" "),i("span",{staticClass:"red no-margin",on:{click:function(i){e.openDelAgentDialog(t.row)}}},[e._v("删除")])]}}])})],1),e._v(" "),e.isListEmpty?i("empty-table",{attrs:{type:e.emptyType}}):e._e()],1),e._v(" "),i("div",{staticClass:"pagination-content change-pagination-default"},[i("el-pagination",{staticClass:"sl-pagination",attrs:{"current-page":e.listQuery.page,"page-sizes":[10,20,40],"page-size":e.listQuery.limit,layout:"sizes, jumper, prev, pager, next",total:e.total},on:{"size-change":e.handleSizeChange,"current-change":e.handleCurrentChange,"update:currentPage":function(t){e.$set(e.listQuery,"page",t)}}})],1)]),e._v(" "),i("el-dialog",{staticClass:"nopadding freeze-dialog",attrs:{visible:e.freezeDialogVisible,width:"600px",top:"35vh",center:""},on:{"update:visible":function(t){e.freezeDialogVisible=t},close:e.hideReezeDialogHandle}},[i("div",{staticClass:"freeze-content"},[this.dialogAgent.is_freeze===e.AgentIsFreeze.NO?i("span",[e._v("确定要执行冻结操作？")]):i("span",[e._v("确定要执行启用操作？")])]),e._v(" "),i("div",{staticClass:"btn-dialog",attrs:{slot:"footer"},slot:"footer"},[this.dialogAgent.is_freeze===e.AgentIsFreeze.NO?i("div",{staticClass:"btn-Dialogok",on:{click:e.freezeOk}},[e._v("确认")]):i("div",{staticClass:"btn-DialogEnable",on:{click:e.freeEnable}},[e._v("确认")]),e._v(" "),i("div",{staticClass:"btn-Dialogcancel",on:{click:e.hideReezeDialog}},[e._v("取消")])])]),e._v(" "),i("el-dialog",{staticClass:"nopadding change-tip-dialog",attrs:{visible:e.sucTipDialogVisible,width:"500px",top:"35vh",center:""},on:{"update:visible":function(t){e.sucTipDialogVisible=t}}},[i("div",{staticClass:"change-tip-content"},[i("div",{staticClass:"icon"},[i("img",{attrs:{src:e.sucTipImg}})]),e._v(" "),i("div",{staticClass:"text"},[i("span",[e._v(e._s(e.sucTipText))])])]),e._v(" "),i("div",{staticClass:"btn-dialog",attrs:{slot:"footer"},slot:"footer"},[i("div",{staticClass:"btn-tip",on:{click:e.hideTipSuc}},[e._v("知道了")])])]),e._v(" "),i("el-dialog",{staticClass:"nopadding freeze-dialog",attrs:{visible:e.delAgentDialogVisible,width:"600px",top:"35vh",center:""},on:{"update:visible":function(t){e.delAgentDialogVisible=t},close:e.hideReezeDialogHandle}},[i("div",{staticClass:"freeze-content"},[i("span",[e._v("确定要删除此数据？")])]),e._v(" "),i("div",{staticClass:"btn-dialog",attrs:{slot:"footer"},slot:"footer"},[i("div",{staticClass:"btn-Dialogok",on:{click:e.deleteAgentOk}},[e._v("确认")]),e._v(" "),i("div",{staticClass:"btn-Dialogcancel",on:{click:e.hideDelAgentDialog}},[e._v("取消")])])])],1)},staticRenderFns:[function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"fl"},[t("span",{staticClass:"label-text"},[this._v("创建时间")])])},function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"fl"},[t("span",{staticClass:"label-text"},[this._v("名称")])])}]};var p=i("C7Lr")(d,g,!1,function(e){i("e0c2"),i("RCdB")},"data-v-7e5ff418",null);t.default=p.exports}});