<?php
// 跨域访问
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');

?>
{
	"ret": 0,
    "data":{
 "is_invoice_vat": [
        {
          "is_invoice": 1,
          "invoice_type": [1,2,3]
        }
      ]
   }
}