<?php
require_once("Request.php");
require_once("current_dir_env.php");
require_once("const.php");
require_once("cfg.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
use \Pub\Mongodb as Mgo;

function OpenInvoice(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $goods_order_id = $_["goods_order_id"];

    if(!$goods_order_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo  = new Mgo\GoodsOrder;
    $info = $mgo->GetGoodsOrderById($goods_order_id);
    if($info->invoice_status != 1 || !$info->pay_time)
    {
    	LogErr("invoice_status:".$info->invoice_status);
    	return errcode::NOT_INVOICE;
    }
	$goods_info=[];
	$money = 0;
	//我们分两类吧，一个硬件一个软件服务，硬件的编码是：1090511000000000000    软件服务的编码是：3040201000000000000，税率都是3%
	foreach ($info->goods_list as $key => $value)
	{
		$list = (object)array();
		if($value->invoice == 2)
		{
			$goods = \Cache\Goods::Get($value->goods_id);
			switch ($goods->goods_type) {
				case 1:
					$tax_code = '1090511000000000000';
					break;
				case 2:
					$tax_code = '3040201000000000000';
					break;
				default:
					LogErr($value->goods_id."goods_type err");
        			return errcode::PARAM_ERR;
					break;
			}
			$list->tax_code = $tax_code;//<<<<<<<<<<<<<<<<<<<<<<<<<<税目编码
			$list->tax_rate = Cfg::instance()->invoice->tax_rate;//<<<<<<<<<<<<<<<<<<<<<<<<<<税率
			$list->name = $value->goods_name;
			$list->models = $value->spec_name;
			$list->unit = "个";
			$list->total_price = (string)round($value->invoice_price/(1+$list->tax_rate),2);
			$list->total = $value->goods_num;
			$list->tax_amount = (string)round($list->total_price*$list->tax_rate,2);
			array_push($goods_info, $list);
			$money += $list->total_price;
		}
	}
	if($money<=0)
	{
		LogErr("order money:".$money);
    	return errcode::NOT_INVOICE;
	}
	$a = new GoldenOpenPlat\Request(Cfg::instance()->invoice->appkey, Cfg::instance()->invoice->appsecret);
	$post = [
		//'appid'              => Cfg::instance()->invoice->appid,
		'order_id'           => "sl_goods_order_" . $goods_order_id,
		'money'              => $money,
		'timestamp'          => time(),
		'type'               =>  0,//授权类型，0：开票授权，1：填写字段开票授权，2：领票授权
		'source'             => 'web',//来源.app：app开票，web：微信h5开票，wap：普通网页开票，wxa：小程序开发票
		'redirect_url'       => 'http://mart.jzzwlcm.com/weixin/#/orderinfo?orderid='.$goods_order_id,
		'callback_url'       => Cfg::instance()->invoice->callback_url,//异步推送接收地址
		'taxpayer_num'       => Cfg::instance()->invoice->taxpayer_num,//开票方纳税人识别号
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
		'card_id'            => Cfg::instance()->invoice->card_id
	];
	//设置环境及发出请求
	$c = $a->setEnv('test')->requestPost("/authorize/authurl-invoice-card", $post);
	$req = json_decode($c['body']);
	if($req->code != 0)
	{
		LogErr($req->msg);
        return $req->code;
	}
	$resp = (object)array(
        'url' => $req->data->auth_url
    );
	return 0;
}

function GetInvoice(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $goods_order_id = $_["goods_order_id"];

    if(!$goods_order_id)
    {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }
    $mgo  = new Mgo\GoodsOrder;
    $info = $mgo->GetGoodsOrderById($goods_order_id);

	$a = new GoldenOpenPlat\Request(Cfg::instance()->invoice->appkey, Cfg::instance()->invoice->appsecret);
	$post = [
		'order_id'           => "sl_goods_order_" . $goods_order_id,
		'taxpayer_num'       => Cfg::instance()->invoice->taxpayer_num//开票方纳税人识别号
	];
	//设置环境及发出请求
	$c = $a->setEnv('test')->requestPost("/invoice/status", $post);
	$req = json_decode($c['body']);
	if($req->code != 0)
	{
		LogErr($req->msg);
        return $req->code;
	}
	$resp = (object)array(
        'url' => $req->data->gp_pdf_name
    );
	return 0;
}

function GetCardId(&$resp)
{
    $_ = $GLOBALS["_"];
    if (!$_) {
        LogErr("param err");
        return errcode::PARAM_ERR;
    }


	$a = new GoldenOpenPlat\Request(Cfg::instance()->invoice->appkey, Cfg::instance()->invoice->appsecret);
	$post = [
		'appid'           => "wxe6ee6bc6898df9df",//商户公众号appid
		'full_name'       => "深圳前海赛领科技有限公司",//收款方全称
		'short_name'      => "欣吃货",//收款方简称
		'logo_url'        => ''//发票商家LOGO，不填默认为发票儿LOGO
	];
	//设置环境及发出请求
	$c = $a->setEnv()->requestPost("/card/create-template", $post);
	$req = json_decode($c['body']);
	if($req->code != 0)
	{
		LogErr($req->msg);
        return $req->code;
	}
	$resp = (object)array(
        'card_id' => $req->data->card_id
    );
	return 0;
}

$ret = -1;
$resp = (object)array();

if(isset($_["open_invoice"]))
{
    $ret = OpenInvoice($resp);
}
elseif(isset($_["get_invoice"]))
{
    $ret = GetInvoice($resp);
}
elseif(isset($_["get_card_id"]))
{
    $ret = GetCardId($resp);
}
else
{
    $ret = errcode::PARAM_ERR;
    LogErr("param err");
}

$html = json_encode((object)array(
    'ret'   => $ret,
    'data'  => $resp
    // 'crypt' => 1, // 是加密数据标记
    // 'data'  => PageUtil::EncRespData(json_encode($resp))
));
//LogDebug($html);
?><?php /******************************以下为html代码******************************/?>
<?=$html?>