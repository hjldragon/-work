webpackJsonp([61],{"+Fv/":function(e,t){},Zzf2:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=n("aQu3"),i={render:function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("div",{attrs:{id:"agent-edit-dialog"}},[n("el-dialog",{attrs:{visible:e.dialogVisible,width:"1024px",top:e.boxTop,center:"","before-close":e.handleClose},on:{"update:visible":function(t){e.dialogVisible=t}}},[n("div",{staticClass:"head-nav"},[n("span",[e._v("编辑代理商")])]),e._v(" "),n("div",{staticClass:"agent-form"},[n("el-form",{ref:"basicInfoFormr",staticClass:"demo-ruleForm float-left",attrs:{"label-width":"103px",model:e.agentForm,rules:e.rules}},[n("el-form-item",{attrs:{label:"代理商类型",prop:"agent_type"}},[n("span",[e._v(e._s(e.agentForm.agent_type_text))])]),e._v(" "),n("el-form-item",{attrs:{label:"代理商级别",prop:"agent_level"}},[n("span",[e._v(e._s(e.agentForm.agent_level_text))])]),e._v(" "),n("el-form-item",{attrs:{label:"代理商名称",prop:"agent_name"}},[n("el-input",{attrs:{placeholder:"请输入代理商名称"},on:{input:function(t){e.checkFinishInput("basicInfoFormr")}},model:{value:e.agentForm.agent_name,callback:function(t){e.$set(e.agentForm,"agent_name",t)},expression:"agentForm.agent_name"}})],1),e._v(" "),n("el-form-item",{attrs:{label:"联系人",prop:"relation_name"}},[n("el-input",{attrs:{placeholder:"请输入联系人姓名"},on:{input:function(t){e.checkFinishInput("basicInfoFormr")}},model:{value:e.agentForm.relation_name,callback:function(t){e.$set(e.agentForm,"relation_name",t)},expression:"agentForm.relation_name"}})],1),e._v(" "),n("el-form-item",{attrs:{label:"联系电话",prop:"telephone"}},[n("el-input",{attrs:{placeholder:"请输入联系电话"},on:{input:function(t){e.checkFinishInput("basicInfoFormr")}},model:{value:e.agentForm.telephone,callback:function(t){e.$set(e.agentForm,"telephone",t)},expression:"agentForm.telephone"}})],1),e._v(" "),n("el-form-item",{attrs:{label:"电子邮箱",prop:"email"}},[n("el-input",{attrs:{placeholder:"请输入电子邮箱"},on:{input:function(t){e.checkFinishInput("basicInfoFormr")}},model:{value:e.agentForm.email,callback:function(t){e.$set(e.agentForm,"email",t)},expression:"agentForm.email"}})],1),e._v(" "),n("el-form-item",{attrs:{label:"代理商状态",prop:"is_freeze"}},[n("el-radio-group",{on:{change:function(t){e.checkFinishInput("basicInfoFormr")}},model:{value:e.agentForm.is_freeze,callback:function(t){e.$set(e.agentForm,"is_freeze",t)},expression:"agentForm.is_freeze"}},e._l(e.statusOptions,function(t){return n("el-radio-button",{key:t.idx,attrs:{label:t.idx,value:t.idx}},[e._v(e._s(t.label))])}),1)],1),e._v(" "),n("el-form-item",{attrs:{label:"代理商来源",prop:"from"}},[n("el-select",{attrs:{placeholder:"全部"},on:{change:function(t){e.checkFinishInputf("basicInfoFormr")}},model:{value:e.agentForm.from,callback:function(t){e.$set(e.agentForm,"from",t)},expression:"agentForm.from"}},e._l(e.fromOptions,function(e){return n("el-option",{key:e,attrs:{label:e,value:e}})}),1)],1)],1),e._v(" "),n("el-form",{ref:"basicInfoForm",staticClass:"demo-ruleForm float-left",attrs:{"label-width":"100px",model:e.agentForm,rules:e.rules}},[n("el-form-item",{attrs:{label:"地址",prop:"address"}},[n("el-input",{attrs:{placeholder:"请输入详细地址"},on:{input:function(t){e.checkFinishInput("basicInfoForm")}},model:{value:e.agentForm.address,callback:function(t){e.$set(e.agentForm,"address",t)},expression:"agentForm.address"}})],1),e._v(" "),n("el-form-item",{staticClass:"logo-item",attrs:{label:"店铺Logo",prop:"agent_logo"}},[n("div",{staticClass:"certi-upload"},[n("Preview-Img",{staticClass:"fl certi-upload_right",attrs:{width:"309px",height:"217px",imgSrc:e.agentForm.agent_logo}}),e._v(" "),n("img-upload",{staticClass:"certi-upload-btn",class:{"upload-agin":e.BtnText.length>3},attrs:{FreedomCrop:!1,isOfficial:e.isOfficial,isTextBtn:e.isTextBtn,BtnText:e.BtnText},on:{"send-image":e.getPhoto}}),e._v(" "),e.agentForm.agent_logo?e._e():n("div",{staticClass:"upload-img-text"},[n("span",{staticClass:"unload-title"},[e._v("添加图片")]),e._v(" "),n("div",{staticClass:"upload-text"},[e._v("支持JPG、JPEG、PNG文件格式")])])],1)]),e._v(" "),n("el-form-item",{attrs:{label:"销售人员",prop:"real_name"}},[n("el-select",{attrs:{placeholder:"请选择销售人员"},model:{value:e.agentForm.real_name,callback:function(t){e.$set(e.agentForm,"real_name",t)},expression:"agentForm.real_name"}},e._l(e.employeeOptions,function(e){return n("el-option",{key:e.pl_name,attrs:{label:e.pl_name,value:e.pl_name}})}),1)],1)],1)],1),e._v(" "),n("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[n("div",{staticClass:"btn-Dialogcancel of-btn-bd-b-blue  btn-Dialogok",on:{click:e.handleClose}},[e._v("取消")]),e._v(" "),n("div",{staticClass:"save-m-gradient btn-Dialogok",on:{click:e.handleSure}},[e._v("保存")])])])],1)},staticRenderFns:[]};var o=function(e){n("+Fv/"),n("oXr1")},r=n("C7Lr")(a.a,i,!1,o,null,null);t.default=r.exports},aQu3:function(e,t,n){"use strict";(function(e){var a=n("a3Yh"),i=n.n(a),o=n("P9l9"),r=n("9aGi"),l=(n("a2vD"),n("EU70")),s=n("GAg9"),m=n("N6PA"),c=n("EuEE"),u=n("6nXL");t.a={data:function(){var e,t=this;return{boxTop:"15vh",dialogVisible:!0,submitData:{},isCantClick:!1,employeeOptions:[],rules:(e={telephone:[{required:!0,validator:function(e,n,a){var i=m.b.isLandLinePhone(n);return""===t.agentForm.telephone?a(new Error("请输入电话号码")):i?void a():a(new Error("请输入合法的电话号码"))},trigger:"input,blur"}],email:[{required:!0,validator:function(e,t,n){var a=m.b.isEmail(t);""===t?n(new Error("请输入邮箱地址")):a?n():n(new Error("请输入正确的邮箱格式"))},trigger:"input,blur"}],real_name:[{required:!0,message:"请选择销售人员",trigger:"change,blur"}],agent_name:[{required:!0,message:"请输入代理商名称",trigger:"input,blur"}]},i()(e,"real_name",[{required:!0,validator:function(e,t,n){""===t?n(new Error("请输入联系人")):!1===/^[^ ]+$/.test(t)?n(new Error("联系人不能包含空格符")):n()},trigger:"input,blur"}]),i()(e,"address",[{required:!0,validator:function(e,t,n){""===t?n(new Error("请输入地址")):!1===/^[^ ]+$/.test(t)?n(new Error("地址不能包含空格符")):n()},trigger:"input,blur"}]),i()(e,"from",[{required:!0,message:"请选择代理商来源",trigger:"change,blur"}]),i()(e,"is_freeze",[{required:!0,message:"请选择代理商状态",trigger:"change,blur"}]),e),fromOptions:[],statusOptions:[{label:u.x.toString(u.x.NO),idx:u.x.NO},{label:u.x.toString(u.x.YES),idx:u.x.YES}]}},props:{detailInfo:{type:Object,required:!0,default:function(){}}},computed:{agentForm:function(){return this.detailInfo},BtnText:function(){return""!==this.agentForm.agent_logo&&null!==this.agentForm.agent_logo?"重新上传":""},isOfficial:function(){return""!==this.agentForm.agent_logo&&null!==this.agentForm.agent_logo?0:1},isTextBtn:function(){return""!==this.agentForm.agent_logo&&null!==this.agentForm.agent_logo?1:0}},components:{citySelect:r.a,PreviewImg:l.a,ImgUpload:s.a},created:function(){this.getFromList(),this.getEmployeeOption()},mounted:function(){var t=this,n=document.documentElement.clientWidth;document.documentElement.clientHeight;this.boxTop=c.a.changeDialogTop("15vh","10vh","1vh",n),e(window).resize(function(){n=document.documentElement.clientWidth,t.boxTop=c.a.changeDialogTop("15vh","10vh","1vh",n)})},methods:{getEmployeeOption:function(){var e=this;Object(o._1)({get_pl_salesman_list:1}).then(function(t){0===t.ret?e.employeeOptions=t.data.list:e.$slnotify({message:u.X.toString(t.ret)})})},getFromList:function(){var e=this;Object(o.G)({get_from_list:1,only_from:1}).then(function(t){0===t.ret?e.fromOptions=t.data.list:e.$slnotify({message:u.X.toString(t.ret)})})},handleSure:function(){this.isCantClick?this.$slnotify({message:"请填写完整信息"}):(this.dialogVisible=!1,this.isCanClick(this.agentForm),this.$refs.basicInfoFormr.resetFields(),this.$refs.basicInfoForm.resetFields())},handleClose:function(e){this.dialogVisible=!1,this.$refs.basicInfoFormr.resetFields(),this.$refs.basicInfoForm.resetFields(),this.$emit("handleClose")},isCanClick:function(e){this.isCantClick=!1;var t={agent_save:1,agent_id:e.agent_id,agent_name:e.agent_name,telephone:e.telephone,employee_name:this.agentForm.real_name,email:e.email,address:e.address,is_freeze:e.is_freeze,from:e.from_id,agent_logo:e.agent_logo,real_name:e.relation_name};this.submitData=t,console.log(t),this.$emit("handleSure",this.submitData)},istCanClick:function(){this.isCantClick=!0},getPhoto:function(e){this.agentForm.agent_logo=e,this.checkFinishInput("basicInfoForm"),this.checkFinishInput("basicInfoFormr")},checkFinishInput:function(e){var t=this;this.agentForm.email&&this.agentForm.address&&this.agentForm.telephone&&this.agentForm.agent_name&&this.agentForm.from&&this.agentForm.real_name&&""!==this.agentForm.is_freeze?this.$refs[e].validate(function(e){if(!e)return t.isCantClick=!0,!1;t.isCantClick=!1}):this.isCantClick=!0},checkFinishInputf:function(){this.agentForm.from_id=this.agentForm.from}}}}).call(t,n("L7Pj"))},oXr1:function(e,t){}});