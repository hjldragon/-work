webpackJsonp([126],{"1V0h":function(s,t,e){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var i=e("6ROu"),a=e.n(i),n=e("6nXL"),u={props:{agentinfo:{type:Object}},data:function(){return{certificateData:{name:{label:"企业名称：",value:"--",select:0,cert_text:"已通过"},agent:{label:"法人代表：",value:"--",select:1,cert_text:"已通过"},cardid:{label:"企业法人身份证号码：",value:"--",select:2,cert_text:"已通过"},picture:{label:"法人身份证照片：",value:"",cert_text:"已通过",imglist:[e("Wnpm"),e("94No")],select:2},bus_number:{label:"营业执照注册号：",value:"12345678901234567890",cert_text:"已通过",select:0},bus_time:{label:"营业期限：",value:"--",cert_text:"已通过",select:0},bus_picture:{label:"营业执照扫描件：",value:"",imglist:[e("4Xku")],cert_text:"已通过",select:2},scoped:{label:"经营范围：",value:"--",cert_text:"已通过",select:2}},business:{},businessStatus:0,businessStr:"",base_url:"./php"}},computed:{isShowBtn:function(){return this.businessStatus===n.b.NO||this.businessStatus===n.b.FAIL},bussinessClass:function(){return this.businessStatus===n.b.NO?"business-blue":this.businessStatus===n.b.DURING?"business-purple":this.businessStatus===n.b.SUC?"business-green":this.businessStatus===n.b.FAIL?"business-red":void 0}},watch:{agentinfo:{handler:function(){this.init()},deep:!0}},methods:{init:function(){if(this.businessStatus=this.agentinfo.business_status,this.businessStr="（"+n.b.toString(this.businessStatus)+"）",this.business=this.agentinfo.agent_business||{},this.businessStatus!==n.b.NO){this.certificateData.name.value=this.business.company_name,this.certificateData.agent.value=this.business.legal_person,this.certificateData.cardid.value=this.business.legal_card;var s=(this.business.legal_card_photo||[])[0],t=(this.business.legal_card_photo||[])[1];this.certificateData.picture.imglist[0]="./php/img_get.php?img=1&imgname="+s,this.certificateData.picture.imglist[1]="./php/img_get.php?img=1&imgname="+t,this.certificateData.bus_number.value=this.business.business_num;var e=a()(1e3*(this.business.business_date||[])[0]).format("YYYY-MM-DD"),i=a()(1e3*(this.business.business_date||[])[0]).format("YYYY-MM-DD");this.certificateData.bus_time.value=e+" 至 "+i,this.certificateData.bus_picture.imglist[0]="./php/img_get.php?img=1&imgname="+this.business.business_photo,this.certificateData.scoped.value=this.business.business_scope}},goEditor:function(){this.$router.push("/indusagentinfo/editorcert")}}},c={render:function(){var s=this,t=s.$createElement,e=s._self._c||t;return e("div",{attrs:{id:"industry-cert-baseinfo"}},[e("div",{staticClass:"title"},[e("span",[s._v("工商信息")]),s._v(" "),e("span",{staticClass:"cert-str",class:s.bussinessClass},[s._v(s._s(s.businessStr))]),s._v(" "),e("div",{staticClass:"btn-group fr"},[s.isShowBtn?s._e():e("div",{staticClass:"btn-edit-gray"},[s._v("申请认证")]),s._v(" "),s.isShowBtn?e("div",{staticClass:"btn-edit",on:{click:s.goEditor}},[s._v("申请认证")]):s._e()])]),s._v(" "),e("div",{staticClass:"content"},s._l(s.certificateData,function(t,i){return e("div",{staticClass:"cert-item clearfix"},[e("div",{staticClass:"item-left fl"},[e("div",[e("span",{staticClass:"label"},[s._v(s._s(t.label))]),s._v(" "),e("span",{staticClass:"text"},[s._v(s._s(t.value))])]),s._v(" "),t.imglist&&0!==t.imglist.length?e("div",{staticClass:"img-container"},s._l(t.imglist,function(s){return e("img",{attrs:{src:s,alt:""}})})):s._e()])])}))])},staticRenderFns:[]};var l=e("C7Lr")(u,c,!1,function(s){e("ONnP")},"data-v-150088e7",null);t.default=l.exports},ONnP:function(s,t){}});