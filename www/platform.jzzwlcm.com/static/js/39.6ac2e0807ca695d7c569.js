webpackJsonp([39],{Jog7:function(e,t){},PYWp:function(e,t){},"Xl+P":function(e,t){},lsnP:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var s=a("aA9S"),i=a.n(s),r=a("6j3y"),n={components:{codeImg:a("b5mr").a},data:function(){return{showImg:!1,base_url:"./php"}},props:{imgSrc:{type:String,default:null},width:{type:String,default:"100px"},height:{type:String,default:"100px"}},computed:{hasImg:function(){return""!=this.imgSrc}},methods:{clickImg:function(){this.showImg=!0}}},l={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"preview-box",style:{width:e.width,height:e.height}},[e.hasImg?a("div",{staticClass:"preview-hasImg"},[a("img",{attrs:{src:e.base_url+"/img_get.php?img=1&height=100&width=100&imgname="+e.imgSrc,alt:""}}),e._v(" "),a("div",{staticClass:"preview-cover"}),e._v(" "),a("i",{staticClass:"el-icon-zoom-in",on:{click:e.clickImg}})]):a("div",{staticClass:"preview-noImg"}),e._v(" "),e.showImg?a("code-img",{attrs:{imgSrc:e.imgSrc},on:{clickit:function(t){e.showImg=!1}}}):e._e()],1)},staticRenderFns:[]};var o={components:{PreviewImg:a("C7Lr")(n,l,!1,function(e){a("Jog7")},"data-v-77ab1e7d",null).exports,ImgUpload:r.a},data:function(){var e=this;return{baseData:{idFrontal:"",idReverse:"",businessId:"",publicAccount:"",businessDate:"",begin:"",end:"",permissionId:"",businessImg:"",permissionImg:"",operateRange:""},rules:{idFrontal:[{required:!0,validator:function(t,a,s){""==e.baseData.idFrontal?(console.log(898989),s(new Error("请上传法人身份证正面照"))):""==e.baseData.idReverse?s(new Error("请上传法人身份证反面照")):s()},trigger:"change"}],businessId:[{required:!0,message:"请填写营业执照注册号",trigger:"blur"}],publicAccount:[{required:!0,message:"请填写对公账户",trigger:"blur"}],businessDate:[{required:!0,message:"请选择营业期限",trigger:"blur"}],permissionId:[{required:!0,message:"请填写餐饮服务许可证编号",trigger:"blur"}],businessImg:[{required:!0,message:"请上传营业执照扫描件",trigger:"change"}],taxpayerNumber:[{required:!0,message:"请填写纳税人识别号",trigger:"blur"}],permissionImg:[{required:!0,message:"请上传餐饮服务许可证扫描件",trigger:"change"}],operateRange:[{required:!0,message:"请填写经营范围",trigger:"blur"}]}}},props:{formData:{type:[Object,Array],default:null}},created:function(){null!==this.formData&&(baseData=i()({},formData))},methods:{getPhoto:function(e,t){switch(e){case 1:this.baseData.idFrontal=t||"",this.$refs.baseData.validateField("idFrontal");break;case 2:this.baseData.idReverse=t||"",this.$refs.baseData.validateField("idFrontal");break;case 3:this.baseData.businessImg=t||"",this.$refs.baseData.validateField("businessImg");break;case 4:this.baseData.permissionImg=t||"",this.$refs.baseData.validateField("permissionImg")}}}},c={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("el-form",{ref:"baseData",staticClass:"shop-model-main",attrs:{model:e.baseData,rules:e.rules,"label-width":"168px"}},[a("el-form-item",{attrs:{label:"法人身份证照片",prop:"idFrontal"}},[a("div",{staticClass:"clearfix"},[a("div",{staticClass:"fl certificate-left"},[a("div",{staticClass:"certificate-title"},[e._v("\n              正面\n            ")]),e._v(" "),a("Preview-Img",{attrs:{imgSrc:e.baseData.idFrontal,width:"200px",height:"120px"}}),e._v(" "),a("div",{staticClass:"certificate-upload"},[a("img-upload",{on:{"send-image":function(t){e.getPhoto(1,t)}}})],1)],1),e._v(" "),a("div",{staticClass:"fl certificate-right"},[a("div",{staticClass:"certificate-title"},[e._v("\n              反面\n            ")]),e._v(" "),a("Preview-Img",{attrs:{imgSrc:e.baseData.idReverse,width:"200px",height:"120px"}}),e._v(" "),a("div",{staticClass:"certificate-upload"},[a("img-upload",{on:{"send-image":function(t){e.getPhoto(2,t)}}})],1)],1)]),e._v(" "),a("div",{staticClass:"certi-notes"},[e._v("注：支持JPG、JPEG、PNG文件格式，大小不得大于2M")])]),e._v(" "),a("el-form-item",{attrs:{label:"营业执照注册号",prop:"businessId"}},[a("el-input",{staticClass:"up-input-small",model:{value:e.baseData.businessId,callback:function(t){e.$set(e.baseData,"businessId",t)},expression:"baseData.businessId"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"商户对公账户",prop:"publicAccount"}},[a("el-input",{staticClass:"up-input-small",model:{value:e.baseData.publicAccount,callback:function(t){e.$set(e.baseData,"publicAccount",t)},expression:"baseData.publicAccount "}})],1),e._v(" "),a("el-form-item",{attrs:{label:"营业期限",prop:"businessDate"}},[a("el-date-picker",{attrs:{type:"daterange","range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期"},model:{value:e.baseData.businessDate,callback:function(t){e.$set(e.baseData,"businessDate",t)},expression:"baseData.businessDate"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"餐饮服务许可证编号",prop:"permissionId"}},[a("el-input",{staticClass:"up-input-big",model:{value:e.baseData.permissionId,callback:function(t){e.$set(e.baseData,"permissionId",t)},expression:"baseData.permissionId"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"营业执照扫描件",prop:"businessImg"}},[a("div",{staticClass:"certi-upload"},[a("Preview-Img",{staticClass:"fl",attrs:{imgSrc:e.baseData.businessImg,width:"170px",height:"120px"}}),e._v(" "),a("div",{staticClass:"fl certi-upload_right"},[a("img-upload",{on:{"send-image":function(t){e.getPhoto(3,t)}}}),e._v(" "),a("div",{staticClass:"certi-notes notes-bott"},[e._v("注：支持JPG、JPEG、PNG文件格式，大小不得大于2M")])],1)],1)]),e._v(" "),a("el-form-item",{attrs:{label:"纳税人识别号",prop:"taxpayerNumber"}},[a("el-input",{staticClass:"up-input-big",model:{value:e.baseData.taxpayerNumber,callback:function(t){e.$set(e.baseData,"taxpayerNumber",t)},expression:"baseData.taxpayerNumber"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"餐饮服务许可证扫描件",prop:"permissionImg"}},[a("div",{staticClass:"certi-upload"},[a("Preview-Img",{staticClass:"fl",attrs:{imgSrc:e.baseData.permissionImg,width:"170px",height:"120px"}}),e._v(" "),a("div",{staticClass:"fl certi-upload_right"},[a("img-upload",{on:{"send-image":function(t){e.getPhoto(4,t)}}}),e._v(" "),a("div",{staticClass:"certi-notes notes-bott"},[e._v("注：支持JPG、JPEG、PNG文件格式，大小不得大于2M")])],1)],1)]),e._v(" "),a("el-form-item",{attrs:{label:"经营范围",prop:"operateRange"}},[a("el-input",{staticClass:"up-input-big",model:{value:e.baseData.operateRange,callback:function(t){e.$set(e.baseData,"operateRange",t)},expression:"baseData.operateRange"}})],1)],1)},staticRenderFns:[]};var u={components:{ShopBusinessData:a("C7Lr")(o,c,!1,function(e){a("y+cw"),a("PYWp")},"data-v-597d8be5",null).exports},props:{forbid:{type:Boolean,default:!0}},methods:{close:function(){this.$emit("close")},baseInfoSubmit:function(){}}},d={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"shop-model-box"},[a("div",{staticClass:"certi-tabs"},[a("span",{staticClass:"tabs-item"},[e._v("商户认证")]),a("span",{staticClass:"tabs-item active"},[e._v(">申请认证")]),e._v(" "),a("span",{staticClass:"certifi-result-close",on:{click:e.close}})]),e._v(" "),a("div",{staticClass:"shop-model-dialog"},[a("shop-business-data",{staticClass:"shop-model_main"}),e._v(" "),a("div",{staticClass:"shop-model_foot"},[a("div",{staticClass:"certification-btn-big",on:{click:e.baseInfoSubmit}},[e._v("提交")])])],1)])},staticRenderFns:[]};var p=a("C7Lr")(u,d,!1,function(e){a("Xl+P")},"data-v-71650555",null);t.default=p.exports},"y+cw":function(e,t){}});