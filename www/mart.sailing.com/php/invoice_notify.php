<?php
require_once("current_dir_env.php");
require_once("/www/public.sailing.com/php/mart/mgo_goods_order.php");
use \Pub\Mongodb as Mgo;

function notify()
{
	// {
	// 	"appkey":"kCQxS7OBx1NpVreVD2eIYPNbjJsk1kwV",
	// 	"message":"SUCCESS",
	// 	"notify_time":"2018-09-27 13:55:51",
	// 	"notify_type":"invoice.blue",
	// 	"order_id":"sl_goods_order_GO3117",
	// 	"g_unique_id":"20180927000000208815380277473241",
	// 	"ticket_sn":"01362043",
	// 	"ticket_code":"150001490126",
	// 	"ticket_status":2,
	// 	"ticket_date":"2018-09-27 13:55:51",
	// 	"ticket_total_amount_has_tax":"7.43",
	// 	"ticket_total_amount_no_tax":"7.01",
	// 	"ticket_tax_amount":"0.42",
	// 	"pdf_url":"http:\/\/kpserverdev-1251506165.cossh.myqcloud.com\/upload\/150001490126_01362043.pdf",
	//  "qrcode":"",
	// 	"check_code":"50148248051847528848"
	// }
    $ary = $GLOBALS['HTTP_RAW_POST_DATA'];
    $ary = json_decode($ary);
    LogDebug($ary);
    if($ary->ticket_status == 2)
    {
    	$order_id = substr($ary->order_id,15);
    	LogDebug($order_id);
    	$entry = new Mgo\GoodsOrderEntry;
		$entry->goods_order_id = $order_id;
		$entry->invoice_status = 2;
	    $ret = Mgo\GoodsOrder::My()->Save($entry);
    }
    elseif($ary->ticket_status == -2)
    {
    	LogErr($ary->message);
    	return errcode::SYS_ERR;
    }
    else
    {
    	LogErr('开票中');
    	return errcode::SYS_ERR;
    }

    return 0;
}

function main()
{
    $ret = notify();
    if(0 == $ret)
    {
        $html = json_encode((object)array(
			'code' => 0,
			'msg'  => 'success',
			'data' =>(object)array()
		));
        echo $html;
    }
}

main();

?>
