webpackJsonp([75],{"4QZa":function(t,i,e){"use strict";Object.defineProperty(i,"__esModule",{value:!0});var s=e("6ROu"),a=e.n(s),n=e("P9l9"),c=e("6nXL"),o={data:function(){return{imgbase_url:"./php",shopLogo:e("8dRI"),baseShopInfo:{ctime:{label:"创建时间",value:"2017-11-24"},phone:{label:"管理员账号",value:"13746748468"},shop_name:{label:"商户名称",value:"阿花的店"},telephone:{label:"联系电话",value:"13746748468"},shop_area:{label:"商户面积",value:"100.00",detail:"132"},shop_address:{label:"商户地址",value:"深圳市前海创锦一号"},shop_model:{label:"商户业态",value:"中餐"}},certificateDataLeft:{name:{label:"企业名称：",value:"--"},agent:{label:"法人代表：",value:"--"},cardid:{label:"企业法人身份证号码：",value:"--"},picture:{label:"法人身份证照片：",value:"",imglist:[e("x4Cl"),e("8oLo")]},scoped:{label:"经营范围：",value:"--"}},certificateDataRight:{bus_number:{label:"营业执照注册号：",value:"12345678901234567890"},bus_time:{label:"营业期限：",value:"--"},bus_picture:{label:"营业执照扫描件：",value:"",imglist:[e("wH0g")]},per_num:{label:"餐饮服务许可证编号",value:"--"},per_picture:{label:"餐饮服务许可证扫描件：",value:"",imglist:[e("AFVk")]}},serviceInfo:{pad_num:{label:"平板智能点餐机",value:2,img:e("vL9n")},cashier_num:{label:"智能收银机",value:2,img:e("argh")},app_num:{label:"掌柜通",value:2,img:e("GvH5")},machine_num:{label:"自助点餐机",value:2,img:e("NXUB")}},shopInfo:{},icbcinfo:{}}},computed:{shopData:function(){return this.shopInfo}},mounted:function(){this.getShopDetail(),this.getIcbcInfo()},methods:{getShopDetail:function(){var t=this,i={get_shop_info:1,shop_id:this.$route.query.shop_id};Object(n._3)(i).then(function(i){0===i.ret?(t.shopInfo=i.data.shopinfo,t.initData()):t.$slnotify({message:c.N.toString(i.ret)})})},getIcbcInfo:function(){var t=this,i={business_info:1,shop_id:this.$route.query.shop_id};Object(n.t)(i).then(function(i){0===i.ret?(t.icbcinfo=i.data.business_info,t.initIcbcInfo()):t.$slnotify({message:c.N.toString(i.ret),duration:1500})})},initData:function(){this.shopInfo&&(this.baseShopInfo.ctime.value=a()(1e3*this.shopData.ctime).format("YYYY-MM-DD"),this.baseShopInfo.phone.value=this.shopData.phone,this.baseShopInfo.shop_name.value=this.shopData.shop_name,this.baseShopInfo.telephone.value=this.shopData.telephone,this.baseShopInfo.shop_area.value=this.shopData.shop_area+" m² ",this.baseShopInfo.shop_address.value=this.shopData.province+this.shopData.city+this.shopData.area+this.shopData.address,this.shopData.shop_model&&this.shopData.shop_model.length>0&&(this.baseShopInfo.shop_model.value=this.shopData.shop_model.join(" 、")),this.shopData.shop_logo&&(this.shopLogo=this.shopData.shop_logo),this.shopInfo.authorize&&(this.serviceInfo.cashier_num.value=this.shopInfo.authorize.cashier_num||"2",this.serviceInfo.app_num.value=this.shopInfo.authorize.app_num||"2",this.serviceInfo.pad_num.value=this.shopInfo.authorize.pad_num||"2",this.serviceInfo.machine_num.value=this.shopInfo.authorize.machine_num||"2"))},initIcbcInfo:function(){if(this.icbcinfo.shop_name&&(this.certificateDataLeft.name.value=this.icbcinfo.shop_name),null!==this.icbcinfo.legal_person&&(this.certificateDataLeft.agent.value=this.icbcinfo.legal_person),null!==this.icbcinfo.legal_card&&(this.certificateDataLeft.cardid.value=this.icbcinfo.legal_card),this.icbcinfo.legal_card_photo&&this.icbcinfo.legal_card_photo.length>0&&(this.certificateDataLeft.picture.imglist=[],this.certificateDataLeft.picture.imglist.push(this.imgbase_url+"/img_get.php?img=1&height=140&width=140&imgname="+this.icbcinfo.legal_card_photo[0]),this.certificateDataLeft.picture.imglist.push(this.imgbase_url+"/img_get.php?img=1&height=140&width=140&imgname="+this.icbcinfo.legal_card_photo[1])),null!==this.icbcinfo.business_scope&&(this.certificateDataLeft.scoped.value=this.icbcinfo.business_scope),this.icbcinfo.business_num&&(this.certificateDataRight.bus_number.value=this.icbcinfo.business_num),this.icbcinfo.business_date&&this.icbcinfo.business_date.length>0){var t=a()(1e3*(this.icbcinfo.business_date||[])[0]).format("YYYY-MM-DD"),i=a()(1e3*(this.icbcinfo.business_date||[])[1]).format("YYYY-MM-DD");this.certificateDataRight.bus_time.value=t+" 至 "+i}else this.certificateDataRight.bus_time.value='"--"至"--"';this.icbcinfo.business_photo.length>0&&(this.certificateDataRight.bus_picture.imglist=[],this.certificateDataRight.bus_picture.imglist.push(this.imgbase_url+"/img_get.php?img=1&height=140&width=140&imgname="+this.icbcinfo.business_photo)),this.icbcinfo.repast_permit_num&&(this.certificateDataRight.per_num.value=this.icbcinfo.repast_permit_num),this.icbcinfo.repast_permit_photo.length>0&&(this.certificateDataRight.per_picture.imglist=[],this.certificateDataRight.per_picture.imglist.push(this.imgbase_url+"/img_get.php?img=1&height=140&width=140&imgname="+this.icbcinfo.repast_permit_photo))}}},l={render:function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("div",{staticClass:"merchant-detail-content"},[e("div",{staticClass:"up-part"},[t._m(0),t._v(" "),e("div",{staticClass:"detail-content"},[e("div",{staticClass:"detail-item fl"},[e("ul",t._l(t.baseShopInfo,function(i,s){return e("li",{key:s},[e("span",{staticClass:"text-gray"},[t._v(t._s(i.label)+"：")]),t._v(" "),e("span",[t._v(t._s(i.value))])])}))]),t._v(" "),e("div",{staticClass:"detail-ite fl"},[e("ul",[t._m(1),t._v(" "),e("li",[e("img",{attrs:{src:t.imgbase_url+"/img_get.php?img=1&height=140&width=140&imgname="+t.shopLogo,alt:""}})])])])])]),t._v(" "),e("div",{staticClass:"middle-part"},[t._m(2),t._v(" "),e("div",{staticClass:"certifica-box clearfix"},[e("div",{staticClass:"certifica-con-left fl"},t._l(t.certificateDataLeft,function(i,s){return e("div",{key:s,staticClass:"cert-item"},[e("div",{staticClass:"clearfix",class:{"cert-text":i.imglist&&0!==i.imglist.length}},[e("span",{staticClass:"label"},[t._v(t._s(i.label))]),t._v(" "),e("span",{staticClass:"text"},[t._v(t._s(i.value))])]),t._v(" "),t._l(i.imglist,function(s,a){return i.imglist&&0!==i.imglist.length?e("div",{key:a,staticClass:"img-container"},[e("img",{attrs:{src:s,alt:""}})]):t._e()})],2)})),t._v(" "),e("div",{staticClass:"certifica-con-right fl"},t._l(t.certificateDataRight,function(i,s){return e("div",{key:s,staticClass:"cert-item"},[e("div",{staticClass:"clearfix",class:{"cert-text":i.imglist&&0!==i.imglist.length}},[e("span",{staticClass:"label"},[t._v(t._s(i.label))]),t._v(" "),e("span",{staticClass:"text"},[t._v(t._s(i.value))])]),t._v(" "),t._l(i.imglist,function(s,a){return i.imglist&&0!==i.imglist.length?e("div",{key:a,staticClass:"img-container"},[e("img",{attrs:{src:s}})]):t._e()})],2)}))])]),t._v(" "),e("div",{staticClass:"down-part"},[t._m(3),t._v(" "),e("div",{staticClass:"service-content"},[e("p",[e("span",{staticClass:"text-gray"},[t._v("服务状态:")]),t._v(" "),0===t.$route.query.isfreeze?e("span",[t._v("服务中")]):e("span",[t._v("停止服务")])]),t._v(" "),t._m(4),t._v(" "),t._l(t.serviceInfo,function(i,s){return e("div",{key:s,staticClass:"item-block"},[e("img",{attrs:{src:i.img,alt:""}}),t._v(" "),e("span",{staticClass:"item-name"},[t._v(t._s(i.label))]),t._v(" "),e("span",{staticClass:"item-num"},[t._v(t._s(i.value))])])})],2)])])},staticRenderFns:[function(){var t=this.$createElement,i=this._self._c||t;return i("div",{staticClass:"change-titlerr rank-title"},[i("span",[this._v("商户详情")])])},function(){var t=this.$createElement,i=this._self._c||t;return i("li",[i("span",[this._v("商户图片:")])])},function(){var t=this.$createElement,i=this._self._c||t;return i("div",{staticClass:"change-titlerr rank-title"},[i("span",[this._v("认证信息")])])},function(){var t=this.$createElement,i=this._self._c||t;return i("div",{staticClass:"change-titlerr rank-title"},[i("span",[this._v("服务信息")])])},function(){var t=this.$createElement,i=this._self._c||t;return i("p",[i("span",{staticClass:"text-gray"},[this._v("服务账号:")])])}]};var h={components:{DetailContent:e("C7Lr")(o,l,!1,function(t){e("AFst")},"data-v-120a1da5",null).exports},data:function(){return{shopInfo:{},icbcinfo:{}}},created:function(){},methods:{getShopDetail:function(){var t=this,i={get_shop_info:1,shop_id:this.$route.query.shop_id};Object(n._3)(i).then(function(i){0===i.ret?t.shopInfo=i.data.shopinfo:t.$slnotify({message:c.N.toString(i.ret)})})},getIcbcInfo:function(){var t=this,i={business_info:1,shop_id:this.$route.query.shop_id};Object(n.t)(i).then(function(i){0===i.ret?t.icbcinfo=i.data.business_info:t.$slnotify({message:c.N.toString(i.ret),duration:1500})})},goBackMerchantList:function(){this.$router.push("/agent/area/merchant")}}},r={render:function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("div",{staticClass:"merchant-detail-page"},[e("div",{staticClass:"header"},[e("p",[e("span",{staticClass:"black",on:{click:t.goBackMerchantList}},[t._v("商户列表")]),t._v(" "),e("span",[t._v(">")]),t._v(" "),e("span",{staticClass:"btn-text-blue"},[t._v("商户信息")])])]),t._v(" "),e("detail-content",{attrs:{shopInfo:t.shopInfo,icbcinfo:t.icbcinfo}}),t._v(" "),e("div",{staticClass:"footer"},[e("div",{staticClass:"sl-btn-bd-b-blue",on:{click:t.goBackMerchantList}},[t._v("返回")])])],1)},staticRenderFns:[]};var u=e("C7Lr")(h,r,!1,function(t){e("zNoP")},"data-v-7e4758e4",null);i.default=u.exports},AFst:function(t,i){},zNoP:function(t,i){}});