<?php
header('Content-Type:text/html;charset=utf-8');
require_once("Request.php");
require_once("current_dir_env.php");
require_once("const.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
use \Pub\Mongodb as Mgo;


/*
 * appkey,appsecret  高朋开放平台id secret
 * 测试环境与生产环境appkey与appsecret不共用。
 * 测试环境账号请咨询相关人员
*/
//测试商户税号：440301999988780
//$a = new GoldenOpenPlat\Request("kCQxS7OBx1NpVreVD2eIYPNbjJsk1kwV", "RTB4US6y0ddvYIHv7HTEA7m0cKpuLqvpagBt6xkb7wmmzZOS");


//$post = ['appid'=>'wxe6ee6bc6898df9df','full_name'=>'深圳前海赛领科技有限公司','short_name'=>'欣吃货'];
//设置环境及发出请求
//$c = $a->setEnv("test")->requestPost("/card/create-template", $post);

//var_dump($c);
//array(3) { ["body"]=> string(110) "{ "code": 0, "msg": "success", "data": { "card_id": "plN5twTuzQuP0PxtWE0jv2h7FmQ0" } }" ["error"]=> string(0) "" ["statusCode"]=> int(200) }

$goods_order_id = 'GO3100';
$a = new GoldenOpenPlat\Request("kCQxS7OBx1NpVreVD2eIYPNbjJsk1kwV", "RTB4US6y0ddvYIHv7HTEA7m0cKpuLqvpagBt6xkb7wmmzZOS");
$mgo  = new Mgo\GoodsOrder;
$info = $mgo->GetGoodsOrderById($goods_order_id);
$goods_info=[];
$money = 0;
foreach ($info->goods_list as $key => $value)
{
	$list = (object)array();
	if($value->invoice == 2)
	{
		$list->name = $value->goods_name;
		$list->models = $value->spec_name;
		$list->unit = "个";
		$list->total_price = (string)$value->invoice_price;
		$list->total = $value->goods_num;
		$list->tax_code = '3040201010000000000';
		$list->tax_rate = '0.6';
		$list->tax_amount = (string)round($list->total_price*0.6,2);
		array_push($goods_info, $list);
		$money += $value->invoice_price;
	}
}

$post = [
	'order_id'           => time() . "_" . $goods_order_id,
	'money'              => $money,
	'timestamp'          => time(),
	'type'               =>  0,//授权类型，0：开票授权，1：填写字段开票授权，2：领票授权
	'source'             => 'web',//来源.app：app开票，web：微信h5开票，wap：普通网页开票，wxa：小程序开发票
	'callback_url'       => 'http://mart.jzzwlcm.com/php/invoice_notify.php',//异步推送接收地址
	'taxpayer_num'       => '440301999988780',//开票方纳税人识别号
	'tax_name'           => '欣吃货',//	商户名称
	'buyer_title'        => $info->invoice->invoice_title,//购方名称
	'buyer_title_type'   => $info->invoice->title_type,//发票抬头，1个人/事业单位，2企业
	'buyer_taxcode'      => $info->invoice->duty_paragraph,//购货方识别号，当抬头为2时，必填
	'buyer_phone'        => $info->invoice->unit_phone,//购买方电话
	'taker_tel'          => $info->invoice->phone,//	收票人手机
	'buyer_email'        => $info->invoice->email,//购方邮箱
	'buyer_bank_account' => $info->invoice->bank_account,//购买方银行账号
	'buyer_bank_name'    => $info->invoice->bank_name,//	购买方开户银行
	'buyer_address'      => $info->invoice->unit_address,//购方地址
	'goods_info'         => $goods_info,
	//'card_id'            => 'plN5twfy9H8PJ6EOQLjPpPNcgXRs'
];
//设置环境及发出请求
$c = $a->setEnv("test")->requestPost("/authorize/authurl-invoice-card", $post);
var_dump($c);

// {
//     "name": "商品1",商品名
//     "tax_rate": "0.13",税率，范围0-1
//     "models": "XYZ",商品规格
//     "unit": "个",计量单位
//     "total_price": "10",不含税商品金额
//     "tax_amount":"1.3",税额（精确到2位）
//     "total": 2,商品数量（精确到8位）
//     "tax_code": "3040201010000000000"税目编码（以财务提供为准）
// }