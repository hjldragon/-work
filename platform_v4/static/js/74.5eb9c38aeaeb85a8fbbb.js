webpackJsonp([74],{I0hc:function(e,t){},fl85:function(e,t){},iIcH:function(e,t,s){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=s("ZLEe"),i=s.n(a),n=s("aA9S"),l=s.n(n),o=s("4YfN"),r=s.n(o),c=s("6nXL"),_=s("EU70"),u=s("GAg9"),m=s("Q0Ca"),p=s("a2vD"),v=s("9rMa"),d=s("P9l9"),b=1,f=2,h=3,g=4,C=5,I={components:{PreviewImg:_.a,ImgUpload:u.a},data:function(){return{inputTipMsg:"请输入(20字以内)",maxlength:20,shop_id:null,agent_name:null,base_url:"./php",audit_plan:1,userid:p.a.getUserid(),stepsTitle:["销售审核","销售经理审核","运营审核","运营经理审核","财务审核","财务经理审核"],certificationData:{businessName:{label:"商户名称:",name:"--",state:null,note:"",save:0,type:b},legalPerson:{label:"法人代表：",name:"--",state:null,note:"",save:0,type:b},legalPersonId:{label:"法人身份证号:",name:"--",state:null,note:"",save:0,type:b},legalPersonImg:{label:"法人身份证照片:",name:"--",state:null,note:"",type:f,save:0,idFrontalImg:"",idReverseImg:""},businessId:{label:"营业执照注册号:",name:"--",state:null,note:"",save:0,type:b},businessDate:{label:"营业期限:",name:"--",state:null,note:"",save:0,type:b},businessImg:{label:"营业执照扫描件:",name:"--",state:null,note:"",type:h,save:0,imgSrc:""},permissionName:{label:"餐饮服务许可证编号:",name:"--",state:null,note:"",save:0,type:1},permissionId:{label:"餐饮服务许可证扫描件:",name:"--",state:null,note:"",type:3,save:0,imgSrc:""},operateRange:{label:"经营范围:",name:"--",state:null,note:"",save:0,type:b},businessSever:{label:"代理费:",name:"",state:"无",number:"",type:g},isSever:{type:C,label:"代理费",name:"",state:"无"}},certifiBaseInfo:{ctime:{label:"创建时间:",name:"--"},from:{label:"来源:",name:"--"},loginId:{label:"登录账号:",name:"--"},employee_name:{label:"销售人员:",name:"--"},shop_name:{label:"商户名称:",name:"--"},contacts:{label:"联系人:",name:"--"},upAgent:{label:"上级代理商:",name:"--"},contactsPhone:{label:"联系电话:",name:"--"},address:{label:"地址:",name:"--"},email:{label:"邮箱:",name:"--"},authorizeInfo:{label:"授权信息:",name:"--"}},certifiRecord:[]}},created:function(){this.shop_id=this.$route.query.shop_id,this.agent_name=this.$route.query.agent_name,this.getCertiPage()},beforeRouteEnter:function(e,t,s){s(function(e){e.SET_SELECTMENU({selectMenu:"/official/merchant/certificate"})})},beforeRouteLeave:function(e,t,s){this.SET_SELECTMENU({selectMenu:""}),s()},methods:r()({},Object(v.c)(["SET_SELECTMENU"]),{allAgree_m:function(e){var t=this.certificationData;for(var s in t)e?(t[s].state=1,t[s].note=""):t[s].state=0;this.complete()},agree:function(e){e.state=1,e.save=0,e.note=""},noAgree:function(e){e.state=0,!0===e.save&&(e.save=0)},saveInput:function(e){e.save=1},editSave:function(e){e.save=0},getPhoto:function(e){this.certificationData.businessSever.name=e},validateData:function(){var e=this.certificationData;for(var t in e){if(null===e[t].state)return this.$slnotify({message:e[t].label+"未选择通过或不通过",duration:1500}),!1;if(0===e[t].state&&(0===e[t].save||""==e[t].note)&&"businessSever"!=t&&"isSever"!=t)return this.$slnotify({message:"请填写并保存"+e[t].label+"不通过理由",duration:1500}),!1}return 5==this.audit_plan&&1==e.businessSever.state&&""==e.businessSever.number?(this.$slnotify({message:"请输入商户服务费水单号",duration:1500}),!1):5==this.audit_plan&&1==e.businessSever.state&&e.businessSever.number.indexOf(" ")>=0?(this.$slnotify({message:"请输入正确商户服务费水单号",duration:1500}),!1):5!=this.audit_plan||1!=e.businessSever.state||""!=e.businessSever.name||(this.$slnotify({message:"请上传商户服务费水单号图",duration:1500}),!1)},getCertiPage:function(){var e=this,t={business_info:1,shop_id:this.shop_id,platform:1};Object(d.v)(t).then(function(t){0===t.ret?e.handleData(t.data.business_info):e.$slnotify({message:c.X.toString(t.ret),duration:1500})});var s={get_shop_info:1,shop_id:this.shop_id,platform:1};Object(d._6)(s).then(function(t){0===t.ret?e.handleBaseInfo(t.data.shopinfo):e.$slnotify({message:c.X.toString(t.ret),duration:1500})});var a={get_audit_list:1,shop_id:this.shop_id,platform:1};Object(d.t)(a).then(function(t){0===t.ret?e.certifiRecord=t.data.audit_list:e.$slnotify({message:c.X.toString(t.ret),duration:1500})})},submitCertifi:function(e){var t=this,s={save_business_status:1,shop_id:this.shop_id,userid:this.userid,platform:1};s=l()({},s,e),Object(d.u)(s).then(function(e){0===e.ret?(t.$slnotify({message:"提交成功",duration:1500}),setTimeout(function(){t.closePage()},1500)):t.$slnotify({message:c.X.toString(e.ret),duration:1500})})},handleBaseInfo:function(e){if(0!=i()(e).length){var t=this.certifiBaseInfo,s=e;t.ctime.name=Object(m.formatTimeS)(s.ctime),t.shop_name.name=s.shop_name,t.employee_name.name=s.employee_name,t.address.name=s.province+s.city+s.area+s.address,t.from.name=s.from,t.email.name=s.email||"--",t.contactsPhone.name=s.telephone||"--",t.loginId.name=s.phone||"--",t.contacts.name=s.real_name||"--";var a="";s.authorize.app_num&&(a+="掌柜通x"+s.authorize.app_num+";"),s.authorize.pad_num&&(a+="平板智能点餐机x"+s.authorize.pad_num+";"),s.authorize.cashier_num&&(a+="智能收银机x"+s.authorize.cashier_num+";"),s.authorize.machine_num&&(a+="自助点餐机x"+s.authorize.machine_num+";"),s.authorize.pc_num&&(a+="商家运营平台x"+s.authorize.pc_num+";"),t.authorizeInfo.name=a,t.upAgent.name=this.agent_name,this.audit_plan=s.audit_plan}},handleData:function(e){if(0!=i()(e).length){var t=this.certificationData,s=e;t.businessName.name=s.shop_name||"--",t.legalPerson.name=s.legal_person||"--",t.legalPersonId.name=s.legal_card||"--",t.legalPersonImg.idFrontalImg=s.legal_card_photo&&s.legal_card_photo[0],t.legalPersonImg.idReverseImg=s.legal_card_photo&&s.legal_card_photo[1],t.businessId.name=s.business_num||"--",t.businessDate.name=s.business_date?Object(m.formatTimeD)(s.business_date[0])+"至"+Object(m.formatTimeD)(s.business_date[1]):"--",t.businessImg.name=s.business_photo,t.permissionName.name=s.repast_permit_num||"--",t.permissionId.name=s.repast_permit_photo||"--",t.operateRange.name=s.business_scope||"--"}},complete:function(){if(this.validateData()){var e={},t=this.certificationData;return e.shop_name_status=t.businessName.state,e.shop_name_reason=t.businessName.note,e.legal_person_status=t.legalPerson.state,e.legal_person_reason=t.legalPerson.note,e.legal_card_status=t.legalPersonId.state,e.legal_card_reason=t.legalPersonId.note,e.legal_card_photo_status=t.legalPersonImg.state,e.legal_card_photo_reason=t.legalPersonImg.note,e.business_num_status=t.businessId.state,e.business_num_reason=t.businessId.note,e.business_date_status=t.businessDate.state,e.business_date_reason=t.businessDate.note,e.business_photo_status=t.businessImg.state,e.business_photo_reason=t.businessImg.note,e.repast_permit_num_status=t.permissionName.state,e.repast_permit_num_reason=t.permissionName.note,e.repast_permit_photo_status=t.permissionId.state,e.repast_permit_photo_reason=t.permissionId.note,e.business_scope_status=t.operateRange.state,e.business_scope_reason=t.operateRange.note,5==this.audit_plan&&1==t.businessSever.state&&(e.business_sever_money=t.businessSever.name,e.water_num=t.businessSever.number),this.submitCertifi(e),e}return!1},closePage:function(){this.$router.push("/official/merchant/certificate")},goCertiList:function(){this.$router.push("/official/merchant/certificate")}})},y={render:function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{staticClass:"certification-page",attrs:{id:"certification-page"}},[s("div",{staticClass:"nav-own-bar"},[s("span",{staticClass:"cant-click-nav"},[e._v("商户管理")]),e._v("\n    >\n    "),s("span",{staticClass:"can-click-nav",on:{click:e.goCertiList}},[e._v("商户认证")]),e._v("\n    >\n    "),s("span",{staticClass:"current-nav"},[e._v("审核")])]),e._v(" "),s("div",{staticClass:"steps-box"},[s("el-steps",{attrs:{active:e.audit_plan,"finish-status":"success","align-center":""}},e._l(e.stepsTitle,function(e){return s("el-step",{key:e,attrs:{title:e}},[s("span",{staticClass:"step-icon",attrs:{slot:"icon"},slot:"icon"})])}),1)],1),e._v(" "),s("div",{staticClass:"certification-data-box"},[s("div",{staticClass:"change-titlerr"},[e._v("商户认证资料")]),e._v(" "),s("div",{staticClass:"certi-data-main"},e._l(e.certificationData,function(t,a){return s("div",{key:a,staticClass:"certi-data-item clearfix"},[4!=t.type&&5!==t.type?s("div",{staticClass:"fl"},[s("span",[e._v(e._s(t.label))]),e._v(" "),1==t.type?s("span",{staticClass:"baseInfo-height"},[e._v(e._s(t.name))]):e._e(),e._v(" "),2==t.type?s("div",[s("Preview-Img",{staticClass:"fl certi-upload_right",attrs:{width:"200px",height:"120px",imgSrc:t.idFrontalImg}}),e._v(" "),s("Preview-Img",{staticClass:"fl certi-upload_right",attrs:{width:"200px",height:"120px",imgSrc:t.idReverseImg}})],1):e._e(),e._v(" "),3==t.type?s("div",[s("Preview-Img",{staticClass:"fl certi-upload_right",attrs:{width:"200px",height:"120px",imgSrc:t.name}})],1):e._e()]):e._e(),e._v(" "),4==t.type&&5==e.audit_plan?s("div",[s("span",[e._v(e._s(t.label))]),e._v("    \n          "),s("el-radio-group",{model:{value:t.state,callback:function(s){e.$set(t,"state",s)},expression:"item.state"}},[s("el-radio",{attrs:{label:1}},[e._v("已收")]),e._v(" "),s("el-radio",{attrs:{label:0}},[e._v("未收")])],1),e._v(" "),1==t.state?s("el-input",{attrs:{placeholder:"请输入流水单号"},model:{value:t.number,callback:function(s){e.$set(t,"number",s)},expression:"item.number"}}):e._e(),e._v(" "),1==t.state?s("div",{staticClass:"certi-upload"},[s("Preview-Img",{staticClass:"fl certi-upload_right",attrs:{width:"200px",height:"120px",imgSrc:t.name}}),e._v(" "),s("img-upload",{on:{"send-image":e.getPhoto}}),e._v(" "),s("div",{staticClass:"certi-notes notes-bott"},[e._v("注：支持JPG、JPEG、PNG文件格式")])],1):e._e()],1):e._e(),e._v(" "),5==t.type&&6==e.audit_plan?s("div",[s("span",[e._v(e._s(t.label))]),e._v("\n              已收\n        ")]):e._e(),e._v(" "),4!=t.type&&5!=t.type?s("div",{staticClass:"fr"},[s("span",{staticClass:"certification-btn sl-btn-bd-m-blue",class:{succ:1===t.state},on:{click:function(s){e.agree(t)}}},[e._v("通过")]),e._v(" "),s("span",{staticClass:"certification-btn sl-btn-bd-m-blue no-agree",class:{error:0===t.state},on:{click:function(s){e.noAgree(t)}}},[e._v("不通过")]),e._v(" "),s("div",{staticClass:"save-box"},[0===t.state?s("div",[0==t.save?s("span",[s("el-input",{staticClass:"certifi-note-input",attrs:{maxlength:e.maxlength,placeholder:e.inputTipMsg},model:{value:t.note,callback:function(s){e.$set(t,"note",s)},expression:"item.note"}}),e._v(" "),s("span",{staticClass:"certification-btn-small sl-btn-bd-s-blue",on:{click:function(s){e.saveInput(t)}}},[e._v("保存")])],1):e._e(),e._v(" "),1==t.save&&t.note?s("span",{on:{click:function(s){e.editSave(t)}}},[e._v("("+e._s(t.note)+")")]):e._e()]):e._e()])]):e._e()])})),e._v(" "),s("div",{staticClass:"certification-btn-foot"},[s("div",{staticClass:"certification-btn-big sl-btn-bd-b-blue",on:{click:function(t){e.allAgree_m(!0)}}},[e._v("全部通过")]),e._v(" "),s("div",{staticClass:"certification-btn-big sl-btn-bd-b-blue",on:{click:function(t){e.allAgree_m(!1)}}},[e._v("全部不通过")]),e._v(" "),s("div",{staticClass:"certification-btn-big sl-btn-bd-b-blue",on:{click:e.complete}},[e._v("完成审核")]),e._v(" "),s("div",{staticClass:"certification-btn-big sl-btn-bd-b-blue",on:{click:e.closePage}},[e._v("取消")])])]),e._v(" "),s("div",{staticClass:"certi-base-info"},[s("div",{staticClass:"change-titlerr"},[e._v("基本信息")]),e._v(" "),s("div",{staticClass:"certi-baseInfo-main"},[s("el-row",e._l(e.certifiBaseInfo,function(t,a){return s("el-col",{key:a,staticClass:"baseInfo-item",attrs:{xs:12,sm:12,md:12,lg:12,xl:12}},[s("span",[e._v(e._s(t.label))]),e._v(" \n          "),s("span",{staticClass:"baseInfo-height"},[e._v(e._s(t.name||"--")+" ")])])}),1)],1)]),e._v(" "),s("div",{staticClass:"certifi-base-record"},[s("div",{staticClass:"change-titlerr"},[e._v("审核记录")]),e._v(" "),s("div",{staticClass:"certifi-record-box"},[s("div",{staticClass:"certifi-record-table"},[s("el-row",{staticClass:"certifi-table-head"},[s("el-col",{attrs:{span:6}},[e._v("职位")]),e._v(" "),s("el-col",{attrs:{span:6}},[e._v("审核结果")]),e._v(" "),s("el-col",{attrs:{span:6}},[e._v("审核人")]),e._v(" "),s("el-col",{attrs:{span:6}},[e._v("审核时间")])],1),e._v(" "),e._l(e.certifiRecord,function(t,a){return s("el-row",{key:a,staticClass:"certifi-table-tr"},[s("el-col",{attrs:{span:6}},[e._v(e._s(t.position_name))]),e._v(" "),s("el-col",{attrs:{span:6}},[1==t.audit_code?s("span",{staticClass:"certifi-succ"},[e._v("通过")]):e._e(),e._v(" "),0==t.audit_code?s("span",{staticClass:"certifi-err"},[e._v("未通过")]):e._e()]),e._v(" "),s("el-col",{attrs:{span:6}},[e._v(e._s(t.real_name||"--"))]),e._v(" "),s("el-col",{attrs:{span:6}},[e._v(e._s(e._f("formatTimeD")(t.audit_time)))])],1)}),e._v(" "),0==e.certifiRecord.length?s("el-row",{staticClass:"certifi-table-tr"},[e._v("无审核记录")]):e._e()],2)])])])},staticRenderFns:[]};var S=s("C7Lr")(I,y,!1,function(e){s("fl85"),s("I0hc")},"data-v-906a0410",null);t.default=S.exports}});