# 点餐收银系统HTTP协议初稿

该文档为初稿，仅定义HTTP输入输出， 不涉及其他内容

#一、登录

## 1.登录协议

### 1.1 商户ID登录

####描述


	使用账号ID登录

#### 输入 GET     <<<<<<<<<<<<<<<<<<<<<能支持

	{
	   "opr":"login_save", <<<<<<<<<<<<<<<<<<<<<<<接口名称
		"account_login":true,
		"account_phone": "15013731450", //账号或手机号
		"password":"123456789", 	 //密码
		"token":"weqwesdfdfasdfsdfsadf" //16位唯一token
	}

#### 输出       <<<<<<<<<<<<<<<<<<<<<能支持

    {
		"ret":0,
		"token":"",
		"data": {
		}
    }


### 1.2 微信登录

####流程描述    


	获取二维码=>手机扫描=>终端轮询

#####1.2.1 获取二维码

####描述

	获取二维码
	

####输入 GET         <<<<<<<<<<<<<<<<<<<<<能支持
            
	{
		"get_wxlogin_qrcode":true,
		"token":""
	}

####输出

	{
		"ret":0,
		"token":"",
		"data":{
			"url":"http://xxxx.png"
		}
	}

#####1.2.2 终端轮询

####描述
	
	进入二维码页面， 终端开启轮询， 退出即关闭轮询

####输入 POST         <<<<<<<<<<<<<<<<<<<<<能支持

	{
		"ask_login":true,
		"token":""
	}

####输出

	{
		"ret":0,
		"token":"",	
		"data":{
		}
	}

#二、点餐系统

##1. 获取菜单列表
	
####描述

	获取菜单列表协议，会在登录成功后调用。描述属于点餐系统范畴，可使用到登录逻辑中，增加用户体验

####输入 GET

	{
		"token":"",
		"get_ver":1 // 菜单版本号，后台可以判断终端菜单版本，是否进行更新
	}

####输出

	菜单无更新
	{
		"ret":0,
		"token":"",
		"data":{
			"ver":1
			"sort":0, // 0 按时间排序， 1 按热度排序
		}
	}

	菜单有更新, 只做全部更新， 不支持增量

	{
    "ret": 0,
	"token":"",
    "data": {
		"ver":2
        "menu": [
            {
				"food_id":"0001"， // 菜品ID
                "name": "小炒肉", // 菜品名称
                "type": 0, // 菜品类型
				"type_name":"湖南菜", // 菜品类型名称
				"url":"http:xxxx/xiaochaorou.png",
				"attribute":[
					{
						"taste":"辣度", // 口味名
						"value":[
							{"name":"微辣"},
							{"name":"中辣"}
						]
					}
				]
                "price": { // 对应小中大份
                    "min_price": 5,
                    "mid_price": 10,
                    "max_price": 15
                }
            },
            {
				"food_id":"0001"，
                "name": "回锅肉",
                "type": 1,
				"url":"http:xxxx/huiguorou.png",
				"attribute":[
					{
						"taste":"辣度", // 口味名
						"value":[
							{"name":"微辣"},
							{"name":"中辣"}
						]
					}
				]
                "price": {
                    "min_price": 5, // 如果没有大小区分， 最小即为最终价格
                    
                }
            }
        ]
    	}
	}

##2.获取餐台信息

####描述
	
	获取餐台信息，如：大厅、包房、一楼、二楼等

####输入 GET

	{
		"get_tables":true,
		"token":""
	}

####输出

	{
		"ret": 0,
		"token":"",
    	"data": {
			"tables":[
				{
					"table_name":"C001", // 桌名
					"area":"大厅",    // 大厅、一楼、二楼
					"type":"包厢", // 类型名称
					"seat":"4" // 座位人数
				}
			]	
		}
	}

##3.获取待处理

####描述

	获取待处理信息

####输入 GET

	{
		"get_pedding":true,
		"token":"",
	}

####输出

	{
		"ret":0,
		"token":"",
		"data":{
			"pedding":[
				{
					"order_id":"00000002121321", // 订单/退款唯一标识，用于查订单详情 
					"serial_number":"1000", // 流水号
					"type":0, // 待处理类型， 0 新订单， 1 退款
					"serial_time":"2017-11-09 10:10:20", // 流水时间
					"price":30, // 金额
					"is_pay":true // 已支付， 退款默认为true
				}
			]
		}
	}

##4.获取订单或退款详情

####描述

	点击订单或退款，获取详情

####输入 GET

	{
		"get_details":true
		"token":"",
		"order_id":"00000002121321" // 订单/退款唯一标识，用于查订单详情
	}

####输出
	
	{
		"ret":0, // 非0值， 由后台定义
		"token":"",
		"data": {
			"create_time":"2017-11-09 15:39:01", // 创建时间
			"serial_number":"10", // 流水号
			"table_name":"C001", // 桌名
			"remark":"asdfasdf", // 备注
			"is_takeAway":false, // 销售方式， true 在店
			"is_pay":false, // 是否已支付
			"is_order":false, // 是否已下单， false 没有下单
			"pay_time":"2017-10-09 15:45:10", // 支付时间， 只有在已支付情况下才有
			"sum_pay":20, // 订单总计价格
			"discounts":2, // 优惠两元
			"real_price":20, // 已支付为实收， 未支付为应收
			"food":[
				{
					"food_id":"", // 菜品ID
					"name":"小炒肉", // 菜品名
					"price":15, // 单价
					"is_takeAway":false, // 单个菜品是否打包
				}
			]
		}
	}

##5.下单

####描述

	点击下单按钮，提交后台

####输入 POST

	{
		"post_order":true,
		"token":"",
		"remark":"", // 整单备注
		"menu":[
			{
				"id":1, // 菜品ID
				"price":15, // 价格，价格为0，即赠送
				"take_away":true // true 打包带走
			}
		]
	}

####输出

	// 库存不足时
	{
		"ret":-1, // 非0值， 由后台定义
		"token":"",
		"data":{
			"food_id":1, // 当菜品库存不足时返回数据
		}
	}

    //正常返回
	{
		"ret":0, 
		"token":"",
		"data":{
			"order_id":100099889000, // 订单编号
		}
	}

##6.关闭

####描述

	点击关闭订单或关闭并退款

####输入 POST

	{
		"operate_order":0, // 0 关闭订单， 1 退款， 2 拒绝退款
		"token":"",
		"order_id":"", // 订单编号
		"is_pay":true, // 是否已支付， 已支付关闭并退款流程， 未支付关闭订单
		"reason":"", // 关闭原因
		"password":"*****", // 管理员密码，未支付时传空 
	}

####输出

	{
		"ret":0,
		"token":"",
		"data":{
			"order_id":""
		}
	}

##7.预结账

####描述

	点击预结账

####输入 POST

	{
		"advance_price":true,
		"token":"",
		"order_id":"",
	}

##8.获取支付方式支持

####描述
	
	获取取支付信息

####输入 GET

	{
		"get_pay_type":true,
		"token":""
	}

####输出
	
	{
		"ret":0,
		"token":"",
		"data":{
			"erasure":0, 0 抹除分， 1 取整元
			"pay_type":[
				{
					"weixin":true, // 支持微信
					"scan":0, // 主扫、被扫都支持， 主扫 1， 被扫 2
				},
				{
					"zhifu":true, // 支持支付宝
					"scan":0,
				}
			]
		}
	}

##9.获取支付二维码

####描述
	
	获取商户支付二维码

####输入 GET

	{
		"get_qrcode":true,
		"pay_type":0, // 0微信， 1支付宝
		"token":""
	}

####输出

	{
		"ret":0,
		"token":"",
		"data":{
			"url":"http://xxxx.png"
		}
	}

##9.提交用户二维信息

####描述
	
	扫描用户二维码， 得到二维码信息上传服务器

####输入 POST

	{
		"post_qr":true,
		"pay_type": // 0微信， 1支付宝
		"token":"",
	}

####输出

	{
		"ret":0,
		"token":"",
		"data":{
			"is_pay":true, // 支付成功， false支付失败
		}
	}

##10.结账

####描述

	用户点击结账, 如果点餐界面直接结账，会先发下单请求， 收到order_id, 后结账

####输入 POST

	{
		"post_pay":true,
		"pay_type": 0, // 0 微信， 1 支付宝， 2 现金， 3 刷卡， 4 挂账
		"oder_id":"",
		"price":100, // 实际支付
		"token":""
	}

####输出
	
	{
		"ret":0,
		"token":"",
		"data":{
			"is_pay":true, // 结账成功， false结账失败
		}
	}


# 三、预定管理
### 1.预定列表
#### 描述
    根据预定日期展示预定列表
#### 输入
    {
        "reserve_time": "2017-09-30" //预定日期
    }
#### 输出
    {
        "ret": 0,
        "data": [
            {
                "id": "123456abcde",  //唯一预定编号
                "state": 2,  //状态，0：未签到，1:已签到，2：已取消
                "source": "收银台",  //来源
                "create_user": "admin",  //创建人
                "create_time": "2017-09-30 10:24:56",  //创建时间
                "reserve_time": "2017-09-30 12:00",  //预定时间
                "table": "C001",  //餐桌台号， "":不限定，其他：具体餐桌台号
                "table_type": "散座",  //餐桌类型，"":不限定，其他：具体餐桌类型
                "name": "赵四",  //预订人姓名
                "total_people": 3,  //人数，0:不限定，大于0：具体人数
                "phone": "13512356891",  //电话
                "gender": "男",  //性别
                "remark": "会有小孩子，最好是空间大点的包厢",  //备注
                "cancel_time": "2017-10-09-10:46:23",  //取消时间，状态为已取消时有值
                "cancel_user": "admin",  //取消操作者，状态为已取消时有值
                "cancel_remark": "安排有变，有事耽搁"  //取消理由，状态为已取消时有值
            },
            {
                "id": "123456abcde",  //唯一预定编号
                "state": 0,  //状态，0：未签到，1:已签到，2：已取消
                "source": "收银台",  //来源
                "create_user": "admin",  //创建人
                "create_time": "2017-09-30 10:24:56",  //创建时间
                "reserve_time": "2017-09-30 12:00",  //预定时间
                "table": "C001",  //餐桌台号， "":不限定，其他：具体餐桌台号
                "table_type": "散座",  //餐桌类型，"":不限定，其他：具体餐桌类型
                "name": "赵四",  //预订人姓名
                "total_people": 3,  //人数，0:不限定，大于0：具体人数
                "phone": "13512356891",  //电话
                "gender": "男",  //性别
                "remark": "会有小孩子，最好是空间大点的包厢",  //备注
                "cancel_time": "2017-10-09-10:46:23",  //取消时间，状态为已取消时有值
                "cancel_user": "admin",  //取消操作者，状态为已取消时有值
                "cancel_remark": "安排有变，有事耽搁"  //取消理由，状态为已取消时有值
            },
            {
                "id": "123456abcde",  //唯一预定编号
                "state": 1,  //状态，0：未签到，1:已签到，2：已取消
                "source": "收银台",  //来源
                "create_user": "admin",  //创建人
                "create_time": "2017-09-30 10:24:56",  //创建时间
                "reserve_time": "2017-09-30 12:00",  //预定时间
                "table": "C001",  //餐桌台号， "":不限定，其他：具体餐桌台号
                "table_type": "散座",  //餐桌类型，"":不限定，其他：具体餐桌类型
                "name": "赵四",  //预订人姓名
                "total_people": 3,  //人数，0:不限定，大于0：具体人数
                "phone": "13512356891",  //电话
                "gender": "男",  //性别
                "remark": "会有小孩子，最好是空间大点的包厢",  //备注
                "cancel_time": "2017-10-09-10:46:23",  //取消时间，状态为已取消时有值
                "cancel_user": "admin",  //取消操作者，状态为已取消时有值
                "cancel_remark": "安排有变，有事耽搁"  //取消理由，状态为已取消时有值
            }
        ]
    }
### 2.预定搜索
#### 描述
    根据预定时间段、状态、姓名或电话关键字搜索预定订单
#### 输入
    {
        "start_time": "2017-09-30",  //开始时间
        "end_time": "2017-10-03",  //结束时间
        "state": 0,  //状态，0：未签到，1:已签到，2：已取消
        "keyword": "1354554654646"  //关键字，电话号码或姓名
    }
#### 输出
    {
        "ret": 0,
        "data": [
            {
                "id": "123456abcde",  //唯一预定编号
                "state": 2,  //状态，0：未签到，1:已签到，2：已取消
                "source": "收银台",  //来源
                "create_user": "admin",  //创建人
                "create_time": "2017-09-30 10:24:56",  //创建时间
                "reserve_time": "2017-09-30 12:00",  //预定时间
                "table": "C001",  //餐桌台号， "":不限定，其他：具体餐桌台号
                "table_type": "散座",  //餐桌类型，"":不限定，其他：具体餐桌类型
                "name": "赵四",  //预订人姓名
                "total_people": 3,  //人数，0:不限定，大于0：具体人数
                "phone": "13512356891",  //电话
                "gender": "男",  //性别
                "remark": "会有小孩子，最好是空间大点的包厢",  //备注
                "cancel_time": "2017-10-09-10:46:23",  //取消时间，状态为已取消时有值
                "cancel_user": "admin",  //取消操作者，状态为已取消时有值
                "cancel_remark": "安排有变，有事耽搁"  //取消理由，状态为已取消时有值
            },
            {
                "id": "123456abcde",  //唯一预定编号
                "state": 0,  //状态，0：未签到，1:已签到，2：已取消
                "source": "收银台",  //来源
                "create_user": "admin",  //创建人
                "create_time": "2017-09-30 10:24:56",  //创建时间
                "reserve_time": "2017-09-30 12:00",  //预定时间
                "table": "C001",  //餐桌台号， "":不限定，其他：具体餐桌台号
                "table_type": "散座",  //餐桌类型，"":不限定，其他：具体餐桌类型
                "name": "赵四",  //预订人姓名
                "total_people": 3,  //人数，0:不限定，大于0：具体人数
                "phone": "13512356891",  //电话
                "gender": "男",  //性别
                "remark": "会有小孩子，最好是空间大点的包厢",  //备注
                "cancel_time": "2017-10-09-10:46:23",  //取消时间，状态为已取消时有值
                "cancel_user": "admin",  //取消操作者，状态为已取消时有值
                "cancel_remark": "安排有变，有事耽搁"  //取消理由，状态为已取消时有值
            },
            {
                "id": "123456abcde",  //唯一预定编号
                "state": 1,  //状态，0：未签到，1:已签到，2：已取消
                "source": "收银台",  //来源
                "create_user": "admin",  //创建人
                "create_time": "2017-09-30 10:24:56",  //创建时间
                "reserve_time": "2017-09-30 12:00",  //预定时间
                "table": "C001",  //餐桌台号， "":不限定，其他：具体餐桌台号
                "table_type": "散座",  //餐桌类型，"":不限定，其他：具体餐桌类型
                "name": "赵四",  //预订人姓名
                "total_people": 3,  //人数，0:不限定，大于0：具体人数
                "phone": "13512356891",  //电话
                "gender": "男",  //性别
                "remark": "会有小孩子，最好是空间大点的包厢",  //备注
                "cancel_time": "2017-10-09-10:46:23",  //取消时间，状态为已取消时有值
                "cancel_user": "admin",  //取消操作者，状态为已取消时有值
                "cancel_remark": "安排有变，有事耽搁"  //取消理由，状态为已取消时有值
            }
        ]
    }
### 3.新增/编辑预定
#### 描述
    新建预定订单或修改未签到的预定订单
#### 输入
    新建预定：
    {
        "operate": 0,  //操作类型，0：新建，1：修改
        "name": "赵四",  //预订人姓名
        "gender": "男",  //性别
        "reserve_time": "2017-09-30 12:00",  //预定时间
        "phone": "13512356891",  //电话
        "total_people": 3,  //人数，0:不限定，大于0：具体人数
        "table": "C001",  //餐桌台号， "":不限定，其他：具体餐桌台号
        "table_type": "散座",  //餐桌类型，"":不限定，其他：具体餐桌类型
        "remark": "会有小孩子，最好是空间大点的包厢",  //备注
    }

    修改预定：
    {
        "operate": 1,  //操作类型，0：新建，1：修改
        "id": "123456abcde",  //唯一预定编号, 如果操作类型为新建，此项为""
        "name": "赵四",  //预订人姓名
        "gender": "男",  //性别
        "reserve_time": "2017-09-30 12:00",  //预定时间
        "phone": "13512356891",  //电话
        "total_people": 3,  //人数，0:不限定，大于0：具体人数
        "table": "C001",  //餐桌台号， "":不限定，其他：具体餐桌台号
        "table_type": "散座",  //餐桌类型，"":不限定，其他：具体餐桌类型
        "remark": "会有小孩子，最好是空间大点的包厢",  //备注
    }

    签到：
    {
        "operate": 1,  //操作类型，0：新建，1：修改
        "id": "123456abcde",  //唯一预定编号, 如果操作类型为新建，此项为""
        "state": 1,  //状态，0：未签到，1:已签到，2：已取消
    }

    取消预定：
    {
        "operate": 1,  //操作类型，0：新建，1：修改
        "id": "123456abcde",  //唯一预定编号, 如果操作类型为新建，此项为""
        "state": 2,  //状态，0：未签到，1:已签到，2：已取消
        "cancel_remark": "安排有变，有事耽搁"  //取消理由，状态为已取消时有值
    }
#### 输出
    {
        "ret": 0,
        "data":{}
    }
#四、订单
##4.1获取订单类型列表
####描述
	
	进入订单页面， 终端请求订单类型列表，展示类型列表

####输入 POST

	无

####输出

	{
		"ret":0,
		"desc":"succ",
		"data":{
			"list":[
			{
			    "type_id":0,
			    "type_name":"所有",
			    "filter_list":[{
       			    "filter_name":"创建时间",
    			    "filter_type":1, //0：字符串 1:时间间隔 2：下拉列表 
    			    "filter_meta_data":[
    			        {
    			        "request_key":"from_time",
    			        "default_data":1343143434//时间戳
    			        },
    			        {
    			        "request_key":"to_time",
    			        "default_data":1343143434//时间戳
    			        }    			        
    			    ]
			    },
			    {
       			    "filter_name":"销售方式",
    			    "filter_type":2, //0：字符串 1:时间间隔 2：下拉列表 
    			    "filter_meta_data":
    			        {
    			        "request_key":"sale_type",
    			        "default_data":0,//填for_select的ID
    			        "for_select":[
    			            {
    			            "id":0,
    			            "name":"全部"
    			            },
    			            {
    			            "id":1,
    			            "name":"在店吃"
    			            },
    			            {
    			            "id":2,
    			            "name":"打包"
    			            },
    			            {
    			            "id":3,
    			            "name":"自提"
    			            },
    			            {
    			            "id":4,
    			            "name":"外卖"
    			            }    			            
    			        ]
    			        }
			    },
			    {
       			    "filter_name":"支付方式",
    			    "filter_type":2, //0：字符串 1:时间间隔 2：下拉列表 
    			    "filter_meta_data":
    			        {
    			        "request_key":"pay_type",
    			        "default_data":0,//填for_select的ID
    			        "for_select":[
    			            {
    			            "id":0,
    			            "name":"全部"
    			            },
    			            {
    			            "id":1,
    			            "name":"现金支付"
    			            },
    			            {
    			            "id":2,
    			            "name":"微信支付"
    			            },
    			            {
    			            "id":3,
    			            "name":"支付宝支付"
    			            },
    			            {
    			            "id":4,
    			            "name":"银行卡支付"
    			            },
    			            {
    			            "id":5,
    			            "name":"挂账"
    			            }    			            
    			        ]
    			        }
			    },
			    {
       			    "filter_name":"是否开票",
    			    "filter_type":2, //0：字符串 1:时间间隔 2：下拉列表 
    			    "filter_meta_data":
    			        {
    			        "request_key":"invoice_gave",
    			        "default_data":0,//填for_select的ID
    			        "for_select":[
    			            {
    			            "id":0,
    			            "name":"全部"
    			            },
    			            {
    			            "id":1,
    			            "name":"未开票"
    			            },
    			            {
    			            "id":2,
    			            "name":"已开票"
    			            }		            
    			        ]
    			        }
			    },
			    {
       			    "filter_name":"订单编号",
    			    "filter_type":0, //0：字符串 1:时间间隔 2：下拉列表 
    			    "filter_meta_data":
    			        {
    			        "request_key":"order_num",
    			        "default_data":"输入订单编号"
    			        }
			    },
			    {
       			    "filter_name":"餐桌名称/餐桌号",
    			    "filter_type":0, //0：字符串 1:时间间隔 2：下拉列表 
    			    "filter_meta_data":
    			        {
    			        "request_key":"table_num",
    			        "default_data":"餐桌名称/餐桌号"
    			        }
			    }
			    ],
			    "statistics":[//统计数据
			        {
			        "name":"合计单数:",
			        "default_data":"--",
			        "data_key":"order_count"//填该统计数据返回值的字段名
			        },
			        {
			        "name":"订单金额:",
			        "default_data":"--.-元",
			        "data_key":"order_money"
			        },
			        {
			        "name":"实收金额:",
			        "default_data":"--.-元",
			        "data_key":"real_money"
			        },
			        {
			        "name":"订单均价:",
			        "default_data":"--.-元",
			        "data_key":"oder_average_money"
			        },
			        {
			        "name":"客单价:",
			        "default_data":"--.-元",
			        "data_key":"customer_average_money"
			        }
			    ],
			    "table_titles":[
			        {
			           "name":"订单编号",
			           "data_key":"order_num"//填该数据返回值的字段名     
			        },
			        {
			           "name":"创建时间",
			           "data_key":"create_time"       
			        },
			        {
			           "name":"餐桌名称/餐桌号",
			           "data_key":"table_num"       
			        },
			        {
			           "name":"销售方式",
			           "data_key":"sale_type"       
			        },
			        {
			           "name":"订单金额",
			           "data_key":"order_money"       
			        },
			        {
			           "name":"实收金额",
			           "data_key":"real_money"       
			        },
			        {
			           "name":"支付方式",
			           "data_key":"pay_type"       
			        }
			        ,
			        {
			           "name":"是否开票",
			           "data_key":"invoice_gave"       
			        },
			        {
			           "name":"订单状态",
			           "data_key":"order_state"       
			        },
			        {
			           "name":"操作",
			           "data_key":"detail"//detail为保留key，终端会显示成"查看"      
			        }
			    ]
			},
			{
                "type_id": 1,
                "type_name": "未支付",
                "filter_list": [
                    {
                        "filter_name": "创建时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "销售方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "sale_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "在店吃"
                                },
                                {
                                    "id": 2,
                                    "name": "打包"
                                },
                                {
                                    "id": 3,
                                    "name": "自提"
                                },
                                {
                                    "id": 4,
                                    "name": "外卖"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "订单编号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "order_num",
                            "default_data": "输入订单编号"
                        }
                    },
                    {
                        "filter_name": "餐桌名称/餐桌号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "table_num",
                            "default_data": "餐桌名称/餐桌号"
                        }
                    }
                ],
                "statistics": [
                    {
                        "name": "合计单数:",
                        "default_data": "--",
                        "data_key": "order_count"
                    },
                    {
                        "name": "订单金额:",
                        "default_data": "--.-元",
                        "data_key": "order_money"
                    },
                    {
                        "name": "实收金额:",
                        "default_data": "--.-元",
                        "data_key": "real_money"
                    },
                    {
                        "name": "订单均价:",
                        "default_data": "--.-元",
                        "data_key": "oder_average_money"
                    },
                    {
                        "name": "客单价:",
                        "default_data": "--.-元",
                        "data_key": "customer_average_money"
                    }
                ],
                "table_titles": [
                    {
                        "name": "订单编号",
                        "data_key": "order_num"
                    },
                    {
                        "name": "创建时间",
                        "data_key": "create_time"
                    },
                    {
                        "name": "餐桌名称/餐桌号",
                        "data_key": "table_num"
                    },
                    {
                        "name": "销售方式",
                        "data_key": "sale_type"
                    },
                    {
                        "name": "订单金额",
                        "data_key": "order_money"
                    },
                    {
                        "name": "实收金额",
                        "data_key": "real_money"
                    },
                    {
                        "name": "支付方式",
                        "data_key": "pay_type"
                    },
                    {
                        "name": "是否开票",
                        "data_key": "invoice_gave"
                    },
                    {
                        "name": "订单状态",
                        "data_key": "order_state"
                    },
                    {
                        "name": "操作",
                        "data_key": "detail"
                    }
                ]
            },
             {
                "type_id": 2,
                "type_name": "已支付",
                "filter_list": [
                    {
                        "filter_name": "创建时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "支付时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time_pay",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time_pay",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "销售方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "sale_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "在店吃"
                                },
                                {
                                    "id": 2,
                                    "name": "打包"
                                },
                                {
                                    "id": 3,
                                    "name": "自提"
                                },
                                {
                                    "id": 4,
                                    "name": "外卖"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "支付方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "pay_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "现金支付"
                                },
                                {
                                    "id": 2,
                                    "name": "微信支付"
                                },
                                {
                                    "id": 3,
                                    "name": "支付宝支付"
                                },
                                {
                                    "id": 4,
                                    "name": "银行卡支付"
                                },
                                {
                                    "id": 5,
                                    "name": "挂账"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "是否开票",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "invoice_gave",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "未开票"
                                },
                                {
                                    "id": 2,
                                    "name": "已开票"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "订单编号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "order_num",
                            "default_data": "输入订单编号"
                        }
                    },
                    {
                        "filter_name": "餐桌名称/餐桌号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "table_num",
                            "default_data": "餐桌名称/餐桌号"
                        }
                    }
                ],
                "statistics": [
                    {
                        "name": "合计单数:",
                        "default_data": "--",
                        "data_key": "order_count"
                    },
                    {
                        "name": "订单金额:",
                        "default_data": "--.-元",
                        "data_key": "order_money"
                    },
                    {
                        "name": "实收金额:",
                        "default_data": "--.-元",
                        "data_key": "real_money"
                    },
                    {
                        "name": "订单均价:",
                        "default_data": "--.-元",
                        "data_key": "oder_average_money"
                    },
                    {
                        "name": "客单价:",
                        "default_data": "--.-元",
                        "data_key": "customer_average_money"
                    }
                ],
                "table_titles": [
                    {
                        "name": "订单编号",
                        "data_key": "order_num"
                    },
                    {
                        "name": "创建时间",
                        "data_key": "create_time"
                    },
                    {
                        "name": "支付时间",
                        "data_key": "pay_time"
                    },
                    {
                        "name": "餐桌名称/餐桌号",
                        "data_key": "table_num"
                    },
                    {
                        "name": "销售方式",
                        "data_key": "sale_type"
                    },
                    {
                        "name": "订单金额",
                        "data_key": "order_money"
                    },
                    {
                        "name": "实收金额",
                        "data_key": "real_money"
                    },
                    {
                        "name": "支付方式",
                        "data_key": "pay_type"
                    },
                    {
                        "name": "是否开票",
                        "data_key": "invoice_gave"
                    },
                    {
                        "name": "订单状态",
                        "data_key": "order_state"
                    },
                    {
                        "name": "操作",
                        "data_key": "detail"
                    }
                ]
            },
            {
                "type_id": 0,
                "type_name": "所有",
                "filter_list": [
                    {
                        "filter_name": "创建时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "销售方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "sale_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "在店吃"
                                },
                                {
                                    "id": 2,
                                    "name": "打包"
                                },
                                {
                                    "id": 3,
                                    "name": "自提"
                                },
                                {
                                    "id": 4,
                                    "name": "外卖"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "支付方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "pay_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "现金支付"
                                },
                                {
                                    "id": 2,
                                    "name": "微信支付"
                                },
                                {
                                    "id": 3,
                                    "name": "支付宝支付"
                                },
                                {
                                    "id": 4,
                                    "name": "银行卡支付"
                                },
                                {
                                    "id": 5,
                                    "name": "挂账"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "是否开票",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "invoice_gave",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "未开票"
                                },
                                {
                                    "id": 2,
                                    "name": "已开票"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "订单编号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "order_num",
                            "default_data": "输入订单编号"
                        }
                    },
                    {
                        "filter_name": "餐桌名称/餐桌号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "table_num",
                            "default_data": "餐桌名称/餐桌号"
                        }
                    }
                ],
                "statistics": [
                    {
                        "name": "合计单数:",
                        "default_data": "--",
                        "data_key": "order_count"
                    },
                    {
                        "name": "订单金额:",
                        "default_data": "--.-元",
                        "data_key": "order_money"
                    },
                    {
                        "name": "实收金额:",
                        "default_data": "--.-元",
                        "data_key": "real_money"
                    },
                    {
                        "name": "订单均价:",
                        "default_data": "--.-元",
                        "data_key": "oder_average_money"
                    },
                    {
                        "name": "客单价:",
                        "default_data": "--.-元",
                        "data_key": "customer_average_money"
                    }
                ],
                "table_titles": [
                    {
                        "name": "订单编号",
                        "data_key": "order_num"
                    },
                    {
                        "name": "创建时间",
                        "data_key": "create_time"
                    },
                    {
                        "name": "餐桌名称/餐桌号",
                        "data_key": "table_num"
                    },
                    {
                        "name": "销售方式",
                        "data_key": "sale_type"
                    },
                    {
                        "name": "订单金额",
                        "data_key": "order_money"
                    },
                    {
                        "name": "实收金额",
                        "data_key": "real_money"
                    },
                    {
                        "name": "支付方式",
                        "data_key": "pay_type"
                    },
                    {
                        "name": "是否开票",
                        "data_key": "invoice_gave"
                    },
                    {
                        "name": "订单状态",
                        "data_key": "order_state"
                    },
                    {
                        "name": "操作",
                        "data_key": "detail"
                    }
                ]
            },
            {
                "type_id": 3,
                "type_name": "挂账",
                "filter_list": [
                    {
                        "filter_name": "创建时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "销售方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "sale_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "在店吃"
                                },
                                {
                                    "id": 2,
                                    "name": "打包"
                                },
                                {
                                    "id": 3,
                                    "name": "自提"
                                },
                                {
                                    "id": 4,
                                    "name": "外卖"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "订单编号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "order_num",
                            "default_data": "输入订单编号"
                        }
                    },
                    {
                        "filter_name": "餐桌名称/餐桌号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "table_num",
                            "default_data": "餐桌名称/餐桌号"
                        }
                    }
                ],
                "statistics": [
                    {
                        "name": "合计单数:",
                        "default_data": "--",
                        "data_key": "order_count"
                    },
                    {
                        "name": "订单金额:",
                        "default_data": "--.-元",
                        "data_key": "order_money"
                    },
                    {
                        "name": "挂账金额:",
                        "default_data": "--.-元",
                        "data_key": "delay_pay_money"
                    },
                    {
                        "name": "订单均价:",
                        "default_data": "--.-元",
                        "data_key": "oder_average_money"
                    },
                    {
                        "name": "客单价:",
                        "default_data": "--.-元",
                        "data_key": "customer_average_money"
                    }
                ],
                "table_titles": [
                    {
                        "name": "订单编号",
                        "data_key": "order_num"
                    },
                    {
                        "name": "创建时间",
                        "data_key": "create_time"
                    },
                    {
                        "name": "餐桌名称/餐桌号",
                        "data_key": "table_num"
                    },
                    {
                        "name": "销售方式",
                        "data_key": "sale_type"
                    },
                    {
                        "name": "订单金额",
                        "data_key": "order_money"
                    },
                    {
                        "name": "操作",
                        "data_key": "detail"
                    }
                ]
            },
            {
                "type_id": 0,
                "type_name": "所有",
                "filter_list": [
                    {
                        "filter_name": "创建时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "销售方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "sale_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "在店吃"
                                },
                                {
                                    "id": 2,
                                    "name": "打包"
                                },
                                {
                                    "id": 3,
                                    "name": "自提"
                                },
                                {
                                    "id": 4,
                                    "name": "外卖"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "支付方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "pay_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "现金支付"
                                },
                                {
                                    "id": 2,
                                    "name": "微信支付"
                                },
                                {
                                    "id": 3,
                                    "name": "支付宝支付"
                                },
                                {
                                    "id": 4,
                                    "name": "银行卡支付"
                                },
                                {
                                    "id": 5,
                                    "name": "挂账"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "是否开票",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "invoice_gave",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "未开票"
                                },
                                {
                                    "id": 2,
                                    "name": "已开票"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "订单编号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "order_num",
                            "default_data": "输入订单编号"
                        }
                    },
                    {
                        "filter_name": "餐桌名称/餐桌号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "table_num",
                            "default_data": "餐桌名称/餐桌号"
                        }
                    }
                ],
                "statistics": [
                    {
                        "name": "合计单数:",
                        "default_data": "--",
                        "data_key": "order_count"
                    },
                    {
                        "name": "订单金额:",
                        "default_data": "--.-元",
                        "data_key": "order_money"
                    },
                    {
                        "name": "实收金额:",
                        "default_data": "--.-元",
                        "data_key": "real_money"
                    },
                    {
                        "name": "订单均价:",
                        "default_data": "--.-元",
                        "data_key": "oder_average_money"
                    },
                    {
                        "name": "客单价:",
                        "default_data": "--.-元",
                        "data_key": "customer_average_money"
                    }
                ],
                "table_titles": [
                    {
                        "name": "订单编号",
                        "data_key": "order_num"
                    },
                    {
                        "name": "创建时间",
                        "data_key": "create_time"
                    },
                    {
                        "name": "餐桌名称/餐桌号",
                        "data_key": "table_num"
                    },
                    {
                        "name": "销售方式",
                        "data_key": "sale_type"
                    },
                    {
                        "name": "订单金额",
                        "data_key": "order_money"
                    },
                    {
                        "name": "实收金额",
                        "data_key": "real_money"
                    },
                    {
                        "name": "支付方式",
                        "data_key": "pay_type"
                    },
                    {
                        "name": "是否开票",
                        "data_key": "invoice_gave"
                    },
                    {
                        "name": "订单状态",
                        "data_key": "order_state"
                    },
                    {
                        "name": "操作",
                        "data_key": "detail"
                    }
                ]
            },
            {
                "type_id": 4,
                "type_name": "已反结",
                "filter_list": [
                    {
                        "filter_name": "创建时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "反结时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time_back_pay",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time_back_pay",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "销售方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "sale_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "在店吃"
                                },
                                {
                                    "id": 2,
                                    "name": "打包"
                                },
                                {
                                    "id": 3,
                                    "name": "自提"
                                },
                                {
                                    "id": 4,
                                    "name": "外卖"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "支付方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "pay_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "现金支付"
                                },
                                {
                                    "id": 2,
                                    "name": "微信支付"
                                },
                                {
                                    "id": 3,
                                    "name": "支付宝支付"
                                },
                                {
                                    "id": 4,
                                    "name": "银行卡支付"
                                },
                                {
                                    "id": 5,
                                    "name": "挂账"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "是否开票",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "invoice_gave",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "未开票"
                                },
                                {
                                    "id": 2,
                                    "name": "已开票"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "订单编号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "order_num",
                            "default_data": "输入订单编号"
                        }
                    },
                    {
                        "filter_name": "餐桌名称/餐桌号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "table_num",
                            "default_data": "餐桌名称/餐桌号"
                        }
                    }
                ],
                "statistics": [
                    {
                        "name": "合计单数:",
                        "default_data": "--",
                        "data_key": "order_count"
                    },
                    {
                        "name": "订单金额:",
                        "default_data": "--.-元",
                        "data_key": "order_money"
                    },
                    {
                        "name": "反结金额:",
                        "default_data": "--.-元",
                        "data_key": "back_pay_money"
                    },
                    {
                        "name": "订单均价:",
                        "default_data": "--.-元",
                        "data_key": "oder_average_money"
                    },
                    {
                        "name": "客单价:",
                        "default_data": "--.-元",
                        "data_key": "customer_average_money"
                    }
                ],
                "table_titles": [
                    {
                        "name": "订单编号",
                        "data_key": "order_num"
                    },
                    {
                        "name": "创建时间",
                        "data_key": "create_time"
                    },
                    {
                        "name": "反结时间",
                        "data_key": "back_pay_time"
                    },
                    {
                        "name": "餐桌名称/餐桌号",
                        "data_key": "table_num"
                    },
                    {
                        "name": "销售方式",
                        "data_key": "sale_type"
                    },
                    {
                        "name": "订单金额",
                        "data_key": "order_money"
                    },
                    {
                        "name": "实收金额",
                        "data_key": "real_money"
                    },
                    {
                        "name": "支付方式",
                        "data_key": "pay_type"
                    },
                    {
                        "name": "是否开票",
                        "data_key": "invoice_gave"
                    },
                    {
                        "name": "操作",
                        "data_key": "detail"
                    }
                ]
            },
            {
                "type_id": 0,
                "type_name": "所有",
                "filter_list": [
                    {
                        "filter_name": "创建时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "销售方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "sale_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "在店吃"
                                },
                                {
                                    "id": 2,
                                    "name": "打包"
                                },
                                {
                                    "id": 3,
                                    "name": "自提"
                                },
                                {
                                    "id": 4,
                                    "name": "外卖"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "支付方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "pay_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "现金支付"
                                },
                                {
                                    "id": 2,
                                    "name": "微信支付"
                                },
                                {
                                    "id": 3,
                                    "name": "支付宝支付"
                                },
                                {
                                    "id": 4,
                                    "name": "银行卡支付"
                                },
                                {
                                    "id": 5,
                                    "name": "挂账"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "是否开票",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "invoice_gave",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "未开票"
                                },
                                {
                                    "id": 2,
                                    "name": "已开票"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "订单编号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "order_num",
                            "default_data": "输入订单编号"
                        }
                    },
                    {
                        "filter_name": "餐桌名称/餐桌号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "table_num",
                            "default_data": "餐桌名称/餐桌号"
                        }
                    }
                ],
                "statistics": [
                    {
                        "name": "合计单数:",
                        "default_data": "--",
                        "data_key": "order_count"
                    },
                    {
                        "name": "订单金额:",
                        "default_data": "--.-元",
                        "data_key": "order_money"
                    },
                    {
                        "name": "实收金额:",
                        "default_data": "--.-元",
                        "data_key": "real_money"
                    },
                    {
                        "name": "订单均价:",
                        "default_data": "--.-元",
                        "data_key": "oder_average_money"
                    },
                    {
                        "name": "客单价:",
                        "default_data": "--.-元",
                        "data_key": "customer_average_money"
                    }
                ],
                "table_titles": [
                    {
                        "name": "订单编号",
                        "data_key": "order_num"
                    },
                    {
                        "name": "创建时间",
                        "data_key": "create_time"
                    },
                    {
                        "name": "餐桌名称/餐桌号",
                        "data_key": "table_num"
                    },
                    {
                        "name": "销售方式",
                        "data_key": "sale_type"
                    },
                    {
                        "name": "订单金额",
                        "data_key": "order_money"
                    },
                    {
                        "name": "实收金额",
                        "data_key": "real_money"
                    },
                    {
                        "name": "支付方式",
                        "data_key": "pay_type"
                    },
                    {
                        "name": "是否开票",
                        "data_key": "invoice_gave"
                    },
                    {
                        "name": "订单状态",
                        "data_key": "order_state"
                    },
                    {
                        "name": "操作",
                        "data_key": "detail"
                    }
                ]
            },
            {
                "type_id": 5,
                "type_name": "退款成功",
                "filter_list": [
                    {
                        "filter_name": "创建时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "退款时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time_refund",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time_refund",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "销售方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "sale_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "在店吃"
                                },
                                {
                                    "id": 2,
                                    "name": "打包"
                                },
                                {
                                    "id": 3,
                                    "name": "自提"
                                },
                                {
                                    "id": 4,
                                    "name": "外卖"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "支付方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "pay_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "现金支付"
                                },
                                {
                                    "id": 2,
                                    "name": "微信支付"
                                },
                                {
                                    "id": 3,
                                    "name": "支付宝支付"
                                },
                                {
                                    "id": 4,
                                    "name": "银行卡支付"
                                },
                                {
                                    "id": 5,
                                    "name": "挂账"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "是否开票",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "invoice_gave",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "未开票"
                                },
                                {
                                    "id": 2,
                                    "name": "已开票"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "订单编号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "order_num",
                            "default_data": "输入订单编号"
                        }
                    },
                    {
                        "filter_name": "餐桌名称/餐桌号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "table_num",
                            "default_data": "餐桌名称/餐桌号"
                        }
                    }
                ],
                "statistics": [
                    {
                        "name": "合计单数:",
                        "default_data": "--",
                        "data_key": "order_count"
                    },
                    {
                        "name": "订单金额:",
                        "default_data": "--.-元",
                        "data_key": "order_money"
                    },
                    {
                        "name": "退款金额:",
                        "default_data": "--.-元",
                        "data_key": "refund_money"
                    },
                    {
                        "name": "订单均价:",
                        "default_data": "--.-元",
                        "data_key": "oder_average_money"
                    },
                    {
                        "name": "客单价:",
                        "default_data": "--.-元",
                        "data_key": "customer_average_money"
                    }
                ],
                "table_titles": [
                    {
                        "name": "订单编号",
                        "data_key": "order_num"
                    },
                    {
                        "name": "创建时间",
                        "data_key": "create_time"
                    },
                    {
                        "name": "退款时间",
                        "data_key": "refund_time"
                    },
                    {
                        "name": "餐桌名称/餐桌号",
                        "data_key": "table_num"
                    },
                    {
                        "name": "销售方式",
                        "data_key": "sale_type"
                    },
                    {
                        "name": "订单金额",
                        "data_key": "order_money"
                    },
                    {
                        "name": "实收金额",
                        "data_key": "real_money"
                    },
                    {
                        "name": "支付方式",
                        "data_key": "pay_type"
                    },
                    {
                        "name": "是否开票",
                        "data_key": "invoice_gave"
                    },
                    {
                        "name": "操作",
                        "data_key": "detail"
                    }
                ]
            },
            {
                "type_id": 6,
                "type_name": "退款失败",
                "filter_list": [
                    {
                        "filter_name": "创建时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "拒绝退款时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time_reject_refund",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time_reject_refund",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "销售方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "sale_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "在店吃"
                                },
                                {
                                    "id": 2,
                                    "name": "打包"
                                },
                                {
                                    "id": 3,
                                    "name": "自提"
                                },
                                {
                                    "id": 4,
                                    "name": "外卖"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "支付方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "pay_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "现金支付"
                                },
                                {
                                    "id": 2,
                                    "name": "微信支付"
                                },
                                {
                                    "id": 3,
                                    "name": "支付宝支付"
                                },
                                {
                                    "id": 4,
                                    "name": "银行卡支付"
                                },
                                {
                                    "id": 5,
                                    "name": "挂账"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "是否开票",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "invoice_gave",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "未开票"
                                },
                                {
                                    "id": 2,
                                    "name": "已开票"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "订单编号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "order_num",
                            "default_data": "输入订单编号"
                        }
                    },
                    {
                        "filter_name": "餐桌名称/餐桌号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "table_num",
                            "default_data": "餐桌名称/餐桌号"
                        }
                    }
                ],
                "statistics": [
                    {
                        "name": "合计单数:",
                        "default_data": "--",
                        "data_key": "order_count"
                    },
                    {
                        "name": "订单金额:",
                        "default_data": "--.-元",
                        "data_key": "order_money"
                    },
                    {
                        "name": "退款金额:",
                        "default_data": "--.-元",
                        "data_key": "refund_money"
                    },
                    {
                        "name": "订单均价:",
                        "default_data": "--.-元",
                        "data_key": "oder_average_money"
                    },
                    {
                        "name": "客单价:",
                        "default_data": "--.-元",
                        "data_key": "customer_average_money"
                    }
                ],
                "table_titles": [
                    {
                        "name": "订单编号",
                        "data_key": "order_num"
                    },
                    {
                        "name": "创建时间",
                        "data_key": "create_time"
                    },
                    {
                        "name": "拒绝退款时间",
                        "data_key": "reject_refund_time"
                    },
                    {
                        "name": "餐桌名称/餐桌号",
                        "data_key": "table_num"
                    },
                    {
                        "name": "销售方式",
                        "data_key": "sale_type"
                    },
                    {
                        "name": "订单金额",
                        "data_key": "order_money"
                    },
                    {
                        "name": "实收金额",
                        "data_key": "real_money"
                    },
                    {
                        "name": "支付方式",
                        "data_key": "pay_type"
                    },
                    {
                        "name": "是否开票",
                        "data_key": "invoice_gave"
                    },
                    {
                        "name": "操作",
                        "data_key": "detail"
                    }
                ]
            },
            {
                "type_id": 7,
                "type_name": "已关闭",
                "filter_list": [
                    {
                        "filter_name": "创建时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "关闭时间",
                        "filter_type": 1,
                        "filter_meta_data": [
                            {
                                "request_key": "from_time_close",
                                "default_data": 1343143434
                            },
                            {
                                "request_key": "to_time_close",
                                "default_data": 1343143434
                            }
                        ]
                    },
                    {
                        "filter_name": "销售方式",
                        "filter_type": 2,
                        "filter_meta_data": {
                            "request_key": "sale_type",
                            "default_data": 0,
                            "for_select": [
                                {
                                    "id": 0,
                                    "name": "全部"
                                },
                                {
                                    "id": 1,
                                    "name": "在店吃"
                                },
                                {
                                    "id": 2,
                                    "name": "打包"
                                },
                                {
                                    "id": 3,
                                    "name": "自提"
                                },
                                {
                                    "id": 4,
                                    "name": "外卖"
                                }
                            ]
                        }
                    },
                    {
                        "filter_name": "订单编号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "order_num",
                            "default_data": "输入订单编号"
                        }
                    },
                    {
                        "filter_name": "餐桌名称/餐桌号",
                        "filter_type": 0,
                        "filter_meta_data": {
                            "request_key": "table_num",
                            "default_data": "餐桌名称/餐桌号"
                        }
                    }
                ],
                "statistics": [
                    {
                        "name": "合计单数:",
                        "default_data": "--",
                        "data_key": "order_count"
                    },
                    {
                        "name": "订单金额:",
                        "default_data": "--.-元",
                        "data_key": "order_money"
                    },
                    {
                        "name": "未支付金额:",
                        "default_data": "--.-元",
                        "data_key": "unpay_money"
                    },
                    {
                        "name": "订单均价:",
                        "default_data": "--.-元",
                        "data_key": "oder_average_money"
                    },
                    {
                        "name": "客单价:",
                        "default_data": "--.-元",
                        "data_key": "customer_average_money"
                    }
                ],
                "table_titles": [
                    {
                        "name": "订单编号",
                        "data_key": "order_num"
                    },
                    {
                        "name": "创建时间",
                        "data_key": "create_time"
                    },
                    {
                        "name": "关闭时间",
                        "data_key": "close_time"
                    },
                    {
                        "name": "餐桌名称/餐桌号",
                        "data_key": "table_num"
                    },
                    {
                        "name": "销售方式",
                        "data_key": "sale_type"
                    },
                    {
                        "name": "订单金额",
                        "data_key": "order_money"
                    },
                    {
                        "name": "应收金额",
                        "data_key": "need_money"
                    },
                    {
                        "name": "支付方式",
                        "data_key": "pay_type"
                    },
                    {
                        "name": "操作",
                        "data_key": "detail"
                    }
                ]
            }
			]
		}
	}
##4.2获取某类订单报表
####描述
	
	进入某类订单页面， 终端请求获取该类订单的报表数据

####输入 POST

	{
	    "type_id":0, //4.1里面定义的id值
	    "page_num":10,//一页多少条数据
	    "last_order_num":"",分页控件使用,第一页传0
	    //以下筛选条件字段非必填
	    "from_time":1234567,            //创建时间-开始
	    "to_time":1234567,              //创建时间-结束
	    "from_time_pay":1234567,        //支付时间-开始
   	    "to_time_pay":1234567,          //支付时间-结束
   	    "from_time_back_pay":1234567,   //反结时间-开始
   	    "to_time_back_pay":1234567,     //反结时间-结束
	    "from_time_refund":1234567,     //退款时间-开始
   	    "to_time_refund":1234567,       //退款时间-结束
   	    "from_time_reject_refund":12345,//拒绝退款时间-开始
   	    "to_time_reject_refund":1234567,//拒绝退款时间-结束
   	    "sale_type":0,                  //销售方式
   	    "pay_type":0,                   //支付方式
   	    "invoice_gave":0,               //是否开票
   	    "order_num":"",                 //订单编号
   	    "table_num":""                  //餐桌号
	}

####输出
    {
        "ret":0,
		"data":{
		    "statistics":{//统计信息
		        "order_count":50,      //合计单数
		        "order_money":5000.00, //订单金额
                "pay_money":100.00,    //已支付金额
		        "unpay_money":200.00,  //未支付金额
                "delay_pay_money":120.00,//挂账金额
                "back_pay_money":150.00, //反结账金额
		        "refund_money":300.00,   //已退款金额	
		        "oder_average_money":400.00,//订单均价
		        "customer_average_money":80 //客单价
		    },
		    "table_data":[//表格数据
		        {
		        "order_num":"12345678",     //订单编号
		        "create_time":123456789,    //创建时间
		        "pay_time":1232456789,      //支付时间
                "back_pay_time":1232456789, //反结时间
		        "reject_refund_time":123456,//拒绝退款时间
		        "refund_time":123456,  //退款时间
		        "close_time":123456789,//关闭时间
                "table_num":"C001",    //餐桌号
                "sale_type":0,         //销售方式4.1里定义在"filter_name": "销售方式",for_select里的id值
                "order_money":28.00,//订单金额
                "real_money":28.00, //实收金额
                "need_money":28.00, //应收金额
                "pay_type":0,       //支付方式，4.1里定义在"filter_name": "支付方式",for_select里的id值
                "invoice_gave":0,   //是否开票，4.1里定义在"filter_name": "是否开票",for_select里的id值
                "order_state":0     //订单状态，0:已关闭 1、未支付 2、已支付 3、已反结 4、挂账
		    }
		    ]
		}
    }
##4.2获取订单详情
####描述
	
	查看某个订单详情

####输入 POST
    {
        "order_num":""
    }
####输出
    {
        "ret":0,
		"data":{
		    "order_info":{
		        "order_state":"已支付",
    		    "order_num":"C001",
    		    "order_src":"订单来源",
    		    "order_who":"点单人",
    		    "order_time":1234567,//下单时间
    		    "serial_number":10,
    		    "table_num":"C001",
    		    "table_area":"餐桌区域",
    		    "table_type":"餐桌类型",
    		    "customer_num":2,
    		    "sale_type":"销售方式",
    		    "invoice_gave":"未开票",
		    },
		    "pay_info":{
		        "pay_state":"已支付",
		        "order_money":"订单金额",
		        "reduce_money":"减免",
		        "need_money":"应收",
		        "real_money":"实收",
		        "take_off_zero":"抹零",
		        "cashier":"收银员",
		        "pay_time":123456578,//支付时间
		        "pay_type":"现金",
		    },
		    "product_info":{
		        "total_price":"总计金额",
		        "remark":"备注",
		        "product_list":[{
		        "product_name":"商品名称",
		        "unit_price":"单价",
		        "number":1,
		        "count_price":"价格"
		        }]
		    },
		    "oper_info":[
		        {
		        "lable":"结账",
		        "id":0,
		        "need_dlg_confirm":false,
		        "need_admin_confirm":false
		        },
		        {
		        "lable":"开发票",
		        "id":1,
		        "need_dlg_confirm":false,
		        "need_admin_confirm":false
		        },
		        {
		        "lable":"反结账",
		        "id":2,
		        "need_dlg_confirm":true,
		        "need_admin_confirm":true
		        },
		        {
		        "lable":"退款",
		        "id":3,
		        "need_dlg_confirm":true,
		        "need_admin_confirm":true
		        },
		        {
		        "lable":"红冲",
		        "id":4,
		        "need_dlg_confirm":true,
		        "need_admin_confirm":true
		        },
		        {
		        "lable":"关闭订单",
		        "id":5,
		        "need_dlg_confirm":true,
		        "need_admin_confirm":false
		        }
		    ]
		}
	}

##4.3订单操作
####描述
	
	对订单结账、开发票，反结账、退款、红冲或关闭等操作

####输入 POST
    {
        "oper_id":0,//填4.2中 oper_info中定义的id
        "admin_pwd":"",
        "oper_reason":"",
    }
####输出
    {
        "ret":0,
		"data":{
		    "oper_ret":0,
		    "oper_desc":""
		}
	}
	
#五、公告
#六、设置
#七、个人

